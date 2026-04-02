<?php
require_once BASE_PATH . '/app/models/EstadoSolicitud.php';

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

        if (empty($nombre)) {
            Response::error('El nombre es requerido');
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

        if ($id <= 0 || empty($nombre)) {
            Response::error('Datos invalidos');
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
}
