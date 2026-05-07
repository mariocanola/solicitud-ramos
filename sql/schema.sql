-- =====================================================
-- Sistema de Gestion de Solicitudes de Ramos Florales
-- Schema DDL - MySQL
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS flores_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE flores_db;

-- -----------------------------------------------------
-- Tabla: sedes
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS sedes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(20) NOT NULL,
    direccion VARCHAR(255) NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_sedes_nombre (nombre),
    UNIQUE KEY uk_sedes_codigo (codigo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: personas
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS personas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tipo_documento ENUM('CC','CE','TI','PA','NIT','PT') NOT NULL,
    documento VARCHAR(20) NOT NULL,
    primer_nombre VARCHAR(50) NOT NULL,
    segundo_nombre VARCHAR(50) NULL,
    primer_apellido VARCHAR(50) NOT NULL,
    segundo_apellido VARCHAR(50) NULL,
    telefono VARCHAR(20) NULL,
    id_sede INT NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_personas_documento (documento),
    INDEX idx_personas_sede (id_sede),
    INDEX idx_personas_nombre (primer_nombre, primer_apellido),
    CONSTRAINT fk_personas_sede FOREIGN KEY (id_sede)
        REFERENCES sedes(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: motivos_ramo
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS motivos_ramo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    requiere_detalle TINYINT(1) NOT NULL DEFAULT 0,
    orden INT NOT NULL DEFAULT 0,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uk_motivos_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: estados_solicitud
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS estados_solicitud (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    color VARCHAR(7) NULL,
    orden INT NOT NULL DEFAULT 0,
    UNIQUE KEY uk_estados_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: solicitudes
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS solicitudes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    persona_id INT NOT NULL,
    fecha_solicitud DATE NOT NULL,
    id_sede INT NOT NULL,
    nombre_destinatario VARCHAR(150) NOT NULL,
    id_motivo INT NOT NULL,
    motivo_otro VARCHAR(200) NULL,
    observaciones TEXT NULL,
    id_estado INT NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_solicitudes_fecha (fecha_solicitud),
    INDEX idx_solicitudes_sede (id_sede),
    INDEX idx_solicitudes_estado (id_estado),
    INDEX idx_solicitudes_persona (persona_id),
    INDEX idx_solicitudes_sede_fecha (id_sede, fecha_solicitud),
    CONSTRAINT fk_solicitudes_persona FOREIGN KEY (persona_id)
        REFERENCES personas(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_solicitudes_sede FOREIGN KEY (id_sede)
        REFERENCES sedes(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_solicitudes_motivo FOREIGN KEY (id_motivo)
        REFERENCES motivos_ramo(id) ON UPDATE CASCADE ON DELETE RESTRICT,
    CONSTRAINT fk_solicitudes_estado FOREIGN KEY (id_estado)
        REFERENCES estados_solicitud(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: cupos_sede
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS cupos_sede (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_sede INT NOT NULL,
    periodo DATE NOT NULL COMMENT 'Primer dia del mes',
    cupo_maximo INT NOT NULL,
    cupo_usado INT NOT NULL DEFAULT 0,
    notificado TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cupos_sede_periodo (id_sede, periodo),
    CONSTRAINT fk_cupos_sede FOREIGN KEY (id_sede)
        REFERENCES sedes(id) ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: configuracion
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(50) NOT NULL,
    valor TEXT NOT NULL,
    descripcion VARCHAR(255) NULL,
    UNIQUE KEY uk_config_clave (clave)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------------------------------
-- Tabla: usuarios (autenticacion del sistema)
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    rol ENUM('admin','operador') NOT NULL,
    activo TINYINT(1) NOT NULL DEFAULT 1,
    ultimo_login DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_usuarios_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

SET FOREIGN_KEY_CHECKS = 1;
