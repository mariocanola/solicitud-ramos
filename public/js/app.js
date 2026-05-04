/**
 * App.js - Global utilities
 */

// Auto-dismiss alerts after 5 seconds
document.addEventListener('DOMContentLoaded', function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            alert.style.transition = 'opacity 0.5s';
            alert.style.opacity = '0';
            setTimeout(function() { alert.remove(); }, 500);
        }, 5000);
    });
});

/**
 * SweetAlert2 helpers
 */
var Toast = typeof Swal !== 'undefined' ? Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true
}) : null;

function swalError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: message,
        confirmButtonColor: '#582C83'
    });
}

function swalSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Correcto',
        text: message,
        confirmButtonColor: '#582C83',
        timer: 2000,
        timerProgressBar: true
    });
}

function swalWarning(message) {
    Swal.fire({
        icon: 'warning',
        title: 'Atencion',
        text: message,
        confirmButtonColor: '#582C83'
    });
}

function swalConfirm(title, text, callback) {
    Swal.fire({
        title: title,
        text: text,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Si, continuar',
        cancelButtonText: 'Cancelar'
    }).then(function(result) {
        if (result.isConfirmed) callback();
    });
}

/**
 * Safe alert display (XSS-safe, uses textContent instead of innerHTML)
 */
function showAlert(container, type, message) {
    if (typeof container === 'string') {
        container = document.getElementById(container);
    }
    if (!container) return;
    var div = document.createElement('div');
    div.className = 'alert alert-' + type;
    div.textContent = message;
    container.innerHTML = '';
    container.appendChild(div);
}

/**
 * Update CSRF token globally after AJAX response
 */
function updateCsrfToken(data) {
    if (data && data.csrf_token) {
        // Update global variable
        if (typeof CSRF_TOKEN !== 'undefined') {
            window.CSRF_TOKEN = data.csrf_token;
        }
        // Update all hidden CSRF fields in forms
        var fields = document.querySelectorAll('input[name="_csrf_token"]');
        fields.forEach(function(f) { f.value = data.csrf_token; });
    }
}

/**
 * AJAX helper
 */
function ajaxGet(url, callback) {
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        updateCsrfToken(data);
        callback(data);
    })
    .catch(function(err) {
        console.error('AJAX Error:', err);
    });
}

function ajaxPost(url, formData, callback) {
    fetch(url, {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) {
        if (!r.ok && r.status === 403) {
            throw new Error('Token de seguridad expirado. Recargue la página.');
        }
        return r.json();
    })
    .then(function(data) {
        updateCsrfToken(data);
        callback(data);
    })
    .catch(function(err) {
        console.error('AJAX Error:', err);
        swalError(err.message || 'Error de conexion. Intente de nuevo.');
    });
}
