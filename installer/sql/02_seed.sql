-- Datos iniciales para una instalacion nueva.
-- Incluye: sedes, motivos de ramo, estados, configuracion.
-- NO incluye usuarios (los crea el admin a mano o el instalador con su password).

-- sedes
INSERT INTO sedes (id,nombre,codigo,direccion,activo,created_at,updated_at) VALUES ('2','Tandil','TN','Finca el tandil','1','2026-03-29 08:10:30','2026-03-29 08:10:28');
INSERT INTO sedes (id,nombre,codigo,direccion,activo,created_at,updated_at) VALUES ('3','Primavera','PM','Finca primavera','1','2026-02-28 08:10:54','2026-03-29 08:10:52');

-- motivos_ramo
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('1','Cumpleaños','0','1','1');
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('2','Condolencias','0','2','1');
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('3','Nacimiento','0','3','1');
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('4','Recuperación','0','4','1');
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('5','Aniversario','0','5','1');
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('6','Otro','1','99','1');
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('10','Matrimonio','0','6','1');
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('11','Aniversario laboral','1','7','1');
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('12','Dia de la Madre','0','8','1');
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('13','Dia del Padre','0','9','1');
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('14','Despedida / Retiro','0','10','1');
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('15','Reconocimiento especial','1','11','1');
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('16','15 años','0','13','1');
INSERT INTO motivos_ramo (id,nombre,requiere_detalle,orden,activo) VALUES ('17','Regalo-Obsequio','0','1','1');

-- estados_solicitud
INSERT INTO estados_solicitud (id,nombre,color,orden) VALUES ('1','Pendiente','#FFA500','1');
INSERT INTO estados_solicitud (id,nombre,color,orden) VALUES ('2','Aprobada','#28A745','2');
INSERT INTO estados_solicitud (id,nombre,color,orden) VALUES ('3','Rechazada','#DC3545','3');
INSERT INTO estados_solicitud (id,nombre,color,orden) VALUES ('4','Entregada','#007BFF','4');
INSERT INTO estados_solicitud (id,nombre,color,orden) VALUES ('5','Cancelada','#6C757D','5');

-- configuracion
INSERT INTO configuracion (id,clave,valor,descripcion) VALUES ('1','correo_destino','admin@empresa.com','Correo principal para recibir reportes');
INSERT INTO configuracion (id,clave,valor,descripcion) VALUES ('2','correo_cc','','Correos en copia (separados por coma)');
INSERT INTO configuracion (id,clave,valor,descripcion) VALUES ('3','cupo_default','10','Cupo por defecto para nuevas sedes/periodos');
INSERT INTO configuracion (id,clave,valor,descripcion) VALUES ('4','smtp_host','smtp.gmail.com','Servidor SMTP');
INSERT INTO configuracion (id,clave,valor,descripcion) VALUES ('5','smtp_port','587','Puerto SMTP');
INSERT INTO configuracion (id,clave,valor,descripcion) VALUES ('6','smtp_user','','Usuario SMTP');
INSERT INTO configuracion (id,clave,valor,descripcion) VALUES ('7','smtp_pass','','Contraseña SMTP');
INSERT INTO configuracion (id,clave,valor,descripcion) VALUES ('8','smtp_secure','tls','Seguridad SMTP (tls/ssl)');
INSERT INTO configuracion (id,clave,valor,descripcion) VALUES ('9','nombre_organizacion','Tandil','Nombre para encabezados de reportes');
INSERT INTO configuracion (id,clave,valor,descripcion) VALUES ('10','logo_path','/public/img/logo.png','Ruta del logo para reportes');
INSERT INTO configuracion (id,clave,valor,descripcion) VALUES ('21','destinatario_solicitudes','ING. RODRIGO PERDOMO','Persona a quien se dirigen las solicitudes individuales (carta)');
INSERT INTO configuracion (id,clave,valor,descripcion) VALUES ('22','empresa_destinataria','FLORES EL TANDIL','Nombre de la empresa que aparece en el encabezado de la carta');
INSERT INTO configuracion (id,clave,valor,descripcion) VALUES ('32','periodo_tipo','weekly',NULL);

