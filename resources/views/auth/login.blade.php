<x-guest-layout>
  <div class="card" style="width:100%; max-width:380px; padding:32px;">
    @if (session('status'))
      <div style="margin-bottom:16px; font-size:13px; color:#16a34a;">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login') }}">
      @csrf

      <div class="form-group">
        <label class="form-label" for="email">E-Mail</label>
        <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email')
          <div style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Passwort</label>
        <input id="password" class="input" type="password" name="password" required autocomplete="current-password">
        @error('password')
          <div style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:8px;">
        Anmelden
      </button>
    </form>

    <div style="text-align:center; margin-top:20px; font-size:13px; color:var(--text-3);">
      Noch kein Konto? <a href="{{ route('register') }}" style="color:var(--text-2); text-decoration:underline;">Registrieren</a>
    </div>
  </div>
</x-guest-layout>
