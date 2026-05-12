<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Scan;
use App\Models\User;
use App\Services\GooglePlacesService;
use App\Services\WebsiteScanner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    // A "done" scan is considered fresh if scanned within this many seconds.
    private const FRESH_SCAN_TTL = 604800; // 7 days

    public function search(Request $request)
    {
        $request->validate([
            'branche'    => 'required|string|max:100',
            'stadt'      => 'required|string|max:100',
            'page_token' => 'nullable|string',
        ]);

        $branche   = trim($request->input('branche'));
        $stadt     = trim($request->input('stadt'));
        $pageToken = $request->input('page_token');

        $data   = (new GooglePlacesService)->search($branche, $stadt, $pageToken);
        $places = $data['results'];
        $token  = $data['next_page_token'];

        $userId = auth()->id();
        $user   = $userId ? User::find($userId) : null;

        // Calculate how many new scans this user can still start in this request.
        // null means unlimited — no cap applied.
        $remainingQuota = null;
        if ($user) {
            $limit = User::effectiveLimit($user);
            if ($limit !== null) {
                $remainingQuota = max(0, $limit - $user->scans_this_month);
            }
        }

        $enrichedPlaces = [];
        $newScanCount   = 0;

        // Collect companies first, then batch-load their latest scans to avoid N+1.
        $companies = [];
        foreach ($places as $place) {
            $companies[] = Company::updateOrCreate(
                ['place_id' => $place['place_id']],
                [
                    'name'        => $place['name'],
                    'branche'     => $place['branche'],
                    'stadt'       => $place['stadt'],
                    'telefon'     => $place['telefon'] ?? null,
                    'website_url' => $place['website_url'] ?? null,
                    'has_website' => (bool) ($place['has_website'] ?? false),
                    'rating'      => $place['rating'] ?? null,
                    'bewertungen' => $place['bewertungen'] ?? null,
                ]
            );
        }

        $companyIds = collect($companies)->pluck('id')->all();

        // Load the single latest scan per company in one query.
        $latestScans = Scan::whereIn('company_id', $companyIds)
            ->whereIn('id', function ($q) use ($companyIds) {
                $q->selectRaw('MAX(id)')->from('scans')
                  ->whereIn('company_id', $companyIds)
                  ->groupBy('company_id');
            })
            ->get()
            ->keyBy('company_id');

        // Collect companies that need a fresh scan (no recent done scan).
        $toScan = [];
        foreach ($companies as $company) {
            if (!$company->has_website || !$company->website_url) continue;
            $latest = $latestScans->get($company->id);
            if ($latest && $latest->status === 'done'
                && $latest->scanned_at
                && $latest->scanned_at->timestamp >= now()->subSeconds(self::FRESH_SCAN_TTL)->timestamp
            ) {
                continue; // already fresh
            }
            if ($remainingQuota !== null && $newScanCount >= $remainingQuota) continue;
            $toScan[$company->id] = $company->website_url;
            $newScanCount++;
        }

        // Run all pending scans concurrently in one batch request.
        $batchResults = [];
        if (!empty($toScan)) {
            $weights      = $user?->getScoreWeights() ?? [];
            $batchResults = (new WebsiteScanner($weights))->scanBatch($toScan);

            // Persist scan results and increment counters.
            foreach ($batchResults as $companyId => $result) {
                Scan::where('company_id', $companyId)
                    ->whereIn('status', ['pending', 'running'])
                    ->delete(); // clean up any stale pending scans

                $scan = Scan::create(array_merge(['company_id' => $companyId], $result));
                $batchResults[$companyId]['id'] = $scan->id;
            }

            if ($user && $newScanCount > 0) {
                $user->increment('scans_this_month', count($batchResults));
            }
        }

        foreach ($places as $i => $place) {
            $company    = $companies[$i];
            $scanStatus = null;
            $scanData   = null;

            if ($company->has_website) {
                $latest = $latestScans->get($company->id);
                if (isset($batchResults[$company->id])) {
                    $scanStatus = $batchResults[$company->id]['status'] === 'done' ? 'done' : 'failed';
                    $scanData   = $batchResults[$company->id];
                } elseif ($latest && $latest->status === 'done') {
                    $scanStatus = 'done';
                    $scanData   = $latest->toArray();
                } else {
                    $scanStatus = $latest?->status ?? null;
                }
            }

            $enrichedPlaces[] = array_merge($place, [
                'id'          => $company->id,
                'scan_status' => $scanStatus,
                'scan'        => $scanData,
            ]);
        }

        if (!$pageToken) {
            session(['last_search' => [
                'branche'  => $branche,
                'stadt'    => $stadt,
                'at'       => now()->format('d.m.Y H:i'),
                'previews' => array_slice(array_map(fn($p) => [
                    'name'        => $p['name'],
                    'adresse'     => $p['adresse'],
                    'has_website' => $p['has_website'],
                    'website_url' => $p['website_url'],
                    'rating'      => $p['rating'],
                    'bewertungen' => $p['bewertungen'],
                ], $enrichedPlaces), 0, 5),
            ]]);
        }

        return response()->json([
            'companies'       => $enrichedPlaces,
            'total'           => count($enrichedPlaces),
            'ohne_website'    => count(array_filter($enrichedPlaces, fn($p) => !$p['has_website'])),
            'next_page_token' => $token,
        ]);
    }

    /**
     * Decide whether to create+dispatch a new scan or reuse an existing one.
     * Uses a DB transaction + lock to prevent duplicate scans under concurrent requests.
     *
     * Returns [scan_status_string, scan_data_array_or_null].
     * $newScanCount is passed by reference so the caller can track quota usage.
     */
    private function resolveOrCreateScan(
        Company  $company,
        ?int     $userId,
        ?int     $remainingQuota,
        int      &$newScanCount,
        ?Scan    $latestScan
    ): array {
        // Fast-path: use pre-loaded scan to skip the transaction for the common cases.
        if ($latestScan) {
            if (in_array($latestScan->status, ['pending', 'running'], true)) {
                $frontendStatus = $latestScan->status === 'running' ? 'scanning' : 'pending';
                return [$frontendStatus, null];
            }
            if ($latestScan->status === 'done'
                && $latestScan->scanned_at
                && $latestScan->scanned_at->timestamp >= now()->subSeconds(self::FRESH_SCAN_TTL)->timestamp
            ) {
                return ['done', $latestScan->toArray()];
            }
        }

        // Check quota before taking any lock.
        if ($remainingQuota !== null && $newScanCount >= $remainingQuota) {
            return [null, null];
        }

        // Atomic check-then-insert: acquire a row-level lock so concurrent requests
        // for the same company cannot both pass the "no active scan" check.
        $dispatched = DB::transaction(function () use ($company, $userId) {
            $active = Scan::where('company_id', $company->id)
                ->whereIn('status', ['pending', 'running'])
                ->lockForUpdate()
                ->exists();

            if ($active) {
                return false;
            }

            Scan::create(['company_id' => $company->id, 'status' => 'pending']);
            return true;
        });

        if (!$dispatched) {
            // Another concurrent request just created a scan; report it as pending.
            return ['pending', null];
        }

        ScanWebsiteJob::dispatchAfterResponse($company->id, $userId);
        $newScanCount++;

        return ['pending', null];
    }

    public function cities(Request $request)
    {
        $q = trim($request->query('q', ''));

        if (strlen($q) < 2) {
            return response()->json([]);
        }

        $cacheKey = 'city_ac_' . strtolower($q);
        $cities   = Cache::remember($cacheKey, 3600, fn() =>
            (new GooglePlacesService)->autocompleteCity($q)
        );

        return response()->json($cities);
    }
}
