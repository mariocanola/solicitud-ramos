# -*- coding: utf-8 -*-
# Contenido capítulos 3-8

# ═══════════════════════════════════════════════════════════
#  CAPÍTULO 3
# ═══════════════════════════════════════════════════════════
h1('3. Análisis de Requisitos')
body('El análisis de requisitos identifica, documenta y valida las necesidades que el sistema debe satisfacer. Este capítulo describe el proceso de negocio anterior y posterior a la implementación, las técnicas de levantamiento de información, la especificación formal de requisitos y los modelos que representan la estructura y comportamiento esperado del sistema.', first=True)

h2('3.1 Caracterización del Negocio')
body('La caracterización del negocio describe los procesos organizacionales que el sistema digitaliza, los actores involucrados y las herramientas utilizadas para comprender dichos procesos antes de iniciar el diseño de la solución.', first=True)

h3('3.1.1 Procesos Actuales del Negocio')
body('A continuación se describen los procesos en su estado anterior (AS-IS) y posterior (TO-BE) a la implementación del sistema.', first=True)

h4('Proceso AS-IS — Solicitud manual de ramos')
asis = [
    ('Proceso 1 — Recepción de solicitudes (lunes).', 'El empleado se desplaza a la oficina asignada a su sede. El personal de la oficina le entrega un formulario físico en papel. El empleado diligencia sus datos personales, el motivo de la solicitud y el nombre del destinatario. El formulario se almacena físicamente agrupado por sede.'),
    ('Proceso 2 — Traslado al área de elaboración (lunes a jueves).', 'Los formularios almacenados se trasladan físicamente al área de elaboración. El área los revisa y prepara los ramos. No existe confirmación formal del traslado ni registro de recepción.'),
    ('Proceso 3 — Entrega de ramos (viernes).', 'El empleado regresa a la oficina, el personal verifica su nombre en los registros en papel, entrega el ramo y emite una remisión física como comprobante.'),
]
for code, text in asis:
    body(text, bold_prefix=code, first=True)

h4('Proceso TO-BE — Solicitud digitalizada mediante sistema de autoatención')
body('El sistema transforma el proceso en un modelo de autoatención: el propio empleado se presenta en el punto de solicitud, digita su número de cédula en el panel táctil y el sistema valida automáticamente el cupo disponible y la restricción de período antes de registrar la solicitud. No se requiere personal intermediario para el registro. El administrador gestiona los cambios de estado a lo largo del proceso, y el área de elaboración recibe el reporte PDF consolidado sin necesidad de formularios en papel. La entrega queda registrada con el estado "Entregada".', first=True)

h3('3.1.2 Diagrama de Procesos BPMN')
body('El diagrama BPMN del proceso TO-BE representa el flujo de trabajo digitalizado en un modelo de autoatención. La piscina principal contiene tres carriles: el Empleado / Operador (actor principal que inicia el proceso), el Administrador (gestor de estados y reportes) y el Área de Elaboración (receptor final del reporte PDF). Los elementos principales del diagrama incluyen dos compuertas exclusivas —disponibilidad de cupo y restricción de período— y eventos de fin alternativo para cada condición de bloqueo del proceso.', first=True)
tech_note('El diagrama BPMN debe ser elaborado en Bizagi, Lucidchart o Draw.io. Los carriles son: (1) Empleado / Operador, (2) Administrador, (3) Área de Elaboración. No existe carril de "personal de oficina intermediario" ya que el proceso es de autoatención.')
figure_ph(1, 'Diagrama BPMN — Proceso TO-BE de solicitud de ramo mediante panel táctil de autoatención.')

h3('3.1.3 Técnicas Utilizadas')
body('El levantamiento de requisitos empleó cuatro técnicas complementarias que permitieron capturar tanto los procesos formales como las reglas de negocio implícitas.', first=True)
apa_table(8, 'Técnicas de levantamiento de requisitos aplicadas',
    ['Técnica', 'Aplicación en el proyecto'],
    [
        ['Observación directa del proceso', 'El desarrollador observó el proceso manual de solicitud de ramos para identificar etapas, actores, documentos físicos involucrados y puntos de falla'],
        ['Revisión de documentos físicos', 'Análisis del formulario en papel utilizado para el registro de solicitudes, identificando los campos de datos necesarios'],
        ['Entrevista no estructurada', 'Conversaciones con el personal del área de oficina para comprender las reglas de negocio implícitas: cupos por sede, restricciones de período y motivos'],
        ['Prototipado iterativo', 'El panel táctil y los formularios fueron ajustados con base en retroalimentación del equipo durante el período de pruebas de un mes'],
    ]
)

h3('3.1.4 Fuentes de Información')
body('Las fuentes primarias consultadas durante el levantamiento de requisitos fueron las siguientes: el personal operativo de las oficinas de Flores el Tandil, los formularios físicos del proceso anterior como referencia para la identificación de campos de datos, los registros de solicitudes del período de pruebas de un mes con datos reales, y Mario Alexander Cañola Cano en su rol de desarrollador y analista del sistema.', first=True)

h2('3.2 Especificación de Requisitos')
body('La especificación de requisitos documenta de manera formal y trazable las funcionalidades que el sistema debe proveer (requisitos funcionales) y las condiciones de calidad bajo las cuales debe operar (requisitos no funcionales).', first=True)

h3('3.2.1 Requisitos Funcionales')
body('Los requisitos funcionales describen las capacidades concretas que el sistema debe implementar. Se clasifican según prioridad: Alta (imprescindible para la operación), Media (importante pero no bloqueante).', first=True)
apa_table(9, 'Especificación de requisitos funcionales del sistema',
    ['ID', 'Nombre', 'Descripción', 'Módulo', 'Prioridad'],
    [
        ['RF-01','Autenticación de usuarios','El sistema debe permitir el acceso mediante usuario y contraseña, diferenciando roles administrador y operador','Autenticación','Alta'],
        ['RF-02','Cierre de sesión por inactividad','La sesión debe expirar automáticamente tras 30 minutos de inactividad','Autenticación','Alta'],
        ['RF-03','Registro de beneficiarios','Registrar personas con: tipo y número de documento, nombre completo, teléfono, sede y empresa','Personas','Alta'],
        ['RF-04','Edición de beneficiarios','Permitir actualizar los datos de una persona registrada','Personas','Alta'],
        ['RF-05','Baja lógica de beneficiarios','Desactivar un beneficiario sin eliminar su historial de solicitudes','Personas','Alta'],
        ['RF-06','Reactivación de beneficiarios','Reactivar un beneficiario previamente desactivado','Personas','Media'],
        ['RF-07','Importación masiva desde Excel','Cargar archivo Excel con múltiples beneficiarios con previsualización antes de confirmar','Personas','Alta'],
        ['RF-08','Descarga de plantilla de importación','Proveer plantilla Excel descargable con el formato requerido','Personas','Media'],
        ['RF-09','Búsqueda de persona por documento','Buscar beneficiario por número de documento con respuesta AJAX instantánea','Solicitudes','Alta'],
        ['RF-10','Lectura de cédula por escáner','Soportar lectura del código de barras PDF417 de la cédula colombiana','Solicitudes','Alta'],
        ['RF-11','Creación de solicitud','Registrar solicitud indicando motivo, nombre del destinatario y observaciones opcionales','Solicitudes','Alta'],
        ['RF-12','Validación de cupo por sede','Verificar disponibilidad de cupo en la sede; bloquear si está agotado','Solicitudes / Cupos','Alta'],
        ['RF-13','Restricción de período por beneficiario','Verificar que el beneficiario no tenga solicitud activa en los últimos 30 días; con ventana de gracia semanal: si el vencimiento de los 30 días cae entre el lunes y el jueves de la semana en curso, se adelanta el permiso al inicio de esa semana para incluir la solicitud en la preparación del lunes','Solicitudes','Alta'],
        ['RF-14','Listado de solicitudes (admin)','Ver listado completo con filtros por fecha, sede, estado y texto libre','Solicitudes','Alta'],
        ['RF-15','Cambio de estado de solicitud','Cambiar estado entre: Pendiente, Aprobada, Entregada, Rechazada, Cancelada','Solicitudes','Alta'],
        ['RF-16','Eliminación de solicitudes','Eliminar una solicitud del sistema','Solicitudes','Media'],
        ['RF-17','Configuración de cupos por sede','Ajustar el cupo máximo de solicitudes por sede en el período actual','Cupos','Alta'],
        ['RF-18','Generación de reporte PDF','Generar PDF consolidado filtrable por fecha y sede, con diseño corporativo','Reportes','Alta'],
        ['RF-19','Dashboard con estadísticas','Mostrar estadísticas en tiempo real por sede, motivo, estado y período','Dashboard','Alta'],
        ['RF-20','Actualización automática del dashboard','Refrescar el dashboard automáticamente cuando se detecten cambios en los datos','Dashboard','Media'],
        ['RF-21','Gestión de catálogos','Gestionar sedes, motivos de ramo y estados de solicitud desde la interfaz','Configuración','Alta'],
        ['RF-22','Configuración del tipo de período','Cambiar el tipo de período de conteo entre mensual y semanal','Configuración','Media'],
        ['RF-23','Registro de nueva persona desde solicitud','Registrar un beneficiario directamente desde el panel de solicitudes sin cambiar de pantalla','Solicitudes','Alta'],
        ['RF-24','Bloqueo de solicitudes para beneficiarios inactivos','Al buscar un beneficiario por documento, si este se encuentra en estado inactivo el sistema impide la creación de la solicitud y muestra un mensaje de error específico, sin ofrecer la opción de registrar una nueva persona con ese documento','Solicitudes','Alta'],
        ['RF-25','Gestión de contraseña exclusiva del administrador','El cambio de contraseña de los usuarios del sistema es una operación restringida al rol administrador; el operador no dispone de esta opción en su interfaz ni puede acceder al endpoint correspondiente','Autenticación','Alta'],
    ]
)

