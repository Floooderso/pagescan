<x-app-layout>
  <div class="page" style="max-width:720px;">
    <div class="page-header">
      <div class="page-title">Datenschutzerklärung</div>
    </div>

    <div class="card" style="padding:32px; line-height:1.7; font-size:14px; color:var(--text-2);">

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">1. Verantwortlicher</h2>
      <p style="margin:0 0 24px;">
        Verantwortlich für die Datenverarbeitung auf dieser Website ist:<br><br>
        <strong style="color:var(--text-1);">[Vor- und Nachname / Unternehmensname]</strong><br>
        [Straße und Hausnummer]<br>
        [PLZ] [Stadt]<br>
        E-Mail: [Ihre E-Mail-Adresse]
      </p>

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">2. Welche Daten wir erheben</h2>
      <p style="margin:0 0 8px;">Bei der Nutzung von PageScan werden folgende personenbezogene Daten verarbeitet:</p>
      <ul style="margin:0 0 24px; padding-left:20px;">
        <li><strong>Zugangsdaten:</strong> E-Mail-Adresse und Passwort (verschlüsselt gespeichert) bei der Registrierung</li>
        <li><strong>Nutzungsdaten:</strong> Suchanfragen (Branche, Stadt), gespeicherte Leads und Notizen</li>
        <li><strong>Technische Daten:</strong> IP-Adresse, Browser-Typ, Zugriffszeiten (Server-Logs)</li>
      </ul>

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">3. Zweck der Datenverarbeitung</h2>
      <ul style="margin:0 0 24px; padding-left:20px;">
        <li>Bereitstellung und Betrieb der PageScan-Anwendung</li>
        <li>Authentifizierung und Sicherheit des Nutzerkontos</li>
        <li>Speicherung von Leads und Notizen zur eigenen Nutzung</li>
      </ul>

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">4. Rechtsgrundlage</h2>
      <p style="margin:0 0 24px;">
        Die Verarbeitung erfolgt auf Grundlage von Art. 6 Abs. 1 lit. b DSGVO (Vertragserfüllung) sowie Art. 6 Abs. 1 lit. f DSGVO (berechtigte Interessen am sicheren Betrieb der Anwendung).
      </p>

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">5. Drittdienste</h2>
      <p style="margin:0 0 8px;">PageScan nutzt folgende externe Dienste:</p>
      <ul style="margin:0 0 24px; padding-left:20px;">
        <li><strong>Google Places API (Google LLC):</strong> Suche nach Unternehmen. Google kann dabei Daten in die USA übermitteln. Datenschutzrichtlinie: <a href="https://policies.google.com/privacy" target="_blank" style="color:var(--text-2);">policies.google.com/privacy</a></li>
        <li><strong>Thum.io:</strong> Generierung von Website-Screenshots. Dabei wird die jeweilige Website-URL an den Dienst übermittelt.</li>
      </ul>

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">6. Speicherdauer</h2>
      <p style="margin:0 0 24px;">
        Ihre Daten werden so lange gespeichert, wie Ihr Konto aktiv ist. Nach Löschung des Kontos werden alle personenbezogenen Daten innerhalb von 30 Tagen entfernt.
      </p>

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">7. Ihre Rechte</h2>
      <p style="margin:0 0 8px;">Sie haben gemäß DSGVO folgende Rechte:</p>
      <ul style="margin:0 0 24px; padding-left:20px;">
        <li><strong>Auskunft</strong> (Art. 15 DSGVO): Welche Daten wir über Sie gespeichert haben</li>
        <li><strong>Berichtigung</strong> (Art. 16 DSGVO): Korrektur unrichtiger Daten</li>
        <li><strong>Löschung</strong> (Art. 17 DSGVO): Löschung Ihrer Daten</li>
        <li><strong>Einschränkung</strong> (Art. 18 DSGVO): Einschränkung der Verarbeitung</li>
        <li><strong>Widerspruch</strong> (Art. 21 DSGVO): Widerspruch gegen die Verarbeitung</li>
        <li><strong>Beschwerde:</strong> Beschwerde bei der zuständigen Datenschutzbehörde</li>
      </ul>
      <p style="margin:0 0 24px;">
        Zur Ausübung Ihrer Rechte wenden Sie sich an: <a href="mailto:[Ihre E-Mail-Adresse]" style="color:var(--text-2);">[Ihre E-Mail-Adresse]</a>
      </p>

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">8. Cookies und Sessions</h2>
      <p style="margin:0 0 24px;">
        PageScan verwendet technisch notwendige Session-Cookies zur Authentifizierung. Diese Cookies sind für den Betrieb der Anwendung erforderlich und können nicht deaktiviert werden. Es werden keine Tracking- oder Werbe-Cookies eingesetzt.
      </p>

      <p style="margin:0; font-size:12px; color:var(--text-3);">
        Stand: {{ now()->format('F Y') }}
      </p>
    </div>
  </div>
</x-app-layout>
