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

    ajaxPost(BASE_URL + '/personas/crear', formData, function(data) {
        if (data.success) {
            cerrarModalPersona();
            mostrarPersona(data.data);
            document.getElementById('scanner_status').textContent = 'Persona creada y seleccionada';
            document.getElementById('scanner_status').style.color = 'var(--success)';
        } else {
            swalError(data.message || 'Error al crear persona');
        }
    });
}
