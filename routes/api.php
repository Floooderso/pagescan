<?php

use App\Http\Controllers\Api\CompanyApiController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\ContactAttemptController;
use App\Http\Controllers\LeadController;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'auth'])->group(function () {
    Route::post('/search',         [SearchController::class,     'search'])->middleware(['throttle:30,1', 'scan.quota']);
    Route::get('/cities',          [SearchController::class,     'cities']);
    Route::post('/scan-url',       [CompanyApiController::class, 'scanUrl'])->middleware(['throttle:60,1', 'scan.quota']);
    Route::post('/url-lead',       [CompanyApiController::class, 'saveUrlLead']);
    Route::get('/companies',       [CompanyApiController::class, 'index']);
    Route::get('/filter-options',  [CompanyApiController::class, 'filterOptions']);
    Route::get('/scans/status',    [CompanyApiController::class, 'scanStatus']);
    Route::post('/companies/{company}/scan', [CompanyApiController::class, 'dispatchScan'])->middleware('scan.quota');
    Route::get('/leads',     [LeadController::class,       'apiIndex']);
    Route::post('/leads',    [LeadController::class,       'store']);
    Route::put('/leads/{lead}',    [LeadController::class, 'update']);
    Route::delete('/leads/{lead}', [LeadController::class, 'destroy']);
    Route::post('/leads/{lead}/contact-attempts',             [ContactAttemptController::class, 'store']);
    Route::delete('/leads/{lead}/contact-attempts/{attempt}', [ContactAttemptController::class, 'destroy']);
    Route::post('/leads/{lead}/assign', [LeadController::class, 'assign']);
    Route::get('/users', fn() => User::orderBy('name')->get(['id', 'name']));
});
