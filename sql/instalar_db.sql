-- ============================================================
-- Instalador unico de base de datos para el Sistema de Solicitud
-- de Ramos Florales. Crea la BD, todas las tablas, datos iniciales
-- (sedes, motivos, estados, usuarios) y aplica todas las migraciones.
--
-- Uso (desde la consola de MySQL o phpMyAdmin):
--   mysql -u root < sql/instalar_db.sql
-- ============================================================

CREATE DATABASE IF NOT EXISTS flores_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE flores_db;

-- Estructura base (incluye ya PT en tipo_documento y empresa en personas
-- porque schema.sql se mantiene actualizado).
SOURCE schema.sql;

-- Datos iniciales (sedes Tandil/Primavera, motivos, estados, usuario admin).
SOURCE seeds.sql;

-- Idempotente: si ya estan, no hace nada.
SOURCE migracion_pt_documento.sql;
SOURCE migracion_empresa_personas.sql;
SOURCE migracion_config_hoja_individual.sql;
SOURCE migracion_nuevos_motivos.sql;
SOURCE fix_cupo_usado_sync.sql;
