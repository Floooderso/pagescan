<template>
  <div>

    <!-- ── Eingabe ─────────────────────────────────────────── -->
    <div class="card" style="padding:24px; margin-bottom:20px;">
      <div style="font-weight:600; font-size:15px; margin-bottom:4px;">URL direkt scannen</div>
      <div style="font-size:13px; color:var(--text-3); margin-bottom:16px;">
        Beliebige Website direkt analysieren — ohne Google-Suche, ohne Konto.
      </div>

      <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
        <div style="flex:1; min-width:280px;">
          <label class="form-label">Website-URL</label>
          <input
            v-model="url"
            class="input"
            type="text"
            placeholder="z.B. friseur-berlin.de oder https://..."
            @keydown.enter="doScan"
          >
        </div>
        <button
          class="btn btn-primary"
          style="white-space:nowrap;"
          :disabled="scanning || !canScan"
          @click="doScan"
        >
          <span v-if="scanning" style="display:inline-flex; align-items:center; gap:7px;">
            <span class="spinner" style="border-top-color:#fff; border-color:rgba(255,255,255,.3);"></span>
            Scannt…
          </span>
          <span v-else>Jetzt scannen</span>
        </button>
      </div>

      <div v-if="scanning" style="margin-top:14px; font-size:12px; color:var(--text-3);">
        Ladezeit, SSL, Mobile, Impressum, CMS und mehr werden analysiert — dauert bis zu 15 Sek.
      </div>

      <div v-if="error" style="margin-top:12px; padding:10px 14px; background:rgba(220,38,38,0.07); border:1px solid rgba(220,38,38,0.2); border-radius:8px; font-size:13px; color:#dc2626;">
        {{ error }}
      </div>
    </div>

    <!-- ── Ergebnisse ──────────────────────────────────────── -->
    <template v-if="result">

      <!-- Score-Header -->
      <div class="card" style="padding:24px; margin-bottom:16px;">
        <div style="display:flex; align-items:center; gap:20px; flex-wrap:wrap;">

          <!-- Score-Kreis -->
          <div :class="['score-circle', scoreClass]" style="flex-shrink:0;">
            {{ result.score }}
          </div>

          <!-- URL + Quick-Badges -->
          <div style="flex:1; min-width:200px;">
            <a :href="scannedUrl" target="_blank" style="font-size:13px; font-weight:500; color:var(--text-1); text-decoration:none; word-break:break-all;">
              {{ scannedUrl }}
            </a>
            <div style="display:flex; gap:6px; flex-wrap:wrap; align-items:center; margin-top:8px;">
              <span :class="result.has_ssl ? 'badge-green' : 'badge-red'" style="font-size:11px;">
                {{ result.has_ssl ? '🔒 SSL aktiv' : '⚠️ Kein SSL' }}
              </span>
              <span :class="result.is_mobile ? 'badge-green' : 'badge-red'" style="font-size:11px;">
                {{ result.is_mobile ? '📱 Mobil OK' : '📱 Nicht mobil' }}
              </span>
              <span v-if="result.cms" class="badge-gray" style="font-size:11px;">{{ result.cms }}</span>
              <span v-if="result.speed_rating" :class="speedBadge" style="font-size:11px;">{{ result.speed_rating }}</span>
            </div>
          </div>

          <!-- Score-Zahl -->
          <div style="text-align:right; flex-shrink:0;">
            <div style="font-size:32px; font-weight:700; line-height:1;" :style="{ color: scoreColor }">
              {{ result.score }}
              <span style="font-size:14px; font-weight:400; color:var(--text-3);">/100</span>
            </div>
            <div style="font-size:11px; color:var(--text-3); margin-top:2px; text-transform:uppercase; letter-spacing:.05em;">Website Score</div>
          </div>
        </div>

        <!-- Score-Balken -->
        <div style="margin-top:16px; background:var(--bg-2); border-radius:4px; height:5px; overflow:hidden;">
          <div :style="{ width: result.score + '%', background: scoreColor, height: '5px', borderRadius: '4px', transition: 'width 1s ease' }"></div>
        </div>
      </div>

      <!-- 2-Spalten: Checks + Details -->
      <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:16px;" class="detail-grid">

        <!-- Checks -->
        <div class="card" style="padding:24px;">
          <div style="font-size:11px; font-weight:500; color:var(--text-3); text-transform:uppercase; letter-spacing:.05em; margin-bottom:14px;">Bewertete Kriterien</div>

          <div class="check-row">
            <span :class="['check-icon', result.http_status === 200 ? 'ok' : 'fail']">{{ result.http_status === 200 ? '✓' : '✗' }}</span>
            <span style="flex:1;">HTTP erreichbar</span>
            <span style="font-size:11px; color:var(--text-3);">{{ result.http_status ?? '—' }}</span>
          </div>
          <div class="check-row">
            <span :class="['check-icon', result.has_ssl ? 'ok' : 'fail']">{{ result.has_ssl ? '✓' : '✗' }}</span>
            <span>SSL-Zertifikat (HTTPS)</span>
          </div>
          <div class="check-row">
            <span :class="['check-icon', result.is_mobile ? 'ok' : 'fail']">{{ result.is_mobile ? '✓' : '✗' }}</span>
            <span>Mobile-Optimierung</span>
          </div>
          <div class="check-row">
            <span :class="['check-icon', result.has_contact ? 'ok' : 'fail']">{{ result.has_contact ? '✓' : '✗' }}</span>
            <span>Kontaktdaten vorhanden</span>
          </div>
          <div class="check-row">
            <span :class="['check-icon', result.has_impressum ? 'ok' : 'fail']">{{ result.has_impressum ? '✓' : '✗' }}</span>
            <span>Impressum vorhanden</span>
          </div>
          <div class="check-row">
            <span :class="['check-icon', result.has_favicon ? 'ok' : 'fail']">{{ result.has_favicon ? '✓' : '✗' }}</span>
            <span>Favicon</span>
          </div>

          <!-- Bonus-Checks (ohne Wertung) -->
          <div style="margin-top:14px; padding-top:14px; border-top:1px solid var(--border);">
            <div style="font-size:11px; font-weight:500; color:var(--text-3); text-transform:uppercase; letter-spacing:.05em; margin-bottom:10px;">Weitere Checks</div>

            <div class="check-row">
              <span :class="['check-icon', result.has_og_tags ? 'ok' : 'fail']">{{ result.has_og_tags ? '✓' : '✗' }}</span>
              <span>Open Graph Tags</span>
            </div>
            <div class="check-row">
              <span :class="['check-icon', result.has_cookie_banner ? 'ok' : 'fail']">{{ result.has_cookie_banner ? '✓' : '✗' }}</span>
              <span>Cookie-Banner</span>
            </div>
            <div class="check-row">
              <span :class="['check-icon', result.has_tracking ? 'ok' : 'fail']">{{ result.has_tracking ? '✓' : '✗' }}</span>
              <span>Tracking / Analytics</span>
            </div>
            <div v-if="result.has_google_maps !== null" class="check-row">
              <span :class="['check-icon', result.has_google_maps ? 'ok' : 'fail']">{{ result.has_google_maps ? '✓' : '✗' }}</span>
              <span>Google Maps</span>
            </div>
            <div v-if="result.alt_tag_ratio !== null" class="check-row">
              <span :class="['check-icon', result.alt_tag_ratio >= 0.8 ? 'ok' : 'fail']">{{ result.alt_tag_ratio >= 0.8 ? '✓' : '✗' }}</span>
              <span style="flex:1;">Alt-Texte (Bilder)</span>
              <span style="font-size:11px; color:var(--text-3);">{{ Math.round(result.alt_tag_ratio * 100) }}%</span>
            </div>
            <div v-if="result.broken_links_count !== null" class="check-row">
              <span :class="['check-icon', result.broken_links_count === 0 ? 'ok' : 'fail']">{{ result.broken_links_count === 0 ? '✓' : '✗' }}</span>
              <span>{{ result.broken_links_count === 0 ? 'Keine defekten Links' : result.broken_links_count + ' defekte Links' }}</span>
            </div>
          </div>
        </div>

        <!-- Technische Details -->
        <div class="card" style="padding:24px; display:flex; flex-direction:column; gap:0;">
          <div style="font-size:11px; font-weight:500; color:var(--text-3); text-transform:uppercase; letter-spacing:.05em; margin-bottom:14px;">Technische Details</div>

          <div v-if="result.response_ms" class="detail-row">
            <span class="detail-label">Ladezeit</span>
            <span>
              {{ result.response_ms }}ms
              <span v-if="result.speed_rating" :class="speedBadge" style="font-size:11px; margin-left:6px;">{{ result.speed_rating }}</span>
            </span>
          </div>
          <div v-if="result.cms" class="detail-row">
            <span class="detail-label">CMS</span>
            <span>{{ result.cms }}</span>
          </div>
          <div v-if="result.http_status" class="detail-row">
            <span class="detail-label">HTTP-Status</span>
            <span>{{ result.http_status }}</span>
          </div>
          <div v-if="result.page_title" class="detail-row" style="align-items:flex-start;">
            <span class="detail-label">Seitentitel</span>
            <span style="font-style:italic; color:var(--text-2); line-height:1.4;">{{ result.page_title }}</span>
          </div>

          <div v-if="result.meta_description" style="margin-top:14px; padding-top:14px; border-top:1px solid var(--border);">
            <div style="font-size:11px; font-weight:500; color:var(--text-3); text-transform:uppercase; letter-spacing:.05em; margin-bottom:6px;">Meta-Description</div>
            <p style="font-size:13px; color:var(--text-2); line-height:1.5; margin:0;">{{ result.meta_description }}</p>
          </div>

          <!-- Screenshot-Vorschau -->
          <div style="margin-top:auto; padding-top:16px; border-top:1px solid var(--border);">
            <div style="font-size:11px; font-weight:500; color:var(--text-3); text-transform:uppercase; letter-spacing:.05em; margin-bottom:8px;">Vorschau</div>
            <img
              :src="`https://image.thum.io/get/width/600/crop/280/noanimate/${scannedUrl}`"
              style="width:100%; border-radius:6px; border:1px solid var(--border); display:block; min-height:60px; background:var(--bg);"
              loading="lazy"
              @error="$event.target.style.display='none'"
            >
          </div>
        </div>
      </div>

      <!-- Lead speichern -->
      <div class="card" style="padding:20px 24px;">
        <template v-if="!leadSaved">
          <div v-if="!leadFormOpen" style="display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div>
              <div style="font-weight:500; font-size:13px;">Als Lead speichern</div>
              <div style="font-size:12px; color:var(--text-3); margin-top:2px;">Score und alle Checks werden mit dem Lead verknüpft</div>
            </div>
            <button class="btn btn-primary" @click="openLeadForm">+ Als Lead speichern</button>
          </div>
          <div v-else>
            <div style="font-weight:500; font-size:13px; margin-bottom:12px;">Lead speichern</div>
            <div style="display:flex; gap:10px; align-items:flex-end; flex-wrap:wrap;">
              <div style="flex:1; min-width:200px;">
                <label class="form-label">Name des Unternehmens</label>
                <input v-model="leadName" class="input" :placeholder="result.page_title || 'z.B. Salon Musterfrau'" @keydown.enter="saveLead">
              </div>
              <button class="btn btn-primary" :disabled="savingLead || !leadName.trim()" @click="saveLead">
                {{ savingLead ? 'Speichert…' : 'Speichern' }}
              </button>
              <button class="btn" @click="leadFormOpen = false">Abbrechen</button>
            </div>
            <div v-if="leadError" style="margin-top:8px; font-size:12px; color:#dc2626;">{{ leadError }}</div>
          </div>
        </template>
        <div v-else style="display:flex; align-items:center; gap:10px; font-size:13px; color:#16a34a;">
          <span style="font-size:16px;">✓</span>
          Lead gespeichert —
          <a href="/leads" style="color:var(--text-2); text-decoration:underline; text-underline-offset:3px;">Leads öffnen</a>
        </div>
      </div>

    </template>
  </div>
