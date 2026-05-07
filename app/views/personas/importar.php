<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flashTipo ?? 'info' ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span>Cargar maestro de personas</span>
        <a href="<?= BASE_URL ?>/personas" class="btn btn-outline btn-sm">Volver</a>
    </div>
    <div class="card-body">
        <p style="margin-top:0;color:#555">
            Sube un archivo Excel (.xlsx) o CSV con los datos del personal.
            Las personas que ya esten en el sistema con datos iguales se omiten;
            las que tengan datos diferentes se actualizan; las inactivas se reactivan.
        </p>

        <div style="margin:18px 0;padding:14px 16px;background:#f6f4f8;border-left:3px solid #4A1942;border-radius:6px">
            <strong>Formatos de archivo aceptados:</strong>
            <ul style="margin:6px 0 0 18px;padding:0">
                <li><strong>Plantilla del sistema</strong>: columnas separadas (<code>primer_nombre, segundo_nombre, primer_apellido, segundo_apellido</code>).</li>
                <li><strong>Maestro empresarial</strong>: una sola columna <code>nombre</code> con el nombre completo - se parsea automaticamente.</li>
            </ul>
            <strong style="display:block;margin-top:10px">Columnas reconocidas:</strong>
            <code>tipo_documento, documento (o numero_documento), nombre (o primer/segundo_nombre y primer/segundo_apellido), telefono (o telefono_movil), sede</code>.<br>
            <strong>Sedes:</strong> <code>TN</code> = Tandil, <code>PM</code> = Primavera (acepta tambien <code>TN-PM</code> o <code>TN-TN</code>).<br>
            <strong>Tipos de documento:</strong> CC, CE, TI, PA, NIT, PT.
        </div>

        <form method="POST" action="<?= BASE_URL ?>/personas/importar/subir" enctype="multipart/form-data">
            <?= $csrfField ?>
            <div class="form-group">
                <label for="archivo">Archivo del maestro</label>
                <input type="file" id="archivo" name="archivo" class="form-control"
                       accept=".xlsx,.xls,.csv" required>
                <small style="color:#777">Tamano maximo 10 MB.</small>
            </div>
            <div style="display:flex;gap:10px;margin-top:18px">
                <a href="<?= BASE_URL ?>/personas/plantilla" class="btn btn-outline">Descargar plantilla</a>
                <button type="submit" class="btn btn-primary">Procesar archivo</button>
            </div>
        </form>
    </div>
</div>
