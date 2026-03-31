<!-- Dashboard -->
<div class="row">
    <div class="col-3">
        <div class="stat-card primary">
            <div class="stat-value"><?= $estadisticas['total'] ?? 0 ?></div>
            <div class="stat-label">Total Solicitudes</div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card info">
            <div class="stat-value"><?= $estadisticas['total_mes'] ?? 0 ?></div>
            <div class="stat-label">Solicitudes del Mes</div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card success">
            <div class="stat-value"><?= $totalSedes ?></div>
            <div class="stat-label">Sedes Activas</div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card warning">
            <div class="stat-value"><?= $periodoActual ?></div>
            <div class="stat-label">Período Actual</div>
        </div>
    </div>
</div>

<!-- Cupos por sede -->
<div class="card mt-2">
    <div class="card-header">Control de Cupos por Sede - <?= htmlspecialchars($periodoActual) ?></div>
    <div class="card-body">
        <?php if (empty($cupos)): ?>
            <p class="text-muted">No hay cupos configurados para el período actual.</p>
        <?php else: ?>
            <?php foreach ($cupos as $cupo): ?>
            <div style="margin-bottom:15px">
                <div class="d-flex justify-between mb-1">
                    <strong><?= htmlspecialchars($cupo['sede_nombre']) ?></strong>
                    <span><?= $cupo['cupo_usado'] ?> / <?= $cupo['cupo_maximo'] ?></span>
                </div>
                <?php
                    $pct = $cupo['cupo_maximo'] > 0 ? round(($cupo['cupo_usado'] / $cupo['cupo_maximo']) * 100) : 0;
                    $color = $pct >= 90 ? 'var(--danger)' : ($pct >= 70 ? 'var(--warning)' : 'var(--success)');
                ?>
                <div class="progress">
                    <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $color ?>"><?= $pct ?>%</div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<div class="row mt-2">
    <!-- Por Estado -->
    <div class="col-6">
        <div class="card">
            <div class="card-header">Solicitudes por Estado</div>
            <div class="card-body">
                <?php if (!empty($estadisticas['por_estado'])): ?>
                <table class="table">
                    <thead><tr><th>Estado</th><th>Total</th></tr></thead>
                    <tbody>
                    <?php foreach ($estadisticas['por_estado'] as $item): ?>
                    <tr>
                        <td><span class="badge" style="background:<?= htmlspecialchars($item['color']) ?>"><?= htmlspecialchars($item['nombre']) ?></span></td>
                        <td><?= $item['total'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted">Sin datos</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Por Motivo -->
    <div class="col-6">
        <div class="card">
            <div class="card-header">Solicitudes por Motivo</div>
            <div class="card-body">
                <?php if (!empty($estadisticas['por_motivo'])): ?>
                <table class="table">
                    <thead><tr><th>Motivo</th><th>Total</th></tr></thead>
                    <tbody>
                    <?php foreach ($estadisticas['por_motivo'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['nombre']) ?></td>
                        <td><?= $item['total'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                <p class="text-muted">Sin datos</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