h3('3.2.2 Requisitos No Funcionales')
body('Los requisitos no funcionales establecen las condiciones de calidad que el sistema debe cumplir independientemente de las funcionalidades específicas, tomando como marco el modelo ISO/IEC 25010.', first=True)
apa_table(10, 'Especificación de requisitos no funcionales del sistema',
    ['ID', 'Nombre', 'Descripción', 'Categoría'],
    [
        ['RNF-01','Seguridad de contraseñas','Las contraseñas deben almacenarse con algoritmo BCRYPT; nunca en texto plano','Seguridad'],
        ['RNF-02','Protección CSRF','Todos los formularios POST deben incluir y validar un token CSRF de sesión','Seguridad'],
        ['RNF-03','Protección contra inyección SQL','Todas las consultas a la base de datos deben usar PDO con prepared statements','Seguridad'],
        ['RNF-04','Headers de seguridad HTTP','El sistema debe enviar: X-Frame-Options: DENY, X-Content-Type-Options: nosniff, Referrer-Policy, Content-Security-Policy','Seguridad'],
        ['RNF-05','Tiempo de respuesta en búsqueda','La búsqueda por documento debe retornar resultado en menos de 1 segundo en red local','Rendimiento'],
        ['RNF-06','Operación sin conexión a internet','El sistema debe funcionar sin internet; todas las dependencias disponibles localmente','Disponibilidad'],
        ['RNF-07','Compatibilidad de navegador','Compatible con Chrome y Firefox en versiones modernas (últimos 2 años)','Portabilidad'],
        ['RNF-08','Interfaz táctil','El panel del operador debe ser usable en pantallas táctiles de al menos 10 pulgadas','Usabilidad'],
        ['RNF-09','Paginación de listados','Los listados deben paginar con opciones de 15, 30, 50 y 100 registros por página','Rendimiento'],
        ['RNF-10','Mantenibilidad del código','El código debe seguir el patrón MVC con separación clara en capas','Mantenibilidad'],
        ['RNF-11','Codificación de caracteres','Usar UTF-8 (utf8mb4 en MySQL) para soporte de caracteres especiales del español','Portabilidad'],
        ['RNF-12','Logging de errores','Los errores del servidor deben registrarse en storage/logs/errores.log sin exponer detalles técnicos al usuario','Seguridad'],
    ]
)

h2('3.3 Modelado de Requisitos')
body('El modelado de requisitos traduce la especificación textual a representaciones visuales que facilitan la comunicación entre los actores del proyecto y sirven de base para el diseño del sistema.', first=True)

h3('3.3.1 Diagramas de Casos de Uso')
body('Los casos de uso describen las interacciones entre los actores del sistema y las funcionalidades que este provee. El sistema reconoce dos actores principales: el Empleado / Operador, quien interactúa de manera autónoma con el panel táctil de solicitudes, y el Administrador, quien dispone de acceso completo a todos los módulos del sistema.', first=True)
figure_ph(2, 'Diagrama de casos de uso del sistema con actores Empleado/Operador y Administrador.')

usecase_table('CU-01 — Crear Solicitud (Empleado / Operador)', [
    ('Actor principal', 'Empleado / Operador (el propio beneficiario)'),
    ('Precondición', 'El punto de solicitud está habilitado y el panel táctil del sistema se encuentra en la pantalla de inicio de solicitud'),
    ('Flujo principal', '1. El empleado se presenta en el punto de solicitud. 2. Digita su número de cédula en el panel táctil (o la escanea con el lector de código de barras PDF417). 3. El sistema busca y muestra los datos del empleado. 4. El sistema verifica automáticamente la disponibilidad de cupo y la restricción de período. 5. El empleado selecciona el motivo del ramo e ingresa el nombre del destinatario. 6. El empleado confirma; el sistema registra la solicitud con estado "Pendiente".'),
    ('Flujo alternativo A', 'Si el empleado no está registrado en el sistema: se muestra un formulario de registro rápido para que el administrador o el propio empleado ingrese sus datos antes de continuar'),
    ('Flujo alternativo B', 'Si no hay cupo disponible para la sede: el sistema muestra un mensaje informativo y bloquea el registro de la solicitud'),
    ('Flujo alternativo C', 'Si el empleado ya realizó una solicitud en los últimos 30 días y no se encuentra en ventana de gracia semanal: el sistema muestra la solicitud activa existente e impide registrar una nueva. Excepción: si el vencimiento de los 30 días cae entre el lunes y el jueves de la semana en curso, el sistema permite adelantar la solicitud sin mostrar bloqueo'),
    ('Flujo alternativo D', 'Si el beneficiario existe en el sistema pero se encuentra en estado inactivo: el sistema muestra un mensaje de error específico e impide tanto la creación de la solicitud como la oferta de registrar una nueva persona con ese documento'),
    ('Postcondición', 'La solicitud queda registrada con estado "Pendiente" sin intervención de personal intermediario'),
])

usecase_table('CU-02 — Gestionar Estado de Solicitud (Administrador)', [
    ('Actor principal', 'Administrador'),
    ('Precondición', 'El administrador ha iniciado sesión. Existe al menos una solicitud registrada.'),
    ('Flujo principal', '1. Accede al listado de solicitudes (/solicitudes). 2. Aplica filtros si lo requiere. 3. Selecciona una solicitud. 4. Cambia el estado. 5. El sistema actualiza y confirma el cambio.'),
    ('Postcondición', 'La solicitud refleja el nuevo estado en el sistema'),
])

usecase_table('CU-03 — Generar Reporte PDF (Administrador)', [
    ('Actor principal', 'Administrador'),
    ('Precondición', 'El administrador ha iniciado sesión. Existen solicitudes en el rango de fechas seleccionado.'),
    ('Flujo principal', '1. Accede a Reportes (/reportes). 2. Selecciona rango de fechas y sede. 3. Indica si desea hojas individuales por solicitud. 4. El sistema genera el PDF y lo descarga.'),
    ('Postcondición', 'El PDF es descargado en el equipo del administrador'),
])

usecase_table('CU-04 — Importar Beneficiarios desde Excel (Administrador)', [
    ('Actor principal', 'Administrador'),
    ('Precondición', 'El administrador ha iniciado sesión y dispone de un archivo Excel con el formato de la plantilla.'),
    ('Flujo principal', '1. Accede a /personas/importar. 2. Sube el archivo Excel. 3. El sistema procesa y muestra previsualización. 4. El administrador confirma. 5. El sistema inserta los registros.'),
    ('Flujo alternativo', 'Si el archivo contiene errores de formato: el sistema los indica y no procesa la importación'),
    ('Postcondición', 'Los beneficiarios del archivo quedan registrados en el sistema'),
])

