<?php

namespace Database\Seeders;

use App\Models\PlatformPosting;
use Illuminate\Database\Seeder;

/**
 * Default crawl sources, mirrored from a known-good local setup.
 *
 * Uses firstOrCreate so re-running never clobbers sources already
 * customized through the admin panel.
 */
class PlatformPostingSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->sources() as $source) {
            PlatformPosting::firstOrCreate(
                ['title' => $source['title']],
                $source
            );
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function sources(): array
    {
        return [
            [
                'title' => 'E-Estekhdam',
                'method' => 'POST',
                'url' => 'https://www.e-estekhdam.com',
                'endpoint' => '/search-api/search',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                    'X-Requested-With' => 'XMLHttpRequest',
                ],
                'query_params' => [],
                'body_template' => [
                    'where' => '["تهران"]',
                    'position' => '["برنامه-نویس"]',
                    'contract' => '["تمام-وقت"]',
                ],
                'pagination' => [
                    'type' => 'query',
                    'page_key' => 'page',
                    'start_page' => '1',
                    'max_pages' => '500',
                ],
                'response_mapping' => [
                    'list_path' => 'data',
                    'fields' => [
                        'title' => 'title',
                        'company' => 'brand_name',
                        'location' => 'location',
                        'salary' => 'salary',
                        'url' => 'url',
                        'contract' => 'contract.0',
                        'skills' => 'technologies',
                    ],
                ],
                'delay_ms' => 300,
            ],
            [
                'title' => 'JobVision',
                'method' => 'POST',
                'url' => 'https://jobvision.ir/jobs',
                'endpoint' => 'https://candidateapi.jobvision.ir/api/v1/JobPost/List',
                'headers' => [
                    'Content-Type' => 'application/json',
                    'User-Agent' => 'Mozilla/5.0',
                    'Accept' => 'application/json, text/plain, */*',
                ],
                'query_params' => [],
                'body_template' => [
                    'keyword' => 'برنامه نویس',
                    'pageSize' => '20',
                    'jobCategoryIds' => '[30]',
                    'workTypeIds' => '[120]',
                    'locations' => '[{"provinceId": 17}]',
                ],
                'pagination' => [
                    'type' => 'body',
                    'page_key' => 'requestedPage',
                    'start_page' => '1',
                    'max_pages' => '10',
                ],
                'response_mapping' => [
                    'list_path' => 'data.jobPosts',
                    'fields' => [
                        'title' => 'title',
                        'company' => 'company.nameFa',
                        'location' => 'location.province.titleFa',
                        'salary' => 'salary.titleFa',
                        'url' => 'id',
                        'contract' => 'workType.titleFa',
                        'skills' => 'jobCategories.*.titleFa',
                    ],
                ],
                'delay_ms' => 500,
            ],
            [
                'title' => 'Jobinja',
                'method' => 'GET',
                'url' => 'https://jobinja.ir',
                'endpoint' => '/jobs',
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0',
                ],
                'query_params' => [
                    'filters[keywords][0]' => 'برنامه نویس',
                    'filters[locations][0]' => 'تهران',
                ],
                'body_template' => [],
                'pagination' => [
                    'type' => 'query',
                    'page_key' => 'page',
                    'start_page' => '1',
                    'max_pages' => '10',
                ],
                'response_mapping' => [
                    'list_path' => 'data',
                    'fields' => [
                        'title' => 'title',
                        'company' => 'company',
                        'location' => 'location',
                        'salary' => 'salary',
                        'url' => 'url',
                        'contract' => 'contract',
                        'skills' => 'skills',
                    ],
                ],
                'delay_ms' => 500,
            ],
        ];
    }
}
