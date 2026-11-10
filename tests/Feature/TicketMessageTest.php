<?php

use App\Models\User;
use Database\Factories\TicketFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('the reply form targets the specific ticket being viewed', function (string $role) {
    $author = User::factory()->create(['role' => $role]);
    TicketFactory::new()->create(['user_id' => $author->id]);
    $ticket = TicketFactory::new()->create(['user_id' => $author->id]);

    $response = $this->actingAs($author)->get(route('ticket.show', $ticket))->assertOk();

    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $form = (new DOMXPath($document))->query('//form[.//textarea[@name="reply-message"]]')->item(0);

    expect($form)->not->toBeNull();
    expect($form->getAttribute('action'))->toBe(route('ticket.reply.store', $ticket));
})->with(['user', 'admin']);

test('a reply is saved against the route ticket and authenticated author', function (string $role) {
    $author = User::factory()->create(['role' => $role]);
    $otherTicket = TicketFactory::new()->create();
    $ticket = TicketFactory::new()->create(['user_id' => $role === 'admin' ? $otherTicket->user_id : $author->id]);

    $this->actingAs($author)->post(route('ticket.reply.store', $ticket), [
        'reply-message' => 'Please check this specific ticket.',
        'ticket_id' => $otherTicket->id, 'user_id' => $otherTicket->user_id,
    ])->assertRedirect(route('ticket.show', $ticket))->assertSessionHasNoErrors();

    $this->assertDatabaseCount('tickets', 3);
    $this->assertDatabaseHas('tickets', [
        'parent_id' => $ticket->id, 'user_id' => $author->id,
        'description' => 'Please check this specific ticket.',
    ]);
})->with(['user', 'admin']);

test('invalid replies are rejected without saving a message', function (array $payload) {
    $ticket = TicketFactory::new()->create();

    $this->actingAs($ticket->user)->from(route('ticket.show', $ticket))
        ->post(route('ticket.reply.store', $ticket), $payload)
        ->assertRedirect(route('ticket.show', $ticket))->assertSessionHasErrors('reply-message');

    $this->assertDatabaseCount('tickets', 1);
})->with([
    'missing' => [[]],
    'empty' => [['reply-message' => '   ']],
    'non-string' => [['reply-message' => ['invalid']]],
    'too long' => [['reply-message' => str_repeat('a', 20001)]],
]);

test('a user cannot reply to another users ticket', function () {
    $ticket = TicketFactory::new()->create();

    $this->actingAs(User::factory()->create())->post(route('ticket.reply.store', $ticket), [
        'reply-message' => 'Unauthorized reply',
    ])->assertNotFound();

    $this->assertDatabaseCount('tickets', 1);
});

test('guests must log in before replying', function () {
    $ticket = TicketFactory::new()->create();

    $this->post(route('ticket.reply.store', $ticket), ['reply-message' => 'Guest reply'])
        ->assertRedirect(route('login'));

    $this->assertDatabaseCount('tickets', 1);
});

test('closed tickets do not accept replies even through a direct request', function (string $status) {
    $ticket = TicketFactory::new()->create(['status' => $status]);

    $this->actingAs($ticket->user)->post(route('ticket.reply.store', $ticket), [
        'reply-message' => 'Reply to a closed ticket',
    ])->assertForbidden();

    $this->assertDatabaseCount('tickets', 1);
})->with(['closed', 'close', 'resolved']);

test('replying to a missing ticket returns not found', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('ticket.reply.store', 999), ['reply-message' => 'Missing ticket'])
        ->assertNotFound();

    $this->assertDatabaseCount('tickets', 0);
});

test('the conversation renders each saved reply once and only for the selected ticket', function () {
    $ticket = TicketFactory::new()->create();
    $otherTicket = TicketFactory::new()->create();
    $admin = User::factory()->create(['role' => 'admin', 'name' => 'Support Admin']);
    TicketFactory::new()->create(['parent_id' => $ticket->id, 'user_id' => $ticket->user_id, 'title' => null, 'description' => 'Customer question <script>alert(1)</script>']);
    TicketFactory::new()->create(['parent_id' => $ticket->id, 'user_id' => $admin->id, 'title' => null, 'description' => 'Support response']);
    TicketFactory::new()->create(['parent_id' => $otherTicket->id, 'user_id' => $otherTicket->user_id, 'title' => null, 'description' => 'Private other ticket reply']);

    $response = $this->actingAs($ticket->user)->get(route('ticket.show', $ticket));

    $response->assertOk()->assertSee('Customer question <script>alert(1)</script>')
        ->assertSee('Support response')->assertSee('Support Admin')
        ->assertDontSee('<script>alert(1)</script>', false)
        ->assertDontSee('Private other ticket reply');
    expect(substr_count($response->getContent(), e('Customer question <script>alert(1)</script>')))->toBe(1);
    expect(substr_count($response->getContent(), 'Support response'))->toBe(1);
});
