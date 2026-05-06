<x-app-layout>
  <div class="page">
    <div class="page-header">
      <div class="page-title">Unternehmen hinzufügen</div>
      <a href="/companies" class="btn">Zurück</a>
    </div>

    <div class="card" style="max-width:600px; padding:32px;">
      <form method="POST" action="/companies" x-data="{ loading: false }" @submit="loading = true">
        @csrf

        @foreach([
          'name'        => 'Name *',
          'branche'     => 'Branche *',
          'stadt'       => 'Stadt *',
          'plz'         => 'PLZ',
          'telefon'     => 'Telefon',
          'email'       => 'E-Mail',
          'website_url' => 'Website URL',
        ] as $field => $label)
        <div class="form-group">
          <label class="form-label" for="{{ $field }}">{{ $label }}</label>
          <input
            id="{{ $field }}"
            name="{{ $field }}"
            class="input"
            type="{{ $field === 'email' ? 'email' : ($field === 'website_url' ? 'url' : 'text') }}"
            value="{{ old($field) }}"
            {{ in_array($field, ['name','branche','stadt']) ? 'required' : '' }}
          >
          @error($field)
            <div style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</div>
          @enderror
        </div>
        @endforeach

        <button type="submit" class="btn btn-primary" :disabled="loading" :class="{ 'opacity-50 cursor-not-allowed': loading }">
          <span x-text="loading ? 'Wird gespeichert...' : 'Speichern &amp; Scannen'">Speichern &amp; Scannen</span>
        </button>
      </form>
    </div>
  </div>
</x-app-layout>
