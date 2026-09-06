<div>
    <div class="mb-6">
        <h1 class="text-2xl font-semibold tracking-tight">آگهی‌های شغلی</h1>
        <p class="mt-1 text-sm text-muted-foreground">
            {{ $total }} آگهی از {{ $platforms->count() }} منبع
            @if ($latest)
                · تازه‌ترین {{ \Carbon\Carbon::parse($latest)->locale('fa')->diffForHumans() }}
            @endif
        </p>
    </div>

    <x-ui.card>
        {{-- Toolbar --}}
        <div class="flex flex-col gap-3 border-b p-4 sm:p-6 lg:flex-row lg:items-center">
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute start-2.5 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <x-ui.input
                    type="search"
                    wire:model.live.debounce.400ms="search"
                    placeholder="جستجو در عنوان، شرکت یا مهارت…"
                    autocomplete="off"
                    class="ps-9"
                />
            </div>
            <div class="grid grid-cols-2 gap-2 sm:flex sm:items-center">
                <x-ui.select wire:model.live="contract" class="sm:w-40">
                    <option value="">هر نوع قرارداد</option>
                    @foreach ($contracts as $contract)
                        <option value="{{ $contract }}">{{ $contract }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select wire:model.live="location" class="sm:w-40">
                    <option value="">هر موقعیت</option>
                    @foreach ($locations as $location)
                        <option value="{{ $location }}">{{ $location }}</option>
                    @endforeach
                </x-ui.select>
                <x-ui.select wire:model.live="sort" class="sm:w-40">
                    <option value="newest">جدیدترین اول</option>
                    <option value="oldest">قدیمی‌ترین اول</option>
                </x-ui.select>
                @if ($this->hasActiveFilters)
                    <x-ui.button variant="outline" wire:click="clearFilters">
                        پاک کردن فیلترها
                    </x-ui.button>
                @endif
            </div>
        </div>

        {{-- Sources --}}
        <div class="flex flex-wrap items-center gap-2 border-b bg-muted/40 px-4 py-3 sm:px-6">
            <x-ui.button
                size="sm"
                :variant="$platform === '' ? 'default' : 'outline'"
                wire:click="$set('platform', '')"
            >
                همه منابع · {{ $total }}
            </x-ui.button>
            @foreach ($platforms as $source)
                <x-ui.button
                    size="sm"
                    :variant="(string) $platform === (string) $source->id ? 'default' : 'outline'"
                    wire:click="$set('platform', '{{ $source->id }}')"
                >
                    {{ $source->title }} · {{ $source->job_postings_count }}
                </x-ui.button>
            @endforeach
        </div>

        {{-- Loading skeletons --}}
        <div wire:loading class="space-y-4 p-4 sm:p-6">
            @for ($i = 0; $i < 3; $i++)
                <div class="space-y-2">
                    <x-ui.skeleton class="h-4 w-1/2" />
                    <x-ui.skeleton class="h-3 w-1/3" />
                    <div class="flex gap-1.5">
                        <x-ui.skeleton class="h-5 w-16" />
                        <x-ui.skeleton class="h-5 w-20" />
                        <x-ui.skeleton class="h-5 w-14" />
                    </div>
                </div>
            @endfor
        </div>

        {{-- Rows --}}
        <div wire:loading.remove class="divide-y divide-border">
            @forelse ($jobs as $job)
                <div class="flex flex-wrap items-center justify-between gap-3 px-4 py-4 transition-colors hover:bg-muted/40 sm:px-6">
                    <div class="min-w-0 flex-1">
                        <a href="{{ $job->url }}" target="_blank" rel="noopener"
                           class="text-sm font-medium leading-none hover:underline">
                            {{ $job->title }}
                        </a>
                        <p class="mt-1.5 text-[13px] text-muted-foreground">
                            <span class="font-medium text-foreground">{{ $job->company ?? 'شرکت نامشخص' }}</span>
                            @if ($job->platform)
                                · {{ $job->platform->title }}
                            @endif
                            · {{ $job->created_at?->locale('fa')->diffForHumans() ?? 'اخیراً' }}
                        </p>
                        <div class="mt-2 flex flex-wrap items-center gap-1.5">
                            @if ($job->location)
                                <x-ui.badge variant="secondary">{{ $job->location }}</x-ui.badge>
                            @endif
                            @if ($job->contract)
                                <x-ui.badge variant="secondary">{{ $job->contract }}</x-ui.badge>
                            @endif
                            @if ($job->salary)
                                <x-ui.badge variant="secondary">{{ $job->salary }}</x-ui.badge>
                            @endif
                            @foreach ($job->skills_list as $index => $skill)
                                @if ($index < 5)
                                    <x-ui.badge variant="outline">{{ $skill }}</x-ui.badge>
                                @endif
                            @endforeach
                            @if (count($job->skills_list) > 5)
                                <x-ui.badge variant="secondary">+{{ count($job->skills_list) - 5 }} مورد دیگر</x-ui.badge>
                            @endif
                        </div>
                    </div>
                    <x-ui.button size="sm" href="{{ $job->url }}" target="_blank" rel="noopener">
                        مشاهده آگهی
                        <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                        </svg>
                    </x-ui.button>
                </div>
            @empty
                <div class="flex flex-col items-center px-6 py-16 text-center">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-muted">
                        <svg class="h-6 w-6 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                        </svg>
                    </span>
                    <h3 class="mt-4 text-sm font-semibold">آگهی‌ای پیدا نشد</h3>
                    <p class="mt-1 max-w-sm text-sm text-muted-foreground">با این ترکیب چیزی پیدا نشد. عبارت دیگری را امتحان کن یا فیلترها را پاک کن.</p>
                    @if ($this->hasActiveFilters)
                        <x-ui.button variant="outline" wire:click="clearFilters" class="mt-4">
                            پاک کردن همه فیلترها
                        </x-ui.button>
                    @endif
                </div>
            @endforelse
        </div>

        {{-- Footer --}}
        <div class="border-t px-4 py-3 sm:px-6">
            @if ($jobs->hasPages())
                <x-ui.pagination :paginator="$jobs" />
            @else
                <div class="text-xs text-muted-foreground text-center">
                    {{ $jobs->total() }} نتیجه
                </div>
            @endif
        </div>
    </x-ui.card>
</div>
