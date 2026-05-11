<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flashTipo ?? 'info' ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<!-- Tabs -->
<div class="tabs">
    <button class="tab-btn <?= $tabActiva === 'listado' ? 'active' : '' ?>" onclick="cambiarTab('listado')">Listado de Solicitudes</button>
    <button class="tab-btn <?= $tabActiva === 'reportes' ? 'active' : '' ?>" onclick="cambiarTab('reportes')">Reportes / PDF</button>
</div>

<!-- ==================== TAB: LISTADO ==================== -->
<div class="tab-content <?= $tabActiva === 'listado' ? 'active' : '' ?>" id="tab_listado">

    <!-- Filtros -->
    <div class="card">
        <div class="card-header">
            <span>Filtros de Busqueda</span>
            <?php
                $hayFiltros = !empty($filtros['fecha_desde']) || !empty($filtros['fecha_hasta'])
                           || !empty($filtros['id_sede']) || !empty($filtros['id_estado'])
                           || !empty($filtros['busqueda']);
            ?>
            <?php if ($hayFiltros): ?>
                <a href="<?= BASE_URL ?>/solicitudes" class="btn btn-outline btn-sm">Limpiar filtros</a>
            <?php endif; ?>
        </div>
        <div class="card-body">
            <form id="form_filtros" method="GET" action="<?= BASE_URL ?>/solicitudes">
                <input type="hidden" name="tab" value="listado">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Buscar por documento o nombre</label>
                            <input type="text" name="busqueda" class="form-control"
                                   placeholder="Ej: 1234567890 o Juan Perez"
                                   value="<?= htmlspecialchars($filtros['busqueda'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label>Fecha desde</label>
                            <input type="date" name="fecha_desde" class="form-control"
                                   value="<?= htmlspecialchars($filtros['fecha_desde'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label>Fecha hasta</label>
                            <input type="date" name="fecha_hasta" class="form-control"
                                   value="<?= htmlspecialchars($filtros['fecha_hasta'] ?? '') ?>">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label>Sede</label>
                            <select name="id_sede" class="form-control">
                                <option value="">-- Todas --</option>
                                <?php foreach ($sedes as $sede): ?>
                                <option value="<?= $sede['id'] ?>" <?= ($filtros['id_sede'] ?? '') == $sede['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($sede['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="id_estado" class="form-control">
                                <option value="">-- Todos --</option>
                                <?php foreach ($estados as $estado): ?>
                                <option value="<?= $estado['id'] ?>" <?= ($filtros['id_estado'] ?? '') == $estado['id'] ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($estado['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-6" style="display:flex;align-items:flex-end;gap:8px;padding-bottom:16px">
                        <button type="submit" class="btn btn-primary">Buscar</button>
                        <?php if ($hayFiltros): ?>
                            <a href="<?= BASE_URL ?>/solicitudes" class="btn btn-outline">Limpiar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla -->
    <div class="card mt-2">
        <div class="card-header">
            <span>Solicitudes</span>
            <button class="btn btn-success btn-sm" onclick="abrirModalSolicitud()">+ Nueva Solicitud</button>
        </div>
        <div class="card-body">
            <?php if (empty($solicitudes)): ?>
                <div class="text-center" style="padding:40px 20px">
                    <div style="font-size:48px;margin-bottom:10px;opacity:0.3">&#128269;</div>
                    <?php if ($hayFiltros): ?>
                        <p style="font-size:16px;color:#555;margin-bottom:8px">No se encontraron solicitudes con los filtros aplicados.</p>
                        <a href="<?= BASE_URL ?>/solicitudes" class="btn btn-outline">Limpiar filtros</a>
                    <?php else: ?>
                        <p style="font-size:16px;color:#555;margin-bottom:8px">No hay solicitudes registradas aun.</p>
                        <button class="btn btn-success" onclick="abrirModalSolicitud()">Crear primera solicitud</button>
                    <?php endif; ?>
                </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Fecha</th>
                            <th>Solicitante</th>
                            <th>Documento</th>
                            <th>Sede</th>
                            <th>Motivo</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    $contador = ($paginaActual - 1) * ITEMS_PER_PAGE + 1;
                    foreach ($solicitudes as $s):
                    ?>
                        <tr>
                            <td><?= $contador ?></td>
                            <td style="white-space:nowrap"><?= $s['fecha_solicitud'] ?></td>
                            <td><?= htmlspecialchars(Persona::getNombreCompleto($s)) ?></td>
                            <td><?= htmlspecialchars($s['documento']) ?></td>
                            <td><?= htmlspecialchars($s['sede_nombre']) ?></td>
                            <td><?= htmlspecialchars($s['motivo_nombre']) ?></td>
                            <td>
                                <span class="badge" style="background:<?= htmlspecialchars($s['estado_color']) ?>">
                                    <?= htmlspecialchars($s['estado_nombre']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="<?= BASE_URL ?>/solicitudes/ver?id=<?= $s['id'] ?>" class="btn btn-primary btn-sm">Ver</a>
                                <button class="btn btn-danger btn-sm"
                                        onclick="eliminarSolicitud(<?= $s['id'] ?>)">
                                    Eliminar
                                </button>
                            </td>
                        </tr>
                    <?php
                    $contador++;
                    endforeach;
                    ?>
                    </tbody>
                </table>
            </div>

            <?php if ($totalPaginas > 1): ?>
            <div class="pagination">
                <?php if ($paginaActual > 1): ?>
                    <?php $params = $_GET; $params['pagina'] = $paginaActual - 1; ?>
                    <a href="<?= BASE_URL ?>/solicitudes?<?= http_build_query($params) ?>">&laquo; Anterior</a>
                <?php endif; ?>
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <?php $params = $_GET; $params['pagina'] = $i; $qs = http_build_query($params); ?>
                    <?php if ($i == $paginaActual): ?>
                        <span class="active"><?= $i ?></span>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/solicitudes?<?= $qs ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                <?php if ($paginaActual < $totalPaginas): ?>
                    <?php $params = $_GET; $params['pagina'] = $paginaActual + 1; ?>
                    <a href="<?= BASE_URL ?>/solicitudes?<?= http_build_query($params) ?>">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ==================== TAB: REPORTES ==================== -->
<div class="tab-content <?= $tabActiva === 'reportes' ? 'active' : '' ?>" id="tab_reportes">
    <div class="card">
        <div class="card-header">Generar Reporte PDF Consolidado</div>
        <div class="card-body">
            <form id="form_reporte" method="POST" action="<?= BASE_URL ?>/reportes/generar-pdf">
                <?= $csrfField ?>
                <div class="row">
                    <div class="col-3">
                        <div class="form-group">
                            <label>Fecha Desde *</label>
                            <input type="date" name="fecha_desde" class="form-control" required value="<?= date('Y-m-01') ?>">
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label>Fecha Hasta *</label>
                            <input type="date" name="fecha_hasta" class="form-control" required value="<?= date('Y-m-d') ?>">
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label>Sede</label>
                            <select name="id_sede" class="form-control">
                                <option value="">Todas las sedes</option>
                                <?php foreach ($sedes as $sede): ?>
                                <option value="<?= $sede['id'] ?>"><?= htmlspecialchars($sede['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-3">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="id_estado" class="form-control">
                                <option value="">Todos</option>
                                <?php foreach ($estados as $estado): ?>
                                <option value="<?= $estado['id'] ?>"><?= htmlspecialchars($estado['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
                <label class="card-option mt-2">
                    <input type="checkbox" name="incluir_hojas" value="1" class="card-option-input">
                    <span class="card-option-toggle" aria-hidden="true">
                        <span class="card-option-knob"></span>
                    </span>
                    <span class="card-option-content">
                        <span class="card-option-title">Incluir hojas individuales <span class="card-option-pill">remisiones para imprimir</span></span>
                        <span class="card-option-desc">Al final del PDF se anexan las solicitudes aprobadas.</span>
                    </span>
                </label>
                <div class="mt-2" style="display:flex;justify-content:flex-end">
                    <button type="submit" class="btn-descargar-pdf">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        <span>Descargar PDF</span>
                    </button>
                </div>
                <style>
                .btn-descargar-pdf {
                    display:inline-flex; align-items:center; gap:10px;
                    background: linear-gradient(135deg, #4A1942 0%, #5C2A47 100%);
                    color:#fff;
                    border:none;
                    padding:12px 26px;
                    font-size:14px; font-weight:600; letter-spacing:0.3px;
                    border-radius:8px;
                    cursor:pointer;
                    box-shadow:0 2px 6px rgba(74,25,66,0.25);
                    transition: transform 0.12s ease, box-shadow 0.12s ease, background 0.12s ease;
                }
                .btn-descargar-pdf:hover {
                    background: linear-gradient(135deg, #5C2A47 0%, #7A4866 100%);
                    box-shadow:0 4px 12px rgba(74,25,66,0.35);
                    transform: translateY(-1px);
                }
                .btn-descargar-pdf:active {
                    transform: translateY(0);
                    box-shadow:0 1px 3px rgba(74,25,66,0.25);
                }
                .btn-descargar-pdf:disabled {
                    opacity:0.6; cursor:not-allowed; transform:none;
                }
                .btn-descargar-pdf svg { flex-shrink:0; }

                /* ===== Opcion en formato tarjeta con toggle estilo switch ===== */
                .card-option {
                    display:flex; align-items:center; gap:14px;
                    padding:14px 16px;
                    background:#fff;
                    border:1px solid #e2e8f0;
                    border-left:3px solid #4A1942;
                    border-radius:8px;
                    cursor:pointer;
                    transition: background 0.15s ease, border-color 0.15s ease, box-shadow 0.15s ease;
                }
                .card-option:hover { background:#faf8fb; box-shadow:0 1px 4px rgba(74,25,66,0.08); }
                .card-option-input { position:absolute; opacity:0; pointer-events:none; }
                .card-option-toggle {
                    flex-shrink:0;
                    width:44px; height:24px;
                    background:#cbd5e1;
                    border-radius:999px;
                    position:relative;
                    transition: background 0.2s ease;
                }
                .card-option-knob {
                    position:absolute; top:2px; left:2px;
                    width:20px; height:20px;
                    background:#fff;
                    border-radius:50%;
                    box-shadow:0 1px 3px rgba(0,0,0,0.2);
                    transition: transform 0.2s cubic-bezier(0.4,0,0.2,1);
                }
                .card-option-input:checked + .card-option-toggle {
                    background: linear-gradient(135deg, #4A1942, #5C2A47);
                }
                .card-option-input:checked + .card-option-toggle .card-option-knob {
                    transform: translateX(20px);
                }
                .card-option-input:focus-visible + .card-option-toggle {
                    box-shadow: 0 0 0 3px rgba(74,25,66,0.25);
                }
                .card-option-content { display:flex; flex-direction:column; gap:3px; line-height:1.35; }
                .card-option-title {
                    font-weight:600; font-size:14px; color:#1e293b;
                    display:inline-flex; align-items:center; gap:8px;
                }
                .card-option-pill {
                    display:inline-block;
                    background:#f1e8ed;
                    color:#4A1942;
                    font-size:10px; font-weight:700;
                    padding:2px 8px; border-radius:999px;
                    text-transform:uppercase; letter-spacing:0.4px;
                }
                .card-option-desc { font-size:12px; color:#64748b; }
                </style>
            </form>
        </div>
    </div>
</div>

<!-- ==================== MODAL: NUEVA SOLICITUD ==================== -->
<div class="modal-overlay" id="modal_solicitud">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3>Nueva Solicitud</h3>
            <button class="modal-close" onclick="cerrarModalSolicitud()">&times;</button>
        </div>
        <div class="modal-body">
            <!-- Scanner -->
            <div class="form-group">
                <label>Escanee o digite el numero de documento:</label>
                <div class="d-flex gap-1">
                    <input type="text" id="scanner_input" class="form-control scanner-input"
                           placeholder="Escanee el codigo de barras o escriba el documento..." autocomplete="off" style="flex:1">
                    <button type="button" class="btn btn-primary btn-lg" onclick="buscarManual()">Buscar</button>
                </div>
            </div>
            <div id="scanner_status" class="text-muted mt-1" style="font-size:13px"></div>

            <!-- Persona info -->
            <div id="persona_info" class="persona-info hidden">
                <h4 id="persona_nombre"></h4>
                <p><strong>Documento:</strong> <span id="persona_doc"></span></p>
                <p><strong>Sede:</strong> <span id="persona_sede"></span></p>
                <p><strong>Telefono:</strong> <span id="persona_tel"></span></p>
            </div>

            <!-- Form -->
            <form id="form_solicitud" method="POST" action="<?= BASE_URL ?>/solicitudes/crear">
                <?= $csrfField ?>
                <!-- Datos automáticos: se completan desde la persona identificada -->
                <input type="hidden" name="persona_id"          id="persona_id"          value="">
                <input type="hidden" name="fecha_solicitud"     id="fecha_solicitud"     value="<?= $hoy ?>">
                <input type="hidden" name="id_sede"             id="id_sede"             value="">
                <input type="hidden" name="nombre_destinatario" id="nombre_destinatario" value="">

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Motivo *</label>
                            <select name="id_motivo" id="id_motivo" class="form-control" required>
                                <option value="">-- Seleccione el motivo --</option>
                                <?php foreach ($motivos as $motivo): ?>
                                <option value="<?= $motivo['id'] ?>" data-requiere-detalle="<?= $motivo['requiere_detalle'] ?>">
                                    <?= htmlspecialchars($motivo['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group hidden" id="grupo_motivo_otro">
                            <label>Especifique el motivo *</label>
                            <input type="text" name="motivo_otro" id="motivo_otro" class="form-control" maxlength="200">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label>Observaciones <span style="color:#7F8C8D;font-weight:400;font-size:12px">(opcional)</span></label>
                    <textarea name="observaciones" id="observaciones" class="form-control" maxlength="500"
                              placeholder="Si desea agregar algún detalle adicional, escríbalo aquí."></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline mr-1" onclick="cerrarModalSolicitud()">Cancelar</button>
            <button type="button" class="btn btn-success btn-lg" id="btn_guardar" disabled onclick="enviarSolicitud()" title="Primero busque una persona por documento">Guardar Solicitud</button>
        </div>
    </div>
</div>

<!-- ==================== MODAL: CREAR PERSONA ==================== -->
<div class="modal-overlay" id="modal_persona">
    <div class="modal">
        <div class="modal-header">
            <h3>Registrar Nueva Persona</h3>
            <button class="modal-close" onclick="cerrarModalPersona()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form_persona">
                <input type="hidden" name="_csrf_token" value="<?= Session::getCsrfToken() ?>">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Tipo Documento *</label>
                            <select name="tipo_documento" id="p_tipo_documento" class="form-control" required>
                                <option value="CC">CC - Cedula</option>
                                <option value="CE">CE - Cedula Extranjeria</option>
                                <option value="TI">TI - Tarjeta Identidad</option>
                                <option value="PA">PA - Pasaporte</option>
                                <option value="NIT">NIT</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Documento *</label>
                            <input type="text" name="documento" id="p_documento" class="form-control" maxlength="20">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Primer Nombre *</label>
                            <input type="text" name="primer_nombre" id="p_primer_nombre" class="form-control" required maxlength="50">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Segundo Nombre</label>
                            <input type="text" name="segundo_nombre" id="p_segundo_nombre" class="form-control" maxlength="50">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Primer Apellido *</label>
                            <input type="text" name="primer_apellido" id="p_primer_apellido" class="form-control" required maxlength="50">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Segundo Apellido</label>
                            <input type="text" name="segundo_apellido" id="p_segundo_apellido" class="form-control" maxlength="50">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Telefono</label>
                            <input type="text" name="telefono" id="p_telefono" class="form-control" maxlength="20">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Sede *</label>
                            <select name="id_sede" id="p_id_sede" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($sedes as $sede): ?>
                                <option value="<?= $sede['id'] ?>"><?= htmlspecialchars($sede['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline mr-1" onclick="cerrarModalPersona()">Cancelar</button>
            <button class="btn btn-success" onclick="guardarPersona()">Guardar Persona</button>
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
    // Activate the correct button
    var btns = document.querySelectorAll('.tab-btn');
    var tabNames = ['listado', 'reportes'];
    var idx = tabNames.indexOf(tab);
    if (idx >= 0 && btns[idx]) btns[idx].classList.add('active');
}

// Auto-rellena el destinatario oculto con el nombre completo de la persona identificada.
// solicitud.js dispara esta callback tras encontrar persona; aquí solo seteamos el campo.
window.onPersonaEncontrada = function (persona) {
    var dest = document.getElementById('nombre_destinatario');
    if (dest) {
        dest.value = persona.nombre_completo ||
            ((persona.primer_nombre || '') + ' ' + (persona.primer_apellido || '')).trim();
    }
};

// === MODAL SOLICITUD ===
function abrirModalSolicitud() {
    document.getElementById('modal_solicitud').classList.add('show');
    setTimeout(function() {
        var si = document.getElementById('scanner_input');
        if (si) si.focus();
    }, 100);
}

function cerrarModalSolicitud() {
    document.getElementById('modal_solicitud').classList.remove('show');
    // Reset form
    document.getElementById('form_solicitud').reset();
    document.getElementById('persona_id').value = '';
    document.getElementById('persona_info').classList.add('hidden');
    var btnGuardar = document.getElementById('btn_guardar');
    btnGuardar.disabled = true;
    btnGuardar.textContent = 'Guardar Solicitud';
    btnGuardar.title = 'Primero busque una persona por documento';
    document.getElementById('scanner_status').textContent = '';
    document.getElementById('scanner_input').value = '';
    var sedeSelect = document.getElementById('id_sede');
    sedeSelect.disabled = false;
    var hiddenSede = document.getElementById('id_sede_hidden');
    if (hiddenSede) hiddenSede.remove();
    var nota = document.getElementById('sede_nota');
    if (nota) nota.style.display = 'none';
    var gmo = document.getElementById('grupo_motivo_otro');
    if (gmo) gmo.classList.add('hidden');
}

function enviarSolicitud() {
    var personaId = document.getElementById('persona_id').value;
    if (!personaId) {
        swalWarning('Debe escanear o buscar una persona primero.');
        return;
    }

    var motivo = document.getElementById('id_motivo').value;
    if (!motivo) {
        swalWarning('Debe seleccionar un motivo.');
        document.getElementById('id_motivo').focus();
        return;
    }

    // Verificar motivo "otro"
    var opcionMotivo = document.getElementById('id_motivo').options[document.getElementById('id_motivo').selectedIndex];
    if (opcionMotivo.getAttribute('data-requiere-detalle') === '1') {
        var motivoOtro = document.getElementById('motivo_otro').value.trim();
        if (!motivoOtro) {
            swalWarning('Debe especificar el motivo.');
            document.getElementById('motivo_otro').focus();
            return;
        }
    }

    // Deshabilitar botón para evitar doble envío
    var btn = document.getElementById('btn_guardar');
    btn.disabled = true;
    btn.textContent = 'Guardando...';

    document.getElementById('form_solicitud').submit();
}

// === ELIMINAR SOLICITUD ===
function eliminarSolicitud(id) {
    swalConfirm(
        '¿Eliminar solicitud?',
        'Esta seguro de eliminar la solicitud #' + id + '? Esta accion no se puede deshacer.',
        function() {
            var formData = new FormData();
            formData.append('id', id);
            formData.append('_csrf_token', CSRF_TOKEN);

            ajaxPost(BASE_URL + '/solicitudes/eliminar', formData, function(data) {
                if (data.success) {
                    Toast.fire({ icon: 'success', title: 'Solicitud eliminada' }).then(function() {
                        location.reload();
                    });
                } else {
                    swalError(data.message || 'Error al eliminar');
                }
            });
        }
    );
}

// Stub para buscarManual - scanner.js lo sobreescribe
function buscarManual() {
    var input = document.getElementById('scanner_input');
    if (input && input.value.trim().length >= 3) {
        window.procesarEntradaScanner && window.procesarEntradaScanner(input.value.trim());
    } else {
        swalWarning('Escriba al menos 3 digitos del documento');
    }
}
</script>
<script>
// Client-side validators
// Solo el motivo es input visible del usuario; destinatario, sede y fecha se auto-completan
// desde la persona identificada (campos hidden), por eso no necesitan validación del cliente.
var solicitudValidator = new FormValidator('form_solicitud', {
    'id_motivo': [V.required('Debe seleccionar un motivo')]
});

var personaValidator = new FormValidator('form_persona', {
    'documento': [V.required('El documento es requerido'), V.numeric('Solo numeros')],
    'primer_nombre': [V.required('El primer nombre es requerido'), V.maxLength(50)],
    'primer_apellido': [V.required('El primer apellido es requerido'), V.maxLength(50)],
    'id_sede': [V.required('Debe seleccionar una sede')]
});
</script>
<?php
  $jsScannerVer = @filemtime(BASE_PATH . '/public/js/scanner.js') ?: time();
  $jsSolicitudVer = @filemtime(BASE_PATH . '/public/js/solicitud.js') ?: time();
?>
<script src="<?= BASE_URL ?>/js/scanner.js?v=<?= $jsScannerVer ?>"></script>
<script src="<?= BASE_URL ?>/js/solicitud.js?v=<?= $jsSolicitudVer ?>"></script>
