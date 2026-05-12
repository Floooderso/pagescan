<x-guest-layout>
  <div style="width:100%; max-width:480px; background:var(--bg-2); border:1px solid var(--border); border-radius:12px; padding:40px 32px; text-align:center;">

    {{-- Icon --}}
    <div style="font-size:40px; margin-bottom:16px;">⏳</div>

    <div style="font-weight:700; font-size:20px; color:var(--text-1); margin-bottom:10px;">
      Du stehst auf der Warteliste
    </div>

    <div style="font-size:14px; color:var(--text-2); line-height:1.7; margin-bottom:24px;">
      Danke für dein Interesse an PageScan!<br>
      Dein Account wurde erfolgreich erstellt. Wir prüfen Anmeldungen manuell
      und schalten dich so schnell wie möglich frei.<br><br>
      Du erhältst eine Benachrichtigung, sobald dein Zugang aktiviert wurde.
    </div>

    <div style="background:var(--bg); border:1px solid var(--border); border-radius:8px; padding:16px; margin-bottom:24px; font-size:13px; color:var(--text-3);">
      Angemeldet als <strong style="color:var(--text-1);">{{ auth()->user()->email }}</strong>
    </div>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn-sm" style="width:100%; justify-content:center;">
        Abmelden
      </button>
    </form>
  </div>
</x-guest-layout>
