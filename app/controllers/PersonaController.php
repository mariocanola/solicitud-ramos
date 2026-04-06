<?php
require_once BASE_PATH . '/app/services/PersonaService.php';
require_once BASE_PATH . '/app/models/Persona.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/helpers/Validator.php';

class PersonaController
{
    private $service;

    private static $tiposDocumentoValidos = ['CC', 'CE', 'TI', 'PA', 'NIT'];

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

        if ($id <= 0) {
            Response::error('ID invalido');
            return;
        }

        $errors = $this->validarActualizar($data);
        if (!empty($errors)) {
            Response::error('Datos invalidos', 400, $errors);
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

    private function validarActualizar($data)
    {
        $errors = [];

        if (!Validator::inArray($data['tipo_documento'], self::$tiposDocumentoValidos)) {
            $errors['tipo_documento'] = 'Tipo de documento invalido';
        }

        if (!Validator::required($data['primer_nombre'])) {
            $errors['primer_nombre'] = 'El primer nombre es requerido';
        } elseif (!Validator::maxLength($data['primer_nombre'], 100)) {
            $errors['primer_nombre'] = 'El primer nombre no puede exceder 100 caracteres';
        }

        if (!Validator::required($data['primer_apellido'])) {
            $errors['primer_apellido'] = 'El primer apellido es requerido';
        } elseif (!Validator::maxLength($data['primer_apellido'], 100)) {
            $errors['primer_apellido'] = 'El primer apellido no puede exceder 100 caracteres';
        }

        if ($data['telefono'] !== null && !Validator::onlyNumbers($data['telefono'])) {
            $errors['telefono'] = 'El telefono solo puede contener numeros';
        }

        if ($data['id_sede'] <= 0) {
            $errors['id_sede'] = 'Debe seleccionar una sede';
        }

        if (!in_array($data['activo'], [0, 1], true)) {
            $errors['activo'] = 'El estado debe ser activo o inactivo';
        }

        return $errors;
    }
}
