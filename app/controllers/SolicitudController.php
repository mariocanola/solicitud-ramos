<?php
require_once BASE_PATH . '/app/services/SolicitudService.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/models/MotivoRamo.php';
require_once BASE_PATH . '/app/models/EstadoSolicitud.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';

class SolicitudController
{
    private $service;

    public function __construct()
    {
        $this->service = new SolicitudService();
    }

    public function listar()
    {
        $filtros = [
            'fecha_desde' => $_GET['fecha_desde'] ?? '',
            'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
            'id_sede'     => $_GET['id_sede'] ?? '',
            'id_estado'   => $_GET['id_estado'] ?? '',
            'busqueda'    => trim($_GET['busqueda'] ?? ''),
            'pagina'      => $_GET['pagina'] ?? 1,
        ];

        $resultado = $this->service->listar($filtros);

        $sedeModel = new Sede();
        $estadoModel = new EstadoSolicitud();
        $motivoModel = new MotivoRamo();

        $sedes = $sedeModel->getActivas();
        $estados = $estadoModel->getAll();
        $motivos = $motivoModel->getActivos();
        $solicitudes = $resultado['data'] ?? [];
        $totalPaginas = $resultado['total_paginas'] ?? 1;
        $paginaActual = $resultado['pagina'] ?? 1;
        $totalRegistros = $resultado['total'] ?? 0;

        $hoy = DateHelper::today();
        $flash = Session::getFlash('mensaje');
        $flashTipo = Session::getFlash('tipo');
        $pageTitle = 'Solicitudes';
        $csrfField = Csrf::field();

        // Tab activa
        $tabActiva = $_GET['tab'] ?? 'listado';

        ob_start();
        require BASE_PATH . '/app/views/solicitudes/listar.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/views/layouts/main.php';
    }

    public function formCrear()
    {
        // Redirect to solicitudes with tab
        Response::redirect('solicitudes?tab=listado');
    }

    public function crear()
    {
        Csrf::validate();

        $data = [
            'persona_id'         => (int)($_POST['persona_id'] ?? 0),
            'fecha_solicitud'    => $_POST['fecha_solicitud'] ?? DateHelper::today(),
            'id_sede'            => (int)($_POST['id_sede'] ?? 0),
            'nombre_destinatario'=> trim($_POST['nombre_destinatario'] ?? ''),
            'id_motivo'          => (int)($_POST['id_motivo'] ?? 0),
            'motivo_otro'        => trim($_POST['motivo_otro'] ?? '') ?: null,
            'observaciones'      => trim($_POST['observaciones'] ?? '') ?: null,
        ];

        $resultado = $this->service->crear($data);

        if ($resultado['success']) {
            Session::flash('mensaje', $resultado['message']);
            Session::flash('tipo', 'success');
        } else {
            Session::flash('mensaje', $resultado['message']);
            Session::flash('tipo', 'danger');
        }

        Response::redirect('solicitudes');
    }

    public function ver()
    {
        $id = (int)($_GET['id'] ?? 0);
        $solicitud = $this->service->obtener($id);

        if (!$solicitud) {
            Session::flash('mensaje', 'Solicitud no encontrada');
            Session::flash('tipo', 'danger');
            Response::redirect('solicitudes');
            return;
        }

        $solicitud['nombre_completo'] = Persona::getNombreCompleto($solicitud);
        $estadoModel = new EstadoSolicitud();
        $estados = $estadoModel->getAll();
        $pageTitle = "Solicitud #$id";
        $csrfField = Csrf::field();

        ob_start();
        require BASE_PATH . '/app/views/solicitudes/detalle.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/views/layouts/main.php';
    }

    public function cambiarEstado()
    {
        Csrf::validate();
        $id = (int)($_POST['id'] ?? 0);
        $id_estado = (int)($_POST['id_estado'] ?? 0);

        $resultado = $this->service->cambiarEstado($id, $id_estado);
        if ($resultado['success']) {
            Response::success(null, $resultado['message']);
        } else {
            Response::error($resultado['message']);
        }
    }
}
