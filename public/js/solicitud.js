/**
 * Solicitud.js - Form logic for creating solicitudes
 */

(function() {
    // ---- Motivo "Otro" toggle ----
    var selectMotivo = document.getElementById('id_motivo');
    var grupoMotivoOtro = document.getElementById('grupo_motivo_otro');
    var inputMotivoOtro = document.getElementById('motivo_otro');

    if (selectMotivo) {
        selectMotivo.addEventListener('change', function() {
            var option = this.options[this.selectedIndex];
            var requiere = option.getAttribute('data-requiere-detalle') === '1';
            if (requiere) {
                grupoMotivoOtro.classList.remove('hidden');
                inputMotivoOtro.required = true;
            } else {
                grupoMotivoOtro.classList.add('hidden');
                inputMotivoOtro.required = false;
                inputMotivoOtro.value = '';
            }
        });
    }

    // ---- Area "Otro" toggle ----
    var selectArea = document.getElementById('id_area_solicitud');
    var grupoAreaOtro = document.getElementById('grupo_area_otro');
    var inputAreaOtro = document.getElementById('area_otro');

    if (selectArea) {
        selectArea.addEventListener('change', function() {
            var text = this.options[this.selectedIndex].textContent.trim();
            if (text === 'Otro') {
                grupoAreaOtro.classList.remove('hidden');
                inputAreaOtro.required = true;
            } else {
                grupoAreaOtro.classList.add('hidden');
                inputAreaOtro.required = false;
                inputAreaOtro.value = '';
            }
        });
    }

    // ---- Form validation ----
    var form = document.getElementById('form_solicitud');
    if (form) {
        form.addEventListener('submit', function(e) {
            var personaId = document.getElementById('persona_id').value;
            if (!personaId) {
                e.preventDefault();
                swalWarning('Debe escanear o buscar una persona primero.');
                return false;
            }
        });
    }
})();

/**
 * Show person data in the info card and fill hidden fields
 */
function mostrarPersona(persona) {
    document.getElementById('persona_id').value = persona.id;
    document.getElementById('persona_nombre').textContent =
        persona.nombre_completo || (persona.primer_nombre + ' ' + persona.primer_apellido);
    document.getElementById('persona_doc').textContent =
        persona.tipo_documento + ' ' + persona.documento;
    document.getElementById('persona_sede').textContent = persona.sede_nombre || '';
    document.getElementById('persona_tel').textContent = persona.telefono || 'N/A';

    document.getElementById('persona_info').classList.remove('hidden');

    // Fijar la sede de la persona (no se puede cambiar)
    var sedeSelect = document.getElementById('id_sede');
    if (sedeSelect && persona.id_sede) {
        sedeSelect.value = persona.id_sede;
        sedeSelect.disabled = true;
        // Agregar hidden para que se envíe el valor (disabled no se envía en forms)
        var hiddenSede = document.getElementById('id_sede_hidden');
        if (!hiddenSede) {
            hiddenSede = document.createElement('input');
            hiddenSede.type = 'hidden';
            hiddenSede.name = 'id_sede';
            hiddenSede.id = 'id_sede_hidden';
            sedeSelect.parentNode.appendChild(hiddenSede);
        }
        hiddenSede.value = persona.id_sede;

        // Mostrar nota
        var nota = document.getElementById('sede_nota');
        if (nota) {
            nota.style.display = 'block';
            nota.classList.remove('hidden');
        }
    }

    // Enable submit button
    var btnGuardar = document.getElementById('btn_guardar');
    btnGuardar.disabled = false;
    btnGuardar.textContent = 'Guardar Solicitud';
    btnGuardar.title = '';
}

/**
 * Open modal to create a new person
 */
function abrirModalPersona(documento) {
    document.getElementById('p_documento').value = documento || '';
    document.getElementById('modal_persona').classList.add('show');
    document.getElementById('p_primer_nombre').focus();
}

function cerrarModalPersona() {
    document.getElementById('modal_persona').classList.remove('show');
}

/**
 * Save new person via AJAX
 */
