<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('scans_this_month')->default(0)->after('score_weights');
            $table->unsignedInteger('scans_limit')->default(10)->after('scans_this_month');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['scans_this_month', 'scans_limit']);
        });
    }
};
