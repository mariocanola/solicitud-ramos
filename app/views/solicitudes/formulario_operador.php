<?php
// Vista optimizada para operadores - Fase 3
// Panel táctil para creación rápida de solicitudes
?>

<?php if (!empty($flash)): ?>
<div class="alert alert-<?= $flashTipo ?? 'info' ?>"><?= htmlspecialchars($flash) ?></div>
<?php endif; ?>

<!-- Kiosko de Solicitud de Ramos -->
<div class="operator-panel">
    <!-- Header -->
    <div class="operator-header">
        <div class="operator-info">
            <h1 class="operator-title">Solicitud de Ramos Florales</h1>
            <div class="operator-session">
                Bienvenido. Identifíquese para registrar su solicitud.
            </div>
        </div>
    </div>

    <!-- Mensaje de Inicio -->
    <div id="welcome_message" class="welcome-message">
        <div class="welcome-icon">i</div>
        <h2>¿Cómo solicitar un ramo?</h2>
        <p>Siga estos tres pasos. Tomará menos de un minuto.</p>
        <div class="welcome-steps">
            <div class="step">
                <span class="step-number">1</span>
                <span class="step-text">Ingrese su cédula</span>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <span class="step-text">Verifique sus datos</span>
            </div>
            <div class="step">
                <span class="step-number">3</span>
                <span class="step-text">Complete y envíe la solicitud</span>
            </div>
        </div>
    </div>

    <!-- Scanner Section -->
    <div class="scanner-section" id="scanner_section">
        <div class="scanner-header">
            <h2>
                <span class="scanner-icon">1</span>
                Ingrese su número de cédula
            </h2>
            <div class="scanner-indicator" id="scanner_indicator">
                <span class="indicator-dot"></span>
                <span class="indicator-text">Listo</span>
            </div>
        </div>
        
        <div class="scanner-input-area">
            <input type="text" id="scanner_input" class="scanner-input-touch"
                   placeholder="Su número de cédula"
                   autocomplete="off" readonly inputmode="none">
            <button type="button" class="btn btn-scanner" onclick="buscarManual()">
                Continuar
            </button>
        </div>

        <!-- Teclado numérico táctil -->
        <div class="numpad" id="numpad" aria-label="Teclado numérico">
            <button type="button" class="numpad-key" data-key="1">1</button>
            <button type="button" class="numpad-key" data-key="2">2</button>
            <button type="button" class="numpad-key" data-key="3">3</button>
            <button type="button" class="numpad-key" data-key="4">4</button>
            <button type="button" class="numpad-key" data-key="5">5</button>
            <button type="button" class="numpad-key" data-key="6">6</button>
            <button type="button" class="numpad-key" data-key="7">7</button>
            <button type="button" class="numpad-key" data-key="8">8</button>
            <button type="button" class="numpad-key" data-key="9">9</button>
            <button type="button" class="numpad-key numpad-action" data-action="clear">Limpiar</button>
            <button type="button" class="numpad-key" data-key="0">0</button>
            <button type="button" class="numpad-key numpad-back" data-action="back" aria-label="Borrar">&larr;</button>
        </div>

        <div id="scanner_status" class="scanner-status"></div>
    </div>

    <!-- Persona Info -->
    <div id="persona_info" class="persona-card hidden">
        <div class="persona-header">
            <h3 class="persona-name" id="persona_nombre"></h3>
            <span class="persona-badge">Identificado</span>
        </div>
        <div class="persona-details">
            <div class="persona-detail">
                <span class="detail-label">Documento:</span>
                <span class="detail-value" id="persona_doc"></span>
            </div>
            <div class="persona-detail">
                <span class="detail-label">Sede:</span>
                <span class="detail-value" id="persona_sede"></span>
            </div>
            <div class="persona-detail">
                <span class="detail-label">Teléfono:</span>
                <span class="detail-value" id="persona_tel"></span>
            </div>
        </div>
    </div>

    <!-- Formulario de Solicitud de Ramo -->
    <div id="form_section" class="card hidden">
        <div class="card-header">
            <h3><span class="step-badge-inline">2</span> Datos de su solicitud</h3>
        </div>
        <div class="card-body">
            <form id="form_solicitud" method="POST" action="<?= BASE_URL ?>/solicitudes/nueva"
                  onsubmit="event.preventDefault(); enviarSolicitud(); return false;">
                <?= $csrfField ?>
                <!-- Datos automáticos: se completan desde la persona identificada -->
                <input type="hidden" name="persona_id"          id="persona_id"          value="">
                <input type="hidden" name="fecha_solicitud"     id="fecha_solicitud"     value="<?= $hoy ?>">
                <input type="hidden" name="id_sede"             id="id_sede"             value="">
                <input type="hidden" name="nombre_destinatario" id="nombre_destinatario" value="">

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label for="id_motivo">Motivo *</label>
                            <select name="id_motivo" id="id_motivo" class="form-control" required>
                                <option value="">-- Seleccione el motivo --</option>
                                <?php foreach ($motivos as $motivo): ?>
                                <option value="<?= $motivo['id'] ?>" data-requiere-detalle="<?= $motivo['requiere_detalle'] ?>">
                                    <?= htmlspecialchars($motivo['nombre']) ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group hidden" id="grupo_motivo_otro">
                            <label for="motivo_otro">Especifique el motivo *</label>
                            <input type="text" name="motivo_otro" id="motivo_otro" class="form-control" maxlength="200">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="observaciones">Observaciones <span style="color:var(--secondary-500);font-weight:400;text-transform:none;letter-spacing:0;font-size:12px">(opcional)</span></label>
                    <textarea name="observaciones" id="observaciones" class="form-control" maxlength="500"
                              placeholder="Si desea agregar algún detalle adicional, escríbalo aquí."></textarea>
                </div>

                <div class="form-actions">
                    <div class="form-actions-left">
                        <button type="button" class="btn btn-outline" onclick="limpiarFormulario()">
                            Cancelar
                        </button>
                    </div>
                    <div class="form-actions-right">
                        <button type="reset" class="btn btn-outline">
                            Limpiar
                        </button>
                        <button type="button" class="btn btn-primary btn-lg" id="btn_guardar" disabled
                                onclick="enviarSolicitud()" title="Primero busque una persona por documento">
                            Guardar Solicitud
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Pantalla de Éxito -->
    <div id="success_message" class="success-screen hidden">
        <div class="success-icon">&check;</div>
        <h2>¡Solicitud registrada con éxito!</h2>
        <p id="success_subtitle">Su solicitud ha sido enviada correctamente.</p>
        <button type="button" class="btn btn-primary btn-lg" onclick="reiniciarKiosco()">
            Registrar otra solicitud
        </button>
    </div>
</div>

