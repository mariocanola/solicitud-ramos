<?php
require_once BASE_PATH . '/app/services/PersonaService.php';
require_once BASE_PATH . '/app/models/Persona.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/helpers/Validator.php';

class PersonaController
{
    private $service;

    private static $tiposDocumentoValidos = ['CC', 'CE', 'TI', 'PA', 'NIT', 'PT'];

    public function __construct()
    {
        $this->service = new PersonaService();
    }

    public function index()
    {
        $personaModel = new Persona();
        $sedeModel = new Sede();

        $filtros = [
            'busqueda'   => trim($_GET['busqueda'] ?? ''),
            'id_sede'    => $_GET['id_sede'] ?? '',
            'empresa'    => $_GET['empresa'] ?? '',
            'pagina'     => $_GET['pagina'] ?? 1,
            'por_pagina' => $_GET['por_pagina'] ?? 15,
        ];

        $resultado = $personaModel->getAll($filtros);
        $sedes = $sedeModel->getAll();

        $personas = $resultado['data'];
        $totalPaginas = $resultado['total_paginas'];
        $paginaActual = $resultado['pagina'];
        $totalRegistros = $resultado['total'];
        $porPagina = $resultado['por_pagina'];

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

        try {
            $personaModel = new Persona();
            $personaModel->desactivar($id);
            Response::success(null, 'Persona inhabilitada exitosamente');
        } catch (Exception $e) {
            Response::error('Error al inhabilitar la persona');
        }
    }

    public function habilitar()
    {
        Csrf::validate();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            Response::error('ID invalido');
            return;
        }

