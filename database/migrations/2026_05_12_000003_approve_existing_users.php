<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Approve all users that existed before the waitlist system was introduced.
     * This prevents locking out users who registered before the waitlist rollout.
     */
    public function up(): void
    {
        // Set all users with the default 'waiting' status to 'approved'
        // since they pre-existed the waitlist feature.
        DB::table('users')
            ->where('waitlist_status', 'waiting')
            ->update([
                'waitlist_status' => 'approved',
                'is_free_tier'    => true,
                'approved_at'     => now(),
            ]);
    }

    public function down(): void
    {
        // Non-reversible data migration — intentionally a no-op on rollback
    }
};
