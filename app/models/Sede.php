<?php
require_once BASE_PATH . '/app/models/Database.php';

class Sede
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT * FROM sedes ORDER BY nombre");
        return $stmt->fetchAll();
    }

    public function getActivas()
    {
        $stmt = $this->db->query("SELECT * FROM sedes WHERE activo = 1 ORDER BY nombre");
        return $stmt->fetchAll();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM sedes WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetch();
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO sedes (nombre, codigo, direccion, activo) VALUES (?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['nombre'],
            $data['codigo'],
            $data['direccion'] ?? null,
            $data['activo'] ?? 1,
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE sedes SET nombre = ?, codigo = ?, direccion = ?, activo = ? WHERE id = ?"
        );
        return $stmt->execute([
            $data['nombre'],
            $data['codigo'],
            $data['direccion'] ?? null,
            $data['activo'] ?? 1,
            (int)$id,
        ]);
    }

    public function delete($id)
    {
        $stmt = $this->db->prepare("DELETE FROM sedes WHERE id = ?");
        return $stmt->execute([(int)$id]);
    }

    public function tieneRegistrosAsociados($id)
    {
        $id = (int)$id;

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM solicitudes WHERE id_sede = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return 'solicitudes';
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM personas WHERE id_sede = ?");
        $stmt->execute([$id]);
        if ((int)$stmt->fetchColumn() > 0) {
            return 'personas';
        }

        return false;
    }
}
