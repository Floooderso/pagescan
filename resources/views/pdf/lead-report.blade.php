<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
  <title>Lead-Report: {{ $company->name }}</title>
  <style>
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      font-family: DejaVu Sans, Arial, sans-serif;
      font-size: 11px;
      color: #1a1a2e;
      background: #fff;
      padding: 30px 36px;
    }
    /* Header */
    .header {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      margin-bottom: 24px;
      padding-bottom: 14px;
      border-bottom: 3px solid #18181b;
    }
    .header-title { font-size: 22px; font-weight: 700; color: #18181b; }
    .header-sub   { font-size: 12px; color: #71717a; margin-top: 4px; }
    .header-meta  { font-size: 10px; color: #a1a1aa; text-align: right; }

    /* Section */
    .section { margin-bottom: 20px; }
    .section-title {
      font-size: 10px;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      color: #71717a;
      margin-bottom: 8px;
      padding-bottom: 4px;
      border-bottom: 1px solid #e4e4e7;
    }

    /* Grid */
    .grid-2 { width: 100%; border-collapse: collapse; }
    .grid-2 td { width: 50%; padding: 4px 0; vertical-align: top; }
    .label { font-size: 10px; color: #71717a; }
    .value { font-size: 11px; color: #18181b; font-weight: 500; }

    /* Chips */
    .chip {
      display: inline-block;
      padding: 2px 8px;
      border-radius: 20px;
      font-size: 10px;
      font-weight: 600;
      margin: 2px 2px 2px 0;
    }
    .chip-ok     { background: #f0fdf4; color: #16a34a; }
    .chip-fail   { background: #fef2f2; color: #dc2626; }
    .chip-warn   { background: #fefce8; color: #ca8a04; }
    .chip-neutral{ background: #f4f4f5; color: #52525b; }

    /* Score pill */
    .score-pill {
      display: inline-block;
      padding: 4px 14px;
      border-radius: 24px;
      font-size: 16px;
      font-weight: 700;
    }
    .score-good   { background: #f0fdf4; color: #16a34a; }
    .score-medium { background: #fefce8; color: #ca8a04; }
    .score-bad    { background: #fef2f2; color: #dc2626; }

    /* Risk badge */
    .risk-critical { background: #fef2f2; color: #dc2626; }
    .risk-warning  { background: #fefce8; color: #ca8a04; }
    .risk-ok       { background: #f0fdf4; color: #16a34a; }

    /* Table */
    .data-table { width: 100%; border-collapse: collapse; font-size: 10px; }
    .data-table th {
      background: #f4f4f5;
      padding: 5px 8px;
      text-align: left;
      font-weight: 600;
      color: #52525b;
      border: 1px solid #e4e4e7;
    }
    .data-table td {
      padding: 5px 8px;
      border: 1px solid #e4e4e7;
      color: #18181b;
      vertical-align: top;
    }
    .data-table tr:nth-child(even) td { background: #fafafa; }

    /* Security header row */
    .hdr-present { color: #16a34a; font-weight: 600; }
    .hdr-missing { color: #dc2626; font-weight: 600; }

    /* Notes */
    .notes-box {
      background: #fafafa;
      border: 1px solid #e4e4e7;
      border-radius: 6px;
      padding: 10px 12px;
      font-size: 11px;
      color: #3f3f46;
      line-height: 1.5;
      white-space: pre-wrap;
    }

    /* Footer */
    .footer {
      margin-top: 30px;
      padding-top: 10px;
      border-top: 1px solid #e4e4e7;
      font-size: 9px;
      color: #a1a1aa;
      text-align: center;
    }
  </style>
</head>
<body>

  <!-- ── Header ────────────────────────────────────────────────────── -->
  <div class="header">
    <div>
      <div class="header-title">{{ $company->name }}</div>
      <div class="header-sub">
        {{ $company->branche ?? '' }}{{ $company->branche && $company->stadt ? ' · ' : '' }}{{ $company->stadt ?? '' }}
      </div>
    </div>
    <div class="header-meta">
      Lead-Report<br>
      Erstellt: {{ now()->format('d.m.Y') }}<br>
      Status: {{ ucfirst($lead->status) }}<br>
      Priorität: {{ ucfirst($lead->priority) }}
    </div>
  </div>

  <!-- ── Firmendaten ────────────────────────────────────────────────── -->
  <div class="section">
    <div class="section-title">Firmendaten</div>
    <table class="grid-2">
      <tr>
        <td>
          <div class="label">Website</div>
          <div class="value">{{ $company->website_url ?? '—' }}</div>
        </td>
        <td>
          <div class="label">Telefon</div>
          <div class="value">{{ $company->telefon ?? '—' }}</div>
        </td>
      </tr>
      <tr>
        <td>
          <div class="label">Adresse</div>
          <div class="value">{{ $company->adresse ?? '—' }}</div>
        </td>
        <td>
          <div class="label">Bewertung</div>
          <div class="value">
            @if($company->rating)
              {{ number_format($company->rating, 1) }} / 5
              ({{ $company->bewertungen ?? 0 }} Bewertungen)
            @else
              —
            @endif
          </div>
        </td>
      </tr>
    </table>
  </div>

  @if($scan)
  <!-- ── Scan-Übersicht ─────────────────────────────────────────────── -->
  <div class="section">
    <div class="section-title">Scan-Übersicht</div>
    <table class="grid-2">
      <tr>
        <td>
          <div class="label">Gesamt-Score</div>
          <div style="margin-top:4px;">
            @php
              $scoreClass = $scan->score >= 70 ? 'score-good' : ($scan->score >= 40 ? 'score-medium' : 'score-bad');
            @endphp
            <span class="score-pill {{ $scoreClass }}">{{ $scan->score }}/100</span>
          </div>
        </td>
        <td>
          <div class="label">Risiko</div>
          <div style="margin-top:4px;">
            @php $risk = $scan->risk_badge; @endphp
            <span class="chip chip-{{ $risk === 'critical' ? 'fail' : ($risk === 'warning' ? 'warn' : 'ok') }}">
              {{ $risk === 'critical' ? 'Kritisch' : ($risk === 'warning' ? 'Warnung' : 'OK') }}
            </span>
          </div>
        </td>
      </tr>
      <tr>
        <td>
          <div class="label">HTTP-Status</div>
          <div class="value">{{ $scan->http_status ?? '—' }}</div>
        </td>
        <td>
          <div class="label">Antwortzeit</div>
          <div class="value">
            {{ $scan->response_ms ? $scan->response_ms . ' ms (' . $scan->speed_rating . ')' : '—' }}
          </div>
        </td>
      </tr>
      <tr>
        <td>
          <div class="label">CMS</div>
          <div class="value">{{ $scan->cms ?? 'Unbekannt' }}</div>
        </td>
        <td>
          <div class="label">Gescannt am</div>
          <div class="value">{{ $scan->scanned_at?->format('d.m.Y H:i') ?? '—' }}</div>
        </td>
      </tr>
    </table>
  </div>

  <!-- ── Technische Checks ──────────────────────────────────────────── -->
  <div class="section">
    <div class="section-title">Technische Checks</div>
    <div>
      <span class="chip {{ $scan->has_ssl ? 'chip-ok' : 'chip-fail' }}">{{ $scan->has_ssl ? '✓' : '✗' }} SSL</span>
      <span class="chip {{ $scan->is_mobile ? 'chip-ok' : 'chip-fail' }}">{{ $scan->is_mobile ? '✓' : '✗' }} Mobile</span>
      <span class="chip {{ $scan->has_impressum ? 'chip-ok' : 'chip-fail' }}">{{ $scan->has_impressum ? '✓' : '✗' }} Impressum</span>
      <span class="chip {{ $scan->has_contact ? 'chip-ok' : 'chip-fail' }}">{{ $scan->has_contact ? '✓' : '✗' }} Kontakt</span>
      <span class="chip {{ $scan->has_tracking ? 'chip-ok' : 'chip-fail' }}">{{ $scan->has_tracking ? '✓' : '✗' }} Tracking</span>
      <span class="chip {{ $scan->has_favicon ? 'chip-ok' : 'chip-fail' }}">{{ $scan->has_favicon ? '✓' : '✗' }} Favicon</span>
      @if($scan->has_og_tags !== null)
        <span class="chip {{ $scan->has_og_tags ? 'chip-ok' : 'chip-fail' }}">{{ $scan->has_og_tags ? '✓' : '✗' }} OG-Tags</span>
      @endif
    </div>
  </div>

  <!-- ── DSGVO-Analyse ──────────────────────────────────────────────── -->
  <div class="section">
    <div class="section-title">DSGVO-Analyse</div>
    <table class="data-table">
      <thead>
        <tr>
          <th>Prüfpunkt</th>
          <th>Ergebnis</th>
          <th>Bewertung</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>Cookie-Banner</td>
          <td>{{ $scan->cookie_banner_tool ?? ($scan->cookie_banner_status === 'found' ? 'Gefunden' : 'Nicht gefunden') }}</td>
          <td>
            @if($scan->cookie_banner_status === 'found')
              <span class="chip chip-ok">OK</span>
            @elseif($scan->cookie_banner_status === 'not_found')
              <span class="chip chip-fail">Fehlt</span>
            @else
              <span class="chip chip-neutral">—</span>
            @endif
          </td>
        </tr>
        <tr>
          <td>Google Fonts</td>
          <td>
            @if($scan->google_fonts_source === 'external') Extern (Google CDN)
            @elseif($scan->google_fonts_source === 'local') Lokal eingebunden
            @else Nicht erkannt
            @endif
          </td>
          <td>
            @if($scan->google_fonts_source === 'external')
              <span class="chip chip-fail">Problem</span>
            @elseif($scan->google_fonts_source === 'local')
              <span class="chip chip-ok">OK</span>
            @else
              <span class="chip chip-neutral">—</span>
            @endif
          </td>
        </tr>
        <tr>
          <td>Impressum</td>
          <td>
            @if($scan->impressum_location === 'menu') Im Navigationsmenü
            @elseif($scan->impressum_location === 'footer') Im Footer
            @elseif($scan->impressum_location === 'not_found') Nicht gefunden
            @else —
            @endif
          </td>
          <td>
            @if($scan->impressum_location === 'not_found')
              <span class="chip chip-fail">Fehlt</span>
            @elseif($scan->impressum_location)
              <span class="chip chip-ok">OK</span>
            @else
              <span class="chip chip-neutral">—</span>
            @endif
          </td>
        </tr>
        <tr>
          <td>Datenschutzerklärung</td>
          <td>
            @if($scan->datenschutz_location === 'menu') Im Navigationsmenü
            @elseif($scan->datenschutz_location === 'footer') Im Footer
            @elseif($scan->datenschutz_location === 'not_found') Nicht gefunden
            @else —
            @endif
          </td>
          <td>
            @if($scan->datenschutz_location === 'not_found')
              <span class="chip chip-fail">Fehlt</span>
            @elseif($scan->datenschutz_location)
              <span class="chip chip-ok">OK</span>
            @else
              <span class="chip chip-neutral">—</span>
            @endif
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- ── Performance ───────────────────────────────────────────────── -->
  <div class="section">
    <div class="section-title">Performance (Heuristik)</div>
    <table class="data-table">
      <thead>
        <tr><th>Metrik</th><th>Wert</th><th>Bewertung</th></tr>
      </thead>
      <tbody>
        @foreach(['ttfb' => 'TTFB (Serverantwort)', 'lcp' => 'LCP (Ladezeit)', 'cls' => 'CLS (Layout-Stabilität)'] as $key => $label)
          @php $val = $scan->{$key . '_score'}; @endphp
          <tr>
            <td>{{ $label }}</td>
            <td>{{ $val ?? '—' }}</td>
            <td>
              @if($val === 'gut')
                <span class="chip chip-ok">Gut</span>
              @elseif($val === 'mittel')
                <span class="chip chip-warn">Mittel</span>
              @elseif($val === 'schlecht')
                <span class="chip chip-fail">Schlecht</span>
              @else
                <span class="chip chip-neutral">—</span>
              @endif
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>

  <!-- ── Security Headers ──────────────────────────────────────────── -->
  @if($scan->security_headers_detail)
  <div class="section">
    <div class="section-title">Sicherheits-Header (Score: {{ $scan->security_headers_score }}/4)</div>
    <table class="data-table">
      <thead>
        <tr><th>Header</th><th>Status</th></tr>
      </thead>
      <tbody>
        @foreach($scan->security_headers_detail as $header => $present)
          <tr>
            <td>{{ $header }}</td>
            <td class="{{ $present ? 'hdr-present' : 'hdr-missing' }}">{{ $present ? 'Vorhanden' : 'Fehlt' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  <!-- ── Veraltete Technologien ─────────────────────────────────────── -->
  @if($scan->outdated_tech && count($scan->outdated_tech) > 0)
  <div class="section">
    <div class="section-title">Veraltete Technologien</div>
    <div>
      @foreach($scan->outdated_tech as $tech)
        <span class="chip chip-fail">{{ $tech }}</span>
      @endforeach
    </div>
  </div>
  @endif

  @else
  <!-- No scan -->
  <div class="section">
    <div class="section-title">Scan-Daten</div>
    <p style="color:#71717a; font-size:11px;">Kein Website-Scan vorhanden.</p>
  </div>
  @endif

  <!-- ── SEO-Info ───────────────────────────────────────────────────── -->
  @if($scan && ($scan->page_title || $scan->meta_description))
  <div class="section">
    <div class="section-title">SEO-Informationen</div>
    @if($scan->page_title)
      <div class="label" style="margin-bottom:2px;">Seitentitel</div>
      <div class="value" style="margin-bottom:8px;">{{ $scan->page_title }}</div>
    @endif
    @if($scan->meta_description)
      <div class="label" style="margin-bottom:2px;">Meta-Beschreibung</div>
      <div class="value">{{ $scan->meta_description }}</div>
    @endif
  </div>
  @endif

  <!-- ── Notizen ───────────────────────────────────────────────────── -->
  @if($lead->notes)
  <div class="section">
    <div class="section-title">Notizen</div>
    <div class="notes-box">{{ $lead->notes }}</div>
  </div>
  @endif

  <!-- ── Kontakthistorie ───────────────────────────────────────────── -->
  @if($lead->contactAttempts && $lead->contactAttempts->count() > 0)
  <div class="section">
    <div class="section-title">Kontakthistorie ({{ $lead->contactAttempts->count() }})</div>
    <table class="data-table">
      <thead>
        <tr><th>Datum</th><th>Kanal</th><th>Notiz</th></tr>
      </thead>
      <tbody>
        @foreach($lead->contactAttempts as $attempt)
          <tr>
            <td>{{ $attempt->kontaktiert_am?->format('d.m.Y') ?? '—' }}</td>
            <td>{{ ucfirst($attempt->kanal) }}</td>
            <td>{{ $attempt->notiz ?? '—' }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif

  <!-- ── Footer ────────────────────────────────────────────────────── -->
  <div class="footer">
    Erstellt von PageScan &middot; {{ now()->format('d.m.Y H:i') }} &middot; {{ $company->website_url ?? '' }}
  </div>

</body>
</html>
