<?php
require_once BASE_PATH . '/app/models/Database.php';

class Persona
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function buscarPorDocumento($documento)
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, s.nombre AS sede_nombre
             FROM personas p
             INNER JOIN sedes s ON s.id = p.id_sede
             WHERE p.documento = ?"
        );
        $stmt->execute([$documento]);
        return $stmt->fetch() ?: null;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare(
            "SELECT p.*, s.nombre AS sede_nombre
             FROM personas p
             INNER JOIN sedes s ON s.id = p.id_sede
             WHERE p.id = ?"
        );
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function existeDocumento($documento)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM personas WHERE documento = ?");
        $stmt->execute([$documento]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create($data)
    {
        if ($this->existeDocumento($data['documento'])) {
            return false;
        }

        $stmt = $this->db->prepare(
            "INSERT INTO personas (tipo_documento, documento, primer_nombre, segundo_nombre,
             primer_apellido, segundo_apellido, telefono, id_sede, activo)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['tipo_documento'],
            $data['documento'],
            $data['primer_nombre'],
            $data['segundo_nombre'] ?? null,
            $data['primer_apellido'],
            $data['segundo_apellido'] ?? null,
            $data['telefono'] ?? null,
            (int)$data['id_sede'],
            $data['activo'] ?? 1,
        ]);
        return $this->db->lastInsertId();
    }

    public function update($id, $data)
    {
        $stmt = $this->db->prepare(
            "UPDATE personas SET tipo_documento = ?, primer_nombre = ?, segundo_nombre = ?,
             primer_apellido = ?, segundo_apellido = ?, telefono = ?, id_sede = ?, activo = ?
             WHERE id = ?"
        );
        return $stmt->execute([
            $data['tipo_documento'],
            $data['primer_nombre'],
            $data['segundo_nombre'] ?? null,
            $data['primer_apellido'],
            $data['segundo_apellido'] ?? null,
            $data['telefono'] ?? null,
            (int)$data['id_sede'],
            $data['activo'] ?? 1,
            (int)$id,
        ]);
    }

    public static function getNombreCompleto($persona)
    {
        $parts = array_filter([
            $persona['primer_nombre'] ?? '',
            $persona['segundo_nombre'] ?? '',
            $persona['primer_apellido'] ?? '',
            $persona['segundo_apellido'] ?? '',
        ], fn($p) => $p !== '' && $p !== null);
        return implode(' ', $parts);
    }
}
