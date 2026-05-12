<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests for:
 *   - GET /robots.txt  — dynamic Sitemap URL rendered correctly
 *   - POST /onboarding/dismiss — session flag is set and user is redirected
 */
class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    // =========================================================================
    // A) GET /robots.txt — dynamic Sitemap URL
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * The /robots.txt route must return a 200 response with Content-Type text/plain.
     */
    public function should_return_200_with_plain_text_content_type_for_robots_txt(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'text/plain; charset=UTF-8');
    }

    /**
     * @test
     * EXPECTED: PASS
     * The Sitemap URL in robots.txt must use the configured APP_URL, not a hard-coded value.
     * This verifies the `{{ config('app.url') }}/sitemap.xml` template fix.
     */
    public function should_render_robots_txt_with_dynamic_app_url_in_sitemap_directive(): void
    {
        // Set a recognisable APP_URL for the assertion
        config(['app.url' => 'https://pagescan.example.com']);

        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertSee('Sitemap: https://pagescan.example.com/sitemap.xml', escape: false);
    }

    /**
     * @test
     * EXPECTED: PASS
     * The Sitemap line must not contain literal Blade syntax (un-rendered template).
     * Regression guard: ensures the view is compiled and not served raw.
     */
    public function should_not_render_raw_blade_syntax_in_robots_txt(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertDontSee('{{ config(', escape: false);
        $response->assertDontSee('{!!', escape: false);
    }

    /**
     * @test
     * EXPECTED: PASS
     * robots.txt must include the standard disallow rules for protected routes.
     */
    public function should_include_disallow_rules_for_protected_routes_in_robots_txt(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertStatus(200);
        $response->assertSee('Disallow: /dashboard', escape: false);
        $response->assertSee('Disallow: /admin', escape: false);
        $response->assertSee('User-agent: *', escape: false);
    }

    /**
     * @test
     * EXPECTED: PASS
     * robots.txt must be accessible without authentication.
     */
    public function should_be_publicly_accessible_without_authentication(): void
    {
        $this->assertGuest();

        $this->get('/robots.txt')->assertStatus(200);
    }

    // =========================================================================
    // B) POST /onboarding/dismiss — Session flag
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * An authenticated POST to /onboarding/dismiss must set the session key
     * 'onboarding_dismissed' to true.
     */
    public function should_set_onboarding_dismissed_session_flag_when_authenticated_user_posts_dismiss(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/onboarding/dismiss');

        $response->assertSessionHas('onboarding_dismissed', true);
    }

    /**
     * @test
     * EXPECTED: PASS
     * After dismissing the onboarding banner the user must be redirected to the dashboard.
     */
    public function should_redirect_to_dashboard_after_onboarding_dismiss(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->post('/onboarding/dismiss');

        $response->assertRedirect(route('dashboard'));
    }

    /**
     * @test
     * EXPECTED: PASS
     * An unauthenticated POST to /onboarding/dismiss must redirect to the login page.
     * The route is inside the 'auth' middleware group.
     */
    public function should_redirect_unauthenticated_user_to_login_when_posting_to_onboarding_dismiss(): void
    {
        $response = $this->post('/onboarding/dismiss');

        $response->assertRedirect(route('login'));
    }

    /**
     * @test
     * EXPECTED: PASS
     * The session flag must persist across a subsequent request (not be a flash value).
     * Verifies the standard session() helper — not withSession() — was used.
     */
    public function should_persist_onboarding_dismissed_flag_across_subsequent_requests(): void
    {
        $user = User::factory()->create();

        // First request: dismiss
        $this->actingAs($user)->post('/onboarding/dismiss');

        // Second request: flag must still be in the session
        $response = $this->actingAs($user)->get('/profile');

        // The flag is stored as a plain session value; it does not expire after one request.
        $this->assertTrue(session('onboarding_dismissed'));
    }

    /**
     * @test
     * EXPECTED: PASS
     * A GET request to /onboarding/dismiss must return 405 Method Not Allowed
     * since only POST is registered for this route.
     */
    public function should_return_405_when_getting_onboarding_dismiss_endpoint(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/onboarding/dismiss')
            ->assertMethodNotAllowed();
    }

    /**
     * @test
     * EXPECTED: PASS
     * Calling dismiss multiple times must be idempotent — no error, flag stays true.
     */
    public function should_be_idempotent_when_onboarding_dismiss_is_called_multiple_times(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/onboarding/dismiss')->assertRedirect(route('dashboard'));
        $this->actingAs($user)->post('/onboarding/dismiss')->assertRedirect(route('dashboard'));

        $this->assertTrue(session('onboarding_dismissed'));
    }
}
