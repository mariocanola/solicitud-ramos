<?php
require_once BASE_PATH . '/app/models/Solicitud.php';
require_once BASE_PATH . '/app/models/Persona.php';
require_once BASE_PATH . '/app/models/Configuracion.php';

class PdfService
{
    // Colores corporativos
    private $colorPrimario   = [88, 44, 131];   // Morado (del logo)
    private $colorSecundario = [44, 62, 80];     // Azul oscuro
    private $colorAccento    = [142, 68, 173];   // Morado claro
    private $colorExito      = [39, 174, 96];    // Verde
    private $colorFilaAlt    = [245, 240, 250];  // Morado muy claro para filas alternas

    public function generarConsolidado($filtros = [])
    {
        require_once BASE_PATH . '/vendor/fpdf/fpdf.php';
        $solicitudModel = new Solicitud();
        $configModel = new Configuracion();

        $solicitudes = $solicitudModel->getParaReporte($filtros);
        $nombreOrg = $configModel->get('nombre_organizacion') ?: 'TANDIL';

        // Agrupar solo por sede (sin area)
        $agrupado = [];
        foreach ($solicitudes as $s) {
            $sede = $s['sede_nombre'] ?? 'Sin Sede';
            $agrupado[$sede][] = $s;
        }

        $pdf = new FPDF('L', 'mm', 'Letter');
        $pdf->SetAutoPageBreak(true, 20);
        $pdf->AddPage();

        // === ENCABEZADO CON LOGO ===
        $this->encabezadoPagina($pdf, $nombreOrg, $filtros);

        $totalGeneral = 0;
        $totalesSede = [];
        $totalesMotivo = [];
        $totalesEstado = [];

        foreach ($agrupado as $nombreSede => $registros) {
            $totalSede = count($registros);
            $consecutivo = 1; // Reiniciar numeracion por sede

            // Verificar espacio para header de sede + al menos 2 filas
            if ($pdf->GetY() > 160) {
                $pdf->AddPage();
                $this->encabezadoPagina($pdf, $nombreOrg, $filtros, true);
            }

            // Barra de sede
            $pdf->SetFillColor(...$this->colorPrimario);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->Cell(0, 8, $this->toLatin1("  SEDE: $nombreSede  |  $totalSede solicitud(es)"), 0, 1, 'L', true);
            $pdf->Ln(2);

            // Encabezado tabla
            $this->tablaEncabezado($pdf);

            foreach ($registros as $r) {
                if ($pdf->GetY() > 180) {
                    $pdf->AddPage();
                    $this->encabezadoPagina($pdf, $nombreOrg, $filtros, true);
                    // Repetir barra de sede en nueva pagina
                    $pdf->SetFillColor(...$this->colorPrimario);
                    $pdf->SetTextColor(255, 255, 255);
                    $pdf->SetFont('Helvetica', 'B', 10);
                    $pdf->Cell(0, 8, $this->toLatin1("  SEDE: $nombreSede (continuación)"), 0, 1, 'L', true);
                    $pdf->Ln(2);
                    $this->tablaEncabezado($pdf);
                }

                // Filas alternas
                if ($consecutivo % 2 === 0) {
                    $pdf->SetFillColor(...$this->colorFilaAlt);
                } else {
                    $pdf->SetFillColor(255, 255, 255);
                }
                $pdf->SetTextColor(50, 50, 50);
                $pdf->SetFont('Helvetica', '', 8);

                $nombre = Persona::getNombreCompleto($r);
                $motivo = $r['motivo_nombre'];
                if ($r['motivo_otro']) {
                    $motivo .= ': ' . $r['motivo_otro'];
                }
                $estado = $r['estado_nombre'] ?? '';

                $pdf->Cell(12, 6, $consecutivo, 'B', 0, 'C', true);
                $pdf->Cell(22, 6, date('d/m/Y', strtotime($r['fecha_solicitud'])), 'B', 0, 'C', true);
                $pdf->Cell(55, 6, $this->toLatin1($this->truncar($nombre, 32)), 'B', 0, 'L', true);
                $pdf->Cell(28, 6, $r['documento'], 'B', 0, 'C', true);
                $pdf->Cell(50, 6, $this->toLatin1($this->truncar($r['nombre_destinatario'], 28)), 'B', 0, 'L', true);
                $pdf->Cell(40, 6, $this->toLatin1($this->truncar($motivo, 24)), 'B', 0, 'L', true);
                $pdf->Cell(25, 6, $this->toLatin1($estado), 'B', 0, 'C', true);
                $pdf->Cell(0, 6, $this->toLatin1($this->truncar($r['observaciones'] ?? '-', 30)), 'B', 1, 'L', true);

                $consecutivo++;

                // Acumular totales
                $mNombre = $r['motivo_nombre'];
                $totalesMotivo[$mNombre] = ($totalesMotivo[$mNombre] ?? 0) + 1;
                $eNombre = $r['estado_nombre'] ?? 'Sin estado';
                $totalesEstado[$eNombre] = ($totalesEstado[$eNombre] ?? 0) + 1;
            }

            // Total sede
            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->SetTextColor(...$this->colorPrimario);
            $pdf->Cell(0, 7, $this->toLatin1("Total $nombreSede: $totalSede solicitud(es)"), 0, 1, 'R');
            $pdf->Ln(4);

            $totalesSede[$nombreSede] = $totalSede;
            $totalGeneral += $totalSede;
        }

        // === PAGINA DE RESUMEN ===
        $pdf->AddPage();
        $this->encabezadoPagina($pdf, $nombreOrg, $filtros, true);

        $pdf->SetFont('Helvetica', 'B', 16);
        $pdf->SetTextColor(...$this->colorPrimario);
        $pdf->Cell(0, 10, $this->toLatin1('RESUMEN GENERAL'), 0, 1, 'C');
        $pdf->Ln(2);

        // Tarjeta de total general
        $pdf->SetFillColor(...$this->colorPrimario);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 14);
        $y = $pdf->GetY();
        $pdf->Rect(10, $y, 259, 14, 'F');
        $pdf->Cell(0, 14, $this->toLatin1("TOTAL DE SOLICITUDES: $totalGeneral"), 0, 1, 'C');
        $pdf->Ln(8);

