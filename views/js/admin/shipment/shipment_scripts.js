var sqPendingFormId = null;

function sqOpenModal(shipmentId) {
    sqPendingFormId = shipmentId;
    document.getElementById('sq-shipment-modal').classList.add('active');
}

function sqCloseModal() {
    document.getElementById('sq-shipment-modal').classList.remove('active');
    sqPendingFormId = null;
}

document.getElementById('sq-modal-confirm-btn').addEventListener('click', function () {
    if (sqPendingFormId !== null) {
        var form = document.getElementById('sq-form-' + sqPendingFormId);
        if (form) {
            var btn = document.createElement('input');
            btn.type = 'hidden';
            btn.name = 'createShipment';
            btn.value = '1';
            form.appendChild(btn);
            form.submit();
        }
    }
    sqCloseModal();
});

document.getElementById('sq-shipment-modal').addEventListener('click', function (e) {
    if (e.target === this) sqCloseModal();
});




//==========================================================================
// shipment PANEL
//==========================================================================

var sqPendingFormId = null;

function sqOpenModal(shipmentId) {
    sqPendingFormId = shipmentId;
    document.getElementById('sq-shipment-modal').classList.add('active');
}

function sqCloseModal() {
    document.getElementById('sq-shipment-modal').classList.remove('active');
    sqPendingFormId = null;
}

document.getElementById('sq-modal-confirm-btn').addEventListener('click', function () {
    if (sqPendingFormId !== null) {
        var form = document.getElementById('sq-form-' + sqPendingFormId);
        if (form) {
            var btn = document.createElement('input');
            btn.type = 'hidden';
            btn.name = 'createShipment';
            btn.value = '1';
            form.appendChild(btn);
            form.submit();
        }
    }
    sqCloseModal();
});

document.getElementById('sq-shipment-modal').addEventListener('click', function (e) {
    if (e.target === this) sqCloseModal();
});


//==========================================================================
// shipment REVIEWS
//==========================================================================

(function () {

    // BASE_COST e SQ_OPTIONS sono iniettate dal template nel scope globale window

    window.sqCalcOptionCost = function (type) {
        if (!window.SQ_OPTIONS[type]) return;
        var cfg = window.SQ_OPTIONS[type];
        var input = document.getElementById('sr-val-' + type);
        if (!input) return;

        var val = Math.min(parseFloat(input.value) || 0, cfg.max);
        var cost = cfg.formula === 'flat'
            ? cfg.rate
            : parseFloat((val * cfg.rate / 100).toFixed(2));

        var preview = document.getElementById('sr-cost-' + type);
        if (preview) {
            preview.innerHTML = 'Premio stimato: <strong>€ ' + cost.toFixed(2) + '</strong>';
        }

        var sumVal = document.getElementById('sr-sum-val-' + type);
        if (sumVal) sumVal.textContent = '€ ' + cost.toFixed(2);

        sqUpdateTotal();
    };

    window.sqUpdateTotal = function () {
        var total = window.BASE_COST;
        Object.keys(window.SQ_OPTIONS).forEach(function (type) {
            var chk = document.getElementById('sr-chk-' + type);
            if (!chk || !chk.checked) return;
            var cfg = window.SQ_OPTIONS[type];
            var input = document.getElementById('sr-val-' + type);
            var val = input ? Math.min(parseFloat(input.value) || 0, cfg.max) : 0;
            total += cfg.formula === 'flat'
                ? cfg.rate
                : parseFloat((val * cfg.rate / 100).toFixed(2));
        });
        var el = document.getElementById('sr-grand-total');
        if (el) el.textContent = '€ ' + total.toFixed(2);
    };

    window.sqToggleOption = function (type) {
        var chk = document.getElementById('sr-chk-' + type);
        if (!chk || chk.disabled) return;
        chk.checked = !chk.checked;
        sqOnCheckChange(type);
    };

    window.sqOnCheckChange = function (type) {
        var chk = document.getElementById('sr-chk-' + type);
        var block = document.getElementById('sr-opt-' + type);
        var body = document.getElementById('sr-body-' + type);
        var sumRow = document.getElementById('sr-sum-row-' + type);
        if (!chk) return;
        var on = chk.checked;
        if (block) block.classList.toggle('sr-opt-active', on);
        if (body) body.classList.toggle('sr-opt-body-visible', on);
        if (sumRow) sumRow.classList.toggle('sr-sum-hidden', !on);
        sqCalcOptionCost(type);
    };

    window.sqReviewSubmit = function (e) {
        var btn = document.getElementById('sr-submit-btn');
        var icon = document.getElementById('sr-submit-icon');
        var label = document.getElementById('sr-submit-label');
        var spinner = document.getElementById('sr-spinner');
        if (btn.disabled) { e.preventDefault(); return false; }
        btn.disabled = true;
        icon.style.display = 'none';
        spinner.style.display = 'inline-block';
        label.textContent = 'Invio in corso...';
        return true;
    };

    document.addEventListener('DOMContentLoaded', function () {
        Object.keys(window.SQ_OPTIONS).forEach(sqCalcOptionCost);
        sqUpdateTotal();
    });

}());

