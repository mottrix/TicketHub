
@props([
    'title',
    'count',
    'style' => 'blue',
    'icon' => '🎫',
])

@php
    $colors = [
        'blue' => [
            'border' => 'border-blue-100 dark:border-blue-900/70',
            'background' => 'bg-blue-50/70 dark:bg-blue-950/40',
            'text' => 'text-blue-700 dark:text-blue-300',
            'shadow' => 'hover:shadow-blue-200/70',
            'iconBg' => 'bg-blue-100 dark:bg-blue-900/70',
        ],
        'green' => [
            'background' => 'bg-emerald-50/70 dark:bg-emerald-950/40',
            'border' => 'border-emerald-100 dark:border-emerald-900/70',
            'text' => 'text-emerald-700 dark:text-emerald-300',
            'shadow' => 'hover:shadow-emerald-200/70',
            'iconBg' => 'bg-emerald-100 dark:bg-emerald-900/70',
        ],
        'orange' => [
            'background' => 'bg-amber-50/70 dark:bg-amber-950/40',
            'border' => 'border-amber-100 dark:border-amber-900/70',
            'text' => 'text-amber-700 dark:text-amber-300',
            'shadow' => 'hover:shadow-amber-200/70',
            'iconBg' => 'bg-amber-100 dark:bg-amber-900/70',
        ],
        'red' => [
            'background' => 'bg-rose-50/70 dark:bg-rose-950/40',
            'border' => 'border-rose-100 dark:border-rose-900/70',
            'text' => 'text-rose-700 dark:text-rose-300',
            'shadow' => 'hover:shadow-rose-200/30',
            'iconBg' => 'bg-rose-100 dark:bg-rose-900/70',
        ],
    ];

    $color = $colors[$style] ?? $colors['blue'];
@endphp

<div class="group box-border flex min-w-0 items-center justify-between rounded-xl border p-4
            {{ $color['border'] }} {{ $color['background'] }}
            transition-all duration-300 ease-out hover:-translate-y-1 hover:shadow-lg {{ $color['shadow'] }}">

    <div class="flex flex-col gap-2">

        <span class="text-sm font-medium {{ $color['text'] }}">
            {{ $title }}
        </span>

        <span class="text-2xl font-bold text-slate-800 dark:text-slate-100">
            {{ $count }}
        </span>

    </div>

    <div class="flex h-10 w-10 items-center justify-center
                rounded-xl {{ $color['iconBg'] }} text-lg transition-transform duration-300 group-hover:scale-110">

        {{ $icon }}

    </div>

</div>
