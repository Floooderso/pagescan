<template>
  <div>
    <!-- Filter-Leiste + View-Toggle -->
    <div style="display:flex; gap:8px; margin-bottom:16px; flex-wrap:wrap; align-items:center;">
      <select v-model="filterStatus" class="input" style="max-width:160px;" @change="applyFilter">
        <option value="">Alle Status</option>
        <option value="neu">Neu</option>
        <option value="kontaktiert">Kontaktiert</option>
        <option value="angebot">Angebot</option>
        <option value="gewonnen">Gewonnen</option>
        <option value="verloren">Verloren</option>
      </select>
      <select v-model="filterPriority" class="input" style="max-width:160px;" @change="applyFilter">
        <option value="">Alle Prioritäten</option>
        <option value="hoch">Hoch</option>
        <option value="mittel">Mittel</option>
        <option value="niedrig">Niedrig</option>
      </select>
      <input v-model="filterMinScore" type="number" min="0" max="100" class="input" style="max-width:90px;" placeholder="Score min" @input="applyFilter">
      <input v-model="filterMaxScore" type="number" min="0" max="100" class="input" style="max-width:90px;" placeholder="Score max" @input="applyFilter">
      <select v-model="filterCms" class="input" style="max-width:140px;" @change="applyFilter">
        <option value="">Alle CMS</option>
        <option>WordPress</option>
        <option>Shopify</option>
        <option>Wix</option>
        <option>Joomla</option>
        <option>Drupal</option>
        <option>TYPO3</option>
        <option>Squarespace</option>
        <option>Webflow</option>
      </select>
      <span style="font-size:13px; color:var(--text-3); margin-left:4px;">
        {{ filtered.length }} Lead{{ filtered.length !== 1 ? 's' : '' }}
      </span>
      <!-- View-Toggle -->
      <div style="display:flex; border:1px solid var(--border-md); border-radius:7px; overflow:hidden; margin-left:auto;">
        <button
          :style="viewMode === 'list' ? 'background:#18181b;color:#fff;' : 'background:#fff;color:#52525b;'"
          style="padding:5px 10px; border:none; cursor:pointer; display:flex; align-items:center;"
          title="Listenansicht"
          @click="setView('list')"
        >
          <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M3 5h14M3 10h14M3 15h14" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
        </button>
        <button
          :style="viewMode === 'kanban' ? 'background:#18181b;color:#fff;' : 'background:#fff;color:#52525b;'"
          style="padding:5px 10px; border:none; border-left:1px solid var(--border-md); cursor:pointer; display:flex; align-items:center;"
          title="Kanban-Ansicht"
          @click="setView('kanban')"
        >
          <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><rect x="2" y="3" width="4" height="14" rx="1"/><rect x="8" y="3" width="4" height="10" rx="1"/><rect x="14" y="3" width="4" height="12" rx="1"/></svg>
        </button>
      </div>
    </div>

    <!-- Lade-Zustand -->
    <div v-if="loading" style="text-align:center; padding:48px; color:var(--text-3);">
      <div class="spinner" style="margin:0 auto 12px;"></div>
      Lädt...
    </div>

    <!-- Leer -->
    <div v-else-if="filtered.length === 0 && leads.length === 0" class="card" style="padding:48px; text-align:center; color:var(--text-3);">
      Noch keine Leads gespeichert.<br>
      <a href="/search" style="color:var(--text-2); text-decoration:underline; margin-top:8px; display:inline-block;">
        Zur Suche
      </a>
    </div>

    <div v-else-if="filtered.length === 0" class="card" style="padding:32px; text-align:center; color:var(--text-3);">
      Kein Lead passt zu den gewählten Filtern.
    </div>

    <template v-else>

      <!-- ═══════════════════════════ LISTEN-ANSICHT ═══════════════════════════ -->
      <div v-if="viewMode === 'list'" style="display:flex; flex-direction:column; gap:16px;">
        <div v-for="lead in filtered" :key="lead.id" class="card lead-card">

          <!-- Bild + Basis-Info -->
          <div class="lead-body">

            <!-- Bild -->
            <div class="screenshot-col" :class="{ 'screenshot-empty': !imageUrl(lead) }">
              <img
                v-if="imageUrl(lead)"
                :src="imageUrl(lead)"
                :alt="lead.company.name"
                class="screenshot-img"
                loading="lazy"
                @error="onImgError($event, lead)"
              >
              <span v-else style="font-size:11px; color:var(--text-3);">Kein Bild</span>
            </div>

            <!-- Haupt-Info -->
            <div class="lead-content">
              <div class="lead-actions">
                <div style="flex:1; min-width:0;">
                  <div style="font-weight:600; font-size:15px;">{{ lead.company.name }}</div>
                  <div style="font-size:12px; color:var(--text-3); margin-top:2px;">
                    {{ lead.company.branche }} · {{ lead.company.stadt }}
                    <span v-if="lead.company.rating"> · ⭐ {{ lead.company.rating.toFixed(1) }} ({{ lead.company.bewertungen }})</span>
                  </div>
                  <div v-if="lead.scan?.page_title" style="font-size:12px; color:var(--text-2); margin-top:4px; font-style:italic;">
                    "{{ lead.scan.page_title }}"
                  </div>
                  <div v-if="lead.scan?.meta_description" style="font-size:11px; color:var(--text-3); margin-top:3px; line-height:1.4; max-width:520px;">
                    {{ lead.scan.meta_description }}
                  </div>
                  <div style="display:flex; flex-wrap:wrap; gap:12px; margin-top:6px;">
                    <a v-if="lead.company.website_url" :href="lead.company.website_url" target="_blank" style="font-size:12px; color:var(--text-2); word-break:break-all;">
                      {{ lead.company.website_url }}
                    </a>
                    <span v-else class="badge-red" style="font-size:11px;">Keine Website</span>
                    <span v-if="lead.company.telefon" style="font-size:12px; color:var(--text-3);">{{ lead.company.telefon }}</span>
                  </div>
                </div>

                <div class="lead-selects">
                  <select :value="lead.status" class="input" style="width:140px; font-size:12px; padding:5px 8px;" @change="updateField(lead, 'status', $event.target.value)">
                    <option value="neu">Neu</option>
                    <option value="kontaktiert">Kontaktiert</option>
                    <option value="angebot">Angebot</option>
                    <option value="gewonnen">Gewonnen</option>
                    <option value="verloren">Verloren</option>
                  </select>
                  <select :value="lead.priority" class="input" style="width:120px; font-size:12px; padding:5px 8px;" @change="updateField(lead, 'priority', $event.target.value)">
                    <option value="hoch">Hoch</option>
                    <option value="mittel">Mittel</option>
                    <option value="niedrig">Niedrig</option>
                  </select>
                  <select v-if="isAdmin" :value="lead.assigned_to ?? ''" class="input" style="width:140px; font-size:12px; padding:5px 8px;" @change="assignLead(lead, $event.target.value || null)">
                    <option value="">Nicht zugewiesen</option>
                    <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                  </select>
                  <a :href="`/companies/${lead.company.id}`" class="btn btn-sm">Details</a>
                  <button class="btn btn-sm btn-danger" @click="deleteLead(lead)">Löschen</button>
                </div>
              </div>

              <!-- Scan-Daten -->
              <div v-if="lead.scan" style="margin-top:14px; padding-top:12px; border-top:1px solid var(--border);">
                <div style="display:flex; gap:16px; flex-wrap:wrap; align-items:center;">
                  <div :class="['score-pill', scoreClass(lead.scan.score)]">{{ lead.scan.score }}/100</div>
                  <div style="display:flex; gap:8px; flex-wrap:wrap;">
                    <span class="check-chip" :class="lead.scan.has_ssl ? 'ok' : 'fail'">{{ lead.scan.has_ssl ? '✓' : '✗' }} SSL</span>
                    <span class="check-chip" :class="lead.scan.is_mobile ? 'ok' : 'fail'">{{ lead.scan.is_mobile ? '✓' : '✗' }} Mobile</span>
                    <span class="check-chip" :class="lead.scan.has_impressum ? 'ok' : 'fail'">{{ lead.scan.has_impressum ? '✓' : '✗' }} Impressum</span>
                    <span class="check-chip" :class="lead.scan.has_contact ? 'ok' : 'fail'">{{ lead.scan.has_contact ? '✓' : '✗' }} Kontakt</span>
                    <span class="check-chip" :class="lead.scan.has_tracking ? 'ok' : 'fail'">{{ lead.scan.has_tracking ? '✓' : '✗' }} Tracking</span>
                    <span class="check-chip" :class="lead.scan.has_favicon ? 'ok' : 'fail'">{{ lead.scan.has_favicon ? '✓' : '✗' }} Favicon</span>
                    <span v-if="lead.scan.has_og_tags !== null" class="check-chip" :class="lead.scan.has_og_tags ? 'ok' : 'fail'">{{ lead.scan.has_og_tags ? '✓' : '✗' }} OG-Tags</span>
                    <span v-if="lead.scan.has_cookie_banner !== null" class="check-chip" :class="lead.scan.has_cookie_banner ? 'ok' : 'fail'">{{ lead.scan.has_cookie_banner ? '✓' : '✗' }} Cookie-Banner</span>
                  </div>
                  <div style="display:flex; gap:12px; font-size:11px; color:var(--text-3); flex-wrap:wrap; align-items:center;">
                    <span v-if="lead.scan.response_ms">{{ lead.scan.response_ms }}ms</span>
                    <span v-if="lead.scan.speed_rating" :style="lead.scan.speed_rating === 'schnell' ? 'color:#16a34a;' : lead.scan.speed_rating === 'mittel' ? 'color:#ca8a04;' : 'color:#dc2626;'">{{ lead.scan.speed_rating }}</span>
                    <span v-if="lead.scan.cms">{{ lead.scan.cms }}</span>
                    <span v-if="lead.scan.http_status">HTTP {{ lead.scan.http_status }}</span>
                  </div>
                </div>
              </div>
              <div v-else-if="lead.company.has_website" style="margin-top:12px; font-size:12px; color:var(--text-3);">Noch kein Scan vorhanden</div>
            </div>
          </div>

          <!-- Notizen -->
          <div style="padding:14px 20px 0; border-top:1px solid var(--border);">
            <label style="font-size:11px; font-weight:500; color:var(--text-3); text-transform:uppercase; letter-spacing:0.05em; display:block; margin-bottom:6px;">Notizen</label>
            <div style="display:flex; gap:8px; align-items:flex-start;">
              <textarea v-model="lead.notes" class="input" rows="2" style="resize:vertical; font-size:13px;" placeholder="Notizen zum Lead..." @blur="saveNotes(lead)"></textarea>
              <button class="btn btn-sm" :class="{ 'btn-primary': lead._notesDirty }" @click="saveNotes(lead)" style="flex-shrink:0;">
                {{ lead._saving ? '...' : 'Speichern' }}
              </button>
            </div>
          </div>

          <!-- Kontakthistorie -->
          <div style="padding:14px 20px 20px; border-top:1px solid var(--border); margin-top:14px;">
            <button style="background:none; border:none; cursor:pointer; display:flex; align-items:center; gap:6px; font-size:11px; font-weight:500; color:var(--text-3); text-transform:uppercase; letter-spacing:0.05em; padding:0;" @click="lead._historyOpen = !lead._historyOpen">
              <span>Kontakthistorie</span>
              <span style="font-size:10px;">{{ lead._historyOpen ? '▲' : '▼' }}</span>
              <span style="font-weight:400; text-transform:none; letter-spacing:0;">({{ lead.contact_attempts.length }})</span>
            </button>
            <div v-if="lead._historyOpen" style="margin-top:12px;">
              <div v-if="lead.contact_attempts.length === 0" style="font-size:12px; color:var(--text-3); margin-bottom:12px;">Noch keine Kontaktversuche eingetragen.</div>
              <div v-for="attempt in lead.contact_attempts" :key="attempt.id" style="display:flex; align-items:flex-start; gap:10px; padding:8px 0; border-bottom:1px solid var(--border);">
                <span :class="kanalBadge(attempt.kanal)" style="font-size:11px; padding:2px 8px; border-radius:20px; font-weight:500; white-space:nowrap; flex-shrink:0;">{{ kanalLabel(attempt.kanal) }}</span>
                <div style="flex:1; min-width:0;">
                  <div style="font-size:12px; color:var(--text-2);">{{ attempt.kontaktiert_am }}</div>
                  <div v-if="attempt.notiz" style="font-size:12px; color:var(--text-3); margin-top:2px;">{{ attempt.notiz }}</div>
                </div>
                <button style="background:none; border:none; cursor:pointer; color:var(--text-3); font-size:14px; padding:0 4px; flex-shrink:0;" @click="deleteAttempt(lead, attempt)">🗑</button>
              </div>
              <div style="margin-top:12px; display:flex; flex-direction:column; gap:8px;">
                <div style="display:flex; gap:8px; flex-wrap:wrap;">
                  <select v-model="lead._newAttempt.kanal" class="input" style="flex:1; min-width:130px; font-size:13px; padding:6px 8px;">
                    <option value="email">E-Mail</option>
                    <option value="telefon">Telefon</option>
                    <option value="linkedin">LinkedIn</option>
                    <option value="persoenlich">Persönlich</option>
                    <option value="sonstiges">Sonstiges</option>
                  </select>
                  <input v-model="lead._newAttempt.kontaktiert_am" type="date" class="input" style="flex:1; min-width:130px; font-size:13px; padding:6px 8px;">
                </div>
                <div style="display:flex; gap:8px;">
                  <textarea v-model="lead._newAttempt.notiz" class="input" rows="2" style="flex:1; resize:vertical; font-size:13px;" placeholder="Notiz (optional)..."></textarea>
                  <button class="btn btn-sm btn-primary" style="flex-shrink:0; align-self:flex-end;" @click="addAttempt(lead)">Eintragen</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- ═══════════════════════════ KANBAN-ANSICHT ═══════════════════════════ -->
      <div v-else class="kanban-board">
        <div
          v-for="col in kanbanColumns"
          :key="col.status"
          class="kanban-col"
          :class="{ 'kanban-col-drag-over': dragOverColumn === col.status }"
          @dragover.prevent="dragOverColumn = col.status"
          @dragleave="dragOverColumn = null"
          @drop="onDrop(col.status)"
        >
          <!-- Spalten-Header -->
          <div class="kanban-col-header">
            <div class="kanban-col-bar" :style="{ background: col.color }"></div>
            <div style="display:flex; align-items:center; justify-content:space-between; padding:10px 12px 8px;">
              <span style="font-weight:600; font-size:13px;">{{ col.label }}</span>
              <span style="font-size:11px; background:var(--bg); border-radius:10px; padding:1px 7px; color:var(--text-3);">
                {{ leadsForColumn(col.status).length }}
              </span>
            </div>
          </div>

          <!-- Karten -->
          <div class="kanban-col-body">
            <div
              v-for="lead in leadsForColumn(col.status)"
              :key="lead.id"
              class="kanban-card"
              draggable="true"
              @dragstart="onDragStart(lead.id)"
              @dragend="onDragEnd"
              @click="openModal(lead)"
            >
              <div style="font-weight:600; font-size:13px; margin-bottom:4px;">{{ lead.company.name }}</div>
              <div style="font-size:11px; color:var(--text-3); margin-bottom:8px;">{{ lead.company.stadt }}</div>
              <div style="display:flex; gap:6px; flex-wrap:wrap;">
                <span v-if="lead.scan" :class="['score-pill', scoreClass(lead.scan.score)]" style="font-size:11px; padding:2px 7px;">
                  {{ lead.scan.score }}/100
                </span>
                <span :class="priorityBadge(lead.priority)" style="font-size:11px; padding:2px 7px; border-radius:20px; font-weight:500;">
                  {{ lead.priority }}
                </span>
              </div>
            </div>
            <div v-if="leadsForColumn(col.status).length === 0" class="kanban-empty">Keine Leads</div>
          </div>
        </div>
      </div>

    </template>

    <!-- ═══════════════════════════ DETAIL-MODAL (Kanban) ═══════════════════════════ -->
    <div v-if="modalLead" class="modal-overlay" @click.self="closeModal">
      <div class="modal-box" @keydown.esc="closeModal">
        <div style="display:flex; align-items:flex-start; justify-content:space-between; margin-bottom:16px;">
          <div>
            <div style="font-weight:700; font-size:16px;">{{ modalLead.company.name }}</div>
            <div style="font-size:12px; color:var(--text-3);">{{ modalLead.company.branche }} · {{ modalLead.company.stadt }}</div>
          </div>
          <button style="background:none; border:none; cursor:pointer; font-size:20px; color:var(--text-3); line-height:1;" @click="closeModal">✕</button>
        </div>

        <!-- Status + Priorität -->
        <div style="display:flex; gap:8px; margin-bottom:16px;">
          <select :value="modalLead.status" class="input" style="width:150px; font-size:13px;" @change="updateField(modalLead, 'status', $event.target.value)">
            <option value="neu">Neu</option>
            <option value="kontaktiert">Kontaktiert</option>
            <option value="angebot">Angebot</option>
            <option value="gewonnen">Gewonnen</option>
            <option value="verloren">Verloren</option>
          </select>
          <select :value="modalLead.priority" class="input" style="width:130px; font-size:13px;" @change="updateField(modalLead, 'priority', $event.target.value)">
            <option value="hoch">Hoch</option>
            <option value="mittel">Mittel</option>
            <option value="niedrig">Niedrig</option>
          </select>
          <a :href="`/companies/${modalLead.company.id}`" class="btn btn-sm" target="_blank">Details</a>
        </div>

        <!-- Scan-Daten -->
        <div v-if="modalLead.scan" style="margin-bottom:16px; padding:12px; background:var(--bg); border-radius:8px;">
          <div style="display:flex; gap:10px; flex-wrap:wrap; align-items:center; margin-bottom:8px;">
            <span :class="['score-pill', scoreClass(modalLead.scan.score)]">{{ modalLead.scan.score }}/100</span>
            <span v-if="modalLead.scan.cms" style="font-size:12px; color:var(--text-3);">{{ modalLead.scan.cms }}</span>
            <span v-if="modalLead.scan.speed_rating" style="font-size:12px;" :style="modalLead.scan.speed_rating === 'schnell' ? 'color:#16a34a' : modalLead.scan.speed_rating === 'mittel' ? 'color:#ca8a04' : 'color:#dc2626'">{{ modalLead.scan.speed_rating }}</span>
          </div>
          <div style="display:flex; gap:6px; flex-wrap:wrap;">
            <span class="check-chip" :class="modalLead.scan.has_ssl ? 'ok' : 'fail'">{{ modalLead.scan.has_ssl ? '✓' : '✗' }} SSL</span>
            <span class="check-chip" :class="modalLead.scan.is_mobile ? 'ok' : 'fail'">{{ modalLead.scan.is_mobile ? '✓' : '✗' }} Mobile</span>
            <span class="check-chip" :class="modalLead.scan.has_impressum ? 'ok' : 'fail'">{{ modalLead.scan.has_impressum ? '✓' : '✗' }} Impressum</span>
            <span class="check-chip" :class="modalLead.scan.has_contact ? 'ok' : 'fail'">{{ modalLead.scan.has_contact ? '✓' : '✗' }} Kontakt</span>
            <span class="check-chip" :class="modalLead.scan.has_favicon ? 'ok' : 'fail'">{{ modalLead.scan.has_favicon ? '✓' : '✗' }} Favicon</span>
          </div>
        </div>

        <!-- Notizen -->
        <div style="margin-bottom:16px;">
          <label style="font-size:11px; font-weight:500; color:var(--text-3); text-transform:uppercase; letter-spacing:0.05em; display:block; margin-bottom:6px;">Notizen</label>
          <div style="display:flex; gap:8px;">
            <textarea v-model="modalLead.notes" class="input" rows="3" style="resize:vertical; font-size:13px;" placeholder="Notizen..."></textarea>
            <button class="btn btn-sm" @click="saveNotes(modalLead)" style="flex-shrink:0; align-self:flex-end;">
              {{ modalLead._saving ? '...' : 'Speichern' }}
            </button>
          </div>
        </div>

        <!-- Kontakthistorie -->
        <div>
          <div style="font-size:11px; font-weight:500; color:var(--text-3); text-transform:uppercase; letter-spacing:0.05em; margin-bottom:8px;">
            Kontakthistorie ({{ modalLead.contact_attempts.length }})
          </div>
          <div v-if="modalLead.contact_attempts.length === 0" style="font-size:12px; color:var(--text-3); margin-bottom:8px;">Noch keine Einträge.</div>
          <div v-for="attempt in modalLead.contact_attempts" :key="attempt.id" style="display:flex; align-items:flex-start; gap:8px; padding:6px 0; border-bottom:1px solid var(--border);">
            <span :class="kanalBadge(attempt.kanal)" style="font-size:11px; padding:2px 8px; border-radius:20px; font-weight:500; white-space:nowrap; flex-shrink:0;">{{ kanalLabel(attempt.kanal) }}</span>
            <div style="flex:1; font-size:12px; color:var(--text-2);">{{ attempt.kontaktiert_am }}<span v-if="attempt.notiz"> — {{ attempt.notiz }}</span></div>
            <button style="background:none; border:none; cursor:pointer; color:var(--text-3); font-size:13px; padding:0;" @click="deleteAttempt(modalLead, attempt)">🗑</button>
          </div>
        </div>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toast" style="position:fixed; bottom:24px; right:24px; background:var(--text-1); color:#fff; padding:10px 18px; border-radius:8px; font-size:13px; z-index:9999;">
      {{ toast }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    isAdmin: { type: Boolean, default: false },
  },
  data() {
    const params = new URLSearchParams(window.location.search)
    return {
      leads:          [],
      users:          [],
      loading:        true,
      filterStatus:   params.get('status')   ?? '',
      filterPriority: params.get('priority') ?? '',
      filterMinScore: params.get('min_score') ?? '',
      filterMaxScore: params.get('max_score') ?? '',
      filterCms:      params.get('cms') ?? '',
      toast:          null,
      viewMode:       localStorage.getItem('leads_view') ?? 'list',
      draggedLeadId:  null,
      dragOverColumn: null,
      modalLead:      null,
      kanbanColumns: [
        { status: 'neu',         label: 'Neu',         color: '#71717a' },
        { status: 'kontaktiert', label: 'Kontaktiert', color: '#3b82f6' },
        { status: 'angebot',     label: 'Angebot',     color: '#f59e0b' },
        { status: 'gewonnen',    label: 'Gewonnen',    color: '#22c55e' },
        { status: 'verloren',    label: 'Verloren',    color: '#ef4444' },
      ],
    }
  },
  computed: {
    filtered() {
      return this.leads.filter(l => {
        if (this.filterStatus   && l.status   !== this.filterStatus)   return false
        if (this.filterPriority && l.priority !== this.filterPriority) return false
        if (this.filterMinScore !== '' && (l.scan?.score ?? -1) < Number(this.filterMinScore)) return false
        if (this.filterMaxScore !== '' && (l.scan?.score ?? 101) > Number(this.filterMaxScore)) return false
        if (this.filterCms && l.scan?.cms !== this.filterCms) return false
        return true
      })
    },
  },
  async mounted() {
    try {
      const requests = [axios.get('/api/leads')]
      if (this.isAdmin) requests.push(axios.get('/api/users'))
      const [leadsRes, usersRes] = await Promise.all(requests)
      this.leads = leadsRes.data.map(l => ({
        ...l,
        _notesDirty:  false,
        _saving:      false,
        _historyOpen: false,
        _newAttempt:  { kanal: 'email', notiz: '', kontaktiert_am: new Date().toISOString().slice(0, 10) },
        contact_attempts: l.contact_attempts ?? [],
      }))
      if (usersRes) this.users = usersRes.data
    } finally {
      this.loading = false
    }
    window.addEventListener('keydown', this.onKeyDown)
  },
  beforeUnmount() {
    window.removeEventListener('keydown', this.onKeyDown)
  },
  methods: {
    setView(mode) {
      this.viewMode = mode
      localStorage.setItem('leads_view', mode)
    },

    leadsForColumn(status) {
      return this.filtered.filter(l => l.status === status)
    },

    onDragStart(leadId) {
      this.draggedLeadId = leadId
    },

    onDragEnd() {
      this.draggedLeadId  = null
      this.dragOverColumn = null
    },

    async onDrop(newStatus) {
      this.dragOverColumn = null
      if (!this.draggedLeadId) return
      const lead = this.leads.find(l => l.id === this.draggedLeadId)
      if (!lead || lead.status === newStatus) return

      const prevStatus = lead.status
      lead.status = newStatus // optimistic update
      try {
        await axios.put(`/api/leads/${lead.id}`, { status: newStatus })
        this.showToast(`Status: ${newStatus}`)
      } catch {
        lead.status = prevStatus // rollback
        this.showToast('Fehler beim Verschieben')
      }
    },

    openModal(lead) {
      this.modalLead = lead
    },

    closeModal() {
      this.modalLead = null
    },

    onKeyDown(e) {
      if (e.key === 'Escape') this.closeModal()
    },

    applyFilter() {
      const params = new URLSearchParams()
      if (this.filterStatus)   params.set('status',    this.filterStatus)
      if (this.filterPriority) params.set('priority',  this.filterPriority)
      if (this.filterMinScore) params.set('min_score', this.filterMinScore)
      if (this.filterMaxScore) params.set('max_score', this.filterMaxScore)
      if (this.filterCms)      params.set('cms',       this.filterCms)
      const qs = params.toString()
      history.replaceState(null, '', qs ? '?' + qs : window.location.pathname)
    },

    imageUrl(lead) {
      if (lead.company.screenshot_path) return lead.company.screenshot_path
      if (lead.company.has_website && lead.company.website_url) {
        return `https://image.thum.io/get/width/600/crop/400/noanimate/${lead.company.website_url}`
      }
      return null
    },

    onImgError(event, lead) {
      const fallback = lead.company.has_website && lead.company.website_url
        ? `https://image.thum.io/get/width/600/crop/400/noanimate/${lead.company.website_url}`
        : null
      if (fallback && event.target.src !== fallback) {
        event.target.src = fallback
      } else {
        event.target.style.display = 'none'
      }
    },

    scoreClass(score) {
      if (score >= 70) return 'score-good'
      if (score >= 40) return 'score-medium'
      return 'score-bad'
    },

    priorityBadge(priority) {
      return { hoch: 'prio-hoch', mittel: 'prio-mittel', niedrig: 'prio-niedrig' }[priority] ?? 'prio-niedrig'
    },

    async deleteLead(lead) {
      if (!confirm(`Lead "${lead.company.name}" wirklich löschen?`)) return
      try {
        await axios.delete(`/api/leads/${lead.id}`)
        this.leads = this.leads.filter(l => l.id !== lead.id)
        this.showToast('Lead gelöscht')
      } catch {
        this.showToast('Fehler beim Löschen')
      }
    },

    async updateField(lead, field, value) {
      lead[field] = value
      try {
        await axios.put(`/api/leads/${lead.id}`, { [field]: value })
        this.showToast(`${field === 'status' ? 'Status' : 'Priorität'} gespeichert`)
      } catch {
        this.showToast('Fehler beim Speichern')
      }
    },

    async saveNotes(lead) {
      if (lead._saving) return
      lead._saving = true
      try {
        await axios.put(`/api/leads/${lead.id}`, { notes: lead.notes ?? '' })
        lead._notesDirty = false
        this.showToast('Notiz gespeichert')
      } catch {
        this.showToast('Fehler beim Speichern')
      } finally {
        lead._saving = false
      }
    },

    async addAttempt(lead) {
      const { kanal, notiz, kontaktiert_am } = lead._newAttempt
      if (!kanal || !kontaktiert_am) return
      try {
        const res = await axios.post(`/api/leads/${lead.id}/contact-attempts`, { kanal, notiz, kontaktiert_am })
        lead.contact_attempts.unshift(res.data)
        lead._newAttempt = { kanal: 'email', notiz: '', kontaktiert_am: new Date().toISOString().slice(0, 10) }
        this.showToast('Kontaktversuch eingetragen')
      } catch {
        this.showToast('Fehler beim Speichern')
      }
    },

    async deleteAttempt(lead, attempt) {
      try {
        await axios.delete(`/api/leads/${lead.id}/contact-attempts/${attempt.id}`)
        lead.contact_attempts = lead.contact_attempts.filter(a => a.id !== attempt.id)
        this.showToast('Eintrag gelöscht')
      } catch {
        this.showToast('Fehler beim Löschen')
      }
    },

    kanalLabel(kanal) {
      return { email: 'E-Mail', telefon: 'Telefon', linkedin: 'LinkedIn', persoenlich: 'Persönlich', sonstiges: 'Sonstiges' }[kanal] ?? kanal
    },

    kanalBadge(kanal) {
      return { email: 'kanal-email', telefon: 'kanal-telefon', linkedin: 'kanal-linkedin', persoenlich: 'kanal-persoenlich', sonstiges: 'kanal-sonstiges' }[kanal] ?? 'kanal-sonstiges'
    },

    async assignLead(lead, userId) {
      try {
        const res = await axios.post(`/api/leads/${lead.id}/assign`, { assigned_to: userId || null })
        lead.assigned_to      = res.data.assigned_to
        lead.assigned_to_name = res.data.assigned_to_name
        this.showToast('Zugewiesen')
      } catch {
        this.showToast('Fehler beim Zuweisen')
      }
    },

    showToast(msg) {
      this.toast = msg
      clearTimeout(this._toastTimer)
      this._toastTimer = setTimeout(() => { this.toast = null }, 3000)
    },
  },
}
</script>

