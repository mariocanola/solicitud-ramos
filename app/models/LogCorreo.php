<?php
require_once BASE_PATH . '/app/models/Database.php';

class LogCorreo
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function registrar($tipo, $destinatario, $asunto, $estado, $error = null)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO log_correos (tipo, destinatario, asunto, estado, error_detalle)
             VALUES (?, ?, ?, ?, ?)"
        );
        return $stmt->execute([$tipo, $destinatario, $asunto, $estado, $error]);
    }

    public function getRecientes($limit = 20)
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM log_correos ORDER BY created_at DESC LIMIT ?"
        );
        $stmt->execute([(int)$limit]);
        return $stmt->fetchAll();
    }
}
