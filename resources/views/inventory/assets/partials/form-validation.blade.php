<style>
    .form-control.is-invalid,
    select.is-invalid,
    textarea.is-invalid {
        border-color: var(--status-down, #ef4444) !important;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.12);
    }
    .field-error-message {
        margin-top: 0.35rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: var(--status-down, #ef4444);
        line-height: 1.35;
    }
    .asset-form-error-summary {
        background: #fef2f2;
        border: 1px solid rgba(239, 68, 68, 0.25);
        color: #b91c1c;
        padding: 1rem 1.25rem;
        border-radius: 10px;
        margin-bottom: 1.25rem;
    }
    .asset-form-error-summary ul {
        margin: 0.5rem 0 0;
        padding-left: 1.15rem;
    }
    .asset-form-error-summary li {
        margin-bottom: 0.25rem;
        font-size: 0.85rem;
    }
</style>
@php
    $assetFormRequiredFields = [
        'asset_name',
        'model_number',
        'serial_number',
        'management_ip',
        'customer_id',
        'asset_type_id',
        'asset_category_id',
        'status_id',
        'criticality_id',
        'manufacturer_id',
        'site_location_id',
        'sla_policy_id',
        'service_name_id',
    ];
    $assetFormServerErrors = $errors->getMessages();
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('asset-create-form');
    if (!form) {
        return;
    }

    const requiredFields = @json($assetFormRequiredFields);

    const serverErrors = @json($assetFormServerErrors);

    function isValidIp(value) {
        const parts = value.split('.');
        if (parts.length !== 4) {
            return false;
        }
        return parts.every(function (part) {
            if (part === '' || !/^\d+$/.test(part)) {
                return false;
            }
            const num = Number(part);
            return num >= 0 && num <= 255;
        });
    }

    function isFieldValid(input) {
        const name = input.name;
        if (!name) {
            return true;
        }

        const tag = input.tagName.toLowerCase();
        const type = (input.type || '').toLowerCase();

        if (type === 'file') {
            return !input.files.length || input.files[0].size <= 20 * 1024 * 1024;
        }

        if (type === 'hidden' && name === 'customer_id') {
            return input.value !== '';
        }

        if (tag === 'select') {
            if (requiredFields.indexOf(name) !== -1 || input.hasAttribute('required')) {
                return input.value !== '';
            }
            return true;
        }

        if (name === 'management_ip') {
            const value = input.value.trim();
            return value !== '' && isValidIp(value);
        }

        if (requiredFields.indexOf(name) !== -1 || input.hasAttribute('required')) {
            return input.value.trim() !== '';
        }

        if (name.endsWith('_id')) {
            return input.value !== '';
        }

        return input.value.trim() !== '' || !input.hasAttribute('required');
    }

    function clearFieldError(input) {
        const name = input.name;
        if (!name) {
            return;
        }

        input.classList.remove('is-invalid');
        input.removeAttribute('aria-invalid');

        const group = input.closest('.form-group');
        if (group) {
            group.querySelectorAll('.field-error-message').forEach(function (el) {
                el.remove();
            });
        }

        const summary = document.getElementById('asset-form-error-summary');
        if (summary) {
            summary.querySelectorAll('[data-error-field="' + name + '"]').forEach(function (el) {
                el.remove();
            });

            const remaining = summary.querySelectorAll('li[data-error-field]');
            if (!remaining.length) {
                summary.remove();
            }
        }
    }

    function markFieldError(input, message) {
        const name = input.name;
        if (!name || !message) {
            return;
        }

        input.classList.add('is-invalid');
        input.setAttribute('aria-invalid', 'true');

        const group = input.closest('.form-group');
        if (group && !group.querySelector('.field-error-message')) {
            const el = document.createElement('div');
            el.className = 'field-error-message';
            el.setAttribute('data-field-error', name);
            el.textContent = message;
            group.appendChild(el);
        }
    }

    function handleFieldChange(input) {
        if (!input.classList.contains('is-invalid') && !input.closest('.form-group')?.querySelector('.field-error-message')) {
            return;
        }

        if (isFieldValid(input)) {
            clearFieldError(input);
        }
    }

    Object.entries(serverErrors).forEach(function (entry) {
        const field = entry[0];
        const messages = entry[1];
        const message = messages[0];
        if (!message) {
            return;
        }

        const input = form.querySelector('[name="' + field + '"]');
        if (!input) {
            return;
        }

        markFieldError(input, message);
    });

    const firstInvalid = form.querySelector('.is-invalid');
    if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
        firstInvalid.focus({ preventScroll: true });
    }

    form.querySelectorAll('input, select, textarea').forEach(function (input) {
        const events = input.type === 'file' || input.tagName.toLowerCase() === 'select'
            ? ['change']
            : ['input', 'change'];

        events.forEach(function (eventName) {
            input.addEventListener(eventName, function () {
                handleFieldChange(input);
            });
        });
    });
});
</script>
