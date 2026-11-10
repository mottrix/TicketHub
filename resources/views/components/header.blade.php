<header
    class="sticky top-0 z-30 flex h-16 w-full items-center gap-3 border-b border-slate-200 bg-white px-4 transition-colors dark:border-slate-800 dark:bg-slate-900 sm:gap-4 sm:px-6">
    <button type="button" data-sidebar-toggle aria-controls="main-sidebar" aria-expanded="false"
        aria-label="Open navigation menu"
        class="flex size-10 shrink-0 items-center justify-center rounded-lg text-slate-700 transition-colors hover:bg-slate-100 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 dark:text-slate-300 dark:hover:bg-slate-800">
        <svg xmlns="http://www.w3.org/2000/svg" class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor"
            stroke-width="2" stroke-linecap="round" aria-hidden="true">
            <path d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <form role="search" action="{{ route('index') }}" method="GET" class="relative w-full max-w-md">
        @foreach (['status', 'userID', 'table-filter'] as $filter)
            @if (is_scalar(request($filter)) && request()->filled($filter))
                <input type="hidden" name="{{ $filter }}" value="{{ request($filter) }}">
            @endif
        @endforeach
        <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-slate-400"
            xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
            stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <circle cx="11" cy="11" r="8"></circle>
            <path d="m21 21-4.3-4.3"></path>
        </svg>
        <input type="search" name="search" value="{{ is_string(request('search')) ? request('search') : '' }}" maxlength="160" placeholder="Search tickets..." aria-label="Search tickets"
            class="h-10 w-full rounded-lg border border-slate-300 bg-slate-50 py-2 pl-10 pr-3 text-sm text-slate-900 placeholder:text-slate-400 outline-none transition focus:border-blue-500 focus:bg-white focus:ring-2 focus:ring-blue-500/20 dark:border-slate-700 dark:bg-slate-800 dark:text-slate-100 dark:placeholder:text-slate-500 dark:focus:border-blue-400 dark:focus:bg-slate-800 dark:focus:ring-blue-400/20">
    </form>
    <div class="ml-auto flex items-center gap-2 sm:gap-3">
        <button type="button" id="theme-toggle" aria-label="Toggle theme" aria-pressed="false"
            class="flex size-10 cursor-pointer items-center justify-center rounded-full text-slate-600 transition-colors duration-200 hover:bg-slate-100 hover:text-slate-900 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 dark:text-slate-300 dark:hover:bg-slate-800 dark:hover:text-white">
            <span id="moon-icon" aria-hidden="true">🌙</span>
            <span id="sun-icon" class="hidden" aria-hidden="true">☀️</span>
        </button>



        @auth

            <div class="group relative">
                <button type="button" aria-label="User profile"
                    class="flex size-10 shrink-0 cursor-pointer items-center justify-center rounded-full bg-slate-800 text-sm font-semibold text-white transition-colors hover:bg-slate-700 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-blue-600 dark:bg-slate-700 dark:hover:bg-slate-600">
                    <span aria-hidden="true">{{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 2)) }}</span>
                </button>

                <div
                    class="invisible absolute right-0 top-full z-50 w-64 translate-y-1 pt-2 opacity-0 transition duration-150 group-hover:visible group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:visible group-focus-within:translate-y-0 group-focus-within:opacity-100">
                    <div
                        class="rounded-xl border border-slate-200 bg-white p-4 shadow-lg dark:border-slate-700 dark:bg-slate-800">
                        <p class="truncate text-sm font-semibold text-slate-900 dark:text-slate-100">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="mt-1 truncate text-xs text-slate-500 dark:text-slate-400">
                            {{ auth()->user()->email }}
                        </p>
                        @if (auth()->user()->isAdmin())
                            <div class="ml-auto ">
                                <p class="  rounded-2xl w-25 h-10 me-5 mt-5 text-left">admin</p>
                            </div>
                        @endif
                        <div class="my-3 border-t border-slate-200 dark:border-slate-700 dark:text-gray-800"></div>

                        <form action="{{ route('logout.destroy') }}" method="POST">
                            @csrf

                            <button
                                class="h-8 rounded-lg px-4 text-rose-600 hover:bg-rose-50 focus-visible:outline-2 focus-visible:outline-rose-600 dark:text-rose-300 dark:hover:bg-rose-950/60" type="submit">
                                Logout
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endauth
    </div>
</header>
