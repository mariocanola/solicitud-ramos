-- =====================================================
-- Datos iniciales (Seeds)
-- =====================================================

USE flores_db;

-- Motivos de ramo
INSERT INTO motivos_ramo (nombre, requiere_detalle, orden, activo) VALUES
('Cumpleaños', 0, 1, 1),
('Condolencias', 0, 2, 1),
('Nacimiento', 0, 3, 1),
('Recuperación', 0, 4, 1),
('Aniversario', 0, 5, 1),
('Otro', 1, 99, 1);

-- Estados de solicitud
INSERT INTO estados_solicitud (nombre, color, orden) VALUES
('Pendiente', '#FFA500', 1),
('Aprobada', '#28A745', 2),
('Rechazada', '#DC3545', 3),
('Entregada', '#007BFF', 4),
('Cancelada', '#6C757D', 5);

-- Configuracion del sistema
INSERT INTO configuracion (clave, valor, descripcion) VALUES
('correo_destino', 'admin@empresa.com', 'Correo principal para recibir reportes'),
('correo_cc', '', 'Correos en copia (separados por coma)'),
('cupo_default', '50', 'Cupo por defecto para nuevas sedes/periodos'),
('smtp_host', 'smtp.gmail.com', 'Servidor SMTP'),
('smtp_port', '587', 'Puerto SMTP'),
('smtp_user', '', 'Usuario SMTP'),
('smtp_pass', '', 'Contraseña SMTP'),
('smtp_secure', 'tls', 'Seguridad SMTP (tls/ssl)'),
('nombre_organizacion', 'Mi Organización', 'Nombre para encabezados de reportes'),
('logo_path', '/public/img/logo.png', 'Ruta del logo para reportes');

-- Sede de ejemplo
INSERT INTO sedes (nombre, codigo, direccion, activo) VALUES
('Sede Principal', 'SP', 'Dirección sede principal', 1);
