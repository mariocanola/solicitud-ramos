<?php
require_once BASE_PATH . '/app/models/Configuracion.php';
require_once BASE_PATH . '/app/models/LogCorreo.php';
require_once BASE_PATH . '/app/models/Sede.php';

class MailService
{
    private $configModel;
    private $logModel;

    public function __construct()
    {
        $this->configModel = new Configuracion();
        $this->logModel = new LogCorreo();
    }

    public function enviarReporte($destinatario, $cc, $asunto, $cuerpoHtml, $adjuntoPdf)
    {
        try {
            $mailConfig = $this->configModel->getMailConfig();

            // PHPMailer autoload
            $phpmailerPath = BASE_PATH . '/vendor/phpmailer/src/PHPMailer.php';
            if (!file_exists($phpmailerPath)) {
                throw new Exception('PHPMailer no instalado. Coloque los archivos en /vendor/phpmailer/src/');
            }

            require_once BASE_PATH . '/vendor/phpmailer/src/Exception.php';
            require_once $phpmailerPath;
            require_once BASE_PATH . '/vendor/phpmailer/src/SMTP.php';

            $mail = new PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $mailConfig['smtp_host'] ?? 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = $mailConfig['smtp_user'] ?? '';
            $mail->Password   = $mailConfig['smtp_pass'] ?? '';
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)($mailConfig['smtp_port'] ?? 587);
            $mail->CharSet    = 'UTF-8';

            $mail->setFrom($mail->Username, $mailConfig['nombre_organizacion'] ?? APP_NAME);

            foreach (array_filter(array_map('trim', explode(',', $destinatario))) as $email) {
                $mail->addAddress($email);
            }

            if (!empty($cc)) {
                foreach (array_filter(array_map('trim', explode(',', $cc))) as $emailCc) {
                    $mail->addCC($emailCc);
                }
            }

            $mail->isHTML(true);
            $mail->Subject = $asunto;
            $mail->Body    = $cuerpoHtml;
            $mail->AltBody = strip_tags($cuerpoHtml);

            if (!empty($adjuntoPdf) && file_exists($adjuntoPdf)) {
                $mail->addAttachment($adjuntoPdf, basename($adjuntoPdf));
            }

            $mail->send();

            $this->logModel->registrar('manual', $destinatario, $asunto, 'enviado');
            return ['success' => true, 'message' => 'Correo enviado exitosamente'];

        } catch (Exception $e) {
            $error = $e->getMessage();
            error_log("MailService::enviarReporte - $error");
            $this->logModel->registrar('manual', $destinatario, $asunto, 'fallido', $error);
            return ['success' => false, 'message' => "Error al enviar correo: $error"];
        }
    }

    public function enviarReporteCupoLleno($id_sede)
    {
        $sedeModel = new Sede();
        $sede = $sedeModel->getById($id_sede);
        $nombreSede = $sede ? $sede['nombre'] : "Sede #$id_sede";

        $destinatario = $this->configModel->get('correo_destino');
        $cc = $this->configModel->get('correo_cc') ?: '';
        $nombreOrg = $this->configModel->get('nombre_organizacion') ?: APP_NAME;

        if (empty($destinatario)) {
            error_log("MailService: No hay correo destino configurado");
            return ['success' => false, 'message' => 'No hay correo destino configurado'];
        }

        require_once BASE_PATH . '/app/services/PdfService.php';
        $pdfService = new PdfService();
        $periodo = date('Y-m-01');
        $rutaPdf = $pdfService->generarConsolidado([
            'id_sede'     => $id_sede,
            'fecha_desde' => $periodo,
            'fecha_hasta' => date('Y-m-t'),
        ]);

        require_once BASE_PATH . '/app/helpers/DateHelper.php';
        $periodoTexto = DateHelper::mesAnio($periodo);
        $asunto = "Cupo Lleno - $nombreSede - $periodoTexto";

        $cuerpo = "
        <html><body style='font-family:Arial,sans-serif;color:#333'>
            <h2 style='color:#2C3E50'>$nombreOrg</h2>
            <h3 style='color:#E74C3C'>Notificación de Cupo Lleno</h3>
            <p>La sede <strong>$nombreSede</strong> ha alcanzado el límite de cupo para <strong>$periodoTexto</strong>.</p>
            <p>Se adjunta el reporte consolidado.</p>
            <hr style='border:1px solid #eee'>
            <p style='font-size:12px;color:#999'>Mensaje automático - $nombreOrg</p>
        </body></html>";

        $resultado = $this->enviarReporte($destinatario, $cc, $asunto, $cuerpo, $rutaPdf);

        if ($resultado['success']) {
            $this->logModel->registrar('automatico', $destinatario, $asunto, 'enviado');
        }

        return $resultado;
    }

    public function enviarReporteManual($filtros)
    {
        $destinatario = $this->configModel->get('correo_destino');
        $cc = $this->configModel->get('correo_cc') ?: '';
        $nombreOrg = $this->configModel->get('nombre_organizacion') ?: APP_NAME;

        if (empty($destinatario)) {
            return ['success' => false, 'message' => 'No hay correo destino configurado'];
        }

        require_once BASE_PATH . '/app/services/PdfService.php';
        $pdfService = new PdfService();
        $rutaPdf = $pdfService->generarConsolidado($filtros);

        $desde = $filtros['fecha_desde'] ?? 'N/A';
        $hasta = $filtros['fecha_hasta'] ?? 'N/A';
        $asunto = "Reporte Ramos Florales - $desde a $hasta";

        $cuerpo = "
        <html><body style='font-family:Arial,sans-serif;color:#333'>
            <h2 style='color:#2C3E50'>$nombreOrg</h2>
            <h3>Reporte Consolidado de Solicitudes</h3>
            <p>Período: <strong>$desde</strong> al <strong>$hasta</strong>.</p>
            <p>Se adjunta el reporte consolidado.</p>
            <hr style='border:1px solid #eee'>
            <p style='font-size:12px;color:#999'>Correo generado manualmente - $nombreOrg</p>
        </body></html>";

        return $this->enviarReporte($destinatario, $cc, $asunto, $cuerpo, $rutaPdf);
    }
}
