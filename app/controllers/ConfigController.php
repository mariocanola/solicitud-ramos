<?php
require_once BASE_PATH . '/app/models/Configuracion.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/models/CupoSede.php';
require_once BASE_PATH . '/app/models/MotivoRamo.php';
require_once BASE_PATH . '/app/models/EstadoSolicitud.php';
require_once BASE_PATH . '/app/services/CupoService.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';

class ConfigController
{
    public function index()
    {
        $configModel = new Configuracion();
        $sedeModel = new Sede();
        $cupoService = new CupoService();
        $motivoModel = new MotivoRamo();
        $estadoModel = new EstadoSolicitud();

        $configuraciones = $configModel->getAllWithDescriptions();
        $sedes = $sedeModel->getActivas();
        $todasSedes = $sedeModel->getAll();
        $cupos = $cupoService->getResumen();
        $motivos = $motivoModel->getAll();
        $estadosSolicitud = $estadoModel->getAll();
        $periodoActual = DateHelper::currentPeriod();
        $periodoTexto = DateHelper::mesAnio($periodoActual);

        $pageTitle = 'Configuracion';
        $csrfField = Csrf::field();
        $flash = Session::getFlash('mensaje');
        $flashTipo = Session::getFlash('tipo');
        $tabActiva = $_GET['tab'] ?? 'general';

        ob_start();
        require BASE_PATH . '/app/views/configuracion/index.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/views/layouts/main.php';
    }

    public function guardar()
    {
        Csrf::validate();

        $configModel = new Configuracion();
        $claves = ['correo_destino', 'correo_cc', 'cupo_default', 'smtp_host',
                    'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure',
                    'nombre_organizacion', 'logo_path'];

        foreach ($claves as $clave) {
            if (isset($_POST[$clave])) {
                $configModel->set($clave, trim($_POST[$clave]));
            }
        }

        Session::flash('mensaje', 'Configuracion guardada exitosamente');
        Session::flash('tipo', 'success');
        Response::redirect('configuracion');
    }
}
