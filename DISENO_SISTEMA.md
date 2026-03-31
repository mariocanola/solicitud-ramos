# Sistema de Gestion de Solicitudes de Ramos Florales
## Documento de Diseno Arquitectonico v1.0

---

## 1. DISENO DE BASE DE DATOS

### 1.1 Diagrama Entidad-Relacion (Tablas + Relaciones)

```
sedes (1) ──────────< (N) personas
sedes (1) ──────────< (N) solicitudes
sedes (1) ──────────< (N) cupos_sede
personas (1) ───────< (N) solicitudes
motivos_ramo (1) ───< (N) solicitudes
estados_solicitud (1) < (N) solicitudes
```

### 1.2 Definicion de Tablas

#### TABLA: `sedes`
| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | INT AUTO_INCREMENT | PK |
| nombre | VARCHAR(100) | NOT NULL, UNIQUE |
| codigo | VARCHAR(20) | NOT NULL, UNIQUE |
| direccion | VARCHAR(255) | NULL |
| activo | TINYINT(1) | DEFAULT 1 |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| updated_at | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

#### TABLA: `personas`
| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | INT AUTO_INCREMENT | PK |
| tipo_documento | ENUM('CC','CE','TI','PA','NIT') | NOT NULL |
| documento | VARCHAR(20) | NOT NULL, UNIQUE |
| primer_nombre | VARCHAR(50) | NOT NULL |
| segundo_nombre | VARCHAR(50) | NULL |
| primer_apellido | VARCHAR(50) | NOT NULL |
| segundo_apellido | VARCHAR(50) | NULL |
| telefono | VARCHAR(20) | NULL |
| id_sede | INT | FK -> sedes(id), NOT NULL |
| activo | TINYINT(1) | DEFAULT 1 |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| updated_at | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

**Indices:**
- UNIQUE INDEX `idx_documento` ON (`documento`)
- INDEX `idx_sede` ON (`id_sede`)
- INDEX `idx_nombre` ON (`primer_nombre`, `primer_apellido`)

#### TABLA: `motivos_ramo`
| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | INT AUTO_INCREMENT | PK |
| nombre | VARCHAR(100) | NOT NULL, UNIQUE |
| requiere_detalle | TINYINT(1) | DEFAULT 0 |
| orden | INT | DEFAULT 0 |
| activo | TINYINT(1) | DEFAULT 1 |

**Datos iniciales:**
- Cumpleanos
- Condolencias
- Nacimiento
- Recuperacion
- Aniversario
- Otro (requiere_detalle = 1)

#### TABLA: `estados_solicitud`
| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | INT AUTO_INCREMENT | PK |
| nombre | VARCHAR(50) | NOT NULL, UNIQUE |
| color | VARCHAR(7) | NULL (hex para UI) |
| orden | INT | DEFAULT 0 |

