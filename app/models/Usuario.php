<?php
require_once BASE_PATH . '/app/models/Database.php';

class Usuario
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function buscarPorUsername($username)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM usuarios WHERE username = ? AND activo = 1 LIMIT 1"
        );
        $stmt->execute([$username]);
        return $stmt->fetch() ?: null;
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ? LIMIT 1");
        $stmt->execute([(int)$id]);
        return $stmt->fetch() ?: null;
    }

    public function registrarLogin($id)
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET ultimo_login = NOW() WHERE id = ?");
        $stmt->execute([(int)$id]);
    }

    public function existeUsername($username)
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE username = ?");
        $stmt->execute([$username]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function create($data)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO usuarios (username, password_hash, nombre, rol, activo)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['username'],
            password_hash($data['password'], PASSWORD_BCRYPT),
            $data['nombre'],
            $data['rol'],
            $data['activo'] ?? 1,
        ]);
        return $this->db->lastInsertId();
    }

    public function actualizarPassword($id, $password)
    {
        $stmt = $this->db->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        return $stmt->execute([
            password_hash($password, PASSWORD_BCRYPT),
            (int)$id,
        ]);
    }
}
