# Documentazione sviluppatore - Musicare

## 1. Obiettivo del progetto

Musicare e una web app PHP pensata per l'allenamento musicale e la gestione di utenti con livelli di accesso diversi. Il progetto combina:

- autenticazione con sessione PHP
- autenticazione API con JWT e refresh token
- gestione tenant per separare i dati per contesto
- ruoli e privilegi per limitare le aree accessibili
- esercizi musicali base e avanzati
- dashboard utente, statistiche e pannello admin

Questa documentazione serve come riferimento rapido per sviluppare, manutenere ed estendere il sito.

## 2. Struttura del progetto

Il modulo principale si trova nella cartella corrente del progetto Musicare.

### File e cartelle principali

- [index.php](../index.php): landing page pubblica di Musicare
- [login.php](../login.php): login con sessione PHP e tenant
- [register.php](../register.php): registrazione utente con inizializzazione progressi
- [dashboard.php](../dashboard.php): area principale dopo l'accesso
- [esercizi_base.php](../esercizi_base.php): esercizi accessibili agli utenti registrati
- [esercizi_pro.php](../esercizi_pro.php): esercizi riservati a Pro e Admin
- [esercizio.php](../esercizio.php): pagina di dettaglio e svolgimento di un esercizio
- [statistiche.php](../statistiche.php): statistiche personali per Pro e Admin
- [admin_users.php](../admin_users.php): gestione utenti per Admin
- [admin_exercises.php](../admin_exercises.php): gestione esercizi per Admin
- [admin_statistics.php](../admin_statistics.php): statistiche globali per Admin
- [check_privileges.php](../check_privileges.php): helper per login, ruoli e privilegi
- [database.php](../database.php): connessione PDO al database
- [tenant_context.php](../tenant_context.php): gestione tenant richiesto o corrente
- [musicare-theme.css](../musicare-theme.css): stile condiviso della UI
- [API/auth_api.php](../API/auth_api.php): login API e generazione JWT
- [API/refresh_api.php](../API/refresh_api.php): rinnovo access token e refresh token
- [API/permissions_api.php](../API/permissions_api.php): lettura dati utente, ruolo e privilegi
- [API/jwt_config.php](../API/jwt_config.php): configurazione della chiave JWT
- [API/test.html](../API/test.html): pagina di test API con endpoint relativi
- [API/debug_tokens.html](../API/debug_tokens.html): pagina di debug token
- [migrations/20260220_add_refresh_token_to_utenti.sql](../migrations/20260220_add_refresh_token_to_utenti.sql): aggiunge il campo refresh_token
- [migrations/20260410_add_multi_tenancy.sql](../migrations/20260410_add_multi_tenancy.sql): base multi-tenant

## 3. Requisiti tecnici

- Linux o ambiente compatibile con Apache, PHP e MariaDB/MySQL
- PHP con estensione PDO MySQL
- Composer per la libreria `firebase/php-jwt`
- Apache attivo e configurato per servire la cartella del progetto

## 4. Avvio locale

Lo stack locale e gestito dagli script alla radice del repository:

- [install.sh](../../../../install.sh): installazione componenti e configurazione iniziale
- [avvia.sh](../../../../avvia.sh): avvio rapido dei servizi locali

Flusso tipico:

1. rendere eseguibili gli script
2. eseguire l'installazione dello stack
3. avviare Apache e MariaDB
4. installare le dipendenze Composer se necessario

## 5. Configurazione database

La connessione al DB e definita in [database.php](../database.php).

### Nota operativa

Il file usa parametri locali hardcoded. In un ambiente diverso da quello di sviluppo vanno aggiornati host, nome database e credenziali.

### Tabelle usate dal modulo Musicare

Le query presenti nel codice fanno riferimento almeno a queste tabelle:

- `tenants`
- `utenti`
- `ruoli`
- `privilegi`
- `ruolo_privilegi`
- `esercizi`
- `progressi`
- `svolge`
- `abbonamenti`

### Migrazioni rilevanti

- la migrazione multi-tenant aggiunge `id_tenant` alle tabelle operative e crea il tenant `public`
- la migrazione refresh token aggiunge `refresh_token` alla tabella `utenti`

## 6. Modello tenant

La gestione tenant e centralizzata in [tenant_context.php](../tenant_context.php).

