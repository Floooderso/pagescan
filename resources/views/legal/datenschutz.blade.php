<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Datenschutzerklärung - PageScan</title>
  <link rel="preconnect" href="https://fonts.bunny.net">
  <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
  @vite(['resources/css/app.css'])
</head>
<body>
  <nav class="nav">
    <a href="/" class="nav-brand">PageScan</a>
  </nav>

  <div class="page" style="max-width:680px;">
    <div style="margin-bottom:32px;">
      <a href="/" style="font-size:13px; color:var(--text-3); text-decoration:none;">← Zurück</a>
    </div>

    <div class="page-title" style="margin-bottom:4px;">Datenschutzerklärung</div>
    <div class="page-subtitle" style="margin-bottom:32px;">Gemäß DSGVO · Stand: {{ date('F Y') }}</div>

    <p style="font-size:14px; color:var(--text-2); line-height:1.7; margin-bottom:24px;">
      Wir nehmen den Schutz deiner persönlichen Daten ernst. Diese Erklärung informiert dich darüber, welche Daten wir erheben, wie wir sie verwenden und welche Rechte du hast.
    </p>

    @php
    $sections = [
      ['title' => '1. Verantwortlicher', 'content' => '
        <p>[Vor- und Nachname / Firmenname]<br>[Straße und Hausnummer]<br>[PLZ Ort]<br>E-Mail: <a href="mailto:[kontakt@beispiel.de]" style="color:var(--text-1);">[kontakt@beispiel.de]</a></p>
      '],
      ['title' => '2. Nutzerkonto & Registrierung', 'content' => '
        <p>Zur Nutzung von PageScan ist eine Registrierung erforderlich. Wir erheben Name, E-Mail-Adresse und Passwort (verschlüsselt). Scan-Ergebnisse, Leads und Suchanfragen werden für die Dauer des Vertragsverhältnisses gespeichert.</p>
        <p style="margin-top:8px;">Rechtsgrundlage: Art. 6 Abs. 1 lit. b DSGVO.</p>
      '],
      ['title' => '3. Server-Logfiles', 'content' => '
        <p>Beim Aufruf unserer Website speichern wir automatisch IP-Adresse (anonymisiert), Datum/Uhrzeit, aufgerufene URL sowie Browser- und Betriebssystem-Info. Logs werden nach 7 Tagen gelöscht.</p>
        <p style="margin-top:8px;">Rechtsgrundlage: Art. 6 Abs. 1 lit. f DSGVO.</p>
      '],
      ['title' => '4. Cookies', 'content' => '
        <p>Wir setzen ausschließlich technisch notwendige Cookies ein: Session-Cookie (Anmeldung), CSRF-Token (Sicherheit) und Cookie-Zustimmung. Keine Tracking- oder Werbe-Cookies, kein Analytics.</p>
        <p style="margin-top:8px;">Rechtsgrundlage: Art. 6 Abs. 1 lit. f DSGVO.</p>
      '],
      ['title' => '5. Zahlungsabwicklung – Stripe', 'content' => '
        <p>Zahlungen werden über Stripe Payments Europe, Ltd. (Dublin, Irland) abgewickelt. Zahlungsdaten werden direkt an Stripe übermittelt — wir speichern keine Kreditkartendaten. Stripe gibt uns lediglich den Abo-Status zurück.</p>
        <p style="margin-top:8px;">Datenschutzrichtlinie: <a href="https://stripe.com/de/privacy" target="_blank" rel="noopener" style="color:var(--text-1);">stripe.com/de/privacy</a> · Rechtsgrundlage: Art. 6 Abs. 1 lit. b DSGVO.</p>
      '],
      ['title' => '6. Google Places API', 'content' => '
        <p>Die Suchfunktion nutzt die Google Places API (Google Ireland Ltd., Dublin). Suchanfragen (Branche und Stadt) werden an Google-Server übermittelt.</p>
        <p style="margin-top:8px;">Datenschutzrichtlinie: <a href="https://policies.google.com/privacy" target="_blank" rel="noopener" style="color:var(--text-1);">policies.google.com/privacy</a> · Rechtsgrundlage: Art. 6 Abs. 1 lit. b DSGVO.</p>
      '],
      ['title' => '7. Schriftarten – Bunny Fonts', 'content' => '
        <p>Wir laden Schriftarten von Bunny Fonts (BunnyWay d.o.o., Ljubljana). Der Anbieter protokolliert keine IP-Adressen — es werden keine personenbezogenen Daten weitergegeben.</p>
      '],
      ['title' => '8. Deine Rechte', 'content' => '
        <p>Du hast das Recht auf Auskunft (Art. 15), Berichtigung (Art. 16), Löschung (Art. 17), Einschränkung (Art. 18), Datenübertragbarkeit (Art. 20) und Widerspruch (Art. 21 DSGVO). Dein Konto kannst du unter <em>Profil → Konto löschen</em> selbst entfernen. Für alle weiteren Anfragen: <a href="mailto:[kontakt@beispiel.de]" style="color:var(--text-1);">[kontakt@beispiel.de]</a></p>
      '],
      ['title' => '9. Datensicherheit', 'content' => '
        <p>Alle Übertragungen erfolgen verschlüsselt via HTTPS/TLS. Passwörter werden ausschließlich gehasht (bcrypt) gespeichert. Zugriff auf Produktionsdaten ist auf autorisiertes Personal beschränkt.</p>
      '],
    ];
    @endphp

    @foreach($sections as $s)
    <div class="card" style="padding:24px 28px; margin-bottom:12px;">
      <div style="font-size:12px; font-weight:500; color:var(--text-3); text-transform:uppercase; letter-spacing:.05em; margin-bottom:12px;">{{ $s['title'] }}</div>
      <div style="font-size:14px; color:var(--text-2); line-height:1.7;">{!! $s['content'] !!}</div>
    </div>
    @endforeach

  </div>
</body>
</html>
