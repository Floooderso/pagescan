<?php

use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\WaitlistController as AdminWaitlistController;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SavedSearchController;
use App\Http\Controllers\ScoreSettingsController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return view('welcome');
})->name('home');

Route::get('/impressum',   fn() => view('legal.impressum'))->name('impressum');
Route::get('/datenschutz', fn() => view('legal.datenschutz'))->name('datenschutz');
Route::get('/sitemap.xml', fn() => response(view('sitemap'), 200)->header('Content-Type', 'application/xml'))->name('sitemap');
Route::get('/robots.txt',  fn() => response(view('robots'), 200)->header('Content-Type', 'text/plain'))->name('robots');

Route::middleware('auth')->group(function () {

    // ─── Profile (always accessible) ─────────────────────────────────────────
    Route::get('/profile',    [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile',  [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ─── Waitlist status pages (always accessible for logged-in users) ────────
    Route::get('/waitlist/pending',  fn() => view('waitlist.pending'))->name('waitlist.pending');
    Route::get('/waitlist/rejected', fn() => view('waitlist.rejected'))->name('waitlist.rejected');

    // ─── Approved users only ──────────────────────────────────────────────────
    Route::middleware('approved')->group(function () {

        // Onboarding-Banner Dismissal
        Route::post('/onboarding/dismiss', function () {
            session(['onboarding_dismissed' => true]);
            return redirect()->route('dashboard');
        })->name('onboarding.dismiss');

        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/search',    fn() => view('search'))->name('search');

        Route::get('/companies/export',          [CompanyController::class, 'exportCsv'])->name('companies.export');
        Route::get('/companies/{company}',       [CompanyController::class, 'show'])->name('companies.show');
        Route::post('/companies/{company}/scan', [CompanyController::class, 'scan'])->name('companies.scan')->middleware(['scan.quota', 'throttle:30,1']);

        Route::get('/leads/export',            [LeadController::class, 'exportCsv'])->name('leads.export');
        Route::post('/leads/export-pdf-batch', [LeadController::class, 'exportPdfBatch'])->name('leads.export.pdf.batch');
        Route::get('/leads/{lead}/pdf',        [LeadController::class, 'exportPdf'])->name('leads.export.pdf');
        Route::get('/leads',                   [LeadController::class, 'index'])->name('leads.index');

        Route::get('/watchlist',               [SavedSearchController::class, 'index'])->name('watchlist.index');
        Route::post('/watchlist',              [SavedSearchController::class, 'store'])->name('watchlist.store');
        Route::delete('/watchlist/{search}',   [SavedSearchController::class, 'destroy'])->name('watchlist.destroy');
        Route::post('/watchlist/{search}/run', [SavedSearchController::class, 'run'])->name('watchlist.run');

        Route::get('/settings/score',  [ScoreSettingsController::class, 'show'])->name('settings.score');
        Route::post('/settings/score', [ScoreSettingsController::class, 'update'])->name('settings.score.update');

        // ─── Admin ─────────────────────────────────────────────────────────────
        Route::middleware('admin')->prefix('admin')->name('admin.')->group(function () {
            // User roles
            Route::get('/users',          [AdminUserController::class, 'index'])->name('users.index');
            Route::patch('/users/{user}', [AdminUserController::class, 'update'])->name('users.update');

            // Waitlist management
            Route::get('/waitlist',                 [AdminWaitlistController::class, 'index'])->name('waitlist.index');
            Route::post('/waitlist/{user}/approve', [AdminWaitlistController::class, 'approve'])->name('waitlist.approve');
            Route::post('/waitlist/{user}/reject',  [AdminWaitlistController::class, 'reject'])->name('waitlist.reject');
            Route::post('/waitlist/approve-all',    [AdminWaitlistController::class, 'approveAll'])->name('waitlist.approve-all');
        });
    });
});

Route::get('/auth/{provider}',          [SocialiteController::class, 'redirect'])->name('socialite.redirect')->where('provider', 'github|google');
Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])->name('socialite.callback')->where('provider', 'github|google');

require __DIR__ . '/auth.php';
