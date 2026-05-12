<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add waitlist fields to users table.
     * waitlist_status: 'waiting' | 'approved' | 'rejected'
     * is_free_tier: true when approved among the first 25 users
     * approved_at: timestamp when admin approved the user
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('waitlist_status')->default('waiting')->after('role');
            $table->boolean('is_free_tier')->default(false)->after('waitlist_status');
            $table->timestamp('approved_at')->nullable()->after('is_free_tier');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['waitlist_status', 'is_free_tier', 'approved_at']);
        });
    }
};
