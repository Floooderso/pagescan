@component('mail::message')
# Willkommen bei PageScan, {{ $user->name }}!

Danke für deine Registrierung. PageScan hilft dir dabei, lokale Unternehmen mit verbesserungswürdigem Web-Auftritt zu finden — perfekte Leads für deine Webdesign- oder SEO-Agentur.

@if($user->isApproved())
## Dein Zugang ist sofort aktiv!

Du hast einen der ersten kostenlosen Plätze erhalten — herzlichen Glückwunsch!

## So geht's los

1. **Suche starten** – Branche und Stadt eingeben, fertig
2. **Websites scannen** – Jede gefundene Website wird automatisch bewertet (Score 0–100)
3. **Leads speichern** – Die vielversprechendsten Kontakte als Leads markieren

@component('mail::button', ['url' => config('app.url') . '/dashboard'])
Zum Dashboard
@endcomponent

@else
## Du stehst auf der Warteliste

Dein Account wurde erfolgreich erstellt. Wir prüfen Anmeldungen manuell und schalten dich bald frei.

Du erhältst eine weitere E-Mail, sobald dein Zugang aktiviert wurde. Kein weiteres Zutun nötig.

@endif

Wenn du Fragen hast, antworte einfach auf diese E-Mail.

Mit freundlichen Grüßen,
Das PageScan-Team
@endcomponent
