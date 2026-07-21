<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flashTipo ?? 'info' ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<!-- Filtros -->
<div class="card">
    <div class="card-header">
        <span>Filtros de Busqueda</span>
        <?php
            $hayFiltros = !empty($filtros['busqueda']) || !empty($filtros['id_sede']) || !empty($filtros['empresa']);
        ?>
        <?php if ($hayFiltros): ?>
            <a href="<?= BASE_URL ?>/personas" class="btn btn-outline btn-sm">Limpiar filtros</a>
        <?php endif; ?>
    </div>
    <div class="card-body">
        <form method="GET" action="<?= BASE_URL ?>/personas">
            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label>Buscar por documento o nombre</label>
                        <input type="text" name="busqueda" class="form-control"
                               placeholder="Ej: 1234567890 o Juan"
                               value="<?= htmlspecialchars($filtros['busqueda'] ?? '') ?>">
                    </div>
                </div>
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
                        <label>Empresa</label>
                        <select name="empresa" class="form-control">
                            <option value="">-- Todas --</option>
                            <option value="TANDIL" <?= ($filtros['empresa'] ?? '') === 'TANDIL' ? 'selected' : '' ?>>Tandil</option>
                            <option value="CREOS" <?= ($filtros['empresa'] ?? '') === 'CREOS' ? 'selected' : '' ?>>Creos</option>
                        </select>
                    </div>
                </div>
                <div class="col-12" style="display:flex;justify-content:flex-end;gap:8px">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla -->
