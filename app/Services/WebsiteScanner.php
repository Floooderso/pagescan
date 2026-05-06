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

    public function scan(string $url): array
    {
        $client = new Client([
            'timeout'         => 10,
            'allow_redirects' => true,
            'verify'          => true,
        ]);

        $start = microtime(true);

        try {
            $response = $client->get($url);
            $responseMs = (int) round((microtime(true) - $start) * 1000);
            $httpStatus = $response->getStatusCode();
            $html = substr((string) $response->getBody(), 0, 150000);

            $hasSsl = str_starts_with($url, 'https://');
            $isMobile = (bool) preg_match('/<meta[^>]+viewport/i', $html);
            $hasContact = (bool) preg_match('/(tel:|mailto:|kontakt|contact)/i', $html);
            $hasImpressum = (bool) preg_match('/(impressum|imprint|legal\s*notice)/i', $html);
            $hasTracking = (bool) preg_match('/(gtag|_ga|fbq|analytics|hotjar|matomo|plausible)/i', $html);
            $hasFavicon = (bool) preg_match('/<link[^>]+rel=["\'](?:shortcut )?icon/i', $html);
            $cms = $this->detectCms($html);

            preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $titleMatch);
            $pageTitle = isset($titleMatch[1]) ? trim(html_entity_decode(strip_tags($titleMatch[1]), ENT_QUOTES, 'UTF-8')) : null;
            if ($pageTitle && strlen($pageTitle) > 500) $pageTitle = substr($pageTitle, 0, 500);

            preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)["\']|<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']description["\']/i', $html, $descMatch);
            $metaDesc = trim($descMatch[1] ?? $descMatch[2] ?? '');
            $metaDesc = $metaDesc ? html_entity_decode($metaDesc, ENT_QUOTES, 'UTF-8') : null;
            if ($metaDesc && strlen($metaDesc) > 1000) $metaDesc = substr($metaDesc, 0, 1000);

            // Extended checks
            $hasOgTags      = (bool) preg_match('/<meta[^>]+property=["\']og:title["\']/i', $html)
                           && (bool) preg_match('/<meta[^>]+property=["\']og:image["\']/i', $html);
            $hasGoogleMaps  = (bool) preg_match('/maps\.google\.com|maps\.googleapis\.com/i', $html);
            $hasCookieBanner= (bool) preg_match('/cookieconsent|cookie-banner|cookiebot|complianz|real\s*cookie\s*banner/i', $html);
            $altTagRatio    = $this->calcAltTagRatio($html);
            $brokenLinksCount = $this->checkBrokenLinks($html, $url);
            $speedRating    = $responseMs < 1000 ? 'schnell' : ($responseMs <= 3000 ? 'mittel' : 'langsam');

            $score = $this->calcScore($httpStatus, $hasSsl, $isMobile, $hasContact, $hasImpressum, $hasFavicon);

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
            ];
        } catch (ConnectException $e) {
            // SSL-Fehler oder Verbindungsfehler — retry ohne SSL-Verifikation
            if (str_contains($e->getMessage(), 'SSL') || str_contains($e->getMessage(), 'certificate')) {
                try {
                    $retryClient = new Client(['timeout' => 10, 'allow_redirects' => true, 'verify' => false]);
                    $response    = $retryClient->get($url);
                    $responseMs  = (int) round((microtime(true) - $start) * 1000);
                    $httpStatus  = $response->getStatusCode();
                    $html        = substr((string) $response->getBody(), 0, 150000);
                    $hasSsl      = false;
                    $isMobile    = (bool) preg_match('/<meta[^>]+viewport/i', $html);
                    $hasContact  = (bool) preg_match('/(tel:|mailto:|kontakt|contact)/i', $html);
                    $hasImpressum= (bool) preg_match('/(impressum|imprint|legal\s*notice)/i', $html);
                    $hasTracking = (bool) preg_match('/(gtag|_ga|fbq|analytics|hotjar|matomo|plausible)/i', $html);
                    $hasFavicon  = (bool) preg_match('/<link[^>]+rel=["\'](?:shortcut )?icon/i', $html);
                    $cms         = $this->detectCms($html);
                    preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $tm);
                    $pageTitle   = isset($tm[1]) ? substr(trim(html_entity_decode(strip_tags($tm[1]), ENT_QUOTES, 'UTF-8')), 0, 500) : null;
                    preg_match('/<meta[^>]+name=["\']description["\'][^>]+content=["\']([^"\']*)["\']|<meta[^>]+content=["\']([^"\']*)["\'][^>]+name=["\']description["\']/i', $html, $dm);
                    $metaDesc    = isset($dm[1]) || isset($dm[2]) ? substr(html_entity_decode(trim($dm[1] ?? $dm[2] ?? ''), ENT_QUOTES, 'UTF-8'), 0, 1000) : null;
                    $score       = $this->calcScore($httpStatus, $hasSsl, $isMobile, $hasContact, $hasImpressum, $hasFavicon);
                    $hasOgTags2  = (bool) preg_match('/<meta[^>]+property=["\']og:title["\']/i', $html) && (bool) preg_match('/<meta[^>]+property=["\']og:image["\']/i', $html);
                    $speedRating2 = $responseMs < 1000 ? 'schnell' : ($responseMs <= 3000 ? 'mittel' : 'langsam');
                    return ['status' => 'done', 'http_status' => $httpStatus, 'response_ms' => $responseMs, 'has_ssl' => false, 'is_mobile' => $isMobile, 'cms' => $cms, 'page_title' => $pageTitle ?: null, 'meta_description' => $metaDesc ?: null, 'has_contact' => $hasContact, 'has_impressum' => $hasImpressum, 'has_tracking' => $hasTracking, 'has_favicon' => $hasFavicon, 'has_og_tags' => $hasOgTags2, 'has_google_maps' => (bool) preg_match('/maps\.google\.com|maps\.googleapis\.com/i', $html), 'has_cookie_banner' => (bool) preg_match('/cookieconsent|cookie-banner|cookiebot|complianz|real\s*cookie\s*banner/i', $html), 'alt_tag_ratio' => $this->calcAltTagRatio($html), 'broken_links_count' => null, 'speed_rating' => $speedRating2, 'score' => $score, 'error' => null, 'scanned_at' => now()];
                } catch (\Exception) {}
            }
            return ['status' => 'failed', 'http_status' => null, 'response_ms' => (int) round((microtime(true) - $start) * 1000), 'has_ssl' => null, 'is_mobile' => null, 'cms' => null, 'page_title' => null, 'meta_description' => null, 'has_contact' => null, 'has_impressum' => null, 'has_tracking' => null, 'has_favicon' => null, 'has_og_tags' => null, 'has_google_maps' => null, 'has_cookie_banner' => null, 'alt_tag_ratio' => null, 'broken_links_count' => null, 'speed_rating' => null, 'score' => 0, 'error' => $e->getMessage(), 'scanned_at' => now()];
        } catch (RequestException $e) {
            $responseMs = (int) round((microtime(true) - $start) * 1000);
            $httpStatus = $e->hasResponse() ? $e->getResponse()->getStatusCode() : null;

            return [
                'status'             => 'failed',
                'http_status'        => $httpStatus,
                'response_ms'        => $responseMs,
                'has_ssl'            => null,
                'is_mobile'          => null,
                'cms'                => null,
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
                'error'              => $e->getMessage(),
                'scanned_at'         => now(),
            ];
        } catch (\Exception $e) {
            return [
                'status'             => 'failed',
                'http_status'        => null,
                'response_ms'        => null,
                'has_ssl'            => null,
                'is_mobile'          => null,
                'cms'                => null,
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
                'error'              => $e->getMessage(),
                'scanned_at'         => now(),
            ];
        }
    }

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
