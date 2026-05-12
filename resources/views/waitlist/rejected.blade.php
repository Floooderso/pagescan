<x-guest-layout>
  <div style="width:100%; max-width:480px; background:var(--bg-2); border:1px solid var(--border); border-radius:12px; padding:40px 32px; text-align:center;">

    {{-- Icon --}}
    <div style="font-size:40px; margin-bottom:16px;">🚫</div>

    <div style="font-weight:700; font-size:20px; color:var(--text-1); margin-bottom:10px;">
      Zugang nicht gewährt
    </div>

    <div style="font-size:14px; color:var(--text-2); line-height:1.7; margin-bottom:24px;">
      Leider konnten wir deinen Zugang zu PageScan nicht aktivieren.<br><br>
      Wenn du glaubst, dass dies ein Fehler ist, wende dich bitte an unser Team.
    </div>

    <a href="mailto:info@pagescan.de" class="btn btn-primary" style="display:block; text-align:center; margin-bottom:12px; text-decoration:none;">
      Kontakt aufnehmen
    </a>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn btn-sm" style="width:100%; justify-content:center;">
        Abmelden
      </button>
    </form>
  </div>
</x-guest-layout>
