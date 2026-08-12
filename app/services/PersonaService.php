<?php
require_once BASE_PATH . '/app/models/Persona.php';
require_once BASE_PATH . '/app/helpers/Validator.php';
require_once BASE_PATH . '/app/helpers/BarcodeParser.php';

class PersonaService
{
    private $personaModel;

    public function __construct()
    {
        $this->personaModel = new Persona();
    }

    public function buscar($documento)
    {
        $documento = trim($documento);
        if (empty($documento)) {
            return null;
        }
        $persona = $this->personaModel->buscarPorDocumento($documento);
        if ($persona) {
            $persona['nombre_completo'] = Persona::getNombreCompleto($persona);
        }
        return $persona;
    }

    public function crear($data)
    {
        $errors = $this->validar($data);
        if (!empty($errors)) {
            return ['success' => false, 'message' => 'Datos inválidos', 'errors' => $errors];
        }

        if ($this->personaModel->existeDocumento($data['documento'])) {
            return ['success' => false, 'message' => 'Ya existe una persona con este documento'];
        }

        try {
            $id = $this->personaModel->create($data);
            if (!$id) {
                return ['success' => false, 'message' => 'Error al crear la persona'];
            }
            $persona = $this->personaModel->getById($id);
            $persona['nombre_completo'] = Persona::getNombreCompleto($persona);
            return ['success' => true, 'message' => 'Persona creada exitosamente', 'data' => $persona];
        } catch (Exception $e) {
            error_log("PersonaService::crear - " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear la persona'];
        }
    }

    public function parsearEntradaEscaner($raw)
    {
        $parsed = BarcodeParser::parse($raw);
        $documento = $parsed['documento'];
        $persona = !empty($documento) ? $this->buscar($documento) : null;

        return [
            'persona'   => $persona,
            'documento' => $documento,
            'formato'   => $parsed['formato'],
        ];
    }

    private function validar($data)
    {
        $errors = [];
        $tiposValidos = ['CC', 'CE', 'TI', 'PA', 'NIT', 'PT'];

        if (!Validator::required($data['tipo_documento'] ?? '')) {
            $errors['tipo_documento'] = 'Seleccione tipo de documento';
        } elseif (!Validator::inArray($data['tipo_documento'], $tiposValidos)) {
            $errors['tipo_documento'] = 'Tipo de documento inválido';
        }

        if (!Validator::required($data['documento'] ?? '')) {
            $errors['documento'] = 'Documento es requerido';
        } elseif (!Validator::onlyNumbers($data['documento'])) {
            $errors['documento'] = 'Documento debe contener solo números';
        } elseif (!Validator::minLength($data['documento'], 5) || !Validator::maxLength($data['documento'], 20)) {
            $errors['documento'] = 'Documento debe tener entre 5 y 20 dígitos';
        }

        if (!Validator::required($data['primer_nombre'] ?? '')) {
            $errors['primer_nombre'] = 'Primer nombre es requerido';
        }

        if (!Validator::required($data['primer_apellido'] ?? '')) {
            $errors['primer_apellido'] = 'Primer apellido es requerido';
        }

        if (empty($data['id_sede']) || (int)$data['id_sede'] < 1) {
            $errors['id_sede'] = 'Seleccione una sede';
        }

        return $errors;
    }
}
