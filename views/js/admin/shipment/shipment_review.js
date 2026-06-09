(function (window, document) {
    'use strict';

    // ─── Stato interno ─────────────────────────────────────────────────────
    var _config = {};

    // ─── Init ──────────────────────────────────────────────────────────────
    function init() {
        var cfg = window.SQ_Review;
        if (!cfg) {
            console.warn('[SQ] window.SQ_Review non trovato. JS non inizializzato.');
            return;
        }
        _config = cfg;
        bindEvents();
    }

    // ─── Binding eventi ────────────────────────────────────────────────────
    function bindEvents() {
        var insuranceToggle = document.getElementById('sq-insurance-toggle');
        if (insuranceToggle) {
            insuranceToggle.addEventListener('change', sqToggleInsurance);
        }

        var valueInput = document.getElementById('sq-insured-value');
        if (valueInput) {
            valueInput.addEventListener('input', sqUpdateInsuranceSummary);
            valueInput.addEventListener('change', sqUpdateInsuranceSummary); // ← copia-incolla e autofill
        }
    }

    // ─── Clamp ────────────────────────────────────────────────────────────

    /**
     * Clamp del valore al range [min, max] definiti sull'input stesso.
     * Aggiunge feedback visivo temporaneo se il valore viene corretto.
     */
    function sqClampInsuranceValue(input) {
        var max = parseFloat(input.dataset.max) || 0;
        var min = parseFloat(input.min) || 0.01;
        var val = parseFloat(input.value);

        if (isNaN(val) || val < min) {
            val = min;
        } else if (val > max) {
            val = max;
            input.classList.add('sq-input-clamped');
            setTimeout(function () {
                input.classList.remove('sq-input-clamped');
            }, 1500);
        }

        var formatted = val.toFixed(2);
        if (input.value !== formatted) {
            input.value = formatted;
        }
    }

    // ─── Funzioni pubbliche ────────────────────────────────────────────────

    /**
     * Toggling visibilità sezione assicurazione.
     * Legge lo stato direttamente dal DOM.
     */
    function sqToggleInsurance() {
        var toggle = document.getElementById('sq-insurance-toggle');
        var section = document.getElementById('sq-insurance-section');
        if (!toggle || !section) return;

        section.style.display = toggle.checked ? 'block' : 'none';
        sqUpdateInsuranceSummary();
    }

    /**
     * Clamp + aggiornamento riepilogo copertura assicurativa.
     * Unica funzione — niente duplicati.
     */
    function sqUpdateInsuranceSummary() {
        var input = document.getElementById('sq-insured-value');
        var summaryEl = document.getElementById('sq-insurance-summary');
        if (!input || !summaryEl) return;

        sqClampInsuranceValue(input); // ← clamp prima di leggere il valore

        var val = parseFloat(input.value) || 0;
        var max = parseFloat(input.dataset.max) || 0;
        var currency = _config.currency_sign || '€';

        if (val > 0 && max > 0) {
            var pct = Math.min((val / max) * 100, 100).toFixed(1);
            summaryEl.textContent = 'Copertura: ' + val.toFixed(2) + currency
                + ' su ' + max.toFixed(2) + currency
                + ' (' + pct + '%)';
            summaryEl.style.color = val >= max ? '#e74c3c' : '#5a6a7a';
        } else {
            summaryEl.textContent = '';
            summaryEl.style.color = '';
        }
    }

    // ─── Esposizione globale ───────────────────────────────────────────────
    // Esposti per compatibilità con attributi inline oninput/onchange nel .tpl
    window.sqToggleInsurance = sqToggleInsurance;
    window.sqUpdateInsuranceSummary = sqUpdateInsuranceSummary;

    // ─── Bootstrap ────────────────────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

}(window, document));