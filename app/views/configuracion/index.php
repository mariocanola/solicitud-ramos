<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flashTipo ?? 'info' ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<!-- Tabs -->
<div class="tabs">
    <button class="tab-btn <?= $tabActiva === 'general' ? 'active' : '' ?>" onclick="cambiarTab('general')">General</button>
    <button class="tab-btn <?= $tabActiva === 'cupos' ? 'active' : '' ?>" onclick="cambiarTab('cupos')">Control de Cupos</button>
    <button class="tab-btn <?= $tabActiva === 'sedes' ? 'active' : '' ?>" onclick="cambiarTab('sedes')">Gestion de Sedes</button>
    <button class="tab-btn <?= $tabActiva === 'motivos' ? 'active' : '' ?>" onclick="cambiarTab('motivos')">Motivos de Ramo</button>
    <button class="tab-btn <?= $tabActiva === 'estados' ? 'active' : '' ?>" onclick="cambiarTab('estados')">Estados de Solicitud</button>
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
                    'Cupos' => ['cupo_default'],
                    'Hoja individual (PDF de remision)' => ['empresa_destinataria', 'destinatario_solicitudes'],
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
                        $tipo = ($clave === 'cupo_default') ? 'number' : 'text';
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
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Codigo</th>
                            <th>Direccion</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1; foreach ($todasSedes as $sede): ?>
                    <tr>
                        <td><?= $i++ ?></td>
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
                            <button class="btn btn-danger btn-sm"
                                    onclick="eliminarRegistro('api/sedes/eliminar', <?= $sede['id'] ?>, 'sedes', '<?= htmlspecialchars(addslashes($sede['nombre']), ENT_QUOTES) ?>')">
                                Eliminar
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

<!-- ==================== TAB: MOTIVOS DE RAMO ==================== -->
<div class="tab-content <?= $tabActiva === 'motivos' ? 'active' : '' ?>" id="tab_motivos">
    <div class="card">
        <div class="card-header">
            <span>Motivos de Ramo</span>
            <button class="btn btn-success btn-sm" onclick="abrirModalMotivo()">+ Nuevo Motivo</button>
        </div>
        <div class="card-body">
            <?php if (empty($motivos)): ?>
                <p class="text-muted text-center" style="padding:20px">No hay motivos registrados.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Requiere Detalle</th>
                            <th>Orden</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1; foreach ($motivos as $motivo): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td><strong><?= htmlspecialchars($motivo['nombre']) ?></strong></td>
                        <td><?= $motivo['requiere_detalle'] ? 'Si' : 'No' ?></td>
                        <td><?= $motivo['orden'] ?></td>
                        <td>
                            <?php if ($motivo['activo']): ?>
                                <span class="badge" style="background:var(--success)">Activo</span>
                            <?php else: ?>
                                <span class="badge" style="background:var(--danger)">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm"
                                    onclick="editarMotivo(<?= $motivo['id'] ?>, '<?= htmlspecialchars(addslashes($motivo['nombre']), ENT_QUOTES) ?>', <?= $motivo['requiere_detalle'] ?>, <?= $motivo['orden'] ?>, <?= $motivo['activo'] ?>)">
                                Editar
                            </button>
                            <button class="btn btn-danger btn-sm"
                                    onclick="eliminarRegistro('api/motivos/eliminar', <?= $motivo['id'] ?>, 'motivos', '<?= htmlspecialchars(addslashes($motivo['nombre']), ENT_QUOTES) ?>')">
                                Eliminar
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

<!-- ==================== TAB: ESTADOS DE SOLICITUD ==================== -->
<div class="tab-content <?= $tabActiva === 'estados' ? 'active' : '' ?>" id="tab_estados">
    <div class="card">
        <div class="card-header">
            <span>Estados de Solicitud</span>
            <button class="btn btn-success btn-sm" onclick="abrirModalEstado()">+ Nuevo Estado</button>
        </div>
        <div class="card-body">
            <?php if (empty($estadosSolicitud)): ?>
                <p class="text-muted text-center" style="padding:20px">No hay estados registrados.</p>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Color</th>
                            <th>Orden</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $i = 1; foreach ($estadosSolicitud as $estado): ?>
                    <tr>
                        <td><?= $i++ ?></td>
                        <td>
                            <span class="badge" style="background:<?= htmlspecialchars($estado['color'] ?? '#6c757d') ?>">
                                <?= htmlspecialchars($estado['nombre']) ?>
                            </span>
                        </td>
                        <td>
                            <span style="display:inline-block;width:24px;height:24px;border-radius:4px;background:<?= htmlspecialchars($estado['color'] ?? '#6c757d') ?>;vertical-align:middle"></span>
                            <?= htmlspecialchars($estado['color'] ?? '#6c757d') ?>
                        </td>
                        <td><?= $estado['orden'] ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm"
                                    onclick="editarEstado(<?= $estado['id'] ?>, '<?= htmlspecialchars(addslashes($estado['nombre']), ENT_QUOTES) ?>', '<?= htmlspecialchars($estado['color'] ?? '#6c757d', ENT_QUOTES) ?>', <?= $estado['orden'] ?>)">
                                Editar
                            </button>
                            <button class="btn btn-danger btn-sm"
                                    onclick="eliminarRegistro('api/estados/eliminar', <?= $estado['id'] ?>, 'estados', '<?= htmlspecialchars(addslashes($estado['nombre']), ENT_QUOTES) ?>')">
                                Eliminar
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

