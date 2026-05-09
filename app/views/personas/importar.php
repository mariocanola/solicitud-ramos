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
            <strong>El sistema detecta automaticamente el tipo de archivo:</strong>
            <ul style="margin:8px 0 0 18px;padding:0">
                <li><strong>Maestro Tandil</strong> (rptMaestroEmpSinSal*.xlsx): personal directo de Tandil. Marca a las personas como empresa <code>TANDIL</code>.</li>
                <li><strong>Maestro CREOS</strong> (ACTIVOS CREOS*.xls): personal temporal contratado por CREOS que trabaja en Tandil. Marca como empresa <code>CREOS</code> y respeta el campo Estado del archivo.</li>
            </ul>
            <strong style="display:block;margin-top:10px">Sedes reconocidas en cualquiera de los formatos:</strong>
            <code>TN</code>, <code>TANDIL</code>, <code>FLORES EL TANDIL</code>, <code>TN-TN</code> &rarr; Tandil &middot;
            <code>PM</code>, <code>PRIMAVERA</code>, <code>TN-PM</code> &rarr; Primavera.<br>
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
