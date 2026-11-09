@props(['docTitle' => 'TicketHub'])
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <meta name="color-scheme" content="light dark">
    <script>
        const savedTheme = localStorage.getItem("theme");
        const systemDark = window.matchMedia(
            "(prefers-color-scheme: dark)"
        ).matches;

        document.documentElement.classList.toggle(
            "dark",
            savedTheme ? savedTheme === "dark" : systemDark
        );
    </script>
    @vite(['resources/css/app.css', 'resources/css/create-ticket.css', 'resources/css/edit-ticket.css', 'resources/js/app.js'])
    <title>{{ $docTitle }}</title>
    @if (request()->routeIs('create', 'ticket.edit'))
        @vite('resources/js/edit-ticket.js')
    @endif
</head>

<body
    class="min-h-screen bg-slate-50 text-slate-900 antialiased transition-colors dark:bg-slate-950 dark:text-slate-100">
    <div class="flex min-h-screen">
        <x-sidbar />
        <div class="min-w-0 flex-1">
            <x-header />
            <main class="px-4 py-6 sm:px-6 lg:px-8 bg-slate-50 dark:bg-slate-900">
                @if (session('success'))
                    <div role="status">{{ session('success') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>

</body>

</html>


