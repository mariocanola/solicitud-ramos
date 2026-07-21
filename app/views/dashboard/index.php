<style>
.stat-value, .progress-bar { transition: all 0.4s ease; }
</style>

<!-- KPI Cards -->
<div class="row">
    <div class="col-3">
        <div class="stat-card primary">
            <div class="stat-value" id="kpi-total"><?= $estadisticas['total'] ?? 0 ?></div>
            <div class="stat-label">Total Solicitudes</div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card info">
            <div class="stat-value" id="kpi-mes"><?= $estadisticas['total_mes'] ?? 0 ?></div>
            <div class="stat-label">Solicitudes del Mes</div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card success">
            <div class="stat-value" id="kpi-semana"><?= $estadisticas['total_semana'] ?? 0 ?></div>
            <div class="stat-label">Solicitudes esta Semana</div>
        </div>
    </div>
    <div class="col-3">
        <div class="stat-card warning">
            <div class="stat-value"><?= htmlspecialchars($periodoActual) ?></div>
            <div class="stat-label">Periodo Actual</div>
        </div>
    </div>
</div>

<!-- Graficas -->
<div class="row mt-2">
    <!-- Grafica Circular: Solicitudes de la semana por sede -->
    <div class="col-6">
        <div class="card">
            <div class="card-header">Solicitudes de la <?= $tipo === 'weekly' ? 'Semana' : 'Mes' ?> por Sede</div>
            <div class="card-body">
                <?php if (empty($pieData['data'])): ?>
                    <p class="text-muted text-center" style="padding:40px 0">No hay solicitudes registradas esta semana.</p>
                <?php else: ?>
                <div style="position:relative;height:300px;display:flex;justify-content:center">
                    <canvas id="chartPie"></canvas>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Grafica de Barras: Solicitudes del periodo actual por sede -->
    <div class="col-6">
        <div class="card">
            <div class="card-header">Solicitudes de la <?= $tipo === 'weekly' ? 'Semana' : 'Mes' ?> por Sede</div>
            <div class="card-body">
                <?php if (empty($barData['data'])): ?>
                    <p class="text-muted text-center" style="padding:40px 0">No hay solicitudes registradas.</p>
                <?php else: ?>
                <div style="position:relative;height:300px">
                    <canvas id="chartBar"></canvas>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Cupos por Sede -->
<div class="card mt-2">
    <div class="card-header">Control de Cupos por Sede</div>
    <div class="card-body" id="cupos-container">
        <?php if (empty($cupos)): ?>
            <p class="text-muted">No hay cupos configurados para el periodo actual.</p>
        <?php else: ?>
            <?php foreach ($cupos as $cupo): ?>
            <div class="cupo-row" data-sede="<?= (int)$cupo['id_sede'] ?>" style="margin-bottom:15px">
                <div class="d-flex justify-between mb-1">
                    <strong><?= htmlspecialchars($cupo['sede_nombre']) ?></strong>
                    <span class="cupo-num"><?= $cupo['cupo_usado'] ?> / <?= $cupo['cupo_maximo'] ?></span>
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