<!-- Modal: Crear Persona -->
<div class="modal-overlay" id="modal_persona">
    <div class="modal">
        <div class="modal-header">
            <h3>Registrar Nueva Persona</h3>
            <button class="modal-close" onclick="cerrarModalPersona()">&times;</button>
        </div>
        <div class="modal-body">
            <form id="form_persona">
                <input type="hidden" name="_csrf_token" value="<?= Session::getCsrfToken() ?>">
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Tipo Documento *</label>
                            <select name="tipo_documento" id="p_tipo_documento" class="form-control" required>
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
                            <input type="text" name="documento" id="p_documento" class="form-control" maxlength="20">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Primer Nombre *</label>
                            <input type="text" name="primer_nombre" id="p_primer_nombre" class="form-control" required maxlength="50">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Segundo Nombre</label>
                            <input type="text" name="segundo_nombre" id="p_segundo_nombre" class="form-control" maxlength="50">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Primer Apellido *</label>
                            <input type="text" name="primer_apellido" id="p_primer_apellido" class="form-control" required maxlength="50">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Segundo Apellido</label>
                            <input type="text" name="segundo_apellido" id="p_segundo_apellido" class="form-control" maxlength="50">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Telefono</label>
                            <input type="text" name="telefono" id="p_telefono" class="form-control" maxlength="20">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Sede *</label>
                            <select name="id_sede" id="p_id_sede" class="form-control" required>
                                <option value="">-- Seleccione --</option>
                                <?php foreach ($sedes as $sede): ?>
                                <option value="<?= $sede['id'] ?>"><?= htmlspecialchars($sede['nombre']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <!-- Campo estado eliminado para operador -->
                    <input type="hidden" name="activo" value="1">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline mr-1" onclick="cerrarModalPersona()">Cancelar</button>
            <button class="btn btn-success" onclick="guardarPersona()">Guardar Persona</button>
        </div>
    </div>
</div>

<!-- Estilos específicos del panel operador -->
<style>
/* ===== MODO KIOSCO: ocultar sidebar y expandir contenido ===== */
body .sidebar { display: none !important; }
body .app-wrapper { display: block; }
body .main-content { margin-left: 0 !important; width: 100% !important; max-width: 100% !important; }

/* ===== PALETA EMPRESARIAL MORADA ===== */
:root {
    --primary-50:  #FBF7FA;
    --primary-100: #F2E9EE;
    --primary-200: #E1CDDA;
    --primary-300: #C8A3BB;
    --primary-400: #A2748F;
    --primary-500: #7A4866;
    --primary-600: #5C2A47;
    --primary-700: #4A1942;
    --primary-800: #36102F;
    --primary-900: #220A1E;
    
    --secondary-50: #f8fafc;
    --secondary-100: #f1f5f9;
    --secondary-200: #e2e8f0;
    --secondary-300: #cbd5e1;
    --secondary-400: #94a3b8;
    --secondary-500: #64748b;
    --secondary-600: #475569;
    --secondary-700: #334155;
    --secondary-800: #1e293b;
    --secondary-900: #0f172a;
    
    --success-50: #f0fdf4;
    --success-100: #dcfce7;
    --success-200: #bbf7d0;
    --success-300: #86efac;
    --success-400: #4ade80;
    --success-500: #22c55e;
    --success-600: #16a34a;
    --success-700: #15803d;
    --success-800: #166534;
    --success-900: #14532d;
    
    --warning-50: #fffbeb;
    --warning-100: #fef3c7;
    --warning-200: #fde68a;
    --warning-300: #fcd34d;
    --warning-400: #fbbf24;
    --warning-500: #f59e0b;
    --warning-600: #d97706;
    --warning-700: #b45309;
    --warning-800: #92400e;
    --warning-900: #78350f;
    
    --danger-50: #fef2f2;
    --danger-100: #fee2e2;
    --danger-200: #fecaca;
    --danger-300: #fca5a5;
    --danger-400: #f87171;
    --danger-500: #ef4444;
    --danger-600: #dc2626;
    --danger-700: #b91c1c;
    --danger-800: #991b1b;
    --danger-900: #7f1d1d;
    
    --gradient-primary: linear-gradient(135deg, var(--primary-600), var(--primary-700));
    --gradient-success: linear-gradient(135deg, var(--success-500), var(--success-600));
    --gradient-warning: linear-gradient(135deg, var(--warning-500), var(--warning-600));
    --gradient-danger: linear-gradient(135deg, var(--danger-500), var(--danger-600));
    
    --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    --shadow-lg: 0 10px 15px -3px rgb(0 0 0 / 0.1), 0 4px 6px -4px rgb(0 0 0 / 0.1);
    --shadow-xl: 0 20px 25px -5px rgb(0 0 0 / 0.1), 0 8px 10px -6px rgb(0 0 0 / 0.1);
}

/* ===== ESTILOS PRINCIPALES ===== */
.operator-panel {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    background: var(--secondary-50);
    min-height: 100vh;
}

.operator-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: var(--gradient-primary);
    color: white;
    padding: 24px 32px;
    border-radius: 16px;
    margin-bottom: 32px;
    box-shadow: var(--shadow-xl);
    position: relative;
    overflow: hidden;
}

.operator-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 100%);
    pointer-events: none;
}

.operator-title {
    font-size: 32px;
    margin: 0;
    font-weight: 700;
    letter-spacing: -0.5px;
}

.operator-session {
    margin-top: 8px;
    font-size: 14px;
    opacity: 0.9;
    font-weight: 500;
}

.operator-name {
    font-weight: 600;
    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
}

.operator-role {
    background: rgba(255,255,255,0.25);
    padding: 4px 12px;
    border-radius: 20px;
    margin-left: 12px;
    font-size: 12px;
    font-weight: 600;
    backdrop-filter: blur(10px);
}

.operator-actions {
    display: flex;
    gap: 12px;
}

.operator-actions .btn {
    background: rgba(255,255,255,0.15);
    border: 1px solid rgba(255,255,255,0.25);
    color: white;
    padding: 12px 20px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 14px;
    backdrop-filter: blur(10px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.operator-actions .btn:hover {
    background: rgba(255,255,255,0.25);
    transform: translateY(-1px);
    box-shadow: var(--shadow-lg);
}

.scanner-section {
    background: white;
    border-radius: 16px;
    padding: 32px;
    margin-bottom: 32px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--secondary-200);
    position: relative;
}

.scanner-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
    border-radius: 16px 16px 0 0;
}

.scanner-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 28px;
}

.scanner-header h2 {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 28px;
    color: var(--secondary-800);
    margin: 0;
    font-weight: 700;
}

.scanner-icon {
    font-size: 32px;
    color: var(--primary-500);
    background: var(--primary-50);
    padding: 12px;
    border-radius: 12px;
}

.scanner-input-area {
    display: flex;
    gap: 20px;
    margin-bottom: 24px;
}

.scanner-input-touch {
    flex: 1;
    font-size: 28px;
    padding: 24px;
    border: 2px solid var(--secondary-200);
    border-radius: 16px;
    text-align: center;
    font-weight: 700;
    letter-spacing: 3px;
    text-transform: uppercase;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: var(--secondary-50);
    color: var(--secondary-700);
}

.scanner-input-touch::placeholder {
    color: var(--secondary-400);
    font-weight: 500;
}

.scanner-input-touch:focus {
    border-color: var(--primary-500);
    box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
    transform: scale(1.01);
    background: white;
    outline: none;
}

.btn-scanner {
    padding: 24px 48px;
    font-size: 18px;
    font-weight: 700;
    background: var(--gradient-success);
    border: none;
    border-radius: 16px;
    color: white;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    box-shadow: var(--shadow-md);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    min-width: 200px;
}

.btn-scanner:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-xl);
}

.btn-scanner:active {
    transform: translateY(0);
}

.scanner-status {
    min-height: 28px;
    font-size: 15px;
    font-weight: 600;
    padding: 12px 16px;
    border-radius: 8px;
    background: var(--secondary-50);
    border: 1px solid var(--secondary-200);
    color: var(--secondary-600);
}

.persona-card {
    background: linear-gradient(135deg, var(--success-50), white);
    border: 2px solid var(--success-200);
    border-radius: 16px;
    padding: 28px;
    margin-bottom: 32px;
    box-shadow: var(--shadow-md);
    animation: slideInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.persona-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-success);
    border-radius: 16px 16px 0 0;
}

.persona-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 24px;
}

.persona-name {
    font-size: 24px;
    color: var(--success-700);
    margin: 0;
    font-weight: 700;
}

.persona-badge {
    background: var(--success-500);
    color: white;
    padding: 8px 16px;
    border-radius: 24px;
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    box-shadow: var(--shadow-sm);
}

.persona-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 20px;
}

.persona-detail {
    display: flex;
    justify-content: space-between;
    padding: 16px 0;
    border-bottom: 1px solid var(--success-200);
    background: rgba(255,255,255,0.5);
    padding: 16px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.persona-detail:hover {
    background: rgba(255,255,255,0.8);
    transform: translateY(-1px);
    box-shadow: var(--shadow-sm);
}

.detail-label {
    font-weight: 600;
    color: var(--secondary-600);
    font-size: 14px;
}

.detail-value {
    font-weight: 600;
    color: var(--secondary-800);
    font-size: 15px;
}

.form-section {
    background: white;
    border-radius: 16px;
    padding: 32px;
    box-shadow: var(--shadow-md);
    border: 1px solid var(--secondary-200);
    animation: slideInUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    position: relative;
}

.form-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
    border-radius: 16px 16px 0 0;
}

.form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 32px;
}

.form-header h2 {
    display: flex;
    align-items: center;
    gap: 16px;
    font-size: 28px;
    color: var(--secondary-800);
    margin: 0;
    font-weight: 700;
}

