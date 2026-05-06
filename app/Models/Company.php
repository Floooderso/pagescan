<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'place_id', 'name', 'branche', 'stadt', 'plz', 'telefon', 'email',
        'website_url', 'has_website', 'screenshot_path', 'rating', 'bewertungen',
    ];

    protected $casts = [
        'has_website' => 'boolean',
        'rating'      => 'float',
        'bewertungen' => 'integer',
    ];

    public function scans()
    {
        return $this->hasMany(Scan::class);
    }

    public function lead()
    {
        return $this->hasOne(Lead::class);
    }

    public function latestScan()
    {
        return $this->scans()->latest()->first();
    }

    public function scopeFilter($query, array $filters)
    {
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('branche', 'like', "%{$search}%")
                  ->orWhere('stadt', 'like', "%{$search}%")
                  ->orWhere('website_url', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['stadt'])) {
            $query->where('stadt', $filters['stadt']);
        }

        if (!empty($filters['branche'])) {
            $query->where('branche', $filters['branche']);
        }

        if (!empty($filters['websiteFilter'])) {
            match ($filters['websiteFilter']) {
                'ohne'    => $query->where('has_website', false),
                'mit'     => $query->where('has_website', true),
                'schlecht' => $query->whereHas('scans', fn($q) => $q->where('score', '<', 50)->where('status', 'done')),
                default   => null,
            };
        }

        if (isset($filters['has_website']) && $filters['has_website'] !== '') {
            $query->where('has_website', (bool) $filters['has_website']);
        }

        if (!empty($filters['min_score']) || !empty($filters['max_score'])) {
            $query->whereHas('scans', function ($q) use ($filters) {
                $q->where('status', 'done');
                if (!empty($filters['min_score'])) $q->where('score', '>=', (int) $filters['min_score']);
                if (!empty($filters['max_score']))  $q->where('score', '<=', (int) $filters['max_score']);
            });
        }

        if (!empty($filters['cms'])) {
            $query->whereHas('scans', fn($q) => $q->where('cms', $filters['cms']));
        }

        if (!empty($filters['has_ssl'])) {
            $query->whereHas('scans', fn($q) => $q->where('has_ssl', true));
        }

        if (!empty($filters['is_mobile'])) {
            $query->whereHas('scans', fn($q) => $q->where('is_mobile', true));
        }

        return $query;
    }
}
