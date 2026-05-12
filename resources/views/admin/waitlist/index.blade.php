<x-app-layout>
  <div class="page" style="max-width:960px;">

    <div class="page-header">
      <div>
        <div class="page-title">Warteliste</div>
        <div class="page-subtitle">Nutzer-Freischaltung verwalten</div>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('info'))
      <div class="alert" style="background:rgba(37,99,235,0.08); border:1px solid rgba(37,99,235,0.2); color:#2563eb; padding:10px 14px; border-radius:6px; font-size:13px; margin-bottom:16px;">{{ session('info') }}</div>
    @endif
    @if($errors->any())
      <div class="alert alert-error">{{ $errors->first() }}</div>
    @endif

    {{-- Free-Tier Stats --}}
    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:12px; margin-bottom:20px;">
      <div class="card" style="padding:16px; text-align:center;">
        <div style="font-size:24px; font-weight:700; color:var(--text-1);">{{ $waiting->count() }}</div>
        <div style="font-size:12px; color:var(--text-3); margin-top:2px;">Wartend</div>
      </div>
      <div class="card" style="padding:16px; text-align:center;">
        <div style="font-size:24px; font-weight:700; color:#059669;">{{ $approvedCount }}</div>
        <div style="font-size:12px; color:var(--text-3); margin-top:2px;">Freigeschaltet</div>
      </div>
      <div class="card" style="padding:16px; text-align:center;">
        <div style="font-size:24px; font-weight:700; color:{{ $freeTierLeft > 0 ? '#2563eb' : '#dc2626' }};">
          {{ $freeTierLeft }} / {{ $freeTierSlots }}
        </div>
        <div style="font-size:12px; color:var(--text-3); margin-top:2px;">Kostenlos-Plätze frei</div>
      </div>
    </div>

    {{-- ─── Waiting Users ─── --}}
    @if($waiting->count() > 0)
    <div class="card" style="padding:0; overflow:hidden; margin-bottom:20px;">
      <div style="display:flex; align-items:center; justify-content:space-between; padding:14px 16px; border-bottom:1px solid var(--border); background:var(--bg-2);">
        <div style="font-weight:600; font-size:14px;">Warten auf Freischaltung ({{ $waiting->count() }})</div>
        <form method="POST" action="{{ route('admin.waitlist.approve-all') }}">
          @csrf
          <button type="submit" class="btn btn-primary btn-sm"
                  onclick="return confirm('Alle {{ $waiting->count() }} wartenden Nutzer freischalten?')">
            Alle freischalten
          </button>
        </form>
      </div>
      <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead>
          <tr style="border-bottom:1px solid var(--border);">
            <th style="text-align:left; padding:10px 16px; font-weight:500; color:var(--text-3);">Name</th>
            <th style="text-align:left; padding:10px 16px; font-weight:500; color:var(--text-3);">E-Mail</th>
            <th style="text-align:center; padding:10px 16px; font-weight:500; color:var(--text-3);">Registriert</th>
            <th style="padding:10px 16px;"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($waiting as $user)
            <tr style="border-bottom:1px solid var(--border);">
              <td style="padding:10px 16px;">{{ $user->name }}</td>
              <td style="padding:10px 16px; color:var(--text-2);">{{ $user->email }}</td>
              <td style="padding:10px 16px; text-align:center; color:var(--text-3);">{{ $user->created_at->format('d.m.Y') }}</td>
              <td style="padding:10px 16px; text-align:right; display:flex; gap:6px; justify-content:flex-end;">
                <form method="POST" action="{{ route('admin.waitlist.approve', $user) }}">
                  @csrf
                  <button type="submit" class="btn btn-primary btn-sm">Freischalten</button>
                </form>
                <form method="POST" action="{{ route('admin.waitlist.reject', $user) }}">
                  @csrf
                  <button type="submit" class="btn btn-sm" style="color:#dc2626;">Ablehnen</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @else
    <div class="card" style="padding:24px; text-align:center; color:var(--text-3); font-size:13px; margin-bottom:20px;">
      Keine wartenden Nutzer.
    </div>
    @endif

    {{-- ─── Approved Users ─── --}}
    @if($approved->count() > 0)
    <div class="card" style="padding:0; overflow:hidden; margin-bottom:20px;">
      <div style="padding:14px 16px; border-bottom:1px solid var(--border); background:var(--bg-2); font-weight:600; font-size:14px;">
        Freigeschaltete Nutzer ({{ $approved->count() }})
      </div>
      <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead>
          <tr style="border-bottom:1px solid var(--border);">
            <th style="text-align:left; padding:10px 16px; font-weight:500; color:var(--text-3);">Name</th>
            <th style="text-align:left; padding:10px 16px; font-weight:500; color:var(--text-3);">E-Mail</th>
            <th style="text-align:center; padding:10px 16px; font-weight:500; color:var(--text-3);">Tier</th>
            <th style="text-align:center; padding:10px 16px; font-weight:500; color:var(--text-3);">Freigeschaltet</th>
            <th style="padding:10px 16px;"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($approved as $user)
            <tr style="border-bottom:1px solid var(--border);">
              <td style="padding:10px 16px;">
                {{ $user->name }}
                @if($user->role === 'admin')
                  <span style="font-size:11px; background:#ede9fe; color:#7c3aed; padding:1px 6px; border-radius:12px; margin-left:4px;">Admin</span>
                @endif
              </td>
              <td style="padding:10px 16px; color:var(--text-2);">{{ $user->email }}</td>
              <td style="padding:10px 16px; text-align:center;">
                @if($user->is_free_tier)
                  <span style="background:#d1fae5; color:#065f46; padding:2px 8px; border-radius:12px; font-size:11px; font-weight:500;">Kostenlos</span>
                @else
                  <span style="background:#f1f5f9; color:#475569; padding:2px 8px; border-radius:12px; font-size:11px;">Standard</span>
                @endif
              </td>
              <td style="padding:10px 16px; text-align:center; color:var(--text-3);">
                {{ $user->approved_at?->format('d.m.Y') ?? '–' }}
              </td>
              <td style="padding:10px 16px; text-align:right;">
                @if($user->id !== auth()->id())
                  <form method="POST" action="{{ route('admin.waitlist.reject', $user) }}" style="display:inline;">
                    @csrf
                    <button type="submit" class="btn btn-sm" style="color:#dc2626; font-size:12px;"
                            onclick="return confirm('Zugang für {{ $user->name }} wirklich entziehen?')">
                      Entziehen
                    </button>
                  </form>
                @endif
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif

    {{-- ─── Rejected Users ─── --}}
    @if($rejected->count() > 0)
    <div class="card" style="padding:0; overflow:hidden;">
      <div style="padding:14px 16px; border-bottom:1px solid var(--border); background:var(--bg-2); font-weight:600; font-size:14px; color:var(--text-2);">
        Abgelehnte Nutzer ({{ $rejected->count() }})
      </div>
      <table style="width:100%; border-collapse:collapse; font-size:13px;">
        <thead>
          <tr style="border-bottom:1px solid var(--border);">
            <th style="text-align:left; padding:10px 16px; font-weight:500; color:var(--text-3);">Name</th>
            <th style="text-align:left; padding:10px 16px; font-weight:500; color:var(--text-3);">E-Mail</th>
            <th style="text-align:center; padding:10px 16px; font-weight:500; color:var(--text-3);">Registriert</th>
            <th style="padding:10px 16px;"></th>
          </tr>
        </thead>
        <tbody>
          @foreach($rejected as $user)
            <tr style="border-bottom:1px solid var(--border); opacity:0.7;">
              <td style="padding:10px 16px;">{{ $user->name }}</td>
              <td style="padding:10px 16px; color:var(--text-3);">{{ $user->email }}</td>
              <td style="padding:10px 16px; text-align:center; color:var(--text-3);">{{ $user->created_at->format('d.m.Y') }}</td>
              <td style="padding:10px 16px; text-align:right;">
                <form method="POST" action="{{ route('admin.waitlist.approve', $user) }}" style="display:inline;">
                  @csrf
                  <button type="submit" class="btn btn-sm btn-primary" style="font-size:12px;">Doch freischalten</button>
                </form>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
    @endif

  </div>
</x-app-layout>
