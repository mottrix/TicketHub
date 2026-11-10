<?php

use App\Models\Ticket;
use App\Models\User;
use Database\Factories\TicketFactory;
use Database\Seeders\TicketSeeder;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('the seeder creates ten root conversations with ten correctly authored replies each', function () {
    User::factory()->count(2)->create();
    $admin = User::factory()->create(['role' => 'admin']);

    $this->seed(TicketSeeder::class);

    $roots = Ticket::whereNull('parent_id')->with('user', 'replies.user')->get();
    expect($roots)->toHaveCount(10);
    $this->assertDatabaseCount('tickets', 110);

    foreach ($roots as $root) {
        expect($root->title)->not->toBeEmpty();
        expect($root->priority)->toBeIn(['low', 'medium', 'high']);
        expect($root->user->isAdmin())->toBeFalse();
        expect($root->replies)->toHaveCount(10);

        foreach ($root->replies->sortBy('id')->values() as $index => $reply) {
            expect($reply->parent_id)->toBe($root->id);
            expect($reply->title)->toBeNull();
            expect($reply->user_id)->toBe($index % 2 === 0 ? $root->user_id : $admin->id);
            expect($reply->description)->not->toBeEmpty();
        }
    }
});

test('rerunning the seeder preserves existing records without duplicate replies', function () {
    $owner = User::factory()->create();
    User::factory()->create(['role' => 'admin']);
    $legacy = TicketFactory::new()->create([
        'title' => 'Unable to sign in',
        'user_id' => $owner->id, 'description' => 'Preserve these existing details.',
    ]);
    $this->seed(TicketSeeder::class);
    $original = Ticket::orderBy('id')->get()->toArray();

    $this->seed(TicketSeeder::class);

    expect(Ticket::orderBy('id')->get()->toArray())->toBe($original);
    expect($legacy->fresh()->description)->toBe('Preserve these existing details.');
    $this->assertDatabaseCount('tickets', 110);
});

test('a root with an invalid owner rolls back seeding without changing existing records', function () {
    User::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);
    $unrelated = TicketFactory::new()->create(['title' => 'Unable to sign in', 'user_id' => $admin->id]);

    expect(fn () => $this->seed(TicketSeeder::class))->toThrow(RuntimeException::class);

    $this->assertDatabaseCount('tickets', 1);
    $this->assertModelExists($unrelated);
});

test('a matching reply with the wrong author rolls back rather than creating a duplicate', function () {
    $owner = User::factory()->create();
    $admin = User::factory()->create(['role' => 'admin']);
    $root = TicketFactory::new()->create(['title' => 'Unable to sign in', 'user_id' => $owner->id]);
    $reply = TicketFactory::new()->create([
        'parent_id' => $root->id, 'user_id' => $admin->id, 'title' => null,
        'description' => 'I cannot sign in to my account. After entering my email and password, the page keeps loading and eventually displays an error.',
    ]);

    expect(fn () => $this->seed(TicketSeeder::class))->toThrow(RuntimeException::class);

    $this->assertDatabaseCount('tickets', 2);
    $this->assertModelExists($reply);
});

test('the seeder requires both a customer and an administrator before writing tickets', function () {
    User::factory()->create();

    expect(fn () => $this->seed(TicketSeeder::class))->toThrow(RuntimeException::class);

    $this->assertDatabaseCount('tickets', 0);
});

test('ambiguous root titles are rejected without attaching replies to an arbitrary ticket', function () {
    User::factory()->create(['role' => 'admin']);
    TicketFactory::new()->count(2)->create(['title' => 'Unable to sign in']);

    expect(fn () => $this->seed(TicketSeeder::class))->toThrow(RuntimeException::class);

    $this->assertDatabaseCount('tickets', 2);
});
