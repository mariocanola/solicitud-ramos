<?php
require_once BASE_PATH . '/app/models/CupoSede.php';
require_once BASE_PATH . '/app/models/Configuracion.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';

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
        $tipo = Configuracion::getPeriodoTipo();
        $periodo = DateHelper::getPeriodStart($fecha, $tipo);
        $cupoDefault = $this->obtenerCupoDefault();
        $cupo = $this->cupoModel->existeOCrear($id_sede, $periodo, $cupoDefault);
        // Contamos en tiempo real para evitar desincronizacion con el campo cupo_usado almacenado.
        $usadoReal = $this->cupoModel->contarSolicitudesActivas($id_sede, $periodo, $tipo);
        return $usadoReal < (int)$cupo['cupo_maximo'];
    }

    /**
     * Asegura que exista el registro cupos_sede para la sede y periodo.
     * Antes era incrementar(), pero el conteo se calcula en tiempo real,
     * asi que solo necesitamos garantizar la existencia del registro.
     */
    public function asegurarRegistro($id_sede, $fecha)
    {
        $tipo = Configuracion::getPeriodoTipo();
        $periodo = DateHelper::getPeriodStart($fecha, $tipo);
        $this->cupoModel->existeOCrear($id_sede, $periodo, $this->obtenerCupoDefault());
    }

    public function getResumen()
    {
        $tipo = Configuracion::getPeriodoTipo();
        return $this->cupoModel->getResumen($tipo);
    }

    public function obtenerCupoDefault()
    {
        $valor = $this->configModel->get('cupo_default');
        return $valor ? (int)$valor : CUPO_DEFAULT;
    }
}
