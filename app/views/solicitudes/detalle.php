<!-- Detalle de Solicitud -->

<div class="mb-2">
    <a href="<?= BASE_URL ?>/solicitudes" class="btn btn-outline">&larr; Volver al listado</a>
</div>

<!-- Encabezado -->
<div class="card">
    <div class="card-body" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:12px">
        <div>
            <h2 style="font-size:22px;color:var(--primary);margin-bottom:4px">Solicitud #<?= $solicitud['id'] ?></h2>
            <span style="font-size:13px;color:#7F8C8D">Creada el <?= date('d/m/Y', strtotime($solicitud['created_at'])) ?> a las <?= date('h:i A', strtotime($solicitud['created_at'])) ?></span>
        </div>
        <div style="display:flex;align-items:center;gap:12px">
            <span class="badge" style="background:<?= htmlspecialchars($solicitud['estado_color']) ?>;font-size:14px;padding:6px 16px">
                <?= htmlspecialchars($solicitud['estado_nombre']) ?>
            </span>
            <button class="btn btn-danger btn-sm" onclick="eliminarSolicitud(<?= $solicitud['id'] ?>)">
                Eliminar
            </button>
        </div>
    </div>
</div>

<div class="row">
    <!-- Columna izquierda: Solicitante -->
    <div class="col-6">
        <div class="card">
            <div class="card-header">
                <span>Datos del Solicitante</span>
            </div>
            <div class="card-body">
                <div class="detalle-campo">
                    <span class="detalle-label">Nombre completo</span>
                    <span class="detalle-valor"><?= htmlspecialchars($solicitud['nombre_completo']) ?></span>
                </div>
                <div class="detalle-campo">
                    <span class="detalle-label">Tipo y No. Documento</span>
                    <span class="detalle-valor"><?= htmlspecialchars($solicitud['tipo_documento']) ?> <?= htmlspecialchars($solicitud['documento']) ?></span>
                </div>
                <div class="detalle-campo">
                    <span class="detalle-label">Telefono</span>
                    <span class="detalle-valor"><?= htmlspecialchars($solicitud['telefono'] ?? 'No registrado') ?></span>
                </div>
                <div class="detalle-campo">
                    <span class="detalle-label">Sede</span>
                    <span class="detalle-valor"><?= htmlspecialchars($solicitud['sede_nombre']) ?></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Columna derecha: Solicitud -->
    <div class="col-6">
        <div class="card">
            <div class="card-header">
                <span>Datos de la Solicitud</span>
            </div>
            <div class="card-body">
                <div class="detalle-campo">
                    <span class="detalle-label">Fecha de solicitud</span>
                    <span class="detalle-valor"><?= date('d/m/Y', strtotime($solicitud['fecha_solicitud'])) ?></span>
                </div>
                <div class="detalle-campo">
                    <span class="detalle-label">Destinatario del ramo</span>
                    <span class="detalle-valor" style="font-weight:600"><?= htmlspecialchars($solicitud['nombre_destinatario']) ?></span>
                </div>
                <div class="detalle-campo">
                    <span class="detalle-label">Motivo</span>
                    <span class="detalle-valor">
                        <?= htmlspecialchars($solicitud['motivo_nombre']) ?>
                        <?php if ($solicitud['motivo_otro']): ?>
                            <span style="color:#7F8C8D"> - <?= htmlspecialchars($solicitud['motivo_otro']) ?></span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="detalle-campo">
                    <span class="detalle-label">Observaciones</span>
                    <span class="detalle-valor"><?= htmlspecialchars($solicitud['observaciones'] ?? 'Sin observaciones') ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Cambiar estado -->
<div class="card">
    <div class="card-header">
        <span>Gestion del Estado</span>
    </div>
    <div class="card-body">
        <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
            <label style="font-weight:600;font-size:14px;color:#555;margin:0">Cambiar estado a:</label>
            <select id="nuevo_estado" class="form-control" style="width:200px">
                <?php foreach ($estados as $estado): ?>
                <option value="<?= $estado['id'] ?>" <?= $estado['id'] == $solicitud['id_estado'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($estado['nombre']) ?>
                </option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary" onclick="cambiarEstado(<?= $solicitud['id'] ?>)">
                Actualizar Estado
            </button>
        </div>
        <div id="estado_msg" class="mt-1"></div>
    </div>
</div>

<style>
.detalle-campo {
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}
.detalle-campo:last-child {
    border-bottom: none;
}
.detalle-label {
    display: block;
    font-size: 11px;
    font-weight: 600;
    color: #95A5A6;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: 3px;
}
.detalle-valor {
    display: block;
    font-size: 15px;
    color: #2C3E50;
}
</style>

<script>
var BASE_URL = '<?= BASE_URL ?>';
var CSRF_TOKEN = '<?= Session::getCsrfToken() ?>';

function eliminarSolicitud(id) {
    swalEliminar(
        '¿Eliminar solicitud?',
        function() {
            var formData = new FormData();
            formData.append('id', id);
            formData.append('_csrf_token', CSRF_TOKEN);
            ajaxPost(BASE_URL + '/solicitudes/eliminar', formData, function(data) {
                if (data.success) {
                    Swal.fire({ icon: 'success', title: 'Solicitud eliminada', showConfirmButton: false, timer: 1800, timerProgressBar: true }).then(function() {
                        window.location.href = BASE_URL + '/solicitudes';
                    });
                } else {
                    swalError(data.message || 'Error al eliminar');
                }
            });
        }
    );
}

function cambiarEstado(id) {
    var nuevoEstado = document.getElementById('nuevo_estado').value;
    var formData = new FormData();
    formData.append('id', id);
    formData.append('id_estado', nuevoEstado);
    formData.append('_csrf_token', CSRF_TOKEN);

    fetch(BASE_URL + '/solicitudes/cambiar-estado', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        var msgEl = document.getElementById('estado_msg');
        if (data.success) {
            showAlert(msgEl, 'success', data.message);
            setTimeout(function() { location.reload(); }, 1000);
        } else {
            showAlert(msgEl, 'danger', data.message || 'Error al cambiar estado');
        }
        updateCsrfToken(data);
    });
}
</script>