<div class="card mt-2">
    <div class="card-header">
        <span>Personas</span>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
            <a href="<?= BASE_URL ?>/personas/importar" class="btn-cargar-maestro">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <span>Cargar maestro</span>
            </a>
            <button class="btn btn-success btn-sm" onclick="abrirModalPersona()">+ Nueva Persona</button>
        </div>
        <style>
        .btn-cargar-maestro {
            display:inline-flex; align-items:center; gap:7px;
            background:#fff;
            color:#4A1942;
            border:1.5px solid #4A1942;
            padding:6px 14px;
            font-size:13px; font-weight:600;
            border-radius:6px;
            text-decoration:none;
            transition: background 0.15s, color 0.15s, box-shadow 0.15s;
        }
        .btn-cargar-maestro:hover {
            background:#4A1942;
            color:#fff;
            box-shadow:0 2px 6px rgba(74,25,66,0.25);
        }
        .btn-cargar-maestro svg { flex-shrink:0; }
        </style>
    </div>
    <div class="card-body">
        <?php if (empty($personas)): ?>
            <div class="text-center" style="padding:40px 20px">
                <div style="font-size:48px;margin-bottom:10px;opacity:0.3">&#128100;</div>
                <?php if ($hayFiltros): ?>
                    <p style="font-size:16px;color:#555;margin-bottom:8px">No se encontraron personas con los filtros aplicados.</p>
                    <a href="<?= BASE_URL ?>/personas" class="btn btn-outline">Limpiar filtros</a>
                <?php else: ?>
                    <p style="font-size:16px;color:#555;margin-bottom:8px">No hay personas registradas aun.</p>
                    <button class="btn btn-success" onclick="abrirModalPersona()">Registrar primera persona</button>
                <?php endif; ?>
            </div>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Documento</th>
                        <th>Nombre Completo</th>
                        <th>Telefono</th>
                        <th>Sede</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $contador = ($paginaActual - 1) * $porPagina + 1;
                foreach ($personas as $p):
                ?>
                    <tr>
                        <td><?= $contador ?></td>
                        <td><?= htmlspecialchars($p['documento']) ?></td>
                        <td><strong><?= htmlspecialchars(Persona::getNombreCompleto($p)) ?></strong></td>
                        <td><?= htmlspecialchars($p['telefono'] ?? '-') ?></td>
                        <td>
                            <?= htmlspecialchars($p['sede_nombre']) ?><?php if (($p['empresa'] ?? 'TANDIL') === 'CREOS'): ?><span class="badge-creos">/Creos</span><?php endif; ?>
                        </td>
                        <td>
                            <?php if ($p['activo']): ?>
                                <span class="badge" style="background:var(--success)">Activo</span>
                            <?php else: ?>
                                <span class="badge" style="background:var(--danger)">Inactivo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button class="btn btn-primary btn-sm"
                                    onclick="editarPersona(<?= htmlspecialchars(json_encode($p), ENT_QUOTES) ?>)">
                                Editar
                            </button>
                            <?php if ($p['activo']): ?>
                            <button class="btn btn-danger btn-sm"
                                    onclick="togglePersona(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes(Persona::getNombreCompleto($p)), ENT_QUOTES) ?>', 0)">
                                Inhabilitar
                            </button>
                            <?php else: ?>
                            <button class="btn btn-success btn-sm"
                                    onclick="togglePersona(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes(Persona::getNombreCompleto($p)), ENT_QUOTES) ?>', 1)">
                                Habilitar
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php
                $contador++;
                endforeach;
                ?>
                </tbody>
            </table>
        </div>

        <?php
        // Barra de info + selector tamano + paginacion compacta
        $desde = ($paginaActual - 1) * $porPagina + 1;
        $hasta = min($paginaActual * $porPagina, $totalRegistros);
        $linkPag = function ($pagina) use ($paginaActual) {
            $params = $_GET; $params['pagina'] = $pagina;
            return BASE_URL . '/personas?' . http_build_query($params);
        };
        // Construir lista de paginas: 1, ..., (n-2..n+2), ..., total
        $paginasMostrar = [];
        if ($totalPaginas <= 7) {
            for ($i = 1; $i <= $totalPaginas; $i++) $paginasMostrar[] = $i;
        } else {
            $paginasMostrar[] = 1;
            $inicio = max(2, $paginaActual - 2);
            $fin    = min($totalPaginas - 1, $paginaActual + 2);
            if ($inicio > 2) $paginasMostrar[] = '...';
            for ($i = $inicio; $i <= $fin; $i++) $paginasMostrar[] = $i;
            if ($fin < $totalPaginas - 1) $paginasMostrar[] = '...';
            $paginasMostrar[] = $totalPaginas;
        }
        ?>
        <div class="pagination-bar">
            <div class="pagination-info">
                Mostrando <strong><?= number_format($desde) ?>&ndash;<?= number_format($hasta) ?></strong>
                de <strong><?= number_format($totalRegistros) ?></strong>
            </div>
            <form method="GET" action="<?= BASE_URL ?>/personas" class="pagination-pagesize">
                <?php foreach (['busqueda','id_sede'] as $k): if (!empty($_GET[$k])): ?>
                    <input type="hidden" name="<?= $k ?>" value="<?= htmlspecialchars($_GET[$k]) ?>">
                <?php endif; endforeach; ?>
                <label for="por_pagina">Por pagina</label>
                <select id="por_pagina" name="por_pagina" onchange="this.form.submit()">
                    <?php foreach ([15,30,50,100] as $opt): ?>
                        <option value="<?= $opt ?>" <?= $porPagina == $opt ? 'selected' : '' ?>><?= $opt ?></option>
                    <?php endforeach; ?>
                </select>
            </form>
            <?php if ($totalPaginas > 1): ?>
            <nav class="pagination-nav" aria-label="Paginacion">
                <a class="pg-btn <?= $paginaActual <= 1 ? 'disabled' : '' ?>"
                   <?= $paginaActual > 1 ? 'href="' . $linkPag($paginaActual - 1) . '"' : '' ?>>&laquo;</a>
                <?php foreach ($paginasMostrar as $p): ?>
                    <?php if ($p === '...'): ?>
                        <span class="pg-ellipsis">&hellip;</span>
                    <?php elseif ($p == $paginaActual): ?>
                        <span class="pg-btn active"><?= $p ?></span>
                    <?php else: ?>
                        <a class="pg-btn" href="<?= $linkPag($p) ?>"><?= $p ?></a>
                    <?php endif; ?>
                <?php endforeach; ?>
                <a class="pg-btn <?= $paginaActual >= $totalPaginas ? 'disabled' : '' ?>"
                   <?= $paginaActual < $totalPaginas ? 'href="' . $linkPag($paginaActual + 1) . '"' : '' ?>>&raquo;</a>
            </nav>
            <?php endif; ?>
        </div>
        <style>
        .pagination-bar {
            display:flex; align-items:center; justify-content:space-between;
            flex-wrap:wrap; gap:14px;
            margin-top:18px; padding-top:14px;
            border-top:1px solid #e2e8f0;
        }
        .pagination-info { color:#475569; font-size:13px; }
        .pagination-pagesize { display:flex; align-items:center; gap:8px; margin:0; }
        .pagination-pagesize label { font-size:12px; color:#64748b; text-transform:uppercase; letter-spacing:0.3px; font-weight:600; margin:0; }
        .pagination-pagesize select {
            padding:6px 10px; border:1px solid #cbd5e1; border-radius:6px;
            background:#fff; font-size:13px; cursor:pointer;
        }
        .pagination-nav { display:flex; gap:4px; flex-wrap:wrap; }
        .pg-btn {
            display:inline-flex; align-items:center; justify-content:center;
            min-width:34px; height:34px; padding:0 10px;
            border:1px solid #cbd5e1; border-radius:6px;
            background:#fff; color:#334155;
            font-size:13px; font-weight:600;
            text-decoration:none; cursor:pointer;
            transition:background 0.12s, border-color 0.12s;
        }
        .pg-btn:hover:not(.disabled):not(.active) { background:#f1f5f9; border-color:#94a3b8; }
        .pg-btn.active { background:#4A1942; color:#fff; border-color:#4A1942; cursor:default; }
        .pg-btn.disabled { color:#cbd5e1; pointer-events:none; background:#f8fafc; }
        .pg-ellipsis { display:inline-flex; align-items:center; padding:0 6px; color:#94a3b8; }
        .badge-creos {
            display:inline-block;
            margin-left:4px;
            padding:1px 6px;
            background:#e0f2fe; color:#0369a1;
            border-radius:4px;
            font-size:10px; font-weight:700;
            letter-spacing:0.3px;
        }
        @media (max-width: 640px) {
            .pagination-bar { justify-content:center; text-align:center; }
            .pagination-info { width:100%; }
        }
        </style>
        <?php endif; ?>
    </div>
</div>

<!-- ==================== MODAL: CREAR/EDITAR PERSONA ==================== -->
<div class="modal-overlay" id="modal_persona">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 id="modal_persona_titulo">Nueva Persona</h3>
            <button class="modal-close" onclick="cerrarModalPersona()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form_persona">
                <input type="hidden" name="_csrf_token" value="<?= Session::getCsrfToken() ?>">
                <input type="hidden" name="id" id="persona_edit_id" value="">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Tipo Documento *</label>
                            <select name="tipo_documento" id="pe_tipo_documento" class="form-control" required>
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
                            <input type="text" name="documento" id="pe_documento" class="form-control" required maxlength="20">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Primer Nombre *</label>
                            <input type="text" name="primer_nombre" id="pe_primer_nombre" class="form-control" required maxlength="50">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Segundo Nombre</label>
                            <input type="text" name="segundo_nombre" id="pe_segundo_nombre" class="form-control" maxlength="50">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Primer Apellido *</label>
                            <input type="text" name="primer_apellido" id="pe_primer_apellido" class="form-control" required maxlength="50">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Segundo Apellido</label>
                            <input type="text" name="segundo_apellido" id="pe_segundo_apellido" class="form-control" maxlength="50">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-4">
                        <div class="form-group">
                            <label>Telefono</label>
                            <input type="text" name="telefono" id="pe_telefono" class="form-control" maxlength="20">
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Sede *</label>
                            <select name="id_sede" id="pe_id_sede" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($sedes as $sede): ?>
                                <option value="<?= $sede['id'] ?>"><?= htmlspecialchars($sede['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="form-group">
                            <label>Estado</label>
                            <select name="activo" id="pe_activo" class="form-control">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
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
var esEdicion = false;

function abrirModalPersona() {
    esEdicion = false;
    document.getElementById('modal_persona_titulo').textContent = 'Nueva Persona';
    document.getElementById('form_persona').reset();
    document.getElementById('persona_edit_id').value = '';
    document.getElementById('pe_documento').readOnly = false;
    document.getElementById('modal_persona').classList.add('show');
    document.getElementById('pe_documento').focus();
}

function editarPersona(p) {
    esEdicion = true;
    document.getElementById('modal_persona_titulo').textContent = 'Editar Persona';
    document.getElementById('persona_edit_id').value = p.id;
    document.getElementById('pe_tipo_documento').value = p.tipo_documento;
    document.getElementById('pe_documento').value = p.documento;
    document.getElementById('pe_primer_nombre').value = p.primer_nombre || '';
    document.getElementById('pe_segundo_nombre').value = p.segundo_nombre || '';
    document.getElementById('pe_primer_apellido').value = p.primer_apellido || '';
    document.getElementById('pe_segundo_apellido').value = p.segundo_apellido || '';
    document.getElementById('pe_telefono').value = p.telefono || '';
    document.getElementById('pe_id_sede').value = p.id_sede;
    document.getElementById('pe_activo').value = p.activo;
    document.getElementById('modal_persona').classList.add('show');
}

function cerrarModalPersona() {
    document.getElementById('modal_persona').classList.remove('show');
}

function togglePersona(id, nombre, activar) {
    var titulo      = activar ? '¿Habilitar a ' + nombre + '?' : '¿Inhabilitar a ' + nombre + '?';
    var btnTexto    = activar ? 'Habilitar'    : 'Inhabilitar';
    var btnColor    = activar ? '#16a34a'       : '#e74c3c';
    var endpoint    = activar ? '/personas/habilitar' : '/personas/eliminar';
    var successMsg  = activar ? 'Persona habilitada'  : 'Persona inhabilitada';

    swalEliminar(titulo, function() {
            var formData = new FormData();
            formData.append('id', id);
            formData.append('_csrf_token', CSRF_TOKEN);
            ajaxPost(BASE_URL + endpoint, formData, function(data) {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: successMsg, showConfirmButton: false, timer: 1800, timerProgressBar: true }).then(function() {
                        location.reload();
                    });
                } else {
                    swalError(data.message || 'Error al ' + btnTexto.toLowerCase());
                }
            });
        }, btnTexto, btnColor
    );
}

var personaValidator = new FormValidator('form_persona', {
    'documento': [V.required('El documento es requerido'), V.numeric('Solo numeros'), V.maxLength(20)],
    'primer_nombre': [V.required('El primer nombre es requerido'), V.maxLength(100)],
    'primer_apellido': [V.required('El primer apellido es requerido'), V.maxLength(100)],
    'telefono': [V.numeric('Solo numeros')],
    'id_sede': [V.required('Debe seleccionar una sede')]
});

function guardarPersona() {
    if (!personaValidator.validateAll()) return;

    var form = document.getElementById('form_persona');
    var formData = new FormData(form);
    var personaId = document.getElementById('persona_edit_id').value;
    var url = personaId
        ? BASE_URL + '/personas/actualizar'
        : BASE_URL + '/personas/crear';

    ajaxPost(url, formData, function(data) {
        if (data.success) {
            cerrarModalPersona();
            Toast.fire({ icon: 'success', title: personaId ? 'Persona actualizada' : 'Persona creada' }).then(function() {
                location.reload();
            });
        } else {
            if (data.errors && Object.keys(data.errors).length > 0) {
                personaValidator.showServerErrors(data.errors);
            } else {
                swalError(data.message || 'Error al guardar la persona');
            }
        }
    });
}

// Reset validator when opening modal
var _origAbrirPersona = abrirModalPersona;
abrirModalPersona = function() { _origAbrirPersona(); personaValidator.reset(); };
</script>
