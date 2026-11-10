<div
    class="mt-6 rounded-xl border border-slate-200 bg-white p-4 shadow-sm transition-colors dark:border-slate-800 dark:bg-slate-900 sm:p-6">

    {{-- All filters share one GET form, so changing any select submits the others too.
         request() restores the selected options after the controller filters the tickets. --}}
    <form action="{{ route('index') }}" method="GET" class="mb-5 flex flex-wrap items-center justify-between gap-3">

        <input type="hidden" name="search" value="{{ request('search') }}">

        @if (auth()->user()->role === 'admin')

                <select name="userID" aria-label="Filter by user" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-blue-400 dark:focus:ring-blue-400/20">

                    <option value="">All users</option>
                    @foreach ($users as $user)
                        <option value="{{ $user->id }}" @selected((string) request('userID') === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
        @endif

        <div class="ml-auto flex flex-wrap items-center justify-end gap-3">
            <select name="status" aria-label="Filter by status" onchange="this.form.submit()"
                class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-blue-400 dark:focus:ring-blue-400/20">
                <option value="" @selected(! request('status'))>All Statuses</option>
                <option value="open" @selected(request('status') === 'open')>Open</option>
                <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                <option value="closed" @selected(request('status') === 'closed')>Close</option>
            </select>

                <select aria-label="Sort tickets" name="table-filter" onchange="this.form.submit()"
                    class="rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none transition focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200 dark:focus:border-blue-400 dark:focus:ring-blue-400/20">
                    <option value="newest" @selected(request('table-filter') !== 'oldest')>Newest First</option>
                    <option value="oldest" @selected(request('table-filter') === 'oldest')>Oldest First</option>
                </select>

        </div>
    </form>

    {{-- Tickets Table --}}
    <div class="overflow-x-auto">

        <table class="w-full text-left text-sm text-slate-700 dark:text-slate-300">

            {{-- Table Head --}}
            <thead class="bg-slate-50 text-slate-600 dark:bg-slate-800/80 dark:text-slate-300">
                <tr>

                    <th scope="col" class="px-4 py-3 font-semibold">ID</th>
                    @if (auth()->user()->role === 'admin')
                        <th class="px-4 py-4">Ticket Owner</th>
                    @endif
                    <th scope="col" class="px-4 py-3 font-semibold">Title</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Priority</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Status</th>
                    <th scope="col" class="px-4 py-3 font-semibold">Created At</th>
                    <th scope="col" class="px-4 py-3 text-center font-semibold">Actions</th>
                </tr>
            </thead>

            {{-- Table Body --}}
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">

                @forelse ($tickets as $ticket)
                    <tr class="transition-colors hover:bg-slate-50 dark:hover:bg-slate-800/70">

                        {{-- ID --}}
                        <td class="px-4 py-4 font-medium text-slate-600 dark:text-slate-300">
                            #{{ str_pad($ticket->id, 4, '0', STR_PAD_LEFT) }}
                        </td>
                        {{-- userName of ticket for admin --}}
                        @if (auth()->user()->role === 'admin')
                            <td class="px-4 py-4">
                                <div class="font-semibold text-slate-800 dark:text-slate-100">
                                    {{ $ticket->user?->name ?? 'Unknown User' }}
                                </div>
                            </td>
                        @endif

                        {{-- Title --}}
                        <td class="px-4 py-4">
                            <div class="font-semibold text-slate-800 dark:text-slate-100">
                                {{ $ticket->title }}
                            </div>

                            {{-- Convert editor HTML into a short plain-text preview; Blade escapes the result. --}}
                            <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                {{ Str::limit(html_entity_decode(strip_tags($ticket->description ?? ''), ENT_QUOTES | ENT_HTML5, 'UTF-8'), 35) }}
                            </div>
                        </td>

                        {{-- Priority --}}
                        <td class="px-4 py-4">
                            <span @class([
                                'rounded-lg px-3 py-1 text-xs font-medium',
                                'bg-red-50 text-red-700 dark:bg-red-950/60 dark:text-red-300' =>
                                    $ticket->priority === 'high',
                                'bg-orange-50 text-orange-700 dark:bg-orange-950/60 dark:text-orange-300' =>
                                    $ticket->priority === 'medium',
                                'bg-emerald-50 text-emerald-700 dark:bg-emerald-950/60 dark:text-emerald-300' =>
                                    $ticket->priority === 'low',
                            ])>
                                {{ ucfirst($ticket->priority) }}
                            </span>
                        </td>

                        {{-- Status --}}
                        <td class="px-4 py-4">
                            <x-ticket-badge :value="$ticket->status" />
                        </td>

                        {{-- Created At --}}
                        <td class="px-4 py-4 text-slate-600 dark:text-slate-300">
                            <div>{{ $ticket->created_at?->format('M d, Y') }}</div>
                            <div class="mt-1 text-xs text-slate-400 dark:text-slate-500">
                                {{ $ticket->created_at?->format('h:i A') }}
                            </div>
                        </td>

                        {{-- Actions --}}
                        <td class="px-4 py-4">
                            <div class="flex items-center justify-center gap-2">

                                {{-- View --}}
                                <a href="{{ route('ticket.show', $ticket) }}" aria-label="View ticket"
                                    class="rounded-lg border border-slate-200 p-2 text-slate-600 transition-colors hover:bg-blue-50 hover:text-blue-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-blue-950/60 dark:hover:text-blue-300">
                                    <span>👁</span>
                                </a>

                                {{-- Edit --}}
                                @can('update', $ticket)
                                <a href="{{ route('ticket.edit', $ticket) }}" aria-label="Edit ticket"
                                    class="rounded-lg border border-slate-200 p-2 text-slate-600 transition-colors hover:bg-amber-50 hover:text-amber-600 dark:border-slate-700 dark:text-slate-300 dark:hover:bg-amber-950/60 dark:hover:text-amber-300">
                                    <span>✎</span>
                                </a>
                                @endcan

                                {{-- Delete --}}
                                <form action="{{ route('ticket.destroy', $ticket) }}" method="POST"
                                    onsubmit="return confirm('Are you sure you want to delete this ticket?')">

                                    {{-- HTML submits POST; Laravel reads the hidden _method field as DELETE. --}}
                                    @csrf
                                    @method('DELETE')

                                    <button type="submit" aria-label="Delete ticket"
                                        class="rounded-lg border border-red-100 p-2 text-red-500 transition-colors hover:bg-red-50 dark:border-red-900/70 dark:text-red-300 dark:hover:bg-red-950/60">
                                        🗑
                                    </button>
                                </form>

                            </div>
                        </td>

                    </tr>

                @empty

                    <tr>
                        <td colspan="{{ auth()->user()->isAdmin() ? 7 : 6 }}" class="py-10 text-center text-slate-400 dark:text-slate-500">
                            No tickets found.
                        </td>
                    </tr>
                @endforelse

            </tbody>

        </table>

    </div>

</div>
