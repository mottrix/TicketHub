<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ticketController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'userID' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'in:open,pending,closed'],
            'table-filter' => ['nullable', 'in:newest,oldest'],
        ]);

        $query = Ticket::query();
        $users = User::all();

        // Admin can filter tickets by user ID if provided
        if ($request->user()->isAdmin() && ! empty($validated['userID'])) {
            $query->where('user_id', $validated['userID']);
        }

        // user can only see their own tickets if they are not an admin
        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }
        $totalTickets = (clone $query)->count();
        $pendingTickets = (clone $query)->whereIn('status', ['pending', 'in_progress'])->count();
        $closeTickets = (clone $query)->whereIn('status', ['closed', 'close', 'resolved'])->count();
        $openTickets = (clone $query)->where('status', 'open')->count();

        //filter by status
        if (! empty($validated['status'])) {
            $statuses = [
                'open' => ['open'],
                'pending' => ['pending', 'in_progress'],
                'closed' => ['closed', 'close', 'resolved'],
            ];

            $query->whereIn('status', $statuses[$validated['status']]);
        }

        //filter by newest or oldest
        $direction = ($validated['table-filter'] ?? 'newest') === 'oldest' ? 'asc' : 'desc';
        $tickets = $query->orderBy('created_at', $direction)->orderBy('id', $direction)->get();

        return view('tickets.index', compact('tickets', 'totalTickets', 'pendingTickets', 'closeTickets', 'openTickets', 'users'));
    }

    public function create(): View
    {
        return view('tickets.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:20000'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        do {
            $ticketCode = random_int(100000000, 999999999);
        } while (Ticket::where('ticket_code', $ticketCode)->exists());

        $request->user()->tickets()->create([
            'ticket_code' => $ticketCode,
            'ticket_name' => $validated['title'],
            'description' => $validated['description'],
            'ticket_priority' => $validated['priority'],
            'status' => 'open',
        ]);

        return redirect()->route('index')
            ->with('success', 'Ticket created successfully.');
    }

    public function show(Ticket $ticket): View
    {
        Gate::authorize('show', $ticket);

        $descriptionText = preg_replace('/<\/(?:div|p|li)>|<br\s*\/?>/i', "\n", $ticket->description ?? '');
        $descriptionText = trim(html_entity_decode(strip_tags($descriptionText), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        $messages = Message::query()->where('ticket_id', $ticket->id)
            ->with('user')->oldest()->orderBy('id')->get();

        return view('tickets.show', compact('ticket', 'descriptionText', 'messages'));
    }

    public function edit(Ticket $ticket): View
    {
        Gate::authorize('update', $ticket);

        return view('tickets.edit', compact('ticket'));
    }

    public function update(Request $request, Ticket $ticket): RedirectResponse
    {
        Gate::authorize('update', $ticket);

        $canUpdateStatus = Gate::allows('updateStatus', $ticket);

        if (! $canUpdateStatus && $request->has('status') && $request->input('status') !== $ticket->status) {
            Gate::authorize('updateStatus', $ticket);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:20000'],
            'priority' => ['required', 'in:low,medium,high'],
            'status' => [$canUpdateStatus ? 'required' : 'sometimes', 'in:open,pending,closed'],
        ]);

        $attributes = [
            'ticket_name' => $validated['title'],
            'description' => $validated['description'],
            'ticket_priority' => $validated['priority'],
        ];

        if ($canUpdateStatus) {
            $attributes['status'] = $validated['status'];
        }

        $ticket->update($attributes);

        return redirect()->route('index')->with('success', 'Ticket updated successfully.');
    }

    public function updateStatus(Request $request, Ticket $ticket, User $user): RedirectResponse
    {

        Gate::authorize('updateStatus', $ticket);

        // Gate::authorize('updateStatus' , $user );

        $validated = $request->validate([
            'status' => ['required', 'in:open,pending,closed'],
        ]);

        $ticket->update($validated);

        return redirect()->route('ticket.show', $ticket);

    }

    public function destroy(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('index')->with('success', 'Ticket deleted successfully.');
    }
}
