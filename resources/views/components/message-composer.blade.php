@props(['id' => 'ticket-reply', 'ticket'])

<div
    {{ $attributes->class(['shrink-0 border-t border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900 sm:p-5']) }}>
    <p data-resolved-notice @class([
        'mb-3 text-sm font-medium text-emerald-700 dark:text-emerald-300',
        'hidden' => ! $ticket->isClosed(),
    ])>
        This ticket is resolved. Replies are disabled.
    </p>
    <fieldset data-composer @disabled($ticket->isClosed()) class="group min-w-0 disabled:opacity-60">
        <legend class="sr-only">Reply to the support team</legend>
        @if (! $ticket->isClosed())
            <form method="POST" action="{{ route('ticket.reply.store', $ticket) }}"
                class=" overflow-hidden rounded-xl border border-slate-300 bg-white transition focus-within:border-blue-500 focus-within:ring-2 focus-within:ring-blue-500/15 dark:border-slate-700 dark:bg-slate-900 dark:focus-within:border-blue-400">
                @csrf
                <label for="{{ $id }}" class="sr-only">Your reply</label>

                <textarea name="reply-message" id="{{ $id }}" data-reply rows="3" required maxlength="20000"
                    aria-describedby="{{ $id }}-help"
                    placeholder="{{ $ticket->isClosed() ? 'This ticket is resolved.' : 'Write your reply...' }}"
                    class="block min-h-24 w-full resize-y border-0 bg-transparent px-4 py-3 text-sm leading-6 text-slate-800 caret-blue-600 outline-none placeholder:text-slate-500 disabled:cursor-not-allowed dark:text-slate-100 dark:caret-blue-400 dark:placeholder:text-slate-400">{{ is_string(old('reply-message')) ? old('reply-message') : '' }}</textarea>
                <div class="flex flex-wrap items-center justify-between gap-3 px-3 pb-3">
                    <span class="inline-flex items-center gap-2 px-1 text-xs text-slate-500 dark:text-slate-400"
                        title="Attachment preview only">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="m8 13 6-6a3 3 0 0 1 4 4l-8 8a5 5 0 0 1-7-7l9-9a7 7 0 0 1 10 10l-9 9" />
                        </svg>
                        Attachments <span class="sr-only">(visual preview only)</span>
                    </span>
                    <button type="submit"
                        class="inline-flex min-h-11 items-center justify-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-blue-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-500 disabled:cursor-not-allowed disabled:bg-slate-200 disabled:text-slate-500 dark:disabled:bg-slate-800 dark:disabled:text-slate-400">
                        Send Reply

                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                            aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m5 12 14-7-7 14-2-7-5 0Zm5 0 9-7" />
                        </svg>
                    </button>
                </div>
            </form>
        @endif

    </fieldset>
    @error('reply-message')
        <p role="alert" class="mt-2 text-sm text-rose-600 dark:text-rose-400">{{ $message }}</p>
    @enderror
    <p id="{{ $id }}-help" class="mt-3 text-xs leading-5 text-slate-500 dark:text-slate-400">Replies are
        addressed to the support team and saved to this ticket.</p>
    <p data-reply-feedback role="status" class="mt-1 text-xs font-medium text-blue-700 dark:text-blue-300"></p>
</div>
