<?php

use App\Models\User;
use Database\Factories\TicketFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('guests must log in for every ticket action', function (string $method, string $uri) {
    $this->{$method}($uri)->assertRedirect(route('login'));
})->with([
    ['get', '/'], ['get', '/create'], ['post', '/tickets'],
    ['get', '/tickets/1'], ['get', '/edit/1'],
    ['put', '/tickets/1'], ['delete', '/tickets/1'],
]);

test('the dashboard shows tickets and counts each status', function () {
    TicketFactory::new()->create(['title' => 'Open issue']);
    TicketFactory::new()->create(['status' => 'in_progress']);
    TicketFactory::new()->create(['status' => 'closed']);

    $this->actingAs(User::factory()->create(['role' => 'admin']))->get('/')
        ->assertOk()->assertSee('Open issue')
        ->assertViewHas('totalTickets', 3)
        ->assertViewHas('openTickets', 1)
        ->assertViewHas('pendingTickets', 1)
        ->assertViewHas('closeTickets', 1);
});

test('the create form posts to the store route', function () {
    $this->actingAs(User::factory()->create())->get('/create')
        ->assertOk()->assertSee(route('ticket.store'), false)
        ->assertSee('name="description"', false)
        ->assertSee('name="priority"', false);
});

test('valid tickets are persisted with mapped fields and an open status', function () {
    $this->actingAs(User::factory()->create())->post('/tickets', [
        'title' => 'Cannot log in',
        'description' => 'The password reset link does not arrive.',
        'priority' => 'high',
        'status' => 'closed',
        'ticket_code' => 42,
    ])->assertRedirect(route('index'))->assertSessionHas('success');

    $this->assertDatabaseHas('tickets', [
        'title' => 'Cannot log in',
        'description' => 'The password reset link does not arrive.',
        'priority' => 'high',
        'status' => 'open',
    ]);
    $this->assertDatabaseCount('tickets', 1);
});

test('invalid creation input is rejected without saving a ticket', function (array $payload, array $errors) {
    $this->actingAs(User::factory()->create())
        ->from(route('create'))->post('/tickets', $payload)
        ->assertRedirect(route('create'))->assertSessionHasErrors($errors);

    $this->assertDatabaseCount('tickets', 0);
})->with([
    'missing fields' => [[], ['title', 'description', 'priority']],
    'invalid fields' => [
        ['title' => str_repeat('x', 161), 'description' => ['bad'], 'priority' => 'urgent'],
        ['title', 'description', 'priority'],
    ],
]);

test('show and edit display the bound ticket with escaped content', function (string $routeName) {
    $ticket = TicketFactory::new()->create(['title' => '<script>alert(1)</script>']);

    $this->actingAs($ticket->user)->get(route($routeName, $ticket))
        ->assertOk()->assertSee('<script>alert(1)</script>')
        ->assertDontSee('<script>alert(1)</script>', false);
})->with(['ticket.show', 'ticket.edit']);

test('ticket details display editor entities and paragraphs as readable text', function () {
    $ticket = TicketFactory::new()->create([
        'description' => '<div>A &amp; B</div><div>Second line</div>',
    ]);

    $this->actingAs($ticket->user)->get(route('ticket.show', $ticket))
        ->assertOk()->assertSee("A & B\nSecond line");
});

test('updates persist ticket changes without changing its identity', function () {
    $ticket = TicketFactory::new()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))->put(route('ticket.update', $ticket), [
        'title' => 'Updated issue', 'description' => 'More details',
        'priority' => 'low', 'status' => 'pending', 'ticket_code' => 42,
    ])->assertRedirect(route('index'))->assertSessionHas('success');

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id, 'title' => 'Updated issue',
        'description' => 'More details', 'priority' => 'low',
        'status' => 'pending',
    ]);
});

test('invalid updates leave the ticket unchanged', function () {
    $ticket = TicketFactory::new()->create(['title' => 'Original issue']);

    $this->actingAs(User::factory()->create(['role' => 'admin']))->put(route('ticket.update', $ticket), [
        'title' => '', 'description' => '', 'priority' => 'urgent', 'status' => 'invalid',
    ])->assertSessionHasErrors(['title', 'description', 'priority', 'status']);

    $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'title' => 'Original issue', 'status' => 'open']);
});

test('deleting a ticket removes it from the database', function () {
    $ticket = TicketFactory::new()->create();

    $this->actingAs($ticket->user)->delete(route('ticket.destroy', $ticket))
        ->assertRedirect(route('index'))->assertSessionHas('success');

    $this->assertModelMissing($ticket);
});

test('missing tickets return 404', function (string $method, string $uri) {
    $this->actingAs(User::factory()->create())->{$method}($uri)->assertNotFound();
})->with([
    ['get', '/tickets/999'], ['get', '/edit/999'],
    ['put', '/tickets/999'], ['delete', '/tickets/999'],
]);

test('ticket forms remain renderable after rejecting array inputs', function (string $action) {
    $ticket = TicketFactory::new()->create();
    $this->actingAs($ticket->user);
    $formRoute = $action === 'create' ? route('create') : route('ticket.edit', $ticket);
    $payload = ['title' => ['invalid'], 'description' => ['invalid'], 'priority' => ['invalid']];

    $response = $action === 'create'
        ? $this->from($formRoute)->post(route('ticket.store'), $payload)
        : $this->from($formRoute)->put(route('ticket.update', $ticket), $payload);
    $response->assertRedirect($formRoute)->assertSessionHasErrors(['title', 'description', 'priority']);

    $this->get($formRoute)->assertOk();
})->with(['create', 'edit']);

test('ticket details ignore malformed search control values on unrelated routes', function () {
    $ticket = TicketFactory::new()->create();

    $this->actingAs($ticket->user)->get(route('ticket.show', [
        'ticket' => $ticket, 'search' => ['invalid'], 'status' => ['invalid'],
    ]))->assertOk();
});
