<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformPosting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'headers'          => 'array',
            'query_params'     => 'array',
            'body_template'    => 'array',
            'pagination'       => 'array',
            'response_mapping' => 'array',
            'last_crawled_at'  => 'datetime',
        ];
    }
}
