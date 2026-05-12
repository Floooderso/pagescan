<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WaitlistController extends Controller
{
    /**
     * Show all waitlisted users, grouped by status.
     */
    public function index()
    {
        $waiting  = User::where('waitlist_status', 'waiting')->orderBy('created_at')->get();
        $approved = User::where('waitlist_status', 'approved')->orderByDesc('approved_at')->get();
        $rejected = User::where('waitlist_status', 'rejected')->orderByDesc('updated_at')->get();

        $approvedCount  = $approved->count();
        $freeTierSlots  = User::FREE_TIER_LIMIT;
        $freeTierUsed   = $approvedCount; // approved count is the free-tier counter
        $freeTierLeft   = max(0, $freeTierSlots - $freeTierUsed);

        return view('admin.waitlist.index', compact(
            'waiting', 'approved', 'rejected',
            'approvedCount', 'freeTierSlots', 'freeTierUsed', 'freeTierLeft'
        ));
    }

    /**
     * Approve a waiting user. Assigns free-tier if slots remain.
     */
    public function approve(User $user)
    {
        if ($user->waitlist_status === 'approved') {
            return back()->with('info', "{$user->name} ist bereits freigeschaltet.");
        }

        DB::transaction(function () use ($user) {
            // Re-check free-tier availability inside transaction with lock
            // Use waitlist_status = 'approved' as source of truth (consistent with registration logic)
            $freeTierUsed = User::where('waitlist_status', 'approved')->lockForUpdate()->count();
            $isFree       = $freeTierUsed < User::FREE_TIER_LIMIT;

            $user->update([
                'waitlist_status' => 'approved',
                'is_free_tier'    => $isFree,
                'approved_at'     => now(),
            ]);
        });

        return back()->with('success', "{$user->name} wurde freigeschaltet." . ($user->fresh()->is_free_tier ? ' (Kostenlos-Tier)' : ''));
    }

    /**
     * Reject a waiting or approved user.
     */
    public function reject(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->withErrors(['error' => 'Du kannst dich nicht selbst ablehnen.']);
        }

        $user->update([
            'waitlist_status' => 'rejected',
            'is_free_tier'    => false,
            'approved_at'     => null,
        ]);

        return back()->with('success', "{$user->name} wurde abgelehnt.");
    }

    /**
     * Bulk-approve all waiting users.
     */
    public function approveAll(Request $request)
    {
        $waiting = User::where('waitlist_status', 'waiting')->orderBy('created_at')->get();

        DB::transaction(function () use ($waiting) {
            $freeTierUsed = User::where('waitlist_status', 'approved')->lockForUpdate()->count();

            foreach ($waiting as $user) {
                $isFree = $freeTierUsed < User::FREE_TIER_LIMIT;
                $user->update([
                    'waitlist_status' => 'approved',
                    'is_free_tier'    => $isFree,
                    'approved_at'     => now(),
                ]);
                if ($isFree) {
                    $freeTierUsed++;
                }
            }
        });

        return back()->with('success', "Alle wartenden Nutzer ({$waiting->count()}) wurden freigeschaltet.");
    }
}
