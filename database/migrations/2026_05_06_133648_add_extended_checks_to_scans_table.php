<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->boolean('has_og_tags')->default(false)->after('has_favicon');
            $table->boolean('has_google_maps')->default(false)->after('has_og_tags');
            $table->boolean('has_cookie_banner')->default(false)->after('has_google_maps');
            $table->float('alt_tag_ratio')->nullable()->after('has_cookie_banner');
            $table->integer('broken_links_count')->nullable()->after('alt_tag_ratio');
            $table->string('speed_rating')->nullable()->after('broken_links_count');
        });
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropColumn(['has_og_tags', 'has_google_maps', 'has_cookie_banner', 'alt_tag_ratio', 'broken_links_count', 'speed_rating']);
        });
    }
};
