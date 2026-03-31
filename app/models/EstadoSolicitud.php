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
}
