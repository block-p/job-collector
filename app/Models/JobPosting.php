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

    /**
     * Skills normalized to a flat list of unique non-empty strings.
     *
     * Handles every shape the crawler stores: plain arrays, arrays of
     * objects ({title,titleFa,name}), JSON strings (single or
     * double-encoded), and comma-separated strings.
     *
     * @return array<int, string>
     */
    public function getSkillsListAttribute(): array
    {
        $value = $this->attributes['skills'] ?? null;

        for ($i = 0; $i < 2 && is_string($value); $i++) {
            $decoded = json_decode($value, true);
            $value = json_last_error() === JSON_ERROR_NONE ? $decoded : array_map('trim', explode(',', $value));
        }

        if (! is_array($value)) {
            return [];
        }

        $out = [];
        foreach ($value as $item) {
            if (is_array($item)) {
                $item = $item['title'] ?? $item['titleFa'] ?? $item['name'] ?? null;
            }
            if (is_string($item) || is_numeric($item)) {
                $item = trim((string) $item);
                if ($item !== '') {
                    $out[] = $item;
                }
            }
        }

        return array_values(array_unique($out));
    }
}
