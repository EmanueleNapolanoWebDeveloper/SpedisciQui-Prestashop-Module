{**
  * Template: tariff_config.tpl
  * Pagina di configurazione tariffe per peso — per ogni corriere
  * Variabili attese dal controller:
  *   $carrier_code  — codice servizio del corriere selezionato
  *   $carrier_name  — nome leggibile del corriere
  *   $tariff_rows   — array di righe tariffali esistenti: [{weight_from, weight_to, price}]
  *   $action        — URL POST del controller
  *   $backLink      — URL per tornare alla lista corrieri
  **}

<div class="page-header">
    <h1>
        <i class="icon-truck"></i>
        {l s='Configurazione Tariffe' mod='spedisciquishipping'}
        &mdash;
        <span class="text-primary">{$carrier_name|escape:'htmlall':'UTF-8'}</span>
        <small class="label label-info" style="font-size:13px; vertical-align:middle; margin-left:8px;">
            {$carrier_code|escape:'htmlall':'UTF-8'}
        </small>
    </h1>
</div>

<div class="panel">
    <div class="panel-heading">
        <i class="icon-list"></i>
        {l s='Fasce tariffarie per peso' mod='spedisciquishipping'}
    </div>
    <div class="panel-body">

        <p class="text-muted" style="margin-bottom:20px;">
            <i class="icon-info-sign"></i>
            {l s='Definisci il prezzo di spedizione in base al peso del pacco. Le fasce non devono sovrapporsi.' mod='spedisciquishipping'}
        </p>

        <form method="POST" action="{$action|escape:'htmlall':'UTF-8'}" id="tariff-form">
            <input type="hidden" name="carrier_code" value="{$carrier_code|escape:'htmlall':'UTF-8'}">

            {* ————————————————————————————————
               Tabella fasce tariffarie
               ———————————————————————————————— *}
            <table class="table" id="tariff-table">
                <thead>
                    <tr>
                        <th style="width:35%;">{l s='Peso minimo (kg)' mod='spedisciquishipping'}</th>
                        <th style="width:35%;">{l s='Peso massimo (kg)' mod='spedisciquishipping'}</th>
                        <th style="width:20%;">{l s='Prezzo (€)' mod='spedisciquishipping'}</th>
                        <th style="width:10%; text-align:center;">{l s='Rimuovi' mod='spedisciquishipping'}</th>
                    </tr>
                </thead>
                <tbody id="tariff-rows">

                    {* Righe esistenti salvate in DB *}
                    {if $tariff_rows}
                        {foreach from=$tariff_rows item=row name=rowLoop}
                            <tr class="tariff-row" data-index="{$smarty.foreach.rowLoop.index}">
                                <td>
                                    <div class="input-group">
                                        <input type="number" name="weight_from[]" class="form-control weight-from"
                                            value="{$row.weight_from|floatval}" min="0" step="0.01" placeholder="0.00" required>
                                        <span class="input-group-addon">kg</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" name="weight_to[]" class="form-control weight-to"
                                            value="{$row.weight_to|floatval}" min="0" step="0.01" placeholder="0.00" required>
                                        <span class="input-group-addon">kg</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-addon">€</span>
                                        <input type="number" name="price[]" class="form-control price"
                                            value="{$row.tariff|floatval}" min="0" step="0.01" placeholder="0.00" required>
                                    </div>
                                </td>
                                <td style="text-align:center; vertical-align:middle;">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-row"
                                        title="{l s='Rimuovi riga' mod='spedisciquishipping' js=1}">
                                        <i class="icon-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        {* Riga vuota di default se non ci sono tariffe *}
                        <tr class="tariff-row" data-index="0">
                            <td>
                                <div class="input-group">
                                    <input type="number" name="weight_from[]" class="form-control weight-from" value=""
                                        min="0" step="0.01" placeholder="0.00" required>
                                    <span class="input-group-addon">kg</span>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="number" name="weight_to[]" class="form-control weight-to" value="" min="0"
                                        step="0.01" placeholder="0.00" required>
                                    <span class="input-group-addon">kg</span>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-addon">€</span>
                                    <input type="number" name="tariff[]" class="form-control price" value="" min="0"
                                        step="0.01" placeholder="0.00" required>
                                </div>
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <button type="button" class="btn btn-danger btn-sm btn-remove-row"
                                    title="{l s='Rimuovi riga' mod='spedisciquishipping' js=1}">
                                    <i class="icon-trash"></i>
                                </button>
                            </td>
                        </tr>
                    {/if}

                </tbody>
            </table>

            {* ————————————————————————————————
               Pulsante aggiungi fascia
               ———————————————————————————————— *}
            <div style="margin-bottom:20px;">
                <button type="button" id="btn-add-row" class="btn btn-default">
                    <i class="icon-plus"></i>
                    {l s='Aggiungi fascia' mod='spedisciquishipping'}
                </button>
            </div>

            {* ————————————————————————————————
               Riepilogo visivo (opzionale, generato via JS)
               ———————————————————————————————— *}
            <div id="tariff-preview" style="display:none; margin-bottom:20px;">
                <div class="alert alert-info">
                    <strong><i class="icon-eye"></i> {l s='Anteprima fasce:' mod='spedisciquishipping'}</strong>
                    <span id="tariff-preview-text"></span>
                </div>
            </div>

            {* ————————————————————————————————
               Azioni footer
               ———————————————————————————————— *}
            <div class="panel-footer"
                style="padding:10px 0 0 0; border-top:1px solid #ddd; display:flex; gap:10px; flex-wrap:wrap;">
                <button type="submit" name="saveTariffConfig" class="btn btn-success">
                    <i class="icon-save"></i>
                    {l s='Salva configurazione' mod='spedisciquishipping'}
                </button>
                <a href="{$backLink|escape:'htmlall':'UTF-8'}" class="btn btn-default">
                    <i class="icon-arrow-left"></i>
                    {l s='Torna ai corrieri' mod='spedisciquishipping'}
                </a>
            </div>

        </form>
    </div>
