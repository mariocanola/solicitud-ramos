<?php
require_once BASE_PATH . '/app/services/CupoService.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/models/CupoSede.php';
require_once BASE_PATH . '/app/models/Configuracion.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';

class CupoController
{
    public function index()
    {
        // Redirect to configuracion with cupos tab
        Response::redirect('configuracion?tab=cupos');
    }

    public function actualizar()
    {
        Csrf::validate();

        $id_sede = (int)($_POST['id_sede'] ?? 0);
        $cupo_maximo = (int)($_POST['cupo_maximo'] ?? 0);

        if ($id_sede <= 0 || $cupo_maximo < 1 || $cupo_maximo > 10000) {
            Session::flash('mensaje', 'Datos invalidos. El cupo debe estar entre 1 y 10,000.');
            Session::flash('tipo', 'danger');
            Response::redirect('configuracion?tab=cupos');
            return;
        }

        $cupoModel = new CupoSede();
        $tipo = Configuracion::getPeriodoTipo();
        $periodo = DateHelper::getCurrentPeriodStart($tipo);
        $cupoService = new CupoService();

        // Ensure record exists
        $cupoModel->existeOCrear($id_sede, $periodo, $cupoService->obtenerCupoDefault());
        $cupoModel->actualizarCupoMaximo($id_sede, $periodo, $cupo_maximo);

        Session::flash('mensaje', 'Cupo actualizado exitosamente');
        Session::flash('tipo', 'success');
        Response::redirect('configuracion?tab=cupos');
    }

    public function verificar()
    {
        Csrf::validate();
        
        $id_sede = (int)($_POST['id_sede'] ?? 0);
        
        if ($id_sede <= 0) {
            Response::error('ID de sede inválido');
            return;
        }

        $cupoModel = new CupoSede();
        $tipo = Configuracion::getPeriodoTipo();
        $periodo = DateHelper::getCurrentPeriodStart($tipo);
        $cupoService = new CupoService();

        // Obtener o crear registro de cupos para el periodo actual
        $cupo = $cupoModel->getBySedeYPeriodo($id_sede, $periodo);

        if (!$cupo) {
            $cupo_default = $cupoService->obtenerCupoDefault();
            $cupoModel->existeOCrear($id_sede, $periodo, $cupo_default);
            $cupo = $cupoModel->getBySedeYPeriodo($id_sede, $periodo);
        }

        // cupo_usado se calcula en tiempo real desde solicitudes (fuente unica).
        $usado = $cupoModel->contarSolicitudesActivas($id_sede, $periodo, $tipo);
        $maximo = (int)$cupo['cupo_maximo'];

        $data = [
            'cupo_maximo' => $maximo,
            'cupo_usado' => $usado,
            'disponible' => max(0, $maximo - $usado),
            'porcentaje_usado' => $maximo > 0 ? round(($usado / $maximo) * 100, 1) : 0,
        ];

        Response::success($data, 'Información de cupos obtenida');
    }
}