<!-- ==================== MODAL: CREAR/EDITAR MOTIVO ==================== -->
<div class="modal-overlay" id="modal_motivo">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modal_motivo_titulo">Nuevo Motivo</h3>
            <button class="modal-close" onclick="cerrarModalMotivo()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form_motivo">
                <input type="hidden" name="_csrf_token" value="<?= Session::getCsrfToken() ?>">
                <input type="hidden" name="id" id="motivo_id" value="">

                <div class="form-group">
                    <label>Nombre del Motivo *</label>
                    <input type="text" name="nombre" id="motivo_nombre" class="form-control" required maxlength="100">
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <label>Orden</label>
                            <input type="number" name="orden" id="motivo_orden" class="form-control" min="0" value="0">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Requiere Detalle</label>
                            <select name="requiere_detalle" id="motivo_requiere_detalle" class="form-control">
                                <option value="0">No</option>
                                <option value="1">Si</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="activo" id="motivo_activo" class="form-control">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline mr-1" onclick="cerrarModalMotivo()">Cancelar</button>
            <button class="btn btn-success" onclick="guardarMotivo()">Guardar Motivo</button>
        </div>
    </div>
</div>

<!-- ==================== MODAL: CREAR/EDITAR ESTADO ==================== -->
<div class="modal-overlay" id="modal_estado">
    <div class="modal">
        <div class="modal-header">
            <h3 id="modal_estado_titulo">Nuevo Estado</h3>
            <button class="modal-close" onclick="cerrarModalEstado()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form_estado">
                <input type="hidden" name="_csrf_token" value="<?= Session::getCsrfToken() ?>">
                <input type="hidden" name="id" id="estado_id" value="">

                <div class="form-group">
                    <label>Nombre del Estado *</label>
                    <input type="text" name="nombre" id="estado_nombre" class="form-control" required maxlength="50">
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Color</label>
                            <input type="color" name="color" id="estado_color" class="form-control" value="#6c757d" style="height:38px;padding:2px">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Orden</label>
                            <input type="number" name="orden" id="estado_orden" class="form-control" min="0" value="0">
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline mr-1" onclick="cerrarModalEstado()">Cancelar</button>
            <button class="btn btn-success" onclick="guardarEstado()">Guardar Estado</button>
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
    var tabNames = ['general', 'cupos', 'sedes', 'motivos', 'estados'];
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
    if (typeof sedeValidator !== 'undefined' && sedeValidator.reset) sedeValidator.reset();
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


// === MODAL MOTIVO ===
function abrirModalMotivo() {
    document.getElementById('modal_motivo_titulo').textContent = 'Nuevo Motivo';
    document.getElementById('form_motivo').reset();
    document.getElementById('motivo_id').value = '';
    document.getElementById('modal_motivo').classList.add('show');
    document.getElementById('motivo_nombre').focus();
}

function editarMotivo(id, nombre, requiereDetalle, orden, activo) {
    document.getElementById('modal_motivo_titulo').textContent = 'Editar Motivo';
    // Resetear validador antes de llenar para que no queden errores rojos de aperturas previas.
    if (typeof motivoValidator !== 'undefined' && motivoValidator.reset) motivoValidator.reset();
    document.getElementById('motivo_id').value = id;
    document.getElementById('motivo_nombre').value = nombre;
    document.getElementById('motivo_requiere_detalle').value = requiereDetalle;
    document.getElementById('motivo_orden').value = (orden !== null && orden !== undefined) ? orden : 0;
    document.getElementById('motivo_activo').value = activo;
    document.getElementById('modal_motivo').classList.add('show');
}

function cerrarModalMotivo() {
    document.getElementById('modal_motivo').classList.remove('show');
}


// === MODAL ESTADO ===
function abrirModalEstado() {
    document.getElementById('modal_estado_titulo').textContent = 'Nuevo Estado';
    document.getElementById('form_estado').reset();
    document.getElementById('estado_id').value = '';
    document.getElementById('estado_color').value = '#6c757d';
    document.getElementById('modal_estado').classList.add('show');
    document.getElementById('estado_nombre').focus();
}

