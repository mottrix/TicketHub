@props(['value', 'type' => 'status'])

@php
    if ($type === 'status') {
        $value = ['in_progress' => 'pending', 'close' => 'closed', 'resolved' => 'closed'][$value] ?? $value;
    }

    $label =
        $type === 'status'
            ? ['open' => 'Open', 'pending' => 'Pending', 'closed' => 'Close'][$value] ??
                ucfirst($value)
            : ucfirst($value) . ' priority';
    $color =
        $type === 'priority'
            ? ['high' => 'rose', 'medium' => 'amber', 'low' => 'slate'][$value] ?? 'slate'
            : ['open' => 'blue', 'pending' => 'amber', 'closed' => 'emerald'][$value] ??
                'slate';
@endphp

<span
    {{ $attributes->class([
        'inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold ring-1 ring-inset',
        'bg-blue-50 text-blue-700 ring-blue-600/15 dark:bg-blue-500/10 dark:text-blue-300 dark:ring-blue-400/20' =>
            $color === 'blue',
        'bg-amber-50 text-amber-800 ring-amber-600/15 dark:bg-amber-500/10 dark:text-amber-300 dark:ring-amber-400/20' =>
            $color === 'amber',
        'bg-rose-50 text-rose-700 ring-rose-600/15 dark:bg-rose-500/10 dark:text-rose-300 dark:ring-rose-400/20' =>
            $color === 'rose',
        'bg-emerald-50 text-emerald-700 ring-emerald-600/15 dark:bg-emerald-500/10 dark:text-emerald-300 dark:ring-emerald-400/20' =>
            $color === 'emerald',
        'bg-slate-100 text-slate-600 ring-slate-500/15 dark:bg-slate-800 dark:text-slate-300 dark:ring-slate-600' =>
            $color === 'slate',
    ]) }}>
    <span class="size-1.5 shrink-0 rounded-full bg-current" aria-hidden="true"></span>
    {{ $label }}
</span>
