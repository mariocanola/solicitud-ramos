<?php
require_once BASE_PATH . '/app/models/Solicitud.php';
require_once BASE_PATH . '/app/models/Persona.php';
require_once BASE_PATH . '/app/models/Configuracion.php';

/**
 * PdfService — Reporte consolidado de solicitudes de ramos.
 * Diseño empresarial: un solo acento morado, mucho espacio en blanco,
 * tipografía clara, texto envuelto sin truncar a media palabra.
 */
class PdfService
{
    // Paleta sobria — un solo acento, el resto neutros.
    private $accent     = [74, 25, 66];     // Morado corporativo (acento único)
    private $accentSoft = [232, 222, 230];  // Tinte suave para encabezados de sección
    private $text       = [40, 40, 45];     // Texto principal
    private $textSoft   = [110, 110, 118];  // Texto auxiliar / labels
    private $border     = [220, 220, 224];  // Líneas y bordes
    private $rowAlt     = [248, 248, 250];  // Fila alterna casi blanca
    private $kpiBg      = [249, 247, 249];  // Fondo de tarjeta KPI

    // Geometría de página (Letter landscape)
    private $marginX    = 12;
    private $pageWidth  = 279.4;
    private $usableW;

    public function __construct()
    {
        $this->usableW = $this->pageWidth - 2 * $this->marginX;
    }

    public function generarConsolidado($filtros = [])
    {
        require_once BASE_PATH . '/vendor/fpdf/fpdf.php';

        $solicitudModel = new Solicitud();
        $configModel    = new Configuracion();
        $solicitudes    = $solicitudModel->getParaReporte($filtros);
        $nombreOrg      = $configModel->get('nombre_organizacion') ?: 'TANDIL';

        // Agrupar por sede (orden alfabético)
        $agrupado = [];
        foreach ($solicitudes as $s) {
            $sede = $s['sede_nombre'] ?? 'Sin Sede';
            $agrupado[$sede][] = $s;
        }
        ksort($agrupado);

        $pdf = new FPDF('L', 'mm', 'Letter');
        $pdf->SetMargins($this->marginX, 12, $this->marginX);
        $pdf->SetAutoPageBreak(true, 18);
        $pdf->AddPage();
        $this->headerPagina($pdf, $nombreOrg, $filtros);

        // Acumuladores para el resumen
        $totalGeneral   = 0;
        $totalesSede    = [];
        $totalesMotivo  = [];

        foreach ($agrupado as $nombreSede => $registros) {
            $totalSede = count($registros);

            // Salto de página si no caben encabezado de sede + 2 filas
            if ($pdf->GetY() > 170) {
                $pdf->AddPage();
                $this->headerPagina($pdf, $nombreOrg, $filtros, true);
            }

            $this->headerSede($pdf, $nombreSede, $totalSede);
            $this->tablaHeader($pdf);

            $consecutivo = 1;
            foreach ($registros as $r) {
                // Pre-cálculo: ¿cabe la fila en la página actual?
                $tempH = $this->calcularAlturaFila($pdf, $r, $consecutivo);
                if ($pdf->GetY() + $tempH > 195) {
                    $pdf->AddPage();
                    $this->headerPagina($pdf, $nombreOrg, $filtros, true);
                    $this->headerSede($pdf, $nombreSede . ' (continuación)', $totalSede);
                    $this->tablaHeader($pdf);
                }

                $this->renderFila($pdf, $r, $consecutivo);
                $consecutivo++;

                $mNombre = $r['motivo_nombre'] ?? 'Sin motivo';
                $totalesMotivo[$mNombre] = ($totalesMotivo[$mNombre] ?? 0) + 1;
            }

            $this->footerSede($pdf, $nombreSede, $totalSede);
            $totalesSede[$nombreSede] = $totalSede;
            $totalGeneral += $totalSede;
            $pdf->Ln(2);
        }

        // === PÁGINA DE RESUMEN ===
        $pdf->AddPage();
        $this->headerPagina($pdf, $nombreOrg, $filtros, true);
        $this->paginaResumen($pdf, $totalGeneral, $totalesSede, $totalesMotivo);

        // Guardar
        $nombre = 'reporte_ramos_' . date('Ymd_His') . '.pdf';
        $ruta = PDF_PATH . '/' . $nombre;
        $pdf->Output('F', $ruta);
        return $ruta;
    }

