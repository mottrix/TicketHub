@extends('components.layout')
@section('content')
    <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100 sm:text-4xl">Tickets</h1>
    <p class="mt-2 text-base text-slate-600 dark:text-slate-400">View and manage all tickets</p>

    <section aria-label="Ticket summary" class="mt-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4  ">

        @include('components.summary-card', [
            'title' => 'Total, Tickets',
            'count' => $totalTickets,
            'style' => 'blue',
            'icon' => '🎫',
        ])
        @include('components.summary-card', [
            'title' => 'Open',
            'count' => $openTickets,
            'style' => 'green',
            'icon' => '◉',
        ])
        @include('components.summary-card', [
            'title' => 'Pending',
            'count' => $pendingTickets,
            'style' => 'orange',
            'icon' => '◷',
        ])
        @include('components.summary-card', [
            'title' => 'Close',
            'count' => $closeTickets,
            'style' => 'red',
            'icon' => '✓',
        ])


    </section>

    @include('components.table' )
@endsection
