-- Script de diagnóstico para problemas de cupos semanales
-- Ejecutar para verificar el estado actual del sistema

USE flores_db;

-- 1. Verificar configuración de periodo_tipo
SELECT '=== CONFIGURACIÓN PERIODO_TIPO ===' AS info;
SELECT clave, valor, descripcion FROM configuracion WHERE clave = 'periodo_tipo';

-- 2. Verificar registros en cupos_sede (últimos 10)
SELECT '=== REGISTROS EN CUPOS_SEDE (últimos 10) ===' AS info;
SELECT cs.id, cs.id_sede, s.nombre AS sede_nombre, cs.periodo, cs.cupo_maximo, cs.cupo_usado, cs.notificado
FROM cupos_sede cs
INNER JOIN sedes s ON s.id = cs.id_sede
ORDER BY cs.periodo DESC, cs.id_sede
LIMIT 10;

-- 3. Verificar solicitudes de esta semana
SELECT '=== SOLICITUDES DE ESTA SEMANA ===' AS info;
SELECT 
    sol.id,
    sol.persona_id,
    p.primer_nombre,
    p.primer_apellido,
    sol.id_sede,
    s.nombre AS sede_nombre,
    sol.fecha_solicitud,
    DATE_SUB(DATE(sol.fecha_solicitud), INTERVAL WEEKDAY(sol.fecha_solicitud) DAY) AS periodo_semana,
    e.nombre AS estado
FROM solicitudes sol
INNER JOIN personas p ON p.id = sol.persona_id
INNER JOIN sedes s ON s.id = sol.id_sede
INNER JOIN estados_solicitud e ON e.id = sol.id_estado
WHERE sol.fecha_solicitud >= DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
  AND e.nombre IN ('Pendiente','Aprobada','Entregada')
ORDER BY sol.fecha_solicitud DESC;

-- 4. Verificar lunes de la semana actual
SELECT '=== LUNES DE LA SEMANA ACTUAL ===' AS info;
SELECT DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AS lunes_semana_actual;

-- 5. Verificar si existen registros de cupos para el lunes actual
SELECT '=== CUPOS PARA EL LUNES ACTUAL ===' AS info;
SELECT 
    cs.id_sede,
    s.nombre AS sede_nombre,
    cs.periodo,
    cs.cupo_maximo,
    cs.cupo_usado,
    cs.cupo_maximo - cs.cupo_usado AS disponible
FROM cupos_sede cs
INNER JOIN sedes s ON s.id = cs.id_sede
WHERE cs.periodo = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY);

-- 6. Contar solicitudes por sede esta semana
SELECT '=== CONTAR SOLICITUDES POR SEDE ESTA SEMANA ===' AS info;
SELECT 
    s.id AS id_sede,
    s.nombre AS sede_nombre,
    COUNT(sol.id) AS solicitudes_semana
FROM solicitudes sol
INNER JOIN sedes s ON s.id = sol.id_sede
INNER JOIN estados_solicitud e ON e.id = sol.id_estado
WHERE DATE_SUB(DATE(sol.fecha_solicitud), INTERVAL WEEKDAY(sol.fecha_solicitud) DAY) = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
  AND e.nombre IN ('Pendiente','Aprobada','Entregada')
GROUP BY s.id, s.nombre;
