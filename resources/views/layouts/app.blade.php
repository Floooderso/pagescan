<!DOCTYPE html>
<html lang="de" style="height:100%;">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>PageScan</title>
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body style="min-height:100%; display:flex; flex-direction:column;">
  <nav class="nav">
    <a href="/dashboard" class="nav-brand">PageScan</a>
    <a href="/dashboard" class="nav-link {{ request()->is('dashboard') ? 'nav-link-active' : '' }}">Dashboard</a>
    <a href="/search"    class="nav-link {{ request()->is('search')    ? 'nav-link-active' : '' }}">Suche</a>
    <a href="/leads"          class="nav-link {{ request()->is('leads*')       ? 'nav-link-active' : '' }}">Leads</a>
    <a href="/watchlist"      class="nav-link {{ request()->is('watchlist*')   ? 'nav-link-active' : '' }}">Watchlist</a>
    <a href="/settings/score" class="nav-link {{ request()->is('settings*')    ? 'nav-link-active' : '' }}">Score</a>
    @if(Auth::user()->isAdmin())
      <a href="/admin/users" class="nav-link {{ request()->is('admin*') ? 'nav-link-active' : '' }}">Team</a>
    @endif
    <div class="nav-spacer"></div>
    <span class="nav-user">{{ Auth::user()->email }}</span>
    <form method="POST" action="/logout" style="display:inline">
      @csrf
      <button type="submit" class="btn btn-sm">Abmelden</button>
    </form>
  </nav>

  <main style="flex:1;">
    {{ $slot }}
  </main>

  <footer style="border-top:1px solid var(--border); padding:16px 24px; display:flex; gap:16px; align-items:center; justify-content:space-between; flex-wrap:wrap;">
    <span style="font-size:12px; color:var(--text-3);">© {{ date('Y') }} PageScan</span>
    <div style="display:flex; gap:16px;">
      <a href="/impressum"   style="font-size:12px; color:var(--text-3); text-decoration:none;">Impressum</a>
      <a href="/datenschutz" style="font-size:12px; color:var(--text-3); text-decoration:none;">Datenschutz</a>
    </div>
  </footer>
</body>
</html>
