<!-- Reportes / Generación de PDF -->

<div class="card">
    <div class="card-header">Generar Reporte PDF Consolidado</div>
    <div class="card-body">
        <form id="form_reporte" method="POST" action="<?= BASE_URL ?>/reportes/generar-pdf">
            <?= $csrfField ?>

            <div class="row">
                <div class="col-3">
                    <div class="form-group">
                        <label>Fecha Desde *</label>
                        <input type="date" name="fecha_desde" class="form-control" required
                               value="<?= date('Y-m-01') ?>">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label>Fecha Hasta *</label>
                        <input type="date" name="fecha_hasta" class="form-control" required
                               value="<?= date('Y-m-d') ?>">
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

            <div class="mt-2 d-flex gap-1">
                <button type="submit" class="btn btn-dark btn-lg">
                    Descargar PDF
                </button>
                <button type="button" class="btn btn-primary btn-lg" onclick="enviarPorCorreo()">
                    Enviar por Correo
                </button>
            </div>
        </form>

        <div id="reporte_msg" class="mt-2"></div>
    </div>
</div>

<script>
var BASE_URL = '<?= BASE_URL ?>';
var CSRF_TOKEN = '<?= Session::getCsrfToken() ?>';

function enviarPorCorreo() {
    swalConfirm('Enviar reporte', '¿Enviar el reporte por correo electronico?', function() {
        var form = document.getElementById('form_reporte');
        var formData = new FormData(form);

        Swal.fire({
            title: 'Enviando correo...',
            text: 'Por favor espere',
            allowOutsideClick: false,
            didOpen: function() { Swal.showLoading(); }
        });

        fetch(BASE_URL + '/reportes/enviar-correo', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                swalSuccess(data.message);
            } else {
                swalError(data.message);
            }
        })
        .catch(function() {
            swalError('Error de conexion');
        });
    });
}
</script>
