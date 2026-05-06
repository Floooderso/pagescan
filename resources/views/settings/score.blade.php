<x-app-layout>
  <div class="page" style="max-width:720px;">
    <div class="page-header">
      <div>
        <div class="page-title">Score-Gewichtung</div>
        <div class="page-subtitle">Lege fest, wie stark jedes Kriterium in den Website-Score einfließt (Summe muss 100 ergeben).</div>
      </div>
    </div>

    @if(session('success'))
      <div style="background:#f0fdf4; border:1px solid #bbf7d0; color:#15803d; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:13px;">
        {{ session('success') }}
      </div>
    @endif

    @if($errors->has('sum'))
      <div style="background:#fef2f2; border:1px solid #fecaca; color:#dc2626; padding:12px 16px; border-radius:8px; margin-bottom:20px; font-size:13px;">
        {{ $errors->first('sum') }}
      </div>
    @endif

    <div
      class="card"
      style="padding:28px;"
      x-data="{
        weights: {
          http_ok:   {{ $weights['http_ok']   ?? 30 }},
          ssl:       {{ $weights['ssl']       ?? 20 }},
          mobile:    {{ $weights['mobile']    ?? 20 }},
          contact:   {{ $weights['contact']   ?? 10 }},
          impressum: {{ $weights['impressum'] ?? 10 }},
          favicon:   {{ $weights['favicon']   ?? 10 }},
        },
        profiles: {
          webdesign: { http_ok:20, ssl:20, mobile:25, contact:10, impressum:5,  favicon:20 },
          seo:       { http_ok:15, ssl:15, mobile:30, contact:15, impressum:15, favicon:10 },
          lokal:     { http_ok:20, ssl:10, mobile:20, contact:25, impressum:15, favicon:10 },
        },
        get sum() {
          return Object.values(this.weights).reduce((a, b) => a + Number(b), 0)
        },
        applyProfile(key) {
          this.weights = { ...this.profiles[key] }
        }
      }"
    >
      <!-- Profil-Buttons -->
      <div style="margin-bottom:24px;">
        <div style="font-size:12px; font-weight:500; color:var(--text-3); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:10px;">Vordefinierte Profile</div>
        <div style="display:flex; gap:8px; flex-wrap:wrap;">
          <button type="button" class="btn" @click="applyProfile('webdesign')">Webdesign-Agentur</button>
          <button type="button" class="btn" @click="applyProfile('seo')">SEO-Agentur</button>
          <button type="button" class="btn" @click="applyProfile('lokal')">Lokales Marketing</button>
        </div>
      </div>

      <form method="POST" action="/settings/score">
        @csrf

        <!-- Gewichtungs-Inputs -->
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:24px;">
          @foreach([
            'http_ok'   => 'HTTP 200 OK',
            'ssl'       => 'SSL / HTTPS',
            'mobile'    => 'Mobil-optimiert',
            'contact'   => 'Kontaktangaben',
            'impressum' => 'Impressum',
            'favicon'   => 'Favicon',
          ] as $key => $label)
          <div class="form-group" style="margin-bottom:0;">
            <label class="form-label" for="w_{{ $key }}">{{ $label }}</label>
            <input
              id="w_{{ $key }}"
              name="{{ $key }}"
              type="number"
              min="0"
              max="100"
              class="input"
              x-model.number="weights.{{ $key }}"
              :value="weights.{{ $key }}"
            >
          </div>
          @endforeach
        </div>

        <!-- Live-Summe -->
        <div style="display:flex; align-items:center; gap:12px; padding:14px 16px; border-radius:8px; margin-bottom:20px;" :style="sum === 100 ? 'background:#f0fdf4; border:1px solid #bbf7d0;' : 'background:#fef2f2; border:1px solid #fecaca;'">
          <span style="font-size:13px; font-weight:500;" :style="sum === 100 ? 'color:#15803d;' : 'color:#dc2626;'">
            Summe: <span x-text="sum"></span> / 100
          </span>
          <span style="font-size:12px;" :style="sum === 100 ? 'color:#15803d;' : 'color:#dc2626;'" x-text="sum === 100 ? '✓ Korrekt' : '✗ Muss genau 100 ergeben'"></span>
        </div>

        <button type="submit" class="btn btn-primary" :disabled="sum !== 100" :class="sum !== 100 ? 'opacity-50 cursor-not-allowed' : ''">
          Gewichtung speichern
        </button>
      </form>
    </div>
  </div>
</x-app-layout>
