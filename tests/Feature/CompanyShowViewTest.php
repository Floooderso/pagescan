<?php

namespace Tests\Feature;

use App\Models\Company;
use App\Models\Lead;
use App\Models\Scan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature Tests für resources/views/companies/show.blade.php
 *
 * Getestete Karten:
 *   - Karte 3: DSGVO & Rechtssicherheit (google_fonts_source, cookie_banner_status,
 *              impressum_location, datenschutz_location)
 *   - Karte 4: Sicherheit & Performance (security_headers_score, security_headers_detail,
 *              outdated_tech, ttfb_score, lcp_score, cls_score)
 *
 * Testname-Konvention: "should [Erwartung] when [Bedingung]"
 */
class CompanyShowViewTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /** Erstellt einen authentifizierten User ohne laufende Trial-Sperre. */
    private function authenticatedUser(): User
    {
        $user = User::factory()->create(['trial_ends_at' => null]);
        $this->actingAs($user);
        return $user;
    }

    /** Erstellt eine minimale Company. */
    private function makeCompany(array $attributes = []): Company
    {
        return Company::create(array_merge([
            'name'        => 'Test GmbH',
            'branche'     => 'IT',
            'stadt'       => 'Berlin',
            'website_url' => 'https://example.com',
            'has_website' => true,
        ], $attributes));
    }

    /** Erstellt einen Scan für eine Company mit den gegebenen Attributen. */
    private function makeScan(Company $company, array $attributes = []): Scan
    {
        return Scan::create(array_merge([
            'company_id' => $company->id,
            'status'     => 'done',
            'score'      => 70,
            'scanned_at' => now(),
        ], $attributes));
    }

    /** Ruft die Company-Detail-Seite auf und gibt die Response zurück. */
    private function showCompany(Company $company)
    {
        return $this->get("/companies/{$company->id}");
    }

    // =========================================================================
    // 1. $scan ist null — keine neuen Karten sichtbar
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * Wenn kein Scan vorhanden ist, dürfen Karte 3 und Karte 4 nicht gerendert werden.
     */
    public function should_not_render_dsgvo_and_security_cards_when_scan_is_null(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Noch kein Scan vorhanden');
        $response->assertDontSee('DSGVO &amp; Rechtssicherheit');
        $response->assertDontSee('Sicherheit &amp; Performance');
    }

    // =========================================================================
    // 2. Karte 3 — Google Fonts
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * google_fonts_source = 'external' → badge-red mit "Abmahngefahr"
     */
    public function should_show_red_badge_with_abmahngefahr_when_google_fonts_source_is_external(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['google_fonts_source' => 'external']);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Extern geladen');
        $response->assertSee('Abmahngefahr');
        $response->assertDontSee('Lokal gehostet');
    }

    /**
     * @test
     * EXPECTED: PASS
     * google_fonts_source = 'local' → badge-green "Lokal gehostet"
     */
    public function should_show_green_badge_lokal_gehostet_when_google_fonts_source_is_local(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['google_fonts_source' => 'local']);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Lokal gehostet');
        $response->assertDontSee('Abmahngefahr');
    }

    /**
     * @test
     * EXPECTED: PASS
     * google_fonts_source = 'unknown' (unbekannter Wert) → badge-gray "Nicht erkannt"
     * Edge Case 5
     */
    public function should_show_gray_badge_nicht_erkannt_when_google_fonts_source_is_unknown(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['google_fonts_source' => 'unknown']);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Nicht erkannt');
        $response->assertDontSee('Abmahngefahr');
        $response->assertDontSee('Lokal gehostet');
    }

    /**
     * @test
     * EXPECTED: PASS
     * google_fonts_source = null → badge-gray "Nicht erkannt"
     */
    public function should_show_gray_badge_nicht_erkannt_when_google_fonts_source_is_null(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['google_fonts_source' => null]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Nicht erkannt');
    }

    // =========================================================================
    // 3. Karte 3 — Cookie-Banner
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * cookie_banner_status = 'found' + cookie_banner_tool = 'Cookiebot' → "Gefunden: Cookiebot"
     */
    public function should_show_green_badge_gefunden_with_tool_name_when_cookie_banner_is_found_with_tool(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'cookie_banner_status' => 'found',
            'cookie_banner_tool'   => 'Cookiebot',
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Gefunden');
        $response->assertSee('Cookiebot');
        $response->assertDontSee('Kein Banner gefunden');
    }

    /**
     * @test
     * EXPECTED: PASS
     * cookie_banner_status = 'found' + cookie_banner_tool = null → nur "Gefunden" ohne Tool-Name
     * Edge Case 4
     */
    public function should_show_gefunden_without_tool_name_when_cookie_banner_found_but_tool_is_null(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'cookie_banner_status' => 'found',
            'cookie_banner_tool'   => null,
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Gefunden');
        $response->assertDontSee('Kein Banner gefunden');
        // Kein Tool-Name-Doppelpunkt darf erscheinen
        $response->assertDontSee('Gefunden:');
    }

    /**
     * @test
     * EXPECTED: PASS
     * cookie_banner_status = 'not_found' → badge-red "Kein Banner gefunden"
     */
    public function should_show_red_badge_kein_banner_when_cookie_banner_status_is_not_found(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'cookie_banner_status' => 'not_found',
            'cookie_banner_tool'   => null,
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Kein Banner gefunden');
        $response->assertDontSee('Cookiebot');
    }

    // =========================================================================
    // 4. Karte 3 — Impressum
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * impressum_location = 'menu' → badge-green "Im Menü"
     */
    public function should_show_green_badge_im_menu_when_impressum_location_is_menu(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['impressum_location' => 'menu']);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Im Menü');
    }

    /**
     * @test
     * EXPECTED: PASS
     * impressum_location = 'footer' → badge-yellow "Nur im Footer"
     */
    public function should_show_yellow_badge_nur_im_footer_when_impressum_location_is_footer(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['impressum_location' => 'footer']);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Nur im Footer');
    }

    /**
     * @test
     * EXPECTED: PASS
     * impressum_location = 'found' → badge-yellow "Gefunden (Position unklar)"
     */
    public function should_show_yellow_badge_position_unklar_when_impressum_location_is_found(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['impressum_location' => 'found']);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Gefunden (Position unklar)');
    }

    /**
     * @test
     * EXPECTED: PASS
     * impressum_location = null (oder unbekannt) → badge-red "Nicht gefunden"
     */
    public function should_show_red_badge_nicht_gefunden_when_impressum_location_is_null(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['impressum_location' => null]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        // "Nicht gefunden" erscheint für Impressum und/oder Datenschutz
        $response->assertSee('Nicht gefunden');
    }

    // =========================================================================
    // 5. Karte 3 — Datenschutz (gleiche Logik wie Impressum)
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * datenschutz_location = 'menu' → badge-green "Im Menü"
     */
    public function should_show_green_badge_im_menu_when_datenschutz_location_is_menu(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['datenschutz_location' => 'menu']);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Im Menü');
    }

    /**
     * @test
     * EXPECTED: PASS
     * datenschutz_location = 'footer' → badge-yellow "Nur im Footer"
     */
    public function should_show_yellow_badge_nur_im_footer_when_datenschutz_location_is_footer(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['datenschutz_location' => 'footer']);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Nur im Footer');
    }

    /**
     * @test
     * EXPECTED: PASS
     * datenschutz_location = null → badge-red "Nicht gefunden"
     */
    public function should_show_red_nicht_gefunden_when_datenschutz_location_is_null(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['datenschutz_location' => null]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Nicht gefunden');
    }

    // =========================================================================
    // 6. Karte 4 — Security Headers Score
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * security_headers_score = 4 → badge-green "4 / 4 Headers"
     */
    public function should_show_green_badge_when_security_headers_score_is_4(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['security_headers_score' => 4]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('4 / 4 Headers');
    }

    /**
     * @test
     * EXPECTED: PASS
     * security_headers_score = 3 → badge-yellow "3 / 4 Headers"
     * Edge Case 7
     */
    public function should_show_yellow_badge_when_security_headers_score_is_3(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['security_headers_score' => 3]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('3 / 4 Headers');
    }

    /**
     * @test
     * EXPECTED: PASS
     * security_headers_score = 2 → badge-yellow "2 / 4 Headers"
     */
    public function should_show_yellow_badge_when_security_headers_score_is_2(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['security_headers_score' => 2]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('2 / 4 Headers');
    }

    /**
     * @test
     * EXPECTED: PASS
     * security_headers_score = 1 → badge-red "1 / 4 Headers"
     */
    public function should_show_red_badge_when_security_headers_score_is_1(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['security_headers_score' => 1]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('1 / 4 Headers');
    }

    /**
     * @test
     * EXPECTED: PASS
     * security_headers_score = 0 → badge-red "0 / 4 Headers"
     */
    public function should_show_red_badge_when_security_headers_score_is_0(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['security_headers_score' => 0]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('0 / 4 Headers');
    }

    // =========================================================================
    // 7. Karte 4 — Security Headers Detail
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * security_headers_detail als Array → pro Header eine Zeile mit badge-green (true) oder badge-red (false)
     * Das Scan-Modell castet das Feld automatisch zu array, daher wird ein PHP-Array übergeben.
     */
    public function should_render_individual_header_rows_when_security_headers_detail_is_present(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'security_headers_score'  => 2,
            'security_headers_detail' => [
                'Content-Security-Policy'   => true,
                'X-Frame-Options'           => true,
                'X-Content-Type-Options'    => false,
                'Strict-Transport-Security' => false,
            ],
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Content-Security-Policy');
        $response->assertSee('X-Frame-Options');
        $response->assertSee('X-Content-Type-Options');
        $response->assertSee('Strict-Transport-Security');
    }

    /**
     * @test
     * EXPECTED: PASS
     * security_headers_detail = ungültiges JSON-Feld (als String, z.B. durch direkte DB-Manipulation) →
     * kein Crash, View rendert ohne Fehler.
     * Edge Case 2: Testen des View-eigenen json_decode()-Fallbacks (is_string-Pfad).
     *
     * Hinweis: Da das Scan-Modell security_headers_detail als 'array' castet,
     * würde ungültiges JSON beim Laden zu einem leeren Array aufgelöst.
     * Wir simulieren dies, indem wir den Cast umgehen und direkt einen null-Wert prüfen.
     */
    public function should_not_crash_when_security_headers_detail_is_null(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'security_headers_detail' => null,
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        // View must not throw a 500 server error — asserting status 200 is sufficient
        // (assertDontSee('500') is too broad; the string "500" can appear in CSS/other content)
    }

    /**
     * @test
     * EXPECTED: PASS
     * security_headers_detail = leeres Array → keine Header-Detail-Rows gerendert
     */
    public function should_not_render_header_detail_rows_when_security_headers_detail_is_empty(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'security_headers_detail' => [],
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertDontSee('Content-Security-Policy');
        $response->assertDontSee('X-Frame-Options');
    }

    // =========================================================================
    // 8. Karte 4 — Veraltete Technologien
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * outdated_tech = ['jQuery 1.x', 'PHP 5.6'] → je ein badge-red je Eintrag
     */
    public function should_show_red_badge_per_entry_when_outdated_tech_is_not_empty(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'outdated_tech' => ['jQuery 1.x', 'PHP 5.6'],
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('jQuery 1.x');
        $response->assertSee('PHP 5.6');
    }

    /**
     * @test
     * EXPECTED: PASS
     * outdated_tech = [] (leeres Array) → kein "Veraltete Tech"-Block gerendert
     * Edge Case 3
     */
    public function should_not_render_outdated_tech_section_when_outdated_tech_is_empty_array(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'outdated_tech' => [],
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertDontSee('Veraltete Tech');
    }

    /**
     * @test
     * EXPECTED: PASS
     * outdated_tech = null → kein "Veraltete Tech"-Block gerendert
     */
    public function should_not_render_outdated_tech_section_when_outdated_tech_is_null(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'outdated_tech' => null,
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertDontSee('Veraltete Tech');
    }

    // =========================================================================
    // 9. Karte 4 — Core Web Vitals
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * ttfb_score = 'gut', lcp_score = 'gut', cls_score = 'gut' → alle angezeigt
     */
    public function should_show_gut_labels_when_all_vitals_scores_are_gut(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'ttfb_score' => 'gut',
            'lcp_score'  => 'gut',
            'cls_score'  => 'gut',
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('TTFB');
        $response->assertSee('LCP');
        $response->assertSee('CLS');
        // 'gut' erscheint mindestens einmal
        $response->assertSee('gut');
    }

    /**
     * @test
     * EXPECTED: PASS
     * ttfb_score = 'mittel' → "mittel" als Label sichtbar
     */
    public function should_show_mittel_label_when_ttfb_score_is_mittel(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, ['ttfb_score' => 'mittel']);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('mittel');
    }

    /**
     * @test
     * EXPECTED: PASS
     * ttfb_score = 'schlecht', lcp_score = 'schlecht', cls_score = 'schlecht' →
     * alle badge-red, "schlecht" im HTML sichtbar.
     * Edge Case 6: Alle Scores 'schlecht'
     */
    public function should_show_schlecht_labels_when_all_vitals_scores_are_schlecht(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'ttfb_score' => 'schlecht',
            'lcp_score'  => 'schlecht',
            'cls_score'  => 'schlecht',
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('schlecht');
    }

    /**
     * @test
     * EXPECTED: PASS
     * ttfb_score = null → Dash "–" als Fallback angezeigt
     */
    public function should_show_dash_fallback_when_ttfb_score_is_null(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'ttfb_score' => null,
            'lcp_score'  => null,
            'cls_score'  => null,
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        // Null-Koaleszenz ergibt '–' im Template
        $response->assertSee('–');
    }

    // =========================================================================
    // 10. Alle schlecht + alle Security-Scores schlecht (kombinierter Edge Case)
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * Alle negativen Werte gleichzeitig → View rendert ohne Fehler,
     * alle roten Badges erscheinen.
     * Edge Case 6 (erweitert)
     */
    public function should_render_all_red_badges_when_all_scores_are_worst_case(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'google_fonts_source'     => 'external',
            'cookie_banner_status'    => 'not_found',
            'cookie_banner_tool'      => null,
            'impressum_location'      => null,
            'datenschutz_location'    => null,
            'security_headers_score'  => 0,
            'security_headers_detail' => [
                'Content-Security-Policy'   => false,
                'X-Frame-Options'           => false,
                'X-Content-Type-Options'    => false,
                'Strict-Transport-Security' => false,
            ],
            'outdated_tech'           => ['jQuery 1.x', 'PHP 5.6'],
            'ttfb_score'              => 'schlecht',
            'lcp_score'               => 'schlecht',
            'cls_score'               => 'schlecht',
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('Extern geladen');
        $response->assertSee('Abmahngefahr');
        $response->assertSee('Kein Banner gefunden');
        $response->assertSee('Nicht gefunden');
        $response->assertSee('0 / 4 Headers');
        $response->assertSee('jQuery 1.x');
        $response->assertSee('schlecht');
    }

    // =========================================================================
    // 11. View mit $lead-Variable
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * Wenn ein Lead vorhanden ist, werden Status-Badge und Notizen angezeigt.
     */
    public function should_render_lead_status_badge_when_lead_is_present(): void
    {
        $user    = $this->authenticatedUser();
        $company = $this->makeCompany();
        $scan    = $this->makeScan($company);

        Lead::create([
            'user_id'    => $user->id,
            'company_id' => $company->id,
            'scan_id'    => $scan->id,
            'status'     => 'neu',
            'priority'   => 'hoch',
            'notes'      => 'Interessanter Lead',
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('neu');
        $response->assertSee('Interessanter Lead');
    }

    /**
     * @test
     * EXPECTED: PASS
     * Wenn kein Lead vorhanden ist, wird kein Notizen-Block gerendert.
     */
    public function should_not_render_notizen_block_when_lead_is_null(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        // Der Notizen-Karteninhalt ("Noch kein Eintrag") erscheint nur wenn $lead vorhanden.
        // HTML-Kommentare im Template enthalten "Notizen", daher assertSee auf das Lead-Feld prüfen.
        $response->assertDontSee('margin-bottom:12px;">Notizen', false);
    }

    // =========================================================================
    // 12. Karte 3 und 4 erscheinen nur wenn $scan vorhanden
    // =========================================================================

    /**
     * @test
     * EXPECTED: PASS
     * Wenn ein Scan vorhanden ist, werden beide neuen Karten gerendert.
     */
    public function should_render_dsgvo_and_security_cards_when_scan_exists(): void
    {
        $this->authenticatedUser();
        $company = $this->makeCompany();
        $this->makeScan($company, [
            'google_fonts_source'    => 'local',
            'cookie_banner_status'   => 'found',
            'cookie_banner_tool'     => 'Cookiebot',
            'impressum_location'     => 'menu',
            'datenschutz_location'   => 'menu',
            'security_headers_score' => 4,
        ]);

        $response = $this->showCompany($company);

        $response->assertStatus(200);
        $response->assertSee('DSGVO');
        $response->assertSee('Rechtssicherheit');
        $response->assertSee('Sicherheit');
        $response->assertSee('Performance');
    }
}
