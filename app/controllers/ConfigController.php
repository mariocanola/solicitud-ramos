<?php
require_once BASE_PATH . '/app/models/Configuracion.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/models/CupoSede.php';
require_once BASE_PATH . '/app/models/MotivoRamo.php';
require_once BASE_PATH . '/app/models/EstadoSolicitud.php';
require_once BASE_PATH . '/app/services/CupoService.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';
require_once BASE_PATH . '/app/helpers/Validator.php';

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

        $errors = [];

        if (isset($_POST['cupo_default']) && $_POST['cupo_default'] !== '') {
            $cupo = (int)$_POST['cupo_default'];
            if ($cupo < 1 || $cupo > 10000) {
                $errors['cupo_default'] = 'El cupo por defecto debe estar entre 1 y 10000';
            }
        }

        if (!empty($errors)) {
            Session::flash('mensaje', 'Error de validacion: ' . implode('. ', $errors));
            Session::flash('tipo', 'danger');
            Response::redirect('configuracion');
            return;
        }

        $configModel = new Configuracion();
        $claves = ['cupo_default', 'nombre_organizacion', 'logo_path'];

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
