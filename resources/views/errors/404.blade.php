<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Seite nicht gefunden – PageScan</title>
  <link rel="icon" href="/favicon.svg" type="image/svg+xml">
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:400,500,600&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css'])
</head>
<body style="min-height:100vh; display:flex; flex-direction:column; font-family:'Inter',system-ui,sans-serif;">

  <nav class="nav">
    <a href="/" class="nav-brand">PageScan</a>
  </nav>

  <div style="flex:1; display:flex; align-items:center; justify-content:center; padding:48px 24px;">
    <div style="text-align:center; max-width:400px;">
      <div style="font-size:72px; font-weight:700; color:var(--border); line-height:1; margin-bottom:16px;">404</div>
      <div style="font-size:20px; font-weight:600; color:var(--text-1); margin-bottom:8px;">Seite nicht gefunden</div>
      <p style="font-size:14px; color:var(--text-3); line-height:1.6; margin-bottom:32px;">
        Die gesuchte Seite existiert nicht oder wurde verschoben.
      </p>
      <div style="display:flex; gap:10px; justify-content:center; flex-wrap:wrap;">
        <a href="/" class="btn btn-primary">Zur Startseite</a>
        @auth
          <a href="/dashboard" class="btn">Zum Dashboard</a>
        @endauth
      </div>
    </div>
  </div>

  <footer style="border-top:1px solid var(--border); padding:16px 24px; text-align:center;">
    <span style="font-size:12px; color:var(--text-3);">© {{ date('Y') }} PageScan</span>
  </footer>

</body>
</html>
