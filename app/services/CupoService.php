<?php
require_once BASE_PATH . '/app/models/CupoSede.php';
require_once BASE_PATH . '/app/models/Configuracion.php';

class CupoService
{
    private $cupoModel;
    private $configModel;

    public function __construct()
    {
        $this->cupoModel = new CupoSede();
        $this->configModel = new Configuracion();
    }

    public function verificarDisponibilidad($id_sede, $fecha)
    {
        $periodo = date('Y-m-01', strtotime($fecha));
        $cupoDefault = $this->obtenerCupoDefault();
        $cupo = $this->cupoModel->existeOCrear($id_sede, $periodo, $cupoDefault);
        return $cupo['cupo_usado'] < $cupo['cupo_maximo'];
    }

    public function incrementar($id_sede, $fecha)
    {
        $periodo = date('Y-m-01', strtotime($fecha));
        $cupoDefault = $this->obtenerCupoDefault();
        $this->cupoModel->existeOCrear($id_sede, $periodo, $cupoDefault);
        $this->cupoModel->incrementar($id_sede, $periodo);
    }

    public function decrementar($id_sede, $fecha)
    {
        $periodo = date('Y-m-01', strtotime($fecha));
        $this->cupoModel->decrementar($id_sede, $periodo);
    }

    public function getResumen()
    {
        return $this->cupoModel->getResumen();
    }

    public function obtenerCupoDefault()
    {
        $valor = $this->configModel->get('cupo_default');
        return $valor ? (int)$valor : CUPO_DEFAULT;
    }
}
