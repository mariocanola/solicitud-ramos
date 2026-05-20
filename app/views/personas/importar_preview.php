<?php
/** @var array $clasificacion */
/** @var string $archivo */
$r = $clasificacion['resumen'];
$puedeImportar = ($r['nuevas'] + $r['a_actualizar'] + $r['a_reactivar'] + $r['a_desactivar']) > 0;
$formatoDetectado = $clasificacion['formato'] ?? 'desconocido';
$labelFormato = $formatoDetectado === 'CREOS'
    ? 'Maestro CREOS (personal temporal)'
    : 'Maestro Tandil (personal directo)';
?>

<style>
.kpi-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(160px,1fr)); gap:12px; margin-bottom:20px; }
.kpi { background:#fff; border:1px solid #e2e8f0; border-radius:8px; padding:14px 16px; border-top:3px solid #94a3b8; }
.kpi.k-total   { border-top-color:#475569; }
.kpi.k-new     { border-top-color:#16a34a; }
.kpi.k-upd     { border-top-color:#0284c7; }
.kpi.k-react   { border-top-color:#7c3aed; }
.kpi.k-skip    { border-top-color:#94a3b8; }
.kpi.k-err     { border-top-color:#dc2626; }
.kpi.k-baja    { border-top-color:#ea580c; }
.kpi-num { font-size:24px; font-weight:700; color:#1e293b; }
.kpi-lbl { font-size:11px; text-transform:uppercase; letter-spacing:0.4px; color:#64748b; font-weight:600; }
.tab-bar { display:flex; gap:4px; border-bottom:1px solid #e2e8f0; margin-bottom:14px; flex-wrap:wrap; }
.tab-btn { background:transparent; border:none; padding:10px 16px; cursor:pointer; font-weight:600; font-size:13px; color:#64748b; border-bottom:2px solid transparent; }
.tab-btn.active { color:#4A1942; border-bottom-color:#4A1942; }
.tab-pane { display:none; }
.tab-pane.active { display:block; }
.preview-table { width:100%; font-size:13px; border-collapse:collapse; }
.preview-table th { background:#f8fafc; padding:8px 10px; text-align:left; font-size:11px; text-transform:uppercase; letter-spacing:0.3px; color:#475569; border-bottom:1px solid #e2e8f0; }
.preview-table td { padding:8px 10px; border-bottom:1px solid #f1f5f9; }
.preview-table tr:hover td { background:#fafbfc; }
.row-new   { border-left:3px solid #16a34a; }
.row-upd   { border-left:3px solid #0284c7; }
.row-react { border-left:3px solid #7c3aed; }
.row-skip  { border-left:3px solid #cbd5e1; }
.row-err   { border-left:3px solid #dc2626; background:#fef2f2; }
.cambio { font-size:11px; color:#0284c7; }
.cambio del { color:#94a3b8; }
.empty { text-align:center; color:#94a3b8; padding:30px; font-size:13px; }
</style>

<div class="card">
    <div class="card-header">
        <span>Previsualizacion: <?= htmlspecialchars($archivo) ?></span>
        <a href="<?= BASE_URL ?>/personas/importar/cancelar" class="btn btn-outline btn-sm">Cancelar</a>
    </div>
    <div class="card-body">

        <div style="margin-bottom:16px;padding:10px 14px;background:#f0f9ff;border-left:3px solid #0284c7;border-radius:6px;font-size:13px;color:#0c4a6e">
            <strong>Formato detectado:</strong> <?= htmlspecialchars($labelFormato) ?>
            &middot; <strong><?= number_format($r['total']) ?></strong> filas leidas
        </div>

        <div class="kpi-grid">
            <div class="kpi k-total"><div class="kpi-num"><?= $r['total'] ?></div><div class="kpi-lbl">Total</div></div>
            <div class="kpi k-new"><div class="kpi-num"><?= $r['nuevas'] ?></div><div class="kpi-lbl">Nuevas</div></div>
            <div class="kpi k-upd"><div class="kpi-num"><?= $r['a_actualizar'] ?></div><div class="kpi-lbl">Actualizar</div></div>
            <div class="kpi k-react"><div class="kpi-num"><?= $r['a_reactivar'] ?></div><div class="kpi-lbl">Reactivar</div></div>
            <div class="kpi k-skip"><div class="kpi-num"><?= $r['sin_cambios'] ?></div><div class="kpi-lbl">Sin cambios</div></div>
            <div class="kpi k-err"><div class="kpi-num"><?= $r['errores'] ?></div><div class="kpi-lbl">Errores</div></div>
            <div class="kpi k-baja"><div class="kpi-num"><?= $r['a_desactivar'] ?></div><div class="kpi-lbl">Dar de baja</div></div>
        </div>

        <?php if ($r['a_desactivar'] > 0): ?>
        <div style="margin-bottom:16px;padding:12px 14px;background:#fff7ed;border-left:3px solid #ea580c;border-radius:6px;font-size:13px;color:#7c2d12">
            ⚠ <strong><?= $r['a_desactivar'] ?> persona<?= $r['a_desactivar'] > 1 ? 's' : '' ?> ser<?= $r['a_desactivar'] > 1 ? 'án' : 'á' ?> dada<?= $r['a_desactivar'] > 1 ? 's' : '' ?> de baja.</strong>
        </div>
        <?php endif; ?>

        <div class="tab-bar">
            <button type="button" class="tab-btn active" data-tab="nuevas">Nuevas (<?= $r['nuevas'] ?>)</button>
            <button type="button" class="tab-btn" data-tab="actualizar">Actualizar (<?= $r['a_actualizar'] ?>)</button>
            <button type="button" class="tab-btn" data-tab="reactivar">Reactivar (<?= $r['a_reactivar'] ?>)</button>
            <button type="button" class="tab-btn" data-tab="skip">Sin cambios (<?= $r['sin_cambios'] ?>)</button>
            <button type="button" class="tab-btn" data-tab="errores">Errores (<?= $r['errores'] ?>)</button>
            <?php if ($r['a_desactivar'] > 0): ?>
            <button type="button" class="tab-btn" data-tab="baja" style="color:#ea580c">Dar de baja (<?= $r['a_desactivar'] ?>)</button>
            <?php endif; ?>
        </div>

        <?php
        function renderTabla(array $filas, string $clase, string $tipo, int $maxFilas = 100) {
            if (empty($filas)) {
                echo '<div class="empty">Sin filas en esta categoria.</div>';
                return;
            }
            echo '<div style="overflow-x:auto"><table class="preview-table"><thead><tr>';
            $cols = ['Linea','Doc','Tipo','Nombre','Apellido','Sede','Empresa','Tel'];
            if ($tipo === 'errores') $cols[] = 'Error';
            if (in_array($tipo, ['actualizar','reactivar'])) $cols[] = 'Cambios';
            foreach ($cols as $c) echo '<th>'.htmlspecialchars($c).'</th>';
            echo '</tr></thead><tbody>';
            $count = 0;
            foreach ($filas as $f) {
                if ($count++ >= $maxFilas) break;
                echo '<tr class="'.$clase.'">';
                echo '<td>'.htmlspecialchars($f['_linea_excel'] ?? '-').'</td>';
                echo '<td>'.htmlspecialchars($f['documento'] ?? '').'</td>';
                echo '<td>'.htmlspecialchars($f['tipo_documento'] ?? '').'</td>';
                $nombreCell = htmlspecialchars(trim(($f['primer_nombre'] ?? '').' '.($f['segundo_nombre'] ?? '')));
                $apellidoCell = htmlspecialchars(trim(($f['primer_apellido'] ?? '').' '.($f['segundo_apellido'] ?? '')));
                if (!empty($f['_nombre_parseado'])) {
                    $badge = !empty($f['_nombre_ambiguo'])
                        ? ' <span title="Revise: nombre con 5+ palabras" style="background:#fef3c7;color:#92400e;font-size:9px;padding:1px 5px;border-radius:3px;font-weight:600">REVISAR</span>'
                        : ' <span title="Parseado automaticamente desde columna nombre" style="background:#e0f2fe;color:#075985;font-size:9px;padding:1px 5px;border-radius:3px;font-weight:600">AUTO</span>';
                    $nombreCell .= $badge;
                }
                echo '<td>'.$nombreCell.'</td>';
                echo '<td>'.$apellidoCell.'</td>';
                echo '<td>'.htmlspecialchars($f['_sede_nombre'] ?? $f['sede'] ?? '').'</td>';
                $emp = $f['empresa'] ?? 'TANDIL';
                echo '<td>'.($emp === 'CREOS'
                    ? '<span style="background:#e0f2fe;color:#0369a1;padding:1px 6px;border-radius:3px;font-size:10px;font-weight:700">CREOS</span>'
                    : '<span style="color:#64748b;font-size:11px">Tandil</span>').'</td>';
                echo '<td>'.htmlspecialchars($f['telefono'] ?? '').'</td>';
                if ($tipo === 'errores') {
                    echo '<td style="color:#b91c1c">'.htmlspecialchars($f['_error'] ?? '').'</td>';
                } elseif (in_array($tipo, ['actualizar','reactivar'])) {
                    echo '<td><div class="cambio">';
                    if (!empty($f['_cambios'])) {
                        $parts = [];
                        foreach ($f['_cambios'] as $campo => $c) {
                            $parts[] = htmlspecialchars($campo).': <del>'
                                .htmlspecialchars((string)($c['antes'] ?? '∅')).'</del> &rarr; '
                                .htmlspecialchars((string)($c['despues'] ?? '∅'));
                        }
                        echo implode('<br>', $parts);
                    } else {
                        echo '<span style="color:#94a3b8">(reactivacion sin cambios de datos)</span>';
                    }
                    echo '</div></td>';
                }
                echo '</tr>';
            }
            echo '</tbody></table></div>';
            if (count($filas) > $maxFilas) {
                echo '<p style="color:#64748b;font-size:12px;margin-top:8px">Mostrando primeras '.$maxFilas.' de '.count($filas).' filas. Todas seran procesadas al confirmar.</p>';
            }
        }
        ?>

        <div class="tab-pane active" data-pane="nuevas"><?php renderTabla($clasificacion['nuevas'], 'row-new', 'nuevas'); ?></div>
        <div class="tab-pane" data-pane="actualizar"><?php renderTabla($clasificacion['a_actualizar'], 'row-upd', 'actualizar'); ?></div>
        <div class="tab-pane" data-pane="reactivar"><?php renderTabla($clasificacion['a_reactivar'], 'row-react', 'reactivar'); ?></div>
        <div class="tab-pane" data-pane="skip"><?php renderTabla($clasificacion['sin_cambios'], 'row-skip', 'skip'); ?></div>
        <div class="tab-pane" data-pane="errores"><?php renderTabla($clasificacion['errores'], 'row-err', 'errores'); ?></div>

        <?php if (!empty($clasificacion['a_desactivar'])): ?>
        <div class="tab-pane" data-pane="baja">
            <div style="overflow-x:auto"><table class="preview-table"><thead><tr>
                <th>Documento</th><th>Nombre</th><th>Apellido</th><th>Sede</th><th>Empresa</th>
            </tr></thead><tbody>
            <?php foreach ($clasificacion['a_desactivar'] as $p): ?>
            <tr style="border-left:3px solid #ea580c">
                <td><?= htmlspecialchars($p['documento']) ?></td>
                <td><?= htmlspecialchars(trim($p['primer_nombre'] . ' ' . $p['segundo_nombre'])) ?></td>
                <td><?= htmlspecialchars(trim($p['primer_apellido'] . ' ' . $p['segundo_apellido'])) ?></td>
                <td><?= htmlspecialchars($p['sede_nombre'] ?? '') ?></td>
                <td><span style="color:#64748b;font-size:11px"><?= htmlspecialchars($p['empresa']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            </tbody></table></div>
        </div>
        <?php endif; ?>

        <form method="POST" action="<?= BASE_URL ?>/personas/importar/confirmar" style="margin-top:24px;display:flex;gap:10px;justify-content:flex-end;border-top:1px solid #e2e8f0;padding-top:18px">
            <?= $csrfField ?>
            <a href="<?= BASE_URL ?>/personas/importar/cancelar" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary"<?= $puedeImportar ? '' : ' disabled' ?>>
                <?php if ($puedeImportar): ?>
                    Confirmar importacion (<?= $r['nuevas'] + $r['a_actualizar'] + $r['a_reactivar'] ?> registros<?= $r['a_desactivar'] > 0 ? ' · ' . $r['a_desactivar'] . ' bajas' : '' ?>)
                <?php else: ?>
                    No hay nada para importar
                <?php endif; ?>
            </button>
        </form>
    </div>
</div>

<script>
document.querySelectorAll('.tab-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var tab = btn.getAttribute('data-tab');
        document.querySelectorAll('.tab-btn').forEach(function (b) { b.classList.remove('active'); });
        document.querySelectorAll('.tab-pane').forEach(function (p) { p.classList.remove('active'); });
        btn.classList.add('active');
        var pane = document.querySelector('.tab-pane[data-pane="' + tab + '"]');
        if (pane) pane.classList.add('active');
    });
});
</script>
