<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ScanWebsiteJob;
use App\Models\Company;
use App\Models\Scan;
use App\Services\GooglePlacesService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
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

        $userId         = auth()->id();
        $enrichedPlaces = [];

        foreach ($places as $place) {
            $company = Company::updateOrCreate(
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

            if ($company->has_website) {
                Scan::create(['company_id' => $company->id, 'status' => 'pending']);
                ScanWebsiteJob::dispatch($company->id, $userId);
            }

            $enrichedPlaces[] = array_merge($place, ['id' => $company->id]);
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
