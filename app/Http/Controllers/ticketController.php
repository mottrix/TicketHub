<?php

namespace App\Http\Controllers;

use App\Models\Ticket;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

/**
 * Coordinates the ticket screens and writes reached through routes/web.php.
 *
 * Route binding supplies the ticket from the URL. TicketPolicy decides access;
 * validation decides which submitted fields are allowed to reach the database.
 */
class ticketController extends Controller
{
    /**
     * Build the dashboard from validated GET filters.
     *
     * Apply owner access first, then clone that query for the summary cards.
     * Status filtering narrows the table only, so the cards still summarize all
     * tickets accessible for the selected owner. Legacy status strings share
     * the current Pending/Close groups; ID breaks ties when timestamps match.
     */
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'userID' => ['nullable', 'integer', 'exists:users,id'],
            'status' => ['nullable', 'in:open,pending,closed'],
            'table-filter' => ['nullable', 'in:newest,oldest'],
            'search' => ['nullable', 'string', 'max:160'],
        ]);

        $query = Ticket::whereNull('parent_id')->with('user');
        $users = $request->user()->isAdmin() ? User::orderBy('name')->get() : collect();

        if ($request->user()->isAdmin() && ! empty($validated['userID'])) {
            $query->where('user_id', $validated['userID']);
        }

        if (! $request->user()->isAdmin()) {
            $query->where('user_id', $request->user()->id);
        }
        $totalTickets = (clone $query)->count();
        $pendingTickets = (clone $query)->whereIn('status', ['pending', 'in_progress'])->count();
        $closeTickets = (clone $query)->whereIn('status', ['closed', 'close', 'resolved'])->count();
        $openTickets = (clone $query)->where('status', 'open')->count();

        if (isset($validated['search']) && $validated['search'] !== '') {
            $search = $validated['search'];
            $query->where(function (Builder $query) use ($search): void {
                $query->where('title', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');

                $ticketId = filter_var($search, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
                if ($ticketId !== false) {
                    $query->orWhere('id', $ticketId);
                }
            });
        }

        if (! empty($validated['status'])) {
            $statuses = [
                'open' => ['open'],
                'pending' => ['pending', 'in_progress'],
                'closed' => ['closed', 'close', 'resolved'],
            ];

            $query->whereIn('status', $statuses[$validated['status']]);
        }

        $direction = ($validated['table-filter'] ?? 'newest') === 'oldest' ? 'asc' : 'desc';
        $tickets = $query->orderBy('created_at', $direction)->orderBy('id', $direction)->get();

        return view('tickets.index', compact('tickets', 'totalTickets', 'pendingTickets', 'closeTickets', 'openTickets', 'users'));
    }

    /**
     * Render the form; its editor and priority buttons submit hidden fields.
     */
    public function create(): View
    {
        return view('tickets.create');
    }

    /**
     * Map form names to database columns and create an open ticket for this user.
     *
     * Owner and initial status come from the server, not submitted values.
     * Root tickets always have a null parent, regardless of submitted input.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:160'],
            'description' => ['required', 'string', 'max:20000'],
            'priority' => ['required', 'in:low,medium,high'],
        ]);

        $request->user()->tickets()->create([
            'parent_id' => null,
            'title' => $validated['title'],
            'description' => $validated['description'],
            'priority' => $validated['priority'],
            'status' => 'open',
        ]);

        return redirect()->route('index')
            ->with('success', 'Ticket created successfully.');
    }

    /**
     * Show one authorized ticket and only the replies associated with its ID.
     *
     * Convert editor HTML to plain text while preserving paragraph breaks;
     * Blade escapes the resulting text. Eager load child replies and their authors
     * in chronological order; the conversation uses only Ticket records.
     */
    public function show(Ticket $ticket): View
    {
        Gate::authorize('show', $ticket);

        $descriptionText = preg_replace('/<\/(?:div|p|li)>|<br\s*\/?>/i', "\n", $ticket->description ?? '');
        $descriptionText = trim(html_entity_decode(strip_tags($descriptionText), ENT_QUOTES | ENT_HTML5, 'UTF-8'));

        $ticket->load([
            'user',
            'replies' => fn (HasMany $query): HasMany => $query->with('user')->oldest()->orderBy('id'),
        ]);

        return view('tickets.show', compact('ticket', 'descriptionText'));
    }

    /**
     * Create a child Ticket using the root from the URL and the authenticated author.
     */
    public function storeReply(Request $request, Ticket $ticket): RedirectResponse
    {
        DB::transaction(function () use ($request, $ticket): void {
            $root = Ticket::whereNull('parent_id')->lockForUpdate()->findOrFail($ticket->id);
            Gate::authorize('show', $root);

            abort_if($root->isClosed(), 403, 'Replies are disabled for closed tickets.');

            $validated = $request->validate([
                'reply-message' => ['required', 'string', 'max:20000'],
            ]);

            $request->user()->tickets()->create([
                'parent_id' => $root->id,
                'title' => null,
                'description' => $validated['reply-message'],
                'priority' => $root->priority,
                'status' => $root->status,
            ]);
        });

        return redirect()->route('ticket.show', $ticket)
            ->with('success', 'Reply sent successfully.');
    }

    /**
     * Require edit permission before exposing the existing ticket to the form.
     */
    public function edit(Ticket $ticket): View
    {
        Gate::authorize('update', $ticket);

        return view('tickets.edit', compact('ticket'));
    }

    /**
     * Save ticket details without letting an owner change status or ownership.
     *
     * Owners may omit status or send an unchanged current value (open, pending,
     * closed); unchanged legacy values still fail validation. A changed value
     * needs the separate admin-only updateStatus ability. Only authorized admins
     * add status to the update array; hiding controls is not the security check.
     */
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
            'title' => $validated['title'],

            'description' => $validated['description'],
            'priority' => $validated['priority'],
        ];

        if ($canUpdateStatus) {
            $attributes['status'] = $validated['status'];
        }

        $ticket->update($attributes);

        return redirect()->route('index')->with('success', 'Ticket updated successfully.');
    }

    /**
     * Handle the details-page status dropdown independently of the edit form.
     *
     * Persist closed even though the visible option label is Close.
     */
    public function updateStatus(Request $request, Ticket $ticket): RedirectResponse
    {

        Gate::authorize('updateStatus', $ticket);

        $validated = $request->validate([
            'status' => ['required', 'in:open,pending,closed'],
        ]);

        $ticket->update($validated);

        return redirect()->route('ticket.show', $ticket);

    }

    /**
     * Delete an authorized root; foreign keys cascade child tickets and legacy messages.
     */
    public function destroy(Ticket $ticket): RedirectResponse
    {
        Gate::authorize('delete', $ticket);

        $ticket->delete();

        return redirect()->route('index')->with('success', 'Ticket deleted successfully.');
    }
}
