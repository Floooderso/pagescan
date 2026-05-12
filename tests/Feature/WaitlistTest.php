<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Feature tests for the Waitlist system.
 *
 * Covers:
 *  - Registration waitlist flow (free-tier vs. waiting)
 *  - Admin /admin/waitlist access control
 *  - Admin approve/reject actions
 *  - RequireApprovedUser middleware
 */
class WaitlistTest extends TestCase
{
    use RefreshDatabase;

    // ─────────────────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────────────────

    private function registerUser(string $email = 'user@example.com'): \Illuminate\Testing\TestResponse
    {
        return $this->post('/register', [
            'name'                  => 'Test User',
            'email'                 => $email,
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);
    }

    private function makeAdmin(): User
    {
        return User::factory()->create([
            'role'             => 'admin',
            'waitlist_status'  => 'approved',
            'is_free_tier'     => true,
        ]);
    }

    private function makeApprovedUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'waitlist_status' => 'approved',
            'is_free_tier'    => true,
            'approved_at'     => now(),
        ], $overrides));
    }

    private function makeWaitingUser(array $overrides = []): User
    {
        return User::factory()->create(array_merge([
            'waitlist_status' => 'waiting',
            'is_free_tier'    => false,
        ], $overrides));
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Registration — waitlist placement
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function should_redirect_to_pending_when_user_registers_after_limit_is_reached(): void
    {
        // Fill up all 25 free-tier slots
        User::factory()->count(User::FREE_TIER_LIMIT)->create([
            'waitlist_status' => 'approved',
            'is_free_tier'    => true,
        ]);

        $response = $this->registerUser('late@example.com');

        $this->assertAuthenticated();
        $response->assertRedirect(route('waitlist.pending'));
    }

    #[Test]
    public function should_redirect_to_dashboard_when_user_registers_within_free_tier_limit(): void
    {
        // No approved users yet — first registration gets free tier
        $response = $this->registerUser('early@example.com');

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
    }

    #[Test]
    public function should_set_is_free_tier_true_for_first_25_registrations(): void
    {
        // 24 already approved — this user is #25 (last free slot)
        User::factory()->count(24)->create([
            'waitlist_status' => 'approved',
            'is_free_tier'    => true,
        ]);

        $this->registerUser('slot25@example.com');

        $user = User::where('email', 'slot25@example.com')->first();
        $this->assertTrue((bool) $user->is_free_tier);
        $this->assertEquals('approved', $user->waitlist_status);
    }

    #[Test]
    public function should_not_set_is_free_tier_for_user_26_and_beyond(): void
    {
        // 25 already approved — this user is #26 (no free slot)
        User::factory()->count(25)->create([
            'waitlist_status' => 'approved',
            'is_free_tier'    => true,
        ]);

        $this->registerUser('slot26@example.com');

        $user = User::where('email', 'slot26@example.com')->first();
        $this->assertFalse((bool) $user->is_free_tier);
        $this->assertEquals('waiting', $user->waitlist_status);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Admin — access control
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function should_deny_guest_access_to_admin_waitlist(): void
    {
        $response = $this->get('/admin/waitlist');

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function should_deny_non_admin_user_access_to_admin_waitlist(): void
    {
        $user = $this->makeApprovedUser(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/waitlist');

        $response->assertForbidden();
    }

    #[Test]
    public function should_allow_admin_to_access_admin_waitlist(): void
    {
        $admin = $this->makeAdmin();

        $response = $this->actingAs($admin)->get('/admin/waitlist');

        $response->assertOk();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Admin — approve action
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function should_set_waitlist_status_to_approved_when_admin_approves_user(): void
    {
        $admin   = $this->makeAdmin();
        $waiting = $this->makeWaitingUser();

        $this->actingAs($admin)
             ->post("/admin/waitlist/{$waiting->id}/approve");

        $this->assertEquals('approved', $waiting->fresh()->waitlist_status);
    }

    #[Test]
    public function should_assign_free_tier_when_fewer_than_25_users_already_approved(): void
    {
        $admin   = $this->makeAdmin();
        // Admin itself is approved — that's 1. Add 23 more = 24 total.
        User::factory()->count(23)->create([
            'waitlist_status' => 'approved',
            'is_free_tier'    => true,
        ]);
        $waiting = $this->makeWaitingUser();

        $this->actingAs($admin)
             ->post("/admin/waitlist/{$waiting->id}/approve");

        $approved = $waiting->fresh();
        $this->assertEquals('approved', $approved->waitlist_status);
        $this->assertTrue((bool) $approved->is_free_tier);
    }

    #[Test]
    public function should_not_assign_free_tier_when_25_or_more_users_already_approved(): void
    {
        $admin = $this->makeAdmin();
        // Admin itself is approved (1) + 24 more = 25 total approved
        User::factory()->count(24)->create([
            'waitlist_status' => 'approved',
            'is_free_tier'    => true,
        ]);
        $waiting = $this->makeWaitingUser();

        $this->actingAs($admin)
             ->post("/admin/waitlist/{$waiting->id}/approve");

        $approved = $waiting->fresh();
        $this->assertEquals('approved', $approved->waitlist_status);
        $this->assertFalse((bool) $approved->is_free_tier);
    }

    #[Test]
    public function should_not_re_approve_already_approved_user(): void
    {
        $admin    = $this->makeAdmin();
        $approved = $this->makeApprovedUser();

        $response = $this->actingAs($admin)
                         ->post("/admin/waitlist/{$approved->id}/approve");

        $response->assertRedirect();
        // Still approved, no change
        $this->assertEquals('approved', $approved->fresh()->waitlist_status);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // RequireApprovedUser middleware
    // ─────────────────────────────────────────────────────────────────────────

    #[Test]
    public function should_redirect_guest_to_login_when_accessing_approved_only_route(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    #[Test]
    public function should_redirect_waiting_user_to_pending_when_accessing_approved_only_route(): void
    {
        $waiting = $this->makeWaitingUser();

        $response = $this->actingAs($waiting)->get('/dashboard');

        $response->assertRedirect(route('waitlist.pending'));
    }

    #[Test]
    public function should_allow_approved_user_through_approved_middleware(): void
    {
        $approved = $this->makeApprovedUser();

        $response = $this->actingAs($approved)->get('/dashboard');

        // Dashboard responds 200 (or redirect to sub-page) — not a waitlist redirect
        $this->assertNotEquals(route('waitlist.pending'), $response->headers->get('Location'));
        $this->assertNotEquals(route('login'), $response->headers->get('Location'));
    }

    #[Test]
    public function should_allow_admin_through_approved_middleware_regardless_of_waitlist_status(): void
    {
        // Admin with waiting status (edge case) — should still pass
        $admin = User::factory()->create([
            'role'            => 'admin',
            'waitlist_status' => 'waiting',
        ]);

        $response = $this->actingAs($admin)->get('/dashboard');

        // Should not be redirected to waitlist.pending
        $location = $response->headers->get('Location');
        if ($location) {
            $this->assertStringNotContainsString('waitlist/pending', $location);
        } else {
            $response->assertOk();
        }
    }

    #[Test]
    public function should_redirect_rejected_user_to_rejected_page(): void
    {
        $rejected = User::factory()->create([
            'waitlist_status' => 'rejected',
            'is_free_tier'    => false,
        ]);

        $response = $this->actingAs($rejected)->get('/dashboard');

        $response->assertRedirect(route('waitlist.rejected'));
    }
}
