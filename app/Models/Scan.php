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
        // DSGVO
        'google_fonts_source',
        'cookie_banner_tool',
        'cookie_banner_status',
        'impressum_location',
        'datenschutz_location',
        // Security
        'security_headers_score',
        'security_headers_detail',
        // Outdated tech
        'outdated_tech',
        // Performance
        'ttfb_score',
        'lcp_score',
        'cls_score',
        // robots.txt
        'robots_txt_exists',
        'robots_txt_disallow',
    ];

    protected $casts = [
        'has_ssl'                 => 'boolean',
        'is_mobile'               => 'boolean',
        'has_contact'             => 'boolean',
        'has_impressum'           => 'boolean',
        'has_tracking'            => 'boolean',
        'has_favicon'             => 'boolean',
        'has_og_tags'             => 'boolean',
        'has_google_maps'         => 'boolean',
        'has_cookie_banner'       => 'boolean',
        'alt_tag_ratio'           => 'float',
        'scanned_at'              => 'datetime',
        'security_headers_detail' => 'array',
        'outdated_tech'           => 'array',
        'robots_txt_exists'       => 'boolean',
        'robots_txt_disallow'     => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Risk-Badge Accessor — berechnet das höchste Risiko anhand der Scan-Felder.
     * Gibt zurück: 'critical' | 'warning' | 'ok' | null (wenn kein Scan vorhanden)
     */
    public function getRiskBadgeAttribute(): string
    {
        // Kritisch: kein SSL, kein Impressum, Sicherheits-Score sehr niedrig, veraltete Technologien
        if (
            $this->has_ssl === false ||
            ($this->security_headers_score !== null && $this->security_headers_score <= 1) ||
            ($this->outdated_tech && count($this->outdated_tech) > 0) ||
            $this->impressum_location === 'not_found' ||
            $this->datenschutz_location === 'not_found'
        ) {
            return 'critical';
        }

        // Warnung: kein Cookie-Banner, kein Mobile, kein Kontakt, langsame Performance
        if (
            $this->has_cookie_banner === false ||
            $this->is_mobile === false ||
            $this->has_contact === false ||
            $this->ttfb_score === 'schlecht' ||
            $this->lcp_score === 'schlecht' ||
            ($this->security_headers_score !== null && $this->security_headers_score <= 2)
        ) {
            return 'warning';
        }

        return 'ok';
    }

    /**
     * Hat die Website DSGVO-Probleme (für Filter)?
     */
    public function getHasDsgvoIssuesAttribute(): bool
    {
        return $this->cookie_banner_status === 'not_found'
            || $this->google_fonts_source === 'external'
            || $this->impressum_location === 'not_found'
            || $this->datenschutz_location === 'not_found';
    }

    /**
     * Hat die Website Performance-Probleme (für Filter)?
     */
    public function getHasPerformanceIssuesAttribute(): bool
    {
        return $this->ttfb_score === 'schlecht'
            || $this->lcp_score === 'schlecht'
            || $this->speed_rating === 'langsam';
    }
}
