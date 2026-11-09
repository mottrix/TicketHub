<?php

use App\Models\User;
use Database\Factories\TicketFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('users see only their own tickets and summary counts', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    TicketFactory::new()->create(['user_id' => $owner->id, 'ticket_name' => 'My open ticket']);
    TicketFactory::new()->create(['user_id' => $owner->id, 'status' => 'in_progress']);
    TicketFactory::new()->create(['user_id' => $owner->id, 'status' => 'closed']);
    TicketFactory::new()->create(['user_id' => $otherUser->id, 'ticket_name' => 'Private other ticket']);
    TicketFactory::new()->create(['user_id' => null, 'ticket_name' => 'Legacy unowned ticket']);

    $this->actingAs($owner)->get(route('index'))
        ->assertOk()->assertSee('My open ticket')
        ->assertDontSee('Private other ticket')->assertDontSee('Legacy unowned ticket')
        ->assertViewHas('totalTickets', 3)
        ->assertViewHas('openTickets', 1)
        ->assertViewHas('pendingTickets', 1)
        ->assertViewHas('closeTickets', 1);
});

test('admins see all users tickets including unowned tickets', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create();
    TicketFactory::new()->create(['user_id' => $owner->id, 'ticket_name' => 'Another users ticket']);
    TicketFactory::new()->create(['user_id' => null, 'ticket_name' => 'Legacy ticket']);

    $this->actingAs($admin)->get(route('index'))
        ->assertOk()->assertSee('Another users ticket')->assertSee('Legacy ticket')
        ->assertViewHas('totalTickets', 2)->assertViewHas('openTickets', 2);
});

test('the user filter respects ticket access and updates summary counts', function (string $role) {
    $visitor = User::factory()->create(['role' => $role]);
    $selectedUser = User::factory()->create();
    TicketFactory::new()->create(['user_id' => $visitor->id, 'ticket_name' => 'Visitor ticket']);
    TicketFactory::new()->create(['user_id' => $selectedUser->id, 'ticket_name' => 'Selected owner ticket', 'status' => 'closed']);

    $response = $this->actingAs($visitor)->get(route('index', ['userID' => $selectedUser->id]));

    $response->assertOk()->assertViewHas('totalTickets', 1);

    if ($role === 'admin') {
        $response->assertSee('Selected owner ticket')->assertDontSee('Visitor ticket')
            ->assertViewHas('closeTickets', 1)->assertViewHas('openTickets', 0);
    } else {
        $response->assertSee('Visitor ticket')->assertDontSee('Selected owner ticket')
            ->assertViewHas('openTickets', 1)->assertViewHas('closeTickets', 0);
    }
})->with(['admin', 'user']);

test('selecting all users clears the ticket filter', function () {
    TicketFactory::new()->create(['ticket_name' => 'First owner ticket']);
    TicketFactory::new()->create(['ticket_name' => 'Second owner ticket']);

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('index', ['userID' => '']))
        ->assertOk()->assertSee('First owner ticket')->assertSee('Second owner ticket')
        ->assertViewHas('totalTickets', 2);
});

test('the user filter rejects malformed user ids', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get(route('index', ['userID' => 'invalid']))
        ->assertSessionHasErrors('userID');
});

test('users cannot access another users ticket or an unowned ticket', function (string $method, string $routeName, bool $hasOwner) {
    $visitor = User::factory()->create();
    $owner = User::factory()->create();
    $ticket = TicketFactory::new()->create([
        'user_id' => $hasOwner ? $owner->id : null,
        'ticket_name' => 'Private ticket',
    ]);

    $this->actingAs($visitor)->{$method}(route($routeName, $ticket), [
        'title' => 'Changed', 'description' => 'Changed',
        'priority' => 'high', 'status' => 'closed',
    ])->assertNotFound();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id, 'ticket_name' => 'Private ticket', 'status' => 'open',
    ]);
})->with([
    ['get', 'ticket.show', true], ['get', 'ticket.edit', true],
    ['put', 'ticket.update', true], ['delete', 'ticket.destroy', true],
    ['get', 'ticket.show', false], ['get', 'ticket.edit', false],
    ['put', 'ticket.update', false], ['delete', 'ticket.destroy', false],
]);

test('admins can access tickets belonging to other users', function (string $method, string $routeName) {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create();
    $ticket = TicketFactory::new()->create(['user_id' => $owner->id]);

    $response = $this->actingAs($admin)->{$method}(route($routeName, $ticket), [
        'title' => 'Admin update', 'description' => 'Updated by admin',
        'priority' => 'low', 'status' => 'closed',
    ]);

    if ($method === 'get') {
        $response->assertOk();
        $this->assertModelExists($ticket);
    } elseif ($method === 'put') {
        $response->assertRedirect(route('index'));
        $this->assertDatabaseHas('tickets', [
            'id' => $ticket->id, 'ticket_name' => 'Admin update', 'user_id' => $owner->id,
        ]);
    } else {
        $response->assertRedirect(route('index'));
        $this->assertModelMissing($ticket);
    }
})->with([
    ['get', 'ticket.show'], ['get', 'ticket.edit'],
    ['put', 'ticket.update'], ['delete', 'ticket.destroy'],
]);

test('ticket creation uses the logged in owner despite a supplied user id', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();

    $this->actingAs($owner)->post(route('ticket.store'), [
        'title' => 'My ticket', 'description' => 'Details',
        'priority' => 'medium', 'user_id' => $otherUser->id,
    ])->assertRedirect(route('index'));

    $this->assertDatabaseHas('tickets', ['ticket_name' => 'My ticket', 'user_id' => $owner->id]);
});

test('ticket updates cannot transfer ownership', function () {
    $owner = User::factory()->create();
    $otherUser = User::factory()->create();
    $ticket = TicketFactory::new()->create(['user_id' => $owner->id]);

    $this->actingAs($owner)->put(route('ticket.update', $ticket), [
        'title' => 'Updated ticket', 'description' => 'Details',
        'priority' => 'medium', 'status' => 'open', 'user_id' => $otherUser->id,
    ])->assertRedirect(route('index'));

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id, 'ticket_name' => 'Updated ticket', 'user_id' => $owner->id,
    ]);
});
