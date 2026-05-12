User-agent: *
Disallow: /dashboard
Disallow: /search
Disallow: /leads
Disallow: /companies
Disallow: /watchlist
Disallow: /settings
Disallow: /profile
Disallow: /admin
Disallow: /billing
Disallow: /subscription
Disallow: /pricing
Disallow: /api/

Allow: /
Allow: /impressum
Allow: /datenschutz
Allow: /login
Allow: /register

Sitemap: {{ config('app.url') }}/sitemap.xml
