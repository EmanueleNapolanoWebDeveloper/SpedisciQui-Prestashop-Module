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
        }
    }

    // ─── Funzioni pubbliche ────────────────────────────────────────────────

    /**
     * Toggling visibilità sezione assicurazione.
     * Legge lo stato direttamente dal DOM — nessun parametro, nessun this.
     * Funziona sia da addEventListener che da onchange="" inline.
     */
    function sqToggleInsurance() {
        var toggle = document.getElementById('sq-insurance-toggle'); // ← legge dal DOM
        var section = document.getElementById('sq-insurance-section');
        if (!toggle || !section) return;

        section.style.display = toggle.checked ? 'block' : 'none';
        sqUpdateInsuranceSummary();
    }

    /**
     * Aggiorna il riepilogo del costo assicurazione.
     */
    function sqUpdateInsuranceSummary() {
        var valueInput = document.getElementById('sq-insured-value');
        var summaryEl = document.getElementById('sq-insurance-summary');
        if (!summaryEl) return;

        var value = valueInput ? parseFloat(valueInput.value) || 0 : 0;
        var currency = _config.currency_sign || '€';

        summaryEl.textContent = currency + ' ' + value.toFixed(2);
    }

    // ─── Esposizione globale ───────────────────────────────────────────────
    window.sqToggleInsurance = sqToggleInsurance;
    window.sqUpdateInsuranceSummary = sqUpdateInsuranceSummary;

    // ─── Bootstrap ────────────────────────────────────────────────────────
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

}(window, document));