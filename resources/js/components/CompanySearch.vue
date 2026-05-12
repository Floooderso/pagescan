<template>
  <div>
    <!-- Suchmaske -->
    <div class="card" style="padding:24px; margin-bottom:20px;">
      <div style="font-weight:600; font-size:15px; margin-bottom:16px;">Unternehmen suchen</div>
      <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:flex-end;">
        <div style="flex:1; min-width:180px; position:relative;">
          <label class="form-label">Branche / Beruf</label>
          <input
            v-model="query.branche"
            class="input"
            placeholder="Branche eingeben oder wählen…"
            autocomplete="off"
            style="padding-right:28px;"
            @focus="brancheOpen = true"
            @input="brancheOpen = true; brancheIndex = -1"
            @keydown.down.prevent="brancheMove(1)"
            @keydown.up.prevent="brancheMove(-1)"
            @keydown.enter.prevent="branchePick()"
            @keydown.esc="brancheOpen = false"
            @blur="onBrancheBlur"
          >
          <button
            v-if="query.branche"
            @mousedown.prevent="query.branche = ''; brancheOpen = false"
            style="position:absolute; right:10px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:var(--text-3); font-size:14px; line-height:1; padding:0;"
            tabindex="-1"
          >✕</button>
          <div v-if="brancheOpen && filteredBrancheGroups.length" class="ac-dropdown" style="max-height:260px; overflow-y:auto;">
            <template v-for="group in filteredBrancheGroups" :key="group.label">
              <div style="padding:5px 12px 3px; font-size:10px; font-weight:600; color:var(--text-3); text-transform:uppercase; letter-spacing:.07em; background:var(--bg-2); position:sticky; top:0;">
                {{ group.label }}
              </div>
              <div
                v-for="option in group.options"
                :key="option"
                class="ac-item"
                :class="{ 'ac-item-active': brancheFlat.indexOf(option) === brancheIndex }"
                @mousedown.prevent="pickBranche(option)"
              >
                {{ option }}
              </div>
            </template>
          </div>
        </div>
        <div style="flex:1; min-width:160px; position:relative;">
          <label class="form-label">Stadt</label>
          <input
            v-model="query.stadt"
            class="input"
            placeholder="z.B. Köln, Wuppertal"
            autocomplete="off"
            @input="onStadtInput"
            @keydown.down.prevent="acMove(1)"
            @keydown.up.prevent="acMove(-1)"
            @keydown.enter.prevent="acSelect()"
            @keydown.esc="stadtInputOpen = false"
            @blur="onStadtBlur"
          >
          <div v-if="stadtInputOpen && filteredStaedte.length" class="ac-dropdown">
            <div
              v-for="(s, i) in filteredStaedte"
              :key="s"
              class="ac-item"
              :class="{ 'ac-item-active': i === acIndex }"
              @mousedown.prevent="pickStadt(s)"
            >
              {{ s }}
            </div>
          </div>
        </div>
        <button
          class="btn btn-primary"
          style="white-space:nowrap;"
          :disabled="searching || !query.branche || !query.stadt"
          @click="startSearch"
        >
          {{ searching ? 'Suche läuft...' : 'Suchen & Scannen' }}
        </button>
      </div>

      <!-- Fortschritt während der Suche -->
      <div v-if="searching" style="margin-top:16px;">
        <div style="display:flex; align-items:center; gap:10px; font-size:13px; color:var(--text-2);">
          <div class="spinner"></div>
          <span>{{ statusText }}</span>
        </div>
      </div>

      <!-- Scan-Fortschritt nach der Suche -->
      <div v-else-if="pollingActive && pendingScanCount > 0" style="margin-top:12px;">
        <div style="display:flex; align-items:center; gap:10px; font-size:13px; color:var(--text-2);">
          <div class="spinner"></div>
          <span>{{ pendingScanCount }} Website{{ pendingScanCount !== 1 ? 's werden' : ' wird' }} gescannt...</span>
        </div>
      </div>
    </div>

    <!-- Zusammenfassung + Filter -->
    <div v-if="companies.length > 0 && !searching" style="margin-bottom:16px; display:flex; gap:10px; flex-wrap:wrap; align-items:center;">
      <div class="card summary-pill">
        <span style="color:var(--text-3);">Gefunden: </span><strong>{{ companies.length }}</strong>
      </div>
      <div class="card summary-pill">
        <span style="color:var(--text-3);">Mit Website: </span><strong>{{ companies.filter(c => c.has_website).length }}</strong>
      </div>
      <div class="card summary-pill">
        <span style="color:var(--text-3);">Ohne Website: </span><strong style="color:#dc2626;">{{ companies.filter(c => !c.has_website).length }}</strong>
      </div>
      <div v-if="avgScore > 0" class="card summary-pill">
        <span style="color:var(--text-3);">Ø Score: </span><strong>{{ avgScore }}/100</strong>
      </div>

      <!-- Filter-Buttons -->
      <div style="display:flex; gap:6px; margin-left:auto; flex-wrap:wrap; align-items:center;">
        <button
          v-for="f in filters"
          :key="f.value"
          class="btn btn-sm"
          :class="{ 'btn-primary': activeFilter === f.value }"
          @click="activeFilter = f.value"
        >
          {{ f.label }}
        </button>
      </div>
    </div>

    <!-- Ergebnistabelle -->
    <div v-if="filtered.length > 0" class="card" style="overflow:hidden;">
      <div class="table-scroll"><table>
        <thead>
          <tr>
            <th>Unternehmen</th>
            <th class="col-hide-mobile">Bewertung</th>
            <th class="col-hide-mobile">Website</th>
            <th>Score</th>
            <th class="col-hide-mobile">Details</th>
            <th>Aktionen</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in filtered" :key="c.place_id">
            <td>
              <div style="display:flex; align-items:center; gap:10px;">
                <img
                  v-if="c.photo_url"
                  :src="c.photo_url"
                  style="width:44px; height:44px; object-fit:cover; border-radius:6px; flex-shrink:0; border:1px solid var(--border);"
                  loading="lazy"
                  @error="$event.target.style.display='none'"
                >
                <div>
                  <div style="font-weight:500;">{{ c.name }}</div>
                  <div style="font-size:11px; color:var(--text-3);">{{ c.adresse }}</div>
                </div>
              </div>
            </td>
            <td class="col-hide-mobile">
              <span v-if="c.rating" style="font-size:13px;">
                ⭐ {{ c.rating.toFixed(1) }}
                <span style="color:var(--text-3); font-size:11px;">({{ c.bewertungen }})</span>
              </span>
              <span v-else style="color:var(--text-3);">—</span>
            </td>
            <td class="col-hide-mobile">
              <a v-if="c.has_website" :href="c.website_url" target="_blank"
                style="font-size:12px; color:var(--text-2); word-break:break-all; max-width:200px; display:inline-block;">
                {{ c.website_url }}
              </a>
              <span v-else class="badge-red">Keine Website</span>
            </td>
            <td>
              <div v-if="isScanRunning(c)" style="display:flex; align-items:center; gap:6px; font-size:12px; color:var(--text-3);">
                <div class="spinner spinner-sm"></div> Scannt...
              </div>
              <span v-else-if="c.scan_status === 'done'">
                <span :class="scoreBadge(c.scan.score)">{{ c.scan.score }}/100</span>
              </span>
              <span v-else-if="c.scan_status === 'failed'" class="badge-red">Fehler</span>
              <span v-else-if="!c.has_website" style="color:var(--text-3);">—</span>
              <span v-else class="badge-gray">Ausstehend</span>
            </td>
            <td class="col-hide-mobile" style="font-size:12px; color:var(--text-2);">
              <div v-if="c.scan_status === 'done'" style="display:flex; flex-direction:column; gap:2px;">
                <span v-if="c.scan.has_ssl !== null">{{ c.scan.has_ssl ? '✓' : '✗' }} SSL</span>
                <span v-if="c.scan.is_mobile !== null">{{ c.scan.is_mobile ? '✓' : '✗' }} Mobile</span>
                <span v-if="c.scan.has_impressum !== null">{{ c.scan.has_impressum ? '✓' : '✗' }} Impressum</span>
                <span v-if="c.scan.cms" style="color:var(--text-3);">{{ c.scan.cms }}</span>
              </div>
            </td>
            <td>
              <div style="display:flex; gap:6px;">
                <button
                  v-if="c.has_website"
                  class="btn btn-sm action-btn"
                  :disabled="isScanRunning(c)"
                  @click="scanOne(c)"
                >
                  {{ isScanRunning(c) ? '...' : c.scan_status === 'done' ? 'Neu scannen' : 'Scannen' }}
                </button>
                <div v-else class="action-btn-placeholder"></div>
                <button
                  class="btn btn-sm btn-primary action-btn"
                  :disabled="c.lead_created"
                  @click="createLead(c)"
                >
                  {{ c.lead_created ? '✓ Lead' : '+ Lead' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table></div>
    </div>

    <!-- Kein Treffer nach Filter -->
    <div v-else-if="companies.length > 0 && !searching" class="card" style="padding:32px; text-align:center; color:var(--text-3);">
      Kein Ergebnis für diesen Filter.
    </div>

    <!-- Weitere laden -->
    <div v-if="nextPageToken && !searching" style="text-align:center; margin-top:16px;">
      <button class="btn btn-primary" :disabled="loadingMore" @click="loadMore">
        <span v-if="loadingMore" style="display:flex; align-items:center; gap:8px;">
          <span class="spinner" style="width:12px;height:12px;border-width:2px;"></span> Lädt...
        </span>
        <span v-else>20 weitere laden</span>
      </button>
    </div>

    <!-- Suche speichern -->
    <div v-if="!searching && companies.length > 0" style="text-align:center; margin-top:12px;">
      <span v-if="!saveFormOpen" style="font-size:13px; color:var(--text-3); cursor:pointer; text-decoration:underline;" @click="saveFormOpen = true">
        Diese Suche speichern
      </span>
      <div v-else style="display:inline-flex; gap:8px; align-items:center; flex-wrap:wrap; justify-content:center;">
        <input v-model="saveName" class="input" style="width:220px;" :placeholder="`${query.branche} in ${query.stadt}`">
        <button class="btn btn-sm btn-primary" @click="saveSearch">Speichern</button>
        <button class="btn btn-sm" @click="saveFormOpen = false">Abbrechen</button>
      </div>
    </div>

    <!-- Leerzustand nach Suche -->
    <div v-else-if="!searching && hasSearched && companies.length === 0" class="card" style="padding:48px; text-align:center; color:var(--text-3);">
      Keine Unternehmen gefunden. Versuche eine andere Branche oder Stadt.
    </div>

    <!-- Toast -->
    <div v-if="toast" style="position:fixed; bottom:24px; right:24px; background:var(--text-1); color:#fff; padding:10px 18px; border-radius:8px; font-size:13px; z-index:999;">
      {{ toast }}
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      query:        { branche: '', stadt: '' },
      companies:    [],
      searching:    false,
      hasSearched:  false,
      statusText:   'Suche läuft...',
      toast:        null,
      activeFilter:   'alle',
      nextPageToken:  null,
      loadingMore:    false,
      saveFormOpen:   false,
      saveName:       '',
      stadtInputOpen: false,
      acIndex:        -1,
      stadtSuggestions: [],
      _acTimer:       null,
      // Branche Combobox
      brancheOpen:  false,
      brancheIndex: -1,
      brancheGroups: [
        { label: 'Gesundheit & Beauty',    options: ['Friseur','Kosmetikstudio','Nagelstudio','Massagepraxis','Physiotherapie','Zahnarzt','Arztpraxis','Optiker'] },
        { label: 'Gastronomie & Food',     options: ['Restaurant','Café','Bäckerei','Pizzeria','Imbiss','Catering'] },
        { label: 'Handwerk & Bau',         options: ['Elektriker','Klempner','Maler','Dachdecker','Schreiner','Bauunternehmen','Fliesenleger','Heizung Sanitär'] },
        { label: 'Kfz & Mobilität',        options: ['Autohaus','Kfz-Werkstatt','Fahrradladen'] },
        { label: 'Handel & Dienstleistung',options: ['Blumenladen','Reinigungsservice','Umzugsunternehmen','Fotografen','Reisebüro'] },
        { label: 'Beratung & Recht',       options: ['Steuerberater','Rechtsanwalt','Versicherung','Immobilienmakler','Unternehmensberatung'] },
        { label: 'Bildung & Freizeit',     options: ['Fitnessstudio','Yogastudio','Nachhilfe','Fahrschule','Musikschule'] },
      ],
      pollingActive:  false,
      _pollTimer:     null,
      filters: [
        { value: 'alle',         label: 'Alle' },
        { value: 'ohne_website', label: 'Ohne Website' },
        { value: 'mit_website',  label: 'Mit Website' },
        { value: 'schlechter_score', label: 'Score < 50' },
      ],
    }
  },
  computed: {
    filteredStaedte() {
      return this.stadtSuggestions
    },
    filteredBrancheGroups() {
      const q = (this.query.branche || '').toLowerCase()
      if (!q) return this.brancheGroups
      return this.brancheGroups
        .map(g => ({ ...g, options: g.options.filter(o => o.toLowerCase().includes(q)) }))
        .filter(g => g.options.length > 0)
    },
    brancheFlat() {
      return this.filteredBrancheGroups.flatMap(g => g.options)
    },
    filtered() {
      switch (this.activeFilter) {
        case 'ohne_website':    return this.companies.filter(c => !c.has_website)
        case 'mit_website':     return this.companies.filter(c => c.has_website)
        case 'schlechter_score':return this.companies.filter(c => c.scan_status === 'done' && c.scan?.score < 50)
        default:                return this.companies
      }
    },
    avgScore() {
      const scanned = this.companies.filter(c => c.scan_status === 'done' && c.scan?.score != null)
      if (!scanned.length) return 0
      return Math.round(scanned.reduce((s, c) => s + c.scan.score, 0) / scanned.length)
    },
    pendingScanCount() {
      return this.companies.filter(c => this.isScanRunning(c)).length
    },
  },
  mounted() {
    const params  = new URLSearchParams(window.location.search)
    const branche = params.get('branche')
    const stadt   = params.get('stadt')
    if (branche) this.query.branche = branche
    if (stadt)   this.query.stadt   = stadt
    if (branche && stadt) this.$nextTick(() => this.startSearch())
  },
  beforeUnmount() {
    this.stopPolling()
  },
  methods: {
    isScanRunning(company) {
      return ['scanning', 'pending', 'running'].includes(company.scan_status)
    },

    async startSearch() {
      if (!this.query.branche || !this.query.stadt || this.searching) return

      this.stopPolling()
      this.searching    = true
      this.hasSearched  = true
      this.companies    = []
      this.nextPageToken = null
      this.activeFilter = 'alle'
      this.statusText   = `Suche nach "${this.query.branche}" in "${this.query.stadt}"...`

      try {
        const res   = await axios.post('/api/search', this.query)
        const found = res.data.companies
        this.nextPageToken = res.data.next_page_token ?? null

        if (!found.length) {
          this.searching = false
          return
        }

        this.companies = found.map(c => ({
          ...c,
          // Prefer the server-provided scan_status (already-done or in-progress
          // scans are returned directly by the API). Only fall back to 'pending'
          // for companies that have a website but no status yet.
          scan_status:  c.scan_status !== undefined ? c.scan_status : (c.has_website ? 'pending' : null),
          scan:         c.scan ?? null,
          lead_created: false,
        }))

        this.startPolling()

      } catch (e) {
        if (e.response?.status === 429) {
          this.showToast('Scan-Limit erreicht. Bitte Plan upgraden.')
        } else {
          this.showToast('Fehler bei der Suche. API-Key konfiguriert?')
        }
        console.error(e)
      } finally {
        this.searching = false
      }
    },

    startPolling() {
      if (this.pollingActive) return
      this.pollingActive = true
      this.schedulePoll()
    },

    stopPolling() {
      this.pollingActive = false
      if (this._pollTimer) {
        clearTimeout(this._pollTimer)
        this._pollTimer = null
      }
    },

    schedulePoll() {
      if (!this.pollingActive) return
      this._pollTimer = setTimeout(() => this.poll(), 2000)
    },

    async poll() {
      if (!this.pollingActive) return

      const pendingCompanies = this.companies.filter(
        c => c.id && this.isScanRunning(c)
      )

      if (pendingCompanies.length === 0) {
        this.stopPolling()
        return
      }

      const params = new URLSearchParams()
      pendingCompanies.forEach(c => params.append('company_ids[]', c.id))

      try {
        const res = await axios.get('/api/scans/status?' + params)

        for (const [companyId, scanData] of Object.entries(res.data)) {
          const idx = this.companies.findIndex(c => c.id === parseInt(companyId))
          if (idx === -1) continue

          if (scanData.status === 'done') {
            this.companies[idx].scan_status = 'done'
            this.companies[idx].scan        = scanData
          } else if (scanData.status === 'failed') {
            this.companies[idx].scan_status = 'failed'
          } else if (['running', 'pending'].includes(scanData.status)) {
            this.companies[idx].scan_status = 'scanning'
          }
        }
      } catch (e) {
        console.error('Polling-Fehler:', e)
      }

      this.schedulePoll()
    },

    async loadMore() {
      if (!this.nextPageToken || this.loadingMore) return
      this.loadingMore = true

      try {
        await new Promise(r => setTimeout(r, 2000))

        const res   = await axios.post('/api/search', {
          branche:    this.query.branche,
          stadt:      this.query.stadt,
          page_token: this.nextPageToken,
        })
        const found = res.data.companies
        this.nextPageToken = res.data.next_page_token ?? null

        const newEntries = found.map(c => ({
          ...c,
          // Same server-aware mapping as startSearch().
          scan_status:  c.scan_status !== undefined ? c.scan_status : (c.has_website ? 'pending' : null),
          scan:         c.scan ?? null,
          lead_created: false,
        }))

        this.companies.push(...newEntries)

        if (!this.pollingActive) {
          this.startPolling()
        }

      } catch (e) {
        this.showToast('Fehler beim Laden weiterer Ergebnisse')
        console.error(e)
      } finally {
        this.loadingMore = false
      }
    },

    async scanOne(company) {
      if (!company.id || !company.has_website) return

      const idx = this.companies.findIndex(c => c.place_id === company.place_id)
      if (idx === -1) return

      this.companies[idx].scan_status = 'scanning'

      try {
        await axios.post(`/api/companies/${company.id}/scan`)

        if (!this.pollingActive) {
          this.startPolling()
        }
      } catch (e) {
        if (e.response?.status === 429) {
          this.showToast('Scan-Limit erreicht. Bitte Plan upgraden.')
        } else {
          this.showToast(`Scan für ${company.name} fehlgeschlagen`)
        }
        this.companies[idx].scan_status = 'failed'
      }
    },

    async createLead(company) {
      const idx = this.companies.findIndex(c => c.place_id === company.place_id)
      try {
        await axios.post('/api/leads', {
          place_id:    company.place_id,
          name:        company.name,
          adresse:     company.adresse,
          stadt:       company.stadt,
          branche:     company.branche,
          telefon:     company.telefon,
          website_url: company.website_url,
          has_website: company.has_website,
          rating:      company.rating,
          bewertungen: company.bewertungen,
          photo_url:   company.photo_url ?? null,
          scan:        company.scan ?? null,
        })
        if (idx !== -1) this.companies[idx].lead_created = true
        this.showToast(`Lead für ${company.name} erstellt`)
      } catch (e) {
        if (e.response?.status === 409) {
          this.showToast('Lead bereits vorhanden')
          if (idx !== -1) this.companies[idx].lead_created = true
        } else {
          this.showToast('Lead konnte nicht erstellt werden')
        }
      }
    },

    async saveSearch() {
      const name = this.saveName.trim() || `${this.query.branche} in ${this.query.stadt}`
      try {
        await axios.post('/watchlist', { name, branche: this.query.branche, stadt: this.query.stadt })
        this.saveFormOpen = false
        this.saveName     = ''
        this.showToast('Suche gespeichert')
      } catch {
        this.showToast('Fehler beim Speichern')
      }
    },

    onStadtInput() {
      this.acIndex = -1
      clearTimeout(this._acTimer)
      const q = this.query.stadt
      if (q.length < 2) {
        this.stadtSuggestions = []
        this.stadtInputOpen   = false
        return
      }
      this._acTimer = setTimeout(async () => {
        try {
          const res = await axios.get('/api/cities', { params: { q } })
          this.stadtSuggestions = res.data
          this.stadtInputOpen   = res.data.length > 0
        } catch {
          this.stadtSuggestions = []
        }
      }, 250)
    },

    acMove(dir) {
      const len = this.filteredStaedte.length
      if (!len) return
      this.acIndex = (this.acIndex + dir + len) % len
    },

    acSelect() {
      if (this.acIndex >= 0 && this.filteredStaedte[this.acIndex]) {
        this.pickStadt(this.filteredStaedte[this.acIndex])
      } else {
        this.stadtInputOpen = false
        this.startSearch()
      }
    },

    pickStadt(s) {
      this.query.stadt    = s
      this.stadtInputOpen = false
      this.acIndex        = -1
    },

    onStadtBlur() {
      setTimeout(() => { this.stadtInputOpen = false; this.acIndex = -1 }, 150)
    },

    // ── Branche Combobox ────────────────────────────────
    brancheMove(dir) {
      const len = this.brancheFlat.length
      if (!len) return
      this.brancheIndex = Math.max(-1, Math.min(len - 1, this.brancheIndex + dir))
    },
    branchePick() {
      if (this.brancheIndex >= 0 && this.brancheFlat[this.brancheIndex]) {
        this.pickBranche(this.brancheFlat[this.brancheIndex])
      } else {
        this.brancheOpen = false
        this.startSearch()
      }
    },
    pickBranche(option) {
      this.query.branche = option
      this.brancheOpen   = false
      this.brancheIndex  = -1
    },
    onBrancheBlur() {
      setTimeout(() => { this.brancheOpen = false; this.brancheIndex = -1 }, 150)
    },

    scoreBadge(score) {
      if (score >= 70) return 'badge-green'
      if (score >= 40) return 'badge-yellow'
      return 'badge-red'
    },

    showToast(msg) {
      this.toast = msg
      setTimeout(() => { this.toast = null }, 3500)
    },
  },
}
</script>

