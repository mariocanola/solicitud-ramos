# -*- coding: utf-8 -*-
# Contenido capítulos 1-4
# Se importa después de gen_docx.py (comparte doc y helpers en el mismo proceso)

# ═══════════════════════════════════════════════════════════
#  CAPÍTULO 1
# ═══════════════════════════════════════════════════════════
h1('1. Introducción')
body('El presente capítulo establece el marco general del Sistema de Solicitud de Ramos desarrollado para Flores el Tandil. Se describe el contexto organizacional que motivó la iniciativa, se definen los objetivos que orientaron el proceso de desarrollo y se delimitan con precisión las funcionalidades incluidas en el alcance del sistema, así como las restricciones técnicas y funcionales que enmarcan su operación.', first=True)

h2('1.1 Descripción General')
body('Esta sección presenta una visión de conjunto del sistema, abordando tanto su propósito central como las razones que justificaron su desarrollo. Se expone el problema organizacional que el sistema resuelve y el contexto operativo dentro del cual se despliega.', first=True)

h3('1.1.1 Breve Resumen del Proyecto')
body('El Sistema de Solicitud de Ramos es una aplicación web interna desarrollada para Flores el Tandil, empresa ubicada en la ciudad de Tandil. El sistema digitaliza y automatiza el proceso mediante el cual los empleados de la organización solicitan ramos florales como beneficio corporativo, gestionando el ciclo completo desde el registro de la solicitud hasta el control de entrega, eliminando la dependencia del papel en cada etapa del proceso.', first=True)
body('El sistema fue desarrollado por Mario Alexander Cañola Cano, Analista y Desarrollador de Software, bajo un modelo de aplicación web MVC (Modelo-Vista-Controlador) en PHP nativo, con base de datos MySQL, diseñado para operar en red local dentro de las instalaciones de la empresa.')
body('La plataforma centraliza la información de beneficiarios (empleados), controla los cupos disponibles por sede y período, registra el estado de cada solicitud y genera reportes en formato PDF para el área operativa encargada de la elaboración y entrega de los ramos.')

h3('1.1.2 Justificación y Necesidad del Software')
body('Previo al desarrollo del sistema, el proceso de solicitud de ramos en Flores el Tandil operaba de forma completamente manual. El flujo de trabajo implicaba el desplazamiento físico de los empleados a las oficinas asignadas, el diligenciamiento de formularios en papel, el traslado físico de dichos formularios al área de elaboración y la posterior entrega del ramo con una remisión impresa como comprobante. Este modelo generaba múltiples deficiencias operativas documentadas en la tabla siguiente.', first=True)

apa_table(1, 'Problemas identificados en el proceso manual de solicitud de ramos',
    ['Problema identificado', 'Impacto operativo'],
    [
        ['Registro manual en papel', 'Pérdida de información, ilegibilidad y errores de transcripción'],
        ['Sin control de cupos en tiempo real', 'Posibilidad de exceder el límite de ramos permitido por sede'],
        ['Sin restricción por período', 'Un mismo beneficiario podía solicitar más de un ramo en el mismo período'],
        ['Sin trazabilidad del estado', 'Imposibilidad de determinar en qué etapa se encontraba cada solicitud'],
        ['Consumo elevado de papel', 'Contradicción directa con las políticas de sostenibilidad de la organización'],
        ['Sin reportes consolidados', 'El área administrativa carecía de visibilidad centralizada sobre el proceso'],
    ],
    note='Problemas documentados mediante observación directa del proceso anterior al sistema.'
)
body('El sistema resuelve de manera directa cada uno de los problemas enumerados, alineándose con la política organizacional de reducción del consumo de papel como objetivo explícito de Flores el Tandil.', first=True)

h2('1.2 Objetivos')
body('Los objetivos del sistema se definen en dos niveles: el objetivo general, que describe el propósito central en términos globales, y los objetivos específicos, que descomponen ese propósito en resultados concretos y verificables.', first=True)

h3('1.2.1 Objetivo General')
body('Desarrollar un sistema de información web de acceso local que digitalice y automatice el proceso de solicitud, seguimiento y entrega de ramos florales en Flores el Tandil, eliminando el uso de papel en todas las etapas del proceso y garantizando el control de cupos, la trazabilidad de cada solicitud y la generación de reportes consolidados para el área administrativa.', first=True)

