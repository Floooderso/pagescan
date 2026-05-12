<x-app-layout>
  <div class="page">
    @php
        // Onboarding-Banner: zeige nur wenn keine Leads, keine Watchlist und noch nicht weggeklickt
        $showOnboarding = $stats['gesamt'] === 0
            && !\App\Models\SavedSearch::where('user_id', auth()->id())->exists()
            && !session('onboarding_dismissed');
    @endphp
    @if($showOnboarding)
      <div id="onboarding-banner" class="alert alert-info" style="margin-bottom:20px; padding:20px 24px;">
        <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
          <div style="flex:1; min-width:0;">
            <div style="font-weight:600; font-size:14px; margin-bottom:12px;">Welcome to PageScan — here's how to get started</div>
            <div style="display:flex; flex-direction:column; gap:10px;">
              <div style="display:flex; align-items:center; gap:10px; font-size:13px;">
                <span style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; background:var(--text-1); color:var(--bg-card); font-size:11px; font-weight:700; flex-shrink:0;">1</span>
                <span><strong>Start a search</strong> — Enter an industry and city to find local businesses.</span>
                <a href="/search" class="btn btn-primary" style="font-size:11px; padding:4px 12px; flex-shrink:0;">Go to Search</a>
              </div>
              <div style="display:flex; align-items:center; gap:10px; font-size:13px;">
                <span style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; background:var(--text-1); color:var(--bg-card); font-size:11px; font-weight:700; flex-shrink:0;">2</span>
                <span><strong>Save a lead</strong> — Click on a business and save it as a lead for your CRM.</span>
              </div>
              <div style="display:flex; align-items:center; gap:10px; font-size:13px;">
                <span style="display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:50%; background:var(--text-1); color:var(--bg-card); font-size:11px; font-weight:700; flex-shrink:0;">3</span>
                <span><strong>Set up Watchlist</strong> — Save your search and automatically receive new leads.</span>
                <a href="{{ route('watchlist.index') }}" class="btn" style="font-size:11px; padding:4px 12px; flex-shrink:0; border:1px solid var(--border);">Go to Watchlist</a>
              </div>
            </div>
          </div>
          <form method="POST" action="{{ route('onboarding.dismiss') }}" style="flex-shrink:0;">
            @csrf
            <button type="submit" style="background:none; border:none; cursor:pointer; color:var(--text-3); font-size:18px; padding:0 4px; line-height:1;" title="Dismiss">&times;</button>
          </form>
        </div>
      </div>
    @endif

    {{-- Trial banner removed — waitlist model active --}}
    <div class="page-header">
      <div>
        <div style="font-size:13px; color:var(--text-3); margin-bottom:2px;">{{ $greeting }},</div>
        <div class="page-title">{{ Auth::user()->name }}</div>
      </div>
      <a href="/search" class="btn btn-primary">New Search</a>
    </div>

    <!-- Quota -->
    <div class="card" style="padding:16px 20px; margin-bottom:20px;">
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px; flex-wrap:wrap; gap:8px;">
        <div style="font-size:13px; font-weight:500;">Scan Quota</div>
        <div style="font-size:12px; color:var(--text-3);">
          {{ $scanUsed }} / {{ $scanLimit === 0 ? '∞' : number_format($scanLimit) }} scans used
        </div>
      </div>
      <div style="background:var(--bg); border-radius:4px; height:6px; overflow:hidden;">
        <div style="height:6px; border-radius:4px; transition:width 0.3s;
          background: {{ $scanPercent >= 90 ? '#dc2626' : ($scanPercent >= 70 ? '#ca8a04' : 'var(--text-1)') }};
          width: {{ $scanPercent }}%;">
        </div>
      </div>
      @if($scanPercent >= 90)
        <div style="font-size:11px; color:#dc2626; margin-top:6px;">
          Scan-Limit fast erreicht — bitte kontaktiere uns für mehr Kapazität.
        </div>
      @endif
    </div>

    @if($stats['gesamt'] === 0)
    <!-- Empty state -->
    <div class="card" style="padding:48px; text-align:center; margin-bottom:24px;">
      <div style="font-weight:600; font-size:16px; margin-bottom:8px;">No leads yet</div>
      <div style="color:var(--text-3); font-size:13px; margin-bottom:20px;">
        Start a search, find businesses and add them as leads.
      </div>
      <a href="/search" class="btn btn-primary">Go to Search</a>
    </div>
    @else

    <!-- Stats -->
    <div class="stats-grid" style="margin-bottom:24px;">
      <div class="card stat-card">
        <div class="stat-label">Total Leads</div>
        <div class="stat-value">{{ $stats['gesamt'] }}</div>
      </div>
      <div class="card stat-card">
        <div class="stat-label">New</div>
        <div class="stat-value">{{ $stats['neu'] }}</div>
      </div>
      <div class="card stat-card">
        <div class="stat-label">Contacted</div>
        <div class="stat-value">{{ $stats['kontaktiert'] }}</div>
      </div>
      <div class="card stat-card">
        <div class="stat-label">Proposal</div>
        <div class="stat-value">{{ $stats['angebot'] }}</div>
      </div>
      <div class="card stat-card">
        <div class="stat-label" style="color:#16a34a;">Won</div>
        <div class="stat-value" style="color:#16a34a;">{{ $stats['gewonnen'] }}</div>
      </div>
      <div class="card stat-card">
        <div class="stat-label" style="color:#dc2626;">Lost</div>
        <div class="stat-value" style="color:#dc2626;">{{ $stats['verloren'] }}</div>
      </div>
    </div>

    <!-- Recent Leads -->
    <div class="card" style="margin-bottom:24px;">
      <div style="padding:16px 20px; border-bottom:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
        <span style="font-weight:500; font-size:14px;">Recent Leads</span>
        <a href="/leads" style="font-size:12px; color:var(--text-2);">View all</a>
      </div>
      <div class="table-scroll"><table>
        <thead>
          <tr>
            <th>Business</th>
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
              @php
                $statusColors  = ['neu'=>'badge-gray','kontaktiert'=>'badge-yellow','angebot'=>'badge-yellow','gewonnen'=>'badge-green','verloren'=>'badge-red'];
                $statusLabels  = ['neu'=>'New','kontaktiert'=>'Contacted','angebot'=>'Proposal','gewonnen'=>'Won','verloren'=>'Lost'];
              @endphp
              <span class="{{ $statusColors[$lead->status] ?? 'badge-gray' }}">{{ $statusLabels[$lead->status] ?? $lead->status }}</span>
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
                <span class="badge-red">No Website</span>
              @endif
            </td>
          </tr>
          @endforeach
        </tbody>
      </table></div>
    </div>
    @endif

    <!-- Last Search -->
    @if($lastSearch)
    <div>
      <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:12px; flex-wrap:wrap; gap:8px;">
        <div>
          <div style="font-weight:600; font-size:14px;">Last Search</div>
          <div style="font-size:12px; color:var(--text-3); margin-top:2px;">
            {{ $lastSearch['branche'] }} in {{ $lastSearch['stadt'] }} · {{ $lastSearch['at'] }}
          </div>
        </div>
        <a href="/search?branche={{ urlencode($lastSearch['branche']) }}&stadt={{ urlencode($lastSearch['stadt']) }}" class="btn btn-primary">
          Search Again
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
              <span class="badge-green" style="font-size:11px;">Has Website</span>
            @else
              <span class="badge-red" style="font-size:11px;">No Website</span>
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
