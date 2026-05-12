<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\WebsiteScanner;

/**
 * Unit tests for WebsiteScanner helper methods.
 *
 * Methods under test (all public):
 *   checkGoogleFonts(string $html): string
 *   checkCookieBanner(string $html): array
 *   checkLegalPages(string $html): array
 *   checkSecurityHeaders(array $headers): array
 *   checkOutdatedTech(array $headers, string $html): array
 *   computePerformanceScores(int $responseMs, string $html): array
 *   scan(string $url): array  — SSRF-protection path only
 */
class WebsiteScannerTest extends TestCase
{
    private WebsiteScanner $scanner;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scanner = new WebsiteScanner();
    }

    // =========================================================================
    // 1. checkGoogleFonts
    // =========================================================================

    public function test_checkGoogleFonts_returns_external_when_fonts_googleapis_com_present(): void
    {
        $html = '<link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto">';
        $this->assertSame('external', $this->scanner->checkGoogleFonts($html));
    }

    public function test_checkGoogleFonts_returns_external_when_import_from_googleapis_present(): void
    {
        $html = '<style>@import url("https://fonts.googleapis.com/css2?family=Open+Sans");</style>';
        $this->assertSame('external', $this->scanner->checkGoogleFonts($html));
    }

    public function test_checkGoogleFonts_returns_local_when_font_face_with_woff2_and_no_googleapis(): void
    {
        $html = '<style>@font-face { font-family: "Roboto"; src: url("/fonts/roboto.woff2") format("woff2"); }</style>';
        $this->assertSame('local', $this->scanner->checkGoogleFonts($html));
    }

    public function test_checkGoogleFonts_returns_local_when_font_face_with_woff_and_no_googleapis(): void
    {
        $html = '<style>@font-face { font-family: "MyFont"; src: url("/assets/myfont.woff") format("woff"); }</style>';
        $this->assertSame('local', $this->scanner->checkGoogleFonts($html));
    }

    public function test_checkGoogleFonts_returns_unknown_when_no_fonts_detected(): void
    {
        $html = '<html><body><p>Hello World</p></body></html>';
        $this->assertSame('unknown', $this->scanner->checkGoogleFonts($html));
    }

    public function test_checkGoogleFonts_returns_external_when_both_googleapis_and_font_face_present(): void
    {
        // External takes precedence (googleapis is matched first)
        $html = '<style>@font-face { src: url("/fonts/r.woff2"); }</style>'
              . '<link href="https://fonts.googleapis.com/css?family=Roboto">';
        $this->assertSame('external', $this->scanner->checkGoogleFonts($html));
    }

    public function test_checkGoogleFonts_returns_unknown_when_only_font_face_but_no_font_file(): void
    {
        // @font-face present but no .woff/.woff2/.ttf/.otf/.eot extension
        $html = '<style>@font-face { font-family: "Custom"; src: local("Custom"); }</style>';
        $this->assertSame('unknown', $this->scanner->checkGoogleFonts($html));
    }

    public function test_checkGoogleFonts_is_case_insensitive_for_googleapis(): void
    {
        $html = '<link href="https://FONTS.GOOGLEAPIS.COM/css?family=Lato">';
        $this->assertSame('external', $this->scanner->checkGoogleFonts($html));
    }

    // =========================================================================
    // 2. checkCookieBanner
    // =========================================================================

    public function test_checkCookieBanner_detects_cookiebot(): void
    {
        $html = '<script src="https://consent.cookiebot.com/uc.js" data-cbid="abc"></script>';
        $result = $this->scanner->checkCookieBanner($html);
        $this->assertSame('found', $result['status']);
        $this->assertSame('Cookiebot', $result['tool']);
    }

    public function test_checkCookieBanner_detects_usercentrics(): void
    {
        $html = '<script id="usercentrics-cmp" src="https://app.usercentrics.eu/browser-ui/latest/loader.js"></script>';
        $result = $this->scanner->checkCookieBanner($html);
        $this->assertSame('found', $result['status']);
        $this->assertSame('Usercentrics', $result['tool']);
    }

    public function test_checkCookieBanner_detects_onetrust(): void
    {
        $html = '<script src="/cdn/consent/onetrust/consent.js"></script>';
        $result = $this->scanner->checkCookieBanner($html);
        $this->assertSame('found', $result['status']);
        $this->assertSame('OneTrust', $result['tool']);
    }

    public function test_checkCookieBanner_detects_borlabs_cookie(): void
    {
        $html = '<div id="BorlabsCookie" class="cookie-box"></div>';
        $result = $this->scanner->checkCookieBanner($html);
        $this->assertSame('found', $result['status']);
        $this->assertSame('Borlabs Cookie', $result['tool']);
    }

    public function test_checkCookieBanner_detects_cookieyes(): void
    {
        $html = '<script src="https://cdn-cookieyes.com/client_data/abc.js"></script>';
        $result = $this->scanner->checkCookieBanner($html);
        $this->assertSame('found', $result['status']);
        $this->assertSame('CookieYes', $result['tool']);
    }

    public function test_checkCookieBanner_detects_complianz(): void
    {
        $html = '<script src="/wp-content/plugins/complianz-gdpr/assets/js/dlv.min.js"></script>';
        $result = $this->scanner->checkCookieBanner($html);
        $this->assertSame('found', $result['status']);
        $this->assertSame('Complianz', $result['tool']);
    }

    public function test_checkCookieBanner_detects_klaro(): void
    {
        $html = '<script src="/assets/klaro.js"></script>';
        $result = $this->scanner->checkCookieBanner($html);
        $this->assertSame('found', $result['status']);
        $this->assertSame('Klaro', $result['tool']);
    }

    public function test_checkCookieBanner_detects_cookie_notice(): void
    {
        $html = '<div class="cookie-notice-container">We use cookies.</div>';
        $result = $this->scanner->checkCookieBanner($html);
        $this->assertSame('found', $result['status']);
        $this->assertSame('Cookie Notice', $result['tool']);
    }

    public function test_checkCookieBanner_detects_cookieconsent(): void
    {
        $html = '<link rel="stylesheet" href="/assets/cookieconsent.min.css">';
        $result = $this->scanner->checkCookieBanner($html);
        $this->assertSame('found', $result['status']);
        $this->assertSame('Cookie Consent', $result['tool']);
    }

    public function test_checkCookieBanner_returns_not_found_when_no_banner_present(): void
    {
        $html = '<html><body><p>Welcome to our site.</p></body></html>';
        $result = $this->scanner->checkCookieBanner($html);
        $this->assertSame('not_found', $result['status']);
        $this->assertNull($result['tool']);
    }

    public function test_checkCookieBanner_is_case_insensitive(): void
    {
        $html = '<script src="https://consent.COOKIEBOT.com/uc.js"></script>';
        $result = $this->scanner->checkCookieBanner($html);
        $this->assertSame('found', $result['status']);
        $this->assertSame('Cookiebot', $result['tool']);
    }

    // =========================================================================
    // 3. checkLegalPages
    // =========================================================================

    public function test_checkLegalPages_returns_menu_when_impressum_in_nav(): void
    {
        $html = '<html><body><nav><a href="/impressum">Impressum</a></nav></body></html>';
        $result = $this->scanner->checkLegalPages($html);
        $this->assertSame('menu', $result['impressum']);
    }

    public function test_checkLegalPages_returns_footer_when_impressum_in_footer(): void
    {
        $html = '<html><body><footer><a href="/impressum">Impressum</a></footer></body></html>';
        $result = $this->scanner->checkLegalPages($html);
        $this->assertSame('footer', $result['impressum']);
    }

    public function test_checkLegalPages_returns_found_when_impressum_only_in_body(): void
    {
        $html = '<html><body><div class="links"><a href="/impressum">Impressum</a></div></body></html>';
        $result = $this->scanner->checkLegalPages($html);
        $this->assertSame('found', $result['impressum']);
    }

    public function test_checkLegalPages_returns_not_found_when_impressum_absent(): void
    {
        $html = '<html><body><p>No legal links here.</p></body></html>';
        $result = $this->scanner->checkLegalPages($html);
        $this->assertSame('not_found', $result['impressum']);
    }

    public function test_checkLegalPages_returns_menu_when_datenschutz_in_nav(): void
    {
        $html = '<html><body><nav><a href="/datenschutz">Datenschutz</a></nav></body></html>';
        $result = $this->scanner->checkLegalPages($html);
        $this->assertSame('menu', $result['datenschutz']);
    }

    public function test_checkLegalPages_returns_footer_when_datenschutz_in_footer(): void
    {
        $html = '<html><body><footer><a href="/privacy">Privacy Policy</a></footer></body></html>';
        $result = $this->scanner->checkLegalPages($html);
        $this->assertSame('footer', $result['datenschutz']);
    }

    public function test_checkLegalPages_returns_not_found_when_datenschutz_absent(): void
    {
        $html = '<html><body><nav><a href="/impressum">Impressum</a></nav></body></html>';
        $result = $this->scanner->checkLegalPages($html);
        $this->assertSame('not_found', $result['datenschutz']);
    }

    public function test_checkLegalPages_nav_takes_priority_over_footer_for_impressum(): void
    {
        $html = '<html><body>'
              . '<nav><a href="/impressum">Impressum</a></nav>'
              . '<footer><a href="/impressum">Impressum</a></footer>'
              . '</body></html>';
        $result = $this->scanner->checkLegalPages($html);
        $this->assertSame('menu', $result['impressum']);
    }

    public function test_checkLegalPages_detects_imprint_as_impressum_in_nav(): void
    {
        $html = '<html><body><nav><a href="/imprint">Imprint</a></nav></body></html>';
        $result = $this->scanner->checkLegalPages($html);
        $this->assertSame('menu', $result['impressum']);
    }

    public function test_checkLegalPages_detects_privacy_policy_as_datenschutz_in_footer(): void
    {
        $html = '<html><body><footer><a href="/privacy-policy">Privacy Policy</a></footer></body></html>';
        $result = $this->scanner->checkLegalPages($html);
        $this->assertSame('footer', $result['datenschutz']);
    }

    public function test_checkLegalPages_header_nav_counts_as_menu(): void
    {
        $html = '<html><body><header><nav><a href="/impressum">Impressum</a></nav></header></body></html>';
        $result = $this->scanner->checkLegalPages($html);
        $this->assertSame('menu', $result['impressum']);
    }

    // =========================================================================
    // 4. checkSecurityHeaders
    // =========================================================================

    public function test_checkSecurityHeaders_returns_score_4_when_all_headers_present(): void
    {
        $headers = [
            'X-Frame-Options'           => ['DENY'],
            'X-Content-Type-Options'    => ['nosniff'],
            'Strict-Transport-Security' => ['max-age=31536000'],
            'Content-Security-Policy'   => ["default-src 'self'"],
        ];
        $result = $this->scanner->checkSecurityHeaders($headers);
        $this->assertSame(4, $result['score']);
        $this->assertTrue($result['detail']['X-Frame-Options']);
        $this->assertTrue($result['detail']['X-Content-Type-Options']);
        $this->assertTrue($result['detail']['Strict-Transport-Security']);
        $this->assertTrue($result['detail']['Content-Security-Policy']);
    }

    public function test_checkSecurityHeaders_returns_score_0_when_no_headers_present(): void
    {
        $headers = [
            'Content-Type' => ['text/html; charset=UTF-8'],
            'Server'       => ['Apache'],
        ];
        $result = $this->scanner->checkSecurityHeaders($headers);
        $this->assertSame(0, $result['score']);
        $this->assertFalse($result['detail']['X-Frame-Options']);
        $this->assertFalse($result['detail']['X-Content-Type-Options']);
        $this->assertFalse($result['detail']['Strict-Transport-Security']);
        $this->assertFalse($result['detail']['Content-Security-Policy']);
    }

    public function test_checkSecurityHeaders_returns_score_2_when_two_headers_present(): void
    {
        $headers = [
            'X-Frame-Options'        => ['SAMEORIGIN'],
            'X-Content-Type-Options' => ['nosniff'],
        ];
        $result = $this->scanner->checkSecurityHeaders($headers);
        $this->assertSame(2, $result['score']);
        $this->assertTrue($result['detail']['X-Frame-Options']);
        $this->assertTrue($result['detail']['X-Content-Type-Options']);
        $this->assertFalse($result['detail']['Strict-Transport-Security']);
        $this->assertFalse($result['detail']['Content-Security-Policy']);
    }

    public function test_checkSecurityHeaders_is_case_insensitive_for_header_names(): void
    {
        // Guzzle may normalize, but checkSecurityHeaders itself lowercases keys
        $headers = [
            'x-frame-options'           => ['DENY'],
            'x-content-type-options'    => ['nosniff'],
            'strict-transport-security' => ['max-age=31536000'],
            'content-security-policy'   => ["default-src 'self'"],
        ];
        $result = $this->scanner->checkSecurityHeaders($headers);
        $this->assertSame(4, $result['score']);
    }

    public function test_checkSecurityHeaders_returns_score_1_when_only_hsts_present(): void
    {
        $headers = [
            'Strict-Transport-Security' => ['max-age=63072000; includeSubDomains'],
        ];
        $result = $this->scanner->checkSecurityHeaders($headers);
        $this->assertSame(1, $result['score']);
        $this->assertTrue($result['detail']['Strict-Transport-Security']);
    }

    public function test_checkSecurityHeaders_detail_array_always_has_four_keys(): void
    {
        $result = $this->scanner->checkSecurityHeaders([]);
        $this->assertCount(4, $result['detail']);
        $this->assertArrayHasKey('X-Frame-Options', $result['detail']);
        $this->assertArrayHasKey('X-Content-Type-Options', $result['detail']);
        $this->assertArrayHasKey('Strict-Transport-Security', $result['detail']);
        $this->assertArrayHasKey('Content-Security-Policy', $result['detail']);
    }

    // =========================================================================
    // 5. checkOutdatedTech
    // =========================================================================

    public function test_checkOutdatedTech_detects_old_php_5_in_x_powered_by(): void
    {
        $headers = ['X-Powered-By' => ['PHP/5.6.40']];
        $result  = $this->scanner->checkOutdatedTech($headers, '');
        $this->assertNotEmpty($result);
        // Should contain something mentioning PHP and 5.x
        $found = implode(' ', $result);
        $this->assertStringContainsStringIgnoringCase('PHP', $found);
    }

    public function test_checkOutdatedTech_detects_old_php_7_0_in_x_powered_by(): void
    {
        $headers = ['X-Powered-By' => ['PHP/7.2.34']];
        $result  = $this->scanner->checkOutdatedTech($headers, '');
        $found = implode(' ', $result);
        $this->assertStringContainsStringIgnoringCase('PHP', $found);
    }

    public function test_checkOutdatedTech_does_not_flag_modern_php_8(): void
    {
        $headers = ['X-Powered-By' => ['PHP/8.2.0']];
        $result  = $this->scanner->checkOutdatedTech($headers, '');
        $found = implode(' ', $result);
        $this->assertStringNotContainsStringIgnoringCase('PHP', $found);
    }

    public function test_checkOutdatedTech_detects_old_apache_2_2(): void
    {
        $headers = ['Server' => ['Apache/2.2.34 (Unix)']];
        $result  = $this->scanner->checkOutdatedTech($headers, '');
        $this->assertContains('Apache 2.2 (veraltet)', $result);
    }

    public function test_checkOutdatedTech_does_not_flag_apache_2_4(): void
    {
        $headers = ['Server' => ['Apache/2.4.51 (Ubuntu)']];
        $result  = $this->scanner->checkOutdatedTech($headers, '');
        $found = implode(' ', $result);
        $this->assertStringNotContainsStringIgnoringCase('Apache', $found);
    }

    public function test_checkOutdatedTech_detects_old_jquery_1x_in_html(): void
    {
        $html   = '<script src="/js/jquery-1.11.3.min.js"></script>';
        $result = $this->scanner->checkOutdatedTech([], $html);
        $this->assertContains('jQuery 1.x/2.x (veraltet)', $result);
    }

    public function test_checkOutdatedTech_detects_old_jquery_2x_in_html(): void
    {
        $html   = '<script src="/js/jquery-2.2.4.min.js"></script>';
        $result = $this->scanner->checkOutdatedTech([], $html);
        $this->assertContains('jQuery 1.x/2.x (veraltet)', $result);
    }

    public function test_checkOutdatedTech_does_not_flag_modern_jquery_3x(): void
    {
        $html   = '<script src="/js/jquery-3.7.1.min.js"></script>';
        $result = $this->scanner->checkOutdatedTech([], $html);
        $this->assertNotContains('jQuery 1.x/2.x (veraltet)', $result);
    }

    public function test_checkOutdatedTech_detects_bootstrap_3_in_html(): void
    {
        $html   = '<link rel="stylesheet" href="/css/bootstrap-3.4.1.min.css">';
        $result = $this->scanner->checkOutdatedTech([], $html);
        $this->assertContains('Bootstrap 2/3 (veraltet)', $result);
    }

    public function test_checkOutdatedTech_detects_bootstrap_2_in_html(): void
    {
        $html   = '<link rel="stylesheet" href="/css/bootstrap-2.3.2.min.css">';
        $result = $this->scanner->checkOutdatedTech([], $html);
        $this->assertContains('Bootstrap 2/3 (veraltet)', $result);
    }

    public function test_checkOutdatedTech_detects_flash_swf_in_html(): void
    {
        $html   = '<embed src="animation.swf" type="application/x-shockwave-flash">';
        $result = $this->scanner->checkOutdatedTech([], $html);
        $this->assertContains('Flash (nicht mehr unterstützt)', $result);
    }

    public function test_checkOutdatedTech_detects_ie_conditional_comments_in_html(): void
    {
        $html   = '<!--[if lt IE 9]><script src="/html5shiv.js"></script><![endif]-->';
        $result = $this->scanner->checkOutdatedTech([], $html);
        $this->assertContains('IE-Conditional Comments', $result);
    }

    public function test_checkOutdatedTech_returns_empty_array_for_modern_clean_site(): void
    {
        $headers = [
            'Server'       => ['nginx/1.24.0'],
            'X-Powered-By' => ['PHP/8.2.0'],
        ];
        $html = '<html><body><script src="/js/jquery-3.7.1.min.js"></script><script src="/js/bootstrap-5.3.0.bundle.min.js"></script></body></html>';
        $result = $this->scanner->checkOutdatedTech($headers, $html);
        $this->assertSame([], $result);
    }

    // =========================================================================
    // 6. computePerformanceScores
    // =========================================================================

    public function test_computePerformanceScores_ttfb_is_gut_when_response_under_200ms(): void
    {
        $result = $this->scanner->computePerformanceScores(100, '<html></html>');
        $this->assertSame('gut', $result['ttfb']);
    }

    public function test_computePerformanceScores_ttfb_is_mittel_when_response_between_200_and_600ms(): void
    {
        $result = $this->scanner->computePerformanceScores(400, '<html></html>');
        $this->assertSame('mittel', $result['ttfb']);
    }

    public function test_computePerformanceScores_ttfb_is_mittel_at_boundary_200ms(): void
    {
        $result = $this->scanner->computePerformanceScores(200, '<html></html>');
        $this->assertSame('mittel', $result['ttfb']);
    }

    public function test_computePerformanceScores_ttfb_is_mittel_at_boundary_600ms(): void
    {
        $result = $this->scanner->computePerformanceScores(600, '<html></html>');
        $this->assertSame('mittel', $result['ttfb']);
    }

    public function test_computePerformanceScores_ttfb_is_schlecht_when_response_over_600ms(): void
    {
        $result = $this->scanner->computePerformanceScores(900, '<html></html>');
        $this->assertSame('schlecht', $result['ttfb']);
    }

    public function test_computePerformanceScores_lcp_is_gut_for_fast_simple_page(): void
    {
        // Fast response, no render-blocking scripts, no large images
        $html   = '<html><body><p>Hello World</p></body></html>';
        $result = $this->scanner->computePerformanceScores(100, $html);
        $this->assertSame('gut', $result['lcp']);
    }

    public function test_computePerformanceScores_lcp_is_schlecht_for_many_blocking_scripts_and_large_images(): void
    {
        // Many render-blocking scripts + large hero images = high penalty
        $scripts = str_repeat('<script src="/js/lib1.js"></script>', 5);
        $images  = str_repeat('<img src="/hero.jpg" width="1200" height="800">', 3);
        $html    = "<html><head>{$scripts}</head><body>{$images}</body></html>";
        $result  = $this->scanner->computePerformanceScores(700, $html);
        $this->assertSame('schlecht', $result['lcp']);
    }

    public function test_computePerformanceScores_lcp_improves_with_lazy_loading(): void
    {
        // Lazy load reduces penalty by 2 — same page without lazy load would be worse
        $html = '<img src="/hero.jpg" width="1200" height="800" loading="lazy">'
              . '<script src="/js/app.js"></script>';
        // With penalty=3 (1 script + 2*1 large img) - 2 (lazy) = 1, plus responseMs=150 => 'gut'
        $result = $this->scanner->computePerformanceScores(150, $html);
        $this->assertSame('gut', $result['lcp']);
    }

    public function test_computePerformanceScores_cls_is_gut_when_all_images_have_size(): void
    {
        $html = '<img src="a.jpg" width="800" height="600">'
              . '<img src="b.jpg" width="400" height="300">';
        $result = $this->scanner->computePerformanceScores(100, $html);
        $this->assertSame('gut', $result['cls']);
    }

    public function test_computePerformanceScores_cls_is_schlecht_when_no_images_have_size(): void
    {
        $html = '<img src="a.jpg"><img src="b.jpg"><img src="c.jpg">';
        $result = $this->scanner->computePerformanceScores(100, $html);
        $this->assertSame('schlecht', $result['cls']);
    }

    public function test_computePerformanceScores_cls_is_gut_when_no_images_present(): void
    {
        // No images means sizeRatio defaults to 1.0 => 'gut'
        $html   = '<html><body><p>No images</p></body></html>';
        $result = $this->scanner->computePerformanceScores(100, $html);
        $this->assertSame('gut', $result['cls']);
    }

    public function test_computePerformanceScores_cls_is_schlecht_when_ads_present(): void
    {
        // Ads trigger 'schlecht' regardless of image size ratio
        $html = '<img src="a.jpg" width="800" height="600">'
              . '<script async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>';
        $result = $this->scanner->computePerformanceScores(100, $html);
        $this->assertSame('schlecht', $result['cls']);
    }

    public function test_computePerformanceScores_returns_all_three_keys(): void
    {
        $result = $this->scanner->computePerformanceScores(150, '<html></html>');
        $this->assertArrayHasKey('ttfb', $result);
        $this->assertArrayHasKey('lcp', $result);
        $this->assertArrayHasKey('cls', $result);
    }

    // =========================================================================
    // 7. scan() — SSRF protection
    // =========================================================================

    public function test_scan_returns_failed_for_file_scheme(): void
    {
        $result = $this->scanner->scan('file:///etc/passwd');
        $this->assertSame('failed', $result['status']);
        $this->assertSame(0, $result['score']);
        $this->assertStringContainsStringIgnoringCase('Schema', $result['error']);
    }

    public function test_scan_returns_failed_for_ftp_scheme(): void
    {
        $result = $this->scanner->scan('ftp://example.com/file.txt');
        $this->assertSame('failed', $result['status']);
        $this->assertSame(0, $result['score']);
        $this->assertNotEmpty($result['error']);
    }

    public function test_scan_returns_failed_for_empty_url(): void
    {
        $result = $this->scanner->scan('');
        $this->assertSame('failed', $result['status']);
        $this->assertSame(0, $result['score']);
        $this->assertNotEmpty($result['error']);
    }

    public function test_scan_returns_failed_for_private_ip_loopback(): void
    {
        // 127.0.0.1 is FILTER_FLAG_NO_RES_RANGE, should be blocked
        $result = $this->scanner->scan('http://127.0.0.1/');
        $this->assertSame('failed', $result['status']);
        $this->assertStringContainsStringIgnoringCase('privat', $result['error']);
    }

    public function test_scan_returns_failed_for_private_ip_192_168_range(): void
    {
        $result = $this->scanner->scan('http://192.168.1.1/');
        $this->assertSame('failed', $result['status']);
        $this->assertStringContainsStringIgnoringCase('privat', $result['error']);
    }

    public function test_scan_returns_failed_for_private_ip_10_range(): void
    {
        $result = $this->scanner->scan('http://10.0.0.1/');
        $this->assertSame('failed', $result['status']);
        $this->assertStringContainsStringIgnoringCase('privat', $result['error']);
    }

    public function test_scan_returns_failed_for_private_ip_172_16_range(): void
    {
        $result = $this->scanner->scan('http://172.16.0.1/');
        $this->assertSame('failed', $result['status']);
        $this->assertStringContainsStringIgnoringCase('privat', $result['error']);
    }

    public function test_scan_failed_result_has_null_advanced_fields(): void
    {
        $result = $this->scanner->scan('file:///etc/passwd');
        $this->assertNull($result['google_fonts_source']);
        $this->assertNull($result['cookie_banner_tool']);
        $this->assertNull($result['security_headers_score']);
        $this->assertNull($result['outdated_tech']);
        $this->assertNull($result['ttfb_score']);
        $this->assertNull($result['lcp_score']);
        $this->assertNull($result['cls_score']);
    }
}
