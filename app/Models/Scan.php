<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Scan extends Model
{
    protected $fillable = [
        'company_id', 'status', 'http_status', 'response_ms',
        'has_ssl', 'is_mobile', 'cms', 'page_title', 'meta_description',
        'has_contact', 'has_impressum', 'has_tracking', 'has_favicon',
        'has_og_tags', 'has_google_maps', 'has_cookie_banner',
        'alt_tag_ratio', 'broken_links_count', 'speed_rating',
        'score', 'error', 'scanned_at',
    ];

    protected $casts = [
        'has_ssl'          => 'boolean',
        'is_mobile'        => 'boolean',
        'has_contact'      => 'boolean',
        'has_impressum'    => 'boolean',
        'has_tracking'     => 'boolean',
        'has_favicon'      => 'boolean',
        'has_og_tags'      => 'boolean',
        'has_google_maps'  => 'boolean',
        'has_cookie_banner'=> 'boolean',
        'alt_tag_ratio'    => 'float',
        'scanned_at'       => 'datetime',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