</template>

<script>
export default {
  data() {
    return {
      url:          '',
      scannedUrl:   '',
      scanning:     false,
      error:        null,
      result:       null,
      // Lead form
      leadFormOpen: false,
      leadName:     '',
      savingLead:   false,
      leadSaved:    false,
      leadError:    null,
    }
  },

  computed: {
    canScan() {
      const u = this.url.trim();
      return u.length > 3 && u !== 'https://' && u !== 'http://';
    },
    scoreClass() {
      if (!this.result) return '';
      if (this.result.score >= 70) return 'good';
      if (this.result.score >= 40) return 'medium';
      return 'bad';
    },
    scoreColor() {
      if (!this.result) return 'var(--text-1)';
      if (this.result.score >= 70) return '#16a34a';
      if (this.result.score >= 40) return '#d97706';
      return '#dc2626';
    },
    speedBadge() {
      switch (this.result?.speed_rating) {
        case 'schnell': return 'badge-green';
        case 'mittel':  return 'badge-yellow';
        case 'langsam': return 'badge-red';
        default:        return 'badge-gray';
      }
    },
  },

  methods: {
    normalizeUrl(raw) {
      raw = raw.trim();
      if (!raw) return raw;
      if (!/^https?:\/\//i.test(raw)) raw = 'https://' + raw;
      return raw;
    },

    async doScan() {
      const normalized = this.normalizeUrl(this.url);
      if (!normalized) return;

      this.url          = normalized;
      this.scannedUrl   = normalized;
      this.scanning     = true;
      this.error        = null;
      this.result       = null;
      this.leadSaved    = false;
      this.leadFormOpen = false;

      try {
        const res = await axios.post('/api/scan-url', { url: normalized });

        if (res.data.status === 'failed') {
          this.error = res.data.error
            ? `Scan fehlgeschlagen: ${res.data.error}`
            : 'Die Website konnte nicht erreicht oder analysiert werden.';
        } else {
          this.result   = res.data;
          this.leadName = res.data.page_title || '';
        }
      } catch (e) {
        if (e.response?.status === 429) {
          this.error = 'Scan-Limit erreicht. Bitte Plan upgraden.';
        } else {
          this.error = 'Scan fehlgeschlagen. Prüfe die URL und versuche es erneut.';
        }
      } finally {
        this.scanning = false;
      }
    },

    openLeadForm() {
      this.leadFormOpen = true;
      this.$nextTick(() => {
        const input = this.$el.querySelector('input[placeholder]');
        input?.focus();
      });
    },

    async saveLead() {
      if (!this.leadName.trim() || this.savingLead) return;
      this.savingLead = true;
      this.leadError  = null;

      try {
        await axios.post('/api/url-lead', {
          url:  this.scannedUrl,
          name: this.leadName.trim(),
          scan: this.result,
        });
        this.leadSaved    = true;
        this.leadFormOpen = false;
      } catch (e) {
        this.leadError = e.response?.data?.message ?? 'Lead konnte nicht gespeichert werden.';
      } finally {
        this.savingLead = false;
      }
    },
  },
}
</script>

<style scoped>
.spinner {
  width: 13px; height: 13px;
  border: 2px solid var(--border-md);
  border-top-color: var(--text-1);
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
  flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }
</style>
