<?php
require_once BASE_PATH . '/app/services/ReporteService.php';
require_once BASE_PATH . '/app/services/CupoService.php';
require_once BASE_PATH . '/app/models/Solicitud.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';

class DashboardController
{
    private $colors = [
        ['bg' => 'rgba(52, 152, 219, 0.7)',  'border' => 'rgba(52, 152, 219, 1)'],
        ['bg' => 'rgba(46, 204, 113, 0.7)',  'border' => 'rgba(46, 204, 113, 1)'],
        ['bg' => 'rgba(231, 76, 60, 0.7)',   'border' => 'rgba(231, 76, 60, 1)'],
        ['bg' => 'rgba(241, 196, 15, 0.7)',  'border' => 'rgba(241, 196, 15, 1)'],
        ['bg' => 'rgba(155, 89, 182, 0.7)',  'border' => 'rgba(155, 89, 182, 1)'],
        ['bg' => 'rgba(230, 126, 34, 0.7)',  'border' => 'rgba(230, 126, 34, 1)'],
        ['bg' => 'rgba(26, 188, 156, 0.7)',  'border' => 'rgba(26, 188, 156, 1)'],
        ['bg' => 'rgba(52, 73, 94, 0.7)',    'border' => 'rgba(52, 73, 94, 1)'],
    ];

    public function index()
    {
        $pageTitle = 'Dashboard';

        try {
            $reporteService = new ReporteService();
            $cupoService = new CupoService();
            $solicitudModel = new Solicitud();

            $estadisticas = $reporteService->getEstadisticas();
            $cupos = $cupoService->getResumen();
            $periodoActual = DateHelper::mesAnio(DateHelper::currentPeriod());
            // Pie chart: solicitudes de esta semana por sede
            $semanaPorSede = $solicitudModel->getSolicitudesSemanaActualPorSede();
            $pieData = $this->buildPieData($semanaPorSede);

            // Bar chart: total solicitudes por sede (historico)
            $totalPorSede = $solicitudModel->getTotalPorSede();
            $barData = $this->buildBarData($totalPorSede);
        } catch (Exception $e) {
            error_log("DashboardController::index - " . $e->getMessage());
            $estadisticas = ['total' => 0, 'total_mes' => 0, 'total_semana' => 0, 'por_sede' => []];
            $cupos = [];
            $periodoActual = DateHelper::mesAnio(DateHelper::currentPeriod());
            $pieData = ['labels' => [], 'data' => [], 'colors' => [], 'borders' => []];
            $barData = ['labels' => [], 'data' => [], 'colors' => [], 'borders' => []];
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

    private function buildPieData($semanaPorSede)
    {
        $labels = [];
        $data = [];
        $colors = [];
        $borders = [];

        foreach ($semanaPorSede as $i => $row) {
            $c = $this->colors[$i % count($this->colors)];
            $labels[] = $row['sede_nombre'];
            $data[] = (int)$row['total'];
            $colors[] = $c['bg'];
            $borders[] = $c['border'];
        }

        return compact('labels', 'data', 'colors', 'borders');
    }

    private function buildBarData($totalPorSede)
    {
        $labels = [];
        $data = [];
        $colors = [];
        $borders = [];

        foreach ($totalPorSede as $i => $row) {
            $c = $this->colors[$i % count($this->colors)];
            $labels[] = $row['sede_nombre'];
            $data[] = (int)$row['total'];
            $colors[] = $c['bg'];
            $borders[] = $c['border'];
        }

        return compact('labels', 'data', 'colors', 'borders');
    }
}
