<?php

namespace Tests\Feature;

use App\Console\Commands\RunWatchlistSearches;
use App\Http\Controllers\SavedSearchController;
use App\Jobs\ScanWebsiteJob;
use App\Models\Company;
use App\Models\Scan;
use App\Models\SavedSearch;
use App\Models\User;
use App\Services\GooglePlacesService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Tests for Watchlist scan dispatching, user_id assignment, and quota enforcement.
 *
 * Critical bugs under test:
 *   BUG-1: Scan::create() missing user_id in RunWatchlistSearches AND SavedSearchController::run()
 *   BUG-2: Quota-bypass — ScanWebsiteJob increments scans_this_month but watchlist-triggered
 *           scans bypass the CheckScanQuota middleware entirely.
 */
class WatchlistScanTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Build a fake Google Places result for a single company. */
    private function fakePlaceResult(array $overrides = []): array
    {
        return array_merge([
            'place_id'    => 'fake_place_' . uniqid(),
            'name'        => 'Test GmbH',
            'adresse'     => 'Musterstraße 1',
            'stadt'       => 'Berlin',
            'branche'     => 'Webdesign',
            'telefon'     => null,
            'website_url' => 'https://example.com',
            'has_website' => true,
            'rating'      => 4.5,
            'bewertungen' => 42,
        ], $overrides);
    }

    /** Create a User with a given scan quota state. */
    private function userWithQuota(int $limit, int $usedThisMonth = 0): User
    {
        return User::factory()->create([
            'scans_limit'      => $limit,
            'scans_this_month' => $usedThisMonth,
        ]);
    }

    /** Mock GooglePlacesService to return given place results. */
    private function mockGooglePlaces(array $places): void
    {
        $mock = $this->createMock(GooglePlacesService::class);
        $mock->method('search')->willReturn(['results' => $places]);
        $this->app->instance(GooglePlacesService::class, $mock);
    }

    // =========================================================================
    // A) RunWatchlistSearches — Artisan Command
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * ScanWebsiteJob must be dispatched when a new company with a website is found.
     */
    public function should_dispatch_scan_job_when_new_company_with_website_is_found_by_watchlist_command(): void
    {
        Queue::fake();

        $user   = User::factory()->create();
        $search = SavedSearch::create([
            'user_id' => $user->id,
            'name'    => 'Test Suche',
            'branche' => 'Webdesign',
            'stadt'   => 'Berlin',
        ]);

        $this->mockGooglePlaces([$this->fakePlaceResult()]);

        $this->artisan('watchlist:run')->assertSuccessful();

        Queue::assertDispatched(ScanWebsiteJob::class);
    }

    /**
     * @test
     * EXPECTED: PASS
     * The dispatched ScanWebsiteJob must carry the correct user_id from the SavedSearch owner.
     * This verifies the job itself is parameterised correctly — the scan credit
     * will be attributed to the right user inside ScanWebsiteJob::handle().
     */
    public function should_dispatch_scan_job_with_correct_user_id_from_saved_search(): void
    {
        Queue::fake();

        $user   = User::factory()->create();
        $search = SavedSearch::create([
            'user_id' => $user->id,
            'name'    => 'Test Suche',
            'branche' => 'Webdesign',
            'stadt'   => 'Berlin',
        ]);

        $place = $this->fakePlaceResult(['place_id' => 'place_user_id_check']);
        $this->mockGooglePlaces([$place]);

        $this->artisan('watchlist:run')->assertSuccessful();

        Queue::assertDispatched(ScanWebsiteJob::class, function (ScanWebsiteJob $job) use ($user) {
            return $job->userId === $user->id;
        });
    }

    /**
     * @test
     * EXPECTED: FAIL (BUG-1) — Scan::create() does NOT include user_id.
     *
     * The Scan model's $fillable array does not contain 'user_id' and the
     * create() call in RunWatchlistSearches passes no user_id at all.
     * If a user_id column existed on scans, this would silently store NULL.
     * This test documents the missing field; it will only pass after the bug is fixed.
     *
     * Note: Because the current Scan schema has no user_id column, this test
     * checks a DB assertion that would be relevant once the column is added.
     * As written it asserts that the pending Scan record CAN be linked back to
     * the originating user — it will fail until both the migration and the
     * create() call are updated.
     */
    public function should_store_user_id_on_scan_record_created_by_watchlist_command(): void
    {
        Queue::fake();

        $user   = User::factory()->create();
        $search = SavedSearch::create([
            'user_id' => $user->id,
            'name'    => 'Test Suche',
            'branche' => 'Webdesign',
            'stadt'   => 'Berlin',
        ]);

        $place = $this->fakePlaceResult(['place_id' => 'place_scan_user_id']);
        $this->mockGooglePlaces([$place]);

        $this->artisan('watchlist:run')->assertSuccessful();

        // BUG: user_id is not in Scan::$fillable and not passed to Scan::create()
        // This assertion will fail until the bug is fixed.
        $this->assertDatabaseHas('scans', [
            'status'  => 'pending',
            'user_id' => $user->id,   // <-- missing in current implementation
        ]);
    }

    /**
     * @test
     * EXPECTED: PASS
     * Companies that have no website must NOT trigger a ScanWebsiteJob.
     */
    public function should_not_dispatch_scan_job_when_new_company_has_no_website(): void
    {
        Queue::fake();

        $user   = User::factory()->create();
        SavedSearch::create([
            'user_id' => $user->id,
            'name'    => 'Test Suche',
            'branche' => 'Webdesign',
            'stadt'   => 'Berlin',
        ]);

        $place = $this->fakePlaceResult([
            'website_url' => null,
            'has_website' => false,
        ]);
        $this->mockGooglePlaces([$place]);

        $this->artisan('watchlist:run')->assertSuccessful();

        Queue::assertNotDispatched(ScanWebsiteJob::class);
    }

    /**
     * @test
     * EXPECTED: PASS
     * A company that already exists (same place_id) must NOT trigger a new job.
     */
    public function should_not_dispatch_scan_job_for_already_existing_company(): void
    {
        Queue::fake();

        $user   = User::factory()->create();
        $search = SavedSearch::create([
            'user_id' => $user->id,
            'name'    => 'Test Suche',
            'branche' => 'Webdesign',
            'stadt'   => 'Berlin',
        ]);

        $place = $this->fakePlaceResult(['place_id' => 'existing_place_001']);

        // Pre-create the company so it "already exists"
        Company::create([
            'place_id'    => 'existing_place_001',
            'name'        => 'Existing GmbH',
            'stadt'       => 'Berlin',
            'branche'     => 'Webdesign',
            'website_url' => 'https://existing.com',
            'has_website' => true,
        ]);

        $this->mockGooglePlaces([$place]);

        $this->artisan('watchlist:run')->assertSuccessful();

        Queue::assertNotDispatched(ScanWebsiteJob::class);
    }

    /**
     * @test
     * EXPECTED: PASS
     * last_run_at must be updated after a successful watchlist run.
     */
    public function should_update_last_run_at_after_successful_watchlist_run(): void
    {
        Queue::fake();

        $user   = User::factory()->create();
        $search = SavedSearch::create([
            'user_id' => $user->id,
            'name'    => 'Test Suche',
            'branche' => 'Webdesign',
            'stadt'   => 'Berlin',
        ]);

        $this->mockGooglePlaces([]);

        $this->artisan('watchlist:run')->assertSuccessful();

        $this->assertNotNull($search->fresh()->last_run_at);
    }

    /**
     * @test
     * EXPECTED: PASS
     * Searches last run less than 23 hours ago must be skipped (not re-run).
     */
    public function should_skip_search_that_was_run_less_than_23_hours_ago(): void
    {
        Queue::fake();

        $user   = User::factory()->create();
        SavedSearch::create([
            'user_id'     => $user->id,
            'name'        => 'Frische Suche',
            'branche'     => 'Webdesign',
            'stadt'       => 'Berlin',
            'last_run_at' => now()->subHours(5),
        ]);

        // GooglePlacesService must never be called for a skipped search
        $mock = $this->createMock(GooglePlacesService::class);
        $mock->expects($this->never())->method('search');
        $this->app->instance(GooglePlacesService::class, $mock);

        $this->artisan('watchlist:run')->assertSuccessful();

        Queue::assertNotDispatched(ScanWebsiteJob::class);
    }

    // =========================================================================
    // B) SavedSearchController::run() — Manual watchlist run via HTTP
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * A POST to /watchlist/{search}/run must dispatch ScanWebsiteJob for new companies.
     */
    public function should_dispatch_scan_job_when_run_endpoint_finds_new_company_with_website(): void
    {
        Queue::fake();

        $user   = $this->userWithQuota(10, 0);
        $search = SavedSearch::create([
            'user_id' => $user->id,
            'name'    => 'Test Suche',
            'branche' => 'Webdesign',
            'stadt'   => 'Berlin',
        ]);

        $this->mockGooglePlaces([$this->fakePlaceResult(['place_id' => 'manual_run_place'])]);

        $response = $this->actingAs($user)
            ->postJson("/watchlist/{$search->id}/run");

        $response->assertOk();
        Queue::assertDispatched(ScanWebsiteJob::class);
    }

    /**
     * @test
     * EXPECTED: PASS
     * The dispatched job from the manual run endpoint must carry the authenticated user's id.
     */
    public function should_dispatch_scan_job_with_authenticated_user_id_from_run_endpoint(): void
    {
        Queue::fake();

        $user   = $this->userWithQuota(10, 0);
        $search = SavedSearch::create([
            'user_id' => $user->id,
            'name'    => 'Test Suche',
            'branche' => 'Webdesign',
            'stadt'   => 'Berlin',
        ]);

        $this->mockGooglePlaces([$this->fakePlaceResult(['place_id' => 'manual_user_id_check'])]);

        $this->actingAs($user)->postJson("/watchlist/{$search->id}/run");

        Queue::assertDispatched(ScanWebsiteJob::class, function (ScanWebsiteJob $job) use ($user) {
            return $job->userId === $user->id;
        });
    }

    /**
     * @test
     * EXPECTED: FAIL (BUG-1) — Same as the command: Scan::create() missing user_id.
     *
     * SavedSearchController::run() calls Scan::create(['company_id' => ..., 'status' => 'pending'])
     * without a user_id. This test will fail until the bug is fixed and a user_id
     * column/fillable exists on the Scan model.
     */
    public function should_store_user_id_on_scan_record_created_by_run_endpoint(): void
    {
        Queue::fake();

        $user   = $this->userWithQuota(10, 0);
        $search = SavedSearch::create([
            'user_id' => $user->id,
            'name'    => 'Test Suche',
            'branche' => 'Webdesign',
            'stadt'   => 'Berlin',
        ]);

        $this->mockGooglePlaces([$this->fakePlaceResult(['place_id' => 'scan_user_id_run'])]);

        $this->actingAs($user)->postJson("/watchlist/{$search->id}/run");

        // BUG: Scan::create() in SavedSearchController does not include user_id
        $this->assertDatabaseHas('scans', [
            'status'  => 'pending',
            'user_id' => $user->id,
        ]);
    }

    /**
     * @test
     * EXPECTED: PASS
     * A different user must not be able to run another user's saved search (403).
     */
    public function should_return_403_when_user_tries_to_run_another_users_saved_search(): void
    {
        Queue::fake();

        $owner  = User::factory()->create();
        $other  = User::factory()->create();
        $search = SavedSearch::create([
            'user_id' => $owner->id,
            'name'    => 'Owner Suche',
            'branche' => 'IT',
            'stadt'   => 'Hamburg',
        ]);

        $response = $this->actingAs($other)
            ->postJson("/watchlist/{$search->id}/run");

        $response->assertForbidden();
        Queue::assertNotDispatched(ScanWebsiteJob::class);
    }

    /**
     * @test
     * EXPECTED: PASS
     * An unauthenticated request to the run endpoint must redirect to login.
     */
    public function should_redirect_unauthenticated_user_when_calling_run_endpoint(): void
    {
        $search = SavedSearch::create([
            'user_id' => User::factory()->create()->id,
            'name'    => 'Test',
            'branche' => 'IT',
            'stadt'   => 'Berlin',
        ]);

        $this->postJson("/watchlist/{$search->id}/run")
            ->assertUnauthorized();
    }

    // =========================================================================
    // C) Quota-Bypass — Automatic scans from ScanWebsiteJob ignore scan limits
    // =========================================================================

    /**
     * @test
     * EXPECTED: FAIL (BUG-2) — Quota-bypass via watchlist-triggered jobs.
     *
     * The CheckScanQuota middleware only guards /companies/{company}/scan.
     * When RunWatchlistSearches dispatches ScanWebsiteJob directly, there is
     * NO quota check. With QUEUE_CONNECTION=sync (test env), the job runs
     * immediately and calls $user->increment('scans_this_month') even when
     * the user is already at their limit.
     *
     * This test verifies that a user at their limit (scans_this_month == limit)
     * does NOT get their counter incremented further by an automatic watchlist scan.
     * It will FAIL until a quota guard is added inside ScanWebsiteJob::handle().
     */
    public function should_not_increment_scans_this_month_when_user_is_at_quota_limit_via_watchlist_job(): void
    {
        // Use sync queue so the job executes immediately inside the test
        config(['queue.default' => 'sync']);

        $limit = 10;
        $user  = $this->userWithQuota($limit, $limit); // already at limit

        $search = SavedSearch::create([
            'user_id' => $user->id,
            'name'    => 'Quota Test',
            'branche' => 'IT',
            'stadt'   => 'Berlin',
        ]);

        $place = $this->fakePlaceResult(['place_id' => 'quota_bypass_place']);
        $this->mockGooglePlaces([$place]);

        // Mock WebsiteScanner so the job does not make real HTTP calls
        $scannerMock = $this->createMock(\App\Services\WebsiteScanner::class);
        $scannerMock->method('scan')->willReturn([
            'status'     => 'done',
            'http_status' => 200,
            'score'      => 80,
        ]);
        $this->app->instance(\App\Services\WebsiteScanner::class, $scannerMock);

        $this->artisan('watchlist:run')->assertSuccessful();

        // BUG: ScanWebsiteJob always increments regardless of quota
        $this->assertSame(
            $limit,
            $user->fresh()->scans_this_month,
            'scans_this_month must not exceed the quota limit — quota-bypass via watchlist job detected'
        );
    }

    /**
     * @test
     * EXPECTED: FAIL (BUG-2) — Same quota-bypass through the HTTP run endpoint.
     *
     * SavedSearchController::run() dispatches ScanWebsiteJob without checking
     * whether the user still has remaining scan quota. A user at their limit can
     * trigger unlimited scans via the watchlist run endpoint.
     *
     * Note: The route IS protected by the 'subscription' middleware group but NOT
     * by 'scan.quota'. This test verifies the expected behaviour after the fix:
     * the controller (or job) must check the quota before processing.
     */
    public function should_not_dispatch_scan_job_when_user_has_exhausted_quota_via_run_endpoint(): void
    {
        Queue::fake();

        $limit = 5;
        $user  = $this->userWithQuota($limit, $limit); // already at limit

        $search = SavedSearch::create([
            'user_id' => $user->id,
            'name'    => 'Quota HTTP Test',
            'branche' => 'IT',
            'stadt'   => 'Berlin',
        ]);

        $this->mockGooglePlaces([$this->fakePlaceResult(['place_id' => 'quota_http_place'])]);

        $response = $this->actingAs($user)
            ->postJson("/watchlist/{$search->id}/run");

        // BUG: No quota check exists in SavedSearchController::run().
        // After the fix, this should return 429 and not dispatch the job.
        $response->assertStatus(429);
        Queue::assertNotDispatched(ScanWebsiteJob::class);
    }
}
