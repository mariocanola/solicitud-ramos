-- =====================================================
-- Script para eliminar el campo Área del sistema
-- Ejecutar en orden para mantener integridad referencial
-- =====================================================

USE flores_db;

-- 1. Eliminar la restricción de foreign key de solicitudes a areas_solicitud
SET @sql = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE 
        WHERE TABLE_SCHEMA = 'flores_db' 
        AND TABLE_NAME = 'solicitudes' 
        AND CONSTRAINT_NAME = 'fk_solicitudes_area'
    ) > 0,
    'ALTER TABLE solicitudes DROP FOREIGN KEY fk_solicitudes_area',
    'SELECT "La foreign key fk_solicitudes_area no existe o ya fue eliminada"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 2. Eliminar el índice idx_solicitudes_area si existe
SET @sql = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS 
        WHERE TABLE_SCHEMA = 'flores_db' 
        AND TABLE_NAME = 'solicitudes' 
        AND INDEX_NAME = 'idx_solicitudes_area'
    ) > 0,
    'ALTER TABLE solicitudes DROP INDEX idx_solicitudes_area',
    'SELECT "El índice idx_solicitudes_area no existe o ya fue eliminado"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 3. Eliminar las columnas de área de la tabla solicitudes
SET @sql = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = 'flores_db' 
        AND TABLE_NAME = 'solicitudes' 
        AND COLUMN_NAME = 'id_area_solicitud'
    ) > 0,
    'ALTER TABLE solicitudes DROP COLUMN id_area_solicitud',
    'SELECT "La columna id_area_solicitud no existe o ya fue eliminada"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

SET @sql = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS 
        WHERE TABLE_SCHEMA = 'flores_db' 
        AND TABLE_NAME = 'solicitudes' 
        AND COLUMN_NAME = 'area_otro'
    ) > 0,
    'ALTER TABLE solicitudes DROP COLUMN area_otro',
    'SELECT "La columna area_otro no existe o ya fue eliminada"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 4. Eliminar la tabla areas_solicitud (ya no se necesita)
SET @sql = (SELECT IF(
    (
        SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES 
        WHERE TABLE_SCHEMA = 'flores_db' 
        AND TABLE_NAME = 'areas_solicitud'
    ) > 0,
    'DROP TABLE areas_solicitud',
    'SELECT "La tabla areas_solicitud no existe o ya fue eliminada"'
));
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;

-- 5. Verificar que los cambios se aplicaron correctamente
SELECT 
    'Verificación final' as estado,
    TABLE_NAME,
    COLUMN_NAME,
    DATA_TYPE,
    IS_NULLABLE
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'flores_db' 
AND TABLE_NAME = 'solicitudes' 
AND COLUMN_NAME LIKE '%area%'
ORDER BY TABLE_NAME, COLUMN_NAME;

SELECT 
    'Tablas restantes' as estado,
    TABLE_NAME,
    TABLE_COMMENT
FROM INFORMATION_SCHEMA.TABLES 
WHERE TABLE_SCHEMA = 'flores_db' 
AND TABLE_NAME LIKE '%area%'
ORDER BY TABLE_NAME;

-- 6. Mostrar resumen de cambios
SELECT 
    'Resumen de cambios aplicados' as mensaje,
    NOW() as fecha_ejecucion,
    USER() as ejecutado_por;

-- =====================================================
-- Fin del script
-- =====================================================