<style scoped>
.lead-card { overflow: hidden; }
.screenshot-col { width:200px; min-height:150px; flex-shrink:0; overflow:hidden; border-right:1px solid var(--border); display:flex; align-items:center; justify-content:center; background:var(--bg); }
.screenshot-img { width:100%; height:150px; object-fit:cover; object-position:top; display:block; }
.screenshot-empty { flex-direction:column; gap:4px; }
.lead-body { display:flex; }
.lead-content { flex:1; padding:20px; min-width:0; }
.lead-actions { display:flex; align-items:flex-start; justify-content:space-between; gap:12px; flex-wrap:wrap; }
.lead-selects { display:flex; gap:8px; flex-wrap:wrap; flex-shrink:0; }

/* Kanban */
.kanban-board { display:flex; gap:12px; overflow-x:auto; padding-bottom:8px; align-items:flex-start; }
.kanban-col { flex:0 0 220px; background:var(--bg); border:1px solid var(--border); border-radius:var(--radius); overflow:hidden; }
.kanban-col-drag-over { outline:2px solid #818cf8; outline-offset:-2px; }
.kanban-col-header { background:var(--bg-card); border-bottom:1px solid var(--border); }
.kanban-col-bar { height:4px; }
.kanban-col-body { padding:8px; display:flex; flex-direction:column; gap:8px; min-height:80px; }
.kanban-card { background:var(--bg-card); border:1px solid var(--border); border-radius:8px; padding:10px 12px; cursor:grab; transition:box-shadow 0.15s; }
.kanban-card:hover { box-shadow:0 2px 8px rgba(0,0,0,.1); }
.kanban-card:active { cursor:grabbing; }
.kanban-empty { font-size:12px; color:var(--text-3); text-align:center; padding:16px 0; }

/* Modal */
.modal-overlay { position:fixed; inset:0; background:rgba(0,0,0,.45); display:flex; align-items:center; justify-content:center; z-index:1000; padding:16px; }
.modal-box { background:var(--bg-card); border-radius:var(--radius); box-shadow:0 8px 32px rgba(0,0,0,.2); width:100%; max-width:580px; max-height:88vh; overflow-y:auto; padding:24px; }

.score-pill { font-size:13px; font-weight:600; padding:3px 10px; border-radius:20px; flex-shrink:0; }
.score-good   { background:#f0fdf4; color:#16a34a; }
.score-medium { background:#fefce8; color:#ca8a04; }
.score-bad    { background:#fef2f2; color:#dc2626; }

.prio-hoch    { background:#fef2f2; color:#dc2626; }
.prio-mittel  { background:#fefce8; color:#ca8a04; }
.prio-niedrig { background:#f4f4f5; color:#71717a; }

.check-chip { font-size:11px; font-weight:500; padding:2px 8px; border-radius:20px; white-space:nowrap; }
.check-chip.ok   { background:#f0fdf4; color:#16a34a; }
.check-chip.fail { background:#fef2f2; color:#dc2626; }

.spinner { width:20px; height:20px; border:2px solid var(--border-md); border-top-color:var(--text-1); border-radius:50%; animation:spin 0.7s linear infinite; }
@keyframes spin { to { transform:rotate(360deg); } }

.kanal-email       { background:#eff6ff; color:#1d4ed8; }
.kanal-telefon     { background:#f0fdf4; color:#15803d; }
.kanal-linkedin    { background:#eef2ff; color:#4338ca; }
.kanal-persoenlich { background:#fff7ed; color:#c2410c; }
.kanal-sonstiges   { background:#f9fafb; color:#6b7280; }

@media (max-width:640px) {
  .lead-body { flex-direction:column; }
  .screenshot-col { width:100%; height:180px; min-height:unset; border-right:none; border-bottom:1px solid var(--border); }
  .screenshot-img { width:100%; height:180px; }
  .lead-content { padding:14px; }
  .lead-actions { flex-direction:column; gap:10px; }
  .lead-selects { width:100%; }
  .lead-selects select { flex:1; }
  .lead-selects a { flex:1; text-align:center; }
}
</style>
