<?php

namespace App\Policies;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TicketPolicy
{
    public function show(User $user, Ticket $ticket)
    {
        // return Response::allow() ;

        return ($user->isAdmin() || $user->id === $ticket->user_id) ? Response::allow() : Response::deny();

    }

    public function update(User $user, Ticket $ticket): Response
    {
        return $this->show($user, $ticket);
    }

    public function delete(User $user, Ticket $ticket): Response
    {
        return $this->show($user, $ticket);
    }

    public function updateStatus(User $user): Response
    {

        return $user->isAdmin() ? Response::allow() : Response::deny('Only admins can change ticket status.');
    }
}
