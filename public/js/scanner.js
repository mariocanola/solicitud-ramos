/**
 * Scanner.js - Barcode scanner detection and handling
 * Detects fast input (barcode scanner) vs manual typing
 */

(function() {
    var scannerInput = document.getElementById('scanner_input');
    if (!scannerInput) return;

    var buffer = '';
    var lastKeyTime = 0;
    var THRESHOLD = 100; // ms between keystrokes for scanner detection
    var MIN_LENGTH = 5;  // minimum chars to consider valid scanner input

    scannerInput.addEventListener('keydown', function(e) {
        var now = Date.now();

        if (e.key === 'Enter') {
            e.preventDefault();

            if (buffer.length >= MIN_LENGTH) {
                // Fast input detected = scanner
                procesarEntrada(buffer);
            } else if (scannerInput.value.length >= MIN_LENGTH) {
                // Manual input - use the input value
                procesarEntrada(scannerInput.value.trim());
            }

            buffer = '';
            return;
        }

        // Reset buffer if too much time between keys (manual typing)
        if (now - lastKeyTime > THRESHOLD && buffer.length > 0) {
            buffer = '';
        }

        // Only add printable characters
        if (e.key.length === 1) {
            buffer += e.key;
        }

        lastKeyTime = now;
    });

    // Manual search button
    window.buscarManual = function() {
        var val = scannerInput.value.trim();
        if (val.length === 0) {
            setStatus('Escriba un número de documento', 'danger');
            scannerInput.focus();
            return;
        }
        if (val.length < 3) {
            setStatus('El documento debe tener al menos 3 caracteres', 'danger');
            scannerInput.focus();
            return;
        }
        procesarEntrada(val);
    };

    function procesarEntrada(raw) {
        // Clean: extract only digits
        var documento = raw.replace(/[^0-9]/g, '');

        // Handle PDF417 format (>15 digits)
        if (documento.length > 15) {
            documento = documento.substring(2, 12).replace(/^0+/, '');
        } else {
            documento = documento.replace(/^0+/, '');
        }

        if (documento.length < 3) {
            setStatus('Documento no válido. Escriba al menos 3 dígitos.', 'danger');
            return;
        }

        scannerInput.value = documento;
        setStatus('Buscando...', 'info');
        buscarPersona(documento);
    }

    function buscarPersona(documento) {
        fetch(BASE_URL + '/personas/buscar?documento=' + encodeURIComponent(documento), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success && data.data) {
                mostrarPersona(data.data);
                setStatus('Persona encontrada', 'success');
            } else {
                setStatus('Persona no encontrada. Complete el registro.', 'warning');
                abrirModalPersona(documento);
            }
        })
        .catch(function(err) {
            console.error('Error buscando persona:', err);
            setStatus('Persona no encontrada. Complete el registro.', 'warning');
            abrirModalPersona(documento);
        });
    }

    function setStatus(msg, type) {
        var el = document.getElementById('scanner_status');
        var colors = {
            success: 'var(--success)',
            danger: 'var(--danger)',
            warning: 'var(--warning)',
            info: 'var(--secondary)'
        };
        el.style.color = colors[type] || '#333';
        el.textContent = msg;
    }

    // Expose for external use
    window.buscarPersona = buscarPersona;
    window.procesarEntradaScanner = procesarEntrada;
    window.buscarManual = window.buscarManual || buscarManual;
})();
