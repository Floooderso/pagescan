<x-guest-layout>
    <p style="font-size:14px; color:var(--text-2); margin-bottom:20px; line-height:1.6;">
        Passwort vergessen? Kein Problem. Gib deine E-Mail-Adresse ein und wir schicken dir einen Link zum Zurücksetzen.
    </p>

    @if(session('status'))
        <div class="alert alert-success">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div style="margin-bottom:16px;">
            <label for="email" class="form-label">E-Mail-Adresse</label>
            <input
                id="email"
                type="email"
                name="email"
                class="input"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="email"
            >
            @error('email')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;">
            Link zum Zurücksetzen senden
        </button>
    </form>

    <div style="text-align:center; margin-top:16px;">
        <a href="{{ route('login') }}" style="font-size:13px; color:var(--text-3);">← Zurück zum Login</a>
    </div>
</x-guest-layout>
