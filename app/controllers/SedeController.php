<?php
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/models/MotivoRamo.php';
require_once BASE_PATH . '/app/models/EstadoSolicitud.php';
require_once BASE_PATH . '/app/helpers/Validator.php';

class SedeController
{
    public function listarActivas()
    {
        $model = new Sede();
        Response::success($model->getActivas());
    }

    public function crear()
    {
        Csrf::validate();

        $nombre = trim($_POST['nombre'] ?? '');
        $codigo = trim($_POST['codigo'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '') ?: null;
        $activo = (int)($_POST['activo'] ?? 1);

        $errors = $this->validar($nombre, $codigo, $direccion, $activo);
        if (!empty($errors)) {
            Response::error('Datos invalidos', 400, $errors);
            return;
        }

        try {
            $model = new Sede();
            $id = $model->create([
                'nombre' => $nombre,
                'codigo' => $codigo,
                'direccion' => $direccion,
                'activo' => $activo,
            ]);
            Response::success(['id' => $id], 'Sede creada exitosamente');
        } catch (Exception $e) {
            $msg = 'Error al crear la sede';
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $msg = 'Ya existe una sede con ese nombre o codigo';
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

        $model = new Sede();
        $asociados = $model->tieneRegistrosAsociados($id);
        if ($asociados) {
            $mensajes = [
                'solicitudes' => 'No se puede eliminar esta sede porque tiene solicitudes asociadas. Desactivela en su lugar.',
                'personas'    => 'No se puede eliminar esta sede porque tiene personas asociadas. Reasigne las personas o desactivela en su lugar.',
            ];
            Response::error($mensajes[$asociados] ?? 'No se puede eliminar esta sede porque tiene registros asociados.');
            return;
        }

        try {
            $model->eliminarConDependencias($id);
            Response::success(null, 'Sede eliminada exitosamente');
        } catch (Exception $e) {
            Response::error('Error al eliminar la sede: tiene registros dependientes');
        }
    }

    public function actualizar()
    {
        Csrf::validate();

        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $codigo = trim($_POST['codigo'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '') ?: null;
        $activo = (int)($_POST['activo'] ?? 1);

        if ($id <= 0) {
            Response::error('ID invalido');
            return;
        }

        $errors = $this->validar($nombre, $codigo, $direccion, $activo);
        if (!empty($errors)) {
            Response::error('Datos invalidos', 400, $errors);
            return;
        }

        try {
            $model = new Sede();
            $model->update($id, [
                'nombre' => $nombre,
                'codigo' => $codigo,
                'direccion' => $direccion,
                'activo' => $activo,
            ]);
            Response::success(null, 'Sede actualizada exitosamente');
        } catch (Exception $e) {
            $msg = 'Error al actualizar la sede';
            if (strpos($e->getMessage(), 'Duplicate') !== false) {
                $msg = 'Ya existe una sede con ese nombre o codigo';
            }
            Response::error($msg);
        }
    }

    private function validar($nombre, $codigo, $direccion, $activo)
    {
        $errors = [];

        if (!Validator::required($nombre)) {
            $errors['nombre'] = 'El nombre es requerido';
        } elseif (!Validator::minLength($nombre, 2) || !Validator::maxLength($nombre, 100)) {
            $errors['nombre'] = 'El nombre debe tener entre 2 y 100 caracteres';
        }

        if (!Validator::required($codigo)) {
            $errors['codigo'] = 'El codigo es requerido';
        } elseif (!Validator::minLength($codigo, 1) || !Validator::maxLength($codigo, 20)) {
            $errors['codigo'] = 'El codigo debe tener entre 1 y 20 caracteres';
        } elseif (!preg_match('/^[a-zA-Z0-9_\-]+$/', $codigo)) {
            $errors['codigo'] = 'El codigo solo puede contener letras, numeros, guiones y guion bajo';
        }

        if ($direccion !== null && !Validator::maxLength($direccion, 255)) {
            $errors['direccion'] = 'La direccion no puede exceder 255 caracteres';
        }

        if (!in_array($activo, [0, 1], true)) {
            $errors['activo'] = 'El estado debe ser activo o inactivo';
        }

        return $errors;
    }
}
