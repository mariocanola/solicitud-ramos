<?php
require_once BASE_PATH . '/app/services/CupoService.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/models/CupoSede.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';

class CupoController
{
    public function index()
    {
        $cupoService = new CupoService();
        $sedeModel = new Sede();

        $cupos = $cupoService->getResumen();
        $sedes = $sedeModel->getActivas();
        $periodoActual = DateHelper::currentPeriod();
        $periodoTexto = DateHelper::mesAnio($periodoActual);
        $pageTitle = 'Control de Cupos';
        $csrfField = Csrf::field();

        ob_start();
        require BASE_PATH . '/app/views/configuracion/cupos.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/views/layouts/main.php';
    }

    public function actualizar()
    {
        Csrf::validate();

        $id_sede = (int)($_POST['id_sede'] ?? 0);
        $cupo_maximo = (int)($_POST['cupo_maximo'] ?? 0);

        if ($id_sede <= 0 || $cupo_maximo < 1) {
            Session::flash('mensaje', 'Datos inválidos. El cupo debe ser mayor a 0.');
            Session::flash('tipo', 'danger');
            Response::redirect('cupos');
            return;
        }

        $cupoModel = new CupoSede();
        $periodo = DateHelper::currentPeriod();
        $cupoService = new CupoService();

        // Ensure record exists
        $cupoModel->existeOCrear($id_sede, $periodo, $cupoService->obtenerCupoDefault());
        $cupoModel->actualizarCupoMaximo($id_sede, $periodo, $cupo_maximo);

        Session::flash('mensaje', 'Cupo actualizado exitosamente');
        Session::flash('tipo', 'success');
        Response::redirect('cupos');
    }
}
