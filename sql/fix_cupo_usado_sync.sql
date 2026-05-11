-- Script de sincronizacion: recalcula cupo_usado en cupos_sede a partir de
-- las solicitudes reales. Util si el contador quedo desincronizado por
-- eliminaciones manuales o estados cambiados. Es seguro correrlo en
-- cualquier momento.

UPDATE cupos_sede cs SET cupo_usado = COALESCE((
    SELECT COUNT(*) FROM solicitudes sol
    INNER JOIN estados_solicitud e ON e.id = sol.id_estado
    WHERE sol.id_sede = cs.id_sede
      AND DATE_FORMAT(sol.fecha_solicitud, '%Y-%m-01') = cs.periodo
      AND e.nombre IN ('Pendiente','Aprobada','Entregada')
), 0);
