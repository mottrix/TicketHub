<button type="button" data-sidebar-overlay aria-label="Close navigation menu"
    class="fixed inset-0 z-40 hidden bg-slate-950/50 md:hidden"></button>

<aside id="main-sidebar"
    class="fixed inset-y-0 left-0 z-50 flex h-dvh w-64 flex-col border-r border-slate-800 bg-slate-900 px-4 py-5 text-white shadow-xl transition-transform duration-200 md:sticky md:top-0 md:h-screen md:w-64 md:max-w-none md:shadow-none">
    <div class="flex items-center justify-between gap-3">
        <div class="flex min-w-0 items-center gap-3 cursor-pointer">
            <img src="{{ asset('images/logo.png') }}" alt="TicketHub logo" class="h-10 w-10 shrink-0 object-contain">
            <span class="truncate text-lg font-semibold tracking-wide">TicketHub</span>
        </div>
        {{-- close sidebar button --}}
        <button type="button" data-sidebar-close aria-label="Close navigation menu"
            class="flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-lg text-slate-300 transition-colors hover:bg-slate-800 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
            <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                <path d="m18 6-12 12M6 6l12 12"></path>
            </svg>
        </button>
    </div>

    <nav aria-label="Main navigation" class="mt-6 flex flex-col gap-2">
        <a href="{{ route('index') }}"
             data-sidebar-my
             -ticket class="rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-800 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
            My Tickets
        </a>
        <a href="{{ route('create') }}"
            data-sidebar-create-ticket class="rounded-lg px-3 py-2 text-sm font-medium text-slate-300 transition-colors hover:bg-slate-800 hover:text-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-white">
            + Add Tickets
        </a>
    </nav>
</aside>
