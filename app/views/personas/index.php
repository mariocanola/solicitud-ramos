<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flashTipo ?? 'info' ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<!-- Filtros -->
<div class="card">
    <div class="card-header">
        <span>Filtros de Busqueda</span>
        <?php
            $hayFiltros = !empty($filtros['busqueda']) || !empty($filtros['id_sede']);
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
                <div class="col-3" style="display:flex;align-items:flex-end;gap:8px;padding-bottom:16px">
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
        <button class="btn btn-success btn-sm" onclick="abrirModalPersona()">+ Nueva Persona</button>
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
                $contador = ($paginaActual - 1) * 15 + 1;
                foreach ($personas as $p):
                ?>
                    <tr>
                        <td><?= $contador ?></td>
                        <td><?= htmlspecialchars($p['documento']) ?></td>
                        <td><strong><?= htmlspecialchars(Persona::getNombreCompleto($p)) ?></strong></td>
                        <td><?= htmlspecialchars($p['telefono'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($p['sede_nombre']) ?></td>
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
                            <button class="btn btn-danger btn-sm"
                                    onclick="eliminarPersona(<?= $p['id'] ?>, '<?= htmlspecialchars(addslashes(Persona::getNombreCompleto($p)), ENT_QUOTES) ?>')">
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
                <a href="<?= BASE_URL ?>/personas?<?= http_build_query($params) ?>">&laquo; Anterior</a>
            <?php endif; ?>
            <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                <?php $params = $_GET; $params['pagina'] = $i; $qs = http_build_query($params); ?>
                <?php if ($i == $paginaActual): ?>
                    <span class="active"><?= $i ?></span>
                <?php else: ?>
                    <a href="<?= BASE_URL ?>/personas?<?= $qs ?>"><?= $i ?></a>
                <?php endif; ?>
            <?php endfor; ?>
            <?php if ($paginaActual < $totalPaginas): ?>
                <?php $params = $_GET; $params['pagina'] = $paginaActual + 1; ?>
                <a href="<?= BASE_URL ?>/personas?<?= http_build_query($params) ?>">Siguiente &raquo;</a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
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
    document.getElementById('pe_documento').readOnly = true;
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

function eliminarPersona(id, nombre) {
    swalConfirm(
        '¿Eliminar persona?',
        'Esta seguro de eliminar a "' + nombre + '"? Esta accion no se puede deshacer.',
        function() {
            var formData = new FormData();
            formData.append('id', id);
            formData.append('_csrf_token', CSRF_TOKEN);
            ajaxPost(BASE_URL + '/personas/eliminar', formData, function(data) {
                if (data.success) {
                    Toast.fire({ icon: 'success', title: 'Persona eliminada' }).then(function() {
                        location.reload();
                    });
                } else {
                    swalError(data.message || 'Error al eliminar');
                }
            });
        }
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
