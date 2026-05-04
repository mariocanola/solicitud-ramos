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
            </div>
        </form>

        <div id="reporte_msg" class="mt-2"></div>
    </div>
</div>
