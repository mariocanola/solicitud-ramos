<?php
require_once BASE_PATH . '/app/models/Solicitud.php';
require_once BASE_PATH . '/app/models/Persona.php';
require_once BASE_PATH . '/app/models/Configuracion.php';

class PdfService
{
    public function generarConsolidado($filtros = [])
    {
        // Lazy-load FPDF only when actually generating a PDF
        require_once BASE_PATH . '/vendor/fpdf/fpdf.php';
        $solicitudModel = new Solicitud();
        $configModel = new Configuracion();

        $solicitudes = $solicitudModel->getParaReporte($filtros);
        $nombreOrg = $configModel->get('nombre_organizacion') ?: 'Organización';

        // Group: sede -> area -> records
        $agrupado = [];
        foreach ($solicitudes as $s) {
            $sede = $s['sede_nombre'] ?? 'Sin Sede';
            $area = $s['area_nombre'] ?? 'Sin Área';
            $agrupado[$sede][$area][] = $s;
        }

        $pdf = new FPDF('L', 'mm', 'Letter');
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // Header
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->SetTextColor(44, 62, 80);
        $pdf->Cell(0, 10, utf8_decode($nombreOrg), 0, 1, 'C');
        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode('Reporte Consolidado de Solicitudes de Ramos Florales'), 0, 1, 'C');
        $pdf->SetFont('Helvetica', '', 10);
        $fechaDesde = $filtros['fecha_desde'] ?? 'N/A';
        $fechaHasta = $filtros['fecha_hasta'] ?? 'N/A';
        $pdf->Cell(0, 6, utf8_decode("Período: $fechaDesde a $fechaHasta"), 0, 1, 'C');
        $pdf->SetFont('Helvetica', 'I', 8);
        $pdf->SetTextColor(128, 128, 128);
        $pdf->Cell(0, 5, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'C');
        $pdf->Ln(3);
        $pdf->SetDrawColor(44, 62, 80);
        $pdf->Line(10, $pdf->GetY(), 269, $pdf->GetY());
        $pdf->Ln(5);

        $totalGeneral = 0;
        $totalesSede = [];
        $totalesMotivo = [];
        $numeroConsecutivo = 1; // Contador global para numeración consecutiva

        foreach ($agrupado as $nombreSede => $areas) {
            $totalSede = 0;

            // Sede header
            $pdf->SetFillColor(44, 62, 80);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->SetFont('Helvetica', 'B', 11);
            $pdf->Cell(0, 8, utf8_decode("  SEDE: $nombreSede"), 0, 1, 'L', true);
            $pdf->Ln(2);

            foreach ($areas as $nombreArea => $registros) {
                $totalArea = count($registros);
                $totalSede += $totalArea;

                // Area sub-header
                $pdf->SetFillColor(52, 152, 219);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->SetFont('Helvetica', 'B', 10);
                $pdf->Cell(0, 7, utf8_decode("    Área: $nombreArea"), 0, 1, 'L', true);
                $pdf->Ln(1);

                // Table header
                $this->tablaEncabezado($pdf);

                foreach ($registros as $r) {
                    if ($pdf->GetY() > 185) {
                        $pdf->AddPage();
                        $this->tablaEncabezado($pdf);
                    }

                    $bgColor = ($numeroConsecutivo % 2 === 0) ? 242 : 255;
                    $pdf->SetFillColor($bgColor, $bgColor, $bgColor);
                    $pdf->SetTextColor(0, 0, 0);
                    $pdf->SetFont('Helvetica', '', 8);

                    $nombre = Persona::getNombreCompleto($r);
                    $motivo = $r['motivo_nombre'];
                    if ($r['motivo_otro']) {
                        $motivo .= ': ' . $r['motivo_otro'];
                    }

                    $pdf->Cell(10, 6, $numeroConsecutivo, 1, 0, 'C', true);
                    $pdf->Cell(22, 6, $r['fecha_solicitud'], 1, 0, 'C', true);
                    $pdf->Cell(50, 6, utf8_decode($this->truncar($nombre, 30)), 1, 0, 'L', true);
                    $pdf->Cell(28, 6, $r['documento'], 1, 0, 'C', true);
                    $pdf->Cell(50, 6, utf8_decode($this->truncar($r['nombre_destinatario'], 30)), 1, 0, 'L', true);
                    $pdf->Cell(50, 6, utf8_decode($this->truncar($motivo, 30)), 1, 0, 'L', true);
                    $pdf->Cell(0, 6, utf8_decode($this->truncar($r['observaciones'] ?? '-', 40)), 1, 1, 'L', true);

                    $numeroConsecutivo++; // Incrementar contador global

                    // Accumulate motivo totals
                    $mNombre = $r['motivo_nombre'];
                    $totalesMotivo[$mNombre] = ($totalesMotivo[$mNombre] ?? 0) + 1;
                }

                // Area subtotal
                $pdf->SetFont('Helvetica', 'B', 9);
                $pdf->SetTextColor(52, 152, 219);
                $pdf->Cell(0, 6, utf8_decode("Subtotal $nombreArea: $totalArea solicitud(es)"), 0, 1, 'R');
                $pdf->Ln(2);

            }

            // Sede total
            $pdf->SetFont('Helvetica', 'B', 10);
            $pdf->SetTextColor(44, 62, 80);
            $pdf->Cell(0, 7, utf8_decode("TOTAL $nombreSede: $totalSede solicitud(es)"), 0, 1, 'R');
            $pdf->Ln(4);

            $totalesSede[$nombreSede] = $totalSede;
            $totalGeneral += $totalSede;
        }