h3('3.3.2 Modelo Entidad-Relación')
body('El modelo entidad-relación define la estructura de datos del sistema. La base de datos flores_db cuenta con ocho tablas que representan las entidades del dominio del negocio.', first=True)
figure_ph(3, 'Modelo entidad-relación de la base de datos flores_db.')
apa_table(11, 'Relaciones entre entidades del modelo de datos',
    ['Entidad A', 'Cardinalidad', 'Entidad B', 'Descripción de la relación'],
    [
        ['sedes', '1:N', 'personas', 'Una sede tiene asignados muchos empleados'],
        ['sedes', '1:N', 'solicitudes', 'Una sede puede tener muchas solicitudes registradas'],
        ['sedes', '1:N', 'cupos_sede', 'Una sede tiene un registro de cupo por período'],
        ['personas', '1:N', 'solicitudes', 'Una persona puede tener múltiples solicitudes en diferentes períodos'],
        ['motivos_ramo', '1:N', 'solicitudes', 'Un motivo puede estar asociado a muchas solicitudes'],
        ['estados_solicitud', '1:N', 'solicitudes', 'Un estado puede corresponder a muchas solicitudes simultáneamente'],
    ]
)

h3('3.3.3 Prototipos y Wireframes de la Interfaz')
body('Los prototipos describen la estructura visual de las pantallas principales. Las descripciones que se presentan a continuación corresponden a las pantallas implementadas en la versión actual del sistema.', first=True)
for num, title, desc in [
    (1, 'Inicio de sesión', 'Formulario centrado con campos para usuario y contraseña, botón de ingreso y mensaje de error en caso de credenciales incorrectas.'),
    (2, 'Dashboard administrativo', 'Cuatro tarjetas métricas en la fila superior (total, período, pendientes, entregadas). Dos gráficos en fila media (distribución por sede en torta; solicitudes semanales por sede en barras). Tabla inferior con las últimas cinco solicitudes.'),
    (3, 'Panel táctil del operador', 'Campo de búsqueda grande para el número de documento. Resultado con nombre, sede y empresa del beneficiario. Formulario con selector de motivo, campo de destinatario y observaciones. Mensajes de estado en colores: cupo agotado en rojo, solicitud duplicada en amarillo.'),
    (4, 'Listado de solicitudes (administrador)', 'Filtros superiores: rango de fechas, sede, estado, texto libre. Tabla paginada con fecha, beneficiario, sede, motivo, destinatario, estado con color e indicador de acciones por fila.'),
    (5, 'Configuración del sistema', 'Organizada en pestañas: General, Cupos y Catálogos. La pestaña Cupos muestra tabla por sede con cupo máximo, cupo utilizado y barra de progreso. La pestaña Catálogos presenta paneles colapsables para sedes, motivos y estados.'),
]:
    body(f'Pantalla {num} — {title}. {desc}', bold_prefix=f'Pantalla {num} — {title}.', first=True)

figure_ph(4, 'Wireframes de las cinco pantallas principales del sistema.')

# ═══════════════════════════════════════════════════════════
#  CAPÍTULO 4
# ═══════════════════════════════════════════════════════════
h1('4. Diseño del Software')
body('El diseño del software documenta las decisiones de arquitectura, la estructura de la base de datos y los principios que guiaron el diseño de la interfaz de usuario. Este capítulo traduce los requisitos identificados en el capítulo anterior en un modelo técnico concreto que sirvió de guía durante la implementación del sistema.', first=True)

h2('4.1 Arquitectura del Software')
body('La arquitectura define la organización de sus componentes, las responsabilidades de cada capa y los mecanismos de comunicación entre ellas. El diseño priorizó la separación de responsabilidades, la mantenibilidad del código y la seguridad en cada punto de entrada.', first=True)

h3('4.1.1 Modelo Arquitectónico')
body('El sistema implementa el patrón MVC con un Front Controller como punto de entrada único. La arquitectura se organiza en seis capas claramente diferenciadas, que se describen a continuación de arriba hacia abajo según el flujo de una petición HTTP.', first=True)
figure_ph(5, 'Diagrama de arquitectura en capas del Sistema de Solicitud de Ramos.')

layers = [
    ('Capa de presentación.', 'Corresponde al navegador web del cliente (Chrome o Firefox) instalado en los equipos de la red local. Envía peticiones HTTP al servidor y renderiza las respuestas HTML o procesa las respuestas JSON en el caso de peticiones AJAX.'),
    ('Capa de entrada — Front Controller.', 'El archivo public/index.php actúa como punto de entrada único. Carga las variables de entorno desde .env, inicializa la configuración de la aplicación, resuelve la ruta solicitada, aplica el middleware de autenticación y CSRF, e instancia el controlador correspondiente para ejecutar el método requerido.'),
    ('Capa de middleware.', 'Compuesta por Auth.php y Csrf.php. El middleware de autenticación verifica la existencia de una sesión activa, controla el rol del usuario y gestiona el timeout de inactividad. El middleware CSRF valida los tokens en las peticiones POST.'),
    ('Capa de controladores.', 'El sistema dispone de diez controladores: AuthController, DashboardController, SolicitudController, PersonaController, CupoController, ReporteController, ConfigController, SedeController, MotivoRamoController y EstadoSolicitudController. Los controladores coordinan el flujo de la petición y retornan vistas HTML o respuestas JSON.'),
    ('Capa de servicios.', 'Seis servicios encapsulan la lógica de negocio compleja: SolicitudService, CupoService, PersonaService, PdfService, ReporteService y MaestroImportService. Esta capa es independiente de los controladores y puede ser reutilizada desde diferentes puntos del sistema.'),
    ('Capa de modelos.', 'Nueve modelos proveen acceso directo a la base de datos mediante PDO con prepared statements: Persona, Solicitud, Sede, MotivoRamo, EstadoSolicitud, CupoSede, Configuracion, Usuario y Database (Singleton de conexión).'),
]
for code, text in layers:
    body(text, bold_prefix=code, first=True)

h3('4.1.2 Componentes Principales del Sistema')
body('La tabla siguiente resume los componentes técnicos más relevantes del sistema, el archivo donde se implementan y la responsabilidad específica de cada uno.', first=True)
apa_table(12, 'Componentes principales del sistema y sus responsabilidades',
    ['Componente', 'Archivo(s)', 'Responsabilidad'],
    [
        ['Front Controller','public/index.php','Punto de entrada único. Enrutamiento, middleware y manejo de excepciones globales'],
        ['Router','public/index.php (arrays de rutas)','Mapeo de URI a Controlador::método con control de acceso por rol'],
        ['Middleware Auth','app/middleware/Auth.php','Verificación de sesión, control de roles y timeout de inactividad'],
        ['Middleware CSRF','app/middleware/Csrf.php','Generación y validación de tokens CSRF por sesión'],
        ['Database Singleton','app/models/Database.php','Única instancia de conexión PDO a MySQL por petición'],
        ['Response Helper','app/helpers/Response.php','Estandarización de respuestas JSON y redirecciones HTTP'],
        ['Session Helper','app/helpers/Session.php','Abstracción de sesiones PHP con soporte para mensajes flash'],
        ['Validator','app/helpers/Validator.php','Validación y sanitización de datos de entrada en formularios'],
        ['DateHelper','app/helpers/DateHelper.php','Cálculo de períodos (mensual/semanal) y formateo de fechas'],
        ['BarcodeParser','app/helpers/BarcodeParser.php','Parsing de la entrada del escáner de cédula (código de barras PDF417)'],
        ['Env Helper','app/helpers/Env.php','Carga de variables de entorno desde el archivo .env'],
    ]
)

h2('4.2 Diseño de la Base de Datos')
body('La base de datos del sistema se denominó flores_db y fue diseñada con charset utf8mb4 y collation utf8mb4_unicode_ci para garantizar el soporte correcto de caracteres especiales del español. Comprende un total de ocho tablas que representan las entidades del dominio del negocio.', first=True)