function guardarPersona() {
    var form = document.getElementById('form_persona');
    var formData = new FormData(form);

    // Basic validation
    var required = ['tipo_documento', 'documento', 'primer_nombre', 'primer_apellido', 'id_sede'];
    for (var i = 0; i < required.length; i++) {
        var field = required[i];
        var el = document.getElementById('p_' + field);
        if (el && !el.value.trim()) {
            swalWarning('Complete el campo: ' + el.previousElementSibling.textContent);
            el.focus();
            return;
        }
    }

    // Validación de documento según tipo
    var tipoDocEl = document.getElementById('p_tipo_documento');
    var docEl = document.getElementById('p_documento');
    var tipoDoc = tipoDocEl ? tipoDocEl.value : '';
    var documento = docEl ? docEl.value.trim() : '';
    
    if (!documento) {
        swalWarning('El documento es requerido');
        docEl.focus();
        return;
    }
    
    // Validar según tipo de documento
    if (tipoDoc === 'CC' || tipoDoc === 'TI') {
        if (!/^\d+$/.test(documento)) {
            swalWarning('Para Cédula y Tarjeta de Identidad, el documento debe contener solo números');
            docEl.focus();
            return;
        }
        if (documento.length < 5 || documento.length > 10) {
            swalWarning('El documento debe tener entre 5 y 10 dígitos');
            docEl.focus();
            return;
        }
    } else if (tipoDoc === 'NIT') {
        if (!/^\d+$/.test(documento)) {
            swalWarning('El NIT debe contener solo números');
            docEl.focus();
            return;
        }
        if (documento.length < 8 || documento.length > 15) {
            swalWarning('El NIT debe tener entre 8 y 15 dígitos');
            docEl.focus();
            return;
        }
    } else if (tipoDoc === 'CE' || tipoDoc === 'PA') {
        if (documento.length < 5 || documento.length > 20) {
            swalWarning('El documento debe tener entre 5 y 20 caracteres');
            docEl.focus();
            return;
        }
    }

    // Mostrar estado de guardando
    var btnGuardar = form.parentElement.querySelector('.modal-footer button[onclick="guardarPersona()"]');
    var originalText = btnGuardar.textContent;
    btnGuardar.disabled = true;
    btnGuardar.textContent = 'Guardando...';

    ajaxPost(BASE_URL + '/personas/crear', formData, function(data) {
        btnGuardar.disabled = false;
        btnGuardar.textContent = originalText;

        if (data.success) {
            cerrarModalPersona();
            
            // Mostrar información de la persona creada
            mostrarPersona(data.data);
            document.getElementById('scanner_status').textContent = 'Persona creada y seleccionada';
            document.getElementById('scanner_status').style.color = 'var(--success)';
            
            // Actualizar indicador visual
            if (window.onScannerStatusChange) {
                window.onScannerStatusChange('success', 'Persona creada');
            }
            
            // Llamar a función global para manejar persona encontrada (que mostrará el formulario)
            if (window.onPersonaEncontrada) {
                window.onPersonaEncontrada(data.data);
            }
        } else {
            if (data.errors && Object.keys(data.errors).length > 0) {
                var errorMsg = 'Por favor corrija los siguientes errores:\\n\\n';
                for (var field in data.errors) {
                    errorMsg += '- ' + data.errors[field] + '\\n';
                }
                swalWarning(errorMsg);
            } else {
                swalError(data.message || 'Error al crear persona');
            }
        }
    }).catch(function() {
        btnGuardar.disabled = false;
        btnGuardar.textContent = originalText;
    });
}

// Helper local: setStatus solo existe dentro del IIFE de scanner.js. Aquí lo llamamos
// vía window.setStatus si la vista lo expuso, o caemos en un fallback que actualiza
// directamente #scanner_status. Sin esto, una ReferenceError abortaba el flujo después
// de mostrar la persona y onPersonaEncontrada nunca se ejecutaba.
function _setStatus(msg, type) {
    if (typeof window.setStatus === 'function') { window.setStatus(msg, type); return; }
    var el = document.getElementById('scanner_status');
    if (!el) return;
    var colors = { success: '#1B873F', danger: '#B42318', error: '#B42318', warning: '#B54708', info: '#175CD3' };
    el.style.color = colors[type] || '#4A4651';
    el.textContent = msg;
}

function buscarPersona(documento) {
    // Usar /solicitudes/buscar-persona en lugar de /personas/buscar porque ese endpoint
    // incluye el flag ya_solicito_mes que onPersonaEncontrada usa para bloquear duplicados.
    var ctrl = new AbortController();
    var timer = setTimeout(function() { ctrl.abort(); }, 8000);
    fetch(BASE_URL + '/solicitudes/buscar-persona?documento=' + encodeURIComponent(documento), {
        signal: ctrl.signal,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { clearTimeout(timer); return r.json(); })
    .then(function(data) {
        if (data.success && data.data) {
            var p = data.data;
            var periodoLabel = p.periodo_label || 'mes';

            // Bloquear duplicado en el periodo actual antes de mostrar cualquier dato.
            if (p.ya_solicito_periodo || p.ya_solicito_mes) {
                var msg = 'Ya solicitó ramo ' + periodoLabel;
                _setStatus(msg, 'error');
                if (window.onScannerStatusChange) window.onScannerStatusChange('error', msg);
                if (typeof alertarSolicitudExistente === 'function') alertarSolicitudExistente(p);
                return;
            }

            // Bloquear si la sede esta sin cupos disponibles.
            if (p.sin_cupo) {
                _setStatus('Sin cupos disponibles', 'error');
                if (window.onScannerStatusChange) window.onScannerStatusChange('error', 'Sin cupos disponibles');
                if (typeof alertarSinCupo === 'function') alertarSinCupo(p);
                return;
            }

            // Persona encontrada - mostrar directamente el formulario
            mostrarPersona(p);
            _setStatus('Persona encontrada', 'success');

            // Actualizar indicador visual
            if (window.onScannerStatusChange) {
                window.onScannerStatusChange('success', 'Persona encontrada');
            }

            // Llamar a función global para manejar persona encontrada
            if (window.onPersonaEncontrada) {
                window.onPersonaEncontrada(p);
            }
        } else {
            // Persona no encontrada - mostrar modal para crearla
            _setStatus('Persona no encontrada. Complete el registro.', 'warning');

            // Actualizar indicador visual
            if (window.onScannerStatusChange) {
                window.onScannerStatusChange('error', 'Persona no encontrada');
            }

            // Abrir modal para crear nueva persona
            abrirModalPersona(documento);
        }
    })
    .catch(function(err) {
        clearTimeout(timer);
        var msg = err.name === 'AbortError'
            ? 'El servidor tardó mucho. Intente nuevamente.'
            : 'Error de conexión. Intente nuevamente.';
        _setStatus(msg, 'error');
        if (window.onScannerStatusChange) {
            window.onScannerStatusChange('error', msg);
        }
    });
}
