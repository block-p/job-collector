<?php

namespace Tests\Feature;

use App\Livewire\JobBoard;
use App\Models\JobPosting;
use App\Models\PlatformPosting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class JobBoardTest extends TestCase
{
    use RefreshDatabase;

    private function seedJobs(): PlatformPosting
    {
        $platform = PlatformPosting::create([
            'title' => 'E-Estekhdam',
            'url' => 'https://www.e-estekhdam.com',
        ]);

        JobPosting::create([
            'platform_id' => $platform->id,
            'title' => 'Backend Developer',
            'company' => 'Acme',
            'location' => 'Tehran',
            'contract' => 'Full-time',
            'url' => 'https://example.com/jobs/1',
            'skills' => ['PHP', 'Laravel'],
        ]);

        JobPosting::create([
            'platform_id' => $platform->id,
            'title' => 'Frontend Developer',
            'company' => 'Globex',
            'location' => 'Remote',
            'contract' => 'Part-time',
            'url' => 'https://example.com/jobs/2',
            'skills' => ['Vue', 'Tailwind'],
        ]);

        return $platform;
    }

    public function test_home_page_lists_all_jobs(): void
    {
        $this->seedJobs();

        $this->get('/')
            ->assertOk()
            ->assertSee('Backend Developer')
            ->assertSee('Frontend Developer');
    }

    public function test_search_filters_jobs(): void
    {
        $this->seedJobs();

        Livewire::test(JobBoard::class)
            ->set('search', 'Backend')
            ->assertSee('Backend Developer')
            ->assertDontSee('Frontend Developer');
    }

    public function test_platform_and_contract_filters(): void
    {
        $platform = $this->seedJobs();

        Livewire::test(JobBoard::class)
            ->set('platform', (string) $platform->id)
            ->set('contract', 'Part-time')
            ->assertSee('Frontend Developer')
            ->assertDontSee('Backend Developer');
    }

    public function test_search_via_query_string(): void
    {
        $this->seedJobs();

        $this->get('/?q=Backend')
            ->assertOk()
            ->assertSee('Backend Developer')
            ->assertDontSee('Frontend Developer');
    }
}
