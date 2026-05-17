-- Configuración para tipo de período (monthly o weekly)
-- Permite cambiar entre períodos mensuales y semanales sin modificar código

USE flores_db;

-- Verificar valor actual
SELECT clave, valor, descripcion FROM configuracion WHERE clave = 'periodo_tipo';

-- Actualizar a weekly
INSERT INTO configuracion (clave, valor, descripcion) 
VALUES ('periodo_tipo', 'weekly', 'Tipo de periodo para cupos: monthly (mensual) o weekly (semanal)')
ON DUPLICATE KEY UPDATE valor = 'weekly', descripcion = 'Tipo de periodo para cupos: monthly (mensual) o weekly (semanal)';

-- Verificar actualización
SELECT clave, valor, descripcion FROM configuracion WHERE clave = 'periodo_tipo';
