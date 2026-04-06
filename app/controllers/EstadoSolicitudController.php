<?php
require_once BASE_PATH . '/app/models/EstadoSolicitud.php';
require_once BASE_PATH . '/app/helpers/Validator.php';

class EstadoSolicitudController
{
    public function listar()
    {
        $model = new EstadoSolicitud();
        Response::success($model->getAll());
    }

    public function crear()
    {
        Csrf::validate();

        $nombre = trim($_POST['nombre'] ?? '');
        $color = trim($_POST['color'] ?? '#6c757d');
        $orden = (int)($_POST['orden'] ?? 0);

        $errors = $this->validar($nombre, $color, $orden);
        if (!empty($errors)) {
            Response::error('Datos invalidos', 400, $errors);
            return;
        }

        try {
            $model = new EstadoSolicitud();
            $id = $model->create([
                'nombre' => $nombre,
                'color' => $color,
                'orden' => $orden,
            ]);
            Response::success(['id' => $id], 'Estado creado exitosamente');
        } catch (Exception $e) {
            $msg = 'Error al crear el estado';
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $msg = 'Ya existe un estado con ese nombre';
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

        $model = new EstadoSolicitud();
        if ($model->tieneRegistrosAsociados($id)) {
            Response::error('No se puede eliminar este estado porque tiene solicitudes asociadas');
            return;
        }

        try {
            $model->delete($id);
            Response::success(null, 'Estado eliminado exitosamente');
        } catch (Exception $e) {
            Response::error('Error al eliminar el estado');
        }
    }

    public function actualizar()
    {
        Csrf::validate();

        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $color = trim($_POST['color'] ?? '#6c757d');
        $orden = (int)($_POST['orden'] ?? 0);

        if ($id <= 0) {
            Response::error('ID invalido');
            return;
        }

        $errors = $this->validar($nombre, $color, $orden);
        if (!empty($errors)) {
            Response::error('Datos invalidos', 400, $errors);
            return;
        }

        try {
            $model = new EstadoSolicitud();
            $model->update($id, [
                'nombre' => $nombre,
                'color' => $color,
                'orden' => $orden,
            ]);
            Response::success(null, 'Estado actualizado exitosamente');
        } catch (Exception $e) {
            $msg = 'Error al actualizar el estado';
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $msg = 'Ya existe un estado con ese nombre';
            }
            Response::error($msg);
        }
    }

    private function validar($nombre, $color, $orden)
    {
        $errors = [];

        if (!Validator::required($nombre)) {
            $errors['nombre'] = 'El nombre es requerido';
        } elseif (!Validator::minLength($nombre, 2) || !Validator::maxLength($nombre, 50)) {
            $errors['nombre'] = 'El nombre debe tener entre 2 y 50 caracteres';
        }

        if (!preg_match('/^#[0-9A-Fa-f]{6}$/', $color)) {
            $errors['color'] = 'El color debe ser un valor hexadecimal valido (#RRGGBB)';
        }

        if (!Validator::integer($orden) || $orden < 0 || $orden > 999) {
            $errors['orden'] = 'El orden debe ser un numero entre 0 y 999';
        }

        return $errors;
    }
}
