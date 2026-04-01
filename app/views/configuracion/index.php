<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flashTipo ?? 'info' ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<!-- Tabs -->
<div class="tabs">
    <button class="tab-btn <?= $tabActiva === 'general' ? 'active' : '' ?>" onclick="cambiarTab('general')">General</button>
    <button class="tab-btn <?= $tabActiva === 'cupos' ? 'active' : '' ?>" onclick="cambiarTab('cupos')">Control de Cupos</button>
    <button class="tab-btn <?= $tabActiva === 'sedes' ? 'active' : '' ?>" onclick="cambiarTab('sedes')">Gestion de Sedes</button>
</div>

<!-- ==================== TAB: GENERAL ==================== -->
<div class="tab-content <?= $tabActiva === 'general' ? 'active' : '' ?>" id="tab_general">
    <div class="card">
        <div class="card-header">Configuracion del Sistema</div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/configuracion/guardar">
                <?= $csrfField ?>
                <?php
                $groups = [
                    'Organizacion' => ['nombre_organizacion', 'logo_path'],
                    'Correo' => ['correo_destino', 'correo_cc'],
                    'SMTP' => ['smtp_host', 'smtp_port', 'smtp_user', 'smtp_pass', 'smtp_secure'],
                    'Cupos' => ['cupo_default'],
                ];
                $configMap = [];
                foreach ($configuraciones as $c) {
                    $configMap[$c['clave']] = $c;
                }
                ?>
                <?php foreach ($groups as $groupName => $claves): ?>
                <h4 style="color:var(--primary);margin:20px 0 10px;padding-bottom:5px;border-bottom:1px solid var(--border)">
                    <?= $groupName ?>
                </h4>
                <div class="row">
                    <?php foreach ($claves as $clave):
                        $config = $configMap[$clave] ?? null;
                        $tipo = ($clave === 'smtp_pass') ? 'password' : 'text';
                        if ($clave === 'smtp_port' || $clave === 'cupo_default') $tipo = 'number';
                    ?>
                    <div class="col-6">
                        <div class="form-group">
                            <label><?= htmlspecialchars($config['descripcion'] ?? $clave) ?></label>
                            <input type="<?= $tipo ?>" name="<?= htmlspecialchars($clave) ?>"
                                   class="form-control"
                                   value="<?= htmlspecialchars($config['valor'] ?? '') ?>">
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>
                <div class="text-right mt-3">
                    <button type="submit" class="btn btn-success btn-lg">Guardar Configuracion</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================== TAB: CUPOS ==================== -->
<div class="tab-content <?= $tabActiva === 'cupos' ? 'active' : '' ?>" id="tab_cupos">
    <div class="card">
        <div class="card-header">Cupos por Sede - <?= htmlspecialchars($periodoTexto) ?></div>
        <div class="card-body">
            <?php if (empty($sedes)): ?>
                <p class="text-muted text-center" style="padding:20px">No hay sedes configuradas. Cree una sede primero.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Sede</th>
                            <th>Cupo Maximo</th>
                            <th>Usado</th>
                            <th>Disponible</th>
                            <th>% Uso</th>
                            <th>Accion</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
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
                            <?php $color = $pct >= 90 ? 'var(--danger)' : ($pct >= 70 ? 'var(--warning)' : 'var(--success)'); ?>
                            <div class="progress" style="width:100px;display:inline-block">
                                <div class="progress-bar" style="width:<?= $pct ?>%;background:<?= $color ?>"><?= $pct ?>%</div>
                            </div>
                        </td>
                        <td>-</td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==================== TAB: SEDES ==================== -->
