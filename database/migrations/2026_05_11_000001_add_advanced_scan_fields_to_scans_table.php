<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            // DSGVO: Google Fonts source
            $table->string('google_fonts_source')->nullable()->after('has_cookie_banner'); // 'local'|'external'|'unknown'

            // DSGVO: Cookie-Banner detail
            $table->string('cookie_banner_tool')->nullable()->after('google_fonts_source'); // Tool-Name oder null
            $table->string('cookie_banner_status')->nullable()->after('cookie_banner_tool'); // 'found'|'not_found'

            // DSGVO: Legal pages
            $table->string('impressum_location')->nullable()->after('cookie_banner_status');   // 'menu'|'footer'|'not_found'
            $table->string('datenschutz_location')->nullable()->after('impressum_location');   // 'menu'|'footer'|'not_found'

            // Security headers
            $table->integer('security_headers_score')->nullable()->after('datenschutz_location'); // 0-4
            $table->json('security_headers_detail')->nullable()->after('security_headers_score');  // array of header => present bool

            // Outdated tech
            $table->json('outdated_tech')->nullable()->after('security_headers_detail'); // array of tech strings

            // Performance scores
            $table->string('ttfb_score')->nullable()->after('outdated_tech');  // 'gut'|'mittel'|'schlecht'
            $table->string('lcp_score')->nullable()->after('ttfb_score');      // 'gut'|'mittel'|'schlecht'
            $table->string('cls_score')->nullable()->after('lcp_score');       // 'gut'|'mittel'|'schlecht'
        });
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropColumn([
                'google_fonts_source',
                'cookie_banner_tool',
                'cookie_banner_status',
                'impressum_location',
                'datenschutz_location',
                'security_headers_score',
                'security_headers_detail',
                'outdated_tech',
                'ttfb_score',
                'lcp_score',
                'cls_score',
            ]);
        });
    }
};
