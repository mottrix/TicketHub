@props(['resolved' => false, 'ticket', 'messages'])

<section data-ticket-conversation data-ticket-resolved="{{ $resolved ? 'true' : 'false' }}" {{ $attributes->class(['flex min-w-0 flex-col overflow-hidden rounded-2xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900']) }} aria-labelledby="conversation-title">
    <header class="flex flex-wrap items-center justify-between gap-4 border-b border-slate-200 px-4 py-4 dark:border-slate-800 sm:px-6">
        <div class="flex flex-col gap-1">
            <h2 id="conversation-title" class="text-base font-semibold text-slate-900 dark:text-slate-100">Conversation</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400">Messages and replies for this ticket</p>
        </div>
        <div data-preview-controls hidden class="flex items-center gap-2">
            <label for="conversation-state" class="text-xs font-medium text-slate-600 dark:text-slate-300">Preview</label>
            <select id="conversation-state" data-conversation-state class="min-h-11 max-w-full rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm text-slate-700 outline-none focus-visible:border-blue-500 focus-visible:ring-2 focus-visible:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-200">
                <option value="normal" @selected(! $resolved)>Conversation</option>
                <option value="resolved" @selected($resolved)>Resolved</option>
                <option value="empty">Empty</option>
                <option value="loading">Loading</option>
            </select>
        </div>
    </header>

    <div data-conversation-scroll tabindex="0" role="region" aria-label="Ticket messages, scroll to read more" class="h-[min(55dvh,32rem)] min-h-72 overflow-y-auto overscroll-y-contain px-4 py-6 outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-blue-500 sm:px-6">
        <div data-conversation-panel="messages" class="flex flex-col gap-6">
            {{-- <x-conversation-message name="Alex Morgan" initials="AM" time="3:42 PM" datetime="2026-11-06T15:42:00">
                <p>Hi team, I’m having trouble downloading my latest invoice. When I click “Download PDF,” the page keeps loading. Could you help me check this?</p>
            </x-conversation-message>
            <x-conversation-message name="Sarah Chen" initials="SC" time="4:08 PM" datetime="2026-11-06T16:08:00" :agent="true">
                <p>Hi Alex, thanks for the details. Could you let me know which browser you’re using and whether the same thing happens in a private window?</p>
            </x-conversation-message>
            <x-conversation-message name="Alex Morgan" initials="AM" time="4:21 PM" datetime="2026-11-06T16:21:00">
                <div class="flex flex-col gap-3">
                    <p>I’m using Chrome on my laptop. I tried a private window and another browser, but the download still doesn’t finish.</p>
                    <p>The invoice appears correctly in Billing, and I can open previous months without any issues. It seems to affect only the November invoice.</p>
                    <p>I need a copy for our finance team, so I’d appreciate any help. Thank you!</p>
                </div>
            </x-conversation-message>
            <div class="flex items-center gap-3" role="separator" aria-label="November 7, 2026">
                <span class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></span>
                <time datetime="2026-11-07" class="text-xs font-medium text-slate-500 dark:text-slate-400">November 7, 2026</time>
                <span class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></span>
            </div> --}}
            @forelse ($messages as $message)
                @if ($loop->first || $message->created_at->toDateString() !== $messages[$loop->index - 1]->created_at->toDateString())
                    <div class="flex items-center gap-3" role="separator" aria-label="{{ $message->created_at->format('F j, Y') }}">
                        <span class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></span>
                        <time datetime="{{ $message->created_at->toDateString() }}" class="text-xs font-medium text-slate-500 dark:text-slate-400">{{ $message->created_at->format('F j, Y') }}</time>
                        <span class="h-px flex-1 bg-slate-200 dark:bg-slate-800"></span>
                    </div>
                @endif
                <x-conversation-message
                    :name="$message->user?->name ?? 'Unknown User'"
                    :initials="mb_strtoupper(mb_substr($message->user?->name ?? 'U', 0, 2))"
                    :time="$message->created_at->format('g:i A')"
                    :datetime="$message->created_at->toIso8601String()"
                    :agent="$message->user?->isAdmin() ?? false"
                    :message="$message">
                    <div class="flex flex-col gap-3">
                        <p class="whitespace-pre-wrap">{{ $message->message }}</p>
                    </div>
                </x-conversation-message>
                
            @empty
                <p class="py-12 text-center text-sm text-slate-500 dark:text-slate-400">No replies yet. Use the composer below to start the conversation.</p>
            @endforelse


            {{-- <x-conversation-message message={{ $messange }}/> --}}
        </div>

        <div data-conversation-panel="empty" hidden class="flex h-full flex-col items-center justify-center gap-3 text-center">
            <svg class="size-9 text-slate-400 dark:text-slate-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M21 11.5a8.4 8.4 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.4 8.4 0 0 1-3.8-.9L3 21l1.9-5.7a8.4 8.4 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.4 8.4 0 0 1 3.8-.9h.5a8.5 8.5 0 0 1 8 8v.5Z" /></svg>
            <h3 class="text-base font-semibold text-slate-800 dark:text-slate-100">Start the conversation</h3>
            <p class="max-w-xs text-sm leading-6 text-slate-500 dark:text-slate-400">No replies yet. Use the composer below to share more details with the support team.</p>
        </div>

        <div data-conversation-panel="loading" hidden class="flex flex-col gap-8" aria-label="Loading sample messages">
            <span class="sr-only">Loading messages…</span>
            @foreach ([false, true, false] as $outgoing)
                <div @class(['flex items-start gap-3 motion-safe:animate-pulse', 'flex-row-reverse' => $outgoing]) aria-hidden="true">
                    <span class="size-9 shrink-0 rounded-full bg-slate-200 dark:bg-slate-700"></span>
                    <div class="flex w-3/4 flex-col gap-3">
                        <span class="h-3 w-24 rounded bg-slate-200 dark:bg-slate-700"></span>
                        <span class="h-20 rounded-xl bg-slate-100 dark:bg-slate-800"></span>
                        <span class="h-2 w-14 rounded bg-slate-100 dark:bg-slate-800"></span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
    <x-message-composer :resolved="$resolved" :ticket="$ticket" />
</section>