//====================================================
// SHIPMENT DETAILS
//====================================================

(function () {

    document.addEventListener('DOMContentLoaded', function () {

        var form = document.getElementById('sq-review-form');
        if (!form) return;

        var cfg = window.SQ_Review || {};
        var shippingCost = cfg.shippingCost || 0;
        var insRate = cfg.insuranceRate || 0;
        var options = cfg.options || {};
        var i18n = cfg.i18n || {};

        // ── Assicurazione ─────────────────────────────────────────

        function toggleInsurance(checked) {
            var body = document.getElementById('sr-insurance-body');
            var sumRow = document.getElementById('sr-sum-row-insurance');
            var block = document.getElementById('sr-insurance-block');

            if (body) body.style.display = checked ? 'block' : 'none';
            if (sumRow) sumRow.classList.toggle('sr-sum-hidden', !checked);
            if (block) block.style.borderColor = checked ? '#1a6fc4' : '';

            updateInsuranceSummary();
        }

        function updateInsuranceSummary() {
            var input = document.getElementById('sr-insurance-value');
            var preview = document.getElementById('sr-insurance-cost-preview');
            var sumVal = document.getElementById('sr-sum-val-insurance');
            var chk = document.getElementById('sr-chk-insurance');

            if (!chk || !chk.checked || !input) return;

            var declared = parseFloat(input.value) || 0;
            var cost = parseFloat((declared * insRate / 100).toFixed(2));

            if (preview) {
                preview.innerHTML = (i18n.insurancePremium || 'Premio stimato:') +
                    ' <strong>€ ' + cost.toFixed(2) + '</strong>';
            }
            if (sumVal) sumVal.textContent = '€ ' + cost.toFixed(2);

            updateTotal();
        }

        // ── Opzioni aggiuntive ─────────────────────────────────────

        function toggleOption(type) {
            var chk = document.getElementById('sr-chk-' + type);
            var card = document.getElementById('sr-opt-' + type);
            var body = document.getElementById('sr-body-' + type);

            if (!chk) return;

            chk.checked = !chk.checked;

            if (card) card.classList.toggle('sr-opt-active', chk.checked);
            if (body) body.classList.toggle('sr-opt-body-visible', chk.checked);

            var sumRow = document.getElementById('sr-sum-row-' + type);
            if (sumRow) sumRow.classList.toggle('sr-sum-hidden', !chk.checked);

            calcOptionCost(type);
        }

        function onCheckChange(type) {
            var chk = document.getElementById('sr-chk-' + type);
            var card = document.getElementById('sr-opt-' + type);
            var body = document.getElementById('sr-body-' + type);

            if (!chk) return;

            if (card) card.classList.toggle('sr-opt-active', chk.checked);
            if (body) body.classList.toggle('sr-opt-body-visible', chk.checked);

            var sumRow = document.getElementById('sr-sum-row-' + type);
            if (sumRow) sumRow.classList.toggle('sr-sum-hidden', !chk.checked);

            calcOptionCost(type);
        }

        function calcOptionCost(type) {
            var opt = options[type];
            var input = document.getElementById('sr-val-' + type);
            var sumVal = document.getElementById('sr-sum-val-' + type);
            var cost = 0;

            if (opt && input) {
                var val = parseFloat(input.value) || 0;
                cost = (opt.formula === 'fixed')
                    ? opt.rate
                    : parseFloat((val * opt.rate / 100).toFixed(2));
            }

            var preview = document.getElementById('sr-cost-' + type);
            if (preview) preview.textContent = cost > 0 ? '€ ' + cost.toFixed(2) : '';
            if (sumVal) sumVal.textContent = '€ ' + cost.toFixed(2);

            updateTotal();
        }

        // ── Totale ─────────────────────────────────────────────────

        function updateTotal() {
            var total = shippingCost;

            // Assicurazione
            var chkIns = document.getElementById('sr-chk-insurance');
            var valIns = document.getElementById('sr-sum-val-insurance');
            if (chkIns && chkIns.checked && valIns) {
                total += parseFloat(valIns.textContent.replace('€', '').trim()) || 0;
            }

            // Opzioni
            Object.keys(options).forEach(function (type) {
                var chk = document.getElementById('sr-chk-' + type);
                var sumVal = document.getElementById('sr-sum-val-' + type);
                if (chk && chk.checked && sumVal) {
                    total += parseFloat(sumVal.textContent.replace('€', '').trim()) || 0;
                }
            });

            var el = document.getElementById('sr-grand-total');
            if (el) el.textContent = '€ ' + total.toFixed(2);
        }

        // ── Submit ─────────────────────────────────────────────────

        function reviewSubmit(e) {
            var btn = document.getElementById('sr-submit-btn');
            var icon = document.getElementById('sr-submit-icon');
            var label = document.getElementById('sr-submit-label');
            var spinner = document.getElementById('sr-spinner');

            if (btn) btn.disabled = true;
            if (icon) icon.style.display = 'none';
            if (spinner) spinner.style.display = 'inline-block';
            if (label) label.textContent = 'Invio in corso…';
        }

        // ── Event listeners ────────────────────────────────────────

        var chkIns = document.getElementById('sr-chk-insurance');
        if (chkIns) {
            chkIns.addEventListener('change', function () {
                toggleInsurance(this.checked);
            });
        }

        var insInput = document.getElementById('sr-insurance-value');
        if (insInput) {
            insInput.addEventListener('input', updateInsuranceSummary);
        }

        // Opzioni — delega sul form
        form.addEventListener('change', function (e) {
            var chk = e.target.closest('[data-type]');
            if (!chk) return;
            onCheckChange(chk.dataset.type);
        });

        form.addEventListener('input', function (e) {
            var input = e.target.closest('.sr-value-input');
            if (!input) return;
            var type = input.id.replace('sr-val-', '');
            if (options[type]) calcOptionCost(type);
        });

        form.addEventListener('submit', reviewSubmit);

        // ── Esponi le funzioni usate dagli onclick nel template ────
        // (i click sui div delle opzioni usano onclick="sqToggleOption(...)")
        window.sqToggleOption = toggleOption;
        window.sqOnCheckChange = onCheckChange;
        window.sqCalcOptionCost = calcOptionCost;
        window.sqToggleInsurance = toggleInsurance;
        window.sqUpdateInsuranceSummary = updateInsuranceSummary;
        window.sqReviewSubmit = reviewSubmit;

    });

})();