<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Services\WebsiteScanner;
use Illuminate\Http\Request;

class CompanyApiController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only([
            'search', 'stadt', 'branche', 'websiteFilter',
            'has_website', 'min_score', 'max_score', 'cms', 'has_ssl', 'is_mobile',
        ]);

        $companies = Company::with(['scans' => fn($q) => $q->latest()->limit(1)])
            ->filter($filters)
            ->latest()
            ->get()
            ->map(function ($c) {
                $scan = $c->scans->first();
                return [
                    'id'          => $c->id,
                    'name'        => $c->name,
                    'branche'     => $c->branche,
                    'stadt'       => $c->stadt,
                    'website_url' => $c->website_url,
                    'has_website' => $c->has_website,
                    'latest_scan' => $scan ? ['score' => $scan->score, 'speed_rating' => $scan->speed_rating] : null,
                ];
            });

        return response()->json($companies);
    }

    public function filterOptions()
    {
        $staedte  = Company::whereNotNull('stadt')->distinct()->orderBy('stadt')->pluck('stadt');
        $branchen = Company::whereNotNull('branche')->distinct()->orderBy('branche')->pluck('branche');

        return response()->json(compact('staedte', 'branchen'));
    }

    public function scanUrl(Request $request)
    {
        $request->validate(['url' => 'required|string|max:500']);

        $weights = auth()->user()?->getScoreWeights() ?? [];
        $result  = (new WebsiteScanner($weights))->scan($request->url);

        return response()->json($result);
    }
}
