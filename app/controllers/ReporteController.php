<?php
require_once BASE_PATH . '/app/services/PdfService.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/models/EstadoSolicitud.php';

class ReporteController
{
    public function index()
    {
        $sedeModel = new Sede();
        $estadoModel = new EstadoSolicitud();

        $sedes = $sedeModel->getActivas();
        $estados = $estadoModel->getAll();
        $pageTitle = 'Reportes';
        $csrfField = Csrf::field();

        ob_start();
        require BASE_PATH . '/app/views/reportes/generar.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/views/layouts/main.php';
    }

    public function generarPdf()
    {
        Csrf::validate();

        $filtros = [
            'fecha_desde' => $_POST['fecha_desde'] ?? '',
            'fecha_hasta' => $_POST['fecha_hasta'] ?? '',
            'id_sede'     => $_POST['id_sede'] ?? '',
            'id_estado'   => $_POST['id_estado'] ?? '',
        ];
        $incluirHojas = !empty($_POST['incluir_hojas']);

        try {
            $pdfService = new PdfService();
            $ruta = $pdfService->generarConsolidado($filtros, $incluirHojas);

            // Clean any output buffers to prevent corrupted PDF downloads
            while (ob_get_level()) {
                ob_end_clean();
            }

            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . basename($ruta) . '"');
            header('Content-Length: ' . filesize($ruta));
            readfile($ruta);
            exit;
        } catch (Exception $e) {
            error_log('Error generando PDF: ' . $e->getMessage());
            Session::flash('mensaje', 'Ocurrio un error al generar el PDF. Intente de nuevo.');
            Session::flash('tipo', 'danger');
            Response::redirect('reportes');
        }
    }
}
