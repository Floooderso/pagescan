<x-app-layout>
  <div class="page" style="max-width: 680px;">
    <div class="page-header">
      <div>
        <div class="page-title">Profil</div>
        <div class="page-subtitle">Konto-Einstellungen und Sicherheit</div>
      </div>
    </div>

    @if(session('status') === 'profile-updated')
      <div class="alert alert-success" style="margin-bottom:24px;">Profil erfolgreich gespeichert.</div>
    @endif
    @if(session('status') === 'password-updated')
      <div class="alert alert-success" style="margin-bottom:24px;">Passwort erfolgreich geändert.</div>
    @endif

    {{-- Profildaten --}}
    <div class="card" style="padding:28px; margin-bottom:20px;">
      <div style="font-weight:600; font-size:14px; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid var(--border);">
        Profildaten
      </div>
      <form method="post" action="{{ route('profile.update') }}">
        @csrf
        @method('patch')
        <div class="form-group">
          <label class="form-label" for="name">Name</label>
          <input id="name" name="name" type="text" class="input" value="{{ old('name', $user->name) }}" required autofocus>
          @error('name')<div style="color:#f87171; font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
        </div>
        <div class="form-group">
          <label class="form-label" for="email">E-Mail</label>
          <input id="email" name="email" type="email" class="input" value="{{ old('email', $user->email) }}" required>
          @error('email')<div style="color:#f87171; font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary">Speichern</button>
      </form>
    </div>

    {{-- Passwort ändern --}}
    <div class="card" style="padding:28px; margin-bottom:20px;">
      <div style="font-weight:600; font-size:14px; margin-bottom:20px; padding-bottom:16px; border-bottom:1px solid var(--border);">
        Passwort ändern
      </div>
      <form method="post" action="{{ route('password.update') }}">
        @csrf
        @method('put')
        <div class="form-group">
          <label class="form-label" for="current_password">Aktuelles Passwort</label>
          <input id="current_password" name="current_password" type="password" class="input" autocomplete="current-password">
          @error('current_password', 'updatePassword')
            <div style="color:#f87171; font-size:12px; margin-top:4px;">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label class="form-label" for="new_password">Neues Passwort</label>
          <input id="new_password" name="password" type="password" class="input" autocomplete="new-password">
          @error('password', 'updatePassword')
            <div style="color:#f87171; font-size:12px; margin-top:4px;">{{ $message }}</div>
          @enderror
        </div>
        <div class="form-group">
          <label class="form-label" for="password_confirmation">Passwort bestätigen</label>
          <input id="password_confirmation" name="password_confirmation" type="password" class="input" autocomplete="new-password">
        </div>
        <button type="submit" class="btn btn-primary">Passwort ändern</button>
      </form>
    </div>

    {{-- Konto löschen --}}
    <div class="card" style="padding:28px; border-color: rgba(220,38,38,0.25);">
      <div style="font-weight:600; font-size:14px; margin-bottom:8px; color:#f87171;">Konto löschen</div>
      <p style="font-size:13px; color:var(--text-2); margin-bottom:20px; line-height:1.6;">
        Sobald dein Konto gelöscht wird, werden alle Daten und Ressourcen dauerhaft entfernt. Bitte exportiere vorher alle Daten die du behalten möchtest.
      </p>
      <form method="post" action="{{ route('profile.destroy') }}"
        onsubmit="return confirm('Konto wirklich löschen? Diese Aktion kann nicht rückgängig gemacht werden.')">
        @csrf
        @method('delete')
        <div class="form-group">
          <label class="form-label" for="delete_password">Passwort zur Bestätigung</label>
          <input id="delete_password" name="password" type="password" class="input" placeholder="Dein Passwort eingeben" style="max-width:320px;">
          @error('password', 'userDeletion')
            <div style="color:#f87171; font-size:12px; margin-top:4px;">{{ $message }}</div>
          @enderror
        </div>
        <button type="submit" class="btn btn-danger">Konto dauerhaft löschen</button>
      </form>
    </div>
  </div>
</x-app-layout>
