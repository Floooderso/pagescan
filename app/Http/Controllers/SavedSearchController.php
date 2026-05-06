<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\SavedSearch;
use App\Services\GooglePlacesService;
use Illuminate\Http\Request;

class SavedSearchController extends Controller
{
    public function index()
    {
        $searches = SavedSearch::where('user_id', auth()->id())->latest()->get();
        return view('watchlist.index', compact('searches'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'    => 'required|string|max:100',
            'branche' => 'required|string|max:100',
            'stadt'   => 'required|string|max:100',
        ]);

        $count = SavedSearch::where('user_id', auth()->id())->count();
        if ($count >= 20) {
            return response()->json(['message' => 'Maximal 20 gespeicherte Suchen erlaubt.'], 422);
        }

        $search = SavedSearch::create(array_merge($data, ['user_id' => auth()->id()]));

        if ($request->expectsJson()) {
            return response()->json($search, 201);
        }

        return back()->with('success', 'Suche gespeichert.');
    }

    public function destroy(SavedSearch $search)
    {
        if ($search->user_id !== auth()->id()) {
            abort(403);
        }

        $search->delete();

        return back()->with('success', 'Suche gelöscht.');
    }

    public function run(SavedSearch $search)
    {
        if ($search->user_id !== auth()->id()) {
            abort(403);
        }

        $data    = (new GooglePlacesService)->search($search->branche, $search->stadt);
        $results = $data['results'];
        $total   = count($results);

        $newCount = 0;
        foreach ($results as $place) {
            $exists = Company::where('place_id', $place['place_id'])->exists();
            if (!$exists) {
                $newCount++;
                Company::create([
                    'place_id'    => $place['place_id'],
                    'name'        => $place['name'],
                    'adresse'     => $place['adresse'] ?? null,
                    'stadt'       => $place['stadt'],
                    'branche'     => $place['branche'],
                    'telefon'     => $place['telefon'] ?? null,
                    'website_url' => $place['website_url'] ?? null,
                    'has_website' => $place['has_website'] ?? false,
                    'rating'      => $place['rating'] ?? null,
                    'bewertungen' => $place['bewertungen'] ?? null,
                ]);
            }
        }

        $search->update([
            'last_run_at'       => now(),
            'last_result_count' => $total,
            'new_leads_count'   => $newCount,
        ]);

        return response()->json([
            'run_at'    => now()->format('d.m.Y H:i'),
            'total'     => $total,
            'new_count' => $newCount,
        ]);
    }
}
