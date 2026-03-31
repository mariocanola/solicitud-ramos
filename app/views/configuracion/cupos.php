<!-- Control de Cupos -->

<?php $flash = Session::getFlash('mensaje'); $flashTipo = Session::getFlash('tipo'); ?>
<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flashTipo ?? 'info' ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">Cupos por Sede - <?= htmlspecialchars($periodoTexto) ?></div>
    <div class="card-body">
        <?php if (empty($cupos) && empty($sedes)): ?>
            <p class="text-muted">No hay sedes configuradas.</p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Sede</th>
                        <th>Cupo Máximo</th>
                        <th>Usado</th>
                        <th>Disponible</th>
                        <th>% Uso</th>
                        <th>Notificado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                // Build cupo lookup by sede
                $cupoMap = [];
                foreach ($cupos as $c) {
                    $cupoMap[$c['id_sede']] = $c;
                }
                foreach ($sedes as $sede):
                    $cupo = $cupoMap[$sede['id']] ?? null;
                    $usado = $cupo ? (int)$cupo['cupo_usado'] : 0;
                    $max = $cupo ? (int)$cupo['cupo_maximo'] : 0;
                    $disp = $max - $usado;
                    $pct = $max > 0 ? round(($usado / $max) * 100) : 0;
                    $notif = $cupo ? (bool)$cupo['notificado'] : false;
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars($sede['nombre']) ?></strong></td>
                    <td>
                        <form method="POST" action="<?= BASE_URL ?>/cupos/actualizar" class="d-flex gap-1 align-center">
                            <?= $csrfField ?>
                            <input type="hidden" name="id_sede" value="<?= $sede['id'] ?>">
                            <input type="number" name="cupo_maximo" class="form-control"
                                   style="width:80px" min="1" value="<?= $max ?: 50 ?>">
                            <button type="submit" class="btn btn-primary btn-sm">Guardar</button>
                        </form>
                    </td>
                    <td><?= $usado ?></td>
                    <td><?= $disp ?></td>
                    <td>
                        <?php
                        $color = $pct >= 90 ? 'var(--danger)' : ($pct >= 70 ? 'var(--warning)' : 'var(--success)');
                        ?>
                        <div class="progress" style="width:100px;display:inline-block">
                            <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $color ?>"><?= $pct ?>%</div>
                        </div>
                    </td>
                    <td><?= $notif ? '<span class="text-success">Sí</span>' : '<span class="text-muted">No</span>' ?></td>
                    <td>-</td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
