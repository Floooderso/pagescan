<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        $leads = Lead::where('user_id', $userId)->with(['company', 'scan'])->latest()->get();

        $stats = [
            'gesamt'      => $leads->count(),
            'neu'         => $leads->where('status', 'neu')->count(),
            'kontaktiert' => $leads->where('status', 'kontaktiert')->count(),
            'angebot'     => $leads->where('status', 'angebot')->count(),
            'gewonnen'    => $leads->where('status', 'gewonnen')->count(),
            'verloren'    => $leads->where('status', 'verloren')->count(),
        ];

        $recentLeads = $leads->take(5);
        $lastSearch  = session('last_search');

        $hour = (int) now()->format('H');
        $greeting = match(true) {
            $hour >= 5  && $hour < 11 => 'Guten Morgen',
            $hour >= 11 && $hour < 14 => 'Guten Mittag',
            $hour >= 18 && $hour < 23 => 'Guten Abend',
            default                   => 'Guten Tag',
        };

        $user        = Auth::user();
        // Use the unified effectiveLimit method (null = unlimited)
        $effectiveLimit = User::effectiveLimit($user);
        $scanLimit   = $effectiveLimit ?? 0; // 0 used as "unlimited" in view
        $scanUsed    = (int) ($user->scans_this_month ?? 0);
        $scanPercent = ($scanLimit > 0)
            ? min(100, (int) round($scanUsed / $scanLimit * 100))
            : 0;

        return view('dashboard', compact('stats', 'recentLeads', 'lastSearch', 'greeting', 'scanUsed', 'scanLimit', 'scanPercent'));
    }
}
