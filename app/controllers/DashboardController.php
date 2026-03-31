<?php
require_once BASE_PATH . '/app/services/ReporteService.php';
require_once BASE_PATH . '/app/services/CupoService.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';

class DashboardController
{
    public function index()
    {
        $pageTitle = 'Dashboard';

        try {
            $reporteService = new ReporteService();
            $cupoService = new CupoService();

            $estadisticas = $reporteService->getEstadisticas();
            $cupos = $cupoService->getResumen();
            $periodoActual = DateHelper::mesAnio(DateHelper::currentPeriod());
            $sedeModel = new Sede();
            $totalSedes = count($sedeModel->getActivas());
        } catch (Exception $e) {
            error_log("DashboardController::index - " . $e->getMessage());
            $estadisticas = ['total' => 0, 'total_mes' => 0, 'por_sede' => [], 'por_motivo' => [], 'por_estado' => []];
            $cupos = [];
            $periodoActual = DateHelper::mesAnio(DateHelper::currentPeriod());
            $totalSedes = 0;
        }

        ob_start();
        require BASE_PATH . '/app/views/dashboard/index.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/views/layouts/main.php';
    }

    public function resumen()
    {
        $reporteService = new ReporteService();
        $cupoService = new CupoService();

        Response::success([
            'estadisticas' => $reporteService->getEstadisticas(),
            'cupos'        => $cupoService->getResumen(),
        ]);
    }
}
