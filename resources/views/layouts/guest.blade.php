<!DOCTYPE html>
<html lang="de">
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
<body>
  <div style="min-height:100vh; display:flex; flex-direction:column; align-items:center; justify-content:center; padding:24px; background:var(--bg);">
    <div style="font-weight:600; font-size:18px; color:var(--text-1); margin-bottom:32px;">PageScan</div>
    {{ $slot }}
  </div>
</body>
</html>
