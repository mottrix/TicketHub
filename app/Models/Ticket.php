<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ticket extends Model
{
    protected $fillable = [
        'ticket_code',
        'ticket_name',
        'description',
        'ticket_priority',
        'status',
        'user_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');

    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }
}
