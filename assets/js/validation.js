/**
 * validation.js
 */

// Toggle password visibility
function togglePassword(inputId, toggleBtnId) {
    const input = document.getElementById(inputId);
    const btn   = document.getElementById(toggleBtnId);
    if (!input || !btn) return;

    const iconShow = '👁️';
    const iconHide = '🙈';

    btn.addEventListener('click', function () {
        if (input.type === 'password') {
            input.type = 'text';
            btn.textContent = iconHide;
            btn.title = 'Sembunyikan password';
        } else {
            input.type = 'password';
            btn.textContent = iconShow;
            btn.title = 'Tampilkan password';
        }
    });
}

// Tampilkan error inline
function showError(elementId, message) {
    const el = document.getElementById(elementId);
    if (el) {
        el.textContent = message;
        el.style.display = 'block';
    }
}

// Sembunyikan error
function hideError(elementId) {
    const el = document.getElementById(elementId);
    if (el) el.style.display = 'none';
}

// Reset semua error dalam form
function clearFormErrors(form) {
    form.querySelectorAll('.error-msg, .invalid-feedback').forEach(el => {
        el.style.display = 'none';
        el.textContent = '';
    });
    form.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
    });
}

function validateRequired(value) {
    return value.trim() !== '';
}

function validateMin(value, min) {
    const num = parseFloat(value);
    return !isNaN(num) && num >= min;
}

// Auto-attach confirm() ke tombol hapus
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.btn-hapus').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            const nama  = this.getAttribute('data-nama') || 'item ini';
            const extra = this.getAttribute('data-extra') || '';
            let pesan   = 'Yakin ingin menghapus "' + nama + '"?\nData yang sudah dihapus tidak bisa dikembalikan.';
            if (extra) pesan += '\n\n' + extra;

            if (!confirm(pesan)) {
                e.preventDefault();
            }
        });
    });

    // Auto-dismiss alert setelah 5 detik
    const alerts = document.querySelectorAll('.alert-dismissible');
    if (alerts.length > 0) {
        setTimeout(function () {
            alerts.forEach(function (alertEl) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                    var bsAlert = bootstrap.Alert.getOrCreateInstance(alertEl);
                    bsAlert.close();
                } else {
                    alertEl.style.display = 'none';
                }
            });
        }, 5000);
    }
});