h3('4.2.1 Modelo Lógico de la Base de Datos')
body('Las dependencias entre tablas se organizan de la siguiente manera: la tabla sedes es referenciada por personas, solicitudes y cupos_sede; la tabla personas es referenciada por solicitudes; las tablas motivos_ramo y estados_solicitud son referenciadas por solicitudes. Las tablas usuarios y configuracion son entidades independientes sin claves foráneas externas.', first=True)
figure_ph(6, 'Modelo lógico de la base de datos flores_db con relaciones entre tablas.')

h3('4.2.2 Diccionario de Datos')
body('El diccionario de datos documenta la estructura detallada de cada tabla, incluyendo el nombre de cada campo, su tipo de dato, las restricciones aplicadas y una descripción de su propósito dentro del modelo de datos del sistema.', first=True)

h4('Tabla: usuarios')
body('Almacena las credenciales y datos de los usuarios que acceden al sistema.', first=True)
apa_table(13, 'Estructura de la tabla usuarios',
    ['Campo', 'Tipo', 'Restricciones', 'Descripción'],
    [
        ['id','INT UNSIGNED','PK, AUTO_INCREMENT','Identificador único del usuario'],
        ['username','VARCHAR(50)','UNIQUE, NOT NULL','Nombre de usuario para inicio de sesión'],
        ['password_hash','VARCHAR(255)','NOT NULL','Hash BCRYPT de la contraseña del usuario'],
        ['nombre','VARCHAR(100)','NOT NULL','Nombre completo del usuario del sistema'],
        ['rol','ENUM(\'admin\',\'operador\')','NOT NULL','Rol del usuario: admin u operador'],
        ['activo','TINYINT(1)','DEFAULT 1','Estado: 1 = activo, 0 = inactivo'],
        ['ultimo_login','DATETIME','NULL','Marca de tiempo del último acceso exitoso'],
        ['created_at','DATETIME','DEFAULT NOW()','Fecha y hora de creación del registro'],
    ]
)

h4('Tabla: sedes')
body('Almacena las sedes de la organización donde se realizan las solicitudes de ramos.', first=True)
apa_table(14, 'Estructura de la tabla sedes',
    ['Campo', 'Tipo', 'Restricciones', 'Descripción'],
    [
        ['id','INT UNSIGNED','PK, AUTO_INCREMENT','Identificador único de la sede'],
        ['nombre','VARCHAR(100)','NOT NULL','Nombre descriptivo de la sede'],
        ['codigo','VARCHAR(20)','UNIQUE, NOT NULL','Código interno de identificación'],
        ['direccion','VARCHAR(200)','NULL','Dirección física de la sede (opcional)'],
        ['activo','TINYINT(1)','DEFAULT 1','Estado: 1 = activa, 0 = inactiva'],
        ['created_at','DATETIME','DEFAULT NOW()','Fecha y hora de creación del registro'],
    ]
)

h4('Tabla: personas')
body('Almacena los datos de los beneficiarios (empleados) que pueden realizar solicitudes de ramos.', first=True)
apa_table(15, 'Estructura de la tabla personas',
    ['Campo', 'Tipo', 'Restricciones', 'Descripción'],
    [
        ['id','INT UNSIGNED','PK, AUTO_INCREMENT','Identificador único del beneficiario'],
        ['tipo_documento','VARCHAR(10)','NOT NULL','Tipo de documento de identidad (CC, CE, etc.)'],
        ['documento','VARCHAR(20)','UNIQUE, NOT NULL','Número de documento de identidad'],
        ['primer_nombre','VARCHAR(60)','NOT NULL','Primer nombre del beneficiario'],
        ['segundo_nombre','VARCHAR(60)','NULL','Segundo nombre (opcional)'],
        ['primer_apellido','VARCHAR(60)','NOT NULL','Primer apellido del beneficiario'],
        ['segundo_apellido','VARCHAR(60)','NULL','Segundo apellido (opcional)'],
        ['telefono','VARCHAR(20)','NULL','Número de teléfono de contacto'],
        ['id_sede','INT UNSIGNED','FK → sedes.id','Sede a la que pertenece el beneficiario'],
        ['empresa','ENUM(\'TANDIL\',\'CREOS\')','NOT NULL','Empresa a la que pertenece el beneficiario'],
        ['activo','TINYINT(1)','DEFAULT 1','Estado: 1 = activo, 0 = dado de baja'],
        ['created_at','DATETIME','DEFAULT NOW()','Fecha y hora de creación del registro'],
    ]
)

h4('Tabla: motivos_ramo')
body('Catálogo de los motivos válidos para la solicitud de un ramo floral.', first=True)
apa_table(16, 'Estructura de la tabla motivos_ramo',
    ['Campo', 'Tipo', 'Restricciones', 'Descripción'],
    [
        ['id','INT UNSIGNED','PK, AUTO_INCREMENT','Identificador único del motivo'],
        ['nombre','VARCHAR(100)','NOT NULL','Nombre descriptivo del motivo de solicitud'],
        ['requiere_detalle','TINYINT(1)','DEFAULT 0','Indica si el motivo requiere campo de detalle adicional'],
        ['orden','INT','DEFAULT 0','Orden de visualización en el selector del formulario'],
        ['activo','TINYINT(1)','DEFAULT 1','Estado: 1 = activo, 0 = inactivo'],
    ]
)

h4('Tabla: estados_solicitud')
body('Catálogo de los estados posibles para el ciclo de vida de una solicitud de ramo.', first=True)
apa_table(17, 'Estructura de la tabla estados_solicitud',
    ['Campo', 'Tipo', 'Restricciones', 'Descripción'],
    [
        ['id','INT UNSIGNED','PK, AUTO_INCREMENT','Identificador único del estado'],
        ['nombre','VARCHAR(50)','NOT NULL','Nombre del estado (Pendiente, Aprobada, Entregada, etc.)'],
        ['color','VARCHAR(7)','NOT NULL','Código hexadecimal del color para la representación visual'],
        ['orden','INT','DEFAULT 0','Orden lógico del estado dentro del ciclo de vida'],
    ]
)

h4('Tabla: solicitudes')
body('Tabla central del sistema. Almacena cada solicitud de ramo registrada, con sus datos de identificación, el estado actual y la referencia al beneficiario, sede, motivo y estado correspondientes.', first=True)
apa_table(18, 'Estructura de la tabla solicitudes',
    ['Campo', 'Tipo', 'Restricciones', 'Descripción'],
    [
        ['id','INT UNSIGNED','PK, AUTO_INCREMENT','Identificador único de la solicitud'],
        ['persona_id','INT UNSIGNED','FK → personas.id, NOT NULL','Beneficiario que realizó la solicitud'],
        ['fecha_solicitud','DATE','NOT NULL','Fecha en que se registró la solicitud'],
        ['id_sede','INT UNSIGNED','FK → sedes.id, NOT NULL','Sede desde la cual se originó la solicitud'],
        ['nombre_destinatario','VARCHAR(150)','NOT NULL','Nombre de la persona a quien va destinado el ramo'],
        ['id_motivo','INT UNSIGNED','FK → motivos_ramo.id, NOT NULL','Motivo de la solicitud del ramo'],
        ['motivo_otro','VARCHAR(200)','NULL','Detalle adicional cuando el motivo requiere especificación'],
        ['observaciones','TEXT','NULL','Observaciones opcionales del operador al registrar la solicitud'],
        ['id_estado','INT UNSIGNED','FK → estados_solicitud.id, NOT NULL','Estado actual de la solicitud en su ciclo de vida'],
        ['created_at','DATETIME','DEFAULT NOW()','Marca de tiempo de creación del registro'],
        ['updated_at','DATETIME','NULL','Marca de tiempo de la última actualización del registro'],
    ]
)

h4('Tabla: cupos_sede')
body('Registra el cupo máximo asignado a cada sede por período, permitiendo el control de disponibilidad en tiempo real.', first=True)
apa_table(19, 'Estructura de la tabla cupos_sede',
    ['Campo', 'Tipo', 'Restricciones', 'Descripción'],
    [
        ['id','INT UNSIGNED','PK, AUTO_INCREMENT','Identificador único del registro de cupo'],
        ['id_sede','INT UNSIGNED','FK → sedes.id, NOT NULL','Sede a la que corresponde el cupo'],
        ['periodo','DATE','NOT NULL','Fecha de inicio del período al que aplica el cupo'],
        ['cupo_maximo','INT UNSIGNED','NOT NULL','Número máximo de solicitudes permitidas en la sede para el período'],
        ['notificado','TINYINT(1)','DEFAULT 0','Indica si se ha notificado al administrador sobre el agotamiento del cupo'],
    ]
)

