@props(['variant' => 'default'])

@php
$classes = \Illuminate\Support\Arr::toCssClasses([
    'inline-flex items-center gap-1 rounded-md border px-2.5 py-0.5 text-xs font-semibold transition-colors',
    'focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2',
    '[&_svg]:size-3',
    match ($variant) {
        'secondary' => 'border-transparent bg-secondary text-secondary-foreground hover:bg-secondary/80',
        'destructive' => 'border-transparent bg-destructive text-white shadow hover:bg-destructive/80',
        'outline' => 'text-foreground',
        default => 'border-transparent bg-primary text-primary-foreground shadow hover:bg-primary/80',
    },
]);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>{{ $slot }}</span>
