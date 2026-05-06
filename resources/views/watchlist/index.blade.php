<x-app-layout>
  <div class="page" style="max-width:860px;">
    <div class="page-header">
      <div>
        <div class="page-title">Watchlist</div>
        <div class="page-subtitle">Gespeicherte Suchen die regelmäßig aktualisiert werden</div>
      </div>
    </div>

    @if(session('success'))
      <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Neue Suche speichern -->
    <div class="card" style="padding:24px; margin-bottom:24px;">
      <div style="font-weight:600; font-size:14px; margin-bottom:16px;">Neue Suche speichern</div>
      <form method="POST" action="/watchlist" style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
        @csrf
        <div class="form-group" style="margin:0; flex:1; min-width:160px;">
          <label class="form-label">Name</label>
          <input name="name" class="input" placeholder="z.B. Friseure München" value="{{ old('name') }}" required>
          @error('name')<div style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
        </div>
        <div class="form-group" style="margin:0; flex:1; min-width:130px;">
          <label class="form-label">Branche</label>
          <input name="branche" class="input" placeholder="z.B. Friseur" value="{{ old('branche') }}" required>
          @error('branche')<div style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
        </div>
        <div class="form-group" style="margin:0; flex:1; min-width:130px;">
          <label class="form-label">Stadt</label>
          <input name="stadt" class="input" placeholder="z.B. München" value="{{ old('stadt') }}" required>
          @error('stadt')<div style="color:#dc2626; font-size:12px; margin-top:4px;">{{ $message }}</div>@enderror
        </div>
        <button type="submit" class="btn btn-primary">Speichern</button>
      </form>
    </div>

    <!-- Gespeicherte Suchen -->
    @if($searches->isEmpty())
      <div class="card" style="padding:48px; text-align:center; color:var(--text-3);">
        Noch keine Suchen gespeichert. Füge oben eine hinzu.
      </div>
    @else
      <div style="display:flex; flex-direction:column; gap:12px;">
        @foreach($searches as $search)
          <div
            class="card"
            style="padding:20px 24px;"
            x-data="{
              loading: false,
              runAt: '{{ $search->last_run_at?->format('d.m.Y H:i') ?? '' }}',
              total: {{ $search->last_result_count ?? 'null' }},
              newCount: {{ $search->new_leads_count ?? 'null' }},
              async refresh() {
                this.loading = true
                try {
                  const res = await fetch('/watchlist/{{ $search->id }}/run', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content }
                  })
                  const data = await res.json()
                  this.runAt    = data.run_at
                  this.total    = data.total
                  this.newCount = data.new_count
                } catch(e) { alert('Fehler beim Ausführen') }
                finally { this.loading = false }
              }
            }"
          >
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap;">
              <div>
                <div style="font-weight:600; font-size:15px; margin-bottom:4px;">{{ $search->name }}</div>
                <div style="font-size:12px; color:var(--text-3);">{{ $search->branche }} in {{ $search->stadt }}</div>

                <div style="display:flex; gap:12px; margin-top:8px; flex-wrap:wrap; align-items:center;">
                  <span style="font-size:12px; color:var(--text-3);">
                    Zuletzt: <span x-text="runAt || 'Noch nie'">{{ $search->last_run_at?->format('d.m.Y H:i') ?? 'Noch nie' }}</span>
                  </span>
                  <span x-show="total !== null" style="font-size:12px; color:var(--text-3);">
                    <span x-text="total"></span> Firmen gefunden
                  </span>
                  <span
                    x-show="newCount > 0"
                    class="badge-green"
                    style="font-size:11px; border-radius:20px;"
                  >
                    <span x-text="newCount"></span> neu
                  </span>
                </div>
              </div>

              <div style="display:flex; gap:8px; align-items:center;">
                <button
                  class="btn btn-sm btn-primary"
                  :disabled="loading"
                  @click="refresh()"
                >
                  <span x-show="loading" style="display:inline-flex; align-items:center; gap:6px;">
                    <span style="width:10px;height:10px;border:2px solid rgba(255,255,255,.4);border-top-color:#fff;border-radius:50%;animation:spin 0.7s linear infinite;display:inline-block;"></span>
                    Läuft...
                  </span>
                  <span x-show="!loading">Jetzt aktualisieren</span>
                </button>

                <form method="POST" action="/watchlist/{{ $search->id }}" style="display:inline;">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Suche löschen?')">Löschen</button>
                </form>
              </div>
            </div>
          </div>
        @endforeach
      </div>
    @endif
  </div>

  <style>
    @keyframes spin { to { transform: rotate(360deg); } }
  </style>
</x-app-layout>