### Comportamento

- il tenant puo arrivare da header `X-Tenant-Id`
- in alternativa puo arrivare da query string `tenant`
- in alternativa puo arrivare dal POST `tenant`
- se esiste una sessione valida, il tenant di sessione ha priorita
- se non e presente nulla, viene usato il tenant di default

### Regole di validazione

Il tenant e considerato valido solo se rispetta il pattern alfanumerico con trattino o underscore, fino a 64 caratteri.

### Nota di sviluppo

Il tenant di default puo essere impostato tramite variabile ambiente `MUSICARE_DEFAULT_TENANT`; in assenza di valore il fallback e `public`.

## 7. Autenticazione e sessioni

### Login web

Il login classico e gestito in [login.php](../login.php).

Flusso:

1. l'utente inserisce email, password e tenant
2. la query cerca l'utente nel tenant corrente
3. la password viene verificata con `password_verify`
4. in caso di esito positivo vengono salvati in sessione `utente_id`, `utente_nome`, `utente_ruolo` e `tenant_id`
5. l'utente viene inviato alla dashboard o alla pagina richiesta prima del login

### Registrazione

La registrazione e gestita in [register.php](../register.php).

Comportamento principale:

- l'utente standard viene creato nel tenant di default
- solo un Admin puo scegliere o creare un tenant custom
- dopo l'inserimento dell'utente viene creato anche il record iniziale in `progressi`
- le operazioni sono racchiuse in transazione

### Helper di accesso

Il file [check_privileges.php](../check_privileges.php) contiene funzioni usate in piu pagine:

- `require_login()` per bloccare gli anonimi
- `require_privilege()` per bloccare l'accesso ai privilegi mancanti
- `is_pro()` e `is_admin()` per controlli rapidi sul ruolo
- `get_user_privileges()` per recuperare i privilegi associati al ruolo corrente

## 8. Ruoli e privilegi

Il sistema usa una struttura a ruoli e privilegi:

- `utenti.id_ruolo` collega l'utente al ruolo
- `ruoli` contiene i ruoli disponibili
- `privilegi` contiene i permessi atomici
- `ruolo_privilegi` associa ruoli e permessi

### Ruoli usati nel codice

- `registrato`
- `pro`
- `admin`

### Regole operative osservate nel codice

- gli utenti registrati accedono alla dashboard e agli esercizi base
- gli utenti Pro accedono anche a statistiche e esercizi avanzati
- gli Admin hanno accesso alle sezioni di gestione utenti, esercizi e statistiche globali

### Nota importante per la manutenzione

Alcuni controlli admin nel codice verificano ancora `id_ruolo = 3`. In caso di modifica della tabella ruoli, e opportuno allineare questi controlli con un lookup per nome ruolo o con costanti applicative.

## 9. Flusso applicativo principale

### Homepage pubblica

[index.php](../index.php) e la landing page con CTA verso login e registrazione. Gestisce anche il logout dalla home se viene passato il parametro appropriato.

### Dashboard utente

[dashboard.php](../dashboard.php) legge i dati dell'utente e i privilegi del ruolo per mostrare contenuti contestualizzati.

### Esercizi base

[esercizi_base.php](../esercizi_base.php) elenca gli esercizi di tipo `base` e mostra gli ultimi progressi dell'utente.

### Esercizi Pro

[esercizi_pro.php](../esercizi_pro.php) mostra gli esercizi di tipo `avanzato` e richiede un ruolo Pro o Admin.

### Dettaglio esercizio

[esercizio.php](../esercizio.php) gestisce lo svolgimento dell'esercizio, il salvataggio dei progressi e alcune logiche specifiche per categorie come il riconoscimento delle note.

### Statistiche personali

[statistiche.php](../statistiche.php) elabora media punti, tempo medio e storico svolgimenti per utenti Pro e Admin.

## 10. Flusso admin

### Gestione utenti

[admin_users.php](../admin_users.php) consente di visualizzare gli utenti del tenant e di cambiare il ruolo assegnato.

### Gestione esercizi

[admin_exercises.php](../admin_exercises.php) consente di creare nuovi esercizi con categoria, difficolta, tempo disponibile e tipo.

### Statistiche globali

[admin_statistics.php](../admin_statistics.php) calcola metriche aggregate del tenant, tra cui utenti totali, esercizi, svolgimenti, media punti e classifiche.

