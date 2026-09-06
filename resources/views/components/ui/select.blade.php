<div {{ $attributes->only('class')->merge(['class' => 'relative']) }}>
    <select {{ $attributes->except('class')->merge(['class' => 'flex h-9 w-full appearance-none items-center justify-between rounded-md border border-input bg-transparent py-2 pe-8 ps-3 text-sm shadow-sm focus:outline-none focus:ring-1 focus:ring-ring disabled:cursor-not-allowed disabled:opacity-50 [&>option]:bg-background']) }}>{{ $slot }}</select>
    <svg class="pointer-events-none absolute end-3 top-1/2 h-4 w-4 -translate-y-1/2 opacity-50" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
    </svg>
</div>
