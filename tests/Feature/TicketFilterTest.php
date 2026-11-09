<?php

use App\Models\User;
use Database\Factories\TicketFactory;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

beforeEach(function () {
    $this->withoutVite();
});

test('ticket sorting returns records in the selected order', function (?string $sort) {
    $owner = User::factory()->create();
    $first = TicketFactory::new()->create(['user_id' => $owner->id, 'created_at' => '2026-11-01 09:00:00']);
    $second = TicketFactory::new()->create(['user_id' => $owner->id, 'created_at' => '2026-11-02 09:00:00']);
    $third = TicketFactory::new()->create(['user_id' => $owner->id, 'created_at' => '2026-11-02 09:00:00']);

    $response = $this->actingAs($owner)->get(route('index', ['table-filter' => $sort]))->assertOk();

    expect($response->viewData('tickets')->modelKeys())->toBe(
        $sort === 'oldest' ? [$first->id, $second->id, $third->id] : [$third->id, $second->id, $first->id]
    );
})->with(['newest', 'oldest', '', null]);

test('status filtering includes the matching legacy statuses', function (string $status, array $expectedStatuses) {
    $owner = User::factory()->create();

    foreach (['open', 'pending', 'in_progress', 'closed', 'close', 'resolved'] as $storedStatus) {
        TicketFactory::new()->create(['user_id' => $owner->id, 'status' => $storedStatus]);
    }

    $response = $this->actingAs($owner)->get(route('index', ['status' => $status]))->assertOk();

    expect($response->viewData('tickets')->pluck('status')->sort()->values()->all())->toBe($expectedStatuses);
})->with([
    ['open', ['open']],
    ['pending', ['in_progress', 'pending']],
    ['closed', ['close', 'closed', 'resolved']],
    ['', ['close', 'closed', 'in_progress', 'open', 'pending', 'resolved']],
]);

test('combined filters preserve ticket access and the selected user', function (string $role) {
    $visitor = User::factory()->create(['role' => $role]);
    $selectedUser = User::factory()->create();
    $ownTicket = TicketFactory::new()->create(['user_id' => $visitor->id, 'status' => 'pending']);
    $selectedTicket = TicketFactory::new()->create(['user_id' => $selectedUser->id, 'status' => 'pending']);
    TicketFactory::new()->create(['user_id' => $selectedUser->id, 'status' => 'open']);

    $response = $this->actingAs($visitor)->get(route('index', [
        'userID' => $selectedUser->id, 'status' => 'pending', 'table-filter' => 'oldest',
    ]))->assertOk();

    expect($response->viewData('tickets')->modelKeys())->toBe([
        $role === 'admin' ? $selectedTicket->id : $ownTicket->id,
    ]);
})->with(['admin', 'user']);

test('the filter controls share a form and keep their selected values', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $owner = User::factory()->create();

    $response = $this->actingAs($admin)->get(route('index', [
        'userID' => $owner->id, 'status' => 'pending', 'table-filter' => 'oldest',
    ]))->assertOk();

    $document = new DOMDocument;
    @$document->loadHTML($response->getContent());
    $xpath = new DOMXPath($document);
    $form = $xpath->query('//form[.//select[@name="table-filter"]]')->item(0);

    expect($form)->not->toBeNull();
    expect($xpath->query('.//select', $form)->length)->toBe(3);
    $selected = [];

    foreach ($xpath->query('.//select', $form) as $select) {
        $option = $xpath->query('./option[@selected]', $select)->item(0);
        $selected[$select->getAttribute('name')] = $option?->getAttribute('value');
    }

    expect($selected)->toBe(['userID' => (string) $owner->id, 'status' => 'pending', 'table-filter' => 'oldest']);
});

test('unsupported filter values are rejected', function (array $query, string $error) {
    $this->actingAs(User::factory()->create())->get(route('index', $query))
        ->assertSessionHasErrors($error);
})->with([
    [['status' => 'invalid'], 'status'],
    [['table-filter' => 'invalid'], 'table-filter'],
]);
