(function () {

    document.addEventListener('DOMContentLoaded', function () {

        var btnAddRow = document.getElementById('btn-add-row');
        var tariffRows = document.getElementById('tariff-rows');
        var tariffForm = document.getElementById('tariff-form');

        if (!btnAddRow || !tariffRows || !tariffForm) return;

        var cfg = window.SQ_Tariffs || {};
        var rowIndex = cfg.rowIndex || 1;

        function newRowHtml(idx) {
            return '<tr class="tariff-row" data-index="' + idx + '">' +
                '<td>' +
                '<div class="input-group">' +
                '<input type="number" name="weight_from[]" class="form-control weight-from" value="" min="0" step="0.01" placeholder="0.00" required>' +
                '<span class="input-group-addon">kg</span>' +
                '</div>' +
                '</td>' +
                '<td>' +
                '<div class="input-group">' +
                '<input type="number" name="weight_to[]" class="form-control weight-to" value="" min="0" step="0.01" placeholder="0.00" required>' +
                '<span class="input-group-addon">kg</span>' +
                '</div>' +
                '</td>' +
                '<td>' +
                '<div class="input-group">' +
                '<span class="input-group-addon">€</span>' +
                '<input type="number" name="price[]" class="form-control price" value="" min="0" step="0.01" placeholder="0.00" required>' +
                '</div>' +
                '</td>' +
                '<td style="text-align:center; vertical-align:middle;">' +
                '<button type="button" class="btn btn-danger btn-sm btn-remove-row" title="' + (cfg.labelRemove || '') + '">' +
                '<i class="icon-trash"></i>' +
                '</button>' +
                '</td>' +
                '</tr>';
        }

        btnAddRow.addEventListener('click', function () {
            var temp = document.createElement('tbody');
            temp.innerHTML = newRowHtml(rowIndex++);

            var newRow = temp.querySelector('tr');
            var rows = tariffRows.querySelectorAll('.tariff-row');

            if (rows.length > 0) {
                var lastWeightTo = rows[rows.length - 1].querySelector('.weight-to').value;
                if (lastWeightTo !== '') {
                    newRow.querySelector('.weight-from').value = lastWeightTo;
                }
            }

            tariffRows.appendChild(newRow);
            updatePreview();
            newRow.querySelector('.weight-to').focus();
        });

        tariffRows.addEventListener('click', function (e) {
            var btn = e.target.closest('.btn-remove-row');
            if (!btn) return;

            var rows = tariffRows.querySelectorAll('.tariff-row');
            if (rows.length <= 1) {
                alert(cfg.msgMinRow || 'Deve essere presente almeno una fascia.');
                return;
            }

            btn.closest('tr').remove();
            updatePreview();
        });

        tariffRows.addEventListener('input', function () {
            updatePreview();
        });

        tariffForm.addEventListener('submit', function (e) {
            var rows = tariffRows.querySelectorAll('.tariff-row');
            var valid = true;

            rows.forEach(function (row) {
                var from = parseFloat(row.querySelector('.weight-from').value);
                var to = parseFloat(row.querySelector('.weight-to').value);
                var price = parseFloat(row.querySelector('.price').value);

                if (isNaN(from) || isNaN(to) || isNaN(price)) { valid = false; return; }
                if (from >= to) { valid = false; return; }
                if (price < 0) { valid = false; return; }
            });

            if (!valid) {
                e.preventDefault();
                alert(cfg.msgInvalidData || 'Controlla i valori.');
            }
        });

        updatePreview();

        function updatePreview() {
            var rows = tariffRows.querySelectorAll('.tariff-row');
            var parts = [];

            rows.forEach(function (row) {
                var from = row.querySelector('.weight-from').value;
                var to = row.querySelector('.weight-to').value;
                var price = row.querySelector('.price').value;

                if (from !== '' && to !== '' && price !== '') {
                    parts.push(from + '–' + to + ' kg → €' + parseFloat(price).toFixed(2));
                }
            });

            var preview = document.getElementById('tariff-preview');
            var previewText = document.getElementById('tariff-preview-text');

            if (!preview || !previewText) return;

            if (parts.length > 0) {
                previewText.textContent = ' ' + parts.join(' | ');
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }

    });

})();