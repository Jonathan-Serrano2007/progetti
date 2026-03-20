ALTER TABLE utenti
ADD COLUMN refresh_token TEXT NULL AFTER password;
