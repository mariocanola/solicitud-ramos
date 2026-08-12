<?php
require_once BASE_PATH . '/app/models/Database.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';

class CupoSede
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getBySedeYPeriodo($id_sede, $periodo)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM cupos_sede WHERE id_sede = ? AND periodo = ?"
        );
        $stmt->execute([(int)$id_sede, $periodo]);
        return $stmt->fetch() ?: null;
    }

    public function crear($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO cupos_sede (id_sede, periodo, cupo_maximo, notificado)
             VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            (int)$data['id_sede'],
            $data['periodo'],
            (int)$data['cupo_maximo'],
            (int)($data['notificado'] ?? 0),
        ]);
        return $this->db->lastInsertId();
    }

    public function marcarNotificado($id_sede, $periodo)
    {
        $stmt = $this->db->prepare(
            "UPDATE cupos_sede SET notificado = 1 WHERE id_sede = ? AND periodo = ?"
        );
        return $stmt->execute([(int)$id_sede, $periodo]);
    }

    /**
     * Cuenta solicitudes activas (Pendiente/Aprobada/Entregada) para una sede+periodo.
     * Usa la misma lógica de mapeo fecha→periodo que getResumen() para evitar desincronización.
     */
    public function contarSolicitudesActivas($id_sede, $periodo, $tipo = 'monthly')
    {
        if ($tipo === 'weekly') {
            $sql = "SELECT COUNT(*) FROM solicitudes sol
                    INNER JOIN estados_solicitud e ON e.id = sol.id_estado
                    WHERE sol.id_sede = ?
                      AND DATE(
                          CASE WHEN TIME(sol.fecha_solicitud) = '00:00:00'
                               THEN sol.fecha_solicitud - INTERVAL WEEKDAY(sol.fecha_solicitud) DAY
                               ELSE DATE_SUB(sol.fecha_solicitud, INTERVAL 6 HOUR) - INTERVAL WEEKDAY(DATE_SUB(sol.fecha_solicitud, INTERVAL 6 HOUR)) DAY
                          END
                      ) = ?
                      AND e.nombre IN ('Pendiente','Aprobada','Entregada')";
        } else {
            $sql = "SELECT COUNT(*) FROM solicitudes sol
                    INNER JOIN estados_solicitud e ON e.id = sol.id_estado
                    WHERE sol.id_sede = ?
                      AND DATE_FORMAT(sol.fecha_solicitud, '%Y-%m-01') = ?
                      AND e.nombre IN ('Pendiente','Aprobada','Entregada')";
        }
        $stmt = $this->db->prepare($sql);
        $stmt->execute([(int)$id_sede, $periodo]);
        return (int)$stmt->fetchColumn();
    }

    public function existeOCrear($id_sede, $periodo, $cupo_default)
    {
        $cupo = $this->getBySedeYPeriodo($id_sede, $periodo);
        if ($cupo) {
            return $cupo;
        }
        try {
            $this->crear([
                'id_sede'     => $id_sede,
                'periodo'     => $periodo,
                'cupo_maximo' => $cupo_default,
            ]);
        } catch (PDOException $e) {
            // Otra peticion inserto la misma sede+periodo (UNIQUE).
            if ($e->getCode() !== '23000') {
                throw $e;
            }
        }
        return $this->getBySedeYPeriodo($id_sede, $periodo);
    }

    public function actualizarCupoMaximo($id_sede, $periodo, $cupo_maximo)
    {
        $stmt = $this->db->prepare(
            "UPDATE cupos_sede SET cupo_maximo = ? WHERE id_sede = ? AND periodo = ?"
        );
        return $stmt->execute([(int)$cupo_maximo, (int)$id_sede, $periodo]);
    }

    public function getResumen($tipo = 'monthly')
    {
        // Calculamos cupo_usado en tiempo real desde la tabla solicitudes para evitar
        // que el contador almacenado quede desincronizado al borrar solicitudes.
        // Cuenta solo solicitudes en estados activos (Pendiente, Aprobada, Entregada),
        // consistente con tieneSolicitudEnPeriodo() del modelo Solicitud.
        
        if ($tipo === 'weekly') {
            $periodo = DateHelper::getCurrentPeriodStart('weekly');
            $fechaCondicion = "DATE(
                CASE WHEN TIME(sol.fecha_solicitud) = '00:00:00'
                     THEN sol.fecha_solicitud - INTERVAL WEEKDAY(sol.fecha_solicitud) DAY
                     ELSE DATE_SUB(sol.fecha_solicitud, INTERVAL 6 HOUR) - INTERVAL WEEKDAY(DATE_SUB(sol.fecha_solicitud, INTERVAL 6 HOUR)) DAY
                END
            ) = cs.periodo";
        } else {
            $periodo = date('Y-m-01');
            $fechaCondicion = "DATE_FORMAT(sol.fecha_solicitud, '%Y-%m-01') = cs.periodo";
        }
        
        $sql = "SELECT t.id, t.id_sede, t.periodo, t.cupo_maximo, t.notificado,
                       t.sede_nombre, t.cupo_usado,
                       ROUND(t.cupo_usado / NULLIF(t.cupo_maximo, 0) * 100, 1) AS porcentaje
                FROM (
                    SELECT cs.id, cs.id_sede, cs.periodo, cs.cupo_maximo, cs.notificado,
                           s.nombre AS sede_nombre,
                           COALESCE((
                               SELECT COUNT(*) FROM solicitudes sol
                               INNER JOIN estados_solicitud e ON e.id = sol.id_estado
                               WHERE sol.id_sede = cs.id_sede
                                 AND {$fechaCondicion}
                                 AND e.nombre IN ('Pendiente','Aprobada','Entregada')
                           ), 0) AS cupo_usado
                    FROM cupos_sede cs
                    INNER JOIN sedes s ON s.id = cs.id_sede
                    WHERE cs.periodo = ?
                ) t
                ORDER BY t.sede_nombre";
             
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$periodo]);
        return $stmt->fetchAll();
    }

    /**
     * Lock row for transactional cupo check
     */
    public function getForUpdate($id_sede, $periodo)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM cupos_sede WHERE id_sede = ? AND periodo = ? FOR UPDATE"
        );
        $stmt->execute([(int)$id_sede, $periodo]);
        return $stmt->fetch() ?: null;
    }
}
