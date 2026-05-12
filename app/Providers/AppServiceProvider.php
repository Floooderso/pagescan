<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\View\Composers\TrialBannerComposer;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        // Inject waitlist banner data into the main app layout
        View::composer('layouts.app', TrialBannerComposer::class);
    }
}