        try {
            $personaModel = new Persona();
            $personaModel->activar($id);
            Response::success(null, 'Persona habilitada exitosamente');
        } catch (Exception $e) {
            Response::error('Error al habilitar la persona');
        }
    }

    public function actualizar()
    {
        Csrf::validate();

        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'tipo_documento'  => trim($_POST['tipo_documento'] ?? ''),
            'documento'       => trim($_POST['documento'] ?? ''),
            'primer_nombre'   => mb_strtoupper(trim($_POST['primer_nombre'] ?? ''), 'UTF-8'),
            'segundo_nombre'  => ($_POST['segundo_nombre'] ?? '') !== '' ? mb_strtoupper(trim($_POST['segundo_nombre']), 'UTF-8') : null,
            'primer_apellido' => mb_strtoupper(trim($_POST['primer_apellido'] ?? ''), 'UTF-8'),
            'segundo_apellido'=> ($_POST['segundo_apellido'] ?? '') !== '' ? mb_strtoupper(trim($_POST['segundo_apellido']), 'UTF-8') : null,
            'telefono'        => trim($_POST['telefono'] ?? '') ?: null,
            'id_sede'         => (int)($_POST['id_sede'] ?? 0),
            'activo'          => (int)($_POST['activo'] ?? 1),
        ];

        if ($id <= 0) {
            Response::error('ID invalido');
            return;
        }

        if (empty($data['documento']) || !preg_match('/^\d{4,20}$/', $data['documento'])) {
            Response::error('El documento es invalido (debe ser numerico, entre 4 y 20 digitos)');
            return;
        }

        $errors = $this->validarActualizar($data);
        if (!empty($errors)) {
            Response::error('Datos invalidos', 400, $errors);
            return;
        }

        try {
            $personaModel = new Persona();
            if ($personaModel->existeDocumentoOtro($data['documento'], $id)) {
                Response::error('El documento ya está registrado para otra persona');
                return;
            }
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
            'primer_nombre'   => mb_strtoupper(trim($_POST['primer_nombre'] ?? ''), 'UTF-8'),
            'segundo_nombre'  => ($_POST['segundo_nombre'] ?? '') !== '' ? mb_strtoupper(trim($_POST['segundo_nombre']), 'UTF-8') : null,
            'primer_apellido' => mb_strtoupper(trim($_POST['primer_apellido'] ?? ''), 'UTF-8'),
            'segundo_apellido'=> ($_POST['segundo_apellido'] ?? '') !== '' ? mb_strtoupper(trim($_POST['segundo_apellido']), 'UTF-8') : null,
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

    public function importar()
    {
        $pageTitle = 'Cargar maestro de personas';
        $csrfField = Csrf::field();
        $flash = Session::getFlash('mensaje');
        $flashTipo = Session::getFlash('tipo');

        ob_start();
        require BASE_PATH . '/app/views/personas/importar.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/views/layouts/main.php';
    }

    public function descargarPlantilla()
    {
        $ruta = BASE_PATH . '/public/templates/plantilla_maestro_personas.xlsx';
        if (!is_file($ruta)) {
            http_response_code(404);
            echo 'Plantilla no encontrada';
            return;
        }
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="plantilla_maestro_personas.xlsx"');
        header('Content-Length: ' . filesize($ruta));
        header('Cache-Control: no-cache, must-revalidate');
        readfile($ruta);
    }

    public function subirMaestro()
    {
        Csrf::validate();

        if (empty($_FILES['archivo']) || $_FILES['archivo']['error'] !== UPLOAD_ERR_OK) {
            $this->redirectImportError('Debe seleccionar un archivo valido.');
        }

        $tmp  = $_FILES['archivo']['tmp_name'];
        $name = $_FILES['archivo']['name'];
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

        if (!in_array($ext, ['xlsx', 'xls', 'csv'], true)) {
            $this->redirectImportError('Formato no soportado. Use .xlsx, .xls o .csv.');
        }

        if ($_FILES['archivo']['size'] > 10 * 1024 * 1024) {
            $this->redirectImportError('El archivo excede 10 MB.');
        }

        require_once BASE_PATH . '/app/services/MaestroImportService.php';
        $service = new MaestroImportService();

        try {
            $entrada = $service->leerArchivo($tmp);
            $clasificacion = $service->clasificar($entrada);
        } catch (Exception $e) {
            $this->redirectImportError('Error al procesar el archivo: ' . $e->getMessage());
        }

        $this->guardarPreview($clasificacion, $name);
        header('Location: ' . BASE_URL . '/personas/importar/preview');
    }

    public function previewMaestro()
    {
        $preview = $this->cargarPreview();
        if ($preview === null) {
            header('Location: ' . BASE_URL . '/personas/importar');
            return;
        }
        $clasificacion = $preview['clasificacion'];
        $archivo       = $preview['archivo'];

        $pageTitle = 'Previsualizacion de maestro';
        $csrfField = Csrf::field();

        ob_start();
        require BASE_PATH . '/app/views/personas/importar_preview.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/views/layouts/main.php';
    }

    public function confirmarImport()
    {
        Csrf::validate();

        $preview = $this->cargarPreview();
        if ($preview === null) {
            Session::flash('mensaje', 'No hay datos para importar. Vuelva a cargar el archivo.');
            Session::flash('tipo', 'warning');
            header('Location: ' . BASE_URL . '/personas/importar');
            return;
        }

        require_once BASE_PATH . '/app/services/MaestroImportService.php';
        $service = new MaestroImportService();

        try {
            $resultado = $service->ejecutar($preview['clasificacion']);
        } catch (Exception $e) {
            $this->redirectImportError('Error al ejecutar la importacion: ' . $e->getMessage());
        }

        $this->limpiarPreview();

        $msg = sprintf(
            'Importacion completada. Nuevas: %d, actualizadas: %d, reactivadas: %d, dadas de baja: %d.',
            $resultado['insertadas'], $resultado['actualizadas'], $resultado['reactivadas'], $resultado['desactivadas']
        );
        Session::flash('mensaje', $msg);
        Session::flash('tipo', 'success');
        header('Location: ' . BASE_URL . '/personas');
    }

    private function redirectImportError($mensaje)
    {
        Session::flash('mensaje', $mensaje);
        Session::flash('tipo', 'danger');
        header('Location: ' . BASE_URL . '/personas/importar');
        exit;
    }

    // Persiste la clasificacion (hasta ~1200 filas) en archivo temporal en lugar
    // de $_SESSION para evitar serializar el payload en cada request.
    private function guardarPreview(array $clasificacion, $archivo)
    {
        $token = bin2hex(random_bytes(16));
        $dir = BASE_PATH . '/storage/maestro_imports';
        if (!is_dir($dir)) mkdir($dir, 0775, true);
        file_put_contents($dir . '/' . $token . '.json', json_encode([
            'clasificacion' => $clasificacion,
            'archivo'       => $archivo,
            'creado'        => time(),
        ]));
        $_SESSION['maestro_preview_token'] = $token;
    }

    private function cargarPreview()
    {
        $token = $_SESSION['maestro_preview_token'] ?? null;
        if (!$token) return null;
        $ruta = BASE_PATH . '/storage/maestro_imports/' . preg_replace('/[^a-f0-9]/', '', $token) . '.json';
        if (!is_file($ruta)) return null;
        $data = json_decode(file_get_contents($ruta), true);
        return is_array($data) ? $data : null;
    }

    public function cancelarImport()
    {
        $this->limpiarPreview();
        header('Location: ' . BASE_URL . '/personas');
    }

    private function limpiarPreview()
    {
        $token = $_SESSION['maestro_preview_token'] ?? null;
        if ($token) {
            $ruta = BASE_PATH . '/storage/maestro_imports/' . preg_replace('/[^a-f0-9]/', '', $token) . '.json';
            if (is_file($ruta)) @unlink($ruta);
        }
        unset($_SESSION['maestro_preview_token']);
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
