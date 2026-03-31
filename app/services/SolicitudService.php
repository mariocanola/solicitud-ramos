<?php
require_once BASE_PATH . '/app/models/Database.php';
require_once BASE_PATH . '/app/models/Solicitud.php';
require_once BASE_PATH . '/app/models/Persona.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/models/MotivoRamo.php';
require_once BASE_PATH . '/app/services/CupoService.php';
require_once BASE_PATH . '/app/helpers/Validator.php';
require_once BASE_PATH . '/app/helpers/DateHelper.php';

class SolicitudService
{
    private $solicitudModel;
    private $personaModel;
    private $sedeModel;
    private $motivoModel;
    private $cupoService;

    public function __construct()
    {
        $this->solicitudModel = new Solicitud();
        $this->personaModel = new Persona();
        $this->sedeModel = new Sede();
        $this->motivoModel = new MotivoRamo();
        $this->cupoService = new CupoService();
    }

    public function crear($data)
    {
        // Validate
        $validacion = $this->validar($data);
        if (!$validacion['valid']) {
            return ['success' => false, 'message' => implode('. ', $validacion['errors'])];
        }

        // Check persona exists and is active
        $persona = $this->personaModel->getById($data['persona_id']);
        if (!$persona) {
            return ['success' => false, 'message' => 'La persona no existe'];
        }
        if (!$persona['activo']) {
            return ['success' => false, 'message' => 'La persona no está activa'];
        }

        // La solicitud debe usar la sede de la persona
        $data['id_sede'] = (int)$persona['id_sede'];

        // Check sede is active
        $sede = $this->sedeModel->getById($data['id_sede']);
        if (!$sede || !$sede['activo']) {
            return ['success' => false, 'message' => 'La sede no es válida o no está activa'];
        }

        // Check cupo
        $fecha = $data['fecha_solicitud'] ?? DateHelper::today();
        if (!$this->cupoService->verificarDisponibilidad($data['id_sede'], $fecha)) {
            return ['success' => false, 'message' => 'No hay cupo disponible para esta sede en el periodo actual'];
        }

        // Check si la persona ya tiene una solicitud en el mismo mes
        if ($this->solicitudModel->tieneSolicitudEnMes($data['persona_id'], $fecha)) {
            return ['success' => false, 'message' => 'La persona ya tiene una solicitud registrada en este mes. Solo se permite un ramo por mes por persona.'];
        }

        // Check motivo "otro"
        $motivo = $this->motivoModel->getById($data['id_motivo']);
        if ($motivo && $motivo['requiere_detalle'] && empty($data['motivo_otro'])) {
            return ['success' => false, 'message' => 'Debe especificar el motivo cuando selecciona "Otro"'];
        }

        // Transaction
        $db = Database::getInstance()->getConnection();
        try {
            $db->beginTransaction();

            $data['fecha_solicitud'] = $fecha;
            // Si es la primera solicitud de la persona, se aprueba automáticamente
            $data['id_estado'] = 2; // 2 = Aprobada
            $id = $this->solicitudModel->create($data);

            $this->cupoService->incrementar($data['id_sede'], $fecha);

            $db->commit();

            return ['success' => true, 'message' => 'Solicitud creada y aprobada exitosamente', 'data' => ['id' => $id]];
        } catch (Exception $e) {
            $db->rollBack();
            error_log("SolicitudService::crear - " . $e->getMessage());
            return ['success' => false, 'message' => 'Error al crear la solicitud'];
        }
    }

    public function listar($filtros = [])
    {
        return $this->solicitudModel->listar($filtros);
    }

    public function obtener($id)
    {
        return $this->solicitudModel->getById($id);
    }

    public function cambiarEstado($id, $id_estado)
    {
        $solicitud = $this->solicitudModel->getById($id);
        if (!$solicitud) {
            return ['success' => false, 'message' => 'Solicitud no encontrada'];
        }

        // If cancelling, decrement cupo
        if ((int)$id_estado === 5 && (int)$solicitud['id_estado'] !== 5) {
            $this->cupoService->decrementar($solicitud['id_sede'], $solicitud['fecha_solicitud']);
        }

        $this->solicitudModel->cambiarEstado($id, $id_estado);
        return ['success' => true, 'message' => 'Estado actualizado'];
    }

    private function validar($data)
    {
        $errors = [];

        if (empty($data['persona_id']) || (int)$data['persona_id'] < 1) {
            $errors[] = 'Debe seleccionar una persona';
        }
        if (empty($data['id_sede']) || (int)$data['id_sede'] < 1) {
            $errors[] = 'Debe seleccionar una sede';
        }
        if (empty($data['nombre_destinatario']) || !Validator::minLength($data['nombre_destinatario'], 2)) {
            $errors[] = 'El nombre del destinatario es requerido (mínimo 2 caracteres)';
        }
        if (empty($data['id_motivo']) || (int)$data['id_motivo'] < 1) {
            $errors[] = 'Debe seleccionar un motivo';
        }
        if (!empty($data['fecha_solicitud']) && !Validator::date($data['fecha_solicitud'])) {
            $errors[] = 'Fecha inválida';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }
}
