<?php

namespace App\Livewire;

use App\Models\JobPosting;
use App\Models\PlatformPosting;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class JobBoard extends Component
{
    use WithPagination;

    #[Url(as: 'q')]
    public string $search = '';

    #[Url]
    public string $platform = '';

    #[Url]
    public string $contract = '';

    #[Url]
    public string $location = '';

    #[Url]
    public string $sort = 'newest';

    public function updated(string $property): void
    {
        if (in_array($property, ['search', 'platform', 'contract', 'location', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function clearFilters(): void
    {
        $this->reset(['search', 'platform', 'contract', 'location', 'sort']);
        $this->resetPage();
    }

    public function getHasActiveFiltersProperty(): bool
    {
        return $this->search !== ''
            || $this->platform !== ''
            || $this->contract !== ''
            || $this->location !== '';
    }

    public function render(): View
    {
        $query = JobPosting::query()->with('platform');

        if ($this->search !== '') {
            $term = '%'.$this->search.'%';
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', $term)
                    ->orWhere('company', 'like', $term)
                    ->orWhere('skills', 'like', $term);
            });
        }

        if ($this->platform !== '') {
            $query->where('platform_id', $this->platform);
        }

        if ($this->contract !== '') {
            $query->where('contract', $this->contract);
        }

        if ($this->location !== '') {
            $query->where('location', $this->location);
        }

        $query->orderBy('created_at', $this->sort === 'oldest' ? 'asc' : 'desc');

        return view('livewire.job-board', [
            'jobs' => $query->paginate(12),
            'platforms' => PlatformPosting::orderBy('title')->get(['id', 'title']),
            'contracts' => JobPosting::query()
                ->whereNotNull('contract')->where('contract', '!=', '')
                ->distinct()->orderBy('contract')->pluck('contract'),
            'locations' => JobPosting::query()
                ->whereNotNull('location')->where('location', '!=', '')
                ->distinct()->orderBy('location')->pluck('location'),
            'total' => JobPosting::count(),
        ]);
    }
}
