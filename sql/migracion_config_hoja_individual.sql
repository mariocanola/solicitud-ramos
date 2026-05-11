-- Migración: parámetros de configuración para la hoja individual (remisión PDF).
-- Aplicar en BD ya existente. Idempotente.

INSERT INTO configuracion (clave, valor, descripcion) VALUES
    ('destinatario_solicitudes', 'ING. RODRIGO PERDOMO',
     'Persona a quien se dirigen las solicitudes individuales (carta)'),
    ('empresa_destinataria', 'FLORES EL TANDIL',
     'Nombre de la empresa que aparece en el encabezado de la carta')
ON DUPLICATE KEY UPDATE valor = VALUES(valor);