.form-icon {
    font-size: 32px;
    color: var(--primary-500);
    background: var(--primary-50);
    padding: 12px;
    border-radius: 12px;
}

.form-grid {
    display: grid;
    gap: 24px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-group label {
    display: block;
    font-weight: 700;
    font-size: 14px;
    color: var(--secondary-700);
    margin-bottom: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-control {
    padding: 16px 20px;
    font-size: 16px;
    border: 2px solid var(--secondary-200);
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: white;
    color: var(--secondary-800);
    font-weight: 500;
}

.form-control::placeholder {
    color: var(--secondary-400);
    font-weight: 400;
}

.form-control:focus {
    border-color: var(--primary-500);
    box-shadow: 0 0 0 4px rgba(14, 165, 233, 0.1);
    outline: none;
    transform: translateY(-1px);
}

.form-control:hover:not(:focus) {
    border-color: var(--secondary-300);
}

.form-note {
    font-size: 13px;
    color: var(--secondary-500);
    margin-top: 8px;
    display: block;
    font-weight: 500;
}

.char-counter {
    text-align: right;
    font-size: 13px;
    color: var(--secondary-500);
    margin-top: 8px;
    font-weight: 500;
}

.form-actions {
    display: flex;
    gap: 16px;
    justify-content: flex-end;
    margin-top: 36px;
    padding-top: 24px;
    border-top: 2px solid var(--secondary-200);
}

.btn-lg {
    padding: 16px 32px;
    font-size: 16px;
    font-weight: 700;
    border-radius: 12px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.btn-lg::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
    transition: left 0.5s;
}

.btn-lg:hover:not(:disabled)::before {
    left: 100%;
}

.btn-lg:hover:not(:disabled) {
    transform: translateY(-2px);
    box-shadow: var(--shadow-xl);
}

.btn-lg:active:not(:disabled) {
    transform: translateY(0);
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

/* Botones específicos */
.btn-outline {
    background: white;
    border: 2px solid var(--secondary-300);
    color: var(--secondary-700);
}

.btn-outline:hover:not(:disabled) {
    background: var(--secondary-50);
    border-color: var(--secondary-400);
    color: var(--secondary-800);
}

.btn-success {
    background: var(--gradient-success);
    border: 2px solid var(--success-500);
    color: white;
}

.btn-success:hover:not(:disabled) {
    background: linear-gradient(135deg, var(--success-600), var(--success-700));
    border-color: var(--success-600);
}

.welcome-message {
    text-align: center;
    padding: 80px 60px;
    background: white;
    border-radius: 20px;
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--secondary-200);
    position: relative;
}

.welcome-message::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-primary);
    border-radius: 20px 20px 0 0;
}

.welcome-icon {
    font-size: 80px;
    color: var(--primary-500);
    margin-bottom: 24px;
    background: var(--primary-50);
    width: 160px;
    height: 160px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 24px;
    box-shadow: var(--shadow-md);
}

.welcome-message h2 {
    font-size: 36px;
    color: var(--secondary-800);
    margin-bottom: 16px;
    font-weight: 700;
}

.welcome-message p {
    font-size: 20px;
    color: var(--secondary-600);
    margin-bottom: 48px;
    font-weight: 500;
    line-height: 1.6;
}

.welcome-steps {
    display: flex;
    justify-content: center;
    gap: 60px;
}

.step {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 16px;
    transition: all 0.3s ease;
}

.step:hover {
    transform: translateY(-4px);
}

.step-number {
    width: 56px;
    height: 56px;
    background: var(--gradient-primary);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
    font-size: 24px;
    box-shadow: var(--shadow-md);
}

.step-text {
    font-size: 16px;
    color: var(--secondary-600);
    font-weight: 600;
    text-align: center;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Modal Styles */
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.6);
    backdrop-filter: blur(8px);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.modal-overlay.show {
    opacity: 1;
    visibility: visible;
}

.modal {
    background: white;
    border-radius: 20px;
    max-width: 600px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--shadow-xl);
    transform: scale(0.9) translateY(20px);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.modal-overlay.show .modal {
    transform: scale(1) translateY(0);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px 32px;
    border-bottom: 1px solid var(--secondary-200);
    background: var(--gradient-primary);
    color: white;
    border-radius: 20px 20px 0 0;
}

.modal-header h3 {
    font-size: 24px;
    font-weight: 700;
    margin: 0;
}

.modal-close {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white;
    font-size: 28px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.modal-close:hover {
    background: rgba(255,255,255,0.3);
    transform: scale(1.1);
}

.modal-body {
    padding: 32px;
}

.modal-footer {
    display: flex;
    gap: 16px;
    justify-content: flex-end;
    padding: 24px 32px;
    border-top: 1px solid var(--secondary-200);
    background: var(--secondary-50);
    border-radius: 0 0 20px 20px;
}

/* Indicadores de Scanner */
.scanner-indicator {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    padding: 8px 16px;
    border-radius: 20px;
    background: var(--secondary-50);
    border: 1px solid var(--secondary-200);
    font-weight: 600;
}

.indicator-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: var(--success-500);
    animation: pulse 2s infinite;
}

.indicator-text {
    font-weight: 600;
    color: var(--secondary-600);
}

.scanner-indicator.scanning .indicator-dot {
    background: var(--warning-500);
    animation: pulse 1s infinite;
}

.scanner-indicator.error .indicator-dot {
    background: var(--danger-500);
    animation: none;
}

.scanner-indicator.success .indicator-dot {
    background: var(--success-500);
    animation: none;
}