function editarEstado(id, nombre, color, orden) {
    document.getElementById('modal_estado_titulo').textContent = 'Editar Estado';
    if (typeof estadoValidator !== 'undefined' && estadoValidator.reset) estadoValidator.reset();
    document.getElementById('estado_id').value = id;
    document.getElementById('estado_nombre').value = nombre;
    document.getElementById('estado_color').value = color;
    document.getElementById('estado_orden').value = orden;
    document.getElementById('modal_estado').classList.add('show');
}

function cerrarModalEstado() {
    document.getElementById('modal_estado').classList.remove('show');
}

// === ELIMINAR GENERICO ===
function eliminarRegistro(endpoint, id, tab, nombre) {
    swalConfirm(
        '¿Eliminar registro?',
        'Esta seguro de eliminar "' + nombre + '"? Esta accion no se puede deshacer.',
        function() {
            var formData = new FormData();
            formData.append('id', id);
            formData.append('_csrf_token', CSRF_TOKEN);

            ajaxPost(BASE_URL + '/' + endpoint, formData, function(data) {
                if (data.success) {
                    Toast.fire({ icon: 'success', title: 'Eliminado correctamente' }).then(function() {
                        location.href = BASE_URL + '/configuracion?tab=' + tab;
                    });
                } else {
                    swalError(data.message || 'Error al eliminar');
                }
            });
        }
    );
}

// === VALIDATORS ===
var sedeValidator = new FormValidator('form_sede', {
    'nombre': [V.required('El nombre es requerido'), V.minLength(2), V.maxLength(100)],
    'codigo': [V.required('El codigo es requerido'), V.minLength(1), V.maxLength(20), V.alphanumeric()]
});

var motivoValidator = new FormValidator('form_motivo', {
    'nombre': [V.required('El nombre es requerido'), V.minLength(2), V.maxLength(100)],
    'orden': [V.integer(0, 999)]
});

var estadoValidator = new FormValidator('form_estado', {
    'nombre': [V.required('El nombre es requerido'), V.minLength(2), V.maxLength(50)],
    'orden': [V.integer(0, 999)]
});

function guardarSede() {
    if (!sedeValidator.validateAll()) return;

    var form = document.getElementById('form_sede');
    var formData = new FormData(form);
    var sedeId = document.getElementById('sede_id').value;
    var url = sedeId
        ? BASE_URL + '/api/sedes/actualizar'
        : BASE_URL + '/api/sedes/crear';

    ajaxPost(url, formData, function(data) {
        if (data.success) {
            cerrarModalSede();
            Toast.fire({ icon: 'success', title: sedeId ? 'Sede actualizada' : 'Sede creada' }).then(function() {
                location.href = BASE_URL + '/configuracion?tab=sedes';
            });
        } else {
            if (data.errors && Object.keys(data.errors).length > 0) sedeValidator.showServerErrors(data.errors);
            else swalError(data.message || 'Error al guardar la sede');
        }
    });
}

function guardarMotivo() {
    if (!motivoValidator.validateAll()) return;

    var form = document.getElementById('form_motivo');
    var formData = new FormData(form);
    var motivoId = document.getElementById('motivo_id').value;
    var url = motivoId
        ? BASE_URL + '/api/motivos/actualizar'
        : BASE_URL + '/api/motivos/crear';

    ajaxPost(url, formData, function(data) {
        if (data.success) {
            cerrarModalMotivo();
            Toast.fire({ icon: 'success', title: motivoId ? 'Motivo actualizado' : 'Motivo creado' }).then(function() {
                location.href = BASE_URL + '/configuracion?tab=motivos';
            });
        } else {
            if (data.errors && Object.keys(data.errors).length > 0) motivoValidator.showServerErrors(data.errors);
            else swalError(data.message || 'Error al guardar el motivo');
        }
    });
}

function guardarEstado() {
    if (!estadoValidator.validateAll()) return;

    var form = document.getElementById('form_estado');
    var formData = new FormData(form);
    var estadoId = document.getElementById('estado_id').value;
    var url = estadoId
        ? BASE_URL + '/api/estados/actualizar'
        : BASE_URL + '/api/estados/crear';

    ajaxPost(url, formData, function(data) {
        if (data.success) {
            cerrarModalEstado();
            Toast.fire({ icon: 'success', title: estadoId ? 'Estado actualizado' : 'Estado creado' }).then(function() {
                location.href = BASE_URL + '/configuracion?tab=estados';
            });
        } else {
            if (data.errors && Object.keys(data.errors).length > 0) estadoValidator.showServerErrors(data.errors);
            else swalError(data.message || 'Error al guardar el estado');
        }
    });
}

// Reset validators when opening modals
var _origAbrirSede = abrirModalSede;
abrirModalSede = function() { _origAbrirSede(); sedeValidator.reset(); };
var _origAbrirMotivo = abrirModalMotivo;
abrirModalMotivo = function() { _origAbrirMotivo(); motivoValidator.reset(); };
var _origAbrirEstado = abrirModalEstado;
abrirModalEstado = function() { _origAbrirEstado(); estadoValidator.reset(); };
</script>
