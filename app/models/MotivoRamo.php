<?php
require_once BASE_PATH . '/app/models/Database.php';

class MotivoRamo
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM motivos_ramo ORDER BY orden, nombre");
        return $stmt->fetchAll();
    }

    public function getActivos()
    {
        $stmt = $this->db->query("SELECT * FROM motivos_ramo WHERE activo = 1 ORDER BY orden");
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM motivos_ramo WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO motivos_ramo (nombre, requiere_detalle, orden, activo) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['nombre'],
            (int)($data['requiere_detalle'] ?? 0),
            (int)($data['orden'] ?? 0),
            (int)($data['activo'] ?? 1),
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE motivos_ramo SET nombre = ?, requiere_detalle = ?, orden = ?, activo = ? WHERE id = ?"
        );
        return $stmt->execute([
            $data['nombre'],
            (int)($data['requiere_detalle'] ?? 0),
            (int)($data['orden'] ?? 0),
            (int)($data['activo'] ?? 1),
            (int)$id,
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM motivos_ramo WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function tieneRegistrosAsociados($id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM solicitudes WHERE id_motivo = ?");
        $stmt->execute([(int)$id]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
