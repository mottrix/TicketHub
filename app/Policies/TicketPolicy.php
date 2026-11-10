<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/**
 * Shared ticket permissions used by controller Gate checks and Blade @can checks.
 * Listing applies its own owner filter in ticketController::index().
 */
class TicketPolicy
{
    /**
     * Owners and administrators may view a ticket and attempt to reply to it.
     * The reply controller separately blocks replies when the ticket is closed.
     * An unowned legacy ticket can therefore be accessed only by administrators.
     */
    public function show(User $user, Ticket $ticket): Response
    {
        return $ticket->parent_id === null && ($user->isAdmin() || $user->id === $ticket->user_id)
            ? Response::allow()
            : Response::denyAsNotFound();

    }

    /**
     * Editing details follows viewing access; changing status has a separate check.
     */
    public function update(User $user, Ticket $ticket): Response
    {
        return $this->show($user, $ticket);
    }

    /**
     * Both owners and administrators may delete a ticket under the current policy.
     * The database also removes replies through the ticket's cascading foreign key.
     */
    public function delete(User $user, Ticket $ticket): Response
    {
        return $this->show($user, $ticket);
    }

    /**
     * Status changes depend only on the administrator role, regardless of ownership.
     */
    public function updateStatus(User $user, Ticket $ticket): Response
    {
        $access = $this->show($user, $ticket);
        if ($access->denied()) {
            return $access;
        }

        return $user->isAdmin() ? Response::allow() : Response::deny('Only admins can change ticket status.');
    }
}
