-- Script de migración one-shot: limpia registros mensuales antiguos y crea
-- los registros semanales del lunes actual para todas las sedes activas.
-- Ejecutar SOLO al cambiar el modo de periodo de monthly a weekly.
--
-- Orden importante: primero INSERT del lunes actual (idempotente con NOT EXISTS),
-- luego DELETE de los mensuales, excluyendo el lunes actual si por casualidad
-- coincide con un día 1 (ej. 2026-06-01 es lunes).

USE flores_db;

-- 1. Diagnóstico: registros mensuales actuales (día 1 del mes)
SELECT '=== REGISTROS MENSUALES ANTES DE LIMPIAR ===' AS info;
SELECT COUNT(*) AS total_mensuales FROM cupos_sede WHERE DAY(periodo) = 1;

-- 2. Crear registros semanales para el lunes actual (idempotente)
INSERT INTO cupos_sede (id_sede, periodo, cupo_maximo, cupo_usado, notificado)
SELECT
    s.id,
    DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY) AS periodo,
    COALESCE((SELECT CAST(valor AS UNSIGNED) FROM configuracion WHERE clave = 'cupo_default'), 50) AS cupo_maximo,
    0 AS cupo_usado,
    0 AS notificado
FROM sedes s
WHERE s.activo = 1
  AND NOT EXISTS (
    SELECT 1 FROM cupos_sede cs
    WHERE cs.id_sede = s.id
      AND cs.periodo = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
  );

-- 3. Eliminar registros mensuales (día 1 del mes), pero NUNCA el lunes actual
-- aunque caiga día 1, para no perder el registro semanal recién creado.
DELETE FROM cupos_sede
WHERE DAY(periodo) = 1
  AND periodo <> DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY);

-- 4. Verificar resultado
SELECT '=== REGISTROS DESPUÉS DE LIMPIAR ===' AS info;
SELECT COUNT(*) AS total_restantes FROM cupos_sede;

SELECT '=== REGISTROS SEMANALES DEL LUNES ACTUAL ===' AS info;
SELECT cs.id, cs.id_sede, s.nombre AS sede_nombre, cs.periodo, cs.cupo_maximo, cs.cupo_usado
FROM cupos_sede cs
INNER JOIN sedes s ON s.id = cs.id_sede
WHERE cs.periodo = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY)
ORDER BY s.nombre;