@keyframes pulse {
    0% { opacity: 1; transform: scale(1); }
    50% { opacity: 0.6; transform: scale(1.1); }
    100% { opacity: 1; transform: scale(1); }
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Responsive */
@media (max-width: 768px) {
    .operator-panel {
        padding: 16px;
    }
    
    .operator-header {
        flex-direction: column;
        text-align: center;
        gap: 20px;
        padding: 20px;
    }
    
    .operator-title {
        font-size: 28px;
    }
    
    .scanner-header {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }
    
    .form-header {
        flex-direction: column;
        gap: 16px;
        text-align: center;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .welcome-steps {
        flex-direction: column;
        gap: 24px;
    }
    
    .form-actions {
        flex-direction: column;
    }
    
    .scanner-input-area {
        flex-direction: column;
    }
    
    .btn-scanner {
        width: 100%;
    }
    
    .welcome-message {
        padding: 60px 40px;
    }
    
    .welcome-icon {
        width: 120px;
        height: 120px;
        font-size: 60px;
    }
    
    .welcome-message h2 {
        font-size: 28px;
    }
    
    .welcome-message p {
        font-size: 18px;
    }
    
    .modal {
        width: 95%;
        margin: 20px;
    }
    
    .modal-header,
    .modal-body,
    .modal-footer {
        padding: 20px;
    }
}

/* Iconos básicos */
.icon-list::before { content: "??"; }
.icon-logout::before { content: "??"; }
.icon-search::before { content: "??"; }
.icon-check::before { content: "??"; }
.icon-reset::before { content: "??"; }

/* ============================================
   OVERRIDES — Tono empresarial sobrio
   ============================================ */

.operator-panel {
    max-width: 1100px;
    padding: 24px;
    background: transparent;
}

/* Header — banda morada plana, sin gradiente exagerado */
.operator-header {
    background: var(--primary-700);
    border-radius: 8px;
    padding: 20px 28px;
    margin-bottom: 24px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
.operator-header::before { display: none; }
.operator-title { font-size: 22px; font-weight: 600; letter-spacing: -0.2px; }
.operator-session { margin-top: 6px; font-size: 13px; opacity: 0.9; }
.operator-role {
    background: rgba(255,255,255,0.18);
    padding: 2px 10px; border-radius: 4px;
    font-size: 11px; font-weight: 600;
    text-transform: uppercase; letter-spacing: 0.4px;
    backdrop-filter: none;
}

/* Tarjetas — bordes finos, radio uniforme, sin franja superior decorativa */
.scanner-section,
.form-section,
.persona-card,
.welcome-message,
#form_section.card {
    border-radius: 8px;
    padding: 22px;
    margin-bottom: 20px;
    border: 1px solid var(--secondary-200);
    background: #fff;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
    animation: none;
    border-top: 3px solid var(--primary-700);
}
.scanner-section::before,
.form-section::before,
.persona-card::before,
.welcome-message::before { display: none; }

/* Encabezado de sección — más sobrio */
.scanner-header { margin-bottom: 16px; align-items: center; }
.scanner-header h2 {
    font-size: 16px;
    color: var(--primary-700);
    font-weight: 600;
    gap: 12px;
}
.scanner-icon {
    font-size: 14px; font-weight: 700;
    width: 28px; height: 28px;
    padding: 0;
    background: var(--primary-100);
    color: var(--primary-700);
    border-radius: 6px;
    display: inline-flex; align-items: center; justify-content: center;
}

/* Indicadores ocultos — el estado se comunica con la pantalla de éxito y los toasts. */
.scanner-indicator,
#scanner_status { display: none !important; }

/* Input escáner — táctil pero no escandaloso */
.scanner-input-area { gap: 12px; margin-bottom: 12px; }
.scanner-input-touch {
    font-size: 17px;
    padding: 16px 18px;
    border: 1.5px solid var(--secondary-200);
    border-radius: 8px;
    text-align: left;
    font-weight: 500;
    letter-spacing: 0;
    text-transform: none;
    background: #fff;
    color: var(--secondary-800);
    height: 56px;
}
.scanner-input-touch:focus {
    border-color: var(--primary-700);
    box-shadow: 0 0 0 3px rgba(74,25,66,0.10);
    transform: none;
}
.scanner-input-touch::placeholder {
    color: var(--secondary-400);
    font-weight: 400;
    text-transform: none;
    letter-spacing: 0;
}
.btn-scanner {
    height: 56px;
    min-width: 140px;
    padding: 0 28px;
    font-size: 14px;
    font-weight: 600;
    background: var(--primary-700);
    border-radius: 8px;
    box-shadow: none;
    letter-spacing: 0.5px;
}
.btn-scanner:hover { background: var(--primary-600); transform: none; box-shadow: none; }

.scanner-status { background: transparent; border: none; padding: 4px 0; min-height: 18px; font-size: 12px; }

/* Persona card — limpia */
.persona-card { background: #fff; border-color: var(--secondary-200); border-top-color: var(--primary-700); }
.persona-header { margin-bottom: 14px; }
.persona-name { font-size: 17px; color: var(--secondary-800); font-weight: 600; }
.persona-badge {
    background: var(--primary-700);
    color: #fff;
    padding: 3px 10px;
    border-radius: 4px;
    font-size: 10px;
    box-shadow: none;
}
.persona-details {
    grid-template-columns: repeat(3, 1fr);
    gap: 1px;
    background: var(--secondary-200);
    border: 1px solid var(--secondary-200);
    border-radius: 6px;
    overflow: hidden;
}
.persona-detail {
    background: #fff;
    border: none;
    border-radius: 0;
    padding: 10px 14px;
    flex-direction: column;
    align-items: flex-start;
    gap: 2px;
}
.persona-detail:hover { background: #fff; transform: none; box-shadow: none; }
.detail-label {
    font-size: 10px;
    color: var(--secondary-500);
    text-transform: uppercase;
    letter-spacing: 0.4px;
    font-weight: 600;
}
.detail-value { font-size: 14px; color: var(--secondary-800); font-weight: 500; }

/* Welcome — instructivo, sin emoji */
.welcome-message { text-align: left; padding: 28px; }
.welcome-icon {
    width: 36px; height: 36px;
    background: var(--primary-700);
    color: #fff;
    font-size: 16px; font-weight: 700;
    border-radius: 6px;
    margin: 0 0 14px 0;
    box-shadow: none;
    display: inline-flex; align-items: center; justify-content: center;
}
.welcome-message h2 { font-size: 17px; color: var(--secondary-800); font-weight: 600; margin-bottom: 6px; }
.welcome-message p { font-size: 14px; color: var(--secondary-600); margin-bottom: 22px; font-weight: 400; }
.welcome-steps { gap: 12px; justify-content: stretch; }
.step {
    flex: 1;
    flex-direction: row;
    background: var(--secondary-50);
    border: 1px solid var(--secondary-200);
    padding: 14px 16px;
    border-radius: 8px;
    align-items: center;
    gap: 12px;
}
.step:hover { transform: none; }
.step-number {
    width: 28px; height: 28px;
    background: #fff;
    color: var(--primary-700);
    border: 1.5px solid var(--primary-700);
    font-size: 13px;
    box-shadow: none;
    flex-shrink: 0;
}
.step-text {
    font-size: 13px;
    color: var(--secondary-700);
    font-weight: 500;
    text-transform: none;
    letter-spacing: 0;
    text-align: left;
}

/* Formulario — encabezado sobrio */
#form_section.card .card-header {
    background: transparent;
    color: var(--primary-700);
    padding: 0 0 14px 0;
    border: none;
    border-bottom: 1px solid var(--secondary-200);
    margin-bottom: 18px;
    font-size: 16px;
    font-weight: 600;
}
#form_section.card .card-header h3 { font-size: 16px; color: var(--primary-700); font-weight: 600; }
#form_section.card .card-body { padding: 0; }

/* Form controls compactos */
#form_section .form-control {
    padding: 11px 14px;
    font-size: 14px;
    border: 1px solid var(--secondary-300);
    border-radius: 6px;
    font-weight: 400;
    color: var(--secondary-800);
    transition: border-color 0.15s, box-shadow 0.15s;
    min-height: 44px;
}
#form_section .form-control:focus {
    border-color: var(--primary-700);
    box-shadow: 0 0 0 3px rgba(74,25,66,0.10);
    transform: none;
}
#form_section .form-group label {
    font-size: 12px;
    color: var(--secondary-700);
    text-transform: uppercase;
    letter-spacing: 0.3px;
    margin-bottom: 6px;
    font-weight: 600;
}
#form_section textarea.form-control { min-height: 80px; resize: vertical; }

/* Acciones del formulario — patrón empresarial: cancelar a la izquierda, primarias a la derecha */
.form-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 24px;
    padding-top: 18px;
    border-top: 1px solid var(--secondary-200);
}
.form-actions-left, .form-actions-right { display: flex; gap: 10px; }

