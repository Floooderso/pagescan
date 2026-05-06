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

## Hinweis

Kein Queue Worker nötig – Scans laufen synchron (QUEUE_CONNECTION=sync).
