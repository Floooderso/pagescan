<!DOCTYPE html>
<html lang="de" style="height:100%;">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PageScan — Website-Scanner für Agenturen</title>
  <meta name="description" content="Finde Unternehmen mit schlechten Websites. Branche und Stadt eingeben, fertig.">
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css'])
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      font-family: 'Inter', system-ui, -apple-system, sans-serif;
      background: #09090b;
      color: #fff;
      -webkit-font-smoothing: antialiased;
    }

    a { text-decoration: none; color: inherit; }

    /* ── Nav ── */
    .nav {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 32px;
      height: 52px;
      border-bottom: 1px solid #27272a;
    }
    .nav-brand { font-size: 15px; font-weight: 600; letter-spacing: -0.2px; }
    .nav-right { display: flex; align-items: center; gap: 8px; }
    .nav-login {
      font-size: 13px;
      color: #a1a1aa;
      padding: 6px 14px;
      border-radius: 6px;
      transition: color .15s;
    }
    .nav-login:hover { color: #fff; }
    .nav-cta {
      font-size: 13px;
      font-weight: 500;
      background: #fff;
      color: #09090b;
      padding: 6px 14px;
      border-radius: 6px;
      transition: background .15s;
    }
    .nav-cta:hover { background: #e4e4e7; }

    /* ── Hero ── */
    .hero {
      max-width: 1100px;
      margin: 0 auto;
      padding: 80px 32px 72px;
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 64px;
      align-items: center;
    }
    .hero-tag {
      display: inline-block;
      font-size: 11px;
      font-weight: 500;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #52525b;
      margin-bottom: 20px;
    }
    .hero h1 {
      font-size: clamp(1.75rem, 3.5vw, 2.75rem);
      font-weight: 700;
      line-height: 1.18;
      letter-spacing: -0.5px;
      color: #fff;
      margin-bottom: 18px;
    }
    .hero h1 em {
      font-style: normal;
      color: #52525b;
    }
    .hero-desc {
      font-size: 15px;
      color: #71717a;
      line-height: 1.7;
      margin-bottom: 32px;
    }
    .hero-actions { display: flex; align-items: center; gap: 12px; flex-wrap: wrap; }
    .btn-hero {
      display: inline-block;
      font-size: 14px;
      font-weight: 500;
      background: #fff;
      color: #09090b;
      padding: 10px 22px;
      border-radius: 7px;
      transition: background .15s;
    }
    .btn-hero:hover { background: #e4e4e7; }
    .hero-note { font-size: 12px; color: #3f3f46; }

    /* ── Mock UI ── */
    .mock {
      background: #18181b;
      border: 1px solid #27272a;
      border-radius: 10px;
      overflow: hidden;
      font-size: 13px;
    }
    .mock-bar {
      padding: 10px 14px;
      border-bottom: 1px solid #27272a;
      display: flex;
      align-items: center;
      gap: 6px;
    }
    .mock-dot { width: 8px; height: 8px; border-radius: 50%; flex-shrink: 0; }
    .mock-label { font-size: 11px; color: #52525b; margin-left: 4px; }
    .mock-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 10px 14px;
      border-bottom: 1px solid #27272a;
      gap: 12px;
    }
    .mock-row:last-child { border-bottom: none; }
    .mock-name { font-size: 12px; font-weight: 500; color: #fafafa; }
    .mock-meta { font-size: 11px; color: #52525b; margin-top: 1px; }
    .score-pill { font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 4px; flex-shrink: 0; }
    .score-red    { background: rgba(220,38,38,.15);  color: #f87171; }
    .score-yellow { background: rgba(202,138,4,.15);  color: #fbbf24; }
    .score-green  { background: rgba(22,163,74,.15);  color: #4ade80; }

    /* ── Sections ── */
    .section {
      max-width: 1100px;
      margin: 0 auto;
      padding: 0 32px 80px;
    }
    .section-title {
      font-size: 11px;
      font-weight: 500;
      letter-spacing: .08em;
      text-transform: uppercase;
      color: #52525b;
      margin-bottom: 28px;
    }
    .divider { border: none; border-top: 1px solid #27272a; margin: 0 32px; }

    /* ── Steps grid (2×2) ── */
    .steps {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 1px;
      background: #27272a;
      border: 1px solid #27272a;
      border-radius: 10px;
      overflow: hidden;
    }
    .step {
      background: #09090b;
      padding: 24px;
    }
    .step:hover { background: #18181b; }
    .step-num { font-size: 11px; color: #3f3f46; margin-bottom: 12px; font-variant-numeric: tabular-nums; }
    .step-title { font-size: 14px; font-weight: 500; margin-bottom: 6px; }
    .step-desc { font-size: 13px; color: #71717a; line-height: 1.6; }

    /* ── Criteria + Context (side by side) ── */
    .criteria-split {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 48px;
      align-items: start;
    }
    .criteria { display: flex; flex-direction: column; }
    .criterion {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 12px 0;
      border-bottom: 1px solid #27272a;
      font-size: 13px;
    }
    .criterion:first-child { border-top: 1px solid #27272a; }
    .criterion-name { color: #d4d4d8; }
    .criterion-pts { color: #3f3f46; font-variant-numeric: tabular-nums; }
    .criteria-note { font-size: 12px; color: #3f3f46; margin-top: 12px; line-height: 1.6; }
    .context-list { display: flex; flex-direction: column; gap: 20px; }
    .context-item { display: flex; gap: 14px; align-items: flex-start; }
    .context-num { font-size: 11px; color: #3f3f46; padding-top: 2px; flex-shrink: 0; font-variant-numeric: tabular-nums; }
    .context-title { font-size: 13px; font-weight: 500; margin-bottom: 4px; }
    .context-desc { font-size: 13px; color: #71717a; line-height: 1.6; }

    /* ── Pricing ── */
    .pricing-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 1px;
      background: #27272a;
      border: 1px solid #27272a;
      border-radius: 10px;
      overflow: hidden;
    }
    .plan {
      background: #09090b;
      padding: 28px 24px;
    }
    .plan:hover { background: #111113; }
    .plan-name {
      font-size: 12px;
      font-weight: 500;
      letter-spacing: .06em;
      text-transform: uppercase;
      color: #52525b;
      margin-bottom: 16px;
    }
    .plan-price {
      font-size: 32px;
      font-weight: 700;
      letter-spacing: -1px;
      margin-bottom: 4px;
    }
    .plan-period { font-size: 13px; font-weight: 400; color: #52525b; }
    .plan-divider { border: none; border-top: 1px solid #27272a; margin: 20px 0; }
    .plan-features { list-style: none; display: flex; flex-direction: column; gap: 8px; margin-bottom: 24px; }
    .plan-features li { font-size: 13px; color: #71717a; }
    .plan-features li strong { color: #d4d4d8; font-weight: 500; }
    .plan-btn {
      display: block;
      text-align: center;
      font-size: 13px;
      font-weight: 500;
      padding: 9px 0;
      border-radius: 6px;
      border: 1px solid #27272a;
      color: #a1a1aa;
      transition: border-color .15s, color .15s;
    }
    .plan-btn:hover { border-color: #52525b; color: #fff; }
    .plan-btn-primary {
      background: #fff;
      color: #09090b;
      border-color: #fff;
    }
    .plan-btn-primary:hover { background: #e4e4e7; border-color: #e4e4e7; color: #09090b; }
    .plan-highlight { background: #18181b; }

    /* ── Footer ── */
    .footer {
      border-top: 1px solid #27272a;
      padding: 24px 32px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
      gap: 12px;
    }
    .footer-copy { font-size: 12px; color: #3f3f46; }
    .footer-links { display: flex; gap: 20px; }
    .footer-links a { font-size: 12px; color: #3f3f46; transition: color .15s; }
    .footer-links a:hover { color: #71717a; }

    @media (max-width: 900px) {
      .hero { grid-template-columns: 1fr; gap: 40px; padding: 56px 24px 48px; }
      .mock { display: none; }
      .criteria-split { grid-template-columns: 1fr; gap: 32px; }
    }
    @media (max-width: 640px) {
      .nav { padding: 0 20px; }
      .section { padding: 0 20px 60px; }
      .steps { grid-template-columns: 1fr; }
      .pricing-grid { grid-template-columns: 1fr; }
      .footer { padding: 20px; }
      .divider { margin: 0 20px; }
    }
  </style>
</head>
<body>

<nav class="nav">
  <span class="nav-brand">PageScan</span>
  <div class="nav-right">
    <a href="#preise" class="nav-login">Preise</a>
    <a href="{{ route('login') }}" class="nav-login">Anmelden</a>
    <a href="{{ route('register') }}" class="nav-cta">Kostenlos testen</a>
  </div>
</nav>

<div class="hero">
  <div>
    <span class="hero-tag">Website-Scanner für Agenturen &amp; Freelancer</span>
    <h1>Finde Unternehmen mit <em>schlechten Websites.</em></h1>
    <p class="hero-desc">
      Branche und Stadt eingeben — PageScan sucht via Google Places, scannt jede Website live und zeigt dir wer einen neuen Webauftritt braucht.
    </p>
    <div class="hero-actions">
      <a href="{{ route('register') }}" class="btn-hero">10 Scans gratis starten</a>
      <span class="hero-note">Keine Kreditkarte nötig</span>
    </div>
  </div>

  <div class="mock">
    <div class="mock-bar">
      <div class="mock-dot" style="background:#ef4444;"></div>
      <div class="mock-dot" style="background:#f59e0b;"></div>
      <div class="mock-dot" style="background:#22c55e;"></div>
      <span class="mock-label">Friseure München — 5 Ergebnisse</span>
    </div>
    <div class="mock-row">
      <div><div class="mock-name">Salon Bella Vista</div><div class="mock-meta">Schwabing · WordPress · kein SSL</div></div>
      <span class="score-pill score-red">28/100</span>
    </div>
    <div class="mock-row">
      <div><div class="mock-name">Hairstyle by Marco</div><div class="mock-meta">Maxvorstadt · keine Website</div></div>
      <span class="score-pill score-red">—</span>
    </div>
    <div class="mock-row">
      <div><div class="mock-name">Friseur am Dom</div><div class="mock-meta">Altstadt · Wix · kein Impressum</div></div>
      <span class="score-pill score-yellow">41/100</span>
    </div>
    <div class="mock-row">
      <div><div class="mock-name">HairLab Studio</div><div class="mock-meta">Bogenhausen · nicht mobil</div></div>
      <span class="score-pill score-yellow">55/100</span>
    </div>
    <div class="mock-row">
      <div><div class="mock-name">Coiffeur Deluxe</div><div class="mock-meta">Neuhausen · TYPO3 · 0,8s</div></div>
      <span class="score-pill score-green">82/100</span>
    </div>
  </div>
</div>

<hr class="divider">

<div class="section" style="padding-top:64px;">
  <div class="section-title">So funktioniert's</div>
  <div class="steps">
    <div class="step">
      <div class="step-num">01</div>
      <div class="step-title">Branche + Stadt eingeben</div>
      <div class="step-desc">„Friseure München" oder „Steuerberater Hamburg" — PageScan zieht alle Einträge direkt aus Google Places.</div>
    </div>
    <div class="step">
      <div class="step-num">02</div>
      <div class="step-title">Websites werden live gescannt</div>
      <div class="step-desc">Jede gefundene Website bekommt einen Score von 0–100 auf Basis von SSL, Mobile, Ladezeit, Impressum und mehr.</div>
    </div>
    <div class="step">
      <div class="step-num">03</div>
      <div class="step-title">Leads speichern und verwalten</div>
      <div class="step-desc">Interessante Unternehmen als Lead markieren, Status tracken, Notizen hinterlegen — ohne externes CRM.</div>
    </div>
    <div class="step">
      <div class="step-num">04</div>
      <div class="step-title">Watchlist automatisch laufen lassen</div>
      <div class="step-desc">Suchen speichern, täglich automatisch wiederholen. Neue Treffer landen sofort in deiner Lead-Liste.</div>
    </div>
  </div>
</div>

<hr class="divider">

<div class="section" style="padding-top:64px;">
  <div class="section-title">Was gescannt wird</div>
  <div class="criteria-split">
    <div class="criteria">
      <div class="criterion">
        <span class="criterion-name">Erreichbarkeit (HTTP 200)</span>
        <span class="criterion-pts">30 Pkt</span>
      </div>
      <div class="criterion">
        <span class="criterion-name">SSL / HTTPS</span>
        <span class="criterion-pts">20 Pkt</span>
      </div>
      <div class="criterion">
        <span class="criterion-name">Mobile-Optimierung</span>
        <span class="criterion-pts">20 Pkt</span>
      </div>
      <div class="criterion">
        <span class="criterion-name">Kontaktmöglichkeit</span>
        <span class="criterion-pts">10 Pkt</span>
      </div>
      <div class="criterion">
        <span class="criterion-name">Impressum vorhanden</span>
        <span class="criterion-pts">10 Pkt</span>
      </div>
      <div class="criterion">
        <span class="criterion-name">Favicon</span>
        <span class="criterion-pts">10 Pkt</span>
      </div>
      <p class="criteria-note">Zusätzlich erkannt (ohne Punktwertung): CMS, OG-Tags, Cookie-Banner, Tracking-Pixel, Alt-Tag-Ratio, Broken Links, Ladezeit-Rating</p>
    </div>
    <div class="context-list">
      <div class="context-item">
        <span class="context-num">—</span>
        <div>
          <div class="context-title">Gesprächseinstieg mit Zahlen</div>
          <div class="context-desc">„Ihre Website hat 32 von 100 Punkten — kein SSL, nicht mobil-optimiert." Das öffnet Türen schneller als jeder Kaltanruf ohne Vorbereitung.</div>
        </div>
      </div>
      <div class="context-item">
        <span class="context-num">—</span>
        <div>
          <div class="context-title">Gezieltes Angebot statt Raten</div>
          <div class="context-desc">Du siehst vor dem ersten Kontakt was fehlt. WordPress ohne SSL, kein Impressum, keine Kontaktseite — dein Angebot passt von Anfang an.</div>
        </div>
      </div>
      <div class="context-item">
        <span class="context-num">—</span>
        <div>
          <div class="context-title">Gewichtung anpassbar</div>
          <div class="context-desc">SEO-Agentur? Webdesign-Freelancer? Lokale Vermarktung? Passe die Kriterien-Gewichtung in deinem Profil an — der Score rechnet sich automatisch neu.</div>
        </div>
      </div>
    </div>
  </div>
</div>

<hr class="divider">

<div class="section" id="preise" style="padding-top:64px; max-width:1100px;">
  <div class="section-title">Preise</div>
  <div class="pricing-grid">

    <div class="plan">
      <div class="plan-name">Starter</div>
      <div class="plan-price">€29<span class="plan-period">/Monat</span></div>
      <hr class="plan-divider">
      <ul class="plan-features">
        <li><strong>100 Scans</strong> pro Monat</li>
        <li><strong>1 Nutzer</strong></li>
        <li>Lead-Management</li>
        <li>Watchlist & Auto-Scan</li>
        <li>CSV-Export</li>
      </ul>
      <a href="{{ route('register') }}" class="plan-btn">Jetzt starten</a>
    </div>

    <div class="plan plan-highlight">
      <div class="plan-name">Agency</div>
      <div class="plan-price">€79<span class="plan-period">/Monat</span></div>
      <hr class="plan-divider">
      <ul class="plan-features">
        <li><strong>500 Scans</strong> pro Monat</li>
        <li><strong>5 Nutzer</strong></li>
        <li>Lead-Management</li>
        <li>Watchlist & Auto-Scan</li>
        <li>Team-Features</li>
        <li>CSV-Export</li>
      </ul>
      <a href="{{ route('register') }}" class="plan-btn plan-btn-primary">14 Tage kostenlos</a>
    </div>

    <div class="plan">
      <div class="plan-name">Pro</div>
      <div class="plan-price">€149<span class="plan-period">/Monat</span></div>
      <hr class="plan-divider">
      <ul class="plan-features">
        <li><strong>Unbegrenzte Scans</strong></li>
        <li><strong>Unbegrenzte Nutzer</strong></li>
        <li>Lead-Management</li>
        <li>Watchlist & Auto-Scan</li>
        <li>Team-Features</li>
        <li>Prioritäts-Support</li>
      </ul>
      <a href="{{ route('register') }}" class="plan-btn">Jetzt starten</a>
    </div>

  </div>
</div>

<footer class="footer">
  <span class="footer-copy">© {{ date('Y') }} PageScan</span>
  <div class="footer-links">
    <a href="{{ route('impressum') }}">Impressum</a>
    <a href="{{ route('datenschutz') }}">Datenschutz</a>
  </div>
</footer>

</body>
</html>
