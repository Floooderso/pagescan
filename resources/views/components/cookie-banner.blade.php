<style>
  .cookie-banner {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    z-index: 9999;
    transition: transform 0.25s ease, opacity 0.25s ease;
  }
  .cookie-banner-inner {
    background: #0f0f11;
    border-top: 1px solid #27272a;
    padding: 18px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
  }
  .cookie-text {
    flex: 1;
    min-width: 260px;
  }
  .cookie-title {
    font-size: 13px;
    font-weight: 600;
    color: #f4f4f5;
    margin-bottom: 3px;
  }
  .cookie-desc {
    font-size: 12px;
    color: #71717a;
    line-height: 1.5;
  }
  .cookie-desc a {
    color: #60a5fa;
    text-decoration: none;
  }
  .cookie-desc a:hover { text-decoration: underline; }
  .cookie-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
  }
  .cookie-btn {
    padding: 8px 16px;
    font-size: 12px;
    font-weight: 500;
    border-radius: 7px;
    cursor: pointer;
    transition: background 0.15s, border-color 0.15s, color 0.15s;
    font-family: inherit;
    line-height: 1;
  }
  .cookie-btn-decline {
    background: transparent;
    color: #71717a;
    border: 1px solid #3f3f46;
  }
  .cookie-btn-decline:hover {
    background: #18181b;
    color: #d4d4d8;
    border-color: #52525b;
  }
  .cookie-btn-accept {
    background: #ffffff;
    color: #09090b;
    border: 1px solid #ffffff;
  }
  .cookie-btn-accept:hover {
    background: #e4e4e7;
    border-color: #e4e4e7;
  }
</style>

<div
    x-data="{
        show: false,
        init() {
            this.show = !localStorage.getItem('cookie_consent')
        },
        accept() {
            localStorage.setItem('cookie_consent', 'accepted')
            this.show = false
        },
        decline() {
            localStorage.setItem('cookie_consent', 'declined')
            this.show = false
        }
    }"
    x-cloak
    x-show="show"
    x-transition:enter-start="opacity-0 translate-y-full"
    x-transition:enter-end="opacity-100 translate-y-0"
    x-transition:leave-start="opacity-100 translate-y-0"
    x-transition:leave-end="opacity-0 translate-y-full"
    class="cookie-banner"
>
  <div class="cookie-banner-inner">
    <div class="cookie-text">
      <div class="cookie-title">Diese Website verwendet Cookies</div>
      <p class="cookie-desc">
        Wir verwenden ausschließlich technisch notwendige Cookies (Session, CSRF-Schutz).
        Keine Tracking- oder Werbe-Cookies.
        <a href="/datenschutz">Datenschutzerklärung</a>
      </p>
    </div>
    <div class="cookie-actions">
      <button class="cookie-btn cookie-btn-decline" @click="decline()">
        Nur notwendige
      </button>
      <button class="cookie-btn cookie-btn-accept" @click="accept()">
        Alle akzeptieren
      </button>
    </div>
  </div>
</div>