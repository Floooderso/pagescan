<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add robots.txt analysis fields to scans table.
     * robots_txt_exists:   Whether /robots.txt was found (HTTP 200)
     * robots_txt_disallow: Whether robots.txt has a Disallow: / for all agents
     */
    public function up(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->boolean('robots_txt_exists')->nullable()->after('cls_score');
            $table->boolean('robots_txt_disallow')->nullable()->after('robots_txt_exists');
        });
    }

    public function down(): void
    {
        Schema::table('scans', function (Blueprint $table) {
            $table->dropColumn(['robots_txt_exists', 'robots_txt_disallow']);
        });
    }
};
