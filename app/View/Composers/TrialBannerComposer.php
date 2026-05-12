<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

/**
 * Provides the $waitlistBanner variable to app layout views.
 * Previously provided $trialBanner (subscription/trial logic — now removed).
 * Now shows a banner for approved free-tier users explaining their free access,
 * or nothing for regular approved users.
 */
class TrialBannerComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        // Always provide both variables so Blade doesn't throw undefined-var warnings
        $view->with('trialBanner', null);
        $view->with('waitlistBanner', null);

        if (!$user) {
            return;
        }

        // Show a friendly banner for free-tier users
        if ($user->is_free_tier && $user->isApproved()) {
            $view->with('waitlistBanner', [
                'type'    => 'free_tier',
                'message' => 'Du nutzt PageScan kostenlos als einer der ersten Nutzer. Danke!',
            ]);
        }
    }
}