</div>


{* ================================================================
   JAVASCRIPT — gestione dinamica delle righe
   ================================================================ *}
<script type="text/javascript">
    (function() {

        var rowIndex = {if $tariff_rows}{$tariff_rows|count}{else}1{/if};

        {* —— Template HTML per una nuova riga —— *}
        function newRowHtml(idx) {
            return '<tr class="tariff-row" data-index="' + idx + '">' +
                '<td>' +
                '<div class="input-group">' +
                '<input type="number" name="weight_from[]" class="form-control weight-from" ' +
                'value="" min="0" step="0.01" placeholder="0.00" required>' +
                '<span class="input-group-addon">kg</span>' +
                '</div>' +
                '</td>' +
                '<td>' +
                '<div class="input-group">' +
                '<input type="number" name="weight_to[]" class="form-control weight-to" ' +
                'value="" min="0" step="0.01" placeholder="0.00" required>' +
                '<span class="input-group-addon">kg</span>' +
                '</div>' +
                '</td>' +
                '<td>' +
                '<div class="input-group">' +
                '<span class="input-group-addon">€</span>' +
                '<input type="number" name="price[]" class="form-control price" ' +
                'value="" min="0" step="0.01" placeholder="0.00" required>' +
                '</div>' +
                '</td>' +
                '<td style="text-align:center; vertical-align:middle;">' +
                '<button type="button" class="btn btn-danger btn-sm btn-remove-row" ' +
                'title="{l s='Rimuovi riga' mod='spedisciquishipping' js=1}">' +
                '<i class="icon-trash"></i>' +
                '</button>' +
                '</td>' +
                '</tr>';
        }

        {* —— Aggiungi nuova riga —— *}
        document.getElementById('btn-add-row').addEventListener('click', function() {

            var tbody = document.getElementById('tariff-rows');

            var temp = document.createElement('tbody');
            temp.innerHTML = newRowHtml(rowIndex++);

            var newRow = temp.querySelector('tr');

            // Pre-compila peso_from con l'ultimo weight_to
            var rows = tbody.querySelectorAll('.tariff-row');

            if (rows.length > 0) {

                var lastRow = rows[rows.length - 1];

                var lastWeightTo = lastRow.querySelector('.weight-to').value;

                if (lastWeightTo !== '') {
                    newRow.querySelector('.weight-from').value = lastWeightTo;
                }
            }

            tbody.appendChild(newRow);

            updatePreview();

            newRow.querySelector('.weight-to').focus();
        });

        {* —— Rimuovi riga (delegazione eventi) —— *}
        document.getElementById('tariff-rows').addEventListener('click', function(e) {
            var btn = e.target.closest('.btn-remove-row');
            if (!btn) return;
            var rows = document.querySelectorAll('#tariff-rows .tariff-row');
            if (rows.length <= 1) {
                alert('{l s='Deve essere presente almeno una fascia.' mod='spedisciquishipping' js=1}');
                return;
            }
            btn.closest('tr').remove();
            updatePreview();
        });

        {* —— Aggiorna anteprima al cambio di qualsiasi input —— *}
        document.getElementById('tariff-rows').addEventListener('input', function() {
            updatePreview();
        });

        function updatePreview() {
            var rows = document.querySelectorAll('#tariff-rows .tariff-row');
            var parts = [];
            rows.forEach(function(row) {
                var from = row.querySelector('.weight-from').value;
                var to = row.querySelector('.weight-to').value;
                var price = row.querySelector('.price').value;
                if (from !== '' && to !== '' && price !== '') {
                    parts.push(from + '–' + to + ' kg → €' + parseFloat(price).toFixed(2));
                }
            });
            var preview = document.getElementById('tariff-preview');
            var previewText = document.getElementById('tariff-preview-text');
            if (parts.length > 0) {
                previewText.textContent = ' ' + parts.join(' | ');
                preview.style.display = 'block';
            } else {
                preview.style.display = 'none';
            }
        }

        {* —— Validazione prima del submit —— *}
        document.getElementById('tariff-form').addEventListener('submit', function(e) {
            var rows = document.querySelectorAll('#tariff-rows .tariff-row');
            var valid = true;
            var prevTo = null;

            rows.forEach(function(row) {
                var from = parseFloat(row.querySelector('.weight-from').value);
                var to = parseFloat(row.querySelector('.weight-to').value);
                var price = parseFloat(row.querySelector('.price').value);

                if (isNaN(from) || isNaN(to) || isNaN(price)) { valid = false; return; }
                if (from >= to) { valid = false; return; }
                if (price < 0) { valid = false; return; }
            });

            if (!valid) {
                e.preventDefault();
                alert('{l s='Controlla i valori: il peso minimo deve essere inferiore al massimo e il prezzo non può essere negativo.' mod='spedisciquishipping' js=1}');
            }
        });

        {* Inizializza anteprima al caricamento *}
        updatePreview();

    })();
</script>