h3('1.2.2 Objetivos Específicos')
body('Los objetivos específicos, identificados con el código OE seguido de su número de orden, describen los resultados funcionales concretos que el sistema debía alcanzar para cumplir con el objetivo general.', first=True)
body('OE-01.', bold_prefix='OE-01.', first=True)

oe_list = [
    ('OE-01.', 'Implementar un módulo de registro y gestión de beneficiarios que permita centralizar la información de los empleados de las empresas Tandil y Creos, con soporte para importación masiva desde archivos Excel.'),
    ('OE-02.', 'Desarrollar un panel táctil de autoatención que permita al propio empleado buscar su registro mediante número de documento —digitado manualmente o mediante escáner de cédula (código de barras PDF417)— y registrar su solicitud de manera autónoma.'),
    ('OE-03.', 'Implementar un motor de validación de reglas de negocio que verifique en tiempo real la disponibilidad de cupo por sede y la restricción de una solicitud por beneficiario por período.'),
    ('OE-04.', 'Proveer al administrador una interfaz de seguimiento de solicitudes que permita gestionar los estados del ciclo de vida: Pendiente, Aprobada, Entregada, Rechazada y Cancelada.'),
    ('OE-05.', 'Implementar un módulo de reportes que genere documentos PDF consolidados filtrados por fecha y sede, con diseño corporativo, para uso del área de elaboración y entrega.'),
    ('OE-06.', 'Construir un panel administrativo con estadísticas en tiempo real sobre solicitudes por sede, motivo, estado y período, con actualización automática sin recargar la página.'),
    ('OE-07.', 'Garantizar la seguridad del acceso mediante autenticación basada en roles, protección CSRF, almacenamiento seguro de contraseñas con BCRYPT y gestión de sesiones con tiempo de expiración.'),
    ('OE-08.', 'Diseñar el sistema para operar sobre infraestructura de red local (intranet), instalado en un equipo servidor y accesible desde cualquier equipo de la red interna sin conexión a internet.'),
]
# Remove the incorrect one above
doc.paragraphs[-1]._element.getparent().remove(doc.paragraphs[-1]._element)
for code, text in oe_list:
    body(text, bold_prefix=code, first=True)

h2('1.3 Alcance del Proyecto')
body('El alcance delimita el conjunto de funcionalidades que el sistema cubre en su versión actual (1.0), los actores que participan directa o indirectamente en su operación y las restricciones que condicionan su uso.', first=True)

h3('1.3.1 Funcionalidades Principales del Software')
body('El sistema se organiza en siete módulos funcionales. A continuación se describe el alcance de cada uno.', first=True)

h4('Módulo 1 — Autenticación y Control de Acceso')
for b in [
    'Autenticación mediante usuario y contraseña con gestión de sesiones.',
    'Expiración automática de sesión tras 30 minutos de inactividad.',
    'Dos roles de usuario: Administrador y Operador, con permisos diferenciados.',
    'Protección CSRF en todos los formularios del sistema.',
]:
    bullet(b)

h4('Módulo 2 — Gestión de Beneficiarios')
for b in [
    'Registro individual de empleados con datos personales, sede asignada y empresa (Tandil / Creos).',
    'Edición de datos de beneficiarios registrados.',
    'Baja lógica (desactivación) y reactivación de beneficiarios sin eliminar historial.',
    'Búsqueda por número de documento y nombre.',
    'Importación masiva desde archivo Excel con previsualización antes de confirmar.',
    'Descarga de plantilla Excel para facilitar la carga de datos.',
]:
    bullet(b)

h4('Módulo 3 — Solicitudes')
for b in [
    'Panel táctil de autoatención: el propio empleado digita su número de cédula para solicitar su ramo.',
    'Búsqueda del empleado por número de documento (escritura manual o escaneo de cédula PDF417).',
    'Validación automática de cupo disponible antes de registrar la solicitud.',
    'Validación automática de restricción de período (una solicitud por empleado por período).',
    'Registro de solicitud con motivo, nombre del destinatario y observaciones opcionales.',
    'Listado de solicitudes con filtros por fecha, sede, estado y texto libre (solo administrador).',
    'Gestión de estados: Pendiente → Aprobada / Rechazada / Entregada / Cancelada (solo administrador).',
    'Eliminación de solicitudes por parte del administrador.',
]:
    bullet(b)

