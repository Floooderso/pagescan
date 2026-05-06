<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ScanWebsiteJob;
use App\Models\Company;
use App\Models\Scan;
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

        if (auth()->check()) {
            auth()->user()->increment('scans_this_month');
        }

        return response()->json($result);
    }

    public function dispatchScan(Request $request, Company $company)
    {
        if (!$company->website_url) {
            return response()->json(['message' => 'Keine Website-URL vorhanden.'], 422);
        }

        Scan::create(['company_id' => $company->id, 'status' => 'pending']);
        ScanWebsiteJob::dispatch($company->id, auth()->id());

        return response()->json(['message' => 'Scan gestartet.', 'status' => 'pending']);
    }

    public function scanStatus(Request $request)
    {
        $ids = $request->input('company_ids', []);

        if (empty($ids) || !is_array($ids)) {
            return response()->json([]);
        }

        $ids = array_map('intval', $ids);

        $scans = Scan::whereIn('company_id', $ids)
            ->whereIn('id', function ($q) use ($ids) {
                $q->selectRaw('MAX(id)')
                  ->from('scans')
                  ->whereIn('company_id', $ids)
                  ->groupBy('company_id');
            })
            ->get()
            ->keyBy('company_id');

        $result = [];
        foreach ($ids as $id) {
            $scan       = $scans->get($id);
            $result[$id] = $scan ? [
                'status'             => $scan->status,
                'score'              => $scan->score,
                'has_ssl'            => $scan->has_ssl,
                'is_mobile'          => $scan->is_mobile,
                'has_impressum'      => $scan->has_impressum,
                'has_contact'        => $scan->has_contact,
                'has_tracking'       => $scan->has_tracking,
                'has_favicon'        => $scan->has_favicon,
                'cms'                => $scan->cms,
                'speed_rating'       => $scan->speed_rating,
                'error'              => $scan->error,
            ] : ['status' => 'not_found'];
        }

        return response()->json($result);
    }
}
