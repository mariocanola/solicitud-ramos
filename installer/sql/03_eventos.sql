-- MySQL Event para reinicio automático de cupos semanales
-- Se ejecuta cada lunes a las 06:00 AM
-- Crea registros de cupos para la nueva semana en todas las sedes activas

USE flores_db;

-- Activar el event scheduler (necesario para que el evento se ejecute)
SET GLOBAL event_scheduler = ON;

DELIMITER $$

-- Eliminar evento si existe (para recrearlo)
DROP EVENT IF EXISTS reiniciar_cupos_semanal$$

CREATE EVENT IF NOT EXISTS reiniciar_cupos_semanal
ON SCHEDULE EVERY 1 WEEK STARTS (TIMESTAMP(DATE_ADD(CURDATE(), INTERVAL (8 - DAYOFWEEK(CURDATE())) DAY)) + INTERVAL 6 HOUR)
DO
BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE v_id_sede INT;
    DECLARE v_cupo_default INT;
    DECLARE v_periodo DATE;
    DECLARE cursor_sedes CURSOR FOR SELECT id FROM sedes WHERE activo = 1;
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;
    
    -- Obtener cupo default desde configuración
    SELECT CAST(valor AS UNSIGNED) INTO v_cupo_default 
    FROM configuracion WHERE clave = 'cupo_default';
    
    -- Si no hay cupo default, usar valor por defecto
    IF v_cupo_default IS NULL OR v_cupo_default = 0 THEN
        SET v_cupo_default = 50;
    END IF;
    
    -- Calcular lunes de la semana actual
    SET v_periodo = DATE_SUB(CURDATE(), INTERVAL WEEKDAY(CURDATE()) DAY);
    
    OPEN cursor_sedes;
    read_loop: LOOP
        FETCH cursor_sedes INTO v_id_sede;
        IF done THEN
            LEAVE read_loop;
        END IF;
        
        -- Insertar registro de cupo si no existe para esta sede y período
        INSERT IGNORE INTO cupos_sede (id_sede, periodo, cupo_maximo, cupo_usado, notificado)
        VALUES (v_id_sede, v_periodo, v_cupo_default, 0, 0);
        
    END LOOP;
    CLOSE cursor_sedes;
END$$

DELIMITER ;