h4('Módulo 4 — Control de Cupos')
for b in [
    'Configuración de cupo máximo por sede y período.',
    'Cálculo de cupo utilizado en tiempo real mediante conteo de solicitudes activas.',
    'Bloqueo automático de nuevas solicitudes cuando se agota el cupo de una sede.',
    'Resumen visual del estado de cupos en el panel de configuración.',
]:
    bullet(b)

h4('Módulo 5 — Reportes')
for b in [
    'Generación de PDF consolidado filtrado por rango de fechas y sede.',
    'Agrupación de solicitudes por sede en el PDF generado.',
    'Opción de incluir hojas individuales por solicitud.',
    'Diseño corporativo con paleta de colores institucional.',
]:
    bullet(b)

h4('Módulo 6 — Panel Administrativo')
for b in [
    'Estadísticas totales de solicitudes: general, por sede, por motivo y por estado.',
    'Estadísticas del período actual y de la semana en curso.',
    'Gráficos de distribución (torta y barras) por sede.',
    'Actualización automática mediante polling sin recarga de página.',
]:
    bullet(b)

h4('Módulo 7 — Configuración del Sistema')
for b in [
    'Configuración del tipo de período (mensual o semanal).',
    'Configuración del cupo por defecto para nuevas sedes.',
    'Gestión de catálogos: sedes, motivos de ramo y estados de solicitud.',
    'Configuración del nombre de la organización y empresa destinataria.',
]:
    bullet(b)

h3('1.3.2 Usuarios Finales y Stakeholders')
body('El sistema reconoce cuatro actores con distintos niveles de interacción. El actor principal es el propio empleado beneficiario, quien interactúa directamente con el panel táctil del sistema de manera autónoma, sin necesidad de un intermediario.', first=True)
apa_table(2, 'Actores identificados y su rol en el sistema',
    ['Actor', 'Rol en el sistema', 'Descripción'],
    [
        ['Empleado / Operador', 'Usuario solicitante (autoatención)', 'El propio empleado de Flores el Tandil o empresa Creos que se presenta en el punto de solicitud y digita su número de cédula en el panel táctil para gestionar su ramo de manera autónoma. Es el actor principal del proceso de solicitud.'],
        ['Administrador', 'Usuario administrativo', 'Persona responsable de la gestión del sistema: aprueba solicitudes, configura cupos por sede, genera reportes PDF, administra el catálogo de beneficiarios y la configuración general del sistema.'],
        ['Área de elaboración', 'Receptor de información', 'No interactúa directamente con el sistema. Recibe los reportes PDF generados por el administrador para elaborar y preparar los ramos correspondientes.'],
        ['Mario Alexander Cañola Cano', 'Desarrollador / Soporte técnico', 'Responsable del desarrollo, mantenimiento y soporte técnico del sistema.'],
    ]
)

h3('1.3.3 Limitaciones o Restricciones')
body('Las limitaciones documentadas a continuación corresponden a funcionalidades excluidas del alcance de la versión 1.0.', first=True)

h4('Limitaciones funcionales')
for code, text in [
    ('LF-01 — Sin notificaciones automáticas:', 'El sistema no envía correos ni alertas automáticas. El flujo de comunicación continúa dependiendo del reporte PDF generado manualmente.'),
    ('LF-02 — Sin historial de cambios de estado:', 'El sistema registra el estado actual de una solicitud pero no almacena el historial de transiciones.'),
    ('LF-03 — Sin granularidad por sede en el rol operador:', 'Un operador tiene acceso genérico al panel sin restricción a una sede específica.'),
    ('LF-04 — Reportes bajo demanda únicamente:', 'No es posible programar la generación automática de reportes periódicos.'),
    ('LF-05 — Proceso de entrega no completamente digitalizado:', 'El sistema gestiona estados hasta "Entregada" pero no genera remisión digital equivalente al comprobante en papel.'),
]:
    body(text, bold_prefix=code, first=True)

h4('Restricciones técnicas')
for code, text in [
    ('RT-01 — Operación exclusivamente en red local:', 'El sistema no está diseñado para acceso externo por internet.'),
    ('RT-02 — Dependencia de un único servidor local:', 'No existe redundancia. Si el servidor falla, el sistema no estará disponible.'),
    ('RT-03 — Sin respaldo automatizado de base de datos:', 'La integridad de los datos depende de copias manuales de la base de datos MySQL.'),
    ('RT-04 — Compatibilidad de navegador no verificada en versiones antiguas:', 'El sistema usa HTML5/CSS3/JavaScript moderno; no se garantiza compatibilidad con navegadores desactualizados.'),
]:
    body(text, bold_prefix=code, first=True)

