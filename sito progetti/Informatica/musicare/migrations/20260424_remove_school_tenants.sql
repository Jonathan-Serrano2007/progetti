-- Rimozione tenant scolastici non piu' usati
-- Eseguire prima dei rollback o della rigenerazione del database se si vogliono pulire i dati demo.

DELETE FROM progressi WHERE id_tenant IN ('scuola_a', 'scuola_b');
DELETE FROM svolge WHERE id_tenant IN ('scuola_a', 'scuola_b');
DELETE FROM abbonamenti WHERE id_tenant IN ('scuola_a', 'scuola_b');
DELETE FROM esercizi WHERE id_tenant IN ('scuola_a', 'scuola_b');
DELETE FROM utenti WHERE id_tenant IN ('scuola_a', 'scuola_b');
DELETE FROM tenants WHERE id_tenant IN ('scuola_a', 'scuola_b');