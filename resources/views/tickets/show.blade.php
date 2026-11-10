@extends('components.layout')

@section('content')
    {{-- Normalize older stored status names for badges, status selection, and reply availability. --}}
    @php
        $status = ['in_progress' => 'pending', 'close' => 'closed', 'resolved' => 'closed'][$ticket->status] ?? $ticket->status;
        $priority = strtolower($ticket->priority);
        $statusLabel =
            ['open' => 'Open', 'pending' => 'Pending', 'closed' => 'Close'][
                $status
            ] ?? ucfirst($status);
        $ticketNumber = str_pad((string) $ticket->id, 4, '0', STR_PAD_LEFT);
    @endphp

    <section class="mx-auto flex max-w-6xl flex-col gap-8 pb-8" aria-labelledby="ticket-title">
        <nav aria-label="Breadcrumb" class="flex items-center gap-2 text-sm text-slate-500 dark:text-slate-400">
            <a href="{{ route('index') }}"
                class="inline-flex items-center gap-2 rounded-md transition hover:text-blue-600 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-blue-500 dark:hover:text-blue-400">
                <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                    aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m12 19-7-7 7-7M5 12h14" />
                </svg>
                All tickets
            </a>
            <span aria-hidden="true" class="text-slate-300 dark:text-slate-600">/</span>
            <span aria-current="page" class="font-medium text-slate-700 dark:text-slate-300">Ticket
                #{{ $ticketNumber }}</span>
        </nav>

        <header class="flex flex-col items-start justify-between gap-5 sm:flex-row">
            <div class="flex min-w-0 flex-col gap-3">
                <div class="flex flex-wrap items-center gap-2.5">
                     <x-ticket-badge :value="$status" />
                    <x-ticket-badge type="priority" :value="$priority" />
                </div>
                <h1 id="ticket-title"
                    class="max-w-3xl text-3xl font-bold tracking-tight wrap-anywhere text-slate-900 dark:text-slate-100 sm:text-4xl">
                    {{ $ticket->title }}</h1>
                <p class="text-sm leading-6 text-slate-500 dark:text-slate-400">
                    Created {{ $ticket->created_at?->format('M d, Y') }}
                    <span aria-hidden="true" class="px-1">·</span>
                    #{{ $ticketNumber }}
                </p>
            </div>
            {{-- This dropdown saves status immediately through its own PATCH form.
                 The conversation preview selector below only changes the local display. --}}
            @can('updateStatus', $ticket)
            <form method="POST" action="{{ route('ticket.status.update', $ticket) }}"
                class="w-full shrink-0 sm:ml-auto sm:w-auto">
                @csrf
                @method('PATCH')

                <select name="status" aria-label="Ticket status" onchange="this.form.submit()"
                    class="min-h-12 w-full cursor-pointer rounded-xl border border-slate-300 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition-colors hover:border-slate-400 focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-200 dark:hover:border-slate-500 dark:focus:border-blue-400 dark:focus:ring-blue-400/20 sm:min-w-44">
                    <option value="open" @selected($status === 'open')>Open</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="closed" @selected($status === 'closed')>Close</option>
                </select>
                @error('status')
                    <p role="alert" class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
                @enderror
            </form>
            @endcan

        </header>



        {{-- A colon on a component attribute evaluates PHP, passing the actual boolean,
             ticket model rather than literal attribute strings. --}}
        <x-ticket-conversation  :ticket="$ticket" />

        <div class="grid min-w-0 items-start gap-6 lg:grid-cols-[minmax(0,1fr)_19rem]">
            <article
                class="min-w-0 overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <header class="flex items-center gap-3 border-b border-slate-100 px-6 py-5 dark:border-slate-800 sm:px-8">
                    <span
                        class="flex size-9 items-center justify-center rounded-xl bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400">
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M8 7h8M8 11h8M8 15h5M6 3h12a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2Z" />
                        </svg>
                    </span>
                    <div>
                        <h2 class="text-base font-semibold text-slate-900 dark:text-slate-100">Description</h2>
                        <p class="text-xs leading-5 text-slate-500 dark:text-slate-400">The details behind this ticket.</p>
                    </div>
                </header>
                <div class="min-h-64 px-6 py-7 sm:px-8">
                    {{-- The controller supplies plain text derived from the saved editor HTML;
                         escaped Blade output prevents that content from becoming executable markup. --}}
                    @if (filled($descriptionText))
                        <div
                            class="whitespace-pre-wrap text-base leading-8 wrap-anywhere text-slate-700 dark:text-slate-300">
                            {{ $descriptionText }}</div>
                    @else
                        <p class="text-sm italic text-slate-500 dark:text-slate-400">No description has been added to this
                            ticket.</p>
                    @endif
                </div>
                <footer
                    class="flex items-center gap-2 border-t border-slate-100 bg-slate-50/60 px-6 py-4 text-xs text-slate-500 dark:border-slate-800 dark:bg-slate-950/30 dark:text-slate-400 sm:px-8">
                    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                        aria-hidden="true">
                        <circle cx="12" cy="12" r="9" />
                        <path stroke-linecap="round" d="M12 7v5l3 2" />
                    </svg>
                    Last updated {{ $ticket->updated_at?->format('M d, Y \a\t h:i A') }}
                </footer>
            </article>

            <aside class="flex min-w-0 flex-col gap-5" aria-label="Ticket information and actions">
                <section
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm dark:border-slate-800 dark:bg-slate-900"
                    aria-labelledby="ticket-info-title">
                    <div class="border-b border-slate-100 px-5 py-5 dark:border-slate-800">
                        <h2 id="ticket-info-title" class="text-sm font-semibold text-slate-900 dark:text-slate-100">Ticket
                            information</h2>
                    </div>
                    <dl class="flex flex-col gap-5 p-5 text-sm">
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-slate-500 dark:text-slate-400">Ticket ID</dt>
                            <dd class="font-mono font-medium text-slate-800 dark:text-slate-200">#{{ $ticketNumber }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-slate-500 dark:text-slate-400">Priority</dt>
                            <dd><x-ticket-badge type="priority" :value="$priority" /></dd>
                        </div>
                        <div class="flex items-center justify-between gap-4">
                            <dt class="text-slate-500 dark:text-slate-400">Status</dt>
                            <dd class="font-medium text-slate-800 dark:text-slate-200">{{ $statusLabel }}</dd>
                        </div>
                        <div class="flex flex-col gap-2 border-t border-slate-100 pt-5 dark:border-slate-800">
                            <dt class="text-xs font-medium uppercase tracking-wide text-slate-500 dark:text-slate-400">
                                Created on</dt>
                            <dd class="font-medium text-slate-800 dark:text-slate-200">
                                <time
                                    datetime="{{ $ticket->created_at?->toIso8601String() }}">{{ $ticket->created_at?->format('M d, Y \a\t h:i A') }}</time>
                            </dd>
                        </div>
                    </dl>
                </section>

                <section class="rounded-2xl border border-slate-200 bg-white p-5 dark:border-slate-800 dark:bg-slate-900"
                    aria-labelledby="delete-ticket-title">
                    <h2 id="delete-ticket-title" class="text-sm font-semibold text-slate-900 dark:text-slate-100">Delete
                        ticket</h2>
                    <p class="mt-2 text-sm leading-6 text-slate-500 dark:text-slate-400">Permanently remove this ticket.
                        This action cannot be undone.</p>
                    <form class="mt-4" method="POST" action="{{ route('ticket.destroy', $ticket) }}"
                        onsubmit="return confirm('Are you sure you want to delete this ticket?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                            class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-rose-200 bg-rose-50/50 px-4 py-2.5 text-sm font-semibold text-rose-600 transition hover:border-rose-300 hover:bg-rose-100 focus-visible:outline-2 focus-visible:outline-offset-4 focus-visible:outline-rose-500 dark:border-rose-900/60 dark:bg-rose-500/5 dark:text-rose-400 dark:hover:bg-rose-500/10">
                            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M3 6h18M9 6V3h6v3M5 6l1 15h12l1-15M10 10v7m4-7v7" />
                            </svg>
                            Delete Ticket
                        </button>
                    </form>
                </section>
            </aside>
        </div>
    </section>
@endsection
