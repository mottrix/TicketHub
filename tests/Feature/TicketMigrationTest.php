<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    $this->originalConnection = DB::getDefaultConnection();
    config(['database.connections.migration_test' => [
        'driver' => 'sqlite',
        'database' => ':memory:',
        'foreign_key_constraints' => true,
    ]]);
    DB::setDefaultConnection('migration_test');
});

afterEach(function () {
    DB::purge('migration_test');
    DB::setDefaultConnection($this->originalConnection);
});

test('ticket schema upgrades and rollbacks preserve populated tickets and legacy messages', function () {
    $migrations = [];
    foreach (glob(database_path('migrations/*.php')) as $path) {
        $migrations[basename($path)] = require $path;
    }

    foreach ($migrations as $name => $migration) {
        if (str_starts_with($name, '2026_11_10_')) {
            continue;
        }
        $migration->up();
    }

    $userId = DB::table('users')->insertGetId([
        'name' => 'Legacy customer', 'email' => 'legacy@example.test', 'password' => 'unused',
    ]);
    $rootId = DB::table('tickets')->insertGetId([
        'ticket_code' => 100, 'ticket_name' => 'Original subject', 'ticket_priority' => 'high',
        'user_id' => $userId, 'description' => 'Original details',
    ]);
    Schema::create('messages', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
        $table->foreignId('ticket_id')->constrained()->cascadeOnDelete();
        $table->text('message');
        $table->timestamp('read_at')->nullable();
        $table->timestamps();
    });
    DB::table('messages')->insert([
        'user_id' => $userId, 'ticket_id' => $rootId, 'message' => 'Legacy reply',
        'read_at' => '2026-11-09 10:00:00',
    ]);
    $originalMessage = (array) DB::table('messages')->first();
    $structure = $migrations['2026_11_10_064023_add_parent_id_and_title_to_tickets_table.php'];
    $structure->up();
    $replyId = DB::table('tickets')->insertGetId([
        'ticket_code' => 101, 'ticket_name' => 'Legacy reply label', 'ticket_priority' => 'medium',
        'user_id' => $userId, 'parent_id' => $rootId, 'description' => 'New reply', 'title' => null,
    ]);
    $namedId = DB::table('tickets')->insertGetId([
        'ticket_code' => 102, 'ticket_name' => 'Old subject', 'ticket_priority' => 'low',
        'title' => 'Explicit subject',
    ]);
    $changes = array_filter($migrations, fn (string $name): bool => str_starts_with($name, '2026_11_10_08'), ARRAY_FILTER_USE_KEY);

    foreach ($changes as $migration) {
        $migration->up();
    }

    $this->assertDatabaseHas('tickets', [
        'id' => $rootId, 'title' => 'Original subject', 'priority' => 'high', 'description' => 'Original details',
    ]);
    $this->assertDatabaseHas('tickets', ['id' => $replyId, 'parent_id' => $rootId, 'title' => null]);
    $this->assertDatabaseHas('tickets', ['id' => $namedId, 'title' => 'Explicit subject']);
    foreach (['ticket_code', 'ticket_name', 'ticket_priority'] as $column) {
        expect(Schema::hasColumn('tickets', $column))->toBeFalse();
    }
    expect((array) DB::table('messages')->first())->toBe($originalMessage);

    foreach (array_reverse($changes) as $migration) {
        $migration->down();
    }

    $this->assertDatabaseHas('tickets', ['id' => $rootId, 'ticket_name' => 'Original subject', 'ticket_priority' => 'high', 'ticket_code' => null]);
    $this->assertDatabaseHas('tickets', ['id' => $namedId, 'ticket_name' => 'Explicit subject']);
    $this->assertDatabaseHas('tickets', ['id' => $replyId, 'parent_id' => $rootId, 'title' => null]);
    expect((array) DB::table('messages')->first())->toBe($originalMessage);

    $structure->down();

    expect(Schema::hasColumn('tickets', 'parent_id'))->toBeFalse();
    expect(Schema::hasColumn('tickets', 'title'))->toBeFalse();
    $this->assertDatabaseCount('tickets', 3);
    expect((array) DB::table('messages')->first())->toBe($originalMessage);
});

test('the conversation index supports chronological replies and rolls back without changing tickets', function () {
    $indexPath = database_path('migrations/2026_11_10_094645_add_parent_created_at_id_index_to_tickets_table.php');
    foreach (glob(database_path('migrations/*.php')) as $path) {
        if ($path !== $indexPath) {
            (require $path)->up();
        }
    }
    $rootId = DB::table('tickets')->insertGetId(['title' => 'Root', 'priority' => 'high']);
    DB::table('tickets')->insert([
        'parent_id' => $rootId, 'title' => null, 'priority' => 'medium',
        'description' => 'Reply', 'created_at' => '2026-11-10 10:00:00',
    ]);
    $originalTickets = DB::table('tickets')->orderBy('id')->get()->toArray();
    $migration = require $indexPath;

    $migration->up();

    expect(Schema::hasIndex('tickets', ['parent_id', 'created_at', 'id']))->toBeTrue();
    $plan = DB::select('EXPLAIN QUERY PLAN SELECT * FROM tickets WHERE parent_id = ? ORDER BY created_at, id', [$rootId]);
    expect(collect($plan)->pluck('detail')->implode(' '))->toContain('tickets_parent_created_at_id_index');
    expect(DB::table('tickets')->orderBy('id')->get()->toArray())->toEqual($originalTickets);

    $migration->down();

    expect(Schema::hasIndex('tickets', ['parent_id', 'created_at', 'id']))->toBeFalse();
    expect(DB::table('tickets')->orderBy('id')->get()->toArray())->toEqual($originalTickets);
});
