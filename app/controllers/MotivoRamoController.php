<?php
require_once BASE_PATH . '/app/models/MotivoRamo.php';
require_once BASE_PATH . '/app/helpers/Validator.php';

class MotivoRamoController
{
    public function listar()
    {
        $model = new MotivoRamo();
        Response::success($model->getAll());
    }

    public function crear()
    {
        Csrf::validate();

        $nombre = trim($_POST['nombre'] ?? '');
        $requiere_detalle = (int)($_POST['requiere_detalle'] ?? 0);
        $orden = (int)($_POST['orden'] ?? 0);
        $activo = (int)($_POST['activo'] ?? 1);

        $errors = $this->validar($nombre, $orden, $requiere_detalle, $activo);
        if (!empty($errors)) {
            Response::error('Datos invalidos', 400, $errors);
            return;
        }

        try {
            $model = new MotivoRamo();
            $id = $model->create([
                'nombre' => $nombre,
                'requiere_detalle' => $requiere_detalle,
                'orden' => $orden,
                'activo' => $activo,
            ]);
            Response::success(['id' => $id], 'Motivo creado exitosamente');
        } catch (Exception $e) {
            $msg = 'Error al crear el motivo';
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $msg = 'Ya existe un motivo con ese nombre';
            }
            Response::error($msg);
        }
    }

    public function eliminar()
    {
        Csrf::validate();

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            Response::error('ID invalido');
            return;
        }

        $model = new MotivoRamo();
        if ($model->tieneRegistrosAsociados($id)) {
            Response::error('No se puede eliminar este motivo porque tiene solicitudes asociadas. Desactivelo en su lugar.');
            return;
        }

        try {
            $model->delete($id);
            Response::success(null, 'Motivo eliminado exitosamente');
        } catch (Exception $e) {
            Response::error('Error al eliminar el motivo');
        }
    }

    public function actualizar()
    {
        Csrf::validate();

        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $requiere_detalle = (int)($_POST['requiere_detalle'] ?? 0);
        $orden = (int)($_POST['orden'] ?? 0);
        $activo = (int)($_POST['activo'] ?? 1);

        if ($id <= 0) {
            Response::error('ID invalido');
            return;
        }

        $errors = $this->validar($nombre, $orden, $requiere_detalle, $activo);
        if (!empty($errors)) {
            Response::error('Datos invalidos', 400, $errors);
            return;
        }

        try {
            $model = new MotivoRamo();
            $model->update($id, [
                'nombre' => $nombre,
                'requiere_detalle' => $requiere_detalle,
                'orden' => $orden,
                'activo' => $activo,
            ]);
            Response::success(null, 'Motivo actualizado exitosamente');
        } catch (Exception $e) {
            $msg = 'Error al actualizar el motivo';
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $msg = 'Ya existe un motivo con ese nombre';
            }
            Response::error($msg);
        }
    }

    private function validar($nombre, $orden, $requiere_detalle, $activo)
    {
        $errors = [];

        if (!Validator::required($nombre)) {
            $errors['nombre'] = 'El nombre es requerido';
        } elseif (!Validator::minLength($nombre, 2) || !Validator::maxLength($nombre, 100)) {
            $errors['nombre'] = 'El nombre debe tener entre 2 y 100 caracteres';
        }

        if (!Validator::integer($orden) || $orden < 0 || $orden > 999) {
            $errors['orden'] = 'El orden debe ser un numero entre 0 y 999';
        }

        if (!in_array($requiere_detalle, [0, 1], true)) {
            $errors['requiere_detalle'] = 'Valor invalido para requiere detalle';
        }

        if (!in_array($activo, [0, 1], true)) {
            $errors['activo'] = 'El estado debe ser activo o inactivo';
        }

        return $errors;
    }
}
