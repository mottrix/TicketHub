<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * A signed-in customer or administrator who can own tickets and author replies.
 *
 * Registration can fill only name/email/password, so a submitted role cannot grant
 * administrator access. The database supplies the ordinary customer role, "user".
 * Hidden credentials are omitted when the model is converted to an array or JSON.
 */
#[Fillable(['name', 'email', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Read verification timestamps as dates and hash passwords assigned to the model.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Only the exact stored role "admin" grants administrator permissions.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Owned tickets; creating through this relationship sets user_id to this user.
     */
    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class, 'user_id');
    }
}
