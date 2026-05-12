<?php

namespace App\Http\Middleware;

use App\Models\User;
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

        $limit = User::effectiveLimit($user);

        if ($limit !== null && $user->scans_this_month >= $limit) {
            return response()->json([
                'message' => 'Scan-Limit erreicht. Bitte kontaktiere uns für mehr Kapazität.',
            ], 429);
        }

        return $next($request);
    }
}
