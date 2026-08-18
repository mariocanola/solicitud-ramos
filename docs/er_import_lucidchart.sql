-- =====================================================
-- Sistema de Gestión de Solicitudes de Ramos Florales
-- Diagrama ER - Importar en Lucidchart
-- Menú: Insert > Import Data > Import SQL
-- =====================================================

SET FOREIGN_KEY_CHECKS = 0;

-- -----------------------------------------------------
-- configuracion
-- Parámetros globales del sistema (nombre empresa,
-- cupo por defecto, tipo de periodo, etc.)
-- -----------------------------------------------------
CREATE TABLE configuracion (
    id          INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    clave       VARCHAR(50)  NOT NULL UNIQUE,
    valor       TEXT         NOT NULL,
    descripcion VARCHAR(255) NULL
);

-- -----------------------------------------------------
-- sedes
-- Sucursales o puntos de trabajo de la organización
-- -----------------------------------------------------
CREATE TABLE sedes (
    id         INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(100) NOT NULL UNIQUE,
    codigo     VARCHAR(20)  NOT NULL UNIQUE,
    direccion  VARCHAR(255) NULL,
    activo     TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- motivos_ramo
-- Catálogo de razones por las que se solicita un ramo
-- (cumpleaños, grado, nacimiento, etc.)
-- -----------------------------------------------------
CREATE TABLE motivos_ramo (
    id               INT         NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre           VARCHAR(100) NOT NULL UNIQUE,
    requiere_detalle TINYINT(1)  NOT NULL DEFAULT 0,
    orden            INT         NOT NULL DEFAULT 0,
    activo           TINYINT(1)  NOT NULL DEFAULT 1
);

-- -----------------------------------------------------
-- estados_solicitud
-- Ciclo de vida de una solicitud
-- (Pendiente, Aprobada, En preparación, Entregada, Cancelada)
-- -----------------------------------------------------
CREATE TABLE estados_solicitud (
    id     INT         NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    color  VARCHAR(7)  NULL,
    orden  INT         NOT NULL DEFAULT 0
);

-- -----------------------------------------------------
-- usuarios
-- Cuentas de acceso al panel de administración
-- Roles: admin / operador
-- -----------------------------------------------------
CREATE TABLE usuarios (
    id            INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username      VARCHAR(50)  NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre        VARCHAR(100) NOT NULL,
    rol           ENUM('admin','operador') NOT NULL,
    activo        TINYINT(1)   NOT NULL DEFAULT 1,
    ultimo_login  DATETIME     NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- -----------------------------------------------------
-- personas
-- Beneficiarios del programa de ramos (empleados)
-- Se identifican por tipo y número de documento
-- -----------------------------------------------------
CREATE TABLE personas (
    id               INT         NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tipo_documento   ENUM('CC','CE','TI','PA','NIT','PT') NOT NULL,
    documento        VARCHAR(20) NOT NULL UNIQUE,
    primer_nombre    VARCHAR(50) NOT NULL,
    segundo_nombre   VARCHAR(50) NULL,
    primer_apellido  VARCHAR(50) NOT NULL,
    segundo_apellido VARCHAR(50) NULL,
    telefono         VARCHAR(20) NULL,
    id_sede          INT         NOT NULL,
    empresa          ENUM('TANDIL','CREOS') NOT NULL DEFAULT 'TANDIL',
    activo           TINYINT(1)  NOT NULL DEFAULT 1,
    created_at       DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at       DATETIME    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_personas_sede FOREIGN KEY (id_sede) REFERENCES sedes(id)
);

-- -----------------------------------------------------
-- cupos_sede
-- Límite de solicitudes permitidas por sede y periodo
-- El cupo_usado se calcula en tiempo real desde solicitudes
-- -----------------------------------------------------
CREATE TABLE cupos_sede (
    id          INT       NOT NULL AUTO_INCREMENT PRIMARY KEY,
    id_sede     INT       NOT NULL,
    periodo     DATE      NOT NULL COMMENT 'Primer día del mes o lunes de la semana',
    cupo_maximo INT       NOT NULL,
    notificado  TINYINT(1) NOT NULL DEFAULT 0,
    created_at  DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME  NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_cupos_sede_periodo (id_sede, periodo),
    CONSTRAINT fk_cupos_sede FOREIGN KEY (id_sede) REFERENCES sedes(id)
);

-- -----------------------------------------------------
-- solicitudes
-- Registro principal: cada fila es una solicitud de ramo
-- hecha por un empleado en el panel de autoatención
-- -----------------------------------------------------
CREATE TABLE solicitudes (
    id                  INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    persona_id          INT          NOT NULL,
    fecha_solicitud     DATE         NOT NULL,
    id_sede             INT          NOT NULL,
    nombre_destinatario VARCHAR(150) NOT NULL,
    id_motivo           INT          NOT NULL,
    motivo_otro         VARCHAR(200) NULL,
    observaciones       TEXT         NULL,
    id_estado           INT          NOT NULL DEFAULT 1,
    created_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at          DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_solicitudes_persona FOREIGN KEY (persona_id) REFERENCES personas(id),
    CONSTRAINT fk_solicitudes_sede    FOREIGN KEY (id_sede)    REFERENCES sedes(id),
    CONSTRAINT fk_solicitudes_motivo  FOREIGN KEY (id_motivo)  REFERENCES motivos_ramo(id),
    CONSTRAINT fk_solicitudes_estado  FOREIGN KEY (id_estado)  REFERENCES estados_solicitud(id)
);

-- -----------------------------------------------------
-- log_correos
-- Auditoría de notificaciones por correo enviadas
-- por el sistema (reportes automáticos al área de elaboración)
-- -----------------------------------------------------
CREATE TABLE log_correos (
    id            INT          NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tipo          ENUM('manual','automatico') NOT NULL,
    destinatario  VARCHAR(255) NOT NULL,
    asunto        VARCHAR(255) NOT NULL,
    estado        ENUM('enviado','fallido') NOT NULL,
    error_detalle TEXT         NULL,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
);

SET FOREIGN_KEY_CHECKS = 1;
