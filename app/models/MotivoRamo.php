<?php
require_once BASE_PATH . '/app/models/Database.php';

class MotivoRamo
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
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
}
