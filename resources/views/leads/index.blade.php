<x-app-layout>
  <div class="page">
    <div class="page-header">
      <div>
        <div class="page-title">Leads</div>
        <div class="page-subtitle">Website-Vorschau, Scan-Ergebnisse und Notizen</div>
      </div>
      <a href="/leads/export" class="btn" id="csv-export-btn">CSV Export</a>
    </div>

    <div id="leads-app" data-is-admin="{{ auth()->user()->isAdmin() ? '1' : '0' }}"></div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const btn = document.getElementById('csv-export-btn')
      if (!btn) return
      btn.addEventListener('click', function (e) {
        e.preventDefault()
        const params = new URLSearchParams(window.location.search)
        const url = '/leads/export' + (params.toString() ? '?' + params.toString() : '')
        window.location.href = url
      })
    })
  </script>
</x-app-layout>
