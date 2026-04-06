<!-- Formulario Nueva Solicitud -->

<!-- Escáner de cédula -->
<div class="card">
    <div class="card-header">Escanear Cédula</div>
    <div class="card-body">
        <div class="form-group">
            <label for="scanner_input">Escanee o digite el número de documento:</label>
            <div class="d-flex gap-1">
                <input type="text" id="scanner_input" class="form-control scanner-input"
                       placeholder="Escanee el código de barras o escriba el documento..." autofocus autocomplete="off"
                       style="flex:1">
                <button type="button" class="btn btn-primary btn-lg" id="btn_buscar" onclick="buscarManual()">
                    Buscar
                </button>
            </div>
        </div>
        <div id="scanner_status" class="text-muted mt-1" style="font-size:13px"></div>
    </div>
</div>

<!-- Info persona encontrada -->
<div id="persona_info" class="persona-info hidden">
    <h4 id="persona_nombre"></h4>
    <p><strong>Documento:</strong> <span id="persona_doc"></span></p>
    <p><strong>Sede:</strong> <span id="persona_sede"></span></p>
    <p><strong>Teléfono:</strong> <span id="persona_tel"></span></p>
</div>

<!-- Formulario solicitud -->
<div class="card" id="form_solicitud_card">
    <div class="card-header">Datos de la Solicitud</div>
    <div class="card-body">
        <form id="form_solicitud" method="POST" action="<?= BASE_URL ?>/solicitudes/crear">
            <?= $csrfField ?>
            <input type="hidden" name="persona_id" id="persona_id" value="">

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="fecha_solicitud">Fecha de Solicitud</label>
                        <input type="date" name="fecha_solicitud" id="fecha_solicitud"
                               class="form-control" value="<?= $hoy ?>" readonly>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group">
                        <label for="id_sede">Sede *</label>
                        <select name="id_sede" id="id_sede" class="form-control" required>
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($sedes as $sede): ?>
                            <option value="<?= $sede['id'] ?>"><?= htmlspecialchars($sede['nombre']) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <small id="sede_nota" class="text-muted hidden" style="font-size:11px;margin-top:4px;display:none">
                            La sede corresponde a la persona seleccionada
                        </small>
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="nombre_destinatario">Nombre del Destinatario *</label>
                <input type="text" name="nombre_destinatario" id="nombre_destinatario"
                       class="form-control" maxlength="150" required>
            </div>

            <div class="row">
                <div class="col-6">
                    <div class="form-group">
                        <label for="id_motivo">Motivo *</label>
                        <select name="id_motivo" id="id_motivo" class="form-control" required>
                            <option value="">-- Seleccione --</option>
                            <?php foreach ($motivos as $motivo): ?>
                            <option value="<?= $motivo['id'] ?>"
                                    data-requiere-detalle="<?= $motivo['requiere_detalle'] ?>">
                                <?= htmlspecialchars($motivo['nombre']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="col-6">
                    <div class="form-group hidden" id="grupo_motivo_otro">
                        <label for="motivo_otro">Especifique el motivo *</label>
                        <input type="text" name="motivo_otro" id="motivo_otro"
                               class="form-control" maxlength="200">
                    </div>
                </div>
            </div>

            <div class="form-group">
                <label for="observaciones">Observaciones</label>
                <textarea name="observaciones" id="observaciones"
                          class="form-control" maxlength="500"></textarea>
            </div>

            <div class="text-right mt-2">
                <a href="<?= BASE_URL ?>/solicitudes" class="btn btn-outline mr-1">Cancelar</a>
                <button type="submit" class="btn btn-success btn-lg" id="btn_guardar" disabled>
                    Guardar Solicitud
                </button>
            </div>
        </form>
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
                                <option value="CC">CC - Cédula</option>
                                <option value="CE">CE - Cédula Extranjería</option>
                                <option value="TI">TI - Tarjeta Identidad</option>
                                <option value="PA">PA - Pasaporte</option>
                                <option value="NIT">NIT</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Documento *</label>
                            <input type="text" name="documento" id="p_documento"
                                   class="form-control" maxlength="20">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Primer Nombre *</label>
                            <input type="text" name="primer_nombre" id="p_primer_nombre"
                                   class="form-control" required maxlength="50">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Segundo Nombre</label>
                            <input type="text" name="segundo_nombre" id="p_segundo_nombre"
                                   class="form-control" maxlength="50">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Primer Apellido *</label>
                            <input type="text" name="primer_apellido" id="p_primer_apellido"
                                   class="form-control" required maxlength="50">
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group">
                            <label>Segundo Apellido</label>
                            <input type="text" name="segundo_apellido" id="p_segundo_apellido"
                                   class="form-control" maxlength="50">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-6">
                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" id="p_telefono"
                                   class="form-control" maxlength="20">
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

// Stub para buscarManual - scanner.js lo sobreescribe al cargar
function buscarManual() {
    var input = document.getElementById('scanner_input');
    if (input && input.value.trim().length >= 3) {
        window.procesarEntradaScanner && window.procesarEntradaScanner(input.value.trim());
    } else {
        swalWarning('Escriba al menos 3 digitos del documento');
    }
}
</script>
<script src="<?= BASE_URL ?>/js/scanner.js"></script>
<script src="<?= BASE_URL ?>/js/solicitud.js"></script>