        // Tabla resumen por sede
        $this->tablaResumen($pdf, 'Solicitudes por Sede', $totalesSede, $this->colorPrimario);
        $pdf->Ln(3);

        // Tabla resumen por motivo
        $this->tablaResumen($pdf, 'Solicitudes por Motivo', $totalesMotivo, $this->colorAccento);
        $pdf->Ln(3);

        // Tabla resumen por estado
        $this->tablaResumen($pdf, 'Solicitudes por Estado', $totalesEstado, $this->colorSecundario);

        // Pie de pagina con fecha
        $pdf->Ln(10);
        $pdf->SetFont('Helvetica', 'I', 8);
        $pdf->SetTextColor(150, 150, 150);
        $pdf->Cell(0, 5, $this->toLatin1('Este documento fue generado automáticamente por el ' . APP_NAME . ' - ' . date('d/m/Y H:i')), 0, 1, 'C');

        // Guardar
        $nombre = 'reporte_ramos_' . date('Ymd_His') . '.pdf';
        $ruta = PDF_PATH . '/' . $nombre;
        $pdf->Output('F', $ruta);

        return $ruta;
    }

    private function encabezadoPagina($pdf, $nombreOrg, $filtros, $compacto = false)
    {
        $logoPath = BASE_PATH . '/public/img/logo-tandil.png';
        $hasLogo = file_exists($logoPath);

        if ($compacto) {
            // Encabezado compacto para paginas internas
            $pdf->SetFillColor(...$this->colorPrimario);
            $pdf->Rect(0, 0, 280, 18, 'F');

            if ($hasLogo) {
                $pdf->Image($logoPath, 8, 2, 14);
            }

            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetXY($hasLogo ? 24 : 10, 4);
            $pdf->Cell(0, 5, $this->toLatin1("$nombreOrg - Reporte de Solicitudes de Ramos Florales"), 0, 1, 'L');
            $pdf->SetXY($hasLogo ? 24 : 10, 10);
            $pdf->SetFont('Helvetica', '', 8);
            $fechaDesde = $filtros['fecha_desde'] ?? 'N/A';
            $fechaHasta = $filtros['fecha_hasta'] ?? 'N/A';
            $pdf->Cell(0, 5, $this->toLatin1("Período: $fechaDesde a $fechaHasta"), 0, 1, 'L');
            $pdf->Ln(8);
        } else {
            // Encabezado completo para primera pagina
            // Barra superior decorativa
            $pdf->SetFillColor(...$this->colorPrimario);
            $pdf->Rect(0, 0, 280, 3, 'F');

            $startY = 8;

            // Logo
            if ($hasLogo) {
                $pdf->Image($logoPath, 10, $startY, 30);
            }

            // Titulo principal
            $pdf->SetFont('Helvetica', 'B', 18);
            $pdf->SetTextColor(...$this->colorPrimario);
            $pdf->SetXY($hasLogo ? 45 : 10, $startY + 2);
            $pdf->Cell(0, 8, $this->toLatin1($nombreOrg), 0, 1, 'L');

            $pdf->SetXY($hasLogo ? 45 : 10, $startY + 11);
            $pdf->SetFont('Helvetica', 'B', 13);
            $pdf->SetTextColor(...$this->colorSecundario);
            $pdf->Cell(0, 7, $this->toLatin1('Reporte Consolidado de Solicitudes de Ramos Florales'), 0, 1, 'L');

            // Periodo y fecha
            $fechaDesde = $filtros['fecha_desde'] ?? 'N/A';
            $fechaHasta = $filtros['fecha_hasta'] ?? 'N/A';
            $pdf->SetXY($hasLogo ? 45 : 10, $startY + 19);
            $pdf->SetFont('Helvetica', '', 10);
            $pdf->SetTextColor(100, 100, 100);
            $pdf->Cell(0, 6, $this->toLatin1("Período: $fechaDesde  a  $fechaHasta"), 0, 1, 'L');

            // Fecha generacion a la derecha
            $pdf->SetFont('Helvetica', 'I', 8);
            $pdf->SetTextColor(150, 150, 150);
            $pdf->SetXY(200, $startY + 2);
            $pdf->Cell(69, 5, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'R');

            // Linea separadora
            $lineY = $startY + 30;
            $pdf->SetDrawColor(...$this->colorPrimario);
            $pdf->SetLineWidth(0.5);
            $pdf->Line(10, $lineY, 269, $lineY);
            $pdf->SetLineWidth(0.2);
            $pdf->SetY($lineY + 5);
        }
    }

    private function tablaEncabezado($pdf)
    {
        $pdf->SetFillColor(...$this->colorSecundario);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(12, 7, 'No.', 1, 0, 'C', true);
        $pdf->Cell(22, 7, 'Fecha', 1, 0, 'C', true);
        $pdf->Cell(55, 7, 'Solicitante', 1, 0, 'C', true);
        $pdf->Cell(28, 7, 'Documento', 1, 0, 'C', true);
        $pdf->Cell(50, 7, 'Destinatario', 1, 0, 'C', true);
        $pdf->Cell(40, 7, 'Motivo', 1, 0, 'C', true);
        $pdf->Cell(25, 7, 'Estado', 1, 0, 'C', true);
        $pdf->Cell(0, 7, 'Observaciones', 1, 1, 'C', true);
    }

    private function tablaResumen($pdf, $titulo, $datos, $colorRgb)
    {
        if (empty($datos)) return;

        $total = array_sum($datos);

        // Titulo de seccion
        $pdf->SetFillColor(...$colorRgb);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(170, 8, $this->toLatin1("  $titulo"), 0, 0, 'L', true);
        $pdf->Cell(0, 8, "Total: $total", 0, 1, 'R', true);

        // Filas
        $pdf->SetTextColor(50, 50, 50);
        $pdf->SetFont('Helvetica', '', 10);
        $fila = 0;
        foreach ($datos as $label => $valor) {
            if ($fila % 2 === 0) {
                $pdf->SetFillColor(...$this->colorFilaAlt);
            } else {
                $pdf->SetFillColor(255, 255, 255);
            }
            $porcentaje = $total > 0 ? round(($valor / $total) * 100, 1) : 0;

            $pdf->Cell(140, 7, $this->toLatin1("  $label"), 'B', 0, 'L', true);
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->Cell(30, 7, $valor, 'B', 0, 'C', true);
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetTextColor(130, 130, 130);
            $pdf->Cell(0, 7, "($porcentaje%)", 'B', 1, 'C', true);
            $pdf->SetTextColor(50, 50, 50);
            $pdf->SetFont('Helvetica', '', 10);
            $fila++;
        }
    }

    private function truncar($texto, $max)
    {
        $texto = $texto ?? '';
        return (mb_strlen($texto) > $max) ? mb_substr($texto, 0, $max - 3) . '...' : $texto;
    }

    private function toLatin1($str)
    {
        return mb_convert_encoding($str, 'ISO-8859-1', 'UTF-8');
    }
}
