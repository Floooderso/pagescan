<x-app-layout>
  <div class="page">
    <div class="page-header">
      <div>
        <div style="font-size:13px; color:var(--text-3); margin-bottom:2px;">{{ $greeting }},</div>
        <div class="page-title">{{ Auth::user()->name }}</div>
      </div>
      <a href="/search" class="btn btn-primary">Neue Suche</a>
    </div>

    @if($stats['gesamt'] === 0)
    <!-- Leerzustand -->
    <div class="card" style="padding:48px; text-align:center; margin-bottom:24px;">
      <div style="font-weight:600; font-size:16px; margin-bottom:8px;">Noch keine Leads vorhanden</div>
      <div style="color:var(--text-3); font-size:13px; margin-bottom:20px;">
        Starte eine Suche, finde Unternehmen und füge sie als Leads hinzu.
      </div>
      <a href="/search" class="btn btn-primary">Zur Suche</a>
    </div>
    @else

    <!-- Stat-Karten -->
    <div class="stats-grid" style="margin-bottom:24px;">
      <div class="card stat-card">
        <div class="stat-label">Leads gesamt</div>
        <div class="stat-value">{{ $stats['gesamt'] }}</div>
      </div>
      <div class="card stat-card">
        <div class="stat-label">Neu</div>
        <div class="stat-value">{{ $stats['neu'] }}</div>
      </div>
      <div class="card stat-card">
        <div class="stat-label">Kontaktiert</div>
        <div class="stat-value">{{ $stats['kontaktiert'] }}</div>
      </div>
      <div class="card stat-card">
        <div class="stat-label">Angebot</div>
        <div class="stat-value">{{ $stats['angebot'] }}</div>
      </div>
      <div class="card stat-card">
        <div class="stat-label" style="color:#16a34a;">Gewonnen</div>
        <div class="stat-value" style="color:#16a34a;">{{ $stats['gewonnen'] }}</div>
      </div>
      <div class="card stat-card">
        <div class="stat-label" style="color:#dc2626;">Verloren</div>
        <div class="stat-value" style="color:#dc2626;">{{ $stats['verloren'] }}</div>
      </div>
    </div>

    <!-- Letzte Leads -->
    <div class="card" style="margin-bottom:24px;">
      <div style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
        <span style="font-weight:500; font-size:14px;">Letzte Leads</span>
        <a href="/leads" style="font-size:12px; color:var(--text-2);">Alle anzeigen</a>
      </div>
      <div class="table-scroll"><table>
        <thead>
          <tr>
            <th>Unternehmen</th>
            <th>Status</th>
            <th>Score</th>
            <th>Website</th>
          </tr>
        </thead>
        <tbody>
          @foreach($recentLeads as $lead)
          <tr>
            <td>
              <a href="/companies/{{ $lead->company->id }}" style="text-decoration:none; color:inherit;">
                <div style="font-weight:500;">{{ $lead->company->name }}</div>
                <div style="font-size:11px; color:var(--text-3);">{{ $lead->company->branche }} · {{ $lead->company->stadt }}</div>
              </a>
            </td>
            <td>
              @php $statusColors = ['neu'=>'badge-gray','kontaktiert'=>'badge-yellow','angebot'=>'badge-yellow','gewonnen'=>'badge-green','verloren'=>'badge-red'] @endphp
              <span class="{{ $statusColors[$lead->status] ?? 'badge-gray' }}" style="text-transform:capitalize;">{{ $lead->status }}</span>
            </td>
            <td>
              @if($lead->scan?->score !== null)
                @if($lead->scan->score >= 70)
                  <span class="badge-green">{{ $lead->scan->score }}/100</span>
                @elseif($lead->scan->score >= 40)
                  <span class="badge-yellow">{{ $lead->scan->score }}/100</span>
                @else
                  <span class="badge-red">{{ $lead->scan->score }}/100</span>
                @endif
              @else
                <span style="color:var(--text-3);">—</span>
              @endif
            </td>
            <td style="font-size:12px;">
              @if($lead->company->website_url)
                <a href="{{ $lead->company->website_url }}" target="_blank" style="color:var(--text-2);">
                  {{ parse_url($lead->company->website_url, PHP_URL_HOST) }}
                </a>
              @else
                <span class="badge-red">Keine Website</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table></div>
    </div>
    @endif

    <!-- Letzte Suche -->
    @if($lastSearch)
    <div>
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
        <div>
          <div style="font-weight:600; font-size:14px;">Letzte Suchanfrage</div>
          <div style="font-size:12px; color:var(--text-3); margin-top:2px;">
            {{ $lastSearch['branche'] }} in {{ $lastSearch['stadt'] }} · {{ $lastSearch['at'] }}
          </div>
        </div>
        <a href="/search?branche={{ urlencode($lastSearch['branche']) }}&stadt={{ urlencode($lastSearch['stadt']) }}" class="btn btn-primary">
          Erneut suchen
        </a>
      </div>

      @if(!empty($lastSearch['previews']))
      <div style="display:flex; flex-direction:column; gap:8px;">
        @foreach($lastSearch['previews'] as $p)
        <div class="card" style="padding:14px 18px; display:flex; align-items:center; gap:16px; flex-wrap:wrap;">
          <div style="flex:1; min-width:0;">
            <div style="font-weight:500; font-size:13px;">{{ $p['name'] }}</div>
            <div style="font-size:11px; color:var(--text-3); margin-top:2px;">{{ $p['adresse'] }}</div>
          </div>
          <div style="display:flex; align-items:center; gap:12px; flex-shrink:0; flex-wrap:wrap;">
            @if($p['rating'])
              <span style="font-size:12px;">⭐ {{ number_format($p['rating'], 1) }}
                <span style="color:var(--text-3);">({{ $p['bewertungen'] }})</span>
              </span>
            @endif
            @if($p['has_website'])
              <span class="badge-green" style="font-size:11px;">Website</span>
            @else
              <span class="badge-red" style="font-size:11px;">Keine Website</span>
            @endif
          </div>
        </div>
        @endforeach
      </div>
      @endif
    </div>
    @endif
  </div>
</x-app-layout>
