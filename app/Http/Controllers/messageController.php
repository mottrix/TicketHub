<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\ticket;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class messageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, ticket $ticket): RedirectResponse
    {
        Gate::authorize('show', $ticket); // why only admin and user == ticket-user-id could ac this route

        abort_if(in_array($ticket->status, ['closed', 'close', 'resolved']), 403, 'Replies are disabled for closed tickets.');

        $validated = $request->validate([
            'reply-message' => ['required', 'string', 'max:20000'],
        ]);

        $request->user()->messages()->create([
            'message' => $validated['reply-message'],
            'ticket_id' => $ticket->id,
        ]);

        return redirect()->route('ticket.show', $ticket)
            ->with('success', 'Reply sent successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Message $message)
    {
   

    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Message $message)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Message $message)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Message $message)
    {
        //
    }
}