h4('Tabla: configuracion')
body('Tabla de parámetros del sistema en formato clave-valor. Almacena la configuración global incluyendo el tipo de período, el cupo por defecto y los datos institucionales.', first=True)
apa_table(20, 'Estructura de la tabla configuracion',
    ['Campo', 'Tipo', 'Restricciones', 'Descripción'],
    [
        ['id','INT UNSIGNED','PK, AUTO_INCREMENT','Identificador único del parámetro'],
        ['clave','VARCHAR(100)','UNIQUE, NOT NULL','Nombre del parámetro de configuración'],
        ['valor','TEXT','NOT NULL','Valor actual del parámetro'],
        ['descripcion','VARCHAR(255)','NULL','Descripción del propósito del parámetro (solo para administración)'],
    ]
)

h2('4.3 Interfaz de Usuario')
body('El diseño de la interfaz priorizó la usabilidad en el punto de atención, donde el operador debe registrar solicitudes con rapidez y con mínima fricción. Se adoptaron principios de diseño centrado en el usuario, teniendo en cuenta la operación en pantallas táctiles.', first=True)

h3('4.3.1 Diseño UX/UI')
body('Los principios de diseño que guiaron la construcción de la interfaz fueron los siguientes. La eficiencia operativa determinó que el panel del operador minimizara los pasos necesarios para registrar una solicitud en no más de tres acciones. La retroalimentación inmediata garantizó que las validaciones del sistema se mostraran en el momento en que ocurren, con mensajes claros y diferenciados por color. La adaptación táctil aseguró que todos los elementos interactivos tuvieran un tamaño mínimo de 44 px, consistente con las guías de accesibilidad para pantallas táctiles. La jerarquía visual organizó los datos más relevantes en posiciones prominentes de cada pantalla.', first=True)

h3('4.3.2 Wireframes o Prototipos')
body('Los wireframes de las pantallas del sistema fueron elaborados de manera iterativa durante el período de pruebas. Las versiones finales aprobadas por el equipo se documentan en las descripciones de la sección 3.3.3 y deben ser formalizadas en herramienta gráfica para la versión definitiva de esta documentación.', first=True)
figure_ph(7, 'Prototipos de alta fidelidad de las pantallas del sistema aprobadas tras el período de pruebas.')

# ═══════════════════════════════════════════════════════════
#  CAPÍTULO 5
# ═══════════════════════════════════════════════════════════
h1('5. Plan de Desarrollo')
body('El plan de desarrollo describe la metodología adoptada para organizar y ejecutar el proceso de construcción del sistema, las fases e iteraciones que estructuraron el trabajo, y las herramientas utilizadas durante el desarrollo.', first=True)

h2('5.1 Metodología de Desarrollo')
body('La elección de la metodología respondió a las características del proyecto: un equipo unipersonal, un cliente con disponibilidad limitada para reuniones formales y un alcance relativamente acotado con requisitos que fueron precisándose durante el desarrollo.', first=True)

h3('5.1.1 Metodología Utilizada')
body('Se adoptó un enfoque de desarrollo iterativo e incremental, inspirado en los principios del desarrollo ágil, adaptado a las condiciones de un proyecto unipersonal. El sistema se construyó por módulos funcionales completos, cada uno de los cuales fue validado con el usuario antes de continuar con el siguiente. No se utilizó ningún framework de gestión de proyectos formal (Scrum, Kanban). El seguimiento del trabajo se realizó mediante listas de tareas y comunicación directa con el área solicitante.', first=True)

h3('5.1.2 Fases de Desarrollo')
body('El desarrollo del sistema se organizó en cinco fases secuenciales, aunque con cierto solapamiento entre ellas para optimizar los tiempos de entrega.', first=True)
apa_table(21, 'Fases del proceso de desarrollo del sistema',
    ['Fase', 'Nombre', 'Actividades principales'],
    [
        ['1','Análisis y levantamiento','Observación del proceso manual, entrevistas con el personal, revisión de documentos físicos, definición de requisitos iniciales'],
        ['2','Diseño','Diseño del modelo de datos, definición de la arquitectura MVC, bosquejo de las pantallas principales, definición de reglas de negocio'],
        ['3','Implementación','Desarrollo de los siete módulos en orden de prioridad: autenticación, personas, solicitudes, cupos, reportes, dashboard, configuración'],
        ['4','Pruebas','Pruebas unitarias de reglas de negocio, pruebas de integración del flujo completo, pruebas de usabilidad con datos reales durante un mes'],
        ['5','Implementación y ajustes','Despliegue en el servidor local de la empresa, capacitación del equipo, corrección de errores detectados en producción'],
    ]
)

h2('5.2 Plan de Iteraciones y Sprints')
body('Dado el carácter unipersonal del equipo, las iteraciones no se formalizaron como sprints con duraciones fijas. El avance se midió por módulos funcionales completados y validados.', first=True)

h3('5.2.1 Cronograma de Actividades')
apa_table(22, 'Cronograma aproximado de desarrollo por módulo',
    ['Módulo / Actividad', 'Estado', 'Observaciones'],
    [
        ['Infraestructura base (MVC, enrutamiento, middleware)','Completado','Base técnica sobre la que se construyeron todos los módulos'],
        ['Autenticación y control de acceso','Completado','Primer módulo desarrollado por ser requisito de todos los demás'],
        ['Gestión de personas (CRUD + importación Excel)','Completado','Incluye importación masiva con previsualización'],
        ['Módulo de solicitudes (panel táctil + validaciones)','Completado','Módulo más complejo; incluyó integración con escáner de cédula'],
        ['Control de cupos por sede','Completado','Implementado en paralelo con el módulo de solicitudes'],
        ['Generación de reportes PDF','Completado','Utilizando la librería FPDF con diseño corporativo'],
        ['Dashboard con estadísticas y polling','Completado','Actualización automática mediante peticiones AJAX periódicas'],
        ['Módulo de configuración y catálogos','Completado','Gestión de sedes, motivos, estados y parámetros globales'],
        ['Período de pruebas con datos reales','Completado','Un mes de operación paralela con el proceso anterior'],
        ['Corrección de errores y ajustes post-pruebas','En curso','Ajustes menores identificados durante el período de pruebas'],
    ]
)

h3('5.2.2 Responsabilidades del Equipo de Desarrollo')
body('El proyecto fue desarrollado por un único integrante, quien asumió la totalidad de los roles del proceso de desarrollo.', first=True)
apa_table(23, 'Responsabilidades del equipo de desarrollo',
    ['Persona', 'Rol', 'Responsabilidades'],
    [
        ['Mario Alexander Cañola Cano','Analista, Desarrollador y Arquitecto de Software','Levantamiento de requisitos, diseño de arquitectura y base de datos, implementación de todos los módulos, pruebas, documentación técnica y soporte post-implementación'],
    ]
)

h2('5.3 Herramientas de Desarrollo')
body('El conjunto de herramientas utilizado durante el desarrollo fue seleccionado con base en la disponibilidad, la compatibilidad con el entorno tecnológico de la empresa y la familiaridad del desarrollador con cada una de ellas.', first=True)

h3('5.3.1 IDEs y Frameworks')
apa_table(24, 'Entornos de desarrollo y herramientas de programación utilizadas',
    ['Herramienta', 'Uso'],
    [
        ['Visual Studio Code','Editor de código principal utilizado durante todo el desarrollo'],
        ['Laragon','Entorno de desarrollo local con Apache, MySQL y PHP integrados'],
        ['Composer','Gestión de dependencias PHP (FPDF, PhpSpreadsheet)'],
        ['TablePlus / phpMyAdmin','Administración visual de la base de datos MySQL durante el desarrollo'],
        ['Postman','Pruebas de los endpoints AJAX del sistema durante el desarrollo'],
    ]
)

