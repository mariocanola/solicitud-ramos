<?php
require_once BASE_PATH . '/app/models/Sede.php';
require_once BASE_PATH . '/app/models/MotivoRamo.php';
require_once BASE_PATH . '/app/models/EstadoSolicitud.php';

class SedeController
{
    public function listarActivas()
    {
        $model = new Sede();
        Response::success($model->getActivas());
    }

    public function listarMotivos()
    {
        $model = new MotivoRamo();
        Response::success($model->getActivos());
    }

    public function listarEstados()
    {
        $model = new EstadoSolicitud();
        Response::success($model->getAll());
    }

    public function crear()
    {
        Csrf::validate();

        $nombre = trim($_POST['nombre'] ?? '');
        $codigo = trim($_POST['codigo'] ?? '');
        $direccion = trim($_POST['direccion'] ?? '') ?: null;
        $activo = (int)($_POST['activo'] ?? 1);

        if (empty($nombre) || empty($codigo)) {
            Response::error('Nombre y codigo son requeridos');
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
            $model->delete($id);
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

        if ($id <= 0 || empty($nombre) || empty($codigo)) {
            Response::error('Datos invalidos');
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
}
