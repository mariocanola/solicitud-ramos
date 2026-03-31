<?php
require_once BASE_PATH . '/app/services/PersonaService.php';

class PersonaController
{
    private $service;

    public function __construct()
    {
        $this->service = new PersonaService();
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