/* Botones uniformes */
.btn {
    padding: 10px 20px;
    font-size: 14px;
    font-weight: 600;
    border-radius: 6px;
    min-height: 44px;
    text-transform: none;
    letter-spacing: 0;
    transition: background 0.15s, border-color 0.15s;
    border: 1px solid transparent;
}
.btn-lg {
    padding: 13px 26px;
    font-size: 15px;
    min-height: 52px;
    border-radius: 6px;
    text-transform: none;
    letter-spacing: 0;
}
.btn-lg::before { display: none; }
.btn-lg:hover:not(:disabled) { transform: none; box-shadow: none; }
.btn-primary { background: var(--primary-700); color: #fff; border-color: var(--primary-700); }
.btn-primary:hover:not(:disabled) { background: var(--primary-600); border-color: var(--primary-600); }
.btn-success { background: var(--success-600); color: #fff; border: 1px solid var(--success-600); }
.btn-success:hover:not(:disabled) { background: var(--success-700); border-color: var(--success-700); }
.btn-outline {
    background: #fff;
    border: 1px solid var(--secondary-300);
    color: var(--secondary-700);
}
.btn-outline:hover:not(:disabled) { background: var(--secondary-50); border-color: var(--secondary-400); }
.btn:disabled { opacity: 0.5; cursor: not-allowed; }

/* Modal — encabezado plano */
.modal { border-radius: 8px; }
.modal-header {
    background: var(--primary-700);
    color: #fff;
    padding: 16px 22px;
    border-radius: 8px 8px 0 0;
}
.modal-header h3 { font-size: 16px; font-weight: 600; }
.modal-close {
    background: rgba(255,255,255,0.15);
    width: 28px; height: 28px;
    font-size: 18px;
    border-radius: 4px;
}
.modal-close:hover { background: rgba(255,255,255,0.25); transform: none; }
.modal-body { padding: 22px; }
.modal-footer {
    padding: 14px 22px;
    background: var(--secondary-50);
    border-radius: 0 0 8px 8px;
}

/* Form rows del modal — más compactos */
.modal-body .form-control { padding: 10px 12px; font-size: 14px; min-height: 40px; }
.modal-body .form-group label { font-size: 12px; text-transform: uppercase; letter-spacing: 0.3px; color: var(--secondary-700); font-weight: 600; }

/* Quita la pseudoclase de inicio del scanner-input que ponía mayúsculas/spacing */
.scanner-input-touch:focus { transform: none; }

/* ===== Teclado numérico táctil ===== */
.numpad {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    max-width: 420px;
    margin: 16px auto 4px;
}

.numpad-key {
    height: 68px;
    font-size: 24px;
    font-weight: 600;
    color: var(--secondary-800);
    background: #fff;
    border: 1.5px solid var(--secondary-200);
    border-radius: 10px;
    cursor: pointer;
    user-select: none;
    -webkit-tap-highlight-color: transparent;
    transition: background 0.08s, border-color 0.08s, transform 0.05s;
    font-family: inherit;
}

.numpad-key:hover { background: var(--primary-50); border-color: var(--primary-300); }
.numpad-key:active { background: var(--primary-100); transform: scale(0.97); }

.numpad-action {
    background: var(--secondary-50);
    color: var(--secondary-700);
    font-size: 15px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.4px;
}
.numpad-action:hover { background: var(--secondary-100); }

.numpad-back {
    background: var(--secondary-50);
    color: var(--primary-700);
    font-size: 26px;
    font-weight: 700;
}
.numpad-back:hover { background: var(--primary-100); }

.scanner-input-touch[readonly] {
    background: #fff;
    cursor: default;
    caret-color: var(--primary-700);
}

/* Badge inline para encabezados numerados */
.step-badge-inline {
    display: inline-flex; align-items: center; justify-content: center;
    width: 24px; height: 24px;
    background: var(--primary-700); color: #fff;
    border-radius: 4px;
    font-size: 13px; font-weight: 700;
    margin-right: 8px;
    vertical-align: middle;
}

/* Pantalla de éxito */
.success-screen {
    background: #fff;
    border: 1px solid var(--secondary-200);
    border-top: 3px solid var(--success-600);
    border-radius: 8px;
    padding: 48px 32px;
    text-align: center;
    box-shadow: 0 1px 2px rgba(0,0,0,0.04);
}
.success-icon {
    width: 72px; height: 72px;
    border-radius: 50%;
    background: var(--success-50);
    color: var(--success-600);
    font-size: 40px; font-weight: 700;
    display: inline-flex; align-items: center; justify-content: center;
    margin-bottom: 18px;
    border: 2px solid var(--success-200);
}
.success-screen h2 {
    font-size: 22px;
    color: var(--secondary-800);
    margin-bottom: 8px;
    font-weight: 600;
}
.success-screen p {
    font-size: 14px;
    color: var(--secondary-600);
    margin-bottom: 24px;
}

/* ============================================
   KIOSCO RESPONSIVE — diseño deterministico, fit-to-screen
   En pantallas anchas usa grid 2-columnas para que todo quepa sin scroll.
   ============================================ */

/* Reset de tamaño base para evitar herencia de configuraciones del navegador */
.operator-panel { font-size: 16px; }

/* Contenedor principal — siempre centrado con max-width fijo */
.operator-panel {
    width: 100%;
    max-width: 1100px;
    margin: 0 auto;
    padding: 14px;
    box-sizing: border-box;
}

/* Header */
.operator-header { padding: 16px 22px; margin-bottom: 14px; }
.operator-title   { font-size: 21px; line-height: 1.25; }
.operator-session { font-size: 13px; }

/* Tarjetas */
.scanner-section,
.form-section,
.persona-card,
.welcome-message,
#form_section.card,
.success-screen {
    padding: 18px;
    margin-bottom: 12px;
}

.scanner-header h2 { font-size: 16px; }
.scanner-input-touch {
    font-size: 18px;
    height: 56px;
    padding: 14px 18px;
}
.btn-scanner {
    height: 56px;
    min-width: 150px;
    font-size: 14px;
    padding: 0 26px;
}

/* Numpad — centrado, anchura fija, teclas grandes */
.numpad {
    width: 100%; max-width: 420px;
    gap: 10px;
    margin: 16px auto 4px;
}
.numpad-key { height: 64px; font-size: 24px; border-radius: 10px; }
.numpad-action { font-size: 14px; }
.numpad-back   { font-size: 26px; }

/* Welcome */
.welcome-message h2 { font-size: 17px; }
.welcome-message p  { font-size: 14px; }
.welcome-steps { display: flex; flex-wrap: wrap; gap: 12px; }
.step { flex: 1; min-width: 220px; padding: 14px; }
.step-text { font-size: 13px; }

/* Persona card */
.persona-details { grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); }

/* Form */
.form-row, .row { flex-wrap: wrap; }
#form_section .form-control { font-size: 14px; min-height: 44px; }

/* ===== Tablet (≤ 900px) — apila scanner+boton y pasos ===== */
@media (max-width: 900px) {
    .operator-panel { max-width: 100%; padding: 14px; }
    .operator-header { padding: 16px 20px; }
    .welcome-steps { flex-direction: column; gap: 10px; }
    .step { min-width: 0; width: 100%; }
}

/* ===== Móvil (≤ 600px) — todo en columna ===== */
@media (max-width: 600px) {
    .operator-panel { padding: 10px; }
    .operator-header {
        flex-direction: column; text-align: center; gap: 10px;
        padding: 14px 16px;
    }
    .operator-title { font-size: 18px; }
    .scanner-header { flex-direction: column; gap: 8px; text-align: center; }
    .scanner-section, .welcome-message, .persona-card, #form_section.card {
        padding: 16px;
    }
    .scanner-input-area { flex-direction: column; gap: 10px; }
    .btn-scanner { width: 100%; min-width: 0; }
    .numpad { max-width: 100%; }
    .numpad-key { height: 56px; font-size: 22px; }
    .col-6, .col-md-6, .col-lg-6 { width: 100%; max-width: 100%; flex: 0 0 100%; }
    .form-row, .row { grid-template-columns: 1fr; }
    .form-actions { flex-direction: column; gap: 10px; align-items: stretch; }
    .form-actions-left, .form-actions-right { width: 100%; justify-content: stretch; }
    .form-actions-left .btn, .form-actions-right .btn { flex: 1; }
    .persona-details { grid-template-columns: 1fr; }
    .modal { width: 95%; margin: 16px; }
    .modal-body, .modal-header, .modal-footer { padding: 16px; }
}

/* ===== Móvil muy pequeño (≤ 380px) ===== */
@media (max-width: 380px) {
    .operator-title { font-size: 16px; }
    .scanner-input-touch { font-size: 16px; height: 50px; }
    .numpad-key { height: 50px; font-size: 20px; }
}

/* ===== Monitores grandes (≥ 1400px) ===== */
@media (min-width: 1400px) {
    .operator-panel { max-width: 1200px; }
}

/* ===== Escritorio (≥ 1024px) — layout 2 columnas para evitar scroll =====
   Header arriba ancho completo, columna izquierda con instrucciones o
   tarjeta de persona, columna derecha con scanner o formulario.
   Usamos grid-column en cada hijo en vez de grid-template-areas para que
   los elementos display:none no reserven filas vacias. */
@media (min-width: 1024px) {
    .operator-panel {
        display: grid;
        grid-template-columns: minmax(0, 1fr) minmax(0, 1.15fr);
        column-gap: 18px;
        row-gap: 12px;
        align-items: start;
        /* Sin esto el grid con min-height:100vh estira las filas para
           llenar la pantalla, generando un gap enorme entre filas. */
        align-content: start;
    }
    /* Layout en dos estados:
       Estado 1 (inicial)  : welcome (izq) + scanner (der)
       Estado 2 (identificado): persona + formulario apilados a todo el ancho */
    .operator-header   { grid-column: 1 / -1; margin-bottom: 0; }
    .welcome-message   { grid-column: 1 / 2;  margin-bottom: 0; }
    .scanner-section   { grid-column: 2 / 3;  margin-bottom: 0; }
    .persona-card      { grid-column: 1 / -1; margin-bottom: 0; }
    #form_section.card { grid-column: 1 / -1; margin-bottom: 0; }
    .success-screen    { grid-column: 1 / -1; margin-bottom: 0; }
    .numpad           { max-width: 360px; margin-top: 12px; }
    .numpad-key       { height: 56px; font-size: 22px; }
}

