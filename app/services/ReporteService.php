<?php
require_once BASE_PATH . '/app/models/Solicitud.php';

class ReporteService
{
    private $solicitudModel;

    public function __construct()
    {
        $this->solicitudModel = new Solicitud();
    }

    public function getEstadisticas()
    {
        return $this->solicitudModel->getEstadisticas();
    }

    public function getDatosReporte($filtros = [])
    {
        return $this->solicitudModel->getParaReporte($filtros);
    }
}
