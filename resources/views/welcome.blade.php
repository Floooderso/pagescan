<!DOCTYPE html>
<html lang="de" class="bg-white">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>PageScan — Website-Scanner für Agenturen</title>
  <meta name="description" content="Finde Unternehmen mit schlechten Websites. Branche und Stadt eingeben, fertig.">
  <link rel="preconnect" href="https://fonts.googleapis.com">
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

    /* ── Feature Grid ──────────────────────────────────────── */
    .feature-grid {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
    }
    .feature-card {
      padding: 24px;
      background: #f9fafb;
      border: 1px solid #e4e4e7;
      border-radius: 10px;
      border-left-width: 3px;
      border-left-style: solid;
    }
    .feature-card:nth-child(1) { border-left-color: #a1a1aa; }
    .feature-card:nth-child(2) { border-left-color: #71717a; }
    .feature-card:nth-child(3) { border-left-color: #52525b; }
    .feature-card-title { font-size: 13px; font-weight: 500; color: #09090b; margin-bottom: 8px; }
    .feature-card-desc  { font-size: 13px; color: #71717a; line-height: 1.65; }

    /* ── Testimonial Row ───────────────────────────────────── */
    .testimonial-row {
      display: grid;
      grid-template-columns: repeat(3, 1fr);
      gap: 12px;
    }
    .testimonial-card {
      background: #f9fafb;
      border: 1px solid #e4e4e7;
      border-radius: 10px;
      padding: 24px;
    }
    .testimonial-card::before {
      content: '\201C';
      font-size: 40px;
      line-height: 1;
      color: #d4d4d8;
      display: block;
      margin-bottom: 8px;
      font-family: Georgia, serif;
    }
    .testimonial-text   { font-size: 13px; color: #71717a; line-height: 1.7; margin-bottom: 16px; }
    .testimonial-author { font-size: 12px; font-weight: 500; color: #a1a1aa; }

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
    @media (max-width: 900px) {
      .steps-row       { grid-template-columns: 1fr 1fr; }
      .steps-row::before { display: none; }
      .feature-grid    { grid-template-columns: 1fr; }
      .testimonial-row { grid-template-columns: 1fr; }
    }
    @media (max-width: 640px) {
      .steps-row    { grid-template-columns: 1fr; }
      .pricing-grid { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body class="bg-white text-zinc-900 antialiased" style="font-family:'Geist',system-ui,sans-serif;">

  {{-- ── 1. NAV ── --}}
  <nav class="nav sticky top-0 z-50">
    <span class="nav-brand">PageScan</span>
    <a href="#schritte" class="nav-link">So funktioniert's</a>
    <a href="#preise"   class="nav-link">Preise</a>
    <div class="nav-spacer"></div>
    <a href="{{ route('login') }}"    class="btn btn-sm" style="margin-right:4px;">Anmelden</a>
    <a href="{{ route('register') }}" class="btn btn-sm btn-primary">Kostenlos testen</a>
  </nav>

  {{-- ── 2. HERO ── --}}
  <section class="py-28 text-center">
    <div class="max-w-6xl mx-auto px-6">
      <h1 class="hero-item opacity-0 translate-y-3 text-6xl font-bold tracking-tight leading-[1.05] text-zinc-900">
        Finde Kunden mit<br>schlechten Websites.
      </h1>
    </div>
  </section>

  {{-- ── 4. STATS ── --}}
  <div class="border-t border-b border-zinc-200" data-fade>
    <div class="max-w-6xl mx-auto px-6">
      <div class="grid grid-cols-3 divide-x divide-zinc-200">

        <div class="px-12 py-10">
          <div class="text-5xl font-bold text-zinc-900 tracking-tight leading-none">
            <span class="text-zinc-400 font-normal">0–</span><span data-counter data-value="100">0</span>
          </div>
          <div class="text-xs uppercase tracking-widest text-zinc-400 mt-2">Website-Score pro Scan</div>
          <p class="text-sm text-zinc-500 mt-3">Jede Website bekommt einen objektiven Score</p>
        </div>

        <div class="px-12 py-10">
          <div class="text-5xl font-bold text-zinc-900 tracking-tight leading-none">
            <span data-counter data-value="6">0</span><span>+</span>
          </div>
          <div class="text-xs uppercase tracking-widest text-zinc-400 mt-2">Geprüfte Kriterien</div>
          <p class="text-sm text-zinc-500 mt-3">SSL, Mobile, Impressum, Ladezeit und mehr</p>
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

  {{-- ── 5. SO FUNKTIONIERT'S ── --}}
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

  {{-- ── 6. MOCK-SCANNER ── --}}
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
    </div>
  </div>

  {{-- ── 7. KRITERIEN + ZITAT ── --}}
  <div class="py-20">
    <div class="max-w-6xl mx-auto px-6">
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
          <blockquote class="text-base font-medium text-zinc-800 leading-relaxed mb-6">
            „Ihre Website hat 28 von 100 Punkten — kein SSL, nicht mobil,
            kein Impressum. Ich kann Ihnen zeigen wie wir das in zwei Wochen beheben."
          </blockquote>
          <p class="text-xs uppercase tracking-widest text-zinc-400">So klingt ein Kaltanruf mit PageScan-Daten</p>
        </div>

      </div>
    </div>
  </div>

  {{-- ── 7. WARUM PAGESCAN ── --}}
  <div class="py-20">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-xs uppercase tracking-widest text-zinc-400 mb-8">Warum PageScan?</div>
      <div class="feature-grid">

        <div class="feature-card">
          <div class="feature-card-title">Kein manuelles Recherchieren</div>
          <p class="feature-card-desc">Stunden sparen — PageScan übernimmt die Arbeit vollautomatisch und liefert dir sofort verwertbare Ergebnisse.</p>
        </div>

        <div class="feature-card">
          <div class="feature-card-title">Datenbasierte Kaltakquise</div>
          <p class="feature-card-desc">Mit konkreten Scores überzeugst du Kunden sofort — keine vagen Versprechen, sondern harte Fakten direkt aus dem Scan.</p>
        </div>

        <div class="feature-card">
          <div class="feature-card-title">Immer aktuell</div>
          <p class="feature-card-desc">Die Watchlist scannt täglich neu. Neue Treffer landen automatisch in deiner Lead-Liste — ohne dass du etwas tun musst.</p>
        </div>

      </div>
    </div>
  </div>

  {{-- ── 8. PRICING ── --}}
  <div id="preise" class="py-20">
    <div class="max-w-6xl mx-auto px-6">
      <div class="pricing-grid">

        {{-- Starter --}}
        <div class="plan">
          <div class="text-xs uppercase tracking-widest text-zinc-400 mb-1">Starter</div>
          <div class="text-5xl font-bold text-zinc-900 tracking-tight mt-4 leading-none">
            €29<span class="text-sm font-normal text-zinc-400">/Monat</span>
          </div>
          <div class="border-t border-zinc-200 my-6"></div>
          <ul class="space-y-2.5 text-sm text-zinc-500 flex-1">
            <li><span class="text-zinc-300 mr-2">·</span>100 Scans pro Monat</li>
            <li><span class="text-zinc-300 mr-2">·</span>1 Nutzer</li>
            <li><span class="text-zinc-300 mr-2">·</span>Lead-Management</li>
            <li><span class="text-zinc-300 mr-2">·</span>Watchlist &amp; Auto-Scan</li>
            <li><span class="text-zinc-300 mr-2">·</span>CSV-Export</li>
          </ul>
          <a href="{{ route('register') }}"
             class="mt-8 border border-zinc-200 hover:border-zinc-400 text-zinc-500 hover:text-zinc-900 py-2.5 text-sm text-center transition-colors block w-full">
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
            <li><span class="text-zinc-300 mr-2">·</span>500 Scans pro Monat</li>
            <li><span class="text-zinc-300 mr-2">·</span>5 Nutzer</li>
            <li><span class="text-zinc-300 mr-2">·</span>Lead-Management</li>
            <li><span class="text-zinc-300 mr-2">·</span>Watchlist &amp; Auto-Scan</li>
            <li><span class="text-zinc-300 mr-2">·</span>Team-Features</li>
            <li><span class="text-zinc-300 mr-2">·</span>CSV-Export</li>
          </ul>
          <a href="{{ route('register') }}"
             class="mt-8 bg-zinc-900 hover:bg-zinc-700 text-white py-2.5 text-sm text-center font-medium transition-colors block w-full">
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
            <li><span class="text-zinc-300 mr-2">·</span>Unbegrenzte Nutzer</li>
            <li><span class="text-zinc-300 mr-2">·</span>Lead-Management</li>
            <li><span class="text-zinc-300 mr-2">·</span>Watchlist &amp; Auto-Scan</li>
            <li><span class="text-zinc-300 mr-2">·</span>Team-Features</li>
            <li><span class="text-zinc-300 mr-2">·</span>Prioritäts-Support</li>
          </ul>
          <a href="{{ route('register') }}"
             class="mt-8 border border-zinc-200 hover:border-zinc-400 text-zinc-500 hover:text-zinc-900 py-2.5 text-sm text-center transition-colors block w-full">
            Jetzt starten
          </a>
        </div>

      </div>
    </div>
  </div>

  {{-- ── 10. FOOTER ── --}}
  <footer class="border-t border-zinc-200 py-8">
    <div class="max-w-6xl mx-auto px-6 flex justify-between items-center flex-wrap gap-4">
      <span class="text-xs text-zinc-400">© {{ date('Y') }} PageScan</span>
      <nav class="flex gap-5">
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

    /* ── SCROLL ANIMATIONS (Stripe-style, DOMContentLoaded) ───── */
    document.addEventListener('DOMContentLoaded', () => {
      const targets = document.querySelectorAll(
        '.step-v2, .feature-card, .plan, .criteria-card'
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
    });

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
  </script>

</body>
</html>
