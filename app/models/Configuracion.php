<?php
require_once BASE_PATH . '/app/models/Database.php';

class Configuracion
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function get($clave)
    {
        $stmt = $this->db->prepare("SELECT valor FROM configuracion WHERE clave = ?");
        $stmt->execute([$clave]);
        $row = $stmt->fetch();
        return $row ? $row['valor'] : null;
    }

    public function set($clave, $valor)
    {
        $stmt = $this->db->prepare(
            "INSERT INTO configuracion (clave, valor) VALUES (?, ?)
             ON DUPLICATE KEY UPDATE valor = VALUES(valor)"
        );
        return $stmt->execute([$clave, $valor]);
    }

    public function getAll()
    {
        $stmt = $this->db->query("SELECT clave, valor, descripcion FROM configuracion ORDER BY id");
        $rows = $stmt->fetchAll();
        $config = [];
        foreach ($rows as $row) {
            $config[$row['clave']] = $row['valor'];
        }
        return $config;
    }

    public function getAllWithDescriptions()
    {
        $stmt = $this->db->query("SELECT * FROM configuracion ORDER BY id");
        return $stmt->fetchAll();
    }

    public function getMailConfig()
    {
        $keys = ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure',
                 'correo_destino', 'correo_cc', 'nombre_organizacion'];
        $placeholders = implode(',', array_fill(0, count($keys), '?'));
        $stmt = $this->db->prepare(
            "SELECT clave, valor FROM configuracion WHERE clave IN ($placeholders)"
        );
        $stmt->execute($keys);
        $rows = $stmt->fetchAll();
        $config = [];
        foreach ($rows as $row) {
            $config[$row['clave']] = $row['valor'];
        }
        return $config;
    }
}
