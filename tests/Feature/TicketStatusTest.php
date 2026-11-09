<?php

use App\Models\User;
use Database\Factories\TicketFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\Gate;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('ticket details keep the current status selected using only the supported choices', function (string $storedStatus, string $selectedStatus) {
    $ticket = TicketFactory::new()->create(['status' => $storedStatus]);

    $response = $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('ticket.show', $ticket));

    $response->assertOk()->assertSee('value="'.$selectedStatus.'" selected', false)
        ->assertDontSee('In Progress');

    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $options = $xpath->query('//select[@name="status"]/option');
    $choices = [];

    foreach ($options as $option) {
        $choices[$option->getAttribute('value')] = trim($option->textContent);
    }

    expect($choices)->toBe(['open' => 'Open', 'pending' => 'Pending', 'closed' => 'Close']);
    expect($xpath->query('//fieldset[@data-composer and @disabled]')->length)
        ->toBe($selectedStatus === 'closed' ? 1 : 0);
})->with([
    ['open', 'open'], ['pending', 'pending'], ['closed', 'closed'],
    ['in_progress', 'pending'], ['close', 'closed'],
]);

test('the edit form submits pending rather than the retired in progress status', function (string $storedStatus) {
    $ticket = TicketFactory::new()->create(['status' => $storedStatus]);

    $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('ticket.edit', $ticket))
        ->assertOk()
        ->assertSee('name="status" id="statusInput" value="pending"', false)
        ->assertSee('data-status="pending"', false)
        ->assertDontSee('data-status="in_progress"', false);
})->with(['pending', 'in_progress']);

test('changing status from ticket details saves only the status and preserves the selected choice', function (string $status) {
    $ticket = TicketFactory::new()->create(['ticket_name' => 'Original title', 'description' => 'Original details']);

    $response = $this->actingAs(User::factory()->create(['role' => 'admin']))->patch(route('ticket.status.update', $ticket), ['status' => $status]);

    $response->assertRedirect(route('ticket.show', $ticket))->assertSessionHasNoErrors();
    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id, 'status' => $status,
        'ticket_name' => 'Original title', 'description' => 'Original details',
    ]);
    $this->get(route('ticket.show', $ticket))->assertOk()
        ->assertSee('value="'.$status.'" selected', false);
})->with(['open', 'pending', 'closed']);

test('status updates reject retired or unsupported values without changing the ticket', function (string $status, string $method, string $routeName) {
    $ticket = TicketFactory::new()->create(['status' => 'open']);

    $this->actingAs(User::factory()->create(['role' => 'admin']))->from(route('ticket.show', $ticket))
        ->{$method}(route($routeName, $ticket), [
            'title' => 'Valid title', 'description' => 'Valid details', 'priority' => 'medium',
            'status' => $status,
        ])
        ->assertRedirect(route('ticket.show', $ticket))->assertSessionHasErrors('status');

    $this->assertDatabaseHas('tickets', ['id' => $ticket->id, 'status' => 'open']);
})->with(['in_progress', 'resolved', 'invalid'])->with([
    ['patch', 'ticket.status.update'],
    ['put', 'ticket.update'],
]);

test('the dashboard counts and displays older in progress tickets as pending', function () {
    $owner = User::factory()->create();
    TicketFactory::new()->create(['user_id' => $owner->id, 'status' => 'pending']);
    TicketFactory::new()->create(['user_id' => $owner->id, 'status' => 'in_progress']);

    $this->actingAs($owner)->get(route('index'))->assertOk()
        ->assertViewHas('pendingTickets', 2)
        ->assertSee('Pending')->assertDontSee('In Progress')->assertDontSee('In progress');
});

test('only admins have permission to change ticket status', function () {
    $ticket = TicketFactory::new()->create();
    $admin = User::factory()->create(['role' => 'admin']);
    $otherUser = User::factory()->create();

    expect(Gate::forUser($admin)->allows('updateStatus', $ticket))->toBeTrue();
    expect(Gate::forUser($ticket->user)->allows('updateStatus', $ticket))->toBeFalse();
    expect(Gate::forUser($otherUser)->allows('updateStatus', $ticket))->toBeFalse();
});

test('ticket owners see the status but cannot select a different one', function (string $routeName) {
    $ticket = TicketFactory::new()->create(['status' => 'pending']);

    $this->actingAs($ticket->user)->get(route($routeName, $ticket))
        ->assertOk()->assertSee('Pending')
        ->assertDontSee('name="status"', false)
        ->assertDontSee('data-status=', false);
})->with(['ticket.show', 'ticket.edit']);

test('ticket owners cannot change status through either update endpoint', function (string $method, string $routeName) {
    $ticket = TicketFactory::new()->create(['status' => 'open', 'ticket_name' => 'Original title']);

    $this->actingAs($ticket->user)->{$method}(route($routeName, $ticket), [
        'title' => 'Changed title', 'description' => 'Changed details', 'priority' => 'low',
        'status' => 'closed',
    ])->assertForbidden();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id, 'status' => 'open', 'ticket_name' => 'Original title',
    ]);
})->with([['patch', 'ticket.status.update'], ['put', 'ticket.update']]);

test('ticket owners can edit other details without sending or changing status', function () {
    $ticket = TicketFactory::new()->create(['status' => 'pending']);

    $this->actingAs($ticket->user)->put(route('ticket.update', $ticket), [
        'title' => 'Updated title', 'description' => 'Updated details', 'priority' => 'high',
    ])->assertRedirect(route('index'))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id, 'ticket_name' => 'Updated title', 'description' => 'Updated details',
        'ticket_priority' => 'high', 'status' => 'pending',
    ]);
});

test('admins can change status through the full edit form', function (string $status) {
    $ticket = TicketFactory::new()->create();

    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->put(route('ticket.update', $ticket), [
            'title' => 'Updated by admin', 'description' => 'Details', 'priority' => 'medium',
            'status' => $status,
        ])->assertRedirect(route('index'))->assertSessionHasNoErrors();

    $this->assertDatabaseHas('tickets', [
        'id' => $ticket->id, 'ticket_name' => 'Updated by admin', 'status' => $status,
    ]);
})->with(['open', 'pending', 'closed']);
