@props(['paginator'])

@php
$links = $paginator->linkCollection()->slice(1, -1);
$hasPages = $paginator->hasPages();
$pageName = $paginator->getPageName();
@endphp

@if ($hasPages)
<nav role="navigation" aria-label="ناوبری صفحات" class="mx-auto flex w-full justify-center">
    <ul class="flex flex-row items-center gap-1">
        {{-- Previous --}}
        @if ($paginator->onFirstPage())
            <li>
                <span class="flex h-9 w-9 items-center justify-center rounded-md text-muted-foreground opacity-50" aria-disabled="true">
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </span>
            </li>
        @else
            <li>
                <button type="button"
                        wire:click="previousPage('{{ $pageName }}')"
                        class="flex h-9 w-9 items-center justify-center rounded-md hover:bg-accent hover:text-accent-foreground transition-colors"
                        rel="prev"
                        aria-label="صفحه قبل">
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>
            </li>
        @endif

        {{-- Page Numbers --}}
        @foreach ($links as $link)
            @if ($link['label'] === '...')
                <li wire:key="ellipsis-{{ $loop->index }}">
                    <span class="flex h-9 w-9 items-center justify-center text-muted-foreground text-sm">...</span>
                </li>
            @elseif ($link['active'])
                <li wire:key="page-active-{{ $link['page'] }}">
                    <span aria-current="page" 
                          class="flex h-9 w-9 items-center justify-center rounded-md bg-primary text-primary-foreground text-sm font-medium shadow-sm">
                        {{ $link['label'] }}
                    </span>
                </li>
            @else
                <li wire:key="page-{{ $link['page'] }}">
                    <button type="button"
                            wire:click="gotoPage({{ $link['page'] }}, '{{ $pageName }}')"
                            class="flex h-9 w-9 items-center justify-center rounded-md text-sm font-medium hover:bg-accent hover:text-accent-foreground transition-colors"
                            aria-label="صفحه {{ $link['label'] }}">
                        {{ $link['label'] }}
                    </button>
                </li>
            @endif
        @endforeach

        {{-- Next --}}
        @if ($paginator->hasMorePages())
            <li>
                <button type="button"
                        wire:click="nextPage('{{ $pageName }}')"
                        class="flex h-9 w-9 items-center justify-center rounded-md hover:bg-accent hover:text-accent-foreground transition-colors"
                        rel="next"
                        aria-label="صفحه بعد">
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </li>
        @else
            <li>
                <span class="flex h-9 w-9 items-center justify-center rounded-md text-muted-foreground opacity-50" aria-disabled="true">
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
            </li>
        @endif
    </ul>
</nav>
@endif
