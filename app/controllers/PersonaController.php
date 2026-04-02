<?php
require_once BASE_PATH . '/app/services/PersonaService.php';
require_once BASE_PATH . '/app/models/Persona.php';
require_once BASE_PATH . '/app/models/Sede.php';

class PersonaController
{
    private $service;

    public function __construct()
    {
        $this->service = new PersonaService();
    }

    public function index()
    {
        $personaModel = new Persona();
        $sedeModel = new Sede();

        $filtros = [
            'busqueda' => trim($_GET['busqueda'] ?? ''),
            'id_sede'  => $_GET['id_sede'] ?? '',
            'pagina'   => $_GET['pagina'] ?? 1,
        ];

        $resultado = $personaModel->getAll($filtros);
        $sedes = $sedeModel->getAll();

        $personas = $resultado['data'];
        $totalPaginas = $resultado['total_paginas'];
        $paginaActual = $resultado['pagina'];
        $totalRegistros = $resultado['total'];

        $pageTitle = 'Personas';
        $csrfField = Csrf::field();
        $flash = Session::getFlash('mensaje');
        $flashTipo = Session::getFlash('tipo');

        ob_start();
        require BASE_PATH . '/app/views/personas/index.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/views/layouts/main.php';
    }

    public function eliminar()
    {
        Csrf::validate();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            Response::error('ID invalido');
            return;
        }

        $personaModel = new Persona();
        if ($personaModel->tieneRegistrosAsociados($id)) {
            Response::error('No se puede eliminar esta persona porque tiene solicitudes asociadas. Desactivela en su lugar.');
            return;
        }

        try {
            $personaModel->delete($id);
            Response::success(null, 'Persona eliminada exitosamente');
        } catch (Exception $e) {
            Response::error('Error al eliminar la persona');
        }
    }

    public function actualizar()
    {
        Csrf::validate();

        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'tipo_documento'  => trim($_POST['tipo_documento'] ?? ''),
            'primer_nombre'   => trim($_POST['primer_nombre'] ?? ''),
            'segundo_nombre'  => trim($_POST['segundo_nombre'] ?? '') ?: null,
            'primer_apellido' => trim($_POST['primer_apellido'] ?? ''),
            'segundo_apellido'=> trim($_POST['segundo_apellido'] ?? '') ?: null,
            'telefono'        => trim($_POST['telefono'] ?? '') ?: null,
            'id_sede'         => (int)($_POST['id_sede'] ?? 0),
            'activo'          => (int)($_POST['activo'] ?? 1),
        ];

        if ($id <= 0 || empty($data['primer_nombre']) || empty($data['primer_apellido'])) {
            Response::error('Datos invalidos');
            return;
        }

        try {
            $personaModel = new Persona();
            $personaModel->update($id, $data);
            Response::success(null, 'Persona actualizada exitosamente');
        } catch (Exception $e) {
            Response::error('Error al actualizar la persona');
        }
    }

    public function buscar()
    {
        $documento = trim($_GET['documento'] ?? '');
        if (empty($documento)) {
            Response::error('Documento requerido');
            return;
        }

        $persona = $this->service->buscar($documento);
        if ($persona) {
            Response::success($persona, 'Persona encontrada');
        } else {
            Response::error('Persona no encontrada', 404);
        }
    }

    public function crear()
    {
        Csrf::validate();

        $data = [
            'tipo_documento'  => trim($_POST['tipo_documento'] ?? ''),
            'documento'       => trim($_POST['documento'] ?? ''),
            'primer_nombre'   => trim($_POST['primer_nombre'] ?? ''),
            'segundo_nombre'  => trim($_POST['segundo_nombre'] ?? '') ?: null,
            'primer_apellido' => trim($_POST['primer_apellido'] ?? ''),
            'segundo_apellido'=> trim($_POST['segundo_apellido'] ?? '') ?: null,
            'telefono'        => trim($_POST['telefono'] ?? '') ?: null,
            'id_sede'         => (int)($_POST['id_sede'] ?? 0),
        ];

        $resultado = $this->service->crear($data);
        if ($resultado['success']) {
            Response::success($resultado['data'], $resultado['message']);
        } else {
            Response::error($resultado['message'], 400, $resultado['errors'] ?? []);
        }
    }
}
