<?php
require_once BASE_PATH . '/app/models/Database.php';

class Solicitud
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO solicitudes (persona_id, fecha_solicitud, id_sede, nombre_destinatario,
             id_motivo, motivo_otro, observaciones, id_estado)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            (int)$data['persona_id'],
            $data['fecha_solicitud'],
            (int)$data['id_sede'],
            $data['nombre_destinatario'],
            (int)$data['id_motivo'],
            $data['motivo_otro'] ?? null,
            $data['observaciones'] ?? null,
            (int)($data['id_estado'] ?? 1),
        ]);
        return $this->db->lastInsertId();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare(
            "SELECT s.*,
                    p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido,
                    p.documento, p.tipo_documento, p.telefono,
                    se.nombre AS sede_nombre,
                    m.nombre AS motivo_nombre, m.requiere_detalle,
                    e.nombre AS estado_nombre, e.color AS estado_color
             FROM solicitudes s
             INNER JOIN personas p ON p.id = s.persona_id
             INNER JOIN sedes se ON se.id = s.id_sede
             INNER JOIN motivos_ramo m ON m.id = s.id_motivo
             INNER JOIN estados_solicitud e ON e.id = s.id_estado
             WHERE s.id = ?"
        );
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function listar($filtros = [])
    {
        $where = [];
        $params = [];

        if (!empty($filtros['fecha_desde'])) {
            $where[] = "s.fecha_solicitud >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $where[] = "s.fecha_solicitud <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['id_sede'])) {
            $where[] = "s.id_sede = ?";
            $params[] = (int)$filtros['id_sede'];
        }
        if (!empty($filtros['id_estado'])) {
            $where[] = "s.id_estado = ?";
            $params[] = (int)$filtros['id_estado'];
        }
        if (!empty($filtros['busqueda'])) {
            $term = '%' . $filtros['busqueda'] . '%';
            $where[] = "(p.documento LIKE ? OR p.primer_nombre LIKE ? OR p.primer_apellido LIKE ? OR CONCAT(p.primer_nombre, ' ', p.primer_apellido) LIKE ? OR s.nombre_destinatario LIKE ?)";
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
            $params[] = $term;
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        // Count total
        $countSql = "SELECT COUNT(*) FROM solicitudes s
                     INNER JOIN personas p ON p.id = s.persona_id
                     $whereClause";
        $countStmt = $this->db->prepare($countSql);
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Paginate
        $page = max(1, (int)($filtros['pagina'] ?? 1));
        $limit = ITEMS_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $sql = "SELECT s.*,
                       p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido,
                       p.documento, p.telefono,
                       se.nombre AS sede_nombre,
                       m.nombre AS motivo_nombre,
                       e.nombre AS estado_nombre, e.color AS estado_color
                FROM solicitudes s
                INNER JOIN personas p ON p.id = s.persona_id
                INNER JOIN sedes se ON se.id = s.id_sede
                INNER JOIN motivos_ramo m ON m.id = s.id_motivo
                INNER JOIN estados_solicitud e ON e.id = s.id_estado
                $whereClause
                ORDER BY s.fecha_solicitud DESC, s.id DESC
                LIMIT $limit OFFSET $offset";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return [
            'data'       => $stmt->fetchAll(),
            'total'      => $total,
            'pagina'     => $page,
            'total_paginas' => (int)ceil($total / $limit),
        ];
    }

    public function cambiarEstado($id, $id_estado)
    {
        $stmt = $this->db->prepare("UPDATE solicitudes SET id_estado = ? WHERE id = ?");
        return $stmt->execute([(int)$id_estado, (int)$id]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM solicitudes WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function getParaReporte($filtros = [])
    {
        $where = [];
        $params = [];

        if (!empty($filtros['fecha_desde'])) {
            $where[] = "s.fecha_solicitud >= ?";
            $params[] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $where[] = "s.fecha_solicitud <= ?";
            $params[] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['id_sede'])) {
            $where[] = "s.id_sede = ?";
            $params[] = (int)$filtros['id_sede'];
        }
        if (!empty($filtros['id_estado'])) {
            $where[] = "s.id_estado = ?";
            $params[] = (int)$filtros['id_estado'];
        }

        $whereClause = $where ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT s.*,
                       p.primer_nombre, p.segundo_nombre, p.primer_apellido, p.segundo_apellido,
                       p.documento, p.telefono,
                       se.nombre AS sede_nombre,
                       m.nombre AS motivo_nombre,
                       e.nombre AS estado_nombre
                FROM solicitudes s
                INNER JOIN personas p ON p.id = s.persona_id
                INNER JOIN sedes se ON se.id = s.id_sede
                INNER JOIN motivos_ramo m ON m.id = s.id_motivo
                INNER JOIN estados_solicitud e ON e.id = s.id_estado
                $whereClause
                ORDER BY se.nombre, s.fecha_solicitud";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function contarPorSede($id_sede, $periodo)
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM solicitudes
             WHERE id_sede = ? AND DATE_FORMAT(fecha_solicitud, '%Y-%m-01') = ?
             AND id_estado NOT IN (SELECT id FROM estados_solicitud WHERE nombre = 'Cancelada')"
        );
        $stmt->execute([(int)$id_sede, $periodo]);
        return (int)$stmt->fetchColumn();
    }

    public function tieneSolicitudEnMes($persona_id, $fecha)
    {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM solicitudes
             WHERE persona_id = ? AND DATE_FORMAT(fecha_solicitud, '%Y-%m') = DATE_FORMAT(?, '%Y-%m')
             AND id_estado IN (SELECT id FROM estados_solicitud WHERE nombre IN ('Aprobada', 'Pendiente', 'Entregada'))"
        );
        $stmt->execute([(int)$persona_id, $fecha]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function getEstadisticas()
    {
        $stats = [];

        // Total
        $stmt = $this->db->query("SELECT COUNT(*) as total FROM solicitudes");
        $stats['total'] = (int)$stmt->fetch()['total'];

        // Por sede
        $stmt = $this->db->query(
            "SELECT se.nombre, COUNT(*) as total
             FROM solicitudes s
             INNER JOIN sedes se ON se.id = s.id_sede
             GROUP BY s.id_sede ORDER BY total DESC"
        );
        $stats['por_sede'] = $stmt->fetchAll();


        // Por motivo
        $stmt = $this->db->query(
            "SELECT m.nombre, COUNT(*) as total
             FROM solicitudes s
             INNER JOIN motivos_ramo m ON m.id = s.id_motivo
             GROUP BY s.id_motivo ORDER BY total DESC"
        );
        $stats['por_motivo'] = $stmt->fetchAll();

        // Por estado
        $stmt = $this->db->query(
            "SELECT e.nombre, e.color, COUNT(*) as total
             FROM solicitudes s
             INNER JOIN estados_solicitud e ON e.id = s.id_estado
             GROUP BY s.id_estado ORDER BY e.orden"
        );
        $stats['por_estado'] = $stmt->fetchAll();

        // Solicitudes del mes actual
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) as total FROM solicitudes
             WHERE DATE_FORMAT(fecha_solicitud, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')"
        );
        $stmt->execute();
        $stats['total_mes'] = (int)$stmt->fetch()['total'];

        // Solicitudes de la semana actual
        $stmt = $this->db->query(
            "SELECT COUNT(*) as total FROM solicitudes
             WHERE YEARWEEK(fecha_solicitud, 1) = YEARWEEK(CURDATE(), 1)"
        );
        $stats['total_semana'] = (int)$stmt->fetch()['total'];

        return $stats;
    }

    /**
     * Solicitudes de la semana actual agrupadas por sede (for pie chart)
     */
    public function getSolicitudesSemanaActualPorSede()
    {
        $stmt = $this->db->query(
            "SELECT se.nombre as sede_nombre, COUNT(*) as total
             FROM solicitudes s
             INNER JOIN sedes se ON se.id = s.id_sede
             WHERE YEARWEEK(s.fecha_solicitud, 1) = YEARWEEK(CURDATE(), 1)
             GROUP BY s.id_sede, se.nombre
             ORDER BY total DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Total solicitudes por sede (all time, for bar chart)
     */
    public function getTotalPorSede()
    {
        $stmt = $this->db->query(
            "SELECT se.nombre as sede_nombre, COUNT(*) as total
             FROM solicitudes s
             INNER JOIN sedes se ON se.id = s.id_sede
             GROUP BY s.id_sede, se.nombre
             ORDER BY total DESC"
        );
        return $stmt->fetchAll();
    }

    /**
     * Get weekly solicitudes by sede for the last N weeks (for chart)
     */
    public function getSolicitudesSemanalPorSede($numSemanas = 8)
    {
        $stmt = $this->db->prepare(
            "SELECT
                YEARWEEK(s.fecha_solicitud, 1) as semana_num,
                MIN(DATE(s.fecha_solicitud - INTERVAL WEEKDAY(s.fecha_solicitud) DAY)) as semana_inicio,
                se.id as sede_id,
                se.nombre as sede_nombre,
                COUNT(*) as total
             FROM solicitudes s
             INNER JOIN sedes se ON se.id = s.id_sede
             WHERE s.fecha_solicitud >= DATE_SUB(CURDATE(), INTERVAL ? WEEK)
             GROUP BY semana_num, se.id, se.nombre
             ORDER BY semana_num ASC, se.nombre ASC"
        );
        $stmt->execute([$numSemanas]);
        return $stmt->fetchAll();
    }

    /**
     * Get last 5 solicitudes for recent activity
     */
    public function getRecientes($limit = 5)
    {
        $stmt = $this->db->prepare(
            "SELECT s.id, s.fecha_solicitud, s.nombre_destinatario,
                    p.primer_nombre, p.primer_apellido, p.documento,
                    se.nombre AS sede_nombre,
                    m.nombre AS motivo_nombre,
                    e.nombre AS estado_nombre, e.color AS estado_color
             FROM solicitudes s
             INNER JOIN personas p ON p.id = s.persona_id
             INNER JOIN sedes se ON se.id = s.id_sede
             INNER JOIN motivos_ramo m ON m.id = s.id_motivo
             INNER JOIN estados_solicitud e ON e.id = s.id_estado
             ORDER BY s.id DESC
             LIMIT ?"
        );
        $stmt->execute([(int)$limit]);
        return $stmt->fetchAll();
    }
}
