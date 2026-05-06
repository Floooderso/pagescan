<x-app-layout>
  <div class="page">

    <!-- Header -->
    <div class="page-header">
      <div>
        <div class="page-title">{{ $company->name }}</div>
        <div class="page-subtitle">{{ $company->branche }} · {{ $company->stadt }}</div>
      </div>
      <div style="display:flex; gap:8px;">
        <a href="/leads" class="btn">Zurück</a>
        @if($company->website_url)
        <form method="POST" action="/companies/{{ $company->id }}/scan">
          @csrf
          <button type="submit" class="btn btn-primary">Neu scannen</button>
        </form>
        @endif
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Obere Reihe: Bild + Stammdaten -->
    <div style="display:grid; grid-template-columns:280px 1fr; gap:16px; margin-bottom:16px;" class="detail-grid">

      <!-- Bild -->
      <div class="card" style="padding:0; overflow:hidden; display:flex; align-items:center; justify-content:center; min-height:200px; background:var(--bg);">
        @if($company->screenshot_path)
          <img src="{{ asset('storage/' . $company->screenshot_path) }}" alt="{{ $company->name }}"
               style="width:100%; height:220px; object-fit:cover; object-position:top; display:block;">
        @elseif($company->website_url)
          <img src="https://image.thum.io/get/width/600/crop/400/noanimate/{{ $company->website_url }}"
               alt="{{ $company->name }}"
               style="width:100%; height:220px; object-fit:cover; object-position:top; display:block;"
               onerror="this.style.display='none'">
        @else
          <span style="font-size:12px; color:var(--text-3);">Kein Bild vorhanden</span>
        @endif
      </div>

      <!-- Stammdaten -->
      <div class="card" style="padding:24px;">
        <div style="font-weight:600; font-size:14px; margin-bottom:16px;">Kontaktdaten</div>

        @if($company->rating)
        <div class="detail-row">
          <span class="detail-label">Bewertung</span>
          <span>⭐ {{ number_format($company->rating, 1) }} <span style="color:var(--text-3); font-size:12px;">({{ $company->bewertungen }} Bewertungen)</span></span>
        </div>
        @endif

        @if($company->adresse)
        <div class="detail-row">
          <span class="detail-label">Adresse</span>
          <span>{{ $company->adresse }}</span>
        </div>
        @endif

        @if($company->plz)
        <div class="detail-row">
          <span class="detail-label">PLZ</span>
          <span>{{ $company->plz }}</span>
        </div>
        @endif

        @if($company->telefon)
        <div class="detail-row">
          <span class="detail-label">Telefon</span>
          <a href="tel:{{ $company->telefon }}" style="color:var(--text-1);">{{ $company->telefon }}</a>
        </div>
        @endif

        @if($company->email)
        <div class="detail-row">
          <span class="detail-label">E-Mail</span>
          <a href="mailto:{{ $company->email }}" style="color:var(--text-2);">{{ $company->email }}</a>
        </div>
        @endif

        @if($company->website_url)
        <div class="detail-row">
          <span class="detail-label">Website</span>
          <a href="{{ $company->website_url }}" target="_blank" style="color:var(--text-2); word-break:break-all;">
            {{ $company->website_url }}
          </a>
        </div>
        @else
        <div class="detail-row">
          <span class="detail-label">Website</span>
          <span class="badge-red">Keine Website</span>
        </div>
        @endif

        @if($lead)
        <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--border); display:flex; gap:8px; flex-wrap:wrap;">
          @php $statusColors = ['neu'=>'badge-gray','kontaktiert'=>'badge-yellow','angebot'=>'badge-yellow','gewonnen'=>'badge-green','verloren'=>'badge-red'] @endphp
          <span class="{{ $statusColors[$lead->status] ?? 'badge-gray' }}" style="text-transform:capitalize;">{{ $lead->status }}</span>
          <span class="badge-gray" style="text-transform:capitalize;">Priorität: {{ $lead->priority }}</span>
        </div>
        @endif
      </div>
    </div>

    @if($scan)
    <!-- Scan-Ergebnisse -->
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;" class="detail-grid">

      <!-- Score + Checks -->
      <div class="card" style="padding:24px;">
        <div style="display:flex; align-items:center; gap:16px; margin-bottom:20px;">
          @php $cls = $scan->score >= 70 ? 'good' : ($scan->score >= 40 ? 'medium' : 'bad'); @endphp
          <div class="score-circle {{ $cls }}">{{ $scan->score }}</div>
          <div>
            <div style="font-weight:600; font-size:14px;">Website Score</div>
            <div style="color:var(--text-3); font-size:12px;">Gescannt: {{ $scan->scanned_at?->format('d.m.Y H:i') ?? '–' }}</div>
          </div>
        </div>

        @foreach([
          ['HTTP erreichbar',  $scan->http_status == 200, $scan->http_status],
          ['SSL-Zertifikat',   $scan->has_ssl,            null],
          ['Mobile optimiert', $scan->is_mobile,          null],
          ['Kontaktdaten',     $scan->has_contact,        null],
          ['Impressum',        $scan->has_impressum,      null],
          ['Tracking',         $scan->has_tracking,       null],
          ['Favicon',          $scan->has_favicon,        null],
          ['Open Graph Tags',  $scan->has_og_tags,        null],
          ['Google Maps',      $scan->has_google_maps,    null],
          ['Cookie-Banner',    $scan->has_cookie_banner,  null],
        ] as [$label, $val, $extra])
        <div class="check-row">
          <span class="check-icon {{ $val ? 'ok' : 'fail' }}">{{ $val ? '✓' : '✗' }}</span>
          <span>{{ $label }}</span>
          @if($extra)
            <span style="margin-left:auto; color:var(--text-3); font-size:12px;">{{ $extra }}</span>
          @endif
        </div>
        @endforeach

        @if($scan->alt_tag_ratio !== null)
        <div class="check-row">
          <span class="check-icon {{ $scan->alt_tag_ratio >= 0.8 ? 'ok' : 'fail' }}">{{ $scan->alt_tag_ratio >= 0.8 ? '✓' : '✗' }}</span>
          <span>Alt-Texte</span>
          <span style="margin-left:auto; color:var(--text-3); font-size:12px;">{{ round($scan->alt_tag_ratio * 100) }}% der Bilder</span>
        </div>
        @endif

        @if($scan->broken_links_count !== null)
        <div class="check-row">
          <span class="check-icon {{ $scan->broken_links_count === 0 ? 'ok' : 'fail' }}">{{ $scan->broken_links_count === 0 ? '✓' : '✗' }}</span>
          <span>{{ $scan->broken_links_count === 0 ? 'Alle Links OK' : $scan->broken_links_count . ' fehlerhafte Links' }}</span>
        </div>
        @endif
      </div>

      <!-- Technische Details + Seiten-Info -->
      <div class="card" style="padding:24px;">
        <div style="font-weight:600; font-size:14px; margin-bottom:16px;">Technische Details</div>

        @if($scan->response_ms)
        <div class="detail-row">
          <span class="detail-label">Ladezeit</span>
          <span>{{ $scan->response_ms }}ms
            @if($scan->speed_rating)
              @php $sc = ['schnell'=>'badge-green','mittel'=>'badge-yellow','langsam'=>'badge-red'][$scan->speed_rating] ?? 'badge-gray' @endphp
              <span class="{{ $sc }}" style="margin-left:6px;">{{ $scan->speed_rating }}</span>
            @endif
          </span>
        </div>
        @endif

        @if($scan->cms)
        <div class="detail-row">
          <span class="detail-label">CMS</span>
          <span>{{ $scan->cms }}</span>
        </div>
        @endif

        @if($scan->http_status)
        <div class="detail-row">
          <span class="detail-label">HTTP Status</span>
          <span>{{ $scan->http_status }}</span>
        </div>
        @endif

        @if($scan->page_title)
        <div class="detail-row" style="align-items:flex-start;">
          <span class="detail-label">Seitentitel</span>
          <span style="font-style:italic; color:var(--text-2);">{{ $scan->page_title }}</span>
        </div>
        @endif

        @if($scan->meta_description)
        <div style="margin-top:16px; padding-top:16px; border-top:1px solid var(--border);">
          <div style="font-size:11px; font-weight:500; color:var(--text-3); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:6px;">Meta-Description</div>
          <div style="font-size:13px; color:var(--text-2); line-height:1.5;">{{ $scan->meta_description }}</div>
        </div>
        @endif

        @if(!$scan->response_ms && !$scan->cms && !$scan->page_title && !$scan->meta_description)
        <div style="color:var(--text-3); font-size:13px;">Keine weiteren Details verfügbar.</div>
        @endif
      </div>
    </div>
    @else
    <div class="card" style="padding:32px; text-align:center; color:var(--text-3); font-size:13px; margin-bottom:16px;">
      Noch kein Scan vorhanden.
      @if($company->website_url)
        Klicke auf "Neu scannen" um die Website zu analysieren.
      @endif
    </div>
    @endif

    <!-- Notizen (wenn Lead vorhanden) -->
    @if($lead)
    <div class="card" style="padding:24px;">
      <div style="font-weight:600; font-size:14px; margin-bottom:12px;">Notizen</div>
      <div style="font-size:13px; color:var(--text-2); line-height:1.6; white-space:pre-wrap;">{{ $lead->notes ?: '–' }}</div>
    </div>
    @endif

  </div>
</x-app-layout>
