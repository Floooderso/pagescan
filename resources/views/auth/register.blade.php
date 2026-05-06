<x-guest-layout>
  <div class="card" style="width:100%; max-width:380px; padding:32px;">
    <form method="POST" action="{{ route('register') }}">
      @csrf

      <div class="form-group">
        <label class="form-label" for="name">Name</label>
        <input id="name" class="input" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        @error('name')
          <div style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label class="form-label" for="email">E-Mail</label>
        <input id="email" class="input" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        @error('email')
          <div style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label class="form-label" for="password">Passwort</label>
        <input id="password" class="input" type="password" name="password" required autocomplete="new-password">
        @error('password')
          <div style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <div class="form-group">
        <label class="form-label" for="password_confirmation">Passwort bestätigen</label>
        <input id="password_confirmation" class="input" type="password" name="password_confirmation" required autocomplete="new-password">
        @error('password_confirmation')
          <div style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
        @enderror
      </div>

      <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:8px;">
        Registrieren
      </button>
    </form>

    <div style="text-align:center; margin-top:20px; font-size:13px; color:var(--text-3);">
      Bereits ein Konto? <a href="{{ route('login') }}" style="color:var(--text-2); text-decoration:underline;">Anmelden</a>
    </div>
  </div>
</x-guest-layout>
