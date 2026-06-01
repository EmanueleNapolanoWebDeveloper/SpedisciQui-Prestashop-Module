// views/js/admin/shipment_review.js

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