h3('5.3.2 Sistemas de Control de Versiones')
body('El código fuente del sistema se gestionó mediante Git como sistema de control de versiones, con un repositorio local en el equipo de desarrollo. No se utilizó una plataforma de repositorios remotos dado el carácter interno del proyecto, aunque se recomienda su adopción para versiones futuras como medida de respaldo y trazabilidad del historial de cambios.', first=True)

# ═══════════════════════════════════════════════════════════
#  CAPÍTULO 6
# ═══════════════════════════════════════════════════════════
h1('6. Pruebas y Validación')
body('Las pruebas y la validación garantizan que el sistema implementado cumple con los requisitos especificados y que su comportamiento es el esperado en condiciones reales de uso. Este capítulo documenta el plan de pruebas ejecutado, los resultados obtenidos y el proceso de validación con los usuarios finales.', first=True)

h2('6.1 Plan de Pruebas')
body('El plan de pruebas definió los tipos de verificación a realizar, los criterios de aceptación y los casos de prueba prioritarios para cada módulo del sistema. La estrategia priorizó las pruebas funcionales de las reglas de negocio críticas y las pruebas de usabilidad con el equipo operativo real.', first=True)

h3('6.1.1 Tipos de Pruebas')
apa_table(25, 'Tipos de pruebas realizadas sobre el sistema',
    ['Tipo de prueba', 'Descripción', 'Módulos cubiertos'],
    [
        ['Pruebas funcionales','Verificación de que cada funcionalidad produce el resultado esperado según los requisitos','Todos los módulos'],
        ['Pruebas de reglas de negocio','Verificación específica de las validaciones críticas: control de cupos, restricción de período, bloqueo por cupo agotado','Solicitudes, Cupos'],
        ['Pruebas de seguridad','Verificación de la protección CSRF, almacenamiento seguro de contraseñas y restricción de acceso por rol','Autenticación, todos'],
        ['Pruebas de usabilidad táctil','Verificación del comportamiento del panel del operador en pantalla táctil de 10 pulgadas','Solicitudes (operador)'],
        ['Pruebas de integración con escáner','Verificación de la lectura correcta del código de barras PDF417 de la cédula de ciudadanía','Solicitudes'],
        ['Pruebas de generación de PDF','Verificación del contenido, formato y completitud de los reportes generados','Reportes'],
        ['Pruebas de importación Excel','Verificación del procesamiento correcto de archivos con distintos volúmenes y casos límite','Personas'],
        ['Pruebas de aceptación (UAT)','Operación del sistema durante un mes con datos reales por parte del equipo de la empresa','Sistema completo'],
    ]
)

h3('6.1.2 Casos de Prueba')
body('A continuación se documentan los casos de prueba más relevantes para las reglas de negocio críticas del sistema.', first=True)
apa_table(26, 'Casos de prueba de las reglas de negocio críticas',
    ['ID', 'Caso de prueba', 'Condición de entrada', 'Resultado esperado'],
    [
        ['CP-01','Solicitud con cupo disponible','Sede con cupo no agotado; beneficiario sin solicitud activa en el período','La solicitud se registra correctamente con estado "Pendiente"'],
        ['CP-02','Solicitud con cupo agotado','Sede con cupo utilizado igual al cupo máximo','El sistema muestra mensaje de cupo agotado y bloquea el registro'],
        ['CP-03','Solicitud duplicada en el período','Beneficiario con una solicitud activa en el período vigente','El sistema muestra la solicitud existente e impide registrar una nueva'],
        ['CP-04','Búsqueda por documento existente','Número de documento registrado en el sistema','El sistema muestra los datos del beneficiario en menos de 1 segundo'],
        ['CP-05','Búsqueda por documento inexistente','Número de documento no registrado','El sistema muestra mensaje de persona no encontrada y ofrece opción de registro'],
        ['CP-06','Lectura de cédula con escáner','Cédula colombiana pasada por el escáner PDF417','El sistema extrae automáticamente el número de documento y realiza la búsqueda'],
        ['CP-07','Cambio de estado de solicitud','Solicitud en estado "Pendiente"; administrador cambia a "Aprobada"','El estado se actualiza y la tabla refleja el nuevo estado con su color'],
        ['CP-08','Generación de PDF sin solicitudes','Rango de fechas sin solicitudes registradas','El sistema informa que no hay datos para el rango seleccionado'],
        ['CP-09','Importación Excel con duplicados','Archivo Excel con documentos ya registrados en el sistema','El sistema identifica los duplicados en la previsualización y los excluye'],
        ['CP-10','Expiración de sesión','Usuario inactivo por más de 30 minutos','El sistema redirige al login con mensaje de sesión expirada'],
    ]
)

h2('6.2 Resultados de Pruebas')
body('Las pruebas se ejecutaron en dos etapas: pruebas internas por el desarrollador sobre datos sintéticos, y pruebas de aceptación con el equipo de la empresa durante un mes de operación real.', first=True)

h3('6.2.1 Registro de Errores Encontrados')
body('Durante el período de pruebas se identificaron y corrigieron los siguientes errores, clasificados por su nivel de severidad.', first=True)
apa_table(27, 'Errores identificados y corregidos durante el período de pruebas',
    ['ID', 'Descripción del error', 'Severidad', 'Estado'],
    [
        ['ERR-01','El escáner enviaba caracteres adicionales al final de la cadena en algunos modelos, provocando que la búsqueda no encontrara al beneficiario','Alta','Corregido'],
        ['ERR-02','El cálculo del cupo utilizado incluía solicitudes en estado "Cancelada" y "Rechazada", sobrecontando el uso real del cupo','Alta','Corregido'],
        ['ERR-03','La paginación del listado con filtros activos perdía los filtros al cambiar de página','Media','Corregido'],
        ['ERR-04','La generación del PDF fallaba cuando el nombre del destinatario contenía caracteres especiales no soportados por la configuración inicial de FPDF','Media','Corregido'],
        ['ERR-05','La importación masiva desde Excel no validaba el campo de sede cuando no correspondía a ninguna sede registrada','Alta','Corregido'],
        ['ERR-06','El cupo máximo configurable no validaba que el nuevo valor no fuera inferior al número de solicitudes activas ya registradas para esa sede en el período','Media','Corregido'],
    ],
    note='Severidad Alta: errores que bloqueaban una funcionalidad principal. Severidad Media: errores que afectaban parcialmente la experiencia sin impedir la operación.'
)