<div class="tab-content <?= $tabActiva === 'sedes' ? 'active' : '' ?>" id="tab_sedes">
    <div class="card">
        <div class="card-header">
            <span>Sedes Registradas</span>
            <button class="btn btn-success btn-sm" onclick="abrirModalSede()">+ Nueva Sede</button>
        </div>
        <div class="card-body">
            <?php if (empty($todasSedes)): ?>
                <p class="text-muted text-center" style="padding:20px">No hay sedes registradas.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Codigo</th>
                            <th>Direccion</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($todasSedes as $sede): ?>
                    <tr>
                        <td><?= $sede['id'] ?></td>
                        <td><strong><?= htmlspecialchars($sede['nombre']) ?></strong></td>
                        <td><?= htmlspecialchars($sede['codigo']) ?></td>
                        <td><?= htmlspecialchars($sede['direccion'] ?? '-') ?></td>
                        <td>
                            <?php if ($sede['activo']): ?>
                                <span class="badge" style="background:var(--success)">Activa</span>
                            <?php else: ?>
                                <span class="badge" style="background:var(--danger)">Inactiva</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm"
                                    onclick="editarSede(<?= $sede['id'] ?>, '<?= htmlspecialchars(addslashes($sede['nombre']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($sede['codigo']), ENT_QUOTES) ?>', '<?= htmlspecialchars(addslashes($sede['direccion'] ?? ''), ENT_QUOTES) ?>', <?= $sede['activo'] ?>)">
                                Editar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==================== MODAL: CREAR/EDITAR SEDE ==================== -->
<div class="modal-overlay" id="modal_sede">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modal_sede_titulo">Nueva Sede</h3>
            <button class="modal-close" onclick="cerrarModalSede()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form_sede">
                <input type="hidden" name="_csrf_token" value="<?= Session::getCsrfToken() ?>">
                <input type="hidden" name="id" id="sede_id" value="">

                <div class="form-group">
                    <label>Nombre de la Sede *</label>
                    <input type="text" name="nombre" id="sede_nombre" class="form-control" required maxlength="100">
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Codigo *</label>
                            <input type="text" name="codigo" id="sede_codigo" class="form-control" required maxlength="20"
                                   placeholder="Ej: SP, SB, etc.">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="activo" id="sede_activo" class="form-control">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Direccion</label>
                    <input type="text" name="direccion" id="sede_direccion" class="form-control" maxlength="255">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline mr-1" onclick="cerrarModalSede()">Cancelar</button>
            <button class="btn btn-success" onclick="guardarSede()">Guardar Sede</button>
        </div>
    </div>
</div>

<script>
var BASE_URL = '<?= BASE_URL ?>';
var CSRF_TOKEN = '<?= Session::getCsrfToken() ?>';

// === TABS ===
function cambiarTab(tab) {
    document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });
    document.getElementById('tab_' + tab).classList.add('active');
    var btns = document.querySelectorAll('.tab-btn');
    var tabNames = ['general', 'cupos', 'sedes'];
    var idx = tabNames.indexOf(tab);
    if (idx >= 0 && btns[idx]) btns[idx].classList.add('active');
}

// === MODAL SEDE ===
function abrirModalSede() {
    document.getElementById('modal_sede_titulo').textContent = 'Nueva Sede';
    document.getElementById('form_sede').reset();
    document.getElementById('sede_id').value = '';
    document.getElementById('modal_sede').classList.add('show');
    document.getElementById('sede_nombre').focus();
}

function editarSede(id, nombre, codigo, direccion, activo) {
    document.getElementById('modal_sede_titulo').textContent = 'Editar Sede';
    document.getElementById('sede_id').value = id;
    document.getElementById('sede_nombre').value = nombre;
    document.getElementById('sede_codigo').value = codigo;
    document.getElementById('sede_direccion').value = direccion;
    document.getElementById('sede_activo').value = activo;
    document.getElementById('modal_sede').classList.add('show');
}

function cerrarModalSede() {
    document.getElementById('modal_sede').classList.remove('show');
}

function guardarSede() {
    var nombre = document.getElementById('sede_nombre').value.trim();
    var codigo = document.getElementById('sede_codigo').value.trim();
    if (!nombre || !codigo) {
        alert('Nombre y codigo son requeridos');
        return;
    }

    var form = document.getElementById('form_sede');
    var formData = new FormData(form);
    var sedeId = document.getElementById('sede_id').value;
    var url = sedeId
        ? BASE_URL + '/api/sedes/actualizar'
        : BASE_URL + '/api/sedes/crear';

    ajaxPost(url, formData, function(data) {
        if (data.success) {
            cerrarModalSede();
            location.href = BASE_URL + '/configuracion?tab=sedes';
        } else {
            alert(data.message || 'Error al guardar la sede');
        }
    });
}
</script>
