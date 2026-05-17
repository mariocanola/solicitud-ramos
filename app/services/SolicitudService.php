<?php
require_once BASE_PATH . '/app/models/Database.php';
require_once BASE_PATH . '/app/models/Solicitud.php';
require_once BASE_PATH . '/app/models/Persona.php';
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/models/MotivoRamo.php';
require_once BASE_PATH . '/app/models/Configuracion.php';
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

        // Check cupo. Si no viene fecha del formulario, usamos now() con hora real
        // para que la regla del corte de 6 AM (modo weekly) aplique correctamente.
        $fecha = !empty($data['fecha_solicitud']) ? $data['fecha_solicitud'] : DateHelper::now();
        if (!$this->cupoService->verificarDisponibilidad($data['id_sede'], $fecha)) {
            $tipo = Configuracion::getPeriodoTipo();
            $mensajePeriodo = $tipo === 'weekly' 
                ? 'semana' 
                : 'mes';
            return [
                'success' => false,
                'message' => "Lo sentimos, se han agotado los cupos disponibles para esta sede en esta {$mensajePeriodo}."
            ];
        }

        // Check si la persona ya tiene una solicitud en el mismo período
        // Siempre verificar contra la fecha actual, no contra la fecha de la solicitud
        // Esto evita que el usuario cambie la fecha para hacer múltiples solicitudes
        $tipo = Configuracion::getPeriodoTipo();
        $fechaActual = DateHelper::now();
        if ($this->solicitudModel->tieneSolicitudEnPeriodo($data['persona_id'], $fechaActual, $tipo)) {
            $mensajePeriodo = $tipo === 'weekly' ? 'semana' : 'mes';
            return ['success' => false, 'message' => "La persona ya tiene una solicitud registrada en esta {$mensajePeriodo}. Solo se permite un ramo por {$mensajePeriodo} por persona."];
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

            // Aseguramos que el registro cupos_sede exista para el periodo.
            // El conteo "usado" se calcula en tiempo real desde solicitudes.
            $this->cupoService->asegurarRegistro($data['id_sede'], $fecha);

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

        // El cupo se calcula en tiempo real desde solicitudes filtrando por estado activo,
        // asi que cambiar a Cancelada automaticamente la saca del conteo. No requiere decrementar.
        $this->solicitudModel->cambiarEstado($id, $id_estado);
        return ['success' => true, 'message' => 'Estado actualizado'];
    }

    public function eliminar($id)
    {
        $solicitud = $this->solicitudModel->getById($id);
        if (!$solicitud) {
            return ['success' => false, 'message' => 'Solicitud no encontrada'];
        }
        // Cupo en tiempo real: al borrar la fila desaparece del conteo automaticamente.

        $this->solicitudModel->delete($id);
        return ['success' => true, 'message' => 'Solicitud eliminada exitosamente'];
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