h3('6.2.2 Mejoras Implementadas')
body('Adicionalmente a la corrección de los errores documentados, a lo largo del desarrollo y el período de pruebas se identificaron y aplicaron las siguientes mejoras al diseño original del sistema.', first=True)
body('Se incorporó la opción de registrar un nuevo beneficiario directamente desde el panel táctil de autoatención, sin necesidad de navegar a la sección de personas, eliminando una fricción operativa detectada durante las pruebas de usabilidad. Se añadió una barra de progreso visual al panel de cupos por sede, que permite al administrador identificar rápidamente el estado de cada sede sin interpretar números. Se implementó la actualización automática del dashboard mediante polling, en respuesta a la necesidad expresada por el administrador de monitorear el estado de las solicitudes sin recargar la página manualmente.')
body('El modelo de período de conteo de solicitudes fue ajustado de un esquema de 30 días continuos a un esquema estrictamente semanal (lunes a domingo), alineado con el flujo operativo real de la empresa. Como complemento, se implementó un evento MySQL automático (reiniciar_cupos_semanal) que se ejecuta cada lunes a las 06:00 a.m. y crea de manera autónoma los registros de cupos para la nueva semana en todas las sedes activas, eliminando la necesidad de intervención manual del administrador para el reinicio de cupos.')
body('Se optimizó el intervalo de actualización (heartbeat) del dashboard y del listado de solicitudes de 5 a 10 segundos, reduciendo la carga de peticiones al servidor sin afectar la percepción de actualización en tiempo real. Se reforzó la validación del campo documento en la actualización de beneficiarios para impedir la asignación de un número de documento ya existente en otro registro. La lógica de importación masiva desde Excel fue actualizada para incluir en el mensaje de resultado el conteo de registros omitidos por estar previamente dados de baja, mejorando la trazabilidad del proceso de importación. Finalmente, se mejoró la gestión de sesión en el middleware de autenticación, incorporando redirección diferenciada para solicitudes AJAX (respuesta JSON con código 401) frente a peticiones de página completa (redirección HTTP al login), evitando respuestas HTML inesperadas en llamadas asíncronas.')
body('Se implementó una ventana de gracia semanal en la validación de período por beneficiario. Dado que la empresa prepara y entrega los ramos los días lunes, los beneficiarios cuyo vencimiento de los 30 días caiga entre el lunes y el jueves de la semana en curso pueden adelantar su solicitud al inicio de esa semana, sin necesidad de esperar al día exacto de vencimiento. La regla permite incluir la solicitud en la preparación del lunes y evita que el beneficiario pierda un ciclo completo de entrega por diferencias de uno a tres días en su calendario de elegibilidad. La lógica se implementó en el método enVentanaGracia() del modelo Solicitud, que es invocado tanto por tieneSolicitudEnPeriodo() como por getSolicitudActivaEnPeriodo(), garantizando coherencia entre la verificación de creación y la validación en el panel de autoatención.')
body('Se implementó el bloqueo explícito de solicitudes para beneficiarios con estado inactivo. La verificación ocurre en el controlador durante la búsqueda por documento (endpoint /solicitudes/buscar-persona): cuando el beneficiario existe pero está inactivo, el sistema retorna un indicador específico que distingue esta condición del caso de persona no encontrada, evitando que el panel táctil ofrezca registrar una nueva persona con ese documento. La validación opera en dos capas —controlador y servicio— aplicando defensa en profundidad: el controlador provee la respuesta inmediata al operador y el servicio garantiza la integridad de los datos ante cualquier llamada directa al endpoint.')
body('La opción de cambio de contraseña fue restringida al rol administrador. Se ocultó el botón correspondiente del menú desplegable del usuario para el rol operador y se agregó verificación de rol en el controlador antes de procesar la solicitud al endpoint /perfil/password. Con este ajuste, el cambio de contraseña queda centralizado en la sección de Usuarios de la pantalla de configuración, donde el administrador puede actualizar la contraseña de cualquier cuenta sin requerir que el operador conozca su contraseña actual.')

h2('6.3 Validación con el Cliente')
body('La validación con el cliente se realizó mediante la operación paralela del sistema durante un período de un mes, en el cual el equipo de la empresa lo utilizó como herramienta principal del proceso mientras el proceso manual anterior se mantuvo como respaldo de contingencia.', first=True)

h3('6.3.1 Proceso de Aceptación del Software')
body('Al término del período de pruebas, el equipo operativo de Flores el Tandil confirmó que el sistema cumplía con las expectativas funcionales establecidas al inicio del proyecto. Se verificó que las validaciones de cupo y período funcionaban correctamente en condiciones reales, que el panel táctil del operador era operativo en el dispositivo disponible en el punto de atención y que los reportes PDF generados satisfacían los requerimientos del área de elaboración de ramos.', first=True)

h3('6.3.2 Feedback Recibido')
body('La retroalimentación recibida del equipo operativo durante el período de pruebas puede resumirse en los siguientes puntos. El panel táctil fue valorado positivamente por su simplicidad y velocidad en comparación con el proceso anterior. Se solicitó la adición del registro de nueva persona desde el panel de solicitudes, mejora que fue implementada durante el mismo período de pruebas. Se identificó como deseable la generación automática de reportes periódicos, funcionalidad que fue documentada como limitación LF-04 para consideración en versiones futuras del sistema.', first=True)

# ═══════════════════════════════════════════════════════════
#  CAPÍTULO 7
# ═══════════════════════════════════════════════════════════
h1('7. Implementación y Mantenimiento')
body('Este capítulo describe la estrategia de despliegue del sistema en el entorno de producción de Flores el Tandil, el plan de capacitación del equipo usuario y las políticas de mantenimiento y soporte técnico que garantizan la continuidad operativa del sistema una vez en producción.', first=True)

h2('7.1 Estrategia de Implementación')
body('La implementación del sistema requirió la configuración del entorno de servidor en las instalaciones de la empresa y la migración de los datos históricos relevantes al nuevo sistema de base de datos.', first=True)

h3('7.1.1 Servidores y Hosting')
body('El sistema opera sobre infraestructura local dentro de las instalaciones de Flores el Tandil. No se requiere contratación de servicios de hosting externo ni conexión a internet para su funcionamiento.', first=True)
apa_table(28, 'Configuración del entorno de servidor de producción',
    ['Componente', 'Configuración'],
    [
        ['Sistema operativo del servidor','Windows (equipo existente designado como servidor de la red local)'],
        ['Servidor web','Apache HTTP Server provisto mediante Laragon'],
        ['Lenguaje de servidor','PHP 8.0 o superior'],
        ['Motor de base de datos','MySQL 5.7 o superior'],
        ['Acceso de clientes','Cualquier equipo conectado a la red local mediante Chrome o Firefox actualizado'],
        ['Dirección de acceso','IP local del servidor en la red interna (sin dominio público)'],
        ['Almacenamiento de archivos','Sistema de archivos local del servidor en el directorio /storage/pdfs/'],
    ]
)
tech_note('Dado que el sistema opera en un único servidor sin redundancia (RT-02), se recomienda establecer un procedimiento de copia de seguridad periódica del directorio del sistema y de la base de datos MySQL, con almacenamiento en un dispositivo externo o equipo diferente al servidor.')

h3('7.1.2 Migración de Datos')
body('La migración de datos implicó la carga inicial de los catálogos del sistema (sedes, motivos de ramo, estados de solicitud) y el registro de los beneficiarios activos de la organización. No existían datos históricos de solicitudes en formato digital, por lo que la migración se limitó a los datos maestros de personas.', first=True)
body('La carga de beneficiarios se realizó mediante la funcionalidad de importación masiva desde Excel (RF-07), con un archivo preparado por el área administrativa con los datos del personal activo de las empresas Tandil y Creos. Se verificó la integridad de los datos importados mediante revisión manual de una muestra representativa de registros antes de poner el sistema en operación.')

h2('7.2 Plan de Capacitación')
body('La capacitación del equipo usuario se realizó de manera presencial durante la fase de implementación, diferenciando el contenido según el rol de cada usuario dentro del sistema.', first=True)

h3('7.2.1 Manuales Técnicos y de Usuario')
body('Se identifican dos tipos de documentación de usuario necesarios para la operación autónoma del sistema.', first=True)
apa_table(29, 'Documentos de usuario identificados para el sistema',
    ['Documento', 'Destinatario', 'Contenido principal', 'Estado'],
    [
        ['Manual del Operador','Personal de oficina (operador)','Registro de solicitudes, búsqueda de beneficiarios, uso del escáner, mensajes de error comunes','Pendiente de formalización'],
        ['Manual del Administrador','Responsable del área (administrador)','Gestión de solicitudes, cambio de estados, configuración de cupos, generación de reportes, gestión de personas y catálogos','Pendiente de formalización'],
        ['Manual Técnico','Soporte técnico','Arquitectura del sistema, estructura de la base de datos, procedimiento de respaldo, configuración del servidor, resolución de incidencias comunes','Presente documento'],
    ]
)

h3('7.2.2 Sesiones de Formación')
body('La capacitación se realizó en dos sesiones presenciales en las instalaciones de la empresa. La primera sesión cubrió el uso del panel táctil, la búsqueda de beneficiarios, el registro de solicitudes y la interpretación de los mensajes del sistema para el personal operativo. La segunda sesión cubrió la gestión del ciclo de vida de solicitudes, la configuración de cupos, la generación de reportes PDF y la administración de catálogos y personas para el administrador del área. Ambas sesiones incluyeron práctica directa sobre el sistema con datos reales.', first=True)

h2('7.3 Mantenimiento del Software')
body('El mantenimiento del sistema está a cargo del desarrollador, Mario Alexander Cañola Cano, quien provee soporte técnico de manera directa. Se definen dos modalidades: el mantenimiento correctivo, orientado a la resolución de errores detectados en producción, y el mantenimiento evolutivo, orientado a la incorporación de nuevas funcionalidades en versiones futuras.', first=True)

