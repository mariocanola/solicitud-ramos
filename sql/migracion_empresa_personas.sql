-- Migración: agregar columna 'empresa' a personas para distinguir personal directo
-- de Tandil vs personal contratado por la empresa externa CREOS.
-- Aplicar en BD ya existente. Idempotente.

ALTER TABLE personas
    ADD COLUMN IF NOT EXISTS empresa ENUM('TANDIL','CREOS') NOT NULL DEFAULT 'TANDIL'
    AFTER id_sede;
