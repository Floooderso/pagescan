<x-app-layout>
  <div class="page" x-data="{ tab: new URLSearchParams(window.location.search).has('branche') ? 'search' : 'search' }">

    <div class="page-header">
      <div>
        <div class="page-title" x-text="tab === 'search' ? 'Business Search' : 'URL Scanner'"></div>
        <div class="page-subtitle" x-text="tab === 'search' ? 'Find businesses via Google Places and scan their websites' : 'Analyze any website directly'"></div>
      </div>

      <!-- Tab-Switcher -->
      <div style="display:flex; background:var(--bg-2); border:1px solid var(--border); border-radius:8px; padding:3px; gap:3px;">
        <button
          @click="tab='search'"
          class="btn btn-sm"
          :class="tab === 'search' ? 'btn-primary' : ''"
          style="border:none; font-size:12px;"
        >
          Business Search
        </button>
        <button
          @click="tab='url'"
          class="btn btn-sm"
          :class="tab === 'url' ? 'btn-primary' : ''"
          style="border:none; font-size:12px;"
        >
          Scan URL Directly
        </button>
      </div>
    </div>

    <div x-show="tab === 'search'" x-cloak>
      <div id="company-search-app"></div>
    </div>

    <div x-show="tab === 'url'" x-cloak>
      <div id="url-scanner-app"></div>
    </div>

  </div>
</x-app-layout>
