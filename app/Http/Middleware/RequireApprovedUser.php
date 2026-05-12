<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequireApprovedUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Admins always pass through regardless of waitlist status
        if ($user->isAdmin()) {
            return $next($request);
        }

        if ($user->isApproved()) {
            return $next($request);
        }

        if ($user->isRejected()) {
            return redirect()->route('waitlist.rejected');
        }

        // Still waiting
        return redirect()->route('waitlist.pending');
    }
}
