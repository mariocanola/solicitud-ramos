<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flashTipo ?? 'info' ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <span>Cargar maestro de personas</span>
        <a href="<?= BASE_URL ?>/personas" class="btn-link-back">&larr; Volver</a>
    </div>
    <div class="card-body">

        <div class="format-pills">
            <span class="format-pill" title="Personal directo">
                <span class="pill-dot pill-dot-tandil"></span> Tandil
            </span>
            <span class="format-pill" title="Personal temporal">
                <span class="pill-dot pill-dot-creos"></span> CREOS
            </span>
            <span class="format-help">El sistema detecta el tipo de archivo automaticamente.</span>
        </div>

        <form method="POST" action="<?= BASE_URL ?>/personas/importar/subir" enctype="multipart/form-data" id="form_maestro">
            <?= $csrfField ?>

            <label for="archivo" class="dropzone" id="dropzone">
                <input type="file" id="archivo" name="archivo" accept=".xlsx,.xls,.csv" required hidden>
                <svg width="42" height="42" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <span class="dropzone-title" id="dz_title">Selecciona o arrastra el archivo</span>
                <span class="dropzone-hint" id="dz_hint">.xlsx, .xls o .csv &middot; hasta 10 MB</span>
            </label>

            <div class="action-bar">
                <a href="<?= BASE_URL ?>/personas/plantilla" class="btn-ghost">Descargar plantilla</a>
                <button type="submit" class="btn-primary-purple" id="btn_procesar" disabled>
                    Procesar archivo
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.btn-link-back {
    color:#64748b; font-size:13px; font-weight:500;
    text-decoration:none;
}
.btn-link-back:hover { color:#4A1942; }

.format-pills {
    display:flex; align-items:center; flex-wrap:wrap; gap:8px;
    margin-bottom:18px;
}
.format-pill {
    display:inline-flex; align-items:center; gap:6px;
    padding:4px 10px;
    background:#f8fafc;
    border:1px solid #e2e8f0;
    border-radius:999px;
    font-size:12px; font-weight:600; color:#475569;
}
.pill-dot {
    width:8px; height:8px; border-radius:50%;
    display:inline-block;
}
.pill-dot-tandil { background:#4A1942; }
.pill-dot-creos  { background:#0284c7; }
.format-help { font-size:12px; color:#94a3b8; margin-left:6px; }

.dropzone {
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    gap:8px;
    padding:36px 24px;
    border:2px dashed #cbd5e1;
    border-radius:10px;
    background:#fafbfc;
    color:#64748b;
    cursor:pointer;
    transition: border-color 0.15s, background 0.15s, color 0.15s;
}
.dropzone:hover { border-color:#4A1942; background:#faf8fb; color:#4A1942; }
.dropzone.is-dragover { border-color:#4A1942; background:#f3e9f0; color:#4A1942; }
.dropzone.has-file { border-color:#4A1942; border-style:solid; background:#fff; color:#1e293b; }
.dropzone-title { font-size:14px; font-weight:600; }
.dropzone-hint { font-size:12px; color:#94a3b8; }
.dropzone.has-file .dropzone-hint { color:#64748b; }

.action-bar {
    display:flex; justify-content:space-between; align-items:center;
    margin-top:18px; padding-top:16px;
    border-top:1px solid #e2e8f0;
}
.btn-ghost {
    color:#475569; font-size:13px; font-weight:600;
    text-decoration:none;
    padding:8px 14px; border-radius:6px;
    transition: background 0.15s;
}
.btn-ghost:hover { background:#f1f5f9; color:#1e293b; }

.btn-primary-purple {
    display:inline-flex; align-items:center; gap:8px;
    background: linear-gradient(135deg, #4A1942 0%, #5C2A47 100%);
    color:#fff;
    border:none;
    padding:11px 24px;
    font-size:14px; font-weight:600;
    border-radius:8px;
    cursor:pointer;
    box-shadow:0 2px 6px rgba(74,25,66,0.25);
    transition: transform 0.12s, box-shadow 0.12s, background 0.12s, opacity 0.12s;
}
.btn-primary-purple:hover:not(:disabled) {
    background: linear-gradient(135deg, #5C2A47, #7A4866);
    box-shadow:0 4px 12px rgba(74,25,66,0.35);
    transform: translateY(-1px);
}
.btn-primary-purple:disabled { opacity:0.4; cursor:not-allowed; box-shadow:none; }
</style>

<script>
(function () {
    var input = document.getElementById('archivo');
    var dz    = document.getElementById('dropzone');
    var title = document.getElementById('dz_title');
    var hint  = document.getElementById('dz_hint');
    var btn   = document.getElementById('btn_procesar');

    function actualizar(file) {
        if (!file) { dz.classList.remove('has-file'); btn.disabled = true; return; }
        var kb = Math.round(file.size / 1024);
        var size = kb > 1024 ? (kb / 1024).toFixed(1) + ' MB' : kb + ' KB';
        title.textContent = file.name;
        hint.textContent  = size;
        dz.classList.add('has-file');
        btn.disabled = false;
    }

    input.addEventListener('change', function () { actualizar(input.files[0]); });

    ['dragenter','dragover'].forEach(function (ev) {
        dz.addEventListener(ev, function (e) {
            e.preventDefault(); e.stopPropagation();
            dz.classList.add('is-dragover');
        });
    });
    ['dragleave','drop'].forEach(function (ev) {
        dz.addEventListener(ev, function (e) {
            e.preventDefault(); e.stopPropagation();
            dz.classList.remove('is-dragover');
        });
    });
    dz.addEventListener('drop', function (e) {
        var f = e.dataTransfer.files && e.dataTransfer.files[0];
        if (f) { input.files = e.dataTransfer.files; actualizar(f); }
    });
})();
</script>