# ═══════════════════════════════════════════════════════════
#  CAPÍTULO 2
# ═══════════════════════════════════════════════════════════
h1('2. Marco Teórico y Estado del Arte')
body('El presente capítulo sitúa el sistema en su contexto teórico y tecnológico. Se analizan los antecedentes del problema, se comparan las alternativas de solución disponibles, se referencian los principios y estándares que fundamentan las decisiones de diseño, y se describen las tecnologías seleccionadas para la implementación.', first=True)

h2('2.1 Antecedentes del Problema')
body('La comprensión del problema que motivó el desarrollo del sistema requiere una descripción precisa del proceso organizacional previo y una evaluación de las alternativas de solución consideradas antes de optar por el desarrollo a medida.', first=True)

h3('2.1.1 Explicación del Problema y Contexto')
body('Flores el Tandil es una empresa del sector floricultor que, como parte de su política de beneficios corporativos, entrega ramos florales a sus empleados en ocasiones específicas: cumpleaños, matrimonios, fallecimiento de familiar, entre otras. Este beneficio aplica también para los empleados de la empresa relacionada Creos.', first=True)
body('Antes del desarrollo del sistema, el proceso operaba con formularios físicos en papel los días lunes, traslado físico de los formularios al área de elaboración entre lunes y jueves, y entrega del ramo con remisión impresa los viernes. No existía ningún mecanismo técnico para prevenir solicitudes duplicadas ni para controlar que el número de solicitudes por sede no superara la capacidad operativa del área de elaboración.')

h3('2.1.2 Comparación con Soluciones Existentes')
body('Antes de tomar la decisión de desarrollar una solución a medida, se evaluaron las alternativas disponibles, resumidas en la tabla siguiente.', first=True)
apa_table(3, 'Comparación entre alternativas de solución evaluadas',
    ['Alternativa', 'Ventajas', 'Desventajas frente al sistema desarrollado'],
    [
        ['Hojas de cálculo (Excel)', 'Familiar; sin costo adicional', 'Sin validaciones automáticas; sin control de cupos; sin restricción de período; sin reportes automáticos'],
        ['Formularios Google / Microsoft Forms', 'Accesibles desde cualquier dispositivo', 'Requieren internet; sin control de cupos; sin gestión de estados del ciclo de vida'],
        ['Software genérico de solicitudes', 'Funcionalidad amplia y documentada', 'Costo de licencia; necesidad de adaptación; complejidad innecesaria'],
        ['Sistema desarrollado a medida', 'Adaptado exactamente al proceso; sin dependencia de internet; control total del dato', 'Requiere mantenimiento interno por parte del desarrollador'],
    ],
    note='La evaluación se realizó con base en los requisitos funcionales y restricciones técnicas identificados durante el levantamiento de información.'
)

h2('2.2 Referentes Teóricos')
body('El diseño del sistema se fundamentó en principios reconocidos de ingeniería de software que guiaron las decisiones de arquitectura, seguridad y mantenibilidad.', first=True)

h3('2.2.1 Principios, Metodologías y Enfoques Aplicables')
body('Patrón MVC (Modelo-Vista-Controlador).', bold_prefix='Patrón MVC (Modelo-Vista-Controlador).', first=True)

principles = [
    ('Patrón MVC (Modelo-Vista-Controlador).', 'El sistema implementa el patrón MVC como estructura central. Este patrón separa la lógica de negocio (Modelo), la presentación al usuario (Vista) y el flujo de control (Controlador), favoreciendo la mantenibilidad y la separación de responsabilidades (Fowler, 2002).'),
    ('Arquitectura Front Controller.', 'El punto de entrada único (public/index.php) actúa como Front Controller, centralizando el enrutamiento, la aplicación de middleware y el manejo de excepciones globales, consistente con frameworks PHP modernos como Laravel y Symfony.'),
    ('Principio de Responsabilidad Única (SRP).', 'La lógica de negocio compleja se separa en una capa de servicios (app/services/) independiente de los controladores. Cada servicio tiene una responsabilidad acotada (Martin, 2003).'),
    ('Modularidad.', 'Cada módulo funcional dispone de su propio controlador, conjunto de vistas y modelos, lo que permite modificar o extender un módulo sin afectar a los demás.'),
    ('Defensa en profundidad.', 'El sistema implementa múltiples capas de seguridad: autenticación en sesión, autorización por rol y ruta, validación en servicios, sanitización en vistas y protección CSRF en formularios (OWASP, 2021).'),
    ('Patrón Singleton para acceso a base de datos.', 'Database.php implementa el patrón Singleton para garantizar una única instancia de conexión PDO durante el ciclo de vida de cada petición.'),
]
# Remove the duplicate paragraph added above
doc.paragraphs[-1]._element.getparent().remove(doc.paragraphs[-1]._element)
for code, text in principles:
    body(text, bold_prefix=code, first=True)