<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {

    var pieData = <?= json_encode($pieData, JSON_UNESCAPED_UNICODE) ?>;
    var barData = <?= json_encode($barData, JSON_UNESCAPED_UNICODE) ?>;

    // === GRAFICA CIRCULAR: Solicitudes de la semana ===
    if (pieData.data.length > 0) {
        var pieTotal = pieData.data.reduce(function(a, b) { return a + b; }, 0);

        window._chartPie = new Chart(document.getElementById('chartPie').getContext('2d'), {
            type: 'doughnut',
            data: {
                labels: pieData.labels,
                datasets: [{
                    data: pieData.data,
                    backgroundColor: pieData.colors,
                    borderColor: pieData.borders,
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 16,
                            usePointStyle: true,
                            pointStyle: 'circle',
                            font: { size: 13, weight: '600' },
                            generateLabels: function(chart) {
                                var data = chart.data;
                                return data.labels.map(function(label, i) {
                                    var value = data.datasets[0].data[i];
                                    var pct = pieTotal > 0 ? Math.round((value / pieTotal) * 100) : 0;
                                    return {
                                        text: label + '  (' + value + ' - ' + pct + '%)',
                                        fillStyle: data.datasets[0].backgroundColor[i],
                                        strokeStyle: data.datasets[0].borderColor[i],
                                        lineWidth: 2,
                                        pointStyle: 'circle',
                                        index: i
                                    };
                                });
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(44, 62, 80, 0.95)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 13, weight: '600' },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: function(context) {
                                var value = context.raw;
                                var pct = pieTotal > 0 ? Math.round((value / pieTotal) * 100) : 0;
                                return ' ' + context.label + ': ' + value + ' solicitudes (' + pct + '%)';
                            }
                        }
                    }
                }
            }
        });
    }

    // === GRAFICA DE BARRAS: Total solicitudes por sede ===
    if (barData.data.length > 0) {
        window._chartBar = new Chart(document.getElementById('chartBar').getContext('2d'), {
            type: 'bar',
            data: {
                labels: barData.labels,
                datasets: [{
                    label: 'Solicitudes',
                    data: barData.data,
                    backgroundColor: barData.colors,
                    borderColor: barData.borders,
                    borderWidth: 2,
                    borderRadius: 6,
                    barPercentage: 0.6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                indexAxis: 'y',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(44, 62, 80, 0.95)',
                        padding: 12,
                        cornerRadius: 8,
                        titleFont: { size: 13, weight: '600' },
                        bodyFont: { size: 12 },
                        callbacks: {
                            label: function(context) {
                                return ' ' + context.raw + ' solicitudes';
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            font: { size: 12 },
                            color: '#7f8c8d'
                        },
                        grid: { color: 'rgba(0,0,0,0.05)' }
                    },
                    y: {
                        grid: { display: false },
                        ticks: {
                            font: { size: 13, weight: '600' },
                            color: '#2c3e50'
                        }
                    }
                }
            }
        });
    }

    // ============================================================
    // AUTO-ACTUALIZACION (heartbeat cada 2s + fetch completo solo si cambia)
    // ============================================================
    var BASE_URL = '<?= BASE_URL ?>';
    var HEARTBEAT_MS = 10000;
    var ultimoHash = null;

    function updateText(el, nuevo) {
        if (!el) return;
        if (String(el.textContent).trim() !== String(nuevo).trim()) {
            el.textContent = nuevo;
        }
    }

    function aplicarResumen(data) {
        if (!data || !data.estadisticas) return;
        var s = data.estadisticas;
        updateText(document.getElementById('kpi-total'),  s.total);
        updateText(document.getElementById('kpi-mes'),    s.total_mes);
        updateText(document.getElementById('kpi-semana'), s.total_semana);

        // Cupos
        if (Array.isArray(data.cupos)) {
            data.cupos.forEach(function (c) {
                var row = document.querySelector('.cupo-row[data-sede="' + c.id_sede + '"]');
                if (!row) return;
                var num = row.querySelector('.cupo-num');
                var bar = row.querySelector('.progress-bar');
                var pct = c.cupo_maximo > 0 ? Math.round((c.cupo_usado / c.cupo_maximo) * 100) : 0;
                var color = pct >= 90 ? 'var(--danger)' : (pct >= 70 ? 'var(--warning)' : 'var(--success)');
                updateText(num, c.cupo_usado + ' / ' + c.cupo_maximo);
                if (bar) {
                    bar.style.width = pct + '%';
                    bar.style.background = color;
                    bar.textContent = pct + '%';
                }
            });
        }

        // Charts
        if (window._chartPie && data.pie) {
            window._chartPie.data.labels   = data.pie.labels;
            window._chartPie.data.datasets[0].data = data.pie.data;
            window._chartPie.data.datasets[0].backgroundColor = data.pie.colors;
            window._chartPie.data.datasets[0].borderColor = data.pie.borders;
            window._chartPie.update('none');
        }
        if (window._chartBar && data.bar) {
            window._chartBar.data.labels   = data.bar.labels;
            window._chartBar.data.datasets[0].data = data.bar.data;
            window._chartBar.data.datasets[0].backgroundColor = data.bar.colors;
            window._chartBar.data.datasets[0].borderColor = data.bar.borders;
            window._chartBar.update('none');
        }
    }

    function fetchCompleto() {
        var ctrl = new AbortController();
        var timer = setTimeout(function() { ctrl.abort(); }, 8000);
        fetch(BASE_URL + '/api/dashboard/resumen', {
            signal: ctrl.signal,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { clearTimeout(timer); return r.json(); })
            .then(function (j) { if (j && j.success) aplicarResumen(j.data); })
            .catch(function () { clearTimeout(timer); });
    }

    function poll() {
        var ctrl = new AbortController();
        var timer = setTimeout(function() { ctrl.abort(); }, 5000);
        fetch(BASE_URL + '/api/dashboard/heartbeat', {
            signal: ctrl.signal,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (r) { clearTimeout(timer); return r.json(); })
            .then(function (j) {
                if (!j || !j.success) return;
                // El periodo entra en el hash: cuando cruza el limite (lunes 6 AM o 1 del mes)
                // el dashboard se refresca aunque no haya solicitudes nuevas.
                var hash = j.data.total + ':' + j.data.last + ':' + (j.data.period || '');
                if (hash !== ultimoHash) {
                    ultimoHash = hash;
                    fetchCompleto();
                }
            })
            .catch(function () { clearTimeout(timer); });
    }

    var pollerId = null;
    function startPoller() { if (!pollerId) pollerId = setInterval(poll, HEARTBEAT_MS); }
    function stopPoller()  { if (pollerId) { clearInterval(pollerId); pollerId = null; } }
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) stopPoller(); else { startPoller(); poll(); }
    });
    startPoller();
});
</script>
