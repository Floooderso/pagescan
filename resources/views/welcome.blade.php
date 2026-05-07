<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PageScan — Website-Scanner für Agenturen</title>
  <meta name="description" content="PageScan scannt Websites lokaler Unternehmen live und bewertet sie 0–100. Branche und Stadt eingeben — in 60 Sekunden eine vollständige Lead-Liste mit konkreten Schwachstellen.">
  <link rel="canonical" href="{{ url('/') }}">
  <meta property="og:type"        content="website">
  <meta property="og:title"       content="PageScan — Finde Unternehmen mit schlechten Websites">
  <meta property="og:description" content="Branche und Stadt eingeben — PageScan scannt Websites live, bewertet sie 0–100 und liefert dir täglich neue Akquise-Leads.">
  <meta property="og:url"         content="{{ url('/') }}">
  <meta name="twitter:card"       content="summary_large_image">
  <meta name="twitter:title"      content="PageScan — Website-Scanner für Agenturen">
  <meta name="twitter:description"content="Branche und Stadt eingeben — PageScan scannt Websites live und liefert dir täglich neue Leads.">
  <link rel="apple-touch-icon" sizes="180x180" href="/favicon.svg">
  <meta property="og:image" content="{{ url('/og-image.png') }}">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Geist:wght@400;500;600;700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css'])
  <style>
    /* ── Steps Row ─────────────────────────────────────────── */
    .steps-row {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      position: relative;
    }
    .steps-row::before {
      content: '';
      position: absolute;
      top: 13px;
      left: 28px;
      right: 28px;
      height: 1px;
      background: #e4e4e7;
      z-index: 0;
    }
    .step-v2 { padding: 0 32px 0 0; }
    .step-v2-num {
      width: 28px; height: 28px;
      border: 1px solid #d4d4d8;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      font-size: 11px; color: #71717a;
      margin-bottom: 16px;
      background: #fff;
      position: relative; z-index: 1;
    }
    .step-v2-title { font-size: 13px; font-weight: 500; color: #09090b; margin-bottom: 6px; }
    .step-v2-desc  { font-size: 12px; color: #71717a; line-height: 1.65; }

    /* ── Criteria Card ─────────────────────────────────────── */
    .criteria-card {
      background: #f9fafb;
      border: 1px solid #e4e4e7;
      border-radius: 10px;
      padding: 24px;
    }

    /* ── Pricing Grid ──────────────────────────────────────── */
    .pricing-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
    }
    .plan {
      background: #f9fafb;
      border: 1px solid #e4e4e7;
      border-radius: 10px;
      padding: 40px;
      display: flex;
      flex-direction: column;
    }
    .plan-highlight { border-color: #a1a1aa; }

    /* ── Pulse animation (Live-Dot) ───────────────────────── */
    @keyframes pulse {
      0%, 100% { opacity: 1; transform: scale(1); }
      50%       { opacity: 0.5; transform: scale(0.85); }
    }

    /* ── Responsive ────────────────────────────────────────── */
    /* Bottom-border for last two FAQ items in 2-col layout */
    #faq-grid > div:nth-last-child(-n+2) {
      border-bottom: 1px solid #f4f4f5;
    }
    @media (max-width: 900px) {
      .steps-row       { grid-template-columns: 1fr 1fr; }
      .steps-row::before { display: none; }
    }
    @media (max-width: 768px) {
      #faq-grid { grid-template-columns: 1fr !important; }
      #faq-grid > div:last-child { border-bottom: 1px solid #f4f4f5; }
      #faq-grid > div:nth-last-child(2) { border-bottom: none; }
    }
    @media (max-width: 640px) {
      .steps-row    { grid-template-columns: 1fr; }
      .pricing-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body class="bg-white text-zinc-900 antialiased" style="font-family:'Geist',system-ui,sans-serif;">

  {{-- ── 1. NAV ── --}}
  <nav class="sticky top-0 z-50 bg-white border-b border-zinc-100 flex items-center gap-2 px-8 h-14">
    <span class="font-semibold text-sm text-zinc-900 mr-4">PageScan</span>
    <a href="#schritte" class="text-sm text-zinc-400 hover:text-zinc-700 px-3 py-1.5 rounded-md transition-colors">So funktioniert's</a>
    <a href="#preise"   class="text-sm text-zinc-400 hover:text-zinc-700 px-3 py-1.5 rounded-md transition-colors">Preise</a>
    <div class="flex-1"></div>
    <a href="{{ route('login') }}"    class="text-sm text-zinc-500 hover:text-zinc-900 px-4 py-1.5 rounded-md border border-zinc-200 hover:border-zinc-400 transition-colors mr-2">Anmelden</a>
    <a href="{{ route('register') }}" class="text-sm font-medium text-white bg-zinc-900 hover:bg-zinc-700 px-4 py-1.5 rounded-md transition-colors">Kostenlos testen</a>
  </nav>

  {{-- ── 2. HERO ── --}}
  <section class="py-28 text-center">
    <div class="max-w-6xl mx-auto px-6">
      <h1 class="hero-item opacity-0 translate-y-3 text-6xl font-bold tracking-tight leading-[1.05] text-zinc-900">
        Finde Unternehmen mit<br>schlechten Websites.
      </h1>
      <p class="hero-item opacity-0 translate-y-3 mt-6 text-lg text-zinc-500 leading-relaxed max-w-xl mx-auto">
        Branche und Stadt eingeben — PageScan findet via Google Places alle passenden Unternehmen, scannt deren Websites live und bewertet sie. Täglich automatisch.
      </p>
      <div class="hero-item opacity-0 translate-y-3 mt-10 flex items-center justify-center gap-4 flex-wrap">
        <a href="{{ route('register') }}"
           class="bg-zinc-900 hover:bg-zinc-700 text-white text-sm font-medium px-6 py-3 rounded-lg transition-colors">
          10 Scans gratis starten
        </a>
        <a href="#schritte" class="text-sm text-zinc-400 hover:text-zinc-600 transition-colors">
          So funktioniert's →
        </a>
      </div>
      <p class="hero-item opacity-0 translate-y-3 mt-4 text-xs text-zinc-400">
        Keine Kreditkarte nötig · 14 Tage kostenlos testen
      </p>
    </div>
  </section>

  {{-- ── 3. STATS ── --}}
  <div class="border-t border-b border-zinc-200">
    <div class="max-w-6xl mx-auto px-6">
      <div class="grid grid-cols-3 divide-x divide-zinc-200">

        <div class="px-12 py-10">
          <div class="text-5xl font-bold text-zinc-900 tracking-tight leading-none">
            <span data-counter data-value="200">0</span>
            <span class="text-2xl text-zinc-400 font-normal">+</span>
          </div>
          <div class="text-xs uppercase tracking-widest text-zinc-400 mt-2">Scans im Starter-Plan</div>
          <p class="text-sm text-zinc-500 mt-3">Monatliches Scan-Kontingent im günstigsten Plan</p>
        </div>

        <div class="px-12 py-10">
          <div class="text-5xl font-bold text-zinc-900 tracking-tight leading-none">
            <span data-counter data-value="12">0</span><span>+</span>
          </div>
          <div class="text-xs uppercase tracking-widest text-zinc-400 mt-2">Erkannte Faktoren</div>
          <p class="text-sm text-zinc-500 mt-3">SSL, Mobile, CMS, Impressum, Broken Links und mehr</p>
        </div>

        <div class="px-12 py-10">
          <div class="text-5xl font-bold text-zinc-900 tracking-tight leading-none">
            <span style="display:inline-block; width:8px; height:8px; background:#22c55e; border-radius:50%; margin-right:8px; vertical-align:middle; animation:pulse 2s infinite;"></span>Live
          </div>
          <div class="text-xs uppercase tracking-widest text-zinc-400 mt-2">Echtzeitdaten via Google</div>
          <p class="text-sm text-zinc-500 mt-3">Keine gecachten Daten, immer aktuell</p>
        </div>

      </div>
    </div>
  </div>

  {{-- ── 4. SO FUNKTIONIERT'S ── --}}
  <div id="schritte" class="py-20">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-xs uppercase tracking-widest text-zinc-400 mb-10">So funktioniert's</div>
      <div class="steps-row">

        <div class="step-v2">
          <div class="step-v2-num">01</div>
          <div class="step-v2-title">Branche + Stadt eingeben</div>
          <p class="step-v2-desc">„Friseure München" oder „Steuerberater Hamburg" — PageScan zieht alle Einträge direkt aus Google Places.</p>
        </div>

        <div class="step-v2">
          <div class="step-v2-num">02</div>
          <div class="step-v2-title">Websites werden live gescannt</div>
          <p class="step-v2-desc">Jede Website bekommt einen Score von 0–100 auf Basis von SSL, Mobile, Ladezeit, Impressum und mehr.</p>
        </div>

        <div class="step-v2">
          <div class="step-v2-num">03</div>
          <div class="step-v2-title">Leads speichern und verwalten</div>
          <p class="step-v2-desc">Interessante Unternehmen als Lead markieren, Status tracken, Notizen hinterlegen — ohne externes CRM.</p>
        </div>

        <div class="step-v2">
          <div class="step-v2-num">04</div>
          <div class="step-v2-title">Watchlist automatisch laufen lassen</div>
          <p class="step-v2-desc">Suchen speichern, täglich automatisch wiederholen. Neue Treffer landen sofort in deiner Lead-Liste.</p>
        </div>

      </div>
    </div>
  </div>

  {{-- ── 5. MOCK-SCANNER ── --}}
  <div class="py-20">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-xs uppercase tracking-widest text-zinc-400 mb-8">Echte Ergebnisse, live gescannt</div>
      <div class="border border-zinc-200 rounded-xl overflow-hidden bg-white" style="box-shadow:0 4px 24px rgba(0,0,0,0.05);">

        {{-- Titelleiste --}}
        <div class="px-5 py-3 bg-zinc-50 border-b border-zinc-200 flex items-center justify-between">
          <div class="flex items-center gap-1.5">
            <span class="w-2.5 h-2.5 rounded-full" style="background:#ef4444;"></span>
            <span class="w-2.5 h-2.5 rounded-full" style="background:#f59e0b;"></span>
            <span class="w-2.5 h-2.5 rounded-full" style="background:#22c55e;"></span>
          </div>
          <span class="text-xs text-zinc-400 font-medium">Friseure München</span>
          <span class="text-xs text-zinc-300">5 Ergebnisse</span>
        </div>

        {{-- Spaltenheader --}}
        <div class="grid px-5 py-2.5 border-b border-zinc-100 bg-zinc-50" style="grid-template-columns:1fr auto auto auto;">
          <span class="text-[11px] font-medium text-zinc-400 uppercase tracking-wider">Unternehmen</span>
          <span class="text-[11px] font-medium text-zinc-400 uppercase tracking-wider w-24 text-center">CMS</span>
          <span class="text-[11px] font-medium text-zinc-400 uppercase tracking-wider w-24 text-center">Probleme</span>
          <span class="text-[11px] font-medium text-zinc-400 uppercase tracking-wider w-20 text-right">Score</span>
        </div>

        {{-- Zeilen --}}
        <div class="grid items-center px-5 py-3.5 border-b border-zinc-100 hover:bg-zinc-50 transition-colors" style="grid-template-columns:1fr auto auto auto;">
          <div>
            <div class="text-sm text-zinc-800 font-medium">Salon Bella Vista</div>
            <div class="text-xs text-zinc-400 mt-0.5">Schwabing</div>
          </div>
          <span class="text-xs text-zinc-400 w-24 text-center">WordPress</span>
          <span class="text-xs text-zinc-400 w-24 text-center">kein SSL</span>
          <span class="text-xs font-mono font-semibold bg-red-50 text-red-500 px-2 py-0.5 w-20 text-center">28/100</span>
        </div>

        <div class="grid items-center px-5 py-3.5 border-b border-zinc-100 hover:bg-zinc-50 transition-colors" style="grid-template-columns:1fr auto auto auto;">
          <div>
            <div class="text-sm text-zinc-800 font-medium">Hairstyle by Marco</div>
            <div class="text-xs text-zinc-400 mt-0.5">Maxvorstadt</div>
          </div>
          <span class="text-xs text-zinc-400 w-24 text-center">—</span>
          <span class="text-xs text-zinc-400 w-24 text-center">keine Website</span>
          <span class="text-xs font-mono font-semibold bg-red-50 text-red-500 px-2 py-0.5 w-20 text-center">—</span>
        </div>

        <div class="grid items-center px-5 py-3.5 border-b border-zinc-100 hover:bg-zinc-50 transition-colors" style="grid-template-columns:1fr auto auto auto;">
          <div>
            <div class="text-sm text-zinc-800 font-medium">Friseur am Dom</div>
            <div class="text-xs text-zinc-400 mt-0.5">Altstadt</div>
          </div>
          <span class="text-xs text-zinc-400 w-24 text-center">Wix</span>
          <span class="text-xs text-zinc-400 w-24 text-center">kein Impressum</span>
          <span class="text-xs font-mono font-semibold bg-amber-50 text-amber-500 px-2 py-0.5 w-20 text-center">41/100</span>
        </div>

        <div class="grid items-center px-5 py-3.5 border-b border-zinc-100 hover:bg-zinc-50 transition-colors" style="grid-template-columns:1fr auto auto auto;">
          <div>
            <div class="text-sm text-zinc-800 font-medium">HairLab Studio</div>
            <div class="text-xs text-zinc-400 mt-0.5">Bogenhausen</div>
          </div>
          <span class="text-xs text-zinc-400 w-24 text-center">Squarespace</span>
          <span class="text-xs text-zinc-400 w-24 text-center">nicht mobil</span>
          <span class="text-xs font-mono font-semibold bg-amber-50 text-amber-500 px-2 py-0.5 w-20 text-center">55/100</span>
        </div>

        <div class="grid items-center px-5 py-3.5 hover:bg-zinc-50 transition-colors" style="grid-template-columns:1fr auto auto auto;">
          <div>
            <div class="text-sm text-zinc-800 font-medium">Coiffeur Deluxe</div>
            <div class="text-xs text-zinc-400 mt-0.5">Neuhausen</div>
          </div>
          <span class="text-xs text-zinc-400 w-24 text-center">TYPO3</span>
          <span class="text-xs text-zinc-400 w-24 text-center">—</span>
          <span class="text-xs font-mono font-semibold bg-green-50 text-green-600 px-2 py-0.5 w-20 text-center">82/100</span>
        </div>

      </div>
      <p class="mt-5 text-xs text-zinc-400 text-center">
        Das siehst du nach deiner ersten Suche — in unter 60 Sekunden.
        <a href="{{ route('register') }}" class="text-zinc-600 hover:text-zinc-900 underline underline-offset-2 transition-colors">Jetzt ausprobieren →</a>
      </p>
    </div>
  </div>

  {{-- ── 6. KRITERIEN + ZITAT ── --}}
  <div class="py-20">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-xs uppercase tracking-widest text-zinc-400 mb-8">Was gescannt wird</div>
      <div class="grid grid-cols-2 gap-16 items-start">

        <div class="criteria-card">
          <div class="divide-y divide-zinc-200">
            <div class="flex justify-between py-4 text-sm">
              <span class="text-zinc-500">Erreichbarkeit (HTTP 200)</span>
              <span class="text-zinc-900 font-semibold tabular-nums">30 Pkt</span>
            </div>
            <div class="flex justify-between py-4 text-sm">
              <span class="text-zinc-500">SSL / HTTPS</span>
              <span class="text-zinc-900 font-semibold tabular-nums">20 Pkt</span>
            </div>
            <div class="flex justify-between py-4 text-sm">
              <span class="text-zinc-500">Mobile-Optimierung</span>
              <span class="text-zinc-900 font-semibold tabular-nums">20 Pkt</span>
            </div>
            <div class="flex justify-between py-4 text-sm">
              <span class="text-zinc-500">Kontaktmöglichkeit</span>
              <span class="text-zinc-900 font-semibold tabular-nums">10 Pkt</span>
            </div>
            <div class="flex justify-between py-4 text-sm">
              <span class="text-zinc-500">Impressum vorhanden</span>
              <span class="text-zinc-900 font-semibold tabular-nums">10 Pkt</span>
            </div>
            <div class="flex justify-between py-4 text-sm">
              <span class="text-zinc-500">Favicon</span>
              <span class="text-zinc-900 font-semibold tabular-nums">10 Pkt</span>
            </div>
          </div>
          <p class="text-xs text-zinc-300 mt-6 leading-relaxed">
            Zusätzlich erkannt ohne Wertung: CMS, OG-Tags, Cookie-Banner,
            Tracking-Pixel, Alt-Tags, Broken Links, Ladezeit
          </p>
        </div>

        <div>
          <div class="text-4xl text-zinc-200 font-serif leading-none mb-4">"</div>
          <blockquote class="text-base font-medium text-zinc-800 leading-relaxed mb-6">
            Ihre Website hat 28 von 100 Punkten — kein SSL, nicht mobil,
            kein Impressum. Ich kann Ihnen zeigen wie wir das in zwei Wochen beheben.
          </blockquote>
          <p class="text-xs text-zinc-400 uppercase tracking-widest">So klingt ein Kaltanruf mit PageScan-Daten</p>
          <div class="mt-8 pt-8 border-t border-zinc-100">
            <p class="text-sm text-zinc-500 leading-relaxed">Der Score ist dein Gesprächseinstieg. Du rufst an mit Zahlen, nicht mit Vermutungen — das ist der Unterschied zwischen einem abgewiesenen Kaltanruf und einem vereinbarten Termin.</p>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- ── 7. VORHER VS. NACHHER ── --}}
  <div class="py-20 border-t border-zinc-100">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-xs uppercase tracking-widest text-zinc-400 mb-10">Vorher vs. Nachher</div>
      <div class="grid grid-cols-2 gap-6">

        <div class="bg-zinc-50 border border-zinc-200 rounded-xl p-8">
          <div class="text-xs font-semibold text-zinc-400 uppercase tracking-widest mb-6">Ohne PageScan</div>
          <div class="space-y-4">
            <div class="flex items-start gap-3">
              <span class="text-zinc-300 mt-0.5 flex-shrink-0">✕</span>
              <p class="text-sm text-zinc-500">2–3 Stunden manuell googeln, um 10 potenzielle Kunden zu finden</p>
            </div>
            <div class="flex items-start gap-3">
              <span class="text-zinc-300 mt-0.5 flex-shrink-0">✕</span>
              <p class="text-sm text-zinc-500">Websites einzeln öffnen und subjektiv beurteilen — ohne Datenbasis</p>
            </div>
            <div class="flex items-start gap-3">
              <span class="text-zinc-300 mt-0.5 flex-shrink-0">✕</span>
              <p class="text-sm text-zinc-500">Kaltanruf ohne konkrete Argumente: „Ihre Website könnte besser sein"</p>
            </div>
            <div class="flex items-start gap-3">
              <span class="text-zinc-300 mt-0.5 flex-shrink-0">✕</span>
              <p class="text-sm text-zinc-500">Leads in Excel-Tabellen, kein System, Kontakte gehen verloren</p>
            </div>
          </div>
        </div>

        <div class="bg-white border border-zinc-200 rounded-xl p-8">
          <div class="text-xs font-semibold text-zinc-900 uppercase tracking-widest mb-6">Mit PageScan</div>
          <div class="space-y-4">
            <div class="flex items-start gap-3">
              <span class="text-zinc-900 mt-0.5 flex-shrink-0">✓</span>
              <p class="text-sm text-zinc-700">In 2 Minuten 20+ Unternehmen mit vollständigem Website-Scan</p>
            </div>
            <div class="flex items-start gap-3">
              <span class="text-zinc-900 mt-0.5 flex-shrink-0">✓</span>
              <p class="text-sm text-zinc-700">Objektiver Score 0–100 mit konkreten Schwachstellen je Website</p>
            </div>
            <div class="flex items-start gap-3">
              <span class="text-zinc-900 mt-0.5 flex-shrink-0">✓</span>
              <p class="text-sm text-zinc-700">„Ihre Website hat 28/100 — kein SSL, kein Impressum." Termin gemacht.</p>
            </div>
            <div class="flex items-start gap-3">
              <span class="text-zinc-900 mt-0.5 flex-shrink-0">✓</span>
              <p class="text-sm text-zinc-700">Leads mit Status, Notizen, Kontaktverlauf — alles an einem Ort</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>

  {{-- ── 8. FAQ ── --}}
  <div class="py-20 border-t border-zinc-100">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-xs uppercase tracking-widest text-zinc-400 mb-10">Häufige Fragen</div>
      <div class="grid grid-cols-2 gap-x-16" id="faq-grid">

        <div class="border-t border-zinc-100 py-5">
          <button class="faq-btn w-full flex justify-between items-center text-left gap-4" onclick="toggleFaq(this)">
            <span class="text-sm font-medium text-zinc-800">Brauche ich einen Google API-Key?</span>
            <span class="faq-icon text-zinc-300 flex-shrink-0 transition-transform duration-200">↓</span>
          </button>
          <p class="faq-answer hidden mt-3 text-sm text-zinc-500 leading-relaxed">
            Ja, du brauchst einen kostenlosen Google Places API-Key. Die Einrichtung dauert 5 Minuten — wir führen dich beim Onboarding Schritt für Schritt durch. Google gibt dir $200 Guthaben pro Monat kostenlos, das reicht für mehrere Hundert Suchen.
          </p>
        </div>

        <div class="border-t border-zinc-100 py-5">
          <button class="faq-btn w-full flex justify-between items-center text-left gap-4" onclick="toggleFaq(this)">
            <span class="text-sm font-medium text-zinc-800">Kann ich jederzeit kündigen?</span>
            <span class="faq-icon text-zinc-300 flex-shrink-0 transition-transform duration-200">↓</span>
          </button>
          <p class="faq-answer hidden mt-3 text-sm text-zinc-500 leading-relaxed">
            Ja, monatlich kündbar ohne Mindestlaufzeit. Nach der Kündigung behältst du Zugriff bis zum Ende des bezahlten Monats. Deine gespeicherten Leads bleiben erhalten.
          </p>
        </div>

        <div class="border-t border-zinc-100 py-5">
          <button class="faq-btn w-full flex justify-between items-center text-left gap-4" onclick="toggleFaq(this)">
            <span class="text-sm font-medium text-zinc-800">Was passiert nach dem 14-Tage-Trial?</span>
            <span class="faq-icon text-zinc-300 flex-shrink-0 transition-transform duration-200">↓</span>
          </button>
          <p class="faq-answer hidden mt-3 text-sm text-zinc-500 leading-relaxed">
            Nach 14 Tagen endet der Trial automatisch — keine Abbuchung ohne dein Zutun. Du bekommst 3 Tage vorher eine Erinnerung per E-Mail. Alle Leads und gespeicherten Suchen bleiben erhalten.
          </p>
        </div>

        <div class="border-t border-zinc-100 py-5">
          <button class="faq-btn w-full flex justify-between items-center text-left gap-4" onclick="toggleFaq(this)">
            <span class="text-sm font-medium text-zinc-800">Funktioniert das nur für Deutschland?</span>
            <span class="faq-icon text-zinc-300 flex-shrink-0 transition-transform duration-200">↓</span>
          </button>
          <p class="faq-answer hidden mt-3 text-sm text-zinc-500 leading-relaxed">
            PageScan funktioniert überall wo Google Places Daten hat. Der Impressum-Check ist auf Deutschland und Österreich optimiert, alle anderen Kriterien laufen weltweit.
          </p>
        </div>

        <div class="border-t border-zinc-100 py-5">
          <button class="faq-btn w-full flex justify-between items-center text-left gap-4" onclick="toggleFaq(this)">
            <span class="text-sm font-medium text-zinc-800">Wie genau ist der Website-Score?</span>
            <span class="faq-icon text-zinc-300 flex-shrink-0 transition-transform duration-200">↓</span>
          </button>
          <p class="faq-answer hidden mt-3 text-sm text-zinc-500 leading-relaxed">
            Der Score basiert auf technisch messbaren Faktoren und gibt einen schnellen Überblick. Er ersetzt kein vollständiges Audit, ist aber ideal als Gesprächseinstieg. Die Gewichtung der Kriterien kannst du im Profil individuell anpassen.
          </p>
        </div>

        <div class="border-t border-zinc-100 py-5">
          <button class="faq-btn w-full flex justify-between items-center text-left gap-4" onclick="toggleFaq(this)">
            <span class="text-sm font-medium text-zinc-800">Kann ich das Tool im Team nutzen?</span>
            <span class="faq-icon text-zinc-300 flex-shrink-0 transition-transform duration-200">↓</span>
          </button>
          <p class="faq-answer hidden mt-3 text-sm text-zinc-500 leading-relaxed">
            Ja, im Agency- und Pro-Plan kannst du mehrere Nutzer einladen und Leads zuweisen. Admins sehen alle Leads des Teams, einzelne Nutzer nur ihre eigenen.
          </p>
        </div>

      </div>
    </div>
  </div>

  {{-- ── 9. PRICING ── --}}
  <div id="preise" class="py-20">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-xs uppercase tracking-widest text-zinc-400 mb-3">Preise</div>
      <div class="flex items-end justify-between mb-10 flex-wrap gap-4">
        <h2 class="text-3xl font-bold text-zinc-900 tracking-tight">Einfach. Transparent. Kündbar.</h2>
        <p class="text-sm text-zinc-400">Alle Pläne starten mit 14 Tagen kostenlosem Trial.</p>
      </div>
      <div class="pricing-grid">

        {{-- Starter --}}
        <div class="plan">
          <div class="text-xs uppercase tracking-widest text-zinc-400 mb-1">Starter</div>
          <div class="text-5xl font-bold text-zinc-900 tracking-tight mt-4 leading-none">
            €29<span class="text-sm font-normal text-zinc-400">/Monat</span>
          </div>
          <div class="border-t border-zinc-200 my-6"></div>
          <ul class="space-y-2.5 text-sm text-zinc-500 flex-1">
            <li><span class="text-zinc-300 mr-2">·</span>200 Scans pro Monat</li>
            <li><span class="text-zinc-300 mr-2">·</span>1 Nutzer</li>
            <li><span class="text-zinc-300 mr-2">·</span>Lead-Management</li>
            <li><span class="text-zinc-300 mr-2">·</span>Watchlist &amp; Auto-Scan</li>
            <li><span class="text-zinc-300 mr-2">·</span>CSV-Export</li>
          </ul>
          <a href="{{ route('register') }}"
             class="mt-8 rounded-lg border border-zinc-200 hover:border-zinc-400 text-zinc-500 hover:text-zinc-900 py-2.5 text-sm text-center transition-colors block w-full">
            Jetzt starten
          </a>
        </div>

        {{-- Agency (featured) --}}
        <div class="plan plan-highlight">
          <div class="flex items-center mb-1">
            <span class="text-xs uppercase tracking-widest text-zinc-400">Agency</span>
            <span class="text-[10px] bg-zinc-900 text-white px-2 py-0.5 font-semibold ml-2 rounded-full">Beliebt</span>
          </div>
          <div class="text-5xl font-bold text-zinc-900 tracking-tight mt-4 leading-none">
            €79<span class="text-sm font-normal text-zinc-400">/Monat</span>
          </div>
          <div class="border-t border-zinc-200 my-6"></div>
          <ul class="space-y-2.5 text-sm text-zinc-500 flex-1">
            <li><span class="text-zinc-300 mr-2">·</span>1.000 Scans pro Monat</li>
            <li><span class="text-zinc-300 mr-2">·</span>3 Nutzer</li>
            <li><span class="text-zinc-300 mr-2">·</span>Lead-Management</li>
            <li><span class="text-zinc-300 mr-2">·</span>Watchlist &amp; Auto-Scan</li>
            <li><span class="text-zinc-300 mr-2">·</span>Team-Features</li>
            <li><span class="text-zinc-300 mr-2">·</span>CSV-Export</li>
          </ul>
          <a href="{{ route('register') }}"
             class="mt-8 rounded-lg bg-zinc-900 hover:bg-zinc-700 text-white py-2.5 text-sm text-center font-medium transition-colors block w-full">
            14 Tage kostenlos
          </a>
        </div>

        {{-- Pro --}}
        <div class="plan">
          <div class="text-xs uppercase tracking-widest text-zinc-400 mb-1">Pro</div>
          <div class="text-5xl font-bold text-zinc-900 tracking-tight mt-4 leading-none">
            €149<span class="text-sm font-normal text-zinc-400">/Monat</span>
          </div>
          <div class="border-t border-zinc-200 my-6"></div>
          <ul class="space-y-2.5 text-sm text-zinc-500 flex-1">
            <li><span class="text-zinc-300 mr-2">·</span>Unbegrenzte Scans</li>
            <li><span class="text-zinc-300 mr-2">·</span>Bis zu 10 Nutzer</li>
            <li><span class="text-zinc-300 mr-2">·</span>Lead-Management</li>
            <li><span class="text-zinc-300 mr-2">·</span>Watchlist &amp; Auto-Scan</li>
            <li><span class="text-zinc-300 mr-2">·</span>Team-Features</li>
            <li><span class="text-zinc-300 mr-2">·</span>Prioritäts-Support</li>
          </ul>
          <a href="{{ route('register') }}"
             class="mt-8 rounded-lg border border-zinc-200 hover:border-zinc-400 text-zinc-500 hover:text-zinc-900 py-2.5 text-sm text-center transition-colors block w-full">
            Jetzt starten
          </a>
        </div>

      </div>
      <p class="text-center text-xs text-zinc-400 mt-8">
        ✓ 14 Tage Trial &nbsp;·&nbsp; ✓ Keine Kreditkarte &nbsp;·&nbsp; ✓ Monatlich kündbar
      </p>
    </div>
  </div>

  {{-- ── 10. CLOSING CTA ── --}}
  <div class="border-t border-zinc-100 py-20 text-center">
    <div class="max-w-xl mx-auto px-6">
      <h2 class="text-3xl font-bold text-zinc-900 tracking-tight leading-tight mb-4">
        Starte heute mit 10 kostenlosen Scans.
      </h2>
      <p class="text-sm text-zinc-500 mb-8 leading-relaxed">
        Keine Kreditkarte nötig. Registriere dich in 30 Sekunden und sieh sofort welche Websites in deiner Zielbranche Verbesserungspotenzial haben.
      </p>
      <a href="{{ route('register') }}"
         class="inline-block bg-zinc-900 hover:bg-zinc-700 text-white text-sm font-medium px-8 py-3.5 rounded-lg transition-colors">
        Kostenlos registrieren →
      </a>
      <p class="mt-4 text-xs text-zinc-400">
        Bereits registriert?
        <a href="{{ route('login') }}" class="text-zinc-500 hover:text-zinc-700 underline underline-offset-2">
          Hier anmelden
        </a>
      </p>
    </div>
  </div>

  {{-- ── 11. FOOTER ── --}}
  <footer class="border-t border-zinc-200 py-8">
    <div class="max-w-6xl mx-auto px-6 flex justify-between items-center flex-wrap gap-4">
      <span class="text-xs text-zinc-400">© {{ date('Y') }} PageScan</span>
      <nav class="flex gap-5">
        <a href="{{ route('login') }}"       class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">Anmelden</a>
        <a href="{{ route('register') }}"    class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">Registrieren</a>
        <a href="{{ route('impressum') }}"   class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">Impressum</a>
        <a href="{{ route('datenschutz') }}" class="text-xs text-zinc-400 hover:text-zinc-600 transition-colors">Datenschutz</a>
      </nav>
    </div>
  </footer>

  <script>
    /* ── HERO LOAD ANIMATION ─────────────────────────────────── */
    const heroItems  = document.querySelectorAll('.hero-item');
    const heroDelays = [0, 80, 180, 280];
    heroItems.forEach((el, i) => {
      const delay = heroDelays[i] ?? 280;
      el.style.transition = `opacity 0.5s ease ${delay}ms, transform 0.5s ease ${delay}ms`;
      requestAnimationFrame(() => requestAnimationFrame(() => {
        el.style.opacity   = '1';
        el.style.transform = 'translateY(0)';
      }));
    });

    /* ── SCROLL ANIMATIONS (Stripe-style) ───────────────────── */
    const targets = document.querySelectorAll(
      '.step-v2, .plan, .criteria-card'
    );
    targets.forEach((el, i) => {
      el.style.opacity    = '0';
      el.style.transform  = 'translateY(20px)';
      el.style.transition = `opacity 0.5s cubic-bezier(0.16,1,0.3,1) ${(i % 4) * 0.08}s, transform 0.5s cubic-bezier(0.16,1,0.3,1) ${(i % 4) * 0.08}s`;
    });
    const observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.style.opacity   = '1';
          entry.target.style.transform = 'translateY(0)';
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: 0.1, rootMargin: '0px 0px -30px 0px' });
    targets.forEach(el => observer.observe(el));

    /* ── COUNTER ANIMATION ───────────────────────────────────── */
    const easeOutQuart = t => 1 - Math.pow(1 - t, 4);

    const counterObserver = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        if (!entry.isIntersecting) return;
        const el       = entry.target;
        const target   = parseInt(el.dataset.value, 10);
        const duration = 1400;
        const start    = performance.now();
        const tick = (now) => {
          const elapsed  = Math.min(now - start, duration);
          const progress = easeOutQuart(elapsed / duration);
          el.textContent = Math.round(progress * target);
          if (elapsed < duration) requestAnimationFrame(tick);
          else el.textContent = target;
        };
        requestAnimationFrame(tick);
        counterObserver.unobserve(el);
      });
    }, { threshold: 0.3 });

    document.querySelectorAll('[data-counter]').forEach(el => counterObserver.observe(el));

    /* ── FAQ TOGGLE ──────────────────────────────────────────── */
    function toggleFaq(btn) {
      const answer = btn.nextElementSibling;
      const icon   = btn.querySelector('.faq-icon');
      const isOpen = !answer.classList.contains('hidden');
      document.querySelectorAll('.faq-answer').forEach(a => a.classList.add('hidden'));
      document.querySelectorAll('.faq-icon').forEach(i => i.style.transform = '');
      if (!isOpen) {
        answer.classList.remove('hidden');
        icon.style.transform = 'rotate(180deg)';
      }
    }
  </script>

</body>
</html>
