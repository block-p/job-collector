<div>
    {{-- Search + filters --}}
    <div class="rounded-xl border border-stone-200 bg-white p-4 shadow-sm sm:p-5">
        <div class="flex flex-col gap-3 lg:flex-row">
            <div class="relative flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-stone-400">⌕</span>
                <input
                    type="search"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Search title, company, or skill…"
                    class="w-full rounded-lg border-stone-300 py-2.5 pl-9 pr-3 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500"
                >
            </div>
            <div class="grid grid-cols-2 gap-3 sm:flex sm:items-center">
                <select wire:model.live="platform" class="rounded-lg border-stone-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">All sources</option>
                    @foreach ($platforms as $platform)
                        <option value="{{ $platform->id }}">{{ $platform->title }}</option>
                    @endforeach
                </select>
                <select wire:model.live="contract" class="rounded-lg border-stone-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">All contracts</option>
                    @foreach ($contracts as $contract)
                        <option value="{{ $contract }}">{{ $contract }}</option>
                    @endforeach
                </select>
                <select wire:model.live="location" class="rounded-lg border-stone-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="">All locations</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location }}">{{ $location }}</option>
                    @endforeach
                </select>
                <select wire:model.live="sort" class="rounded-lg border-stone-300 text-sm shadow-sm focus:border-amber-500 focus:ring-amber-500">
                    <option value="newest">Newest first</option>
                    <option value="oldest">Oldest first</option>
                </select>
            </div>
        </div>
        <div class="mt-3 flex items-center justify-between text-sm">
            <p class="text-stone-500">
                <span class="font-semibold text-stone-800">{{ $jobs->total() }}</span>
                of {{ $total }} {{ Str::plural('job', $total) }} shown
            </p>
            @if ($this->hasActiveFilters)
                <button wire:click="clearFilters" class="font-medium text-red-600 hover:text-red-800">
                    ✕ Clear all filters
                </button>
            @endif
        </div>
    </div>

    {{-- Results --}}
    <div wire:loading.class="opacity-50" class="mt-6 transition-opacity">
        @forelse ($jobs as $job)
            <article class="mb-4 rounded-xl border border-stone-200 bg-white p-4 shadow-sm transition hover:shadow-md sm:p-5">
                <div class="flex flex-wrap items-start justify-between gap-2">
                    <div class="min-w-0">
                        <a href="{{ $job->url }}" target="_blank" rel="noopener"
                           class="text-lg font-bold leading-snug text-stone-900 hover:text-amber-700">
                            {{ $job->title }}
                        </a>
                        <p class="mt-0.5 text-sm text-stone-500">
                            {{ $job->company ?? 'Unknown company' }}
                            @if ($job->platform)
                                <span class="text-stone-300">·</span>
                                <span class="font-medium text-stone-600">{{ $job->platform->title }}</span>
                            @endif
                        </p>
                    </div>
                    <a href="{{ $job->url }}" target="_blank" rel="noopener"
                       class="shrink-0 rounded-lg bg-stone-900 px-3 py-1.5 text-sm font-semibold text-white hover:bg-stone-700">
                        Apply ↗
                    </a>
                </div>

                <div class="mt-3 flex flex-wrap gap-1.5 text-xs">
                    @if ($job->location)
                        <span class="rounded-full bg-sky-100 px-2.5 py-1 font-medium text-sky-800">📍 {{ $job->location }}</span>
                    @endif
                    @if ($job->contract)
                        <span class="rounded-full bg-violet-100 px-2.5 py-1 font-medium text-violet-800">{{ $job->contract }}</span>
                    @endif
                    @if ($job->salary)
                        <span class="rounded-full bg-emerald-100 px-2.5 py-1 font-medium text-emerald-800">💰 {{ $job->salary }}</span>
                    @endif
                    @foreach ($job->skills_list as $index => $skill)
                        @if ($index < 5)
                            <span class="rounded-full bg-amber-100 px-2.5 py-1 font-medium text-amber-800">{{ $skill }}</span>
                        @endif
                    @endforeach
                    @if (count($job->skills_list) > 5)
                        <span class="rounded-full bg-stone-200 px-2.5 py-1 font-medium text-stone-600">+{{ count($job->skills_list) - 5 }} more</span>
                    @endif
                </div>

                <p class="mt-3 text-xs text-stone-400">
                    Collected {{ $job->created_at?->diffForHumans() ?? 'recently' }}
                </p>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-stone-300 bg-white p-12 text-center">
                <p class="text-4xl">🔍</p>
                <h2 class="mt-3 text-lg font-bold">No jobs match your filters</h2>
                <p class="mt-1 text-sm text-stone-500">Try a different keyword or clear the filters to see everything.</p>
                @if ($this->hasActiveFilters)
                    <button wire:click="clearFilters"
                            class="mt-4 rounded-lg bg-stone-900 px-4 py-2 text-sm font-semibold text-white hover:bg-stone-700">
                        Clear all filters
                    </button>
                @endif
            </div>
        @endforelse
    </div>

    <div class="mt-6">
        {{ $jobs->links() }}
    </div>
</div>
