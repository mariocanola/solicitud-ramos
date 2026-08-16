<?php
require_once BASE_PATH . '/app/services/SolicitudService.php';
require_once BASE_PATH . '/app/services/PersonaService.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/models/MotivoRamo.php';
require_once BASE_PATH . '/app/models/EstadoSolicitud.php';
require_once BASE_PATH . '/app/models/Persona.php';
require_once BASE_PATH . '/app/models/Solicitud.php';
require_once BASE_PATH . '/app/models/Configuracion.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';
require_once BASE_PATH . '/app/helpers/Session.php';
require_once BASE_PATH . '/app/middleware/Csrf.php';
require_once BASE_PATH . '/app/helpers/Response.php';

class SolicitudController
{
    private $service;

    public function __construct()
    {
        $this->service = new SolicitudService();
    }

    /**
     * Buscar persona por documento para el panel del operador
     */
    public function buscarPersona()
    {
        $documento = trim($_GET['documento'] ?? '');

        if (empty($documento)) {
            Response::error('El documento es requerido');
            return;
        }

        $personaModel = new Persona();
        $persona = $personaModel->buscarPorDocumento($documento);

        if (!$persona) {
            // 200 con success:false porque "no encontrada" no es un error de request,
            // es un resultado normal que dispara el flujo de registro de nueva persona.
            Response::error('Persona no encontrada', 200);
            return;
        }

        if (!$persona['activo']) {
            Response::json([
                'success'          => false,
                'persona_inactiva' => true,
                'message'          => 'Esta persona está inactiva en el sistema y no puede realizar solicitudes.',
            ], 200);
            return;
        }

        $solicitudModel = new Solicitud();
        $tipo = Configuracion::getPeriodoTipo();

        $persona['nombre_completo'] = Persona::getNombreCompleto($persona);

        // 1) Bloqueo: ya tiene solicitud activa en los últimos 30 días
        $solicitudExistente = $solicitudModel->getSolicitudActivaEnPeriodo($persona['id']);
        $persona['ya_solicito_periodo'] = !empty($solicitudExistente);
        $persona['ya_solicito_mes']     = $persona['ya_solicito_periodo']; // compat frontend
        $persona['periodo_label']       = $tipo === 'weekly' ? 'esta semana' : 'este mes';
        if ($persona['ya_solicito_periodo']) {
            $persona['solicitud_existente'] = $solicitudExistente;
            Response::success($persona, 'Persona encontrada');
            return;
        }

        // 2) Bloqueo: no hay cupos disponibles en la sede de la persona
        require_once BASE_PATH . '/app/services/CupoService.php';
        $cupoService = new CupoService();
        $fechaActual = DateHelper::now();
        $persona['sin_cupo'] = !$cupoService->verificarDisponibilidad((int)$persona['id_sede'], $fechaActual);
        if ($persona['sin_cupo']) {
            $persona['mensaje_sin_cupo'] = 'Los cupos para esta sede se han agotado este mes.';
        }

        Response::success($persona, 'Persona encontrada');
    }

    /**
     * Crear nueva persona desde el panel del operador
     */
    public function crearPersona()
    {
        Csrf::validate();
        
        $data = [
            'tipo_documento'  => $_POST['tipo_documento'] ?? 'CC',
            'documento'       => trim($_POST['documento'] ?? ''),
            'primer_nombre'   => mb_strtoupper(trim($_POST['primer_nombre'] ?? ''), 'UTF-8'),
            'segundo_nombre'  => ($_POST['segundo_nombre'] ?? '') !== '' ? mb_strtoupper(trim($_POST['segundo_nombre']), 'UTF-8') : null,
            'primer_apellido' => mb_strtoupper(trim($_POST['primer_apellido'] ?? ''), 'UTF-8'),
            'segundo_apellido'=> ($_POST['segundo_apellido'] ?? '') !== '' ? mb_strtoupper(trim($_POST['segundo_apellido']), 'UTF-8') : null,
            'telefono'        => trim($_POST['telefono'] ?? '') ?: null,
            'id_sede'         => (int)($_POST['id_sede'] ?? 0),
            'activo'          => 1,
        ];

        $resultado = (new PersonaService())->crear($data);

        if ($resultado['success']) {
            Response::success($resultado['data'], $resultado['message']);
        } else {
            Response::error($resultado['message']);
        }
    }

    public function formTouch()
    {
        // Fase 3: Panel del operador optimizado para pantalla táctil
        $sedeModel = new Sede();
        $motivoModel = new MotivoRamo();
        
        $sedes = $sedeModel->getActivas();
        $motivos = $motivoModel->getActivos();
        
        $hoy = DateHelper::today();
        $flash = Session::getFlash('mensaje');
        $flashTipo = Session::getFlash('tipo');
        $pageTitle = 'Panel del Operador';
        $csrfField = Csrf::field();
        
        ob_start();
        require BASE_PATH . '/app/views/solicitudes/formulario_operador.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/views/layouts/main.php';
    }

    public function crearTouch()
    {
        Csrf::validate();
        
        $data = [
            'persona_id'         => (int)($_POST['persona_id'] ?? 0),
            'fecha_solicitud'    => !empty($_POST['fecha_solicitud']) ? $_POST['fecha_solicitud'] : DateHelper::now(),
            'id_sede'            => (int)($_POST['id_sede'] ?? 0),
            'nombre_destinatario'=> trim($_POST['nombre_destinatario'] ?? 'Solicitante'),
            'id_motivo'          => (int)($_POST['id_motivo'] ?? 0),
            'motivo_otro'        => trim($_POST['motivo_otro'] ?? '') ?: null,
            'observaciones'      => trim($_POST['observaciones'] ?? '') ?: null,
        ];

        $resultado = $this->service->crear($data);

        if ($resultado['success']) {
            Response::success(null, $resultado['message']);
        } else {
            Response::error($resultado['message']);
        }
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

    public function crear()
    {
        Csrf::validate();

        $data = [
            'persona_id'         => (int)($_POST['persona_id'] ?? 0),
            'fecha_solicitud'    => !empty($_POST['fecha_solicitud']) ? $_POST['fecha_solicitud'] : DateHelper::now(),
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

    public function eliminar()
    {
        Csrf::validate();
        $id = (int)($_POST['id'] ?? 0);

        $resultado = $this->service->eliminar($id);
        if ($resultado['success']) {
            Response::success(null, $resultado['message']);
        } else {
            Response::error($resultado['message']);
        }
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
