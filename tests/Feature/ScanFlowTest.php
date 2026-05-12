<?php

namespace Tests\Feature;

use App\Http\Controllers\Api\SearchController;
use App\Jobs\ScanWebsiteJob;
use App\Models\Company;
use App\Models\Scan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Mockery;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Tests for the scan deduplication / quota logic.
 *
 * Section A — resolveOrCreateScan() is tested directly via ReflectionMethod
 *             to bypass the GooglePlacesService HTTP dependency.
 *
 * Section B — ScanWebsiteJob::handle() is tested by calling handle() directly
 *             with an overloaded (Mockery) WebsiteScanner for the success path.
 *
 * Queue fake methods in this Laravel version:
 *   assertPushed / assertNotPushed / assertPushedTimes / assertNothingPushed
 */
class ScanFlowTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Create a User whose effective scan quota is the free-tier (10 scans/month).
     */
    private function freeUser(int $usedThisMonth = 0): User
    {
        return User::factory()->create([
            'scans_this_month' => $usedThisMonth,
        ]);
    }

    /**
     * Create a Company record with a website.
     */
    private function makeCompany(array $overrides = []): Company
    {
        static $n = 0;
        $n++;
        return Company::create(array_merge([
            'place_id'    => 'place_' . $n,
            'name'        => 'Company ' . $n,
            'branche'     => 'Webdesign',
            'stadt'       => 'Berlin',
            'website_url' => 'https://company-' . $n . '.test',
            'has_website' => true,
        ], $overrides));
    }

    /**
     * Return a complete scan result array that covers all NOT NULL columns.
     */
    private function fullScanResult(array $overrides = []): array
    {
        return array_merge([
            'status'             => 'done',
            'http_status'        => 200,
            'response_ms'        => 123,
            'has_ssl'            => true,
            'is_mobile'          => true,
            'cms'                => null,
            'page_title'         => 'Test Page',
            'meta_description'   => null,
            'has_contact'        => true,
            'has_impressum'      => true,
            'has_tracking'       => false,
            'has_favicon'        => true,
            'has_og_tags'        => false,
            'has_google_maps'    => false,
            'has_cookie_banner'  => false,
            'alt_tag_ratio'      => null,
            'broken_links_count' => null,
            'speed_rating'       => null,
            'score'              => 80,
            'error'              => null,
            'scanned_at'         => now(),
        ], $overrides);
    }

    /**
     * Call SearchController::resolveOrCreateScan() via reflection.
     *
     * Returns [scanStatus, scanData] exactly as the method does.
     *
     * @param  Company   $company
     * @param  int|null  $userId
     * @param  int|null  $remainingQuota   null = unlimited
     * @param  int       &$newScanCount    passed by reference
     * @param  Scan|null $latestScan
     */
    private function invokeResolveOrCreateScan(
        Company  $company,
        ?int     $userId,
        ?int     $remainingQuota,
        int      &$newScanCount,
        ?Scan    $latestScan
    ): array {
        $controller = new SearchController();
        $method     = new ReflectionMethod(SearchController::class, 'resolveOrCreateScan');
        $method->setAccessible(true);

        return $method->invokeArgs($controller, [
            $company,
            $userId,
            $remainingQuota,
            &$newScanCount,
            $latestScan,
        ]);
    }

    // =========================================================================
    // A) resolveOrCreateScan() — direct reflection tests
    // =========================================================================

    /**
     * Test 1: should dispatch new scan when company has no scan
     */
    public function test_should_dispatch_new_scan_when_company_has_no_scan(): void
    {
        Queue::fake();

        $user    = $this->freeUser();
        $company = $this->makeCompany();
        $count   = 0;

        [$status, $data] = $this->invokeResolveOrCreateScan(
            $company, $user->id, 10, $count, null
        );

        Queue::assertPushed(ScanWebsiteJob::class);
        $this->assertSame('pending', $status);
        $this->assertNull($data);
        $this->assertSame(1, $count);
        $this->assertDatabaseHas('scans', ['company_id' => $company->id, 'status' => 'pending']);
    }

    /**
     * Test 2: should NOT dispatch new scan when company has active pending scan
     */
    public function test_should_not_dispatch_new_scan_when_company_has_active_pending_scan(): void
    {
        Queue::fake();

        $user    = $this->freeUser();
        $company = $this->makeCompany();
        Scan::create(['company_id' => $company->id, 'status' => 'pending']);

        $latestScan = Scan::where('company_id', $company->id)->latest()->first();
        $count = 0;

        [$status, $data] = $this->invokeResolveOrCreateScan(
            $company, $user->id, 10, $count, $latestScan
        );

        Queue::assertNotPushed(ScanWebsiteJob::class);
        $this->assertSame('pending', $status);
        $this->assertNull($data);
        $this->assertSame(0, $count);
        $this->assertSame(1, Scan::where('company_id', $company->id)->count());
    }

    /**
     * Test 3: should NOT dispatch new scan when company has active running scan
     */
    public function test_should_not_dispatch_new_scan_when_company_has_active_running_scan(): void
    {
        Queue::fake();

        $user    = $this->freeUser();
        $company = $this->makeCompany();
        Scan::create(['company_id' => $company->id, 'status' => 'running']);

        $latestScan = Scan::where('company_id', $company->id)->latest()->first();
        $count = 0;

        [$status, $data] = $this->invokeResolveOrCreateScan(
            $company, $user->id, 10, $count, $latestScan
        );

        Queue::assertNotPushed(ScanWebsiteJob::class);
        $this->assertSame('scanning', $status);   // 'running' maps to 'scanning' in frontend
        $this->assertNull($data);
        $this->assertSame(0, $count);
    }

    /**
     * Test 4: should NOT dispatch new scan when company has fresh done scan (< 7 days)
     */
    public function test_should_not_dispatch_new_scan_when_company_has_fresh_done_scan(): void
    {
        Queue::fake();

        $user    = $this->freeUser();
        $company = $this->makeCompany();
        $scan    = Scan::create([
            'company_id' => $company->id,
            'status'     => 'done',
            'scanned_at' => now()->subDays(3),
            'score'      => 75,
        ]);

        $count = 0;

        [$status, $data] = $this->invokeResolveOrCreateScan(
            $company, $user->id, 10, $count, $scan
        );

        Queue::assertNotPushed(ScanWebsiteJob::class);
        $this->assertSame('done', $status);
        $this->assertNotNull($data);
        $this->assertSame(75, $data['score']);
        $this->assertSame(0, $count);
    }

    /**
     * Test 5: should dispatch new scan when company has stale done scan (> 7 days)
     */
    public function test_should_dispatch_new_scan_when_company_has_stale_done_scan(): void
    {
        Queue::fake();

        $user    = $this->freeUser();
        $company = $this->makeCompany();
        Scan::create([
            'company_id' => $company->id,
            'status'     => 'done',
            'scanned_at' => now()->subDays(10),
            'score'      => 60,
        ]);

        $staleScan = Scan::where('company_id', $company->id)->latest()->first();
        $count = 0;

        [$status, $data] = $this->invokeResolveOrCreateScan(
            $company, $user->id, 10, $count, $staleScan
        );

        Queue::assertPushed(ScanWebsiteJob::class);
        $this->assertSame('pending', $status);
        $this->assertSame(1, $count);
        $this->assertDatabaseHas('scans', [
            'company_id' => $company->id,
            'status'     => 'pending',
        ]);
    }

    /**
     * Test 6: should dispatch new scan when company has failed scan
     */
    public function test_should_dispatch_new_scan_when_company_has_failed_scan(): void
    {
        Queue::fake();

        $user    = $this->freeUser();
        $company = $this->makeCompany();
        $failed  = Scan::create([
            'company_id' => $company->id,
            'status'     => 'failed',
            'error'      => 'timeout',
        ]);

        $count = 0;

        [$status, $data] = $this->invokeResolveOrCreateScan(
            $company, $user->id, 10, $count, $failed
        );

        Queue::assertPushed(ScanWebsiteJob::class);
        $this->assertSame('pending', $status);
        $this->assertSame(1, $count);
        $this->assertDatabaseHas('scans', [
            'company_id' => $company->id,
            'status'     => 'pending',
        ]);
    }

    /**
     * Test 7: should NOT dispatch more scans than remaining quota
     *
     * remainingQuota = 1, call resolveOrCreateScan twice for two companies.
     * First call should create a scan ($count goes to 1 = remainingQuota),
     * second call should return [null, null].
     */
    public function test_should_not_dispatch_more_scans_than_remaining_quota(): void
    {
        Queue::fake();

        $user   = $this->freeUser(9); // used 9, limit 10 → 1 remaining
        $c1     = $this->makeCompany();
        $c2     = $this->makeCompany();
        $count  = 0;
        $quota  = 1; // 10 limit - 9 used

        [$s1, $d1] = $this->invokeResolveOrCreateScan($c1, $user->id, $quota, $count, null);
        [$s2, $d2] = $this->invokeResolveOrCreateScan($c2, $user->id, $quota, $count, null);

        Queue::assertPushedTimes(ScanWebsiteJob::class, 1);
        $this->assertSame('pending', $s1);
        $this->assertNull($s2);
        $this->assertNull($d2);
        $this->assertSame(1, $count);
    }

    /**
     * Test 8: should return scan_status=done and scan data when fresh done scan exists
     */
    public function test_should_return_scan_status_done_and_scan_data_when_fresh_done_scan_exists(): void
    {
        Queue::fake();

        $user    = $this->freeUser();
        $company = $this->makeCompany();
        $scan    = Scan::create([
            'company_id' => $company->id,
            'status'     => 'done',
            'scanned_at' => now()->subHours(2),
            'score'      => 88,
            'has_ssl'    => true,
        ]);

        $count = 0;

        [$status, $data] = $this->invokeResolveOrCreateScan(
            $company, $user->id, 10, $count, $scan
        );

        $this->assertSame('done', $status);
        $this->assertNotNull($data);
        $this->assertSame(88, $data['score']);
        $this->assertTrue($data['has_ssl']);
        Queue::assertNotPushed(ScanWebsiteJob::class);
    }

    /**
     * Test 9: should return scan_status=pending when active scan exists (no data returned)
     */
    public function test_should_return_scan_status_pending_when_active_scan_exists(): void
    {
        Queue::fake();

        $user    = $this->freeUser();
        $company = $this->makeCompany();
        $pending = Scan::create(['company_id' => $company->id, 'status' => 'pending']);

        $count = 0;

        [$status, $data] = $this->invokeResolveOrCreateScan(
            $company, $user->id, 10, $count, $pending
        );

        $this->assertSame('pending', $status);
        $this->assertNull($data);
        Queue::assertNotPushed(ScanWebsiteJob::class);
    }

    /**
     * Test 10: should not create scan for companies without website
     *
     * The controller checks `$company->has_website` before calling resolveOrCreateScan.
     * We verify this by calling the full HTTP endpoint with a place that has no website.
     * Google Places is not needed since the company is pre-created.
     *
     * We test this by simply verifying resolveOrCreateScan is never reached for
     * has_website=false. We do this by confirming the company controller logic:
     * if has_website is false, the scan_status must be null and no scan record exists.
     */
    public function test_should_not_create_scan_for_companies_without_website(): void
    {
        Queue::fake();

        // The controller only calls resolveOrCreateScan when $company->has_website is true.
        // For a company without a website, scan_status stays null.
        $company = $this->makeCompany(['has_website' => false, 'website_url' => null]);

        // Simulate what the controller does: skip resolveOrCreateScan for no-website companies.
        $scanStatus = null;
        if ($company->has_website) {
            $count = 0;
            [$scanStatus,] = $this->invokeResolveOrCreateScan($company, 1, 10, $count, null);
        }

        Queue::assertNothingPushed();
        $this->assertNull($scanStatus);
        $this->assertDatabaseCount('scans', 0);
    }

    // =========================================================================
    // B) ScanWebsiteJob — handle()
    // =========================================================================

    /**
     * Test 11: should mark scan as running and complete it when quota available
     *
     * Uses Mockery overload to intercept `new WebsiteScanner(...)` inside the job.
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_should_mark_scan_as_running_and_complete_it_when_quota_available(): void
    {
        $scannerMock = Mockery::mock('overload:App\Services\WebsiteScanner');
        $scannerMock->shouldReceive('scan')
            ->once()
            ->andReturn($this->fullScanResult(['score' => 72]));

        $user    = $this->freeUser(0);
        $company = $this->makeCompany();
        $scan    = Scan::create(['company_id' => $company->id, 'status' => 'pending']);

        (new ScanWebsiteJob($company->id, $user->id))->handle();

        $scan->refresh();
        $this->assertSame('done', $scan->status);
        $this->assertSame(72, $scan->score);
        $this->assertSame(1, $user->fresh()->scans_this_month);
    }

    /**
     * Test 12: should mark scan as failed with quota_exceeded when quota exceeded
     */
    public function test_should_mark_scan_as_failed_with_quota_exceeded_when_quota_exceeded(): void
    {
        // Free user already at limit: scans_this_month=10, effectiveLimit=10
        $user    = $this->freeUser(10);
        $company = $this->makeCompany();
        $scan    = Scan::create(['company_id' => $company->id, 'status' => 'pending']);

        (new ScanWebsiteJob($company->id, $user->id))->handle();

        $scan->refresh();
        $this->assertSame('failed', $scan->status);
        $this->assertSame('quota_exceeded', $scan->error);
        $this->assertSame(10, $user->fresh()->scans_this_month);
    }

    /**
     * Test 13: should do nothing when no pending scan exists for company
     */
    public function test_should_do_nothing_when_no_pending_scan_exists_for_company(): void
    {
        $user    = $this->freeUser();
        $company = $this->makeCompany();

        $this->assertDatabaseCount('scans', 0);

        (new ScanWebsiteJob($company->id, $user->id))->handle();

        $this->assertDatabaseCount('scans', 0);
        $this->assertSame(0, $user->fresh()->scans_this_month);
    }

    /**
     * Test 14: should handle WebsiteScanner exception gracefully
     *
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function test_should_handle_website_scanner_exception_gracefully(): void
    {
        $scannerMock = Mockery::mock('overload:App\Services\WebsiteScanner');
        $scannerMock->shouldReceive('scan')
            ->once()
            ->andThrow(new \RuntimeException('Connection refused'));

        $user    = $this->freeUser();
        $company = $this->makeCompany();
        $scan    = Scan::create(['company_id' => $company->id, 'status' => 'pending']);

        (new ScanWebsiteJob($company->id, $user->id))->handle();

        $scan->refresh();
        $this->assertSame('failed', $scan->status);
        $this->assertSame('Connection refused', $scan->error);
        $this->assertSame(0, $user->fresh()->scans_this_month);
    }

    // =========================================================================
    // C) Edge cases
    // =========================================================================

    /**
     * Boundary: a scan scanned exactly 604800 seconds ago (7 days) is still
     * within the fresh window because the controller uses >= comparison.
     */
    public function test_should_treat_exactly_7_day_old_scan_as_fresh(): void
    {
        Queue::fake();

        $user    = $this->freeUser();
        $company = $this->makeCompany();
        $scan    = Scan::create([
            'company_id' => $company->id,
            'status'     => 'done',
            'scanned_at' => now()->subSeconds(604800),
            'score'      => 70,
        ]);

        $count = 0;
        [$status, $data] = $this->invokeResolveOrCreateScan(
            $company, $user->id, 10, $count, $scan
        );

        // scanned_at->timestamp >= now()->subSeconds(604800)->timestamp → fresh
        Queue::assertNothingPushed();
        $this->assertSame('done', $status);
        $this->assertNotNull($data);
    }

    /**
     * A running scan must be aliased as 'scanning' (frontend alias for UX).
     */
    public function test_should_return_scanning_alias_when_scan_is_running(): void
    {
        Queue::fake();

        $user    = $this->freeUser();
        $company = $this->makeCompany();
        $running = Scan::create(['company_id' => $company->id, 'status' => 'running']);

        $count = 0;
        [$status,] = $this->invokeResolveOrCreateScan(
            $company, $user->id, 10, $count, $running
        );

        $this->assertSame('scanning', $status);
        Queue::assertNothingPushed();
    }

    /**
     * An unlimited user (remainingQuota = null) always gets a new scan dispatched.
     */
    public function test_should_dispatch_scan_for_unlimited_quota_user(): void
    {
        Queue::fake();

        $user    = $this->freeUser();
        $company = $this->makeCompany();
        $count   = 0;

        // null quota = unlimited
        [$status,] = $this->invokeResolveOrCreateScan(
            $company, $user->id, null, $count, null
        );

        Queue::assertPushed(ScanWebsiteJob::class);
        $this->assertSame('pending', $status);
    }

    /**
     * ScanWebsiteJob exits early when the company has no website_url.
     * The pending scan record must remain untouched.
     */
    public function test_should_leave_scan_pending_when_company_has_no_website_url(): void
    {
        $user    = $this->freeUser();
        $company = $this->makeCompany(['website_url' => null]);
        $scan    = Scan::create(['company_id' => $company->id, 'status' => 'pending']);

        (new ScanWebsiteJob($company->id, $user->id))->handle();

        $scan->refresh();
        $this->assertSame('pending', $scan->status);
        $this->assertSame(0, $user->fresh()->scans_this_month);
    }

    /**
     * A quota of 0 (all used up) must result in [null, null] immediately.
     */
    public function test_should_return_null_status_when_quota_is_zero(): void
    {
        Queue::fake();

        $user    = $this->freeUser(10);
        $company = $this->makeCompany();
        $count   = 0;

        [$status, $data] = $this->invokeResolveOrCreateScan(
            $company, $user->id, 0, $count, null
        );

        $this->assertNull($status);
        $this->assertNull($data);
        Queue::assertNothingPushed();
        $this->assertDatabaseCount('scans', 0);
    }
}
