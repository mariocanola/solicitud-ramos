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
        $this->verificarYNotificar($id_sede, $periodo);
    }

    public function decrementar($id_sede, $fecha)
    {
        $periodo = date('Y-m-01', strtotime($fecha));
        $this->cupoModel->decrementar($id_sede, $periodo);
    }

    public function verificarYNotificar($id_sede, $periodo)
    {
        $cupo = $this->cupoModel->getBySedeYPeriodo($id_sede, $periodo);
        if (!$cupo) {
            return;
        }

        if ($cupo['cupo_usado'] >= $cupo['cupo_maximo'] && $cupo['notificado'] == 0) {
            try {
                require_once BASE_PATH . '/app/services/MailService.php';
                $mailService = new MailService();
                $mailService->enviarReporteCupoLleno($id_sede);
                $this->cupoModel->marcarNotificado($id_sede, $periodo);
            } catch (Exception $e) {
                error_log("CupoService::verificarYNotificar - " . $e->getMessage());
            }
        }
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
