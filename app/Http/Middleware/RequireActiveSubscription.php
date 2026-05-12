<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireActiveSubscription
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return $next($request);
        }

        $subscribed = method_exists($user, 'subscribed') && $user->subscribed('default');

        if ($subscribed) {
            return $next($request);
        }

        // Trial still active — allow through
        if ($user->trial_ends_at && $user->trial_ends_at->isFuture()) {
            return $next($request);
        }

        // No trial ever started — allow (free tier, scan quota handled elsewhere)
        if (!$user->trial_ends_at) {
            return $next($request);
        }

        // Trial expired, not subscribed — redirect to waitlist pending (subscription.plans removed)
        return redirect()->route('waitlist.pending')
            ->with('trial_expired', true);
    }
}