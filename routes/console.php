<?php

use App\Mail\TrialEndingMail;
use App\Models\SavedSearch;
use App\Models\User;
use App\Services\GooglePlacesService;
use App\Models\Company;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('quota:reset', function () {
    $count = User::where('scans_this_month', '>', 0)->count();
    User::query()->update(['scans_this_month' => 0]);
    $this->info("Scan-Quota für {$count} User zurückgesetzt.");
})->purpose('Reset monthly scan quota for all users');

Artisan::command('mail:trial-reminders', function () {
    $users = User::whereNotNull('trial_ends_at')
        ->whereDate('trial_ends_at', now()->addDays(3)->toDateString())
        ->get();

    foreach ($users as $user) {
        Mail::to($user->email)->queue(new TrialEndingMail($user));
    }

    $this->info("Trial-Reminder an {$users->count()} User versandt.");
})->purpose('Send trial ending reminder emails');

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

Schedule::command('quota:reset')->monthlyOn(1, '00:00')->name('quota:reset');

Schedule::command('mail:trial-reminders')->dailyAt('09:00')->name('mail:trial-reminders');
