<?php
require_once BASE_PATH . '/app/models/Configuracion.php';

class ConfigController
{
    public function index()
    {
        $configModel = new Configuracion();
        $configuraciones = $configModel->getAllWithDescriptions();
        $pageTitle = 'Configuración';
        $csrfField = Csrf::field();
        $flash = Session::getFlash('mensaje');
        $flashTipo = Session::getFlash('tipo');

        ob_start();
        require BASE_PATH . '/app/views/configuracion/general.php';
        $content = ob_get_clean();
        require BASE_PATH . '/app/views/layouts/main.php';
    }

    public function guardar()
    {
        Csrf::validate();

        $configModel = new Configuracion();
        $claves = ['correo_destino', 'correo_cc', 'cupo_default', 'smtp_host',
                    'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure',
                    'nombre_organizacion', 'logo_path'];

        foreach ($claves as $clave) {
            if (isset($_POST[$clave])) {
                $configModel->set($clave, trim($_POST[$clave]));
            }
        }

        Session::flash('mensaje', 'Configuración guardada exitosamente');
        Session::flash('tipo', 'success');
        Response::redirect('configuracion');
    }
}
