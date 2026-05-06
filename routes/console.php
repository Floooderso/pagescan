<?php

use App\Models\SavedSearch;
use App\Services\GooglePlacesService;
use App\Models\Company;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function () {
    SavedSearch::where(function ($q) {
        $q->whereNull('last_run_at')
          ->orWhere('last_run_at', '<', now()->subHours(23));
    })->each(function (SavedSearch $search) {
        try {
            $data    = (new GooglePlacesService)->search($search->branche, $search->stadt);
            $results = $data['results'];
            $total   = count($results);
            $newCount = 0;
            foreach ($results as $place) {
                if (!Company::where('place_id', $place['place_id'])->exists()) {
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
            $search->update(['last_run_at' => now(), 'last_result_count' => $total, 'new_leads_count' => $newCount]);
        } catch (\Throwable) {}
    });
})->dailyAt('06:00')->name('watchlist:refresh');
