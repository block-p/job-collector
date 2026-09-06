@props(['orientation' => 'horizontal'])

<div {{ $attributes->merge(['class' => \Illuminate\Support\Arr::toCssClasses([
    'shrink-0 bg-border',
    $orientation === 'vertical' ? 'h-full w-[1px]' : 'h-[1px] w-full',
])]) }} role="separator"></div>
