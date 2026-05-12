<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class WebsiteScanner
{
    private array $weights;

    public function __construct(array $weights = [])
    {
        $default = [
            'http_ok'   => 30,
            'ssl'       => 20,
            'mobile'    => 20,
            'contact'   => 10,
            'impressum' => 10,
            'favicon'   => 10,
        ];
        $this->weights = array_merge($default, $weights);
    }

    /**
     * Scan multiple URLs concurrently using Guzzle Pool.
     * Broken-link checks are skipped to keep batch time reasonable.
     * Returns [url => result_array].
     */
    public function scanBatch(array $urls): array
    {
        $results = [];
        $ssrfSafe = [];

        foreach ($urls as $key => $url) {
            $parsed = parse_url($url);
            if (!in_array($parsed['scheme'] ?? '', ['http', 'https'], true)) {
                $results[$key] = $this->failedResult(0, 'Ungültiges URL-Schema');
                continue;
            }
            $host = $parsed['host'] ?? '';
            if (!$host) {
                $results[$key] = $this->failedResult(0, 'Ungültiger Hostname');
                continue;
            }
            $ip = gethostbyname($host);
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                $results[$key] = $this->failedResult(0, 'Interne/private IP nicht erlaubt');
                continue;
            }
            $ssrfSafe[$key] = $url;
        }

        if (empty($ssrfSafe)) {
            return $results;
        }

        $client = new Client([
            'timeout'         => 8,
            'connect_timeout' => 5,
            'allow_redirects' => ['max' => 5, 'strict' => false, 'referer' => false, 'protocols' => ['http', 'https']],
            'verify'          => false,
            'http_errors'     => false,
        ]);

        $startTimes = [];

        $requests = function () use ($ssrfSafe, &$startTimes) {
            foreach ($ssrfSafe as $key => $url) {
                $startTimes[$key] = microtime(true);
                yield $key => new \GuzzleHttp\Psr7\Request('GET', $url, [
                    'User-Agent' => 'Mozilla/5.0 (compatible; PageScan/1.0)',
                ]);
            }
        };

        $pool = new \GuzzleHttp\Pool($client, $requests(), [
            'concurrency' => 8,
            'fulfilled'   => function ($response, $key) use ($ssrfSafe, &$results, &$startTimes) {
                $url        = $ssrfSafe[$key];
                $responseMs = (int) round((microtime(true) - ($startTimes[$key] ?? microtime(true))) * 1000);
                $httpStatus = $response->getStatusCode();
                $html       = substr((string) $response->getBody(), 0, 150000);
                $headers    = $response->getHeaders();
                $sslVerified = str_starts_with($url, 'https://');
                $results[$key] = $this->buildResult($url, $html, $headers, $httpStatus, $responseMs, $sslVerified, null);
            },
            'rejected'    => function ($reason, $key) use (&$results) {
                $results[$key] = $this->failedResult(null, $reason->getMessage());
            },
        ]);

        $pool->promise()->wait();

        return $results;
    }

    public function scan(string $url): array
    {
        // SSRF protection: only allow public HTTP/HTTPS URLs
        $parsed = parse_url($url);
        if (!in_array($parsed['scheme'] ?? '', ['http', 'https'], true)) {
            return $this->failedResult(0, 'Ungültiges URL-Schema');
        }
        $host = $parsed['host'] ?? '';
        if ($host === '') {
            return $this->failedResult(0, 'Ungültiger Hostname');
        }
        $ip = gethostbyname($host);
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return $this->failedResult(0, 'Interne/private IP-Adressen sind nicht erlaubt');
        }

        $client = new Client([
            'timeout'         => 10,
            'allow_redirects' => ['max' => 5, 'strict' => false, 'referer' => false, 'protocols' => ['http', 'https']],
            'verify'          => true,
        ]);

        $start = microtime(true);

        try {
            $response   = $client->get($url);
            $responseMs = (int) round((microtime(true) - $start) * 1000);
            $httpStatus = $response->getStatusCode();
            $html       = substr((string) $response->getBody(), 0, 150000);
            $headers    = $response->getHeaders();

            return $this->buildResult($url, $html, $headers, $httpStatus, $responseMs, true, $client);

        } catch (ConnectException $e) {
            // SSL-Fehler oder Verbindungsfehler — retry ohne SSL-Verifikation
            if (str_contains($e->getMessage(), 'SSL') || str_contains($e->getMessage(), 'certificate')) {
                try {
                    $retryClient = new Client(['timeout' => 10, 'allow_redirects' => true, 'verify' => false]);
                    $response    = $retryClient->get($url);
                    $responseMs  = (int) round((microtime(true) - $start) * 1000);
                    $httpStatus  = $response->getStatusCode();
                    $html        = substr((string) $response->getBody(), 0, 150000);
                    $headers     = $response->getHeaders();

                    return $this->buildResult($url, $html, $headers, $httpStatus, $responseMs, false, $retryClient);
                } catch (\Exception) {}
            }

            return $this->failedResult((int) round((microtime(true) - $start) * 1000), $e->getMessage());

        } catch (RequestException $e) {
            $responseMs = (int) round((microtime(true) - $start) * 1000);
            $httpStatus = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;

            return $this->failedResult($responseMs, $e->getMessage(), $httpStatus);

        } catch (\Exception $e) {
            return $this->failedResult(null, $e->getMessage());
        }
    }

    // ────────────────────────────────────────────────────────────────────────────
    //  Internal helpers
    // ────────────────────────────────────────────────────────────────────────────

    /**
     * Build a "done" scan result from raw HTTP response data.
     *
     * @param string $url         Original URL that was scanned
     * @param string $html        Truncated HTML body
     * @param array  $headers     Guzzle response headers (['name' => ['value']])
     * @param int    $httpStatus  HTTP status code
     * @param int    $responseMs  Round-trip time in milliseconds
     * @param bool   $sslVerified Whether SSL certificate was successfully verified
     */
    private function buildResult(
        string  $url,
        string  $html,
        array   $headers,
        int     $httpStatus,
        int     $responseMs,
        bool    $sslVerified,
        ?Client $client = null
    ): array {
        $hasSsl      = str_starts_with($url, 'https://') && $sslVerified;
        $isMobile    = (bool) preg_match('/<meta[^>]+viewport/i', $html);
        $hasContact  = (bool) preg_match('/(tel:|mailto:|kontakt|contact)/i', $html);
        $hasImpressum= (bool) preg_match('/(impressum|imprint|legal\s*notice)/i', $html);
        $hasTracking = (bool) preg_match('/(gtag|_ga|fbq|analytics|hotjar|matomo|plausible)/i', $html);
        $hasFavicon  = (bool) preg_match('/<link[^>]+rel=["\'](?:shortcut )?icon/i', $html);
        $cms         = $this->detectCms($html);

        preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $titleMatch);
        $pageTitle = isset($titleMatch[1])
            ? trim(html_entity_decode(strip_tags($titleMatch[1]), ENT_QUOTES, 'UTF-8'))
            : null;
        if ($pageTitle && strlen($pageTitle) > 500) $pageTitle = substr($pageTitle, 0, 500);

        preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)["\']|<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']description["\']/i', $html, $descMatch);
        $metaDesc = trim($descMatch[1] ?? $descMatch[2] ?? '');
        $metaDesc = $metaDesc ? html_entity_decode($metaDesc, ENT_QUOTES, 'UTF-8') : null;
        if ($metaDesc && strlen($metaDesc) > 1000) $metaDesc = substr($metaDesc, 0, 1000);

        $hasOgTags   = (bool) preg_match('/<meta[^>]+property=["\']og:title["\']/i', $html)
                    && (bool) preg_match('/<meta[^>]+property=["\']og:image["\']/i', $html);
        $hasGoogleMaps = (bool) preg_match('/maps\.google\.com|maps\.googleapis\.com/i', $html);

        $altTagRatio      = $this->calcAltTagRatio($html);
        // Skip broken-link checks in batch mode (client is null) to avoid 5 extra requests per site.
        $brokenLinksCount = $client ? $this->checkBrokenLinks($html, $url) : null;
        $speedRating      = $responseMs < 1000 ? 'schnell' : ($responseMs <= 3000 ? 'mittel' : 'langsam');
        $score            = $this->calcScore($httpStatus, $hasSsl, $isMobile, $hasContact, $hasImpressum, $hasFavicon);

        // ── New: DSGVO checks ──
        $googleFontsSource   = $this->checkGoogleFonts($html);
        $cookieBannerResult  = $this->checkCookieBanner($html);
        $legalPagesResult    = $this->checkLegalPages($html);
        // has_cookie_banner remains a bool for backwards compat
        $hasCookieBanner     = $cookieBannerResult['status'] === 'found';

        // ── robots.txt (skip in batch mode) ──
        $robotsResult = $client ? $this->checkRobotsTxt($url, $client) : ['exists' => null, 'disallow_all' => null];

        // ── New: Security & Performance ──
        $securityResult      = $this->checkSecurityHeaders($headers);
        $outdatedTech        = $this->checkOutdatedTech($headers, $html);
        $performanceScores   = $this->computePerformanceScores($responseMs, $html);

        return [
            'status'             => 'done',
            'http_status'        => $httpStatus,
            'response_ms'        => $responseMs,
            'has_ssl'            => $hasSsl,
            'is_mobile'          => $isMobile,
            'cms'                => $cms,
            'page_title'         => $pageTitle ?: null,
            'meta_description'   => $metaDesc ?: null,
            'has_contact'        => $hasContact,
            'has_impressum'      => $hasImpressum,
            'has_tracking'       => $hasTracking,
            'has_favicon'        => $hasFavicon,
            'has_og_tags'        => $hasOgTags,
            'has_google_maps'    => $hasGoogleMaps,
            'has_cookie_banner'  => $hasCookieBanner,
            'alt_tag_ratio'      => $altTagRatio,
            'broken_links_count' => $brokenLinksCount,
            'speed_rating'       => $speedRating,
            'score'              => $score,
            'error'              => null,
            'scanned_at'         => now(),
            // DSGVO
            'google_fonts_source'  => $googleFontsSource,
            'cookie_banner_tool'   => $cookieBannerResult['tool'],
            'cookie_banner_status' => $cookieBannerResult['status'],
            'impressum_location'   => $legalPagesResult['impressum'],
            'datenschutz_location' => $legalPagesResult['datenschutz'],
            // robots.txt
            'robots_txt_exists'    => $robotsResult['exists'],
            'robots_txt_disallow'  => $robotsResult['disallow_all'],
            // Security
            'security_headers_score'  => $securityResult['score'],
            'security_headers_detail' => $securityResult['detail'],
            // Outdated tech
            'outdated_tech'           => $outdatedTech,
            // Performance
            'ttfb_score'              => $performanceScores['ttfb'],
            'lcp_score'               => $performanceScores['lcp'],
            'cls_score'               => $performanceScores['cls'],
        ];
    }

    /**
     * Build a standard "failed" scan result.
     */
    private function failedResult(?int $responseMs, string $error, ?int $httpStatus = null): array
    {
        return [
            'status'             => 'failed',
            'http_status'        => $httpStatus,
            'response_ms'        => $responseMs,
            'has_ssl'            => null,
            'is_mobile'          => null,
            'cms'                => null,
            'page_title'         => null,
            'meta_description'   => null,
            'has_contact'        => null,
            'has_impressum'      => null,
            'has_tracking'       => null,
            'has_favicon'        => null,
            'has_og_tags'        => null,
            'has_google_maps'    => null,
            'has_cookie_banner'  => null,
            'alt_tag_ratio'      => null,
            'broken_links_count' => null,
            'speed_rating'       => null,
            'score'              => 0,
            'error'              => $error,
            'scanned_at'         => now(),
            // Advanced fields — null on failure
            'google_fonts_source'     => null,
            'cookie_banner_tool'      => null,
            'cookie_banner_status'    => null,
            'impressum_location'      => null,
            'datenschutz_location'    => null,
            'robots_txt_exists'       => null,
            'robots_txt_disallow'     => null,
            'security_headers_score'  => null,
            'security_headers_detail' => null,
            'outdated_tech'           => null,
            'ttfb_score'              => null,
            'lcp_score'               => null,
            'cls_score'               => null,
        ];
    }

    // ────────────────────────────────────────────────────────────────────────────
    //  robots.txt check
    // ────────────────────────────────────────────────────────────────────────────

    /**
     * Fetch and analyse robots.txt for the given URL.
     *
     * @param string $url    Scanned page URL (used to derive the root)
     * @param Client $client Existing Guzzle client instance
     * @return array{exists: bool, disallow_all: bool}
     */
    public function checkRobotsTxt(string $url, Client $client): array
    {
        $parsed   = parse_url($url);
        $root     = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');
        $robotsUrl = rtrim($root, '/') . '/robots.txt';

        try {
            $response = $client->get($robotsUrl, ['timeout' => 5, 'http_errors' => false]);
            $status   = $response->getStatusCode();

            if ($status !== 200) {
                return ['exists' => false, 'disallow_all' => false];
            }

            $body       = (string) $response->getBody();
            // Check for Disallow: / for all agents (blocks all crawlers)
            $disallowAll = (bool) preg_match('/User-agent:\s*\*.*?Disallow:\s*\//is', $body);

            return ['exists' => true, 'disallow_all' => $disallowAll];

        } catch (\Throwable) {
            return ['exists' => false, 'disallow_all' => false];
        }
    }

    // ────────────────────────────────────────────────────────────────────────────
    //  Task 1: DSGVO checks
    // ────────────────────────────────────────────────────────────────────────────

    /**
     * Detect whether Google Fonts are loaded locally or from external CDN.
     *
     * @return string 'local'|'external'|'unknown'
     */
    public function checkGoogleFonts(string $html): string
    {
        // External: link to fonts.googleapis.com or @import from fonts.googleapis.com
        if (preg_match('/fonts\.googleapis\.com/i', $html)) {
            return 'external';
        }

        // Local: font files served from own domain (common local-font keywords)
        // Detect Google Fonts served locally via @font-face with common Google Font names
        if (
            preg_match('/@font-face/i', $html) &&
            preg_match('/\.(woff2?|ttf|otf|eot)/i', $html) &&
            !preg_match('/fonts\.googleapis\.com/i', $html)
        ) {
            return 'local';
        }

        // No fonts at all detectable
        return 'unknown';
    }

    /**
     * Detect cookie consent tool presence and name.
     *
     * @return array{tool: string|null, status: 'found'|'not_found'}
     */
    public function checkCookieBanner(string $html): array
    {
        $tools = [
            'Cookiebot'          => '/cookiebot/i',
            'Complianz'          => '/complianz/i',
            'Real Cookie Banner' => '/real[\s-]?cookie[\s-]?banner/i',
            'Borlabs Cookie'     => '/borlabs[\s-]?cookie/i',
            'CookieYes'          => '/cookieyes/i',
            'Usercentrics'       => '/usercentrics/i',
            'OneTrust'           => '/onetrust/i',
            'Klaro'              => '/klaro\.js|klaro-config/i',
            'Cookie Notice'      => '/cookie[\s-]?notice/i',
            'Cookie Consent'     => '/cookieconsent|cookie[\s-]?consent/i',
            'Cookie Banner'      => '/cookie[\s-]?banner/i',
        ];

        foreach ($tools as $name => $pattern) {
            if (preg_match($pattern, $html)) {
                return ['tool' => $name, 'status' => 'found'];
            }
        }

        return ['tool' => null, 'status' => 'not_found'];
    }

    /**
     * Check where Impressum and Datenschutz links appear on the page.
     *
     * @return array{impressum: 'menu'|'footer'|'not_found', datenschutz: 'menu'|'footer'|'not_found'}
     */
    public function checkLegalPages(string $html): array
    {
        $dom = new \DOMDocument();
        libxml_use_internal_errors(true);
        @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);
        libxml_clear_errors();

        $impressum   = 'not_found';
        $datenschutz = 'not_found';

        // Extract footer HTML block for scoped searching
        $footerHtml = '';
        $navHtml    = '';

        $footerNodes = $dom->getElementsByTagName('footer');
        foreach ($footerNodes as $node) {
            $footerHtml .= $dom->saveHTML($node);
        }

        // nav elements + header nav
        foreach (['nav', 'header'] as $tag) {
            $nodes = $dom->getElementsByTagName($tag);
            foreach ($nodes as $node) {
                $navHtml .= $dom->saveHTML($node);
            }
        }

        // Patterns for matching legal page links
        $impressumPattern   = '/(impressum|imprint|legal[\s\-]?notice)/i';
        $datenschutzPattern = '/(datenschutz|datenschutzerkl|privacy[\s\-]?policy|privacy)/i';

        // Check Impressum location: nav first, then footer, then anywhere
        if (preg_match($impressumPattern, $navHtml)) {
            $impressum = 'menu';
        } elseif (preg_match($impressumPattern, $footerHtml)) {
            $impressum = 'footer';
        } elseif (preg_match($impressumPattern, $html)) {
            // Found somewhere on the page but location unknown (not in nav/footer)
            $impressum = 'found';
        }

        // Check Datenschutz location
        if (preg_match($datenschutzPattern, $navHtml)) {
            $datenschutz = 'menu';
        } elseif (preg_match($datenschutzPattern, $footerHtml)) {
            $datenschutz = 'footer';
        } elseif (preg_match($datenschutzPattern, $html)) {
            $datenschutz = 'found';
        }

        return ['impressum' => $impressum, 'datenschutz' => $datenschutz];
    }

    // ────────────────────────────────────────────────────────────────────────────
    //  Task 2: Security & Performance checks
    // ────────────────────────────────────────────────────────────────────────────

    /**
     * Check important HTTP security headers.
     * Scores: each present header adds 1 point, max 4.
     *
     * @param array $headers Guzzle response headers (['header-name' => ['value']])
     * @return array{score: int, detail: array<string, bool>}
     */
    public function checkSecurityHeaders(array $headers): array
    {
        // Normalize header keys to lowercase for consistent lookup
        $normalized = [];
        foreach ($headers as $name => $values) {
            $normalized[strtolower($name)] = $values;
        }

        $checks = [
            'X-Frame-Options'           => 'x-frame-options',
            'X-Content-Type-Options'    => 'x-content-type-options',
            'Strict-Transport-Security' => 'strict-transport-security',
            'Content-Security-Policy'   => 'content-security-policy',
        ];

        $detail = [];
        $score  = 0;

        foreach ($checks as $displayName => $headerKey) {
            $present = array_key_exists($headerKey, $normalized);
            $detail[$displayName] = $present;
            if ($present) {
                $score++;
            }
        }

        return ['score' => $score, 'detail' => $detail];
    }

    /**
     * Detect outdated or risky technologies from headers and HTML.
     *
     * @param array  $headers Guzzle response headers
     * @param string $html    Page HTML
     * @return array<string> List of outdated tech strings found
     */
    public function checkOutdatedTech(array $headers, string $html): array
    {
        $found = [];

        // Normalize headers
        $normalized = [];
        foreach ($headers as $name => $values) {
            $normalized[strtolower($name)] = implode(', ', $values);
        }

        // PHP version from X-Powered-By header (e.g. "PHP/5.6.40")
        $poweredBy = $normalized['x-powered-by'] ?? '';
        if (preg_match('/PHP\/(5\.|7\.[0-3])/i', $poweredBy, $m)) {
            $found[] = 'PHP ' . $m[1] . 'x (veraltet)';
        }

        // Server header revealing old software versions (Apache 2.2, nginx <1.14, IIS <10)
        $server = $normalized['server'] ?? '';
        if (preg_match('/Apache\/2\.2\./i', $server)) {
            $found[] = 'Apache 2.2 (veraltet)';
        }
        if (preg_match('/nginx\/1\.(0|1[0-3])\./i', $server)) {
            $found[] = 'nginx (veraltet)';
        }
        if (preg_match('/Microsoft-IIS\/(7|8|9)\./i', $server)) {
            $found[] = 'IIS ' . preg_replace('/.*IIS\/(\d+)\..*/i', '$1', $server) . ' (veraltet)';
        }

        // jQuery old versions in HTML (1.x or 2.x)
        if (preg_match('/jquery[\.\-]?v?(1\.|2\.)/i', $html)) {
            $found[] = 'jQuery 1.x/2.x (veraltet)';
        }

        // Bootstrap 2 or 3
        if (preg_match('/bootstrap[\.\-]?v?(2\.|3\.)/i', $html)) {
            $found[] = 'Bootstrap 2/3 (veraltet)';
        }

        // Flash content
        if (preg_match('/(application\/x-shockwave-flash|\.swf["\'>\s])/i', $html)) {
            $found[] = 'Flash (nicht mehr unterstützt)';
        }

        // IE-only conditional comments
        if (preg_match('/<!--\[if\s+(?:lt\s+)?IE\s+\d+\]/i', $html)) {
            $found[] = 'IE-Conditional Comments';
        }

        return array_values(array_unique($found));
    }

    /**
     * Compute rough performance score strings from response time and HTML analysis.
     * These are heuristic approximations; real Core Web Vitals require browser measurement.
     *
     * @param int    $responseMs Server response time in milliseconds
     * @param string $html       Page HTML content
     * @return array{ttfb: string, lcp: string, cls: string}
     */
    public function computePerformanceScores(int $responseMs, string $html): array
    {
        // TTFB based on raw server response time
        // Good: < 200ms, Average: 200–600ms, Poor: > 600ms (Google thresholds)
        if ($responseMs < 200) {
            $ttfb = 'gut';
        } elseif ($responseMs <= 600) {
            $ttfb = 'mittel';
        } else {
            $ttfb = 'schlecht';
        }

        // LCP heuristic: approximate page weight and render-blocking resources
        // Signals: inline images, large hero images, many render-blocking scripts
        $renderBlockingScripts = preg_match_all('/<script[^>]+src=["\'][^"\']+["\'][^>]*>/i', $html) ?: 0;
        $largeImgCount         = preg_match_all('/<img[^>]+(width|height)=["\']?[3-9]\d{2,}["\']?/i', $html) ?: 0;
        $hasLazyLoad           = (bool) preg_match('/loading=["\']lazy["\']/i', $html);

        // Each render-blocking script and large image degrades LCP
        $lcpPenalty = ($renderBlockingScripts * 1) + ($largeImgCount * 2);
        if ($hasLazyLoad) {
            $lcpPenalty = max(0, $lcpPenalty - 2); // lazy load reduces LCP penalty
        }

        if ($lcpPenalty <= 2 && $responseMs < 300) {
            $lcp = 'gut';
        } elseif ($lcpPenalty <= 6 && $responseMs <= 800) {
            $lcp = 'mittel';
        } else {
            $lcp = 'schlecht';
        }

        // CLS heuristic: images without explicit width/height cause layout shift
        preg_match_all('/<img[^>]*>/i', $html, $allImgs);
        $totalImgs    = count($allImgs[0]);
        $imgsWithSize = 0;
        foreach ($allImgs[0] as $tag) {
            if (preg_match('/width=["\']?\d+["\']?/i', $tag) && preg_match('/height=["\']?\d+["\']?/i', $tag)) {
                $imgsWithSize++;
            }
        }
        $sizeRatio = $totalImgs > 0 ? ($imgsWithSize / $totalImgs) : 1.0;

        // Also check for ads/embeds that commonly cause CLS
        $hasAds = (bool) preg_match('/google_ad|doubleclick|adsbygoogle/i', $html);

        if ($sizeRatio >= 0.8 && !$hasAds) {
            $cls = 'gut';
        } elseif ($sizeRatio >= 0.5 && !$hasAds) {
            $cls = 'mittel';
        } else {
            $cls = 'schlecht';
        }

        return ['ttfb' => $ttfb, 'lcp' => $lcp, 'cls' => $cls];
    }

    // ────────────────────────────────────────────────────────────────────────────
    //  Existing private helpers (unchanged)
    // ────────────────────────────────────────────────────────────────────────────

    private function calcAltTagRatio(string $html): ?float
    {
        preg_match_all('/<img[^>]*>/i', $html, $allImgs);
        $total = count($allImgs[0]);
        if ($total === 0) return null;

        $withAlt = 0;
        foreach ($allImgs[0] as $tag) {
            if (preg_match('/\balt=["\']([^"\']+)["\']/', $tag)) {
                $withAlt++;
            }
        }

        return round($withAlt / $total, 2);
    }

    private function checkBrokenLinks(string $html, string $baseUrl): int
    {
        preg_match_all('/<a[^>]+href=["\']([^"\'#?][^"\']*)["\'][^>]*>/i', $html, $matches);
        $parsed = parse_url($baseUrl);
        $base   = ($parsed['scheme'] ?? 'https') . '://' . ($parsed['host'] ?? '');

        $checked = 0;
        $broken  = 0;
        $client  = new Client(['timeout' => 3, 'allow_redirects' => true, 'verify' => false]);

        foreach ($matches[1] as $href) {
            if ($checked >= 5) break;

            // Only check internal links
            if (str_starts_with($href, 'http') && !str_contains($href, $parsed['host'] ?? '')) continue;
            if (str_starts_with($href, 'mailto:') || str_starts_with($href, 'tel:')) continue;

            $fullUrl = str_starts_with($href, 'http') ? $href : rtrim($base, '/') . '/' . ltrim($href, '/');

            try {
                $res = $client->head($fullUrl);
                if ($res->getStatusCode() >= 400) $broken++;
            } catch (\Throwable) {
                $broken++;
            }
            $checked++;
        }

        return $broken;
    }

    private function detectCms(string $html): ?string
    {
        if (preg_match('/wp-content|wp-includes|wordpress/i', $html)) return 'WordPress';
        if (preg_match('/joomla/i', $html)) return 'Joomla';
        if (preg_match('/drupal/i', $html)) return 'Drupal';
        if (preg_match('/shopify/i', $html)) return 'Shopify';
        if (preg_match('/wix\.com/i', $html)) return 'Wix';
        if (preg_match('/typo3/i', $html)) return 'TYPO3';
        if (preg_match('/squarespace/i', $html)) return 'Squarespace';
        if (preg_match('/webflow/i', $html)) return 'Webflow';
        return null;
    }

    private function calcScore(
        ?int $httpStatus,
        bool $hasSsl,
        bool $isMobile,
        bool $hasContact,
        bool $hasImpressum,
        bool $hasFavicon
    ): int {
        $w = $this->weights;
        $score = 0;
        if ($httpStatus === 200) $score += $w['http_ok']   ?? 0;
        if ($hasSsl)             $score += $w['ssl']       ?? 0;
        if ($isMobile)           $score += $w['mobile']    ?? 0;
        if ($hasContact)         $score += $w['contact']   ?? 0;
        if ($hasImpressum)       $score += $w['impressum'] ?? 0;
        if ($hasFavicon)         $score += $w['favicon']   ?? 0;
        return $score;
    }
}
