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
  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
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

    /* ── PageScan Demo Animation ───────────────────────── */
    #ps-demo-wrap {
      max-width: 680px;
      margin: 0 auto;
      border: 1px solid #e4e4e7;
      border-radius: 12px;
      overflow: hidden;
      background: #ffffff;
      box-shadow: 0 4px 24px rgba(0,0,0,0.05);
      text-align: left;
      font-family: 'Geist', system-ui, sans-serif;
      transition: opacity 0.5s ease;
    }
    .ps-titlebar {
      display: flex;
      align-items: center;
      gap: 6px;
      padding: 10px 14px;
      background: #f9fafb;
      border-bottom: 1px solid #e4e4e7;
    }
    .ps-dot { width: 10px; height: 10px; border-radius: 50%; }
    .ps-titlebar-text {
      margin-left: 8px;
      font-size: 11px;
      color: #a1a1aa;
      font-weight: 500;
    }
    /* Scenes Wrapper — feste Höhe, kein Layout-Shift */
    #ps-scenes-wrap {
      position: relative;
      min-height: 280px;
    }
    /* Scene 1 — Suchformular */
    #ps-scene-form {
      position: absolute;
      top: 0; left: 0; width: 100%;
      padding: 24px;
      box-sizing: border-box;
    }
    .ps-form-row { display: flex; gap: 10px; margin-bottom: 12px; }
    .ps-field { flex: 1; }
    .ps-field label {
      display: block;
      font-size: 11px;
      font-weight: 500;
      color: #71717a;
      margin-bottom: 5px;
      text-transform: uppercase;
      letter-spacing: 0.05em;
    }
    .ps-field input {
      width: 100%;
      border: 1px solid #e4e4e7;
      border-radius: 6px;
      padding: 8px 12px;
      font-size: 13px;
      color: #09090b;
      background: #fff;
      outline: none;
      box-sizing: border-box;
      font-family: inherit;
      transition: border-color 0.2s;
    }
    .ps-field input:focus { border-color: #a1a1aa; }
    #ps-btn-search {
      margin-top: 4px;
      background: #18181b;
      color: #fff;
      border: none;
      border-radius: 6px;
      padding: 9px 18px;
      font-size: 13px;
      font-weight: 500;
      cursor: default;
      transition: background 0.3s;
      font-family: inherit;
      white-space: nowrap;
      align-self: flex-end;
    }
    #ps-btn-search.ps-btn-active { background: #3f3f46; }
    /* Scene 2 — Loader */
    #ps-scene-loader {
      position: absolute;
      top: 0; left: 0; width: 100%;
      display: none;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 14px;
      padding: 40px 24px;
      box-sizing: border-box;
    }
    .ps-spinner {
      width: 24px; height: 24px;
      border: 2px solid #e4e4e7;
      border-top-color: #18181b;
      border-radius: 50%;
      animation: ps-spin 0.75s linear infinite;
    }
    @keyframes ps-spin { to { transform: rotate(360deg); } }
    #ps-loader-text { font-size: 13px; color: #71717a; }
    /* Scene 3 — Tabelle */
    #ps-scene-table {
      position: absolute;
      top: 0; left: 0; width: 100%;
      display: none;
    }
    .ps-table-header {
      display: grid;
      grid-template-columns: 1fr 90px 90px 72px;
      padding: 8px 16px;
      background: #f9fafb;
      border-bottom: 1px solid #e4e4e7;
    }
    .ps-table-header span {
      font-size: 10px;
      font-weight: 600;
      color: #a1a1aa;
      text-transform: uppercase;
      letter-spacing: 0.06em;
    }
    .ps-table-header span:not(:first-child) { text-align: center; }
    .ps-row {
      display: grid;
      grid-template-columns: 1fr 90px 90px 72px;
      padding: 10px 16px;
      border-bottom: 1px solid #f4f4f5;
      align-items: center;
      opacity: 0;
      transform: translateY(8px);
      transition: opacity 0.2s ease, transform 0.2s ease, background 0.15s;
    }
    .ps-row:last-child { border-bottom: none; }
    .ps-row:hover { background: #f9fafb; }
    .ps-row-name { font-size: 13px; font-weight: 500; color: #18181b; }
    .ps-row-sub { font-size: 11px; color: #a1a1aa; margin-top: 1px; }
    .ps-row-cms { font-size: 12px; color: #71717a; text-align: center; }
    .ps-row-problem { font-size: 11px; color: #71717a; text-align: center; }
    .ps-score-wrap { text-align: center; }
    .ps-score {
      display: inline-block;
      font-size: 11px;
      font-weight: 700;
      font-family: ui-monospace, monospace;
      padding: 2px 7px;
      border-radius: 4px;
    }
    .ps-score-red    { background: #fef2f2; color: #ef4444; }
    .ps-score-yellow { background: #fffbeb; color: #f59e0b; }
    .ps-score-green  { background: #f0fdf4; color: #16a34a; }
    .ps-score-none   { background: #f4f4f5; color: #a1a1aa; }
    /* Mobile */
    @media (max-width: 520px) {
      .ps-form-row { flex-direction: column; }
      .ps-table-header { grid-template-columns: 1fr 0 72px; }
      .ps-table-header span:nth-child(2),
      .ps-table-header span:nth-child(3) { display: none; }
      .ps-row { grid-template-columns: 1fr 0 72px; }
      .ps-row-cms { display: none; }
      .ps-row-problem { display: none; }
    }
  </style>
</head>
<body class="bg-white text-zinc-900 antialiased" style="font-family:'Geist',system-ui,sans-serif;">

  {{-- ── 1. NAV ── --}}
  <nav class="sticky top-0 z-50 bg-white border-b border-zinc-100 flex items-center gap-2 px-8 h-14">
    <span class="font-semibold text-sm text-zinc-900 mr-4">PageScan</span>
    <a href="#schritte" class="text-sm text-zinc-400 hover:text-zinc-700 px-3 py-1.5 rounded-md transition-colors">So funktioniert's</a>
    <div class="flex-1"></div>
    <a href="{{ route('login') }}"    class="text-sm text-zinc-500 hover:text-zinc-900 px-4 py-1.5 rounded-md border border-zinc-200 hover:border-zinc-400 transition-colors mr-2">Anmelden</a>
    <a href="{{ route('register') }}" class="text-sm font-medium text-white bg-zinc-900 hover:bg-zinc-700 px-4 py-1.5 rounded-md transition-colors">Beta-Zugang anfragen</a>
  </nav>

  {{-- ── 2. HERO ── --}}
  <section class="py-28 text-center">
    <div class="max-w-6xl mx-auto px-6">
      <h1 class="hero-item opacity-0 translate-y-3 text-6xl font-bold tracking-tight leading-[1.05] text-zinc-900">
        Finde Unternehmen mit<br>schlechten Websites.
      </h1>
    </div>
  </section>

  <div style="background:#f9fafb; border-top:1px solid #e4e4e7; border-bottom:1px solid #e4e4e7; padding:12px 24px;">
    <div style="max-width:1152px; margin:0 auto; display:flex; align-items:center; justify-content:center; gap:24px; flex-wrap:wrap;">
      <span style="font-size:12px; color:#71717a; display:flex; align-items:center; gap:6px;">
        <span style="color:#16a34a; font-weight:600;">✓</span> Aktuell in der Beta
      </span>
      <span style="color:#e4e4e7;">|</span>
      <span style="font-size:12px; color:#71717a; display:flex; align-items:center; gap:6px;">
        <span style="color:#16a34a; font-weight:600;">✓</span> Aktuell kostenlos in der Beta
      </span>
      <span style="color:#e4e4e7;">|</span>
      <span style="font-size:12px; color:#71717a; display:flex; align-items:center; gap:6px;">
        <span style="color:#16a34a; font-weight:600;">✓</span> Keine Kreditkarte nötig
      </span>
      <span style="color:#e4e4e7;">|</span>
      <span style="font-size:12px; color:#71717a; display:flex; align-items:center; gap:6px;">
        <span style="color:#16a34a; font-weight:600;">✓</span> Freischaltung per E-Mail
      </span>
    </div>
  </div>

  {{-- ── 5b. ANIMIERTE DEMO ── --}}
  <div class="py-20 border-t border-zinc-100">
    <div class="max-w-4xl mx-auto px-6 text-center">
      <div class="text-xs uppercase tracking-widest text-zinc-400 mb-4">Live-Demo</div>
      <h2 class="text-2xl font-bold text-zinc-900 tracking-tight mb-10">
        Von Suchbegriff zu fertiger Lead-Liste — in unter 60 Sekunden
      </h2>

      <div id="ps-demo-wrap">
        <!-- Titlebar -->
        <div class="ps-titlebar">
          <span class="ps-dot" style="background:#ef4444;"></span>
          <span class="ps-dot" style="background:#f59e0b;"></span>
          <span class="ps-dot" style="background:#22c55e;"></span>
          <span class="ps-titlebar-text">Friseure München — PageScan</span>
        </div>

        <div id="ps-scenes-wrap">
        <!-- Szene 1: Suchformular -->
        <div id="ps-scene-form">
          <div class="ps-form-row">
            <div class="ps-field">
              <label>Branche</label>
              <input type="text" id="ps-input-branche" placeholder="z.B. Friseur" readonly>
            </div>
            <div class="ps-field">
              <label>Stadt</label>
              <input type="text" id="ps-input-stadt" placeholder="z.B. München" readonly>
            </div>
            <button id="ps-btn-search" type="button">Suchen &amp; Scannen</button>
          </div>
        </div>

        <!-- Szene 2: Ladestate -->
        <div id="ps-scene-loader">
          <div class="ps-spinner"></div>
          <span id="ps-loader-text">Google Places wird abgefragt...</span>
        </div>

        <!-- Szene 3: Ergebnistabelle -->
        <div id="ps-scene-table">
          <div class="ps-table-header">
            <span>Unternehmen</span>
            <span>CMS</span>
            <span>Problem</span>
            <span>Score</span>
          </div>
          <div class="ps-row" data-demo-score="28" data-score-class="ps-score-red">
            <div><div class="ps-row-name">Salon Bella Vista</div><div class="ps-row-sub">WordPress</div></div>
            <div class="ps-row-cms">WordPress</div>
            <div class="ps-row-problem">kein SSL</div>
            <div class="ps-score-wrap"><span class="ps-score ps-score-red">0</span></div>
          </div>
          <div class="ps-row" data-demo-score="" data-score-class="ps-score-none">
            <div><div class="ps-row-name">Hairstyle by Marco</div><div class="ps-row-sub">—</div></div>
            <div class="ps-row-cms">—</div>
            <div class="ps-row-problem">keine Website</div>
            <div class="ps-score-wrap"><span class="ps-score ps-score-none">—</span></div>
          </div>
          <div class="ps-row" data-demo-score="41" data-score-class="ps-score-yellow">
            <div><div class="ps-row-name">Friseur am Dom</div><div class="ps-row-sub">Wix</div></div>
            <div class="ps-row-cms">Wix</div>
            <div class="ps-row-problem">kein Impressum</div>
            <div class="ps-score-wrap"><span class="ps-score ps-score-yellow">0</span></div>
          </div>
          <div class="ps-row" data-demo-score="55" data-score-class="ps-score-yellow">
            <div><div class="ps-row-name">HairLab Studio</div><div class="ps-row-sub">Squarespace</div></div>
            <div class="ps-row-cms">Squarespace</div>
            <div class="ps-row-problem">nicht mobil</div>
            <div class="ps-score-wrap"><span class="ps-score ps-score-yellow">0</span></div>
          </div>
          <div class="ps-row" data-demo-score="82" data-score-class="ps-score-green">
            <div><div class="ps-row-name">Coiffeur Deluxe</div><div class="ps-row-sub">TYPO3</div></div>
            <div class="ps-row-cms">TYPO3</div>
            <div class="ps-row-problem">✓ Alles OK</div>
            <div class="ps-score-wrap"><span class="ps-score ps-score-green">0</span></div>
          </div>
        </div>
        </div><!-- /ps-scenes-wrap -->
      </div><!-- /#ps-demo-wrap -->

      <script>
      (function() {
        var wrap    = document.getElementById('ps-demo-wrap');
        var scForm  = document.getElementById('ps-scene-form');
        var scLoad  = document.getElementById('ps-scene-loader');
        var scTable = document.getElementById('ps-scene-table');
        var inBranche = document.getElementById('ps-input-branche');
        var inStadt   = document.getElementById('ps-input-stadt');
        var btnSearch = document.getElementById('ps-btn-search');
        var loaderTxt = document.getElementById('ps-loader-text');
        var rows      = document.querySelectorAll('#ps-scene-table .ps-row');

        var timers = [];
        var rafIds = [];
        var isRunning = false;

        function t(fn, ms) {
          var id = setTimeout(fn, ms);
          timers.push(id);
          return id;
        }

        function clearAll() {
          timers.forEach(function(id) { clearTimeout(id); });
          timers = [];
          rafIds.forEach(function(id) { cancelAnimationFrame(id); });
          rafIds = [];
        }

        function typeIn(el, text, cb) {
          var i = 0;
          function tick() {
            if (!isRunning) return;
            if (i < text.length) {
              el.value += text[i++];
              t(tick, 80);
            } else if (cb) {
              t(function() { if (isRunning) cb(); }, 120);
            }
          }
          tick();
        }

        function easeOut(p) { return 1 - Math.pow(1 - p, 4); }

        function animScore(el, target) {
          if (!target) return;
          var start = null;
          function raf(now) {
            if (!isRunning) return;
            if (!start) start = now;
            var p = Math.min((now - start) / 500, 1);
            el.textContent = Math.round(easeOut(p) * target);
            if (p < 1) rafIds.push(requestAnimationFrame(raf));
            else el.textContent = target;
          }
          rafIds.push(requestAnimationFrame(raf));
        }

        function resetAll() {
          wrap.style.opacity = '1';
          inBranche.value = '';
          inStadt.value   = '';
          btnSearch.classList.remove('ps-btn-active');
          scForm.style.display  = 'block';
          scLoad.style.display  = 'none';
          scTable.style.display = 'none';
          rows.forEach(function(row) {
            row.style.opacity   = '0';
            row.style.transform = 'translateY(8px)';
            var scoreEl = row.querySelector('.ps-score');
            var target  = row.dataset.demoScore;
            if (scoreEl && target) scoreEl.textContent = '0';
          });
          loaderTxt.textContent = 'Google Places wird abgefragt...';
        }

        function runDemo() {
          if (!isRunning) return;
          resetAll();

          /* Szene 1: Tippen */
          t(function() {
            typeIn(inBranche, 'Friseur', function() {
              typeIn(inStadt, 'München', function() {
                btnSearch.classList.add('ps-btn-active');

                /* Szene 2: Loader einblenden */
                t(function() {
                  if (!isRunning) return;
                  scForm.style.display  = 'none';
                  scLoad.style.display  = 'flex';
                }, 400);

                t(function() {
                  if (!isRunning) return;
                  loaderTxt.textContent = '12 Websites werden gescannt...';
                }, 1800);

                /* Szene 3: Tabelle */
                t(function() {
                  if (!isRunning) return;
                  scLoad.style.display  = 'none';
                  scTable.style.display = 'block';

                  rows.forEach(function(row, i) {
                    t(function() {
                      row.style.opacity   = '1';
                      row.style.transform = 'translateY(0)';
                      var scoreEl = row.querySelector('.ps-score');
                      var target  = parseInt(row.dataset.demoScore, 10);
                      if (scoreEl && target) animScore(scoreEl, target);
                    }, i * 400);
                  });

                  /* Szene 4: Fade-out → Loop */
                  t(function() {
                    wrap.style.opacity = '0';
                    t(function() {
                      if (isRunning) runDemo();
                    }, 600);
                  }, 4800);

                }, 3200);
              });
            });
          }, 300);
        }

        function startDemo() {
          if (isRunning) return;
          isRunning = true;
          runDemo();
        }

        function stopDemo() {
          isRunning = false;
          clearAll();
        }

        /* IntersectionObserver: Demo nur im Viewport laufen lassen */
        if ('IntersectionObserver' in window) {
          var io = new IntersectionObserver(function(entries) {
            entries.forEach(function(e) {
              if (e.isIntersecting) startDemo();
              else stopDemo();
            });
          }, { threshold: 0.25 });
          io.observe(wrap);
        } else {
          startDemo();
        }
      })();
      </script>

      <p class="mt-6 text-xs text-zinc-400">
        Animation zeigt reale Produktfunktion ·
        <a href="{{ route('register') }}" class="text-zinc-600 hover:text-zinc-900 underline underline-offset-2">
          Selbst ausprobieren →
        </a>
      </p>
    </div>
  </div>

  {{-- ── 3. STATS ── --}}
  <div class="border-t border-b border-zinc-200">
    <div class="max-w-6xl mx-auto px-6">
      <div class="grid grid-cols-3 divide-x divide-zinc-200">

        <div class="px-12 py-10">
          <div class="text-5xl font-bold text-zinc-900 tracking-tight leading-none">
            <span data-counter data-value="60">0</span>
            <span class="text-2xl text-zinc-400 font-normal"> Sek</span>
          </div>
          <div class="text-xs uppercase tracking-widest text-zinc-400 mt-2">Bis zur ersten Lead-Liste</div>
          <p class="text-sm text-zinc-500 mt-3">Branche eingeben, Stadt wählen — fertige Ergebnisliste in unter einer Minute.</p>
        </div>

        <div class="px-12 py-10">
          <div class="text-5xl font-bold text-zinc-900 tracking-tight leading-none">
            <span data-counter data-value="20">0</span><span>+</span>
          </div>
          <div class="text-xs uppercase tracking-widest text-zinc-400 mt-2">Scan-Kriterien pro Website</div>
          <p class="text-sm text-zinc-500 mt-3">SSL, Mobile, Impressum, CMS, Ladezeit, Broken Links, OG-Tags und mehr.</p>
        </div>

        <div class="px-12 py-10">
          <div class="text-5xl font-bold text-zinc-900 tracking-tight leading-none">
            <span style="display:inline-block; width:8px; height:8px; background:#22c55e; border-radius:50%; margin-right:8px; vertical-align:middle; animation:pulse 2s infinite;"></span>Live
          </div>
          <div class="text-xs uppercase tracking-widest text-zinc-400 mt-2">Echtzeitdaten via Google</div>
          <p class="text-sm text-zinc-500 mt-3">Keine gecachten Daten. Jede Suche greift live auf Google Places zu.</p>
        </div>

      </div>
    </div>
  </div>

  {{-- ── 3b. FÜR WEN? ── --}}
  <div class="py-20 border-t border-zinc-100">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-xs uppercase tracking-widest text-zinc-400 mb-3">Für wen?</div>
      <h2 class="text-2xl font-bold text-zinc-900 tracking-tight mb-10">
        Gemacht für alle die aktiv akquirieren.
      </h2>
      <div class="grid grid-cols-3 gap-6">

        <div class="border border-zinc-200 rounded-xl p-8 hover:border-zinc-400 transition-colors">
          <div class="text-sm font-semibold text-zinc-900 mb-3">Freelancer &amp; Webdesigner</div>
          <p class="text-sm text-zinc-500 leading-relaxed">
            Du arbeitest allein und brauchst einen konstanten Strom neuer Anfragen.
            PageScan zeigt dir welche Unternehmen in deiner Region dringend eine neue Website brauchen — mit konkreten Zahlen für den Erstkontakt.
          </p>
          <div class="mt-6 text-xs text-zinc-400">Beta-Zugang kostenlos sichern →</div>
        </div>

        <div class="border border-zinc-900 rounded-xl p-8 bg-zinc-900 text-white">
          <div class="text-sm font-semibold mb-3">Web- &amp; Digitalagenturen</div>
          <p class="text-sm leading-relaxed" style="color:#a1a1aa;">
            Dein Team braucht einen strukturierten Akquise-Prozess. Mit Watchlist, Lead-Kanban und Teamfunktionen verwaltet ihr alle Leads zentral — ohne externes CRM.
          </p>
          <div class="mt-6 text-xs" style="color:#71717a;">Beta-Zugang kostenlos sichern →</div>
        </div>

        <div class="border border-zinc-200 rounded-xl p-8 hover:border-zinc-400 transition-colors">
          <div class="text-sm font-semibold text-zinc-900 mb-3">SEO- &amp; Marketingagenturen</div>
          <p class="text-sm text-zinc-500 leading-relaxed">
            Du erkennst sofort welche Website-Mängel du beheben kannst — Missing Meta, kein Tracking, kein SSL. PageScan gibt dir den Gesprächseinstieg den kein anderes Tool liefert.
          </p>
          <div class="mt-6 text-xs text-zinc-400">Beta-Zugang kostenlos sichern →</div>
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

  {{-- ── 7c. FEATURES ── --}}
  <div class="py-20 border-t border-zinc-100">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-xs uppercase tracking-widest text-zinc-400 mb-3">Funktionen</div>
      <h2 class="text-2xl font-bold text-zinc-900 tracking-tight mb-10">Mehr als nur ein Scanner.</h2>
      <div class="grid grid-cols-3 gap-6">

        <div class="border border-zinc-200 rounded-xl p-6">
          <div class="text-sm font-semibold text-zinc-900 mb-2">Lead-Management</div>
          <p class="text-sm text-zinc-500 leading-relaxed">Status tracken (Neu → Kontaktiert → Angebot → Gewonnen), Priorität setzen, Notizen hinterlegen — ohne externes CRM.</p>
        </div>

        <div class="border border-zinc-200 rounded-xl p-6">
          <div class="text-sm font-semibold text-zinc-900 mb-2">Kanban-Board</div>
          <p class="text-sm text-zinc-500 leading-relaxed">Leads per Drag &amp; Drop durch die Pipeline schieben. Kompakter Überblick über alle Phasen deiner Akquise auf einen Blick.</p>
        </div>

        <div class="border border-zinc-200 rounded-xl p-6">
          <div class="text-sm font-semibold text-zinc-900 mb-2">Watchlist &amp; Auto-Scan</div>
          <p class="text-sm text-zinc-500 leading-relaxed">Suche einmal speichern, täglich automatisch wiederholen. Neue Unternehmen erscheinen sofort in deiner Lead-Liste.</p>
        </div>

        <div class="border border-zinc-200 rounded-xl p-6">
          <div class="text-sm font-semibold text-zinc-900 mb-2">Individueller Score</div>
          <p class="text-sm text-zinc-500 leading-relaxed">Passe die Gewichtung der Kriterien an dein Angebot an. SEO-Agentur oder Webdesigner? Der Score passt sich dir an.</p>
        </div>

        <div class="border border-zinc-200 rounded-xl p-6">
          <div class="text-sm font-semibold text-zinc-900 mb-2">Kontakthistorie</div>
          <p class="text-sm text-zinc-500 leading-relaxed">Wann wurde angerufen, welche E-Mail gesendet, welches Ergebnis? Alle Kontaktversuche chronologisch pro Lead gespeichert.</p>
        </div>

        <div class="border border-zinc-200 rounded-xl p-6">
          <div class="text-sm font-semibold text-zinc-900 mb-2">CSV-Export</div>
          <p class="text-sm text-zinc-500 leading-relaxed">Alle Leads mit Score, Kontaktdaten und Status als CSV exportieren — für eigene Auswertungen oder den Import in andere Tools.</p>
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
            <span class="text-sm font-medium text-zinc-800">Wie lange dauert die Freischaltung?</span>
            <span class="faq-icon text-zinc-300 flex-shrink-0 transition-transform duration-200">↓</span>
          </button>
          <p class="faq-answer hidden mt-3 text-sm text-zinc-500 leading-relaxed">
            Wir schalten Beta-Nutzer manuell frei — in der Regel innerhalb von 24 Stunden. Du bekommst eine E-Mail sobald dein Zugang aktiviert wurde.
          </p>
        </div>

        <div class="border-t border-zinc-100 py-5">
          <button class="faq-btn w-full flex justify-between items-center text-left gap-4" onclick="toggleFaq(this)">
            <span class="text-sm font-medium text-zinc-800">Was bedeutet "kostenlos" in der Beta?</span>
            <span class="faq-icon text-zinc-300 flex-shrink-0 transition-transform duration-200">↓</span>
          </button>
          <p class="faq-answer hidden mt-3 text-sm text-zinc-500 leading-relaxed">
            Während der Beta ist PageScan kostenlos — kein Trial, kein Ablaufdatum. Wir informieren dich rechtzeitig, bevor sich daran etwas ändert.
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


  {{-- ── 10. CLOSING CTA ── --}}
  <div class="py-20 text-center" style="background:#f4f4f5;">
    <div class="max-w-xl mx-auto px-6">
      <h2 class="text-3xl font-bold tracking-tight leading-tight mb-4" style="color:#09090b;">
        PageScan ist aktuell kostenlos.
      </h2>
      <p class="text-sm mb-8 leading-relaxed" style="color:#71717a;">
        Registriere dich jetzt und erhalte kostenlosen Zugang. Keine Kreditkarte, keine Verpflichtung.
      </p>
      <a href="{{ route('register') }}"
         class="inline-block text-sm font-medium px-8 py-3.5 rounded-lg transition-colors"
         style="background:#18181b; color:#ffffff;">
        Kostenlos registrieren →
      </a>
      <p class="mt-4 text-xs" style="color:#a1a1aa;">
        Bereits registriert?
        <a href="{{ route('login') }}" class="underline underline-offset-2" style="color:#71717a;">
          Hier anmelden
        </a>
      </p>
    </div>
  </div>

  {{-- ── 11. FOOTER ── --}}
  <footer style="border-top:1px solid #e4e4e7; background:#f9fafb; padding:48px 24px 32px;">
    <div style="max-width:1152px; margin:0 auto;">
      <div style="display:grid; grid-template-columns:2fr 1fr 1fr; gap:48px; margin-bottom:48px;">

        <div>
          <div style="font-weight:600; font-size:15px; color:#09090b; margin-bottom:8px;">PageScan</div>
          <p style="font-size:13px; color:#71717a; line-height:1.7; max-width:280px;">
            Finde Unternehmen mit schlechten Websites. Scanne live, speichere Leads, gewinne Kunden.
          </p>
        </div>

        <div>
          <div style="font-size:11px; font-weight:600; color:#a1a1aa; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:14px;">Produkt</div>
          <div style="display:flex; flex-direction:column; gap:10px;">
            <a href="#schritte" style="font-size:13px; color:#71717a; text-decoration:none;">So funktioniert's</a>
            <a href="{{ route('register') }}" style="font-size:13px; color:#71717a; text-decoration:none;">Kostenlos testen</a>
            <a href="{{ route('login') }}"    style="font-size:13px; color:#71717a; text-decoration:none;">Anmelden</a>
          </div>
        </div>

        <div>
          <div style="font-size:11px; font-weight:600; color:#a1a1aa; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:14px;">Rechtliches</div>
          <div style="display:flex; flex-direction:column; gap:10px;">
            <a href="{{ route('impressum') }}"   style="font-size:13px; color:#71717a; text-decoration:none;">Impressum</a>
            <a href="{{ route('datenschutz') }}" style="font-size:13px; color:#71717a; text-decoration:none;">Datenschutz</a>
          </div>
        </div>

      </div>
      <div style="border-top:1px solid #e4e4e7; padding-top:24px; display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:12px;">
        <span style="font-size:12px; color:#a1a1aa;">© {{ date('Y') }} PageScan. Alle Rechte vorbehalten.</span>
        <span style="font-size:12px; color:#a1a1aa;">Made in Germany</span>
      </div>
    </div>
  </footer>

  <script>
    /* ── HERO LOAD ANIMATION ─────────────────────────────────── */
    const heroItems  = document.querySelectorAll('.hero-item');
    const heroDelays = [0];
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