    // ────────────────────────────────────────────────────────────
    // ENCABEZADO DE PÁGINA
    // ────────────────────────────────────────────────────────────
    private function headerPagina($pdf, $nombreOrg, $filtros, $compacto = false)
    {
        $logoPath = BASE_PATH . '/public/img/logo-tandil.png';
        $hasLogo  = file_exists($logoPath);
        $fechaDesde = $filtros['fecha_desde'] ?? '—';
        $fechaHasta = $filtros['fecha_hasta'] ?? '—';

        if ($hasLogo) {
            $pdf->Image($logoPath, $this->marginX, 10, 16);
        }

        // Lado izquierdo: organización + título
        $textX = $hasLogo ? ($this->marginX + 20) : $this->marginX;
        $pdf->SetXY($textX, 11);
        $pdf->SetFont('Helvetica', 'B', 13);
        $pdf->SetTextColor(...$this->text);
        $pdf->Cell(150, 5, $this->toLatin1($nombreOrg), 0, 1, 'L');

        $pdf->SetXY($textX, 17);
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...$this->textSoft);
        $titulo = $compacto
            ? 'Reporte de Solicitudes de Ramos Florales (continuación)'
            : 'Reporte Consolidado de Solicitudes de Ramos Florales';
        $pdf->Cell(150, 5, $this->toLatin1($titulo), 0, 1, 'L');

        // Lado derecho: período + fecha de generación
        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(...$this->textSoft);
        $rightX = $this->pageWidth - $this->marginX - 80;
        $pdf->SetXY($rightX, 11);
        $pdf->Cell(80, 5, $this->toLatin1("Período: $fechaDesde — $fechaHasta"), 0, 1, 'R');
        $pdf->SetXY($rightX, 17);
        $pdf->Cell(80, 5, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'R');

        // Línea fina divisoria
        $pdf->SetDrawColor(...$this->accent);
        $pdf->SetLineWidth(0.4);
        $pdf->Line($this->marginX, 27, $this->pageWidth - $this->marginX, 27);
        $pdf->SetLineWidth(0.2);
        $pdf->SetDrawColor(...$this->border);

