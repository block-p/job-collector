<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder
 */
class CrawlLog extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'filters' => 'array',
        ];
    }

    public function platform()
    {
        return $this->belongsTo(PlatformPosting::class, 'platform_id');
    }
}
