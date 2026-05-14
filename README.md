# Progetto: Musicare (repository progetti)

Questo repository contiene piu moduli scolastici, con focus principale sulla web app **Musicare**:

- autenticazione utente (sessione PHP e API JWT)
- gestione ruoli/privilegi
- esercizi musicali (base/pro)
- area statistiche e dashboard

## Struttura repository

- `sito progetti/Informatica/musicare/`: applicazione principale Musicare
- `sito progetti/Informatica/musicare/API/`: endpoint JWT (auth, refresh, permissions)
- `sito progetti/Informatica/musicare/migrations/`: migrazioni SQL
- `install.sh`: setup locale Apache + PHP + MariaDB + phpMyAdmin
- `avvia.sh`: avvio rapido servizi locali

## Requisiti

- Linux (testato in ambiente Ubuntu)
- Apache2
- PHP con PDO MySQL
- MariaDB/MySQL
- Composer (dipendenza: `firebase/php-jwt`)

## Setup rapido (locale)

1. Rendi eseguibili gli script:

```bash
chmod +x install.sh avvia.sh
```

2. Esegui installazione stack locale:

```bash
./install.sh
```

3. Avvia i servizi:

```bash
./avvia.sh
```

4. Installa dipendenze PHP (se necessario):

```bash
composer install
```

## Database

### Configurazione connessione applicazione

Il file `sito progetti/Informatica/musicare/database.php` usa al momento:

- host: `127.0.0.1`
- db: `my_serranojonathan`
- user: `utente_phpmyadmin`

Aggiorna questi valori in base al tuo ambiente prima di usare l'applicazione in produzione.

### Migrazione ultima modifica (refresh token)

Per allineare il DB alle ultime modifiche JWT, applica la migrazione:

```sql
ALTER TABLE utenti
ADD COLUMN refresh_token TEXT NULL AFTER password;
```

Script disponibile in:

- `sito progetti/Informatica/musicare/migrations/20260220_add_refresh_token_to_utenti.sql`

## JWT e sicurezza

La configurazione JWT e gestita in `sito progetti/Informatica/musicare/API/jwt_config.php`.

- variabile supportata: `JWT_SECRET`
- fallback locale se non impostata: secret di sviluppo
- lunghezza minima secret: 32 caratteri

Esempio di export variabile ambiente:

```bash
export JWT_SECRET='inserisci-qui-una-chiave-lunga-almeno-32-caratteri'
```

## API Musicare (stato attuale)

Base path locale tipico:

```text
/sito%20progetti/Informatica/musicare/API
```

### 1) Login e generazione token

- endpoint: `POST /auth_api.php`
- body JSON: `email`, `password`
- risposta: `access_token` (10 min) + `refresh_token` (7 giorni)

Esempio:

```bash
curl -X POST http://localhost/sito%20progetti/Informatica/musicare/API/auth_api.php \
  -H 'Content-Type: application/json' \
  -d '{"email":"utente@example.com","password":"password"}'
```

### 2) Refresh token

- endpoint: `POST /refresh_api.php`
- body JSON: `refresh_token`
- comportamento: ruota il refresh token e restituisce nuovi token

Esempio:

```bash
curl -X POST http://localhost/sito%20progetti/Informatica/musicare/API/refresh_api.php \
  -H 'Content-Type: application/json' \
  -d '{"refresh_token":"<refresh_token>"}'
```

### 3) Permessi utente

- endpoint: `GET|POST /permissions_api.php`
- token via header `Authorization: Bearer <token>`
- fallback supportati: query string `?token=...` o body JSON `{ "token": "..." }`

Esempio:

```bash
curl -X POST http://localhost/sito%20progetti/Informatica/musicare/API/permissions_api.php \
  -H 'Content-Type: application/json' \
  -H 'Authorization: Bearer <access_token>' \
  -d '{}'
```

## Pagine di test API

- `sito progetti/Informatica/musicare/API/test.html`
- `sito progetti/Informatica/musicare/API/debug_tokens.html`

Nota: in `test.html` gli endpoint sono referenziati in modo relativo (`./auth_api.php`, `./refresh_api.php`, `./permissions_api.php`) per compatibilita tra ambienti.

## Funzionalita principali Musicare

- registrazione/login utenti
- distinzione tra utente base e pro
- dashboard e statistiche
- esercizi musicali per categorie
- API JWT per integrazione frontend/backend

## Avvio applicazione

Dopo aver avviato Apache e MariaDB, apri la root del progetto web nel browser in base alla tua configurazione Apache. Per phpMyAdmin, lo script di installazione configura l'alias:

```text
http://localhost/phpmyadmin
```

## Note importanti

- Il repository contiene credenziali e impostazioni pensate per ambiente didattico/locale.
- Prima di deploy pubblico: ruota password, imposta `JWT_SECRET` robusta, limita privilegi DB e aggiorna CORS.

## Autore

Jonathan Serrano