**Datos iniciales:**
- Pendiente (#FFA500)
- Aprobada (#28A745)
- Rechazada (#DC3545)
- Entregada (#007BFF)
- Cancelada (#6C757D)

#### TABLA: `solicitudes`
| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | INT AUTO_INCREMENT | PK |
| persona_id | INT | FK -> personas(id), NOT NULL |
| fecha_solicitud | DATE | NOT NULL |
| id_sede | INT | FK -> sedes(id), NOT NULL |
| nombre_destinatario | VARCHAR(150) | NOT NULL |
| id_motivo | INT | FK -> motivos_ramo(id), NOT NULL |
| motivo_otro | VARCHAR(200) | NULL |
| observaciones | TEXT | NULL |
| id_estado | INT | FK -> estados_solicitud(id), DEFAULT 1 |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| updated_at | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

**Indices:**
- INDEX `idx_fecha` ON (`fecha_solicitud`)
- INDEX `idx_sede` ON (`id_sede`)
- INDEX `idx_estado` ON (`id_estado`)
- INDEX `idx_persona` ON (`persona_id`)
- COMPOSITE INDEX `idx_sede_fecha` ON (`id_sede`, `fecha_solicitud`)

#### TABLA: `cupos_sede`
| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | INT AUTO_INCREMENT | PK |
| id_sede | INT | FK -> sedes(id), NOT NULL |
| periodo | DATE | NOT NULL (primer dia del periodo) |
| cupo_maximo | INT | NOT NULL |
| cupo_usado | INT | DEFAULT 0 |
| notificado | TINYINT(1) | DEFAULT 0 |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |
| updated_at | DATETIME | ON UPDATE CURRENT_TIMESTAMP |

**Indices:**
- UNIQUE INDEX `idx_sede_periodo` ON (`id_sede`, `periodo`)

> `periodo` almacena el primer dia del mes. Permite control mensual de cupos.
> `notificado` evita enviar multiples correos al alcanzar el limite.

#### TABLA: `configuracion`
| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | INT AUTO_INCREMENT | PK |
| clave | VARCHAR(50) | NOT NULL, UNIQUE |
| valor | TEXT | NOT NULL |
| descripcion | VARCHAR(255) | NULL |

**Datos iniciales:**
- `correo_destino` -> email del destinatario de reportes
- `correo_cc` -> emails en copia
- `cupo_default` -> cupo por defecto para sedes nuevas
- `smtp_host`, `smtp_port`, `smtp_user`, `smtp_pass` -> config SMTP
- `nombre_organizacion` -> nombre para encabezado PDF
- `logo_path` -> ruta del logo para PDF

#### TABLA: `log_correos`
| Campo | Tipo | Restricciones |
|-------|------|---------------|
| id | INT AUTO_INCREMENT | PK |
| tipo | ENUM('manual','automatico') | NOT NULL |
| destinatario | VARCHAR(255) | NOT NULL |
| asunto | VARCHAR(255) | NOT NULL |
| estado | ENUM('enviado','fallido') | NOT NULL |
| error_detalle | TEXT | NULL |
| created_at | DATETIME | DEFAULT CURRENT_TIMESTAMP |

---

## 2. ESTRUCTURA DE CARPETAS DEL PROYECTO

```
flores/
|
|-- /app
|   |-- /config
|   |   |-- database.php          # Conexion PDO a MySQL
|   |   |-- app.php               # Constantes globales, rutas base
|   |   |-- mail.php              # Configuracion SMTP
|   |
|   |-- /controllers
|   |   |-- SolicitudController.php
|   |   |-- PersonaController.php
|   |   |-- SedeController.php
|   |   |-- ReporteController.php
|   |   |-- DashboardController.php
|   |   |-- CupoController.php
|   |   |-- ConfigController.php
|   |
|   |-- /models
|   |   |-- Database.php          # Singleton PDO wrapper
|   |   |-- Solicitud.php
|   |   |-- Persona.php
|   |   |-- Sede.php
|   |   |-- MotivoRamo.php
|   |   |-- AreaSolicitud.php
|   |   |-- EstadoSolicitud.php
|   |   |-- CupoSede.php
|   |   |-- Configuracion.php
|   |   |-- LogCorreo.php
|   |
|   |-- /services
|   |   |-- SolicitudService.php   # Logica de negocio solicitudes
|   |   |-- PersonaService.php     # Busqueda, creacion, parseo cedula
|   |   |-- CupoService.php        # Validacion y control de cupos
|   |   |-- PdfService.php         # Generacion PDF consolidado
|   |   |-- MailService.php        # Envio de correos con PHPMailer
|   |   |-- ReporteService.php     # Consultas agregadas para reportes
|   |
|   |-- /helpers
|   |   |-- Validator.php          # Validaciones genericas
|   |   |-- Response.php           # Respuestas JSON estandarizadas
|   |   |-- Session.php            # Manejo de sesion
|   |   |-- DateHelper.php         # Utilidades de fecha
|   |   |-- BarcodeParser.php      # Parseo de cadenas de codigo de barras
|   |
|   |-- /views
|   |   |-- /layouts
|   |   |   |-- main.php           # Layout principal (head, nav, footer)
|   |   |   |-- header.php
|   |   |   |-- footer.php
|   |   |   |-- sidebar.php
|   |   |
|   |   |-- /solicitudes
|   |   |   |-- crear.php
|   |   |   |-- listar.php
|   |   |   |-- detalle.php
|   |   |
|   |   |-- /personas
|   |   |   |-- crear_modal.php
|   |   |   |-- buscar.php
|   |   |
|   |   |-- /reportes
|   |   |   |-- generar.php
|   |   |
|   |   |-- /dashboard
|   |   |   |-- index.php
|   |   |
|   |   |-- /configuracion
|   |       |-- cupos.php
|   |       |-- general.php
|   |
|   |-- /middleware
|       |-- Auth.php               # Validacion de sesion (futuro)
|       |-- Csrf.php               # Proteccion CSRF
|
|-- /public
|   |-- index.php                  # Entry point / front controller
|   |-- .htaccess                  # Rewrite rules
|   |-- /css
|   |   |-- styles.css
|   |   |-- dashboard.css
|   |
|   |-- /js
|   |   |-- app.js                 # Funciones globales
|   |   |-- scanner.js             # Logica del lector de barras
|   |   |-- solicitud.js           # Logica formulario solicitudes
|   |   |-- persona.js             # Busqueda/creacion personas
|   |   |-- reportes.js            # Filtros y exportacion
|   |
|   |-- /img
|       |-- logo.png
|
|-- /vendor
|   |-- /fpdf                      # Libreria FPDF para PDF
|   |-- /phpmailer                 # PHPMailer para correos
|
|-- /storage
|   |-- /pdfs                      # PDFs generados temporalmente
|   |-- /logs                      # Logs de errores
|
|-- /sql
|   |-- schema.sql                 # DDL completo
|   |-- seeds.sql                  # Datos iniciales (catalogos)
|
|-- /docs
    |-- DISENO_SISTEMA.md          # Este documento
```

---

## 3. FLUJO FUNCIONAL DETALLADO

### 3.1 Flujo: Crear Solicitud

```
[1] Operador abre pantalla de registro
         |
[2] Escanea cedula con lector de barras
         |
         v
[3] Input recibe datos del escaner
         |
         v
[4] JS detecta fin de entrada (Enter/timeout)
         |
         v
[5] AJAX -> PersonaController::buscar($documento)
         |
    +----+----+
    |         |
  EXISTE    NO EXISTE
    |         |
    v         v
[6a] Auto-   [6b] Abrir modal
completar     "Crear Persona"
campos        con documento
    |         prellenado
    |         |
    v         v
[7] Operador completa formulario:
    - Sede (dropdown, prellenada de persona)
    - Destinatario (texto)
    - Motivo (dropdown)
    - Motivo otro (condicional)
    - Area (dropdown)
    - Area otro (condicional)
    - Observaciones
         |
         v
[8] JS valida campos en cliente
         |
         v
[9] Submit -> SolicitudController::crear()
         |
         v
[10] Servidor valida:
     - Campos requeridos
     - Persona existe
     - Sede activa
     - Cupo disponible (CupoService)
     - **Persona sin solicitud en el mismo mes (SolicitudService::tieneSolicitudEnMes)**
         |
    +----+----+
    |         |
  VALIDO     INVALIDO
    |         |
    v         v
[11a] INSERT [11b] Rechazar con
solicitud    mensaje claro
con estado   ("Ya tiene solicitud este mes")
"Aprobada"    |
    |         v
[12] CupoService::incrementar()
         |
         v
[13] Verificar si cupo llego al limite
         |
    +----+----+
    |         |
   NO        SI
    |         |
    v         v
[14a] Redir  [14b] Disparar
a listado    MailService
con exito    (envio automatico PDF)
             + marcar notificado
```

### 3.2 Flujo: Generar PDF Consolidado

```
[1] Admin accede a Reportes
         |
[2] Selecciona filtros:
    - Rango de fechas
    - Sede (todas o especifica)
    - Estado
         |
         v
[3] Submit -> ReporteController::generar()
         |
         v
[4] ReporteService consulta solicitudes
    con filtros aplicados
         |
         v
[5] Agrupar resultados:
    SEDE > AREA > FECHA
         |
         v
[6] PdfService genera documento:
    - Encabezado organizacion
    - Por cada sede:
      - Titulo sede
      - Por cada area:
        - Subtitulo area
        - Tabla de solicitudes ordenada por fecha
    - Pie: totales globales
         |
         v
[7] PDF guardado en /storage/pdfs/
         |
    +----+----+
    |         |
[8a] Descar- [8b] Enviar
gar en       por correo
navegador    (MailService)
```

### 3.3 Flujo: Envio de Correo

```
[MANUAL]                        [AUTOMATICO]
Admin presiona                  CupoService detecta
"Enviar por correo"             cupo lleno
    |                               |
    +--------->  MailService  <-----+
                     |
                     v
              Generar PDF (si no existe)
                     |
                     v
              Componer correo:
              - Para: configuracion.correo_destino
              - CC: configuracion.correo_cc
              - Asunto: "Reporte Ramos - [Sede] - [Fecha]"
              - Cuerpo: resumen basico (totales)
              - Adjunto: PDF consolidado
                     |
                     v
              PHPMailer envia via SMTP
                     |
                +----+----+
                |         |
              OK        ERROR
                |         |
                v         v
          Log exito    Log error
          en BD        en BD + notificar admin
```

---

## 4. ARQUITECTURA POR CAPAS

```
+----------------------------------------------------------+
|                    CAPA DE PRESENTACION                    |
|  /views (HTML+PHP) | /public/js (vanilla JS) | /public/css|
|  - Renderiza HTML                                         |
|  - Maneja eventos de UI                                   |
|  - Llamadas AJAX a controladores                          |
+----------------------------------------------------------+
                           |
                           v
+----------------------------------------------------------+
|                    CAPA DE CONTROLADORES                   |
|  /controllers                                             |
|  - Recibe requests HTTP (GET/POST)                        |
|  - Valida input basico                                    |
|  - Delega a Services                                      |
|  - Retorna vistas o JSON                                  |
+----------------------------------------------------------+
                           |
                           v
+----------------------------------------------------------+
|                    CAPA DE SERVICIOS                       |
|  /services                                                |
|  - Logica de negocio                                      |
|  - Orquesta operaciones entre modelos                     |
|  - Validaciones de negocio (cupos, duplicados)            |
|  - Generacion de PDF y envio de correo                    |
+----------------------------------------------------------+
                           |
                           v
+----------------------------------------------------------+
|                    CAPA DE DATOS                           |
|  /models                                                  |
|  - CRUD por entidad                                       |
|  - Queries SQL parametrizados (PDO)                       |
|  - Sin logica de negocio                                  |
+----------------------------------------------------------+
                           |
                           v
+----------------------------------------------------------+
|                    BASE DE DATOS                           |
|  MySQL                                                    |
+----------------------------------------------------------+
```

### Principios aplicados:
- **Separacion de responsabilidades**: cada capa tiene un rol unico
- **Modelos delgados**: solo acceso a datos, sin logica
- **Servicios gruesos**: toda la logica de negocio vive aqui
- **Controladores delgados**: solo routing y delegacion
- **Helpers transversales**: validacion, respuestas, fechas

---

## 5. LOGICA DE CONTROL DE CUPOS

### 5.1 Estructura

```
CupoService {

    verificarDisponibilidad(id_sede, periodo) -> bool
        // SELECT cupo_maximo, cupo_usado FROM cupos_sede
        // WHERE id_sede = ? AND periodo = ?
        // RETURN cupo_usado < cupo_maximo

    incrementarCupo(id_sede, periodo) -> void
        // UPDATE cupos_sede SET cupo_usado = cupo_usado + 1
        // WHERE id_sede = ? AND periodo = ?
        // Usar transaccion para evitar race conditions

    decrementarCupo(id_sede, periodo) -> void
        // Para cancelaciones
        // UPDATE cupos_sede SET cupo_usado = cupo_usado - 1

    verificarYNotificar(id_sede, periodo) -> void
        // IF cupo_usado >= cupo_maximo AND notificado = 0
        //   -> MailService::enviarReporteCupoLleno(id_sede)
        //   -> UPDATE notificado = 1

    obtenerResumen() -> array
        // Para dashboard: cupos por sede con % uso
}
```

### 5.2 Reglas de negocio

| Regla | Descripcion |
|-------|-------------|
| R1 | Cada sede tiene un cupo maximo configurable por periodo (mensual) |
| R2 | Al crear solicitud, verificar cupo ANTES del INSERT |
| R3 | Usar `SELECT ... FOR UPDATE` para evitar race conditions |
| R4 | Al cancelar solicitud, decrementar cupo |
| R5 | Cuando cupo_usado >= cupo_maximo, disparar correo automatico UNA vez |
| R6 | Si no existe registro de cupo para sede/periodo, crearlo con cupo_default |
| R7 | Admin puede modificar cupo_maximo en cualquier momento |

### 5.3 Flujo de validacion en transaccion

```
BEGIN TRANSACTION

  1. SELECT cupo_maximo, cupo_usado
     FROM cupos_sede
     WHERE id_sede = ? AND periodo = ?
     FOR UPDATE

  2. IF cupo_usado >= cupo_maximo
       ROLLBACK
       RETURN error "Cupo agotado para esta sede"

  3. INSERT INTO solicitudes (...)

  4. UPDATE cupos_sede
     SET cupo_usado = cupo_usado + 1

  5. IF cupo_usado + 1 >= cupo_maximo AND notificado = 0
       -> Marcar para notificacion post-commit

COMMIT

  6. IF marcado_para_notificacion
       -> MailService::enviarReporteCupoLleno()
       -> UPDATE notificado = 1
```

---

## 6. DISENO LOGICO DEL PDF CONSOLIDADO

### 6.1 Estructura visual del documento

```
+=====================================================+
|  [LOGO]   ORGANIZACION XYZ                          |
|  Reporte Consolidado de Solicitudes de Ramos        |
|  Periodo: 01/03/2026 - 27/03/2026                   |
|  Generado: 27/03/2026 14:30                          |
+=====================================================+

=== SEDE: SEDE PRINCIPAL ============================

  --- Area: Recursos Humanos ---

  +-----+------------+-------------------+----------+----------------+---------------+-------------+
  | No. | Fecha      | Solicitante       | Documento| Destinatario   | Motivo        | Observacion |
  +-----+------------+-------------------+----------+----------------+---------------+-------------+
  |  1  | 2026-03-01 | Juan Perez Garcia | 12345678 | Maria Lopez    | Cumpleanos    | -           |
  |  2  | 2026-03-01 | Ana Ruiz Torres   | 87654321 | Pedro Martinez | Condolencias  | Familia     |
  +-----+------------+-------------------+----------+----------------+---------------+-------------+
                                              Subtotal Area: 2 solicitudes

  --- Area: Contabilidad ---

  +-----+------------+-------------------+----------+----------------+---------------+-------------+
  |  1  | 2026-03-05 | Carlos Diaz       | 11223344 | Laura Gomez    | Nacimiento    | -           |
  +-----+------------+-------------------+----------+----------------+---------------+-------------+
                                              Subtotal Area: 1 solicitud

                                    TOTAL SEDE PRINCIPAL: 3 solicitudes

=== SEDE: SEDE NORTE ================================
  (...)

+=====================================================+
|  RESUMEN GENERAL                                     |
|                                                      |
|  Total solicitudes: 15                               |
|                                                      |
|  Por Sede:                                           |
|    - Sede Principal: 8                               |
|    - Sede Norte: 4                                   |
|    - Sede Sur: 3                                     |
|                                                      |
|  Por Area:                                           |
|    - Recursos Humanos: 5                             |
|    - Contabilidad: 4                                 |
|    - Operaciones: 3                                  |
|    - Otro: 3                                         |
|                                                      |
|  Por Motivo:                                         |
|    - Cumpleanos: 6                                   |
|    - Condolencias: 4                                 |
|    - Nacimiento: 3                                   |
|    - Otro: 2                                         |
+=====================================================+
```

### 6.2 Especificaciones tecnicas del PDF

| Aspecto | Valor |
|---------|-------|
| Libreria | FPDF 1.86 (sin dependencias externas) |
| Tamano pagina | Carta (Letter) horizontal (landscape) |
| Margenes | 10mm todos los lados |
| Fuente encabezado | Helvetica Bold 14pt |
| Fuente subtitulo | Helvetica Bold 11pt |
| Fuente tabla | Helvetica 8pt |
| Colores alternos | Filas alternas #FFFFFF / #F2F2F2 |
| Encabezado sede | Fondo #2C3E50, texto blanco |
| Encabezado area | Fondo #3498DB, texto blanco |
| Salto de pagina | Automatico, repite encabezado de tabla |
| Nombre archivo | `reporte_ramos_YYYYMMDD_HHmmss.pdf` |

---

## 7. FLUJO DEL ESCANER DE CODIGO DE BARRAS

### 7.1 Comportamiento del lector

El lector de codigo de barras de cedulas colombianas funciona como un dispositivo HID (teclado). Al escanear:

1. Inyecta caracteres en el input enfocado
2. Finaliza con un caracter Enter (keyCode 13)
3. La velocidad de inyeccion es ~50ms entre caracteres (mucho mas rapido que un humano)

### 7.2 Formatos posibles de la cedula colombiana

| Formato | Contenido | Ejemplo |
|---------|-----------|---------|
| Solo numero | Documento | `1234567890` |
| PDF417 completo | Cadena codificada | `0812345678901234567890PEREZ...` |

### 7.3 Logica de scanner.js

```
scanner.js {

    // Estado
    buffer = ""
    lastKeyTime = 0
    THRESHOLD = 100ms  // Tiempo max entre teclas para considerar escaner

    // Eventos
    onKeyPress(event) {
        currentTime = Date.now()

        IF event.key == "Enter" {
            IF buffer.length >= 5 AND (currentTime - lastKeyTime < THRESHOLD) {
                // Es entrada de escaner
                event.preventDefault()
                procesarEntrada(buffer)
            }
            buffer = ""
            RETURN
        }

        IF (currentTime - lastKeyTime > THRESHOLD) AND buffer.length > 0 {
            // Pausa larga = escritura manual, resetear
            buffer = ""
        }

        buffer += event.key
        lastKeyTime = currentTime
    }

    procesarEntrada(raw) {
        documento = BarcodeParser.extraerDocumento(raw)
        buscarPersona(documento)
    }

    buscarPersona(documento) {
        // AJAX GET /api/personas/buscar?documento=XXXXX
        // Si existe -> autocompletar formulario
        // Si no existe -> abrir modal crear persona con documento prellenado
    }
}
```

### 7.4 BarcodeParser (server-side para validacion)

```
BarcodeParser {

    extraerDocumento(raw) {
        // Limpiar caracteres no numericos
        limpio = preg_replace('/[^0-9]/', '', raw)

        // Si tiene mas de 15 chars, es formato PDF417
        IF strlen(limpio) > 15 {
            // Cedula colombiana PDF417:
            // Posiciones 2-12 contienen el numero de documento
            documento = substr(limpio, 2, 10)
            documento = ltrim(documento, '0')  // Quitar ceros a la izquierda
        } ELSE {
            documento = limpio
        }

        RETURN documento
    }
}
```

### 7.5 Flujo visual

```
[Escaner] --caracteres--> [Input #documento]
                                |
                           [Enter detectado]
                                |
                                v
                    [scanner.js: es escaner?]
                        |             |
                       SI            NO
                        |             |
                        v             v
                [procesarEntrada] [Dejar que el
                        |          usuario escriba
                        v          manualmente]
                [AJAX buscar]
                   |        |
                EXISTE   NO EXISTE
                   |        |
                   v        v
              [Llenar    [Modal nueva
               campos]    persona]
```

---

## 8. REGLAS DE VALIDACION

### 8.1 Validaciones por entidad

#### Persona
| Campo | Regla | Mensaje error |
|-------|-------|---------------|
| tipo_documento | Requerido, debe existir en ENUM | "Seleccione tipo de documento" |
| documento | Requerido, solo numeros, 5-20 chars, unico | "Documento invalido o ya registrado" |
| primer_nombre | Requerido, solo letras+espacios, 2-50 chars | "Nombre invalido" |
| segundo_nombre | Opcional, mismas reglas si se llena | "Nombre invalido" |
| primer_apellido | Requerido, solo letras+espacios, 2-50 chars | "Apellido invalido" |
| segundo_apellido | Opcional, mismas reglas si se llena | "Apellido invalido" |
| telefono | Opcional, solo numeros, 7-15 chars | "Telefono invalido" |
| id_sede | Requerido, debe existir en tabla sedes con activo=1 | "Seleccione sede valida" |

#### Solicitud
| Campo | Regla | Mensaje error |
|-------|-------|---------------|
| persona_id | Requerido, debe existir en personas | "Persona no encontrada" |
| fecha_solicitud | Requerido, formato YYYY-MM-DD, no futura | "Fecha invalida" |
| id_sede | Requerido, existir en sedes activas | "Sede invalida" |
| nombre_destinatario | Requerido, 2-150 chars | "Ingrese nombre del destinatario" |
| id_motivo | Requerido, existir en motivos_ramo activos | "Seleccione motivo" |
| motivo_otro | Requerido SI motivo.requiere_detalle = 1, max 200 | "Especifique el motivo" |
| id_area_solicitud | Requerido, existir en areas_solicitud activas | "Seleccione area" |
| area_otro | Requerido SI area seleccionada es "Otro" | "Especifique el area" |
| observaciones | Opcional, max 500 chars | "Observaciones muy largas" |

#### Cupo
| Campo | Regla | Mensaje error |
|-------|-------|---------------|
| cupo_maximo | Requerido, entero positivo, min 1 | "Cupo debe ser mayor a 0" |

### 8.2 Validaciones de negocio (en Services)

| Regla | Capa | Descripción |
|-------|------|-------------|
| Documento unico | PersonaService | No permitir duplicados al crear persona |
| Cupo disponible | CupoService | Verificar antes de insertar solicitud |
| Persona activa | SolicitudService | Solo personas con activo=1 pueden solicitar |
| Sede activa | SolicitudService | Solo sedes activas aceptan solicitudes |
| Consistencia sede | SolicitudService | Alertar si sede de solicitud != sede de persona |
| Un ramo por persona por mes | SolicitudService | Una persona solo puede tener una solicitud por mes (estados: Aprobada, Pendiente, Entregada) |
| Aprobación automática | SolicitudService | Las solicitudes se aprueban automáticamente si es la primera del mes para esa persona |

### 8.3 Validaciones de seguridad

| Medida | Implementacion |
|--------|----------------|
| SQL Injection | PDO prepared statements en TODOS los queries |
| XSS | `htmlspecialchars()` en toda salida a HTML |
| CSRF | Token por sesion en todos los formularios POST |
| Sanitizacion | `trim()`, `strip_tags()` en inputs de texto |
| Tipos | Casting explicito: `(int)` para IDs, `(string)` para textos |

---

## 9. ENDPOINTS NECESARIOS

### 9.1 Front Controller

Todas las rutas pasan por `/public/index.php` que parsea `$_GET['route']` y despacha al controlador correcto.

### 9.2 Mapa de endpoints

#### Personas
| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| GET | `/personas/buscar?documento=X` | PersonaController::buscar | Buscar por documento (AJAX, retorna JSON) |
| POST | `/personas/crear` | PersonaController::crear | Crear nueva persona (AJAX, retorna JSON) |

#### Solicitudes
| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| GET | `/solicitudes` | SolicitudController::listar | Vista listado con filtros |
| GET | `/solicitudes/crear` | SolicitudController::formCrear | Vista formulario creacion |
| POST | `/solicitudes/crear` | SolicitudController::crear | Procesar creacion |
| GET | `/solicitudes/ver/{id}` | SolicitudController::ver | Vista detalle |
| POST | `/solicitudes/cambiar-estado` | SolicitudController::cambiarEstado | Cambiar estado (AJAX) |

#### Catalogos (AJAX para dropdowns)
| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| GET | `/api/sedes` | SedeController::listarActivas | JSON sedes activas |
| GET | `/api/motivos` | SedeController::listarMotivos | JSON motivos activos |
| GET | `/api/areas` | SedeController::listarAreas | JSON areas activas |
| GET | `/api/estados` | SedeController::listarEstados | JSON estados |

#### Reportes
| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| GET | `/reportes` | ReporteController::index | Vista filtros de reporte |
| POST | `/reportes/generar-pdf` | ReporteController::generarPdf | Generar y descargar PDF |
| POST | `/reportes/enviar-correo` | ReporteController::enviarCorreo | Generar PDF y enviar por email |

#### Dashboard
| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| GET | `/dashboard` | DashboardController::index | Vista dashboard |
| GET | `/api/dashboard/resumen` | DashboardController::resumen | JSON metricas (AJAX) |

#### Cupos
| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| GET | `/cupos` | CupoController::index | Vista gestion cupos |
| POST | `/cupos/actualizar` | CupoController::actualizar | Modificar cupo sede |

#### Configuracion
| Metodo | Ruta | Controlador | Descripcion |
|--------|------|-------------|-------------|
| GET | `/configuracion` | ConfigController::index | Vista configuracion |
| POST | `/configuracion/guardar` | ConfigController::guardar | Guardar cambios config |

---

## 11. REGLAS DE NEGOCIO ESPECIFICAS

### 11.1 Restricción de un ramo por persona por mes

**Objetivo:** Evitar que una misma persona solicite múltiples ramos en un mismo período mensual.

**Implementación:**
- **Método de validación:** `Solicitud::tieneSolicitudEnMes($persona_id, $fecha)`
- **Lógica:** Verifica si la persona ya tiene solicitudes con estados Aprobada, Pendiente o Entregada en el mismo mes y año
- **Momento de validación:** Antes de crear cualquier solicitud nueva
- **Mensaje de error:** "La persona ya tiene una solicitud registrada en este mes. Solo se permite un ramo por mes por persona."

**Query SQL utilizado:**
```sql
SELECT COUNT(*) FROM solicitudes
WHERE persona_id = ? AND DATE_FORMAT(fecha_solicitud, '%Y-%m') = DATE_FORMAT(?, '%Y-%m')
AND id_estado IN (SELECT id FROM estados_solicitud WHERE nombre IN ('Aprobada', 'Pendiente', 'Entregada'))
```

### 11.2 Aprobación automática de solicitudes

**Objetivo:** Simplificar el flujo aprobando automáticamente las solicitudes que cumplen con todas las validaciones.

**Implementación:**
- **Estado inicial:** Las solicitudes nuevas se crean directamente con estado "Aprobada" (id: 2)
- **Condición:** Si la persona no tiene solicitudes previas en el mes y hay cupo disponible
- **Beneficio:** Reduce la carga administrativa y agiliza el proceso de entrega

**Flujo actualizado:**
1. Usuario crea solicitud
2. Sistema valida:
   - Datos requeridos ✓
   - Persona activa ✓
   - Sede activa ✓
   - Cupo disponible ✓
   - **Sin solicitud previa en el mes ✓**
3. Si todas las validaciones pasan → **Solicitud aprobada automáticamente**
4. Si alguna validación falla → Solicitud rechazada con mensaje específico

### 11.3 Estados considerados para la validación mensual

Para la restricción de "un ramo por mes", solo se consideran los siguientes estados:
- **Aprobada:** Solicitudes aprobadas y en proceso
- **Pendiente:** Solicitudes en espera de aprobación
- **Entregada:** Solicitudes ya entregadas

**No se consideran:**
- **Rechazada:** Solicitudes denegadas (no consumen el cupo mensual)
- **Cancelada:** Solicitudes canceladas (liberan el cupo mensual)

---

## 12. RIESGOS Y MITIGACIONES

### 10.1 Riesgos tecnicos

| # | Riesgo | Probabilidad | Impacto | Mitigacion |
|---|--------|-------------|---------|------------|
| 1 | **Race condition en cupos**: dos usuarios crean solicitud simultaneamente y superan cupo | Media | Alto | Usar `SELECT ... FOR UPDATE` dentro de transaccion MySQL. El bloqueo a nivel de fila garantiza atomicidad |
| 2 | **Fallo en envio de correo**: servidor SMTP no disponible | Media | Medio | Implementar log_correos para reintentos. Cola basica: si falla, marcar como pendiente y reintentar en siguiente solicitud o via cron |
| 3 | **Formato escaner no reconocido**: nuevo modelo de cedula o escaner diferente | Media | Medio | BarcodeParser debe ser extensible. Implementar fallback: si parseo falla, dejar valor crudo en input para edicion manual. Log del formato no reconocido |
| 4 | **PDF con muchos registros**: reporte con +1000 solicitudes puede agotar memoria | Baja | Alto | FPDF es ligero pero: limitar consulta a maximo 5000 registros. Procesar en bloques. Usar `ini_set('memory_limit', '256M')` para generacion |
| 5 | **Inyeccion SQL/XSS** | Baja (si se implementa bien) | Critico | PDO prepared statements obligatorio. `htmlspecialchars()` en todas las salidas. CSRF tokens en formularios |
| 6 | **Perdida de datos por falta de backup** | Media | Critico | Documentar script de backup MySQL. Recomendar cron job de mysqldump diario |

### 10.2 Riesgos funcionales

| # | Riesgo | Mitigacion |
|---|--------|------------|
| 7 | **Datos inconsistentes en catalogos**: alguien borra un motivo que tiene solicitudes | Foreign keys con RESTRICT. No permitir eliminar catalogos con registros asociados, solo desactivar (activo=0) |
| 8 | **Duplicidad de personas**: escaner retorna formato diferente y se crea persona duplicada | Validacion UNIQUE en BD + verificacion en PersonaService antes de INSERT |
| 9 | **Cupo configurado incorrectamente**: admin pone cupo 0 o negativo | Validacion: cupo_maximo >= 1. No permitir reducir por debajo de cupo_usado actual |
| 10 | **Solicitudes sin area clara**: usuario siempre selecciona "Otro" | Monitorear en dashboard % de "Otro". Alerta si supera umbral. Revisar catalogo periodicamente |

### 10.3 Riesgos operativos

| # | Riesgo | Mitigacion |
|---|--------|------------|
| 11 | **Servidor sin soporte SMTP**: hosting basico sin acceso a puertos | Soportar tanto SMTP directo como API de servicios (SendGrid, etc). Config intercambiable |
| 12 | **Escaner no compatible con navegador** | El escaner es HID (teclado), funciona en cualquier navegador. Documentar: input debe estar enfocado. Auto-focus al cargar pagina |
| 13 | **Multiples usuarios concurrentes** | Sesiones PHP nativas. Transacciones en operaciones criticas. Sin estado compartido en memoria |

---

## RESUMEN EJECUTIVO

| Aspecto | Decision |
|---------|----------|
| **Backend** | PHP puro con arquitectura MVC + Services |
| **Base de datos** | MySQL con 9 tablas + indices optimizados |
| **Frontend** | HTML/CSS/JS vanilla, AJAX para dinamismo |
| **PDF** | FPDF - consolidado, agrupado por sede > area > fecha |
| **Correo** | PHPMailer via SMTP |
| **Escaner** | Deteccion por velocidad de input + parseo configurable |
| **Cupos** | Control transaccional con bloqueo de fila |
| **Seguridad** | PDO, CSRF, XSS prevention, validacion doble (cliente+servidor) |

---

*Documento preparado para revision antes de iniciar fase de implementacion.*
*Siguiente paso: aprobacion del diseno y generacion del codigo por modulos.*
