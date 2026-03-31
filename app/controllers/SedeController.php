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
}
