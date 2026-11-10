<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * A support request or reply, linked to its author and optional parent ticket.
 *
 * Status remains a database string. Current forms write open/pending/closed;
 * listing and reply logic also recognize in_progress, close, and resolved.
 */
class Ticket extends Model
{
    /**
     * Attributes accepted by create()/update(); this list does not authorize changes.
     * Controllers choose validated fields and enforce status permissions separately.
     * New tickets get their owner through the signed-in user's tickets() relationship.
     */
    protected $fillable = [

        'title',
        'parent_id',
        'description',
        'priority',
        'status',
        'user_id',
    ];

    public function isClosed(): bool
    {
        return in_array($this->status, ['closed', 'close', 'resolved'], true);
    }

    /**
     * The parent ticket; root tickets have no parent.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Direct child replies stored in the tickets table.
     */
    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * The ticket owner; legacy tickets may have no owner because user_id is nullable.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');

    }
}
