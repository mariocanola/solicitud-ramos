-- Migración: agregar 'PT' (Permiso de Proteccion Temporal) al ENUM tipo_documento
-- Aplicar en BD ya existente. Idempotente (no falla si ya esta aplicado).

ALTER TABLE personas
    MODIFY COLUMN tipo_documento ENUM('CC','CE','TI','PA','NIT','PT') NOT NULL;
