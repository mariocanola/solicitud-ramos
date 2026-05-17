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
 * Antes de abrir cualquier modal de SweetAlert quitamos el foco del elemento activo
 * y enganchamos un willOpen para asegurarnos justo antes del aria-hidden.
 * Evita el warning "Blocked aria-hidden on an element because its descendant retained focus".
 */
if (typeof Swal !== 'undefined' && !Swal.__blurPatched) {
    var __blurActive = function () {
        var el = document.activeElement;
        if (el && el !== document.body && typeof el.blur === 'function') {
            el.blur();
        }
    };
    // Reemplaza aria-hidden por inert en los hermanos del modal Swal.
    // inert bloquea foco e interaccion, asi que es lo que necesitabamos sin el warning aria.
    var __swapAriaForInert = function () {
        document.querySelectorAll('[aria-hidden="true"]').forEach(function (el) {
            if (el.classList && el.classList.contains('swal2-container')) return;
            if (el.closest && el.closest('.swal2-container')) return;
            el.removeAttribute('aria-hidden');
            el.setAttribute('inert', '');
            el.__swalInert = true;
        });
    };
    var __restoreFromInert = function () {
        document.querySelectorAll('[inert]').forEach(function (el) {
            if (el.__swalInert) {
                el.removeAttribute('inert');
                delete el.__swalInert;
            }
        });
    };

    // Observa el body para reaccionar al instante en que Swal anada aria-hidden.
    // Diferimos hasta que document.body exista (este script se carga en <head>).
    var __startAriaObserver = function () {
        if (window.__swalAriaObs || typeof MutationObserver === 'undefined' || !document.body) return;
        var __obs = new MutationObserver(function (muts) {
            for (var i = 0; i < muts.length; i++) {
                if (muts[i].attributeName === 'aria-hidden') {
                    __swapAriaForInert();
                    break;
                }
            }
        });
        __obs.observe(document.body, { attributes: true, subtree: true, attributeFilter: ['aria-hidden'] });
        window.__swalAriaObs = __obs;
    };
    if (document.body) {
        __startAriaObserver();
    } else {
        document.addEventListener('DOMContentLoaded', __startAriaObserver);
    }

    var __origFire = Swal.fire.bind(Swal);
    Swal.fire = function () {
        __blurActive();
        var args = Array.prototype.slice.call(arguments);
        if (args.length === 1 && typeof args[0] === 'object' && args[0] !== null) {
            var userWillOpen = args[0].willOpen;
            var userDidClose = args[0].didClose;
            args[0].willOpen = function (popup) {
                __blurActive();
                __swapAriaForInert();
                if (typeof userWillOpen === 'function') userWillOpen(popup);
            };
            args[0].didClose = function () {
                __restoreFromInert();
                if (typeof userDidClose === 'function') userDidClose();
            };
        }
        return __origFire.apply(Swal, args);
    };
    Swal.__blurPatched = true;
}

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
