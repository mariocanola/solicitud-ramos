<?php
require_once BASE_PATH . '/app/models/Database.php';

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
            "INSERT INTO cupos_sede (id_sede, periodo, cupo_maximo, cupo_usado, notificado)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            (int)$data['id_sede'],
            $data['periodo'],
            (int)$data['cupo_maximo'],
            (int)($data['cupo_usado'] ?? 0),
            (int)($data['notificado'] ?? 0),
        ]);
        return $this->db->lastInsertId();
    }

    public function incrementar($id_sede, $periodo)
    {
        $stmt = $this->db->prepare(
            "UPDATE cupos_sede SET cupo_usado = cupo_usado + 1
             WHERE id_sede = ? AND periodo = ?"
        );
        return $stmt->execute([(int)$id_sede, $periodo]);
    }

    public function decrementar($id_sede, $periodo)
    {
        $stmt = $this->db->prepare(
            "UPDATE cupos_sede SET cupo_usado = GREATEST(cupo_usado - 1, 0)
             WHERE id_sede = ? AND periodo = ?"
        );
        return $stmt->execute([(int)$id_sede, $periodo]);
    }

    public function marcarNotificado($id_sede, $periodo)
    {
        $stmt = $this->db->prepare(
            "UPDATE cupos_sede SET notificado = 1 WHERE id_sede = ? AND periodo = ?"
        );
        return $stmt->execute([(int)$id_sede, $periodo]);
    }

    public function existeOCrear($id_sede, $periodo, $cupo_default)
    {
        $cupo = $this->getBySedeYPeriodo($id_sede, $periodo);
        if (!$cupo) {
            $this->crear([
                'id_sede'     => $id_sede,
                'periodo'     => $periodo,
                'cupo_maximo' => $cupo_default,
            ]);
            $cupo = $this->getBySedeYPeriodo($id_sede, $periodo);
        }
        return $cupo;
    }

    public function actualizarCupoMaximo($id_sede, $periodo, $cupo_maximo)
    {
        $stmt = $this->db->prepare(
            "UPDATE cupos_sede SET cupo_maximo = ? WHERE id_sede = ? AND periodo = ?"
        );
        return $stmt->execute([(int)$cupo_maximo, (int)$id_sede, $periodo]);
    }

    public function getResumen()
    {
        // Calculamos cupo_usado en tiempo real desde la tabla solicitudes para evitar
        // que el contador almacenado quede desincronizado al borrar solicitudes.
        // Cuenta solo solicitudes en estados activos (Pendiente, Aprobada, Entregada),
        // consistente con tieneSolicitudEnMes() del modelo Solicitud.
        $periodo = date('Y-m-01');
        $stmt = $this->db->prepare(
            "SELECT cs.id, cs.id_sede, cs.periodo, cs.cupo_maximo, cs.notificado,
                    s.nombre AS sede_nombre,
                    COALESCE((
                        SELECT COUNT(*) FROM solicitudes sol
                        INNER JOIN estados_solicitud e ON e.id = sol.id_estado
                        WHERE sol.id_sede = cs.id_sede
                          AND DATE_FORMAT(sol.fecha_solicitud, '%Y-%m-01') = cs.periodo
                          AND e.nombre IN ('Pendiente','Aprobada','Entregada')
                    ), 0) AS cupo_usado,
                    ROUND(COALESCE((
                        SELECT COUNT(*) FROM solicitudes sol
                        INNER JOIN estados_solicitud e ON e.id = sol.id_estado
                        WHERE sol.id_sede = cs.id_sede
                          AND DATE_FORMAT(sol.fecha_solicitud, '%Y-%m-01') = cs.periodo
                          AND e.nombre IN ('Pendiente','Aprobada','Entregada')
                    ), 0) / cs.cupo_maximo * 100, 1) AS porcentaje
             FROM cupos_sede cs
             INNER JOIN sedes s ON s.id = cs.id_sede
             WHERE cs.periodo = ?
             ORDER BY s.nombre"
        );
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
