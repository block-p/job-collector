<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobPosting extends Model
{
    protected $guarded = [];

    protected function casts(): array
    {
        return [
            'skills' => 'array',
        ];
    }

    public function platform()
    {
        return $this->belongsTo(PlatformPosting::class, 'platform_id');
    }
}
