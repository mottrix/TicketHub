@props(['name', 'initials', 'time', 'datetime', 'agent' => false,])

<article {{ $attributes->class(['flex min-w-0 items-start gap-2.5 sm:gap-3', 'flex-row-reverse' => !$agent]) }}
    aria-label="{{ $name }}, {{ $agent ? 'Support agent' : 'Customer' }}, {{ $time }}">
    <span @class([
        'flex size-8 shrink-0 items-center justify-center rounded-full text-xs font-semibold sm:size-9',
        'bg-violet-100 text-violet-800 dark:bg-violet-400/15 dark:text-violet-200' => $agent,
        'bg-blue-100 text-blue-800 dark:bg-blue-400/15 dark:text-blue-200' => !$agent,
    ]) aria-hidden="true">{{ $initials }}</span>
    <div class="flex min-w-0 max-w-[88%] flex-col gap-2 sm:max-w-[82%]">
        <div @class([
            'flex flex-wrap items-center gap-x-2 gap-y-1 text-xs',
            'justify-end' => !$agent,
        ])>
            <span class="font-semibold text-slate-800 dark:text-slate-100">{{ $name }}</span>
            <span class="text-slate-500 dark:text-slate-400">{{ $agent ? 'Support agent' : 'Customer' }}</span>
        </div>
        <div @class([
            'rounded-2xl px-4 py-3 text-sm leading-7 wrap-anywhere selection:bg-blue-200 selection:text-blue-950 sm:px-5',
            'rounded-tl-sm bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200' => $agent,
            'rounded-tr-sm bg-blue-50 text-blue-950 dark:bg-blue-950/60 dark:text-blue-100' => !$agent,
        ])>{{ $slot }}</div>
        <time datetime="{{ $datetime }}" @class([
            'text-xs tabular-nums text-slate-500 dark:text-slate-400',
            'text-right' => !$agent,
        ])>{{ $time }}</time>
    </div>
</article>