<style scoped>
.spinner {
  width: 14px; height: 14px;
  border: 2px solid var(--border-md);
  border-top-color: var(--text-1);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}
.spinner-sm { width: 11px; height: 11px; }
@keyframes spin { to { transform: rotate(360deg); } }

.summary-pill {
  padding: 10px 16px;
  font-size: 13px;
  flex: 1;
  min-width: 100px;
}

.ac-dropdown {
  position: absolute;
  top: calc(100% + 4px);
  left: 0;
  right: 0;
  background: var(--bg-card);
  border: 1px solid var(--border-md);
  border-radius: 8px;
  box-shadow: 0 4px 16px rgba(0,0,0,.08);
  z-index: 100;
  overflow: hidden;
}
.ac-item {
  padding: 9px 14px;
  font-size: 13px;
  cursor: pointer;
  color: var(--text-1);
}
.ac-item:hover,
.ac-item-active {
  background: var(--bg-hover);
}

.action-btn {
  width: 100px;
  justify-content: center;
  text-align: center;
}
.action-btn-placeholder {
  width: 100px;
  flex-shrink: 0;
}

@media (max-width: 640px) {
  .col-hide-mobile { display: none; }
  .action-btn { width: auto; flex: 1; }
  .action-btn-placeholder { display: none; }
}
</style>
