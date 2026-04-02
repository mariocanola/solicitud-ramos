<?php
require_once BASE_PATH . '/app/models/Database.php';

class EstadoSolicitud
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM estados_solicitud ORDER BY orden");
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM estados_solicitud WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO estados_solicitud (nombre, color, orden) VALUES (?, ?, ?)"
        );
        $stmt->execute([
            $data['nombre'],
            $data['color'] ?? '#6c757d',
            (int)($data['orden'] ?? 0),
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE estados_solicitud SET nombre = ?, color = ?, orden = ? WHERE id = ?"
        );
        return $stmt->execute([
            $data['nombre'],
            $data['color'] ?? '#6c757d',
            (int)($data['orden'] ?? 0),
            (int)$id,
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM estados_solicitud WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function tieneRegistrosAsociados($id)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM solicitudes WHERE id_estado = ?");
        $stmt->execute([(int)$id]);
        return (int)$stmt->fetchColumn() > 0;
    }
}
