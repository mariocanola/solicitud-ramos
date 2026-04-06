/**
 * FormValidator - Client-side validation library
 * Validates on blur/change, shows inline error messages with red borders.
 */
function FormValidator(formId, rules) {
    this.form = document.getElementById(formId);
    if (!this.form) return;
    this.rules = rules || {};
    this.errors = {};
    this._init();
}

FormValidator.prototype._init = function() {
    var self = this;
    Object.keys(this.rules).forEach(function(fieldName) {
        var field = self.form.querySelector('[name="' + fieldName + '"]');
        if (!field) return;

        var eventName = (field.tagName === 'SELECT' || field.type === 'checkbox' || field.type === 'color') ? 'change' : 'blur';
        field.addEventListener(eventName, function() {
            self._validateField(fieldName, field);
        });
        // Also validate on input for better UX (clear error as user types)
        field.addEventListener('input', function() {
            if (self.errors[fieldName]) {
                self._validateField(fieldName, field);
            }
        });
    });
};

FormValidator.prototype._validateField = function(fieldName, field) {
    var value = field.value;
    var fieldRules = this.rules[fieldName];
    var error = null;

    for (var i = 0; i < fieldRules.length; i++) {
        var rule = fieldRules[i];
        var result = rule.test(value, this.form);
        if (result !== true) {
            error = result; // The error message string
            break;
        }
    }

    if (error) {
        this.errors[fieldName] = error;
        this._showError(field, error);
    } else {
        delete this.errors[fieldName];
        this._clearError(field);
    }
    return !error;
};

FormValidator.prototype._showError = function(field, message) {
    field.classList.remove('is-valid');
    field.classList.add('is-invalid');
    // Find or create feedback element
    var parent = field.closest('.form-group') || field.parentElement;
    var feedback = parent.querySelector('.invalid-feedback');
    if (!feedback) {
        feedback = document.createElement('div');
        feedback.className = 'invalid-feedback';
        parent.appendChild(feedback);
    }
    feedback.textContent = message;
    feedback.style.display = 'block';
};

FormValidator.prototype._clearError = function(field) {
    field.classList.remove('is-invalid');
    field.classList.add('is-valid');
    var parent = field.closest('.form-group') || field.parentElement;
    var feedback = parent.querySelector('.invalid-feedback');
    if (feedback) {
        feedback.style.display = 'none';
        feedback.textContent = '';
    }
};

FormValidator.prototype.validateAll = function() {
    var self = this;
    var valid = true;
    Object.keys(this.rules).forEach(function(fieldName) {
        var field = self.form.querySelector('[name="' + fieldName + '"]');
        if (!field) return;
        if (!self._validateField(fieldName, field)) {
            valid = false;
        }
    });
    return valid;
};

FormValidator.prototype.isValid = function() {
    return Object.keys(this.errors).length === 0;
};

FormValidator.prototype.reset = function() {
    var self = this;
    this.errors = {};
    this.form.querySelectorAll('.is-invalid, .is-valid').forEach(function(el) {
        el.classList.remove('is-invalid', 'is-valid');
    });
    this.form.querySelectorAll('.invalid-feedback').forEach(function(el) {
        el.style.display = 'none';
        el.textContent = '';
    });
};

/**
 * Show server-side errors on form fields
 * @param {Object} errors - { fieldName: "error message", ... }
 */
FormValidator.prototype.showServerErrors = function(errors) {
    var self = this;
    if (!errors || typeof errors !== 'object') return;
    Object.keys(errors).forEach(function(fieldName) {
        var field = self.form.querySelector('[name="' + fieldName + '"]');
        if (field) {
            self.errors[fieldName] = errors[fieldName];
            self._showError(field, errors[fieldName]);
        }
    });
};

// === Validation rule builders ===
var V = {
    required: function(msg) {
        return { test: function(v) { return (v && v.trim() !== '') ? true : (msg || 'Este campo es requerido'); } };
    },
    minLength: function(min, msg) {
        return { test: function(v) { return (!v || v.trim().length >= min) ? true : (msg || 'Minimo ' + min + ' caracteres'); } };
    },
    maxLength: function(max, msg) {
        return { test: function(v) { return (!v || v.trim().length <= max) ? true : (msg || 'Maximo ' + max + ' caracteres'); } };
    },
    numeric: function(msg) {
        return { test: function(v) { return (!v || /^[0-9]+$/.test(v)) ? true : (msg || 'Solo se permiten numeros'); } };
    },
    integer: function(min, max, msg) {
        return { test: function(v) {
            if (!v && v !== '0') return true;
            var n = parseInt(v, 10);
            if (isNaN(n)) return msg || 'Debe ser un numero entero';
            if (min !== undefined && n < min) return msg || 'Minimo ' + min;
            if (max !== undefined && n > max) return msg || 'Maximo ' + max;
            return true;
        }};
    },
    email: function(msg) {
        return { test: function(v) { return (!v || /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(v)) ? true : (msg || 'Email invalido'); } };
    },
    alphanumeric: function(msg) {
        return { test: function(v) { return (!v || /^[a-zA-Z0-9_\-]+$/.test(v)) ? true : (msg || 'Solo letras, numeros, guiones'); } };
    },
    hexColor: function(msg) {
        return { test: function(v) { return (!v || /^#[0-9A-Fa-f]{6}$/.test(v)) ? true : (msg || 'Color hexadecimal invalido'); } };
    },
    inList: function(list, msg) {
        return { test: function(v) { return (!v || list.indexOf(v) !== -1) ? true : (msg || 'Valor no permitido'); } };
    },
    pattern: function(regex, msg) {
        return { test: function(v) { return (!v || regex.test(v)) ? true : (msg || 'Formato invalido'); } };
    }
};
