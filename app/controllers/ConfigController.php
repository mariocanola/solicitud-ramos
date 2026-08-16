<?php
require_once BASE_PATH . '/app/models/Configuracion.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/models/CupoSede.php';
require_once BASE_PATH . '/app/models/MotivoRamo.php';
require_once BASE_PATH . '/app/models/EstadoSolicitud.php';
require_once BASE_PATH . '/app/services/CupoService.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';
require_once BASE_PATH . '/app/helpers/Validator.php';
require_once BASE_PATH . '/app/models/Usuario.php';

class ConfigController
{
    private $configModel;

    public function __construct()
    {
        $this->configModel = new Configuracion();
    }

    public function index()
    {
        $sedeModel = new Sede();
        $cupoService = new CupoService();
        $motivoModel = new MotivoRamo();
        $estadoModel = new EstadoSolicitud();

        $configuraciones = $this->configModel->getAllWithDescriptions();
        $sedes = $sedeModel->getActivas();
        $todasSedes = $sedeModel->getAll();
        $cupos = $cupoService->getResumen();
        $motivos = $motivoModel->getAll();
        $estadosSolicitud = $estadoModel->getAll();
        $usuarios = (new Usuario())->getAll();
        $tipo = Configuracion::getPeriodoTipo();
        $periodoActual = DateHelper::getCurrentPeriodStart($tipo);
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

        if (isset($_POST['periodo_tipo']) && $_POST['periodo_tipo'] !== '') {
            $periodoTipo = $_POST['periodo_tipo'];
            if (!in_array($periodoTipo, ['monthly', 'weekly'])) {
                $errors['periodo_tipo'] = 'El tipo de periodo debe ser monthly o weekly';
            }
        }

        if (!empty($errors)) {
            Session::flash('mensaje', 'Error de validacion: ' . implode('. ', $errors));
            Session::flash('tipo', 'danger');
            Response::redirect('configuracion');
            return;
        }

        $tipoAnterior = $this->configModel->get('periodo_tipo') ?? 'monthly';
        $claves = ['cupo_default', 'nombre_organizacion', 'empresa_destinataria', 'destinatario_solicitudes', 'periodo_tipo'];

        foreach ($claves as $clave) {
            if (isset($_POST[$clave])) {
                $this->configModel->set($clave, trim($_POST[$clave]));
            }
        }

        $tipoNuevo = $_POST['periodo_tipo'] ?? $tipoAnterior;
        if ($tipoNuevo !== $tipoAnterior && in_array($tipoNuevo, ['monthly', 'weekly'], true)) {
            $this->inicializarCuposParaPeriodo($tipoNuevo);
        }

        Session::flash('mensaje', 'Configuracion guardada exitosamente');
        Session::flash('tipo', 'success');
        Response::redirect('configuracion');
    }

    private function inicializarCuposParaPeriodo($tipo)
    {
        $sedeModel = new Sede();
        $cupoModel = new CupoSede();
        $cupoService = new CupoService();

        $periodo = DateHelper::getPeriodStart(DateHelper::today(), $tipo);
        $cupoDefault = $cupoService->obtenerCupoDefault();

        foreach ($sedeModel->getActivas() as $sede) {
            $cupoModel->existeOCrear((int)$sede['id'], $periodo, $cupoDefault);
        }
    }
}
