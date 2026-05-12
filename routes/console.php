<?php

use App\Mail\TrialEndingMail;
use App\Models\User;
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

Schedule::command('watchlist:run')->dailyAt('06:00')->name('watchlist:run');

Schedule::command('quota:reset')->monthlyOn(1, '00:00')->name('quota:reset');

// 'trials:notify' command removed — use 'mail:trial-reminders' instead
Schedule::command('mail:trial-reminders')->dailyAt('09:00')->name('mail:trial-reminders');
