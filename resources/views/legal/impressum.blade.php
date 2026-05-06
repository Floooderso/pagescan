<x-app-layout>
  <div class="page" style="max-width:720px;">
    <div class="page-header">
      <div class="page-title">Impressum</div>
    </div>

    <div class="card" style="padding:32px; line-height:1.7; font-size:14px; color:var(--text-2);">

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">Angaben gemäß § 5 TMG</h2>

      <p style="margin:0 0 24px;">
        <strong style="color:var(--text-1);">[Vor- und Nachname / Unternehmensname]</strong><br>
        [Straße und Hausnummer]<br>
        [PLZ] [Stadt]<br>
        Deutschland
      </p>

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">Kontakt</h2>
      <p style="margin:0 0 24px;">
        Telefon: [Ihre Telefonnummer]<br>
        E-Mail: [Ihre E-Mail-Adresse]
      </p>

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">Umsatzsteuer-ID</h2>
      <p style="margin:0 0 24px;">
        Umsatzsteuer-Identifikationsnummer gemäß § 27 a Umsatzsteuergesetz:<br>
        [Ihre USt-IdNr. — falls vorhanden]
      </p>

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">Verantwortlich für den Inhalt nach § 55 Abs. 2 RStV</h2>
      <p style="margin:0 0 24px;">
        [Vor- und Nachname]<br>
        [Adresse wie oben]
      </p>

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">Haftung für Inhalte</h2>
      <p style="margin:0 0 16px;">
        Als Diensteanbieter sind wir gemäß § 7 Abs. 1 TMG für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Nach §§ 8 bis 10 TMG sind wir als Diensteanbieter jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen.
      </p>
      <p style="margin:0 0 24px;">
        Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werden wir diese Inhalte umgehend entfernen.
      </p>

      <h2 style="font-size:15px; font-weight:600; color:var(--text-1); margin:0 0 12px;">Haftung für Links</h2>
      <p style="margin:0 0 24px;">
        Unser Angebot enthält Links zu externen Websites Dritter, auf deren Inhalte wir keinen Einfluss haben. Deshalb können wir für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich.
      </p>

      <p style="margin:0; font-size:12px; color:var(--text-3);">
        Stand: {{ now()->format('F Y') }}
      </p>
    </div>
  </div>
</x-app-layout>