## 11. API JWT

Le API si trovano in [API/](../API).

### Autenticazione

[API/auth_api.php](../API/auth_api.php) accetta `POST` JSON con `email` e `password`.

Risposta:

- `access_token` valido 10 minuti
- `refresh_token` valido 7 giorni
- dati utente essenziali

Il refresh token viene salvato in `utenti.refresh_token` per poterlo invalidare o ruotare.

### Refresh token

[API/refresh_api.php](../API/refresh_api.php) valida il refresh token, verifica tenant e corrispondenza con il token salvato a database, poi genera una nuova coppia di token.

### Permessi utente

[API/permissions_api.php](../API/permissions_api.php) decodifica il Bearer token e restituisce:

- dati utente
- tenant corrente
- ruolo
- lista privilegi

### Configurazione JWT

[API/jwt_config.php](../API/jwt_config.php) legge `JWT_SECRET` dall'ambiente e usa un fallback locale solo se il secret ha almeno 32 caratteri.

## 12. Test e debug API

### Test HTML

[API/test.html](../API/test.html) e una pagina di test interattiva per login, refresh e permessi.

Nota importante: gli endpoint sono referenziati con path relativi come `./auth_api.php`, `./refresh_api.php` e `./permissions_api.php` per mantenere la compatibilita tra ambienti diversi.

### Debug tokens

[API/debug_tokens.html](../API/debug_tokens.html) e una pagina semplice per fare login, copiare token, rinnovarli e verificare i permessi.

## 13. Salvataggio progressi esercizi

La pagina [esercizio.php](../esercizio.php) espone anche una chiamata POST AJAX per salvare il progresso.

Comportamento osservato:

- richiede una sessione utente valida
- registra uno svolgimento nella tabella `svolge`
- aggiorna o crea il record in `progressi`
- restituisce JSON con esito, media punti e tempo medio

## 14. Aspetti UI

L'interfaccia usa una combinazione di:

- Bootstrap 5
- Bootstrap Icons
- Google Fonts Poppins
- CSS custom nel file condiviso `musicare-theme.css` e negli stili inline delle singole pagine

La maggior parte delle pagine adotta uno stile a card con gradienti e sfondi scuri o sfumati.

## 15. Convenzioni di sviluppo

### Query e sicurezza

- usare sempre query preparate con PDO
- validare tenant, id utente e input testuali prima delle query
- non fidarsi dei dati in sessione senza verificare il tenant sul database
- usare `password_hash` e `password_verify`

### Controlli di accesso

- bloccare gli accessi non autenticati prima di qualsiasi query sensibile
- verificare il tenant su ogni query importante
- usare i helper di `check_privileges.php` per evitare duplicazione

### API

- rispondere in JSON con codici HTTP coerenti
- supportare `OPTIONS` per CORS dove necessario
- mantenere compatibilita con ambienti in cui l'header Authorization puo essere filtrato

## 16. Note di manutenzione

- il modulo contiene ancora alcuni redirect e riferimenti legacy che vanno controllati quando si rinominano pagine o rotte
- i controlli admin basati su id numerico sono fragili e andrebbero idealmente sostituiti con controlli sul nome ruolo
- la configurazione database locale non dovrebbe essere lasciata cosi in ambiente pubblico
- prima di pubblicare il sito, impostare un `JWT_SECRET` robusto e ruotare le credenziali

## 17. Checklist per nuove funzionalita

Quando aggiungi una nuova pagina o una nuova API, verifica sempre:

1. autenticazione richiesta o accesso pubblico
2. tenant corretto in tutte le query
3. ruolo o privilegio richiesto
4. validazione input lato server
5. risposta coerente se la pagina e una API
6. allineamento con il layout esistente e con `musicare-theme.css`

## 18. Riepilogo rapido

Musicare e una piattaforma didattica musicale multi-tenant con tre livelli principali di accesso: registrato, Pro e Admin. La parte web usa sessioni PHP, la parte API usa JWT con refresh token, e tutte le query importanti devono rispettare il tenant corrente.

Per sviluppare correttamente sul progetto, i riferimenti piu importanti sono [database.php](../database.php), [tenant_context.php](../tenant_context.php), [check_privileges.php](../check_privileges.php) e i file in [API/](../API).
