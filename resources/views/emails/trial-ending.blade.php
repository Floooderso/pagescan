@component('mail::message')
# Dein Trial endet in 3 Tagen

Hallo {{ $user->name }},

dein kostenloser PageScan-Trial läuft in **3 Tagen** aus. Danach stehen keine weiteren Scans zur Verfügung.

Damit du weiter Leads finden und Websites analysieren kannst, wähle jetzt deinen Plan:

- **Starter** – €29/Monat · 200 Scans · 1 Nutzer
- **Agency** – €79/Monat · 1.000 Scans · 3 Nutzer
- **Pro** – €149/Monat · Unbegrenzte Scans · 10 Nutzer

@component('mail::button', ['url' => config('app.url') . '/pricing'])
Plan wählen & upgraden
@endcomponent

Du kannst jederzeit kündigen und wirst erst nach Ablauf der bezahlten Periode belastet.

Mit freundlichen Grüßen,
Das PageScan-Team
@endcomponent
