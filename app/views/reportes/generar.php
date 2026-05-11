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
