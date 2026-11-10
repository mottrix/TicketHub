<?php

use Database\Factories\TicketFactory;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;

uses(LazilyRefreshDatabase::class);

test('root tickets can omit a title and have no parent', function () {
    $ticket = TicketFactory::new()->create(['title' => null]);

    expect($ticket->fresh()->title)->toBeNull();
    expect($ticket->parent)->toBeNull();
    expect($ticket->user->id)->toBe($ticket->user_id);
});

test('replies belong to their parent and retain their own author', function () {
    $parent = TicketFactory::new()->create();
    $otherTicket = TicketFactory::new()->create();
    $attributes = TicketFactory::new()->raw(['title' => null]);

    $reply = $parent->replies()->create($attributes);

    expect($reply->fresh()->title)->toBeNull();
    expect($reply->parent->id)->toBe($parent->id);
    expect($reply->user->id)->toBe($attributes['user_id']);
    expect($parent->replies->modelKeys())->toBe([$reply->id]);
    expect($otherTicket->replies)->toBeEmpty();
});

test('a ticket cannot reference a missing parent', function () {
    expect(fn () => TicketFactory::new()->create(['parent_id' => 999999]))
        ->toThrow(QueryException::class);
});

test('deleting a root removes its replies while preserving unrelated tickets', function () {
    $parent = TicketFactory::new()->create();
    $reply = TicketFactory::new()->create(['parent_id' => $parent->id]);
    $unrelated = TicketFactory::new()->create();

    $parent->delete();

    $this->assertModelMissing($reply);
    $this->assertModelExists($unrelated);
});

test('deleting a root owner cascades through replies by other authors and preserves unrelated tickets', function () {
    $root = TicketFactory::new()->create();
    $reply = TicketFactory::new()->create(['parent_id' => $root->id]);
    $nestedReply = TicketFactory::new()->create(['parent_id' => $reply->id]);
    $unrelated = TicketFactory::new()->create(['user_id' => $reply->user_id]);

    $root->user->delete();

    $this->assertModelMissing($root);
    $this->assertModelMissing($reply);
    $this->assertModelMissing($nestedReply);
    $this->assertModelExists($unrelated);
});
