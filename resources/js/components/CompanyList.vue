<template>
  <div>
    <!-- Count -->
    <div v-if="!loading" style="font-size:13px; color:var(--text-3); margin-bottom:12px;">
      {{ companies.length }} Unternehmen gefunden
    </div>

    <div class="card" style="overflow:hidden;">
      <table>
        <thead>
          <tr>
            <th>Unternehmen</th>
            <th>Branche</th>
            <th>Stadt</th>
            <th>Website</th>
            <th>Score</th>
            <th>Aktionen</th>
          </tr>
        </thead>
        <tbody>
          <tr v-if="loading">
            <td colspan="6" style="text-align:center; color:var(--text-3); padding:32px;">Lädt...</td>
          </tr>
          <tr v-else-if="companies.length === 0">
            <td colspan="6" style="text-align:center; color:var(--text-3); padding:32px;">
              Noch keine Unternehmen – starte eine
              <a href="/search" style="color:var(--text-2); text-decoration:underline;">Suche</a>.
            </td>
          </tr>
          <tr v-for="c in companies" :key="c.id">
            <td>
              <a :href="`/companies/${c.id}`" style="color:var(--text-1); text-decoration:none; font-weight:500;">{{ c.name }}</a>
            </td>
            <td style="color:var(--text-2);">{{ c.branche }}</td>
            <td style="color:var(--text-2);">{{ c.stadt }}</td>
            <td>
              <a v-if="c.has_website" :href="c.website_url" target="_blank" style="font-size:12px; color:var(--text-2); word-break:break-all;">
                {{ c.website_url }}
              </a>
              <span v-else class="badge-gray">Keine Website</span>
            </td>
            <td>
              <span v-if="c.latest_scan" :class="scoreBadge(c.latest_scan.score)">
                {{ c.latest_scan.score }}/100
              </span>
              <span v-else-if="c.has_website" class="badge-gray">Ausstehend</span>
              <span v-else>—</span>
            </td>
            <td>
              <div style="display:flex; gap:6px;">
                <a :href="`/companies/${c.id}`" class="btn btn-sm">Details</a>
                <button class="btn btn-sm btn-primary" @click="createLead(c)" :disabled="c.lead_created">
                  {{ c.lead_created ? 'Lead gespeichert' : '+ Lead' }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <div v-if="toast" style="position:fixed; bottom:24px; right:24px; background:var(--text-1); color:#fff; padding:10px 18px; border-radius:8px; font-size:13px; z-index:999;">
      {{ toast }}
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      companies: [],
      loading:   true,
      toast:     null,
    }
  },
  async mounted() {
    await this.load()
    window.addEventListener('company-filter-changed', () => this.load())
  },
  beforeUnmount() {
    window.removeEventListener('company-filter-changed', () => this.load())
  },
  methods: {
    async load() {
      this.loading = true
      const params = new URLSearchParams(window.location.search)
      const res = await axios.get(`/api/companies?${params}`)
      this.companies = res.data.map(c => ({ ...c, lead_created: false }))
      this.loading = false
    },

    async createLead(company) {
      try {
        await axios.post('/api/leads', { company_id: company.id })
        company.lead_created = true
        this.showToast(`Lead für ${company.name} erstellt`)
      } catch {
        this.showToast('Fehler beim Erstellen')
      }
    },

    scoreBadge(score) {
      if (score >= 70) return 'badge-green'
      if (score >= 40) return 'badge-yellow'
      return 'badge-red'
    },

    showToast(msg) {
      this.toast = msg
      setTimeout(() => { this.toast = null }, 3000)
    },
  },
}
</script>