/* ===== Altura limitada (≤ 800px) — compactar verticalmente ===== */
@media (max-height: 800px) {
    .operator-panel   { padding: 10px; }
    .operator-header  { padding: 12px 18px; margin-bottom: 10px; }
    .scanner-section, .welcome-message, .persona-card, #form_section.card {
        padding: 14px; margin-bottom: 10px;
    }
    .scanner-input-touch { height: 50px; }
    .btn-scanner { height: 50px; }
    .numpad { margin-top: 10px; }
    .numpad-key { height: 50px; font-size: 20px; }
    .welcome-message h2 { font-size: 16px; margin: 0 0 4px 0; }
    .welcome-message p  { margin: 0 0 10px 0; }
    .step { padding: 10px 12px; }
}

/* ===== Estilos del swal de duplicado mensual (alerta del kiosco) ===== */
.swal-tandil {
    border-radius: 14px !important;
    box-shadow: 0 20px 50px rgba(74,25,66,0.18) !important;
}
.swal-tandil-btn {
    padding: 10px 28px !important;
    font-size: 14px !important;
    font-weight: 600 !important;
    border-radius: 8px !important;
    box-shadow: 0 2px 8px rgba(74,25,66,0.3) !important;
    margin-top: 8px !important;
}
.swal-tandil-btn:hover {
    box-shadow: 0 4px 14px rgba(74,25,66,0.4) !important;
    transform: translateY(-1px);
}

/* ===== Altura muy limitada (≤ 650px) ===== */
@media (max-height: 650px) {
    .operator-header  { padding: 10px 16px; margin-bottom: 8px; }
    .operator-title   { font-size: 18px; }
    .scanner-section, .welcome-message, .persona-card, #form_section.card {
        padding: 12px; margin-bottom: 8px;
    }
    .numpad-key { height: 44px; font-size: 18px; }
    .scanner-input-touch { height: 44px; font-size: 16px; }
    .btn-scanner { height: 44px; }
}

/* ===== Pantallas táctiles — botones más confortables ===== */
@media (hover: none) and (pointer: coarse) {
    .numpad-key { min-height: 64px; }
    .btn { min-height: 48px; }
    .btn-lg { min-height: 56px; }
    .form-control { min-height: 48px; }
}

/* ===== Altura limitada (monitores pequeños o landscape de tablet) ===== */
@media (max-height: 700px) {
    .operator-header { padding: 12px 20px; margin-bottom: 14px; }
    .scanner-section, .welcome-message, .persona-card, #form_section.card {
        padding: 16px; margin-bottom: 12px;
    }
    .numpad-key { height: 54px; font-size: 20px; }
    .welcome-message { padding: 16px 20px; }
}
</style>

<script>
// Variables globales
var BASE_URL = '<?= BASE_URL ?>';
var CSRF_TOKEN = '<?= Session::getCsrfToken() ?>';

// Teclado numérico táctil — escribe sobre #scanner_input.
// Se inicializa al cargar el DOM. Los botones tienen data-key (dígito) o data-action (clear/back).
document.addEventListener('DOMContentLoaded', function () {
    var input = document.getElementById('scanner_input');
    var numpad = document.getElementById('numpad');
    if (!input || !numpad) return;

    var MAX_LEN = 12; // suficiente para CC, CE, NIT
    function setValue(v) {
        input.value = v;
        input.dispatchEvent(new Event('input', { bubbles: true }));
    }

    numpad.addEventListener('click', function (e) {
        var btn = e.target.closest('.numpad-key');
        if (!btn) return;
        var key = btn.getAttribute('data-key');
        var action = btn.getAttribute('data-action');
        if (key !== null) {
            if (input.value.length >= MAX_LEN) return;
            setValue(input.value + key);
        } else if (action === 'back') {
            setValue(input.value.slice(0, -1));
        } else if (action === 'clear') {
            setValue('');
        }
        // Mantener foco en el input para que el lector de barras físico siga funcionando.
        input.focus({ preventScroll: true });
    });

    // Foco inicial — necesario para que scanner.js capture el flujo del lector de barras.
    setTimeout(function () { input.focus({ preventScroll: true }); }, 50);
});

// Oculta el teclado al pasar al formulario; lo reaparece al reiniciar el kiosko.
function ocultarNumpad() {
    var np = document.getElementById('numpad');
    if (np) np.style.display = 'none';
}
function mostrarNumpad() {
    var np = document.getElementById('numpad');
    if (np) np.style.display = '';
}

// CRÍTICO: fallback global para setStatus.
// solicitud.js lo invoca a nivel módulo; sin esta definición lanza ReferenceError
// y rompe el flujo justo después de mostrar la persona (el formulario nunca aparece).
window.setStatus = function(msg, type) {
    var el = document.getElementById('scanner_status');
    if (!el) return;
    var colors = {
        success: '#1B873F',
        danger:  '#B42318',
        error:   '#B42318',
        warning: '#B54708',
        info:    '#175CD3'
    };
    el.style.color = colors[type] || '#4A4651';
    el.textContent = msg;
};

// Función para actualizar indicador del scanner
function actualizarScannerIndicator(estado, mensaje) {
    var indicator = document.getElementById('scanner_indicator');
    var text = document.getElementById('scanner_status');
    
    // Remover clases anteriores
    indicator.classList.remove('scanning', 'error', 'success');
    
    // Agregar clase según estado
    if (estado === 'scanning') {
        indicator.classList.add('scanning');
    } else if (estado === 'error') {
        indicator.classList.add('error');
    } else if (estado === 'success') {
        indicator.classList.add('success');
    }
    
    // Actualizar mensaje
    if (text) {
        text.textContent = mensaje;
    }
}

// Función para mostrar información de persona
function mostrarPersona(persona) {
    document.getElementById('persona_nombre').textContent = 
        persona.nombre_completo || 
        (persona.primer_nombre + ' ' + persona.primer_apellido);
    document.getElementById('persona_doc').textContent = persona.documento;
    document.getElementById('persona_sede').textContent = persona.sede_nombre || 'N/A';
    document.getElementById('persona_tel').textContent = persona.telefono || 'N/A';
    document.getElementById('persona_id').value = persona.id;
    document.getElementById('persona_info').classList.remove('hidden');
}

// Función para actualizar información de cupos
function actualizarCupoInfo(sedeId) {
    // Implementar lógica de cupos si es necesario
    var cupoInfo = document.getElementById('cupo_info');
    if (cupoInfo) {
        cupoInfo.classList.add('hidden');
    }
}

// Función para búsqueda manual
function buscarManual() {
    var input = document.getElementById('scanner_input');
    var documento = input.value.trim();
    
    if (documento.length < 3) {
        swalWarning('Escriba al menos 3 dígitos del documento');
        return;
    }
    
    actualizarScannerIndicator('scanning', 'Buscando persona...');
    
    // Buscar persona via AJAX
    var formData = new FormData();
    formData.append('documento', documento);
    
    ajaxGet(BASE_URL + '/solicitudes/buscar-persona?documento=' + encodeURIComponent(documento), function(data) {
        if (data.success) {
            var p = data.data || {};
            var periodoLabel = p.periodo_label || 'mes';

            // Bloqueo 1: ya tiene solicitud activa en el periodo
            if (p.ya_solicito_periodo || p.ya_solicito_mes) {
                actualizarScannerIndicator('error', periodoLabel === '30 días' ? 'Ya solicitó en los últimos 30 días' : 'Ya solicitó ramo esta ' + periodoLabel);
                alertarSolicitudExistente(p);
                return;
            }

            // Bloqueo 2: no hay cupos disponibles en la sede de la persona
            if (p.sin_cupo) {
                actualizarScannerIndicator('error', 'Sin cupos disponibles');
                alertarSinCupo(p);
                return;
            }

            actualizarScannerIndicator('success', 'Persona encontrada');
            mostrarPersona(p);
            mostrarFormularioSolicitud();

            // Auto-llenar campos ocultos
            document.getElementById('id_sede').value = p.id_sede || '';
            document.getElementById('nombre_destinatario').value = p.nombre_completo ||
                (p.primer_nombre + ' ' + p.primer_apellido);

            // Habilitar botón de guardar
            var btnGuardar = document.getElementById('btn_guardar');
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = 'Guardar Solicitud';
            btnGuardar.title = 'Solicitar ramo para esta persona';
        } else {
            actualizarScannerIndicator('error', data.message || 'Persona no encontrada');
            // Mostrar opción de registrar nueva persona
            swalConfirm(
                'Persona no encontrada',
                '¿Desea registrar una nueva persona con este documento?',
                function() {
                    abrirModalPersonaConDocumento(documento);
                }
            );
        }
    });
}

