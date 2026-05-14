-- Multi-tenancy baseline migration for Musicare
-- Target: MySQL 8+

CREATE TABLE IF NOT EXISTS tenants (
    id_tenant VARCHAR(64) PRIMARY KEY,
    nome_tenant VARCHAR(120) NOT NULL,
    creato_il TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO tenants (id_tenant, nome_tenant)
VALUES ('public', 'Tenant pubblico')
ON DUPLICATE KEY UPDATE nome_tenant = VALUES(nome_tenant);

ALTER TABLE utenti
    ADD COLUMN id_tenant VARCHAR(64) NOT NULL DEFAULT 'public' AFTER id_utente,
    ADD INDEX idx_utenti_tenant (id_tenant),
    ADD UNIQUE INDEX uq_utenti_email_tenant (email, id_tenant);

ALTER TABLE esercizi
    ADD COLUMN id_tenant VARCHAR(64) NOT NULL DEFAULT 'public' AFTER id_esercizio,
    ADD INDEX idx_esercizi_tenant (id_tenant);

ALTER TABLE progressi
    ADD COLUMN id_tenant VARCHAR(64) NOT NULL DEFAULT 'public' AFTER id_progresso,
    ADD INDEX idx_progressi_tenant_utente (id_tenant, id_utente);

ALTER TABLE svolge
    ADD COLUMN id_tenant VARCHAR(64) NOT NULL DEFAULT 'public' AFTER id_svolgimento,
    ADD INDEX idx_svolge_tenant_utente (id_tenant, id_utente);

ALTER TABLE abbonamenti
    ADD COLUMN id_tenant VARCHAR(64) NOT NULL DEFAULT 'public' AFTER id_abbonamento,
    ADD INDEX idx_abbonamenti_tenant_utente (id_tenant, id_utente);

ALTER TABLE utenti
    ADD CONSTRAINT fk_utenti_tenant
        FOREIGN KEY (id_tenant) REFERENCES tenants(id_tenant)
        ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE esercizi
    ADD CONSTRAINT fk_esercizi_tenant
        FOREIGN KEY (id_tenant) REFERENCES tenants(id_tenant)
        ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE progressi
    ADD CONSTRAINT fk_progressi_tenant
        FOREIGN KEY (id_tenant) REFERENCES tenants(id_tenant)
        ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE svolge
    ADD CONSTRAINT fk_svolge_tenant
        FOREIGN KEY (id_tenant) REFERENCES tenants(id_tenant)
        ON UPDATE CASCADE ON DELETE RESTRICT;

ALTER TABLE abbonamenti
    ADD CONSTRAINT fk_abbonamenti_tenant
        FOREIGN KEY (id_tenant) REFERENCES tenants(id_tenant)
        ON UPDATE CASCADE ON DELETE RESTRICT;
