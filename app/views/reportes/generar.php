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

            <div class="mt-2" style="background:#f6f4f8;border-left:3px solid #4A1942;border-radius:6px;padding:12px 14px">
                <label style="display:flex;align-items:flex-start;gap:10px;margin:0;cursor:pointer">
                    <input type="checkbox" name="incluir_hojas" value="1" style="margin-top:3px;width:18px;height:18px;cursor:pointer">
                    <span>
                        <strong>Incluir hojas individuales (remisiones para imprimir)</strong>
                        <br>
                        <span style="font-size:12px;color:#64748b">
                            Al final del PDF se anexan las solicitudes aprobadas en formato carta, dos por hoja con linea de corte.
                        </span>
                    </span>
                </label>
            </div>

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
            </style>
        </form>

        <div id="reporte_msg" class="mt-2"></div>
    </div>
</div>
