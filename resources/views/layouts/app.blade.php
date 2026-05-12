<!DOCTYPE html>
<html lang="en" style="height:100%;">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>PageScan</title>
  <link rel="icon" href="/favicon.ico" sizes="any">
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
  <style>[x-cloak]{display:none!important}</style>
</head>
<body style="min-height:100%; display:flex; flex-direction:column;">
  <nav class="nav">
    <a href="/dashboard" class="nav-brand">PageScan</a>
    <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'nav-link-active' : '' }}">Dashboard</a>
    <a href="/search"    class="nav-link {{ request()->is('search')    ? 'nav-link-active' : '' }}">Search</a>
    <a href="/leads"          class="nav-link {{ request()->is('leads*')       ? 'nav-link-active' : '' }}">Leads</a>
    <a href="/watchlist"      class="nav-link {{ request()->is('watchlist*')   ? 'nav-link-active' : '' }}">Watchlist</a>
    <a href="/settings/score" class="nav-link {{ request()->is('settings*')    ? 'nav-link-active' : '' }}">Score</a>
    @if(Auth::user()->isAdmin())
      <a href="/admin/users" class="nav-link {{ request()->is('admin/users*') ? 'nav-link-active' : '' }}">Team</a>
      <a href="/admin/waitlist" class="nav-link {{ request()->is('admin/waitlist*') ? 'nav-link-active' : '' }}">
        Warteliste
        @php $pendingCount = \App\Models\User::where('waitlist_status','waiting')->count(); @endphp
        @if($pendingCount > 0)
          <span style="display:inline-block; min-width:16px; height:16px; background:#dc2626; color:#fff; border-radius:8px; font-size:10px; font-weight:700; text-align:center; line-height:16px; margin-left:4px; padding:0 4px;">{{ $pendingCount }}</span>
        @endif
      </a>
    @endif
    <div class="nav-spacer"></div>
    <span class="nav-user">{{ Auth::user()->email }}</span>
    <a href="{{ route('profile.edit') }}" class="btn btn-sm" style="margin-right:4px;">Profile</a>
    <form method="POST" action="/logout" style="display:inline">
      @csrf
      <button type="submit" class="btn btn-sm">Log Out</button>
    </form>
  </nav>

  @if(!empty($waitlistBanner))
    <div style="
        background: rgba(5,150,105,0.08);
        border-bottom: 1px solid rgba(5,150,105,0.25);
        padding: 8px 24px;
        display: flex;
        align-items: center;
        gap: 8px;
    ">
      <span style="font-size: 13px; color: #059669;">{{ $waitlistBanner['message'] }}</span>
    </div>
  @endif

  <main style="flex:1;">
    {{ $slot }}
  </main>

  <footer style="border-top:1px solid var(--border); padding:16px 24px; display:flex; gap:16px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
    <span style="font-size:12px; color:var(--text-3);">© {{ date('Y') }} PageScan</span>
    <div style="display:flex; gap:16px;">
      <a href="/impressum"   style="font-size:12px; color:var(--text-3); text-decoration:none;">Legal Notice</a>
      <a href="/datenschutz" style="font-size:12px; color:var(--text-3); text-decoration:none;">Privacy Policy</a>
    </div>
  </footer>

  <x-cookie-banner />
</body>
</html>
