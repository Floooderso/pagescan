<?php

namespace App\Console\Commands;

use App\Jobs\ScanWebsiteJob;
use App\Models\Company;
use App\Models\Scan;
use App\Models\SavedSearch;
use App\Services\GooglePlacesService;
use Illuminate\Console\Command;

class RunWatchlistSearches extends Command
{
    protected $signature   = 'watchlist:run';
    protected $description = 'Führt alle gespeicherten Watchlist-Suchen automatisch aus';

    public function handle(): void
    {
        $searches = SavedSearch::where(function ($q) {
            $q->whereNull('last_run_at')
              ->orWhere('last_run_at', '<', now()->subHours(23));
        })->get();

        $service = new GooglePlacesService;

        foreach ($searches as $search) {
            try {
                $data     = $service->search($search->branche, $search->stadt);
                $results  = $data['results'];
                $total    = count($results);
                $newCount = 0;

                foreach ($results as $place) {
                    if (!Company::where('place_id', $place['place_id'])->exists()) {
                        $newCount++;
                        $company = Company::create([
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

                        // Dispatch a background scan for new companies that have a website
                        if ($company->website_url) {
                            Scan::create(['company_id' => $company->id, 'status' => 'pending']);
                            ScanWebsiteJob::dispatch($company->id, $search->user_id);
                        }
                    }
                }

                $search->update([
                    'last_run_at'       => now(),
                    'last_result_count' => $total,
                    'new_leads_count'   => $newCount,
                ]);
            } catch (\Throwable) {}
        }

        $this->info("Watchlist-Suchen abgeschlossen ({$searches->count()} verarbeitet).");
    }
}
