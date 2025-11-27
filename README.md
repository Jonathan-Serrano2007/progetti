**Nome e cognome**

Serranò Jonathan

_________________________________________________________________________________________________________________________________________________________________________

**Titolo**

Musicare

_________________________________________________________________________________________________________________________________________________________________________

**Tagline**

Raccogli gli strumenti necessari per Musicare come si deve!

_________________________________________________________________________________________________________________________________________________________________________

**Descrizione**

Sito che mira all'allenamento delle conoscenze musicali tramite esercizi di riconoscimento delle note sul pentagramma, dettati ritmici e melodici, esercizi di ear training ed esercizi ai fini di individuare le tonalità dei brani attraverso le alterazioni in chiave. Gli esercizi più semplici di ogni categoria saranno gratuiti mentre quelli più avanzati saranno a pagamento. Il piano pro (a pagamento) permetterà di salvare i progessi per visualizzare il proprio percorso attraverso grafici.

_________________________________________________________________________________________________________________________________________________________________________


**Descrizione completa**

1. Attori

Utente non registrato

* Non può accedere a nessuna funzionalità del sito.
* Non può visualizzare esercizi né categorie.
* Può unicamente accedere alla pagina di registrazione.
* Ogni tentativo di accedere a contenuti del sito lo reindirizza alla registrazione.

Utente base (registrato senza abbonamento)

* Può accedere tramite login.
* Può visualizzare le categorie di esercizi.
* Può svolgere solo esercizi base (1–5) di ogni categoria.
* Non può salvare progressi.

Utente Pro (registrato con abbonamento attivo)

* Può accedere tramite login.
* Può svolgere esercizi base e avanzati (1–10).
* Può salvare e visualizzare progressi tramite grafici.
* Può svolgere l’esercizio giornaliero personalizzato.

---

2. Casi d’Uso

UC1 – Registrazione

Attore: Utente non registrato
Descrizione: L’utente crea un account per accedere al sito.
Precondizione: Non essere già registrato.
Postcondizione: Diventa Utente base.
Nota: È l’unica azione consentita all’utente non registrato.

---

UC2 – Accesso (Login)

Attore: Utente base / Utente pro
Descrizione: L’utente accede al sistema tramite credenziali.
Precondizione: Essere registrato.
Postcondizione: L’utente acquisisce accesso ai contenuti relativi al proprio ruolo.

---

UC3 – Acquisto Abbonamento Pro

Attore: Utente base
Descrizione: L’utente acquista un abbonamento per sbloccare contenuti avanzati e progressi.
Precondizione: Login effettuato.
Postcondizione: L’utente diventa Utente Pro.

---

UC4 – Visualizzare Elenco Esercizi

Attore: Utente base / Utente pro
Descrizione: L’utente visualizza le categorie di esercizi:

* Dettato ritmico
* Ear training
* Riconoscimento delle note
* Tonalità

Ogni categoria contiene 10 esercizi:

* Esercizi 1–5 (base) → accessibili a tutti gli utenti registrati
* Esercizi 6–10 (avanzati) → accessibili solo all’utente Pro

Precondizione: Utente loggato.

---

UC5 – Svolgere Esercizio

Attore: Utente base / Utente pro
Descrizione: L’utente svolge un esercizio completo di:

* Timer
* Punteggio finale

Precondizioni:

* Utente base: solo esercizi 1–5
* Utente pro: esercizi 1–10

Postcondizione:

* Utente base: il risultato NON viene salvato
* Utente pro: il risultato viene salvato nei progressi

---

UC6 – Visualizzare Progressi

Attore: Utente pro
Descrizione: L’utente vede grafici e statistiche relative ai risultati conseguiti.
I progressi devono includere:

* Risultati esercizi base
* Risultati esercizi avanzati

---

UC7 – Esercizio Giornaliero Personalizzato

Attore: Utente pro
Descrizione: Il sistema genera automaticamente un esercizio quotidiano con difficoltà adeguata ai progressi dell’utente.
Postcondizione: L’esercizio viene salvato nei progressi con timer e punteggio.

---

3. Requisiti Funzionali

RF1 – Registrazione

* L’unica pagina accessibile all’utente non registrato è la registrazione.
* L’utente non registrato non può visualizzare esercizi o categorie.

RF2 – Login