h3('2.2.2 Normas y Estándares de Calidad')
body('El desarrollo y la documentación del sistema tomaron como referencia los siguientes estándares reconocidos en la industria del software.', first=True)
apa_table(4, 'Normas y estándares aplicados en el desarrollo del sistema',
    ['Norma / Estándar', 'Aplicación en el sistema'],
    [
        ['IEEE 830', 'Base para la especificación de requisitos de software documentada en la sección 3.2'],
        ['IEEE 1471 / ISO/IEC 42010', 'Referencia para la descripción de la arquitectura del software (sección 4.1)'],
        ['OWASP Top 10', 'Guía para mitigaciones de seguridad: SQL Injection (PDO), Broken Authentication (BCRYPT), CSRF (tokens), Security Misconfiguration (headers HTTP)'],
        ['ISO/IEC 25010', 'Modelo de calidad de software; referencia para requisitos no funcionales de usabilidad, seguridad, mantenibilidad y portabilidad'],
    ]
)

h2('2.3 Tecnologías Utilizadas')
body('La selección tecnológica respondió a tres criterios: compatibilidad con el entorno de operación local, ausencia de dependencias de licencias comerciales y alineación con las competencias técnicas del equipo de desarrollo.', first=True)

h3('2.3.1 Lenguajes de Programación')
apa_table(5, 'Lenguajes de programación utilizados en el sistema',
    ['Lenguaje', 'Versión recomendada', 'Uso en el sistema'],
    [
        ['PHP', '8.0 o superior', 'Lógica del servidor: controladores, modelos, servicios, helpers y middleware'],
        ['SQL', 'MySQL 5.7 / 8.0', 'Definición y manipulación de datos en la base de datos relacional'],
        ['HTML5', '—', 'Estructura semántica de las vistas'],
        ['CSS3', '—', 'Estilos y presentación de la interfaz de usuario'],
        ['JavaScript (ES6+)', '—', 'Interacciones del cliente, peticiones AJAX y actualización del dashboard'],
    ]
)

h3('2.3.2 Frameworks y Herramientas')
apa_table(6, 'Frameworks, bibliotecas y herramientas de desarrollo utilizadas',
    ['Herramienta', 'Versión', 'Uso en el sistema'],
    [
        ['FPDF', '1.85', 'Generación de documentos PDF para los reportes del sistema'],
        ['PhpSpreadsheet', '^1.28', 'Lectura de archivos Excel para la importación masiva de beneficiarios'],
        ['Composer', '2.x', 'Gestión de dependencias PHP del proyecto'],
        ['Chart.js', 'CDN', 'Visualización de gráficos estadísticos en el panel administrativo'],
        ['Laragon', 'Local', 'Entorno de desarrollo local (Apache + MySQL + PHP)'],
    ]
)

h3('2.3.3 Bases de Datos y Servicios')
apa_table(7, 'Componentes de infraestructura de datos y servicios del sistema',
    ['Componente', 'Detalle'],
    [
        ['Motor de base de datos', 'MySQL con conjunto de caracteres utf8mb4'],
        ['Acceso a datos', 'PDO (PHP Data Objects) con prepared statements en todas las consultas'],
        ['Event Scheduler MySQL', 'Evento automático reiniciar_cupos_semanal: se ejecuta cada lunes a las 06:00 a.m. y crea los registros de cupos de la nueva semana para todas las sedes activas, tomando el cupo por defecto desde la tabla configuracion'],
        ['Almacenamiento de archivos', 'Sistema de archivos local en el directorio /storage/pdfs/'],
        ['Servicios en la nube', 'No aplica — sistema 100% local sin dependencias externas'],
        ['Servidor web', 'Apache HTTP Server provisto vía Laragon en el entorno de producción local'],
    ]
)

print("Caps 1-2 OK")
