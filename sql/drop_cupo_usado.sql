-- Migracion: eliminar la columna cupo_usado de cupos_sede.
-- Razon: ahora cupo_usado se calcula en tiempo real desde la tabla solicitudes
-- mediante CupoSede::contarSolicitudesActivas() y la subquery COUNT(*) en getResumen().
-- Mantener un campo almacenado generaba desincronizacion: cuando se importaban o se
-- corregian solicitudes manualmente, el contador almacenado quedaba obsoleto y
-- verificarDisponibilidad() permitia o bloqueaba incorrectamente.
--
-- Antes de ejecutar: hacer backup de cupos_sede (ver backups/ en .gitignore).

USE flores_db;

ALTER TABLE cupos_sede DROP COLUMN cupo_usado;
