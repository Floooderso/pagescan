<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavedSearchController;
use App\Http\Controllers\ScoreSettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\WebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');
Route::get('/impressum',    fn() => view('legal.impressum'))->name('impressum');
Route::get('/datenschutz',  fn() => view('legal.datenschutz'))->name('datenschutz');

Route::post('stripe/webhook', [WebhookController::class, 'handleWebhook'])->name('cashier.webhook');

Route::middleware('auth')->group(function () {
    // Immer zugänglich — auch nach Trial-Ablauf
    Route::get('/pricing',                [SubscriptionController::class, 'showPlans'])->name('subscription.plans');
    Route::post('/subscription/checkout', [SubscriptionController::class, 'checkout'])->name('subscription.checkout');
    Route::get('/subscription/success',  [SubscriptionController::class, 'success'])->name('subscription.success');
    Route::get('/subscription/cancel',   [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
    Route::get('/billing',               [SubscriptionController::class, 'billingPortal'])->name('subscription.billing');

    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Nach Trial-Ablauf gesperrt
    Route::middleware('subscription')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/search',    fn() => view('search'))->name('search');

        Route::get('/companies/export',          [CompanyController::class, 'exportCsv'])->name('companies.export');
        Route::get('/companies/{company}',       [CompanyController::class, 'show'])->name('companies.show');
        Route::post('/companies/{company}/scan', [CompanyController::class, 'scan'])->name('companies.scan')->middleware('scan.quota');

        Route::get('/leads/export', [LeadController::class, 'exportCsv'])->name('leads.export');
        Route::get('/leads',        [LeadController::class, 'index'])->name('leads.index');

        Route::get('/watchlist',                  [SavedSearchController::class, 'index'])->name('watchlist.index');
        Route::post('/watchlist',                 [SavedSearchController::class, 'store'])->name('watchlist.store');
        Route::delete('/watchlist/{search}',      [SavedSearchController::class, 'destroy'])->name('watchlist.destroy');
        Route::post('/watchlist/{search}/run',    [SavedSearchController::class, 'run'])->name('watchlist.run');

        Route::get('/settings/score',  [ScoreSettingsController::class, 'show'])->name('settings.score');
        Route::post('/settings/score', [ScoreSettingsController::class, 'update'])->name('settings.score.update');

        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            Route::get('/users',          [AdminUserController::class, 'index'])->name('users.index');
            Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');
        });
    });
});

require __DIR__.'/auth.php';
