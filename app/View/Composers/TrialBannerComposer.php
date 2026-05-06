<?php

namespace App\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class TrialBannerComposer
{
    public function compose(View $view): void
    {
        $user = Auth::user();

        if (!$user) {
            $view->with('trialBanner', null);
            return;
        }

        $subscribed = method_exists($user, 'subscribed') && $user->subscribed('default');

        if ($subscribed) {
            $view->with('trialBanner', null);
            return;
        }

        $trialEndsAt = $user->trial_ends_at;

        if (!$trialEndsAt) {
            $view->with('trialBanner', null);
            return;
        }

        if ($trialEndsAt->isFuture()) {
            $daysLeft = (int) now()->diffInDays($trialEndsAt, false);
            $view->with('trialBanner', [
                'type'    => 'active',
                'days'    => $daysLeft,
                'message' => $daysLeft <= 1
                    ? 'Dein Trial endet heute — jetzt upgraden um den Zugang zu behalten.'
                    : "Noch {$daysLeft} Tage in deinem Trial.",
            ]);
        } else {
            $view->with('trialBanner', [
                'type'    => 'expired',
                'message' => 'Dein 14-tagiger Trial ist abgelaufen.',
            ]);
        }
    }
}