// Función para abrir modal de persona con documento precargado
function abrirModalPersonaConDocumento(documento) {
    document.getElementById('modal_persona').classList.add('show');
    document.getElementById('p_documento').value = documento;
    document.getElementById('p_primer_nombre').focus();
}

// Función para guardar persona desde el panel operador
function guardarPersona() {
    var form = document.getElementById('form_persona');
    var formData = new FormData(form);
    
    // Validación básica
    var documento = formData.get('documento');
    var primerNombre = formData.get('primer_nombre');
    var primerApellido = formData.get('primer_apellido');
    var idSede = formData.get('id_sede');
    
    if (!documento || !primerNombre || !primerApellido || !idSede) {
        swalWarning('Por favor complete los campos requeridos');
        return;
    }
    
    ajaxPost(BASE_URL + '/solicitudes/crear-persona', formData, function(data) {
        if (data.success) {
            cerrarModalPersona();
            Toast.fire({ icon: 'success', title: 'Persona registrada correctamente' });
            
            // Auto-buscar la persona recién creada
            setTimeout(function() {
                document.getElementById('scanner_input').value = documento;
                buscarManual();
            }, 1000);
        } else {
            swalError(data.message || 'Error al registrar la persona');
        }
    });
}

// Función para enviar solicitud
function enviarSolicitud() {
    var personaId = document.getElementById('persona_id').value;
    if (!personaId) {
        swalWarning('Debe buscar una persona primero');
        return;
    }
    
    var form = document.getElementById('form_solicitud');
    var formData = new FormData(form);
    
    // Validación de campos requeridos
    var idMotivo = formData.get('id_motivo');
    if (!idMotivo) {
        swalWarning('Seleccione un motivo para la solicitud');
        document.getElementById('id_motivo').focus();
        return;
    }
    
    // Verificar motivo "otro"
    var motivoSelect = document.getElementById('id_motivo');
    var selectedOption = motivoSelect.options[motivoSelect.selectedIndex];
    if (selectedOption.getAttribute('data-requiere-detalle') === '1') {
        var motivoOtro = formData.get('motivo_otro');
        if (!motivoOtro || motivoOtro.trim() === '') {
            swalWarning('Especifique el motivo detallado');
            document.getElementById('motivo_otro').focus();
            return;
        }
    }
    
    // Deshabilitar botón para evitar doble envío
    var btnGuardar = document.getElementById('btn_guardar');
    btnGuardar.disabled = true;
    btnGuardar.innerHTML = 'GUARDANDO...';
    
    ajaxPost(BASE_URL + '/solicitudes/nueva', formData, function(data) {
        if (data.success) {
            mostrarPantallaExito();
        } else {
            swalError(data.message || 'Error al crear la solicitud');
            btnGuardar.disabled = false;
            btnGuardar.innerHTML = 'Guardar Solicitud';
        }
    });
}

// Muestra la pantalla de éxito y oculta lo demás. El usuario presiona "Registrar otra solicitud" para reiniciar.
function mostrarPantallaExito() {
    // En lugar de mostrar una pantalla intermedia con boton, lanzamos un swal que
    // se autocierra y reinicia el kiosco — el usuario solo ve la confirmacion y
    // de inmediato vuelve al estado inicial listo para la siguiente solicitud.
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'Solicitud registrada',
            text: 'Su solicitud ha sido enviada correctamente.',
            timer: 2500,
            timerProgressBar: true,
            showConfirmButton: false,
            allowOutsideClick: false,
            allowEscapeKey: false,
        }).then(function () { reiniciarKiosco(); });
    } else {
        alert('Solicitud registrada con exito.');
        reiniciarKiosco();
    }
}

// Vuelve al estado inicial del kiosko (para el siguiente usuario).
function reiniciarKiosco() {
    if (window.__kioskoTimer) { clearTimeout(window.__kioskoTimer); window.__kioskoTimer = null; }
    document.getElementById('success_message').classList.add('hidden');
    document.getElementById('scanner_section').classList.remove('hidden');
    document.getElementById('welcome_message').classList.remove('hidden');
    mostrarNumpad();
    limpiarFormulario();
}

// Función para limpiar formulario y dejar el kiosko listo para el siguiente usuario
function limpiarFormulario() {
    var form = document.getElementById('form_solicitud');
    if (form) form.reset();
    document.getElementById('persona_id').value = '';
    document.getElementById('persona_info').classList.add('hidden');
    document.getElementById('form_section').classList.add('hidden');
    document.getElementById('welcome_message').classList.remove('hidden');
    document.getElementById('scanner_section').classList.remove('hidden');
    mostrarNumpad();
    document.getElementById('scanner_input').value = '';
    document.getElementById('scanner_status').textContent = '';

    // Re-habilitar selector de sede (solicitud.js lo deshabilita al identificar persona)
    var sedeSelect = document.getElementById('id_sede');
    if (sedeSelect) {
        sedeSelect.disabled = false;
        sedeSelect.value = '';
    }
    var hiddenSede = document.getElementById('id_sede_hidden');
    if (hiddenSede && hiddenSede.parentNode) hiddenSede.parentNode.removeChild(hiddenSede);
    var nota = document.getElementById('sede_nota');
    if (nota) { nota.style.display = 'none'; nota.classList.add('hidden'); }

    var nombreDest = document.getElementById('nombre_destinatario');
    if (nombreDest) nombreDest.value = '';

    actualizarScannerIndicator('default', 'Listo');
    var cupoInfo = document.getElementById('cupo_info');
    if (cupoInfo) cupoInfo.classList.add('hidden');

    var btnGuardar = document.getElementById('btn_guardar');
    if (btnGuardar) {
        btnGuardar.disabled = true;
        btnGuardar.innerHTML = 'Guardar Solicitud';
        btnGuardar.title = 'Primero identifíquese con su número de cédula';
    }

    document.getElementById('scanner_input').focus();
}

// Función para mostrar formulario cuando se encuentra persona
function mostrarFormularioSolicitud() {
    document.getElementById('welcome_message').classList.add('hidden');
    // Ocultamos el scanner una vez identificada la persona — el flujo continua
    // en el formulario, y el boton Cancelar permite volver al scanner.
    document.getElementById('scanner_section').classList.add('hidden');
    document.getElementById('form_section').classList.remove('hidden');
    ocultarNumpad();
}

// Helper compartido: parsea respuestas JSON aun si el status HTTP es 4xx/5xx.
// El backend retorna {success, message, ...} con codigos como 400 para validaciones,
// asi que no debemos descartar el body por el status code.
function __handleAjax(xhr, callback) {
    if (xhr.readyState !== 4) return;
    // Status 0 = error de red real (CORS, sin conexion, abort).
    if (xhr.status === 0) {
        callback({ success: false, message: 'Error de conexión' });
        return;
    }
    try {
        var data = JSON.parse(xhr.responseText);
        callback(data);
    } catch (e) {
        console.error('Error parsing JSON:', e);
        callback({ success: false, message: 'Error en la respuesta del servidor' });
    }
}

function ajaxGet(url, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('GET', url, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onreadystatechange = function () { __handleAjax(xhr, callback); };
    xhr.send();
}

function ajaxPost(url, formData, callback) {
    var xhr = new XMLHttpRequest();
    xhr.open('POST', url, true);
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.onreadystatechange = function () { __handleAjax(xhr, callback); };
    xhr.send(formData);
}

// Funciones de alerta (compatibilidad con SweetAlert2 si está disponible)
function swalWarning(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'warning',
            title: 'Atención',
            text: message
        });
    } else {
        alert('Atención: ' + message);
    }
}

function swalError(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: message
        });
    } else {
        alert('Error: ' + message);
    }
}

function swalConfirm(title, message, callback) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: title,
            text: message,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí',
            cancelButtonText: 'No'
        }).then(function(result) {
            if (result.isConfirmed) {
                callback();
            }
        });
    } else {
        if (confirm(title + '\n' + message)) {
            callback();
        }
    }
}

