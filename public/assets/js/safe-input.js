/**
 * Client-side guard: block special characters; allow ñ/Ñ where configured.
 */
(function (global) {
    'use strict';

    const PATTERNS = {
        person_name: /[^a-zA-ZñÑ0-9\s\-.'’]/g,
        text: /[^a-zA-ZñÑ0-9\s\-.'’]/g,
        location: /[^a-zA-ZñÑ0-9\s\-.'’]/g,
        address: /[^a-zA-ZñÑ0-9\s\-.'#,/]/g,
        description: /[^a-zA-ZñÑ0-9\s\-.'”,:;!?()]/g,
        reference: /[^a-zA-Z0-9\-]/g,
        postal_code: /[^a-zA-Z0-9\s\-]/g,
        nicotine: /[^a-zA-Z0-9\s\-%\.]/g,
        spec: /[^a-zA-Z0-9\s\-.'\/]/g,
    };

    function cleanValue(value, type) {
        const pattern = PATTERNS[type] || PATTERNS.text;
        return String(value ?? '').replace(pattern, '');
    }

    function bindField(field, type) {
        if (!field || field.dataset.safeInputBound === '1') {
            return;
        }
        field.dataset.safeInputBound = '1';
        field.dataset.safeInputType = type;

        const scrub = () => {
            const cleaned = cleanValue(field.value, type);
            if (cleaned !== field.value) {
                const pos = field.selectionStart;
                field.value = cleaned;
                if (typeof pos === 'number') {
                    field.setSelectionRange(pos - 1, pos - 1);
                }
            }
        };

        field.addEventListener('input', scrub);
        field.addEventListener('paste', (event) => {
            event.preventDefault();
            const pasted = (event.clipboardData || global.clipboardData).getData('text');
            const start = field.selectionStart ?? field.value.length;
            const end = field.selectionEnd ?? field.value.length;
            const merged = field.value.slice(0, start) + pasted + field.value.slice(end);
            field.value = cleanValue(merged, type);
            field.dispatchEvent(new Event('input', { bubbles: true }));
        });
    }

    function bindSelector(selector, type) {
        document.querySelectorAll(selector).forEach((field) => bindField(field, type));
    }

    function initSafeInputs(root) {
        const scope = root && root.querySelectorAll ? root : document;
        scope.querySelectorAll('[data-safe-input]').forEach((field) => {
            bindField(field, field.getAttribute('data-safe-input') || 'text');
        });
    }

    global.SafeInput = {
        cleanValue,
        bindField,
        bindSelector,
        initSafeInputs,
        PATTERNS,
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => initSafeInputs(document));
    } else {
        initSafeInputs(document);
    }
})(window);
