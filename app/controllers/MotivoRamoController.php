<?php
require_once BASE_PATH . '/app/models/MotivoRamo.php';

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

        if (empty($nombre)) {
            Response::error('El nombre es requerido');
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

        if ($id <= 0 || empty($nombre)) {
            Response::error('Datos invalidos');
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
}
