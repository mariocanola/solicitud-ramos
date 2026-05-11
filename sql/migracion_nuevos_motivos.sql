-- Migracion: nuevos motivos de ramo florales para uso empresarial.
-- Aplicar en BD ya existente. Idempotente (ON DUPLICATE KEY).

-- Asegura que 'Otro' siempre quede al final.
UPDATE motivos_ramo SET orden = 99 WHERE nombre = 'Otro';

INSERT INTO motivos_ramo (nombre, requiere_detalle, orden, activo) VALUES
    ('Matrimonio',              0,  6, 1),
    ('Aniversario laboral',     1,  7, 1),
    ('Dia de la Madre',         0,  8, 1),
    ('Dia del Padre',           0,  9, 1),
    ('Despedida / Retiro',      0, 10, 1),
    ('Reconocimiento especial', 1, 11, 1)
ON DUPLICATE KEY UPDATE
    requiere_detalle = VALUES(requiere_detalle),
    orden = VALUES(orden),
    activo = 1;