* Sistema di autenticazione tramite email e password.

RF3 – Gestione ruoli

* Il sistema distingue automaticamente tra:
  utente non registrato → utente base → utente pro.

RF4 – Abbonamento

* L’utente base può acquistare un piano pro.
* L’abbonamento sblocca esercizi avanzati e progressi.

RF5 – Struttura degli esercizi

Per ogni categoria devono essere presenti 10 esercizi:

* 1–5 base (gratuiti)
* 6–10 avanzati (bloccati per l’utente base)

RF6 – Meccanica degli esercizi

Ogni esercizio deve avere:

* Timer
* Punteggio finale
* Valutazione automatica

RF7 – Svolgimento degli esercizi

* Utente base → 1–5
* Utente pro → 1–10
* Solo l’utente pro salva i risultati.

RF8 – Progressi

* L’utente pro visualizza grafici e statistiche.
* I progressi devono raccogliere dati da esercizi base e avanzati.

RF9 – Esercizio giornaliero personalizzato

* Disponibile solo per utenti pro.
* La difficoltà è determinata dall’andamento dei progressi.

---

4. Requisiti Non Funzionali (RNF)

RNF1 – Usabilità: interfaccia chiara e intuitiva.
RNF2 – Performance: caricamento rapido (<2 s).
RNF3 – Sicurezza: password criptate, transazioni sicure.
RNF4 – Compatibilità: desktop, tablet, mobile.
RNF5 – Scalabilità: supporto a numerosi utenti simultanei.

---

5. Definizioni delle categorie di esercizi

1. Dettato ritmico

Esercizio in cui l’utente ascolta un ritmo composto da battiti, figure musicali e pause, e deve riscriverlo correttamente. Allena il riconoscimento di durate, accenti e pattern ritmici.

---

2. Ear training

Esercizi dedicati allo sviluppo dell’orecchio musicale, tra cui:

* riconoscimento intervalli
* accordi
* scale
* progressioni armoniche
* altezze delle note
  Aiuta a capire ciò che si ascolta senza avere uno spartito.

---

3. Riconoscimento delle note (note identification)

L’esercizio mostra una nota sul pentagramma e l’utente deve identificarla (Do, Re, Mi… oppure C, D, E). Migliora la capacità di lettura a prima vista.

---

4. Tonalità (key signature)

L’utente deve identificare la tonalità osservando il numero di alterazioni (diesis o bemolli) in chiave. Serve a riconoscere rapidamente tonalità come Do maggiore, Sol maggiore, Fa maggiore, Re minore ecc.

_________________________________________________________________________________________________________________________________________________________________________


**Target**

musicisti, chiunque voglia rafforzare le proprie competenze musicali teoriche, armoniche e percettive.


_________________________________________________________________________________________________________________________________________________________________________


**Competitors**

musictheory.net - EarMaster - Teoria.com


_________________________________________________________________________________________________________________________________________________________________________

**Tecnologie**

Frontend: HTML, CSS, Bootstrap, Javascript
Backend: PHP
Database: MySQL

_________________________________________________________________________________________________________________________________________________________________________


**Link web app**

_________________________________________________________________________________________________________________________________________________________________________

**Link prototipo**

https://id-preview--e4224955-53a8-49d2-9b5c-b4be9dd144b6.lovable.app/?__lovable_token=eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoib1QwbjVHaUlDb1RDNURzaDBBRDBqQjUzUHREMyIsInByb2plY3RfaWQiOiJlNDIyNDk1NS01M2E4LTQ5ZDItOWI1Yy1iNGJlOWRkMTQ0YjYiLCJub25jZSI6IjBkM2RhMjZkODgxNDc3NmQ3MmJkN2VmZmQwMGMzNTNlIiwiaXNzIjoibG92YWJsZS1hcGkiLCJzdWIiOiJlNDIyNDk1NS01M2E4LTQ5ZDItOWI1Yy1iNGJlOWRkMTQ0YjYiLCJhdWQiOlsibG92YWJsZS1hcHAiXSwiZXhwIjoxNzY0ODcyMzQ0LCJuYmYiOjE3NjQyNjc1NDQsImlhdCI6MTc2NDI2NzU0NH0.QWEcBaKz7AO0wTxs7fGq0ZtJ1wnKRAebi85V000HXm4
