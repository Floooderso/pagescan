<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
                ], $places), 0, 5),
            ]]);
        }

        return response()->json([
            'companies'       => $places,
            'total'           => count($places),
            'ohne_website'    => count(array_filter($places, fn($p) => !$p['has_website'])),
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
