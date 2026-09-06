<?php

namespace App\Services;

use App\Models\PlatformPosting;
use App\Models\JobPosting;
use App\Models\CrawlLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JobCrawlerService
{
    /**
     * Crawl source with support for dynamic runtime overrides.
     *
     * @param PlatformPosting $source
     * @param array $options Optional runtime overrides (e.g. ['max_pages' => 2, 'body' => ['keyword' => 'Go']])
     * @return void
     */
    public function crawl(PlatformPosting $source, array $options = []): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $startTime = microtime(true);
        $totalCrawled = 0;
        $pagesVisited = 0;
        $seenUrlsInRun = [];

        $allFilters = array_merge(
            $source->body_template ?? [],
            $source->query_params ?? [],
            array_filter($options['body'] ?? [], fn ($v) => $v !== null && $v !== ''),
            array_filter($options['query_params'] ?? [], fn ($v) => $v !== null && $v !== '')
        );

        $crawlLog = CrawlLog::create([
            'platform_id'    => $source->id,
            'platform_title' => $source->title,
            'filters'        => $allFilters,
            'status'         => 'running',
        ]);

        // 1. Pagination with dynamic overrides
        $pagination = $source->pagination ?? [];
        $startPage  = $options['start_page'] ?? $pagination['start_page'] ?? 1;
        $maxPages   = $options['max_pages']  ?? $pagination['max_pages']  ?? 1;
        $pageKey    = $pagination['page_key'] ?? 'page';
        $mapping    = $source->response_mapping ?? [];

        $fullUrl = str_starts_with($source->endpoint, 'http')
            ? $source->endpoint
            : (rtrim($source->url, '/') . '/' . ltrim($source->endpoint, '/'));

        for ($page = $startPage; $page <= $maxPages; $page++) {
            try {
                // 2. Merge database defaults with dynamic runtime overrides
                $headers     = array_merge($source->headers ?? [], $options['headers'] ?? []);
                $body        = array_merge($source->body_template ?? [], array_filter($options['body'] ?? [], fn ($v) => $v !== null && $v !== ''));
                $queryParams = array_merge($source->query_params ?? [], array_filter($options['query_params'] ?? [], fn ($v) => $v !== null && $v !== ''));

                // Normalize JSON array strings into actual PHP arrays for API payload and query parameters
                foreach ($body as $k => $v) {
                    if (is_string($v) && str_starts_with(trim($v), '[') && str_ends_with(trim($v), ']')) {
                        $decoded = json_decode($v, true);
                        if (is_array($decoded)) {
                            $body[$k] = $decoded;
                        }
                    }
                }
                foreach ($queryParams as $k => $v) {
                    if (is_string($v) && str_starts_with(trim($v), '[') && str_ends_with(trim($v), ']')) {
                        $decoded = json_decode($v, true);
                        if (is_array($decoded)) {
                            $queryParams[$k] = $decoded;
                        }
                    }
                }

                // Inject current page into request
                if (($pagination['type'] ?? 'query') === 'body') {
                    $body[$pageKey] = $page;
                } else {
                    $queryParams[$pageKey] = $page;
                }

                // 3. Send HTTP request with auto-retry on network drop/rate-limit
                $client = Http::withHeaders($headers)->timeout(25)->retry(3, 1500, throw: false);

                if (strtoupper($source->method) === 'POST') {
                    $targetUrl = !empty($queryParams) ? $fullUrl . '?' . http_build_query($queryParams) : $fullUrl;
                    $response = $client->post($targetUrl, $body);
                } else {
                    $response = $client->get($fullUrl, $queryParams);
                }

                if (!$response->successful()) {
                    throw new \Exception("Remote server error status: " . $response->status());
                }

                $bodyContent = $response->body();
                $isJson = str_contains($response->header('Content-Type') ?? '', 'json') 
                    || (str_starts_with(trim($bodyContent), '{') || str_starts_with(trim($bodyContent), '['));

                if ($isJson) {
                    $jsonResponse = $response->json();
                    $rawList = data_get($jsonResponse, $mapping['list_path'] ?? 'data', []);
                } else {
                    $rawList = $this->parseHtmlJobList($bodyContent, $source);
                }

                if (empty($rawList)) {
                    break;
                }

                // 4. Transform data via mapping
                $cleanJobs = [];
                foreach ($rawList as $item) {
                    $jobData = [];
                    foreach ($mapping['fields'] ?? [] as $targetField => $sourcePath) {
                        $jobData[$targetField] = data_get($item, $sourcePath);
                    }

                    // Auto-fix relative URLs using $source->url
                    if (!empty($jobData['url']) && !str_starts_with($jobData['url'], 'http')) {
                        $jobData['url'] = rtrim($source->url, '/') . '/' . ltrim($jobData['url'], '/');
                    }

                    // Convert array contract to string if necessary
                    if (isset($jobData['contract']) && is_array($jobData['contract'])) {
                        $jobData['contract'] = implode(', ', $jobData['contract']);
                    }

                    // Format skills into clean JSON array of strings
                    if (isset($jobData['skills'])) {
                        $skills = $jobData['skills'];
                        if (is_string($skills) && str_starts_with(trim($skills), '[')) {
                            $skills = json_decode($skills, true);
                        }
                        if (is_array($skills)) {
                            $cleanSkills = [];
                            foreach ($skills as $s) {
                                if (is_array($s)) {
                                    $cleanSkills[] = $s['title'] ?? $s['titleFa'] ?? $s['name'] ?? reset($s);
                                } elseif (!empty($s)) {
                                    $cleanSkills[] = (string) $s;
                                }
                            }
                            $jobData['skills'] = json_encode(array_values(array_filter($cleanSkills)), JSON_UNESCAPED_UNICODE);
                        } elseif (is_string($skills) && !empty($skills)) {
                            $jobData['skills'] = json_encode(array_map('trim', explode(',', $skills)), JSON_UNESCAPED_UNICODE);
                        }
                    }

                    // Ensure any remaining array fields are safely JSON-encoded for database insertion
                    foreach ($jobData as $col => $val) {
                        if (is_array($val)) {
                            $jobData[$col] = json_encode($val, JSON_UNESCAPED_UNICODE);
                        }
                    }

                    // Match your database column: platform_id
                    $jobData['platform_id'] = $source->id;
                    $jobData['created_at']  = now();
                    $jobData['updated_at']  = now();

                    $cleanJobs[] = $jobData;
                }

                // Check for repeating/duplicate pages (when API reaches end of results)
                $pageUrls = array_column($cleanJobs, 'url');
                $newUrlsOnPage = array_diff($pageUrls, $seenUrlsInRun);

                if (empty($newUrlsOnPage)) {
                    Log::info("End of unique listings reached for [{$source->title}] at page {$page}.");
                    break;
                }

                $seenUrlsInRun = array_merge($seenUrlsInRun, $pageUrls);

                // 5. Bulk upsert by unique url & platform_id
                if (!empty($cleanJobs)) {
                    JobPosting::toBase()->upsert($cleanJobs, ['url', 'platform_id']);
                    $totalCrawled += count($newUrlsOnPage);
                }

                $pagesVisited++;

                $source->update([
                    'last_status'     => 'success',
                    'last_error'      => null,
                    'last_crawled_at' => now(),
                ]);

                $crawlLog->update([
                    'status'      => 'success',
                    'jobs_count'  => $totalCrawled,
                    'pages_count' => $pagesVisited,
                    'duration_ms' => (int) round((microtime(true) - $startTime) * 1000),
                ]);

                usleep(($source->delay_ms ?? 1000) * 1000);

            } catch (\Exception $e) {
                $source->update([
                    'last_status' => 'failed',
                    'last_error'  => $e->getMessage(),
                ]);

                $crawlLog->update([
                    'status'      => 'failed',
                    'error'       => $e->getMessage(),
                    'duration_ms' => (int) round((microtime(true) - $startTime) * 1000),
                ]);

                Log::error("Crawler error [{$source->title}]: " . $e->getMessage());
                break;
            }
        }

        // Final completion state
        if ($crawlLog->status === 'running' || $crawlLog->status === 'success') {
            $crawlLog->update([
                'status'      => 'success',
                'jobs_count'  => $totalCrawled,
                'pages_count' => $pagesVisited,
                'duration_ms' => (int) round((microtime(true) - $startTime) * 1000),
            ]);
            $source->update([
                'last_status'     => 'success',
                'last_error'      => null,
                'last_crawled_at' => now(),
            ]);
        }
    }

    /**
     * Parse HTML job listings (for SSR platforms like Jobinja).
     */
    private function parseHtmlJobList(string $html, PlatformPosting $source): array
    {
        $doc = new \DOMDocument();
        @$doc->loadHTML('<?xml encoding="UTF-8">' . $html);
        $xpath = new \DOMXPath($doc);

        $nodes = $xpath->query("//li[contains(@class, 'c-jobListView__item')]");
        $jobs = [];

        foreach ($nodes as $node) {
            $titleNode = $xpath->query(".//a[contains(@class, 'c-jobListView__titleLink')]", $node)->item(0);
            $title = $titleNode ? trim(preg_replace('/\s+/', ' ', $titleNode->textContent)) : '';
            $url = $titleNode ? $titleNode->getAttribute('href') : '';

            if ($url) {
                $urlParts = explode('?', $url);
                $url = $urlParts[0];
            }

            $metaSpans = $xpath->query(".//li[contains(@class, 'c-jobListView__metaItem')]//span", $node);
            $company = $metaSpans->length > 0 ? trim(preg_replace('/\s+/', ' ', $metaSpans->item(0)->textContent)) : '';
            $location = $metaSpans->length > 1 ? trim(preg_replace('/\s+/', ' ', $metaSpans->item(1)->textContent)) : '';
            $contract = $metaSpans->length > 2 ? trim(preg_replace('/\s+/', ' ', $metaSpans->item(2)->textContent)) : '';

            $tagNodes = $xpath->query(".//span[contains(@class, 'c-tag')] | .//a[contains(@class, 'c-tag')] | .//ul[contains(@class, 'c-jobListView__tags')]//li", $node);
            $tags = [];
            foreach ($tagNodes as $tagNode) {
                $t = trim(preg_replace('/\s+/', ' ', $tagNode->textContent));
                if ($t && !in_array($t, $tags)) {
                    $tags[] = $t;
                }
            }

            if ($title && $url) {
                $jobs[] = [
                    'title'    => $title,
                    'company'  => $company,
                    'location' => $location,
                    'salary'   => null,
                    'url'      => $url,
                    'contract' => $contract,
                    'skills'   => $tags,
                ];
            }
        }

        return $jobs;
    }
}
