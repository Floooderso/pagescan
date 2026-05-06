<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckScanQuota
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        if (!$user) {
            return $next($request);
        }

        $limit = $this->effectiveLimit($user);

        if ($limit !== null && $user->scans_this_month >= $limit) {
            return response()->json([
                'message'     => 'Scan-Limit erreicht. Bitte upgraden.',
                'upgrade_url' => route('subscription.plans'),
            ], 429);
        }

        return $next($request);
    }

    private function effectiveLimit(\App\Models\User $user): ?int
    {
        if (method_exists($user, 'subscribed') && $user->subscribed()) {
            // 0 = unlimited plan
            return $user->scans_limit === 0 ? null : $user->scans_limit;
        }

        return 10;
    }
}