        // Summary page
        $pdf->AddPage();
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->SetTextColor(44, 62, 80);
        $pdf->Cell(0, 10, utf8_decode('RESUMEN GENERAL'), 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('Helvetica', 'B', 12);
        $pdf->Cell(0, 8, utf8_decode("Total de solicitudes: $totalGeneral"), 0, 1, 'L');
        $pdf->Ln(5);

        $this->tablaResumen($pdf, 'Por Sede', $totalesSede, [44, 62, 80]);
        $this->tablaResumen($pdf, 'Por Motivo', $totalesMotivo, [39, 174, 96]);

        // Save
        $nombre = 'reporte_ramos_' . date('Ymd_His') . '.pdf';
        $ruta = PDF_PATH . '/' . $nombre;
        $pdf->Output('F', $ruta);

        return $ruta;
    }

    private function tablaEncabezado($pdf)
    {
        $pdf->SetFillColor(44, 62, 80);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->Cell(10, 6, 'No.', 1, 0, 'C', true);
        $pdf->Cell(22, 6, 'Fecha', 1, 0, 'C', true);
        $pdf->Cell(50, 6, 'Solicitante', 1, 0, 'C', true);
        $pdf->Cell(28, 6, 'Documento', 1, 0, 'C', true);
        $pdf->Cell(50, 6, 'Destinatario', 1, 0, 'C', true);
        $pdf->Cell(50, 6, 'Motivo', 1, 0, 'C', true);
        $pdf->Cell(0, 6, 'Observaciones', 1, 1, 'C', true);
    }

    private function tablaResumen($pdf, $titulo, $datos, $colorRgb)
    {
        $pdf->SetFillColor($colorRgb[0], $colorRgb[1], $colorRgb[2]);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->Cell(130, 7, $titulo, 1, 0, 'L', true);
        $pdf->Cell(40, 7, 'Total', 1, 1, 'C', true);

        $pdf->SetTextColor(0, 0, 0);
        $pdf->SetFont('Helvetica', '', 10);
        $fila = 0;
        foreach ($datos as $label => $total) {
            $bg = ($fila % 2 === 0) ? 255 : 242;
            $pdf->SetFillColor($bg, $bg, $bg);
            $pdf->Cell(130, 6, utf8_decode($label), 1, 0, 'L', true);
            $pdf->Cell(40, 6, $total, 1, 1, 'C', true);
            $fila++;
        }
        $pdf->Ln(5);
    }

    private function truncar($texto, $max)
    {
        $texto = $texto ?? '';
        return (mb_strlen($texto) > $max) ? mb_substr($texto, 0, $max - 3) . '...' : $texto;
    }
}
