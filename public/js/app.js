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
 * AJAX helper
 */
function ajaxGet(url, callback) {
    fetch(url, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(function(r) { return r.json(); })
    .then(callback)
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
    .then(callback)
    .catch(function(err) {
        console.error('AJAX Error:', err);
        alert(err.message || 'Error de conexión. Intente de nuevo.');
    });
}
