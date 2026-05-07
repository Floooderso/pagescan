# PageScan

Website-Scanner und Lead-Management für lokale Unternehmen.
Sucht echte Unternehmen via Google Places API, scannt deren Websites live und hilft beim Lead-Management.

## Setup

```bash
composer install
npm install && npm run build
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
# DB_DATABASE in .env auf absoluten Pfad setzen
# GOOGLE_PLACES_API_KEY in .env eintragen (siehe unten)
php artisan migrate --seed
```

## Starten

```bash
php artisan serve
```

In einem zweiten Terminal (Queue Worker für async Scans):

```bash
php artisan queue:work --sleep=3 --tries=2
```

## Login

- **URL:** http://localhost:8000
- **E-Mail:** admin@pagescan.de
- **Passwort:** password

## Google Places API Key

1. [https://console.cloud.google.com](https://console.cloud.google.com) aufrufen
2. Neues Projekt erstellen oder bestehendes wählen
3. APIs & Dienste → **"Places API"** aktivieren
4. Anmeldedaten → API-Schlüssel erstellen
5. Key in `.env` eintragen:
   ```
   GOOGLE_PLACES_API_KEY=dein_key_hier
   ```

Ohne API-Key funktioniert die Suche nicht.
Die Places API hat ein kostenloses Kontingent von ~200 $/Monat.

## Funktionen

- **Suche** – Branche + Stadt eingeben → echte Unternehmen via Google Places
- **Live-Scan** – jede gefundene Website wird automatisch gescannt (Score 0–100)
- **Dashboard** – Statistiken und letzte Scan-Ergebnisse
- **Leads** – interessante Unternehmen direkt als Lead speichern
- **Scan-Details** – SSL, Mobile, Impressum, Tracking, CMS, Ladezeit

## Deployment (Production)

Für lokale Entwicklung SQLite beibehalten, für Production MySQL verwenden.

### MySQL-Verbindung testen

Bevor du auf MySQL umstellst, teste die Verbindung:

```bash
php artisan db:check-mysql
```

Optionen für abweichende Credentials:
```bash
php artisan db:check-mysql --host=db.example.com --database=pagescan_prod --username=pagescan --password=secret
```

### .env für Production

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pagescan
DB_USERNAME=root
DB_PASSWORD=dein_passwort
```

### Vollständiges Deployment

```bash
composer install --no-dev --optimize-autoloader
npm run build
php artisan key:generate --force
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### MySQL-Kompatibilitäts-Hinweise

- Alle Migrations sind MySQL 8.0+ kompatibel
- `score_weights` (JSON) erfordert MySQL 5.7+, empfohlen MySQL 8.0
- `place_id unique` auf `companies`-Tabelle: MySQL erlaubt mehrere NULL-Werte in UNIQUE-Spalten (korrekt)
- `->after()` in ALTER-Migrations ist MySQL-spezifisch, wird von SQLite ignoriert (kein Problem)
- Zeichensatz: MySQL sollte mit `utf8mb4` und `utf8mb4_unicode_ci` konfiguriert sein (Standard in Laravel)

---

## OAuth-Login (GitHub & Google)

### GitHub
1. https://github.com/settings/applications/new
2. **Homepage URL:** `https://deine-domain.de`
3. **Callback URL:** `https://deine-domain.de/auth/github/callback`
4. Client ID + Secret in `.env` eintragen:
   ```
   GITHUB_CLIENT_ID=
   GITHUB_CLIENT_SECRET=
   GITHUB_REDIRECT_URI=https://deine-domain.de/auth/github/callback
   ```

### Google
1. https://console.cloud.google.com → APIs & Dienste → Anmeldedaten
2. OAuth 2.0-Client erstellen (Webanwendung)
3. **Autorisierter Weiterleitungs-URI:** `https://deine-domain.de/auth/google/callback`
4. Client ID + Secret in `.env` eintragen:
   ```
   GOOGLE_CLIENT_ID=
   GOOGLE_CLIENT_SECRET=
   GOOGLE_REDIRECT_URI=https://deine-domain.de/auth/google/callback
   ```

---

## Queue Worker (Pflicht für Scans)

Scans laufen asynchron via Queue Jobs. Ohne Worker werden keine Scans ausgeführt.

**Lokal (Development):**
```bash
php artisan queue:work --sleep=3 --tries=2
```

**Produktion (Supervisor empfohlen):**
```ini
[program:pagescan-worker]
command=php /var/www/pagescan/artisan queue:work --sleep=3 --tries=2 --max-time=3600
autostart=true
autorestart=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/pagescan/storage/logs/worker.log
```

---

## Cron-Jobs (Pflicht für Watchlist & E-Mails)

Folgendes Cron-Entry auf dem Server einrichten:
```
* * * * * cd /var/www/pagescan && php artisan schedule:run >> /dev/null 2>&1
```

Geplante Tasks:
- **täglich 06:00** – Watchlist-Suchen automatisch wiederholen
- **täglich 09:00** – Trial-Ablauf-Reminder senden (3 Tage vor Ende)
- **1. des Monats** – Scan-Kontingent aller User zurücksetzen

---

## Stripe

1. Stripe-Account anlegen: https://dashboard.stripe.com
2. Drei Preise anlegen (Starter €29, Agency €79, Pro €149 – monatlich wiederkehrend)
3. Price IDs in `.env` eintragen: `STRIPE_PRICE_STARTER`, `STRIPE_PRICE_AGENCY`, `STRIPE_PRICE_PRO`
4. Webhook in Stripe konfigurieren: Endpoint `https://deine-domain.de/stripe/webhook`
   - Events: `customer.subscription.created`, `customer.subscription.updated`, `customer.subscription.deleted`, `invoice.payment_failed`
5. `STRIPE_WEBHOOK_SECRET` aus Stripe in `.env` eintragen
