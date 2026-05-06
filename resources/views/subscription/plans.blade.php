<x-app-layout>
  <div class="page">
    <div class="page-header">
      <div>
        <div class="page-title">Pläne & Preise</div>
        <div class="page-subtitle">Wähle den passenden Plan für dein Business</div>
      </div>
    </div>

    @if(session('trial_expired'))
      <div class="alert alert-error" style="display:flex; align-items:center; gap:10px; margin-bottom:24px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        Dein 14-tägiger Trial ist abgelaufen. Wähle einen Plan um weiterzumachen.
      </div>
    @endif

    @if(session('error'))
      <div class="alert alert-error">{{ session('error') }}</div>
    @endif

    @if(session('info'))
      <div class="alert alert-info">{{ session('info') }}</div>
    @endif

    @if(isset($trialBanner) && $trialBanner && $trialBanner['type'] === 'active')
      <div class="alert alert-info" style="display:flex; align-items:center; gap:10px; margin-bottom:24px;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
        {{ $trialBanner['message'] }} Wähle jetzt deinen Plan um nach dem Trial nahtlos weiterzumachen.
      </div>
    @endif

    <div style="display:grid; grid-template-columns:repeat(auto-fit,minmax(260px,1fr)); gap:20px; max-width:900px; margin:0 auto;">

      @foreach($plans as $key => $plan)
      <div class="card" style="padding:28px; position:relative; {{ $key === 'agency' ? 'border:2px solid var(--accent);' : '' }}">
        @if($key === 'agency')
          <div style="position:absolute; top:-12px; left:50%; transform:translateX(-50%); background:var(--accent); color:#fff; font-size:11px; font-weight:600; padding:3px 12px; border-radius:20px; white-space:nowrap;">
            Beliebt
          </div>
        @endif

        <div style="font-size:18px; font-weight:700; margin-bottom:6px;">{{ $plan['name'] }}</div>
        <div style="font-size:32px; font-weight:800; margin-bottom:4px;">
          €{{ $plan['price'] }}<span style="font-size:14px; font-weight:400; color:var(--text-3);">/Monat</span>
        </div>

        <div style="margin:20px 0; padding:20px 0; border-top:1px solid var(--border); border-bottom:1px solid var(--border);">
          <div style="display:flex; flex-direction:column; gap:10px; font-size:13px;">
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="color:#16a34a; font-weight:600;">✓</span>
              <span>{{ $plan['scans'] === 0 ? 'Unbegrenzte Scans' : number_format($plan['scans']) . ' Scans/Monat' }}</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="color:#16a34a; font-weight:600;">✓</span>
              <span>{{ $plan['users'] }} {{ $plan['users'] === 1 ? 'Nutzer' : 'Nutzer' }}</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="color:#16a34a; font-weight:600;">✓</span>
              <span>Lead-Management</span>
            </div>
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="color:#16a34a; font-weight:600;">✓</span>
              <span>Watchlist & Auto-Scan</span>
            </div>
            @if($key !== 'starter')
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="color:#16a34a; font-weight:600;">✓</span>
              <span>Team-Features</span>
            </div>
            @endif
            @if($key === 'pro')
            <div style="display:flex; align-items:center; gap:8px;">
              <span style="color:#16a34a; font-weight:600;">✓</span>
              <span>Prioritäts-Support</span>
            </div>
            @endif
          </div>
        </div>

        @if($user && method_exists($user, 'subscribed') && $user->subscribed('default'))
          <form action="/billing" method="GET">
            <button type="submit" class="btn {{ $key === 'agency' ? 'btn-primary' : '' }}" style="width:100%; justify-content:center;">
              Abo verwalten
            </button>
          </form>
        @else
          <form action="{{ route('subscription.checkout') }}" method="POST">
            @csrf
            <input type="hidden" name="plan" value="{{ $key }}">
            <button type="submit" class="btn {{ $key === 'agency' ? 'btn-primary' : '' }}" style="width:100%; justify-content:center;">
              @if($user && $user->trial_ends_at && $user->trial_ends_at->isFuture())
                Jetzt upgraden
              @elseif($user && $user->trial_ends_at && $user->trial_ends_at->isPast())
                Jetzt abonnieren
              @else
                Jetzt starten
              @endif
            </button>
          </form>
        @endif
      </div>
      @endforeach

    </div>

    <div style="text-align:center; margin-top:32px; color:var(--text-3); font-size:13px;">
      Kostenloser Trial: 10 Scans ohne Kreditkarte · Jederzeit kündbar · Keine versteckten Kosten
    </div>
  </div>
</x-app-layout>
