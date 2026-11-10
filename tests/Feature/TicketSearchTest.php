<?php

use App\Models\User;
use Database\Factories\TicketFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('search finds root titles or descriptions without exposing other users or replies', function () {
    $owner = User::factory()->create();
    $matching = TicketFactory::new()->create(['user_id' => $owner->id, 'title' => 'Printer problem']);
    $details = TicketFactory::new()->create(['user_id' => $owner->id, 'description' => 'Printer is offline']);
    TicketFactory::new()->create(['user_id' => $owner->id, 'title' => 'Other issue']);
    TicketFactory::new()->create(['title' => 'Printer private issue']);
    TicketFactory::new()->create(['parent_id' => $matching->id, 'user_id' => $owner->id, 'description' => 'Printer reply']);

    $response = $this->actingAs($owner)->get(route('index', ['search' => 'Printer', 'table-filter' => 'oldest']))
        ->assertOk()->assertViewHas('totalTickets', 3);

    expect($response->viewData('tickets')->modelKeys())->toBe([$matching->id, $details->id]);
});

test('search preserves admin owner and status filtering', function () {
    $owner = User::factory()->create();
    $matching = TicketFactory::new()->create(['user_id' => $owner->id, 'title' => 'Account issue', 'status' => 'pending']);
    TicketFactory::new()->create(['user_id' => $owner->id, 'title' => 'Account closed', 'status' => 'closed']);
    TicketFactory::new()->create(['title' => 'Account other user', 'status' => 'pending']);

    $response = $this->actingAs(User::factory()->create(['role' => 'admin']))->get(route('index', [
        'search' => 'Account', 'userID' => $owner->id, 'status' => 'pending', 'table-filter' => 'oldest',
    ]))->assertOk()->assertViewHas('totalTickets', 2);

    expect($response->viewData('tickets')->modelKeys())->toBe([$matching->id]);
});

test('numeric search can find the accessible root by id', function () {
    $ticket = TicketFactory::new()->create(['title' => 'Support request']);

    $response = $this->actingAs($ticket->user)->get(route('index', ['search' => (string) $ticket->id]))->assertOk();

    expect($response->viewData('tickets')->modelKeys())->toBe([$ticket->id]);
});

test('malformed searches are rejected', function (mixed $search) {
    $this->actingAs(User::factory()->create())->get(route('index', ['search' => $search]))->assertSessionHasErrors('search');
})->with([
    'array' => [['nested']],
    'too long' => [str_repeat('a', 161)],
]);
