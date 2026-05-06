@component('mail::message')
# Willkommen bei PageScan, {{ $user->name }}!

Danke für deine Registrierung. PageScan hilft dir dabei, lokale Unternehmen mit verbesserungswürdigem Web-Auftritt zu finden — perfekte Leads für deine Webdesign- oder SEO-Agentur.

## So geht's los

1. **Suche starten** – Branche und Stadt eingeben, fertig
2. **Websites scannen** – Jede gefundene Website wird automatisch bewertet (Score 0–100)
3. **Leads speichern** – Die vielversprechendsten Kontakte als Leads markieren

@component('mail::button', ['url' => config('app.url') . '/dashboard'])
Zum Dashboard
@endcomponent

**Dein kostenloser Trial:** 10 Scans kostenlos, keine Kreditkarte nötig.

Wenn du Fragen hast, antworte einfach auf diese E-Mail.

Mit freundlichen Grüßen,
Das PageScan-Team
@endcomponent
