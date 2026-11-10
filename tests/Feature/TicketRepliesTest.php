<?php

use App\Models\User;
use Database\Factories\TicketFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('listings and dashboard statistics exclude replies while preserving owner filters', function (string $role) {
    $owner = User::factory()->create();
    $visitor = $role === 'user' ? $owner : User::factory()->create(['role' => 'admin']);
    $root = TicketFactory::new()->create(['user_id' => $owner->id, 'status' => 'pending']);
    TicketFactory::new()->create(['parent_id' => $root->id, 'user_id' => $owner->id, 'status' => 'closed']);
    TicketFactory::new()->create(['parent_id' => $root->id, 'user_id' => $visitor->id]);
    TicketFactory::new()->create();

    $response = $this->actingAs($visitor)->get(route('index', [
        'userID' => $owner->id, 'status' => 'pending', 'table-filter' => 'oldest',
    ]))->assertOk()->assertViewHas('totalTickets', 1)->assertViewHas('pendingTickets', 1)
        ->assertViewHas('openTickets', 0)->assertViewHas('closeTickets', 0);

    expect($response->viewData('tickets')->modelKeys())->toBe([$root->id]);
    expect($response->viewData('tickets')->first()->relationLoaded('user'))->toBeTrue();
})->with(['user', 'admin']);

test('ticket details render only eager loaded child replies without querying messages', function () {
    $root = TicketFactory::new()->create();
    $other = TicketFactory::new()->create();
    $reply = TicketFactory::new()->create([
        'parent_id' => $root->id, 'description' => 'New reply <script>alert(1)</script>',
    ]);
    TicketFactory::new()->create(['parent_id' => $other->id, 'description' => 'Private reply']);

    $this->actingAs($root->user);
    DB::enableQueryLog();

    try {
        $response = $this->get(route('ticket.show', $root))
            ->assertOk()->assertSee('New reply <script>alert(1)</script>')
            ->assertDontSee('<script>alert(1)</script>', false)->assertDontSee('Private reply');

        expect(collect(DB::getQueryLog())->filter(
            fn (array $query): bool => str_contains($query['query'], '"messages"')
        ))->toBeEmpty();
    } finally {
        DB::disableQueryLog();
        DB::flushQueryLog();
    }

    $loadedRoot = $response->viewData('ticket');
    expect($loadedRoot->relationLoaded('replies'))->toBeTrue();
    expect($loadedRoot->replies->modelKeys())->toBe([$reply->id]);
    expect($loadedRoot->replies->first()->relationLoaded('user'))->toBeTrue();
});

test('new ticket creation ignores a supplied parent and synchronizes its title', function () {
    $parent = TicketFactory::new()->create();

    $this->actingAs($parent->user)->post(route('ticket.store'), [
        'title' => 'New root', 'description' => 'Details', 'priority' => 'medium',
        'parent_id' => $parent->id,
    ])->assertRedirect(route('index'));

    $this->assertDatabaseHas('tickets', ['title' => 'New root', 'parent_id' => null]);
});

test('conversation replies retain chronological order with id breaking timestamp ties', function () {
    $root = TicketFactory::new()->create();
    $newest = TicketFactory::new()->create(['parent_id' => $root->id, 'created_at' => '2026-11-02 09:00:00']);
    $first = TicketFactory::new()->create(['parent_id' => $root->id, 'created_at' => '2026-11-01 09:00:00']);
    $second = TicketFactory::new()->create(['parent_id' => $root->id, 'created_at' => '2026-11-01 09:00:00']);

    $response = $this->actingAs($root->user)->get(route('ticket.show', $root))->assertOk();

    expect($response->viewData('ticket')->replies->modelKeys())->toBe([$first->id, $second->id, $newest->id]);
});

test('rendering more reply authors does not increase the conversation query count', function () {
    $root = TicketFactory::new()->create();
    TicketFactory::new()->create(['parent_id' => $root->id]);
    $this->actingAs($root->user);
    DB::enableQueryLog();

    try {
        $this->get(route('ticket.show', $root))->assertOk();
        $initialQueryCount = count(DB::getQueryLog());
        TicketFactory::new()->count(20)->create(['parent_id' => $root->id]);
        DB::flushQueryLog();

        $this->get(route('ticket.show', $root))->assertOk();

        expect(count(DB::getQueryLog()))->toBe($initialQueryCount);
    } finally {
        DB::disableQueryLog();
        DB::flushQueryLog();
    }
});

test('new replies use the authorized root and signed in author', function (string $role) {
    $root = TicketFactory::new()->create();
    $author = $role === 'user' ? $root->user : User::factory()->create(['role' => 'admin']);
    $other = TicketFactory::new()->create();

    $this->actingAs($author)->post('/tickets/'.$root->id.'/replies', [
        'reply-message' => 'A new Ticket reply', 'parent_id' => $other->id,
        'user_id' => $other->user_id, 'title' => 'Forged title', 'ticket_code' => 42,
        'status' => 'closed',
    ])->assertRedirect(route('ticket.show', $root))->assertSessionHasNoErrors();

    $reply = $root->replies()->sole();
    expect($reply->user_id)->toBe($author->id);
    expect($reply->title)->toBeNull();
    expect($reply->status)->toBe('open');
    expect($reply->description)->toBe('A new Ticket reply');
})->with(['user', 'admin']);

test('unauthorized users cannot view a root or create replies', function () {
    $root = TicketFactory::new()->create();
    $this->actingAs(User::factory()->create());

    $this->get(route('ticket.show', $root))->assertNotFound();
    $this->post('/tickets/'.$root->id.'/replies', ['reply-message' => 'Unauthorized'])->assertNotFound();

    $this->assertDatabaseCount('tickets', 1);
});

test('closed root tickets reject new replies', function (string $status) {
    $root = TicketFactory::new()->create(['status' => $status]);

    $this->actingAs($root->user)->post('/tickets/'.$root->id.'/replies', ['reply-message' => 'Blocked'])
        ->assertForbidden();

    $this->assertDatabaseCount('tickets', 1);
})->with(['closed', 'close', 'resolved']);

test('invalid new replies do not create ticket records', function (array $payload) {
    $root = TicketFactory::new()->create();

    $this->actingAs($root->user)->post('/tickets/'.$root->id.'/replies', $payload)
        ->assertSessionHasErrors('reply-message');

    $this->assertDatabaseCount('tickets', 1);
})->with([
    'missing' => [[]], 'blank' => [['reply-message' => '   ']],
    'array' => [['reply-message' => ['invalid']]],
    'too long' => [['reply-message' => str_repeat('a', 20001)]],
]);

test('child ticket ids cannot be used as standalone root endpoints', function (string $method, string $path) {
    $root = TicketFactory::new()->create();
    $reply = TicketFactory::new()->create(['parent_id' => $root->id]);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->{$method}(str_replace('{id}', (string) $reply->id, $path), ['reply-message' => 'Invalid nesting'])
        ->assertNotFound();

    $this->assertModelExists($reply);
    $this->assertDatabaseCount('tickets', 2);
})->with([
    ['get', '/tickets/{id}'], ['get', '/edit/{id}'], ['put', '/tickets/{id}'],
    ['delete', '/tickets/{id}'], ['patch', '/tickets/{id}/status'],
    ['post', '/tickets/{id}/replies'], ['post', '/tickets/{id}/messages'],
]);

test('guests and missing ticket ids cannot receive replies', function () {
    $this->post('/tickets/999/replies', ['reply-message' => 'Guest'])->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())->post('/tickets/999/replies', ['reply-message' => 'Missing'])
        ->assertNotFound();

    $this->assertDatabaseCount('tickets', 0);
});