        $pdf->SetY(32);
    }

    // ────────────────────────────────────────────────────────────
    // ENCABEZADO DE SECCIÓN (sede)
    // ────────────────────────────────────────────────────────────
    private function headerSede($pdf, $nombreSede, $total)
    {
        $y = $pdf->GetY();
        // Fondo suave, no saturado
        $pdf->SetFillColor(...$this->accentSoft);
        $pdf->Rect($this->marginX, $y, $this->usableW, 8, 'F');
        // Barra acento delgada a la izquierda
        $pdf->SetFillColor(...$this->accent);
        $pdf->Rect($this->marginX, $y, 1.5, 8, 'F');

        $pdf->SetTextColor(...$this->accent);
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetXY($this->marginX + 4, $y + 1.5);
        $pdf->Cell($this->usableW - 8, 5, $this->toLatin1('Sede ' . $nombreSede), 0, 0, 'L');

        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...$this->textSoft);
        $pdf->SetXY($this->marginX, $y + 1.5);
        $pdf->Cell($this->usableW - 4, 5, $this->toLatin1($total . ' solicitud' . ($total === 1 ? '' : 'es')), 0, 1, 'R');

        $pdf->SetY($y + 8);
        $pdf->Ln(1);
    }

    private function footerSede($pdf, $nombreSede, $total)
    {
        $pdf->SetFont('Helvetica', 'B', 9);
        $pdf->SetTextColor(...$this->accent);
        $pdf->Cell($this->usableW, 6, $this->toLatin1("Subtotal $nombreSede: $total"), 0, 1, 'R');
    }

    // ────────────────────────────────────────────────────────────
    // TABLA — encabezados y filas con wrap
    // ────────────────────────────────────────────────────────────
    private function colWidths()
    {
        // Suman ≈ usableW (255 mm en Letter horizontal con márgenes de 12 mm)
        return [
            'no'      => 12,
            'fecha'   => 26,
            'solic'   => 80,
            'doc'     => 32,
            'motivo'  => 55,
            'obs'     => 50,  // ocupa el resto vía auto-extend
        ];
    }

    private function tablaHeader($pdf)
    {
        $w = $this->colWidths();
        $w['obs'] = $this->usableW - ($w['no'] + $w['fecha'] + $w['solic'] + $w['doc'] + $w['motivo']);

        $pdf->SetFont('Helvetica', 'B', 8);
        $pdf->SetFillColor(245, 243, 245);
        $pdf->SetTextColor(...$this->textSoft);
        $pdf->SetDrawColor(...$this->border);

        $h = 7;
        $pdf->Cell($w['no'],     $h, '#',             'B', 0, 'C', true);
        $pdf->Cell($w['fecha'],  $h, 'Fecha',         'B', 0, 'C', true);
        $pdf->Cell($w['solic'],  $h, 'Solicitante',   'B', 0, 'L', true);
        $pdf->Cell($w['doc'],    $h, 'Documento',     'B', 0, 'C', true);
        $pdf->Cell($w['motivo'], $h, 'Motivo',        'B', 0, 'L', true);
        $pdf->Cell($w['obs'],    $h, 'Observaciones', 'B', 1, 'L', true);
    }

    /**
     * Calcula la altura necesaria para una fila considerando wrap en columnas largas.
     */
    private function calcularAlturaFila($pdf, $r, $n)
    {
        $w = $this->colWidths();
        $w['obs'] = $this->usableW - ($w['no'] + $w['fecha'] + $w['solic'] + $w['doc'] + $w['motivo']);

        $pdf->SetFont('Helvetica', '', 8);
        $solic  = $this->nbLines($pdf, $w['solic']  - 2, Persona::getNombreCompleto($r));
        $motivo = $this->nbLines($pdf, $w['motivo'] - 2, $this->motivoCompleto($r));
        $obs    = $this->nbLines($pdf, $w['obs']    - 2, $r['observaciones'] ?? '');
        $maxL   = max(1, $solic, $motivo, $obs);
        return $maxL * 4.6 + 1.4; // alto por línea + padding
    }

    private function renderFila($pdf, $r, $n)
    {
        $w = $this->colWidths();
        $w['obs'] = $this->usableW - ($w['no'] + $w['fecha'] + $w['solic'] + $w['doc'] + $w['motivo']);

        $pdf->SetFont('Helvetica', '', 8);
        $pdf->SetTextColor(...$this->text);
        $pdf->SetDrawColor(...$this->border);

        // Datos formateados
        $solic  = Persona::getNombreCompleto($r);
        $fecha  = date('d/m/Y', strtotime($r['fecha_solicitud']));
        $doc    = $r['documento'] ?? '';
        $motivo = $this->motivoCompleto($r);
        $obs    = ($r['observaciones'] ?? '') ?: '—';

        // Alto de la fila (basado en columnas con wrap)
        $solicL  = $this->nbLines($pdf, $w['solic']  - 2, $solic);
        $motivoL = $this->nbLines($pdf, $w['motivo'] - 2, $motivo);
        $obsL    = $this->nbLines($pdf, $w['obs']    - 2, $obs);
        $maxL    = max(1, $solicL, $motivoL, $obsL);
        $rowH    = $maxL * 4.6 + 1.4;

        // Fondo alterno
        $bg = ($n % 2 === 0) ? $this->rowAlt : [255, 255, 255];
        $pdf->SetFillColor(...$bg);

        $x0 = $pdf->GetX();
        $y0 = $pdf->GetY();
        $x  = $x0;

        // Pintar fondo completo de la fila primero
        $pdf->Rect($x0, $y0, $this->usableW, $rowH, 'F');

        // No.
        $pdf->SetXY($x, $y0);
        $pdf->Cell($w['no'], $rowH, $n, 0, 0, 'C');
        $x += $w['no'];

        // Fecha
        $pdf->SetXY($x, $y0);
        $pdf->Cell($w['fecha'], $rowH, $fecha, 0, 0, 'C');
        $x += $w['fecha'];

        // Solicitante (wrap)
        $pdf->SetXY($x + 1, $y0 + 0.7);
        $pdf->MultiCell($w['solic'] - 2, 4.6, $this->toLatin1($solic), 0, 'L');
        $x += $w['solic'];

        // Documento
        $pdf->SetXY($x, $y0);
        $pdf->Cell($w['doc'], $rowH, $doc, 0, 0, 'C');
        $x += $w['doc'];

        // Motivo (wrap)
        $pdf->SetXY($x + 1, $y0 + 0.7);
        $pdf->MultiCell($w['motivo'] - 2, 4.6, $this->toLatin1($motivo), 0, 'L');
        $x += $w['motivo'];

        // Observaciones (wrap)
        $pdf->SetXY($x + 1, $y0 + 0.7);
        $pdf->SetTextColor(...$this->text);
        $pdf->MultiCell($w['obs'] - 2, 4.6, $this->toLatin1($obs), 0, 'L');

        // Línea inferior delgada
        $pdf->SetDrawColor(...$this->border);
        $pdf->Line($x0, $y0 + $rowH, $x0 + $this->usableW, $y0 + $rowH);

        $pdf->SetXY($x0, $y0 + $rowH);
    }

    private function motivoCompleto($r)
    {
        $m = $r['motivo_nombre'] ?? '';
        if (!empty($r['motivo_otro'])) $m .= ': ' . $r['motivo_otro'];
        return $m;
    }

    /**
     * Cuenta cuántas líneas ocuparía un texto con MultiCell de ancho $w.
     * Adaptado del ejemplo oficial de FPDF.
     */
    private function nbLines($pdf, $w, $txt)
    {
        $txt = $this->toLatin1((string)$txt);
        if ($txt === '') return 1;

        $cw = $pdf->GetStringWidth(' ');
        if ($w === 0) $w = $pdf->w - $pdf->rMargin - $pdf->x;
        $wmax = ($w - 2);

        $s = str_replace("\r", '', $txt);
        $nb = strlen($s);
        if ($nb > 0 && $s[$nb - 1] === "\n") $nb--;

        $sep = -1; $i = 0; $j = 0; $l = 0; $nl = 1;
        while ($i < $nb) {
            $c = $s[$i];
            if ($c === "\n") { $i++; $sep = -1; $j = $i; $l = 0; $nl++; continue; }
            if ($c === ' ') $sep = $i;
            $l += $pdf->GetStringWidth($c);
            if ($l > $wmax) {
                if ($sep === -1) {
                    if ($i === $j) $i++;
                } else {
                    $i = $sep + 1;
                }
                $sep = -1; $j = $i; $l = 0; $nl++;
            } else {
                $i++;
            }
        }
        return $nl;
    }

    // ────────────────────────────────────────────────────────────
    // PÁGINA DE RESUMEN
    // ────────────────────────────────────────────────────────────
    private function paginaResumen($pdf, $totalGeneral, $tSede, $tMotivo)
    {
        // Título de la sección
        $pdf->SetFont('Helvetica', 'B', 14);
        $pdf->SetTextColor(...$this->text);
        $pdf->Cell(0, 8, $this->toLatin1('Resumen general'), 0, 1, 'L');
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...$this->textSoft);
        $pdf->Cell(0, 5, $this->toLatin1('Indicadores principales del período seleccionado'), 0, 1, 'L');
        $pdf->Ln(4);

        // Fila de KPIs (3 tarjetas)
        $kpis = [
            ['Total de solicitudes', $totalGeneral],
            ['Sedes con actividad',  count($tSede)],
            ['Motivos distintos',    count($tMotivo)],
        ];
        $this->kpis($pdf, $kpis);

        // Pie discreto
        $pdf->Ln(6);
        $pdf->SetFont('Helvetica', 'I', 7);
        $pdf->SetTextColor(160, 160, 165);
        $pdf->Cell(0, 4, $this->toLatin1('Documento generado automáticamente por ' . APP_NAME . ' — ' . date('d/m/Y H:i')), 0, 1, 'C');
    }

    private function kpis($pdf, $items)
    {
        $count = count($items);
        $gap   = 4;
        $cardW = ($this->usableW - $gap * ($count - 1)) / $count;
        $cardH = 22;
        $x = $this->marginX;
        $y = $pdf->GetY();

        foreach ($items as $kpi) {
            list($label, $value) = $kpi;

            // Fondo
            $pdf->SetFillColor(...$this->kpiBg);
            $pdf->Rect($x, $y, $cardW, $cardH, 'F');
            // Borde delgado izquierdo (acento)
            $pdf->SetFillColor(...$this->accent);
            $pdf->Rect($x, $y, 1.2, $cardH, 'F');

            // Valor (grande)
            $pdf->SetFont('Helvetica', 'B', 18);
            $pdf->SetTextColor(...$this->text);
            $pdf->SetXY($x + 4, $y + 3);
            $pdf->Cell($cardW - 6, 9, (string)$value, 0, 1, 'L');

            // Label (pequeño, mayúsculas)
            $pdf->SetFont('Helvetica', '', 7);
            $pdf->SetTextColor(...$this->textSoft);
            $pdf->SetXY($x + 4, $y + 13);
            $pdf->Cell($cardW - 6, 5, $this->toLatin1(strtoupper($label)), 0, 1, 'L');

            $x += $cardW + $gap;
        }
        $pdf->SetY($y + $cardH);
    }

    private function tablaResumen($pdf, $titulo, $datos, $totalGeneral)
    {
        if (empty($datos)) return;
        $total = array_sum($datos);

        // Título de la sub-sección (sin barra llena)
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetTextColor(...$this->text);
        $pdf->Cell($this->usableW - 30, 6, $this->toLatin1($titulo), 0, 0, 'L');
        $pdf->SetFont('Helvetica', '', 9);
        $pdf->SetTextColor(...$this->textSoft);
        $pdf->Cell(30, 6, $this->toLatin1('Total: ' . $total), 0, 1, 'R');

        // Línea fina morada
        $y = $pdf->GetY();
        $pdf->SetDrawColor(...$this->accent);
        $pdf->SetLineWidth(0.3);
        $pdf->Line($this->marginX, $y, $this->pageWidth - $this->marginX, $y);
        $pdf->SetDrawColor(...$this->border);
        $pdf->SetLineWidth(0.2);
        $pdf->Ln(1.5);

        // Cabecera de columnas
        $w1 = $this->usableW * 0.55;
        $w2 = $this->usableW * 0.10;
        $w3 = $this->usableW * 0.15;
        $w4 = $this->usableW - $w1 - $w2 - $w3;

        $pdf->SetFont('Helvetica', 'B', 7);
        $pdf->SetTextColor(...$this->textSoft);
        $pdf->Cell($w1, 5, $this->toLatin1(strtoupper('Categoría')),     0, 0, 'L');
        $pdf->Cell($w2, 5, $this->toLatin1(strtoupper('Cant.')),         0, 0, 'C');
        $pdf->Cell($w3, 5, $this->toLatin1(strtoupper('% del total')),   0, 0, 'C');
        $pdf->Cell($w4, 5, '',                                            0, 1, 'L');

        // Filas
        arsort($datos);
        $i = 0;
        foreach ($datos as $label => $valor) {
            $bg = ($i % 2 === 0) ? [255, 255, 255] : $this->rowAlt;
            $pdf->SetFillColor(...$bg);
            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetTextColor(...$this->text);

            $pct = $totalGeneral > 0 ? ($valor / $totalGeneral) * 100 : 0;
            $pctStr = number_format($pct, 1) . '%';

            $y0 = $pdf->GetY();
            $h  = 6;

            $pdf->Rect($this->marginX, $y0, $this->usableW, $h, 'F');

            $pdf->SetXY($this->marginX, $y0);
            $pdf->Cell($w1, $h, $this->toLatin1((string)$label), 0, 0, 'L');

            $pdf->SetFont('Helvetica', 'B', 9);
            $pdf->Cell($w2, $h, (string)$valor, 0, 0, 'C');

            $pdf->SetFont('Helvetica', '', 9);
            $pdf->SetTextColor(...$this->textSoft);
            $pdf->Cell($w3, $h, $pctStr, 0, 0, 'C');

            // Mini barra horizontal de progreso (acento, estilo dataviz)
            $barX = $this->marginX + $w1 + $w2 + $w3 + 2;
            $barW = $w4 - 4;
            $barFill = ($pct / 100) * $barW;
            $pdf->SetFillColor(232, 226, 230);
            $pdf->Rect($barX, $y0 + 2, $barW, 2, 'F');
            $pdf->SetFillColor(...$this->accent);
            $pdf->Rect($barX, $y0 + 2, max(0.5, $barFill), 2, 'F');

            $pdf->SetXY($this->marginX, $y0 + $h);
            $pdf->SetTextColor(...$this->text);
            $i++;
        }
    }

    private function toLatin1($str)
    {
        return mb_convert_encoding((string)$str, 'ISO-8859-1', 'UTF-8');
    }
}
