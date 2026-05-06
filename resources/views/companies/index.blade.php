<x-app-layout>
  <div class="page">
    <div class="page-header">
      <div>
        <div class="page-title">Unternehmen</div>
        <div class="page-subtitle">Alle gespeicherten Unternehmen aus bisherigen Suchen</div>
      </div>
      <div style="display:flex; gap:8px;">
        <a href="/companies/export" class="btn" id="companies-export-btn">CSV Export</a>
        <a href="/search" class="btn btn-primary">Neue Suche</a>
        <a href="/companies/create" class="btn">Manuell hinzufügen</a>
      </div>
    </div>

    <!-- Filter-Panel -->
    <div
      x-data="{
        open: false,
        params: new URLSearchParams(window.location.search),
        get(k) { return this.params.get(k) || '' },
        set(k, v) {
          if (v) this.params.set(k, v); else this.params.delete(k);
          history.replaceState(null, '', '?' + this.params.toString());
          window.dispatchEvent(new Event('company-filter-changed'));
        },
        reset() {
          this.params = new URLSearchParams();
          history.replaceState(null, '', window.location.pathname);
          window.dispatchEvent(new Event('company-filter-changed'));
        },
        get activePills() {
          const map = { search:'Suche', min_score:'Min Score', max_score:'Max Score', cms:'CMS', has_ssl:'Nur SSL', is_mobile:'Nur Mobil', has_website:'Hat Website' };
          const out = [];
          for (const [k, label] of Object.entries(map)) {
            const v = this.params.get(k);
            if (v) out.push({ key: k, label: label + ': ' + v });
          }
          return out;
        }
      }"
      style="margin-bottom:16px;"
    >
      <!-- Suchzeile + Toggle -->
      <div style="display:flex; gap:8px; flex-wrap:wrap; margin-bottom:8px;">
        <input
          type="text"
          class="input"
          style="max-width:300px;"
          placeholder="Name, Branche, Stadt, Website..."
          :value="get('search')"
          @input="set('search', $event.target.value)"
        >
        <button type="button" class="btn" @click="open = !open">
          <span x-text="open ? '▲ Filter ausblenden' : '▼ Erweiterte Filter'"></span>
        </button>
        <a href="#" class="btn" @click.prevent="reset()">Filter zurücksetzen</a>
      </div>

      <!-- Aktive Filter als Pills -->
      <div v-if="activePills.length" style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:8px;">
        <template x-for="pill in activePills" :key="pill.key">
          <span class="badge-gray" style="display:inline-flex; align-items:center; gap:4px;">
            <span x-text="pill.label"></span>
            <button style="background:none;border:none;cursor:pointer;font-size:12px;padding:0 2px;" @click="set(pill.key, '')">✕</button>
          </span>
        </template>
      </div>

      <!-- Erweitertes Filter-Panel -->
      <div x-show="open" x-transition style="display:none;">
        <div class="card" style="padding:20px; display:grid; grid-template-columns:repeat(auto-fill, minmax(180px, 1fr)); gap:16px;">

          <div class="form-group" style="margin:0;">
            <label class="form-label">Score Min</label>
            <input type="number" min="0" max="100" class="input" :value="get('min_score')" @input="set('min_score', $event.target.value)" placeholder="0">
          </div>

          <div class="form-group" style="margin:0;">
            <label class="form-label">Score Max</label>
            <input type="number" min="0" max="100" class="input" :value="get('max_score')" @input="set('max_score', $event.target.value)" placeholder="100">
          </div>

          <div class="form-group" style="margin:0;">
            <label class="form-label">CMS</label>
            <select class="input" :value="get('cms')" @change="set('cms', $event.target.value)">
              <option value="">Alle</option>
              <option>WordPress</option>
              <option>Shopify</option>
              <option>Wix</option>
              <option>Joomla</option>
              <option>Drupal</option>
              <option>TYPO3</option>
              <option>Squarespace</option>
              <option>Webflow</option>
            </select>
          </div>

          <div class="form-group" style="margin:0;">
            <label class="form-label">Hat Website</label>
            <select class="input" :value="get('has_website')" @change="set('has_website', $event.target.value)">
              <option value="">Alle</option>
              <option value="1">Ja</option>
              <option value="0">Nein</option>
            </select>
          </div>

          <div style="display:flex; flex-direction:column; gap:8px; padding-top:20px;">
            <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
              <input type="checkbox" :checked="get('has_ssl') === '1'" @change="set('has_ssl', $event.target.checked ? '1' : '')">
              Nur mit SSL
            </label>
            <label style="display:flex; align-items:center; gap:8px; font-size:13px; cursor:pointer;">
              <input type="checkbox" :checked="get('is_mobile') === '1'" @change="set('is_mobile', $event.target.checked ? '1' : '')">
              Nur Mobil-optimiert
            </label>
          </div>
        </div>
      </div>
    </div>

    <div id="company-list-app"></div>
  </div>

  <script>
    document.getElementById('companies-export-btn')?.addEventListener('click', function (e) {
      e.preventDefault()
      const params = new URLSearchParams(window.location.search)
      window.location.href = '/companies/export' + (params.toString() ? '?' + params.toString() : '')
    })
  </script>
</x-app-layout>