// Objeto Toast para compatibilidad
var Toast = {
    fire: function(options) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: options.icon,
                title: options.title,
                timer: options.timer || 3000,
                showConfirmButton: false
            });
        } else {
            alert(options.title);
            if (options.timer) {
                setTimeout(function() {
                    if (typeof options.then === 'function') {
                        options.then();
                    }
                }, options.timer);
            } else if (typeof options.then === 'function') {
                options.then();
            }
        }
    }
};

// Muestra alerta cuando la persona ya tiene una solicitud activa este mes
// y deja el kiosco en estado limpio para el siguiente usuario.
function alertarSolicitudExistente(persona) {
    var nombreCrudo = persona.nombre_completo || ((persona.primer_nombre || '') + ' ' + (persona.primer_apellido || '')).trim();
    var nombre = nombreCrudo.toLowerCase().replace(/\b\w/g, function (c) { return c.toUpperCase(); });

    var existente = persona.solicitud_existente || {};
    var fechaTxt = '';
    if (existente.fecha_solicitud) {
        var meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
        var d = new Date(existente.fecha_solicitud + 'T00:00:00');
        if (!isNaN(d)) fechaTxt = d.getDate() + ' de ' + meses[d.getMonth()] + ' de ' + d.getFullYear();
        else fechaTxt = existente.fecha_solicitud;
    }
    var motivoTxt = existente.motivo_nombre || '';
    var estadoTxt = existente.estado_nombre || '';

    var meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    var volverTxt = '';
    var diasRestantesTxt = '';
    if (existente.fecha_solicitud) {
        var dBase = new Date(existente.fecha_solicitud + 'T00:00:00');
        var dVolver = new Date(dBase);
        dVolver.setDate(dVolver.getDate() + 30);
        volverTxt = dVolver.getDate() + ' de ' + meses[dVolver.getMonth()] + ' de ' + dVolver.getFullYear();
        var diasRestantes = Math.ceil((dVolver - new Date()) / (1000 * 60 * 60 * 24));
        diasRestantesTxt = diasRestantes > 1 ? 'en ' + diasRestantes + ' días' : (diasRestantes === 1 ? 'mañana' : 'hoy');
    }

    var html =
        '<div style="width:72px;height:72px;border-radius:50%;background:linear-gradient(135deg,#4A1942,#7A3A72);'
        + 'display:flex;align-items:center;justify-content:center;margin:0 auto 16px">'
        + '<svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">'
        + '<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/></svg>'
        + '</div>'
        + '<p style="font-size:20px;font-weight:700;color:#1e293b;margin:0 0 2px 0">' + (nombre || 'Usuario') + '</p>'
        + '<p style="font-size:13px;color:#94a3b8;margin:0 0 20px 0">Ya tienes un ramo registrado</p>'
        + '<div style="display:flex;gap:10px;margin-bottom:4px">'
        +   '<div style="flex:1;background:#f8f5f9;border-radius:10px;padding:14px;text-align:left">'
        +     '<div style="font-size:10px;color:#94a3b8;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Solicitud anterior</div>'
        +     '<div style="font-size:15px;font-weight:700;color:#1e293b">' + (fechaTxt || '—') + '</div>'
        +     (motivoTxt ? '<div style="font-size:12px;color:#64748b;margin-top:3px">' + motivoTxt + '</div>' : '')
        +   '</div>'
        +   (volverTxt
        ?   '<div style="flex:1;background:#4A1942;border-radius:10px;padding:14px;text-align:left">'
        +     '<div style="font-size:10px;color:rgba(255,255,255,0.6);font-weight:700;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px">Podés solicitar</div>'
        +     '<div style="font-size:15px;font-weight:700;color:#fff">' + volverTxt + '</div>'
        +     '<div style="font-size:12px;color:rgba(255,255,255,0.65);margin-top:3px">' + diasRestantesTxt + '</div>'
        +   '</div>'
        :   '')
        + '</div>';

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            html: html,
            showConfirmButton: true,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#4A1942',
            allowOutsideClick: false,
            allowEscapeKey: false,
            width: 440,
            padding: '28px 24px 22px',
            customClass: { popup: 'swal-tandil', confirmButton: 'swal-tandil-btn' }
        }).then(function () { limpiarFormulario(); });
    } else {
        alert('Ya tiene una solicitud registrada. Puede volver a solicitar el ' + (volverTxt || 'en 30 días') + '.');
        limpiarFormulario();
    }
}

// Muestra alerta cuando la sede de la persona no tiene cupos disponibles.
function alertarSinCupo(persona) {
    var nombreCrudo = persona.nombre_completo || ((persona.primer_nombre || '') + ' ' + (persona.primer_apellido || '')).trim();
    var nombre = nombreCrudo.toLowerCase().replace(/\b\w/g, function (c) { return c.toUpperCase(); });
    var mensaje = persona.mensaje_sin_cupo || 'No hay cupos disponibles en esta sede.';

    var iconHTML =
        '<div style="width:88px;height:88px;border-radius:50%;background:linear-gradient(135deg,#b91c1c,#dc2626);'
        + 'display:flex;align-items:center;justify-content:center;margin:0 auto;box-shadow:0 4px 16px rgba(185,28,28,0.3)">'
        + '<svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">'
        + '<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>'
        + '</div>';

    var html =
        iconHTML +
        '<h2 style="font-size:20px;font-weight:700;color:#1e293b;margin:18px 0 4px 0">Sin cupos disponibles</h2>' +
        '<p style="font-size:14px;color:#64748b;margin:0">Hola <strong style="color:#b91c1c">' + (nombre || 'estimado usuario') + '</strong></p>' +
        '<p style="font-size:14px;color:#475569;margin:14px 0 0 0;line-height:1.5">' + mensaje + '</p>';

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            html: html,
            showConfirmButton: true,
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#b91c1c',
            allowOutsideClick: false,
            allowEscapeKey: false,
            width: 460,
            padding: '28px 24px 22px'
        }).then(function () { limpiarFormulario(); });
    } else {
        alert(mensaje);
        limpiarFormulario();
    }
}

// Override de funciones existentes para el panel operador
window.onPersonaEncontrada = function(persona) {
    if (persona && (persona.ya_solicito_periodo || persona.ya_solicito_mes)) {
        var pl = persona.periodo_label || 'mes';
        actualizarScannerIndicator('error', pl === '30 días' ? 'Ya solicitó en los últimos 30 días' : 'Ya solicitó ramo esta ' + pl);
        alertarSolicitudExistente(persona);
        return;
    }
    if (persona && persona.sin_cupo) {
        actualizarScannerIndicator('error', 'Sin cupos disponibles');
        alertarSinCupo(persona);
        return;
    }
    actualizarScannerIndicator('success', 'Persona encontrada');
    mostrarPersona(persona);
    mostrarFormularioSolicitud();
    
    // Auto-llenar campos ocultos con datos de la persona
    document.getElementById('id_sede').value = persona.id_sede || '';
    document.getElementById('nombre_destinatario').value = persona.nombre_completo || 
        (persona.primer_nombre + ' ' + persona.primer_apellido);
    
    // Actualizar información de cupos
    if (persona.id_sede) {
        actualizarCupoInfo(persona.id_sede);
    }
    
    // Habilitar botón de guardar
    var btnGuardar = document.getElementById('btn_guardar');
    btnGuardar.disabled = false;
    btnGuardar.innerHTML = 'Guardar Solicitud';
    btnGuardar.title = 'Solicitar ramo para esta persona';
};

// Override de cerrar modal para el panel operador
function cerrarModalPersona() {
    document.getElementById('modal_persona').classList.remove('show');
    // Focus de vuelta al scanner
    document.getElementById('scanner_input').focus();
}
</script>

<?php
  $jsScannerVer = @filemtime(BASE_PATH . '/public/js/scanner.js') ?: time();
  $jsSolicitudVer = @filemtime(BASE_PATH . '/public/js/solicitud.js') ?: time();
?>
<script src="<?= BASE_URL ?>/js/scanner.js?v=<?= $jsScannerVer ?>"></script>
<script src="<?= BASE_URL ?>/js/solicitud.js?v=<?= $jsSolicitudVer ?>"></script>
