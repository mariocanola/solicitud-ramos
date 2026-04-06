<?php
require_once BASE_PATH . '/app/services/CupoService.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/models/CupoSede.php';
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
        $periodo = DateHelper::currentPeriod();
        $cupoService = new CupoService();

        // Ensure record exists
        $cupoModel->existeOCrear($id_sede, $periodo, $cupoService->obtenerCupoDefault());
        $cupoModel->actualizarCupoMaximo($id_sede, $periodo, $cupo_maximo);

        Session::flash('mensaje', 'Cupo actualizado exitosamente');
        Session::flash('tipo', 'success');
        Response::redirect('configuracion?tab=cupos');
    }
}