h3('7.3.1 Soporte Técnico')
body('El soporte técnico se provee bajo demanda, sin un contrato formal de niveles de servicio (SLA) en la versión actual del sistema. Para incidencias que impidan la operación del sistema, se establece como medida de contingencia la reanudación temporal del proceso manual hasta que el sistema sea restaurado. Las incidencias comunes y sus procedimientos de resolución serán documentados en el Manual Técnico, incluyendo el procedimiento de reinicio del servidor Apache, la verificación del estado del servicio MySQL y el procedimiento de restauración desde copia de seguridad.', first=True)

h3('7.3.2 Estrategia de Actualización')
body('Las actualizaciones del sistema se planificarán en función de la retroalimentación acumulada durante la operación. El sistema incorpora un mecanismo de mantenimiento automático mediante el evento MySQL reiniciar_cupos_semanal, que ejecuta cada lunes a las 06:00 a.m. para crear los registros de cupos de la nueva semana sin intervención del administrador; este mecanismo requiere que el Event Scheduler de MySQL esté habilitado en el servidor (SET GLOBAL event_scheduler = ON) y debe verificarse que permanezca activo tras reinicios del servicio MySQL.', first=True)
body('Se identifican como candidatas para la siguiente versión del sistema las siguientes funcionalidades: historial de cambios de estado por solicitud (LF-02), generación automática y distribución del reporte PDF semanal vía correo electrónico (LF-04) y generación de remisión digital de entrega como comprobante al beneficiario (LF-05). Cualquier actualización deberá ser probada en un entorno de desarrollo separado antes de su despliegue en el servidor de producción, con copia de seguridad previa de la base de datos.')

# ═══════════════════════════════════════════════════════════
#  CAPÍTULO 8
# ═══════════════════════════════════════════════════════════
h1('8. Conclusiones y Recomendaciones')
body('El capítulo final sintetiza los resultados alcanzados con el desarrollo e implementación del Sistema de Solicitud de Ramos, documenta los desafíos enfrentados durante el proceso, extrae las lecciones aprendidas y formula recomendaciones para el mantenimiento y la evolución futura del sistema.', first=True)

h2('8.1 Evaluación del Proyecto')
body('La evaluación del proyecto contrasta los objetivos definidos al inicio del desarrollo con los resultados efectivamente obtenidos, identificando tanto los logros consolidados como los aspectos que permanecen como áreas de mejora.', first=True)

h3('8.1.1 Logros Alcanzados')
body('El Sistema de Solicitud de Ramos cumplió con la totalidad de los objetivos específicos planteados en la sección 1.2.2 del presente documento. El proceso de solicitud de ramos en Flores el Tandil fue digitalizado en su totalidad, eliminando el uso de formularios en papel en todas las etapas del ciclo. Los siete módulos funcionales fueron implementados y validados con el equipo de la empresa durante un período de pruebas de un mes con datos reales.', first=True)
body('Se destaca el cumplimiento de los siguientes logros: la implementación exitosa del motor de validación de reglas de negocio, que garantiza el control de cupos y la restricción de período en tiempo real; la integración del escáner de cédula de ciudadanía mediante lectura de código de barras PDF417, que redujo significativamente el tiempo de atención en el punto de solicitud; y el panel de estadísticas con actualización automática, que provee visibilidad en tiempo real del proceso al área administrativa.')

h3('8.1.2 Desafíos Encontrados')
body('El principal desafío técnico fue la integración con el escáner de cédula de ciudadanía, que requirió el desarrollo de un parser personalizado (BarcodeParser.php) para interpretar correctamente el formato del código de barras PDF417, con normalización de los caracteres adicionales que diferentes modelos de escáner envían al final de la cadena leída.', first=True)
body('Desde el punto de vista del proceso, el principal desafío fue la identificación de las reglas de negocio implícitas que no estaban documentadas en ningún lugar. En particular, la lógica de cálculo del cupo utilizado —que debía excluir las solicitudes canceladas y rechazadas— no era evidente inicialmente y fue identificada durante las pruebas.')

h2('8.2 Lecciones Aprendidas')
body('El proceso de desarrollo del sistema dejó un conjunto de aprendizajes que se documentan tanto para referencia del propio equipo como para orientar el desarrollo de proyectos similares en el futuro.', first=True)

h3('8.2.1 Mejores Prácticas Aplicadas')
body('La separación de la lógica de negocio en una capa de servicios independiente de los controladores demostró ser una decisión acertada: cuando se identificaron errores en las reglas de negocio, las correcciones se aplicaron en un único punto del código sin necesidad de modificar múltiples controladores. Esta experiencia confirma la utilidad del Principio de Responsabilidad Única en proyectos de esta escala.', first=True)
body('El prototipado iterativo con el usuario real, aunque no formalizado como metodología, permitió detectar tempranamente la necesidad de registrar personas desde el panel de solicitudes, una funcionalidad que no había sido identificada en el levantamiento inicial de requisitos pero que resultó crítica para la fluidez del proceso en el punto de atención.')

h3('8.2.2 Recomendaciones para Futuras Versiones')
body('Con base en la experiencia del desarrollo y en la retroalimentación recibida durante el período de pruebas, se formulan las siguientes recomendaciones para la evolución del sistema.', first=True)
for code, text in [
    ('Implementar respaldo automatizado de la base de datos.', 'La ausencia de copias de seguridad automáticas (RT-03) constituye el riesgo operativo más significativo del sistema en su estado actual. Se recomienda configurar una tarea programada en el servidor que realice una copia diaria de la base de datos MySQL en un directorio externo al servidor principal.'),
    ('Incorporar historial de cambios de estado.', 'La limitación LF-02, relativa a la ausencia de trazabilidad de las transiciones de estado, fue señalada como una necesidad por el área administrativa. Su implementación requiere la adición de una tabla historial_estados con las columnas de solicitud, estado anterior, estado nuevo, usuario que realizó el cambio y marca de tiempo.'),
    ('Evaluar la migración a un servidor dedicado.', 'La dependencia de un único equipo como servidor (RT-02) representa un punto único de falla. Para una operación más robusta, se recomienda evaluar la migración del sistema a un servidor dedicado, incluso dentro de la infraestructura local de la empresa.'),
    ('Considerar la generación automática de reportes periódicos.', 'La automatización de la generación y distribución del reporte PDF semanal o mensual, actualmente realizada manualmente por el administrador (LF-04), eliminaría una tarea repetitiva y reduciría el riesgo de omisión del reporte en las fechas esperadas.'),
]:
    body(text, bold_prefix=code, first=True)

h2('8.3 Bibliografía y Referencias')
h3('8.3.1 Fuentes de Información Utilizadas')

refs = [
    'Chart.js Contributors. (2023). Chart.js documentation. https://www.chartjs.org/docs/latest/',
    'Fowler, M. (2002). Patterns of enterprise application architecture. Addison-Wesley Professional.',
    'FPDF Library. (2011). FPDF — Free PDF library for PHP. http://www.fpdf.org/',
    'Institute of Electrical and Electronics Engineers. (1998). IEEE Std 830-1998: IEEE Recommended Practice for Software Requirements Specifications. IEEE.',
    'International Organization for Standardization. (2011). ISO/IEC 25010: Systems and software engineering — Systems and software Quality Requirements and Evaluation (SQuaRE). ISO.',
    'Martin, R. C. (2003). Agile software development: Principles, patterns, and practices. Prentice Hall.',
    'MySQL AB. (2023). MySQL 8.0 reference manual. https://dev.mysql.com/doc/refman/8.0/en/',
    'Open Web Application Security Project. (2021). OWASP Top Ten. https://owasp.org/www-project-top-ten/',
    'PhpOffice. (2023). PhpSpreadsheet documentation. https://phpspreadsheet.readthedocs.io/',
    'The PHP Group. (2023). PHP manual. https://www.php.net/manual/es/',
]
for ref in refs:
    p = doc.add_paragraph()
    pf = p.paragraph_format
    pf.alignment          = WD_ALIGN_PARAGRAPH.JUSTIFY
    pf.left_indent        = Cm(1.27)
    pf.first_line_indent  = Cm(-1.27)   # hanging indent
    pf.space_after        = Pt(6)
    pf.line_spacing_rule  = WD_LINE_SPACING.MULTIPLE
    pf.line_spacing       = 1.5
    _run(p, ref)

print("Caps 3-8 OK")
