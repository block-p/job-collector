<?php

namespace App\Jobs;

use App\Models\PlatformPosting;
use App\Services\JobCrawlerService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class FetchJobsFromApi implements ShouldQueue
{
    use Queueable;

    /**
     * Timeout for long-running crawling processes (1 hour).
     */
    public int $timeout = 3600;
    public int $tries = 1;
     public function __construct(
        public PlatformPosting $source,
        public array $options = []
     )
    {

    }

    /**
     * Execute the job.
     */
    public function handle(JobCrawlerService $crawler): void
    {
        $crawler->crawl($this->source, $this->options);

    }
}
