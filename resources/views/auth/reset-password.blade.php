<x-guest-layout>
    <div style="font-weight:600; font-size:15px; margin-bottom:20px;">Neues Passwort setzen</div>

    <form method="POST" action="{{ route('password.store') }}">
        @csrf

        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <div style="margin-bottom:16px;">
            <label for="email" class="form-label">E-Mail-Adresse</label>
            <input
                id="email"
                type="email"
                name="email"
                class="input"
                value="{{ old('email', $request->email) }}"
                readonly
                autocomplete="username"
            >
            @error('email')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom:16px;">
            <label for="password" class="form-label">Neues Passwort</label>
            <input
                id="password"
                type="password"
                name="password"
                class="input"
                required
                autocomplete="new-password"
            >
            @error('password')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <div style="margin-bottom:20px;">
            <label for="password_confirmation" class="form-label">Passwort bestätigen</label>
            <input
                id="password_confirmation"
                type="password"
                name="password_confirmation"
                class="input"
                required
                autocomplete="new-password"
            >
            @error('password_confirmation')
                <p style="color:#ef4444; font-size:12px; margin-top:4px;">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary" style="width:100%;">
            Passwort zurücksetzen
        </button>
    </form>
</x-guest-layout>
