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

            {* Modifica fondamentale: Inserimento del token di sicurezza per evitare l'avviso di PrestaShop *}
            <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">
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
                    {if isset($tariff_rows) && !empty($tariff_rows)}
                        {foreach from=$tariff_rows item=row name=rowLoop}
                            <tr class="tariff-row" data-index="{$smarty.foreach.rowLoop.index}">
                                <td>
                                    <div class="input-group">
                                        <input type="number" name="weight_from[]" class="form-control weight-from" value="{$row.weight_from|floatval}" min="0" step="0.01" placeholder="0.00" required>
                                        <span class="input-group-addon">kg</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <input type="number" name="weight_to[]" class="form-control weight-to" value="{$row.weight_to|floatval}" min="0" step="0.01" placeholder="0.00" required>
                                        <span class="input-group-addon">kg</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-addon">€</span>
                                        <input type="number" name="price[]" class="form-control price" value="{$row.tariff|floatval}" min="0" step="0.01" placeholder="0.00" required>
                                    </div>
                                </td>
                                <td style="text-align:center; vertical-align:middle;">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-row" title="{l s='Rimuovi riga' mod='spedisciquishipping' js=1}">
                                        <i class="icon-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        {* Riga vuota di default se non ci sono tariffe — Corretto name="price[]" *}
                        <tr class="tariff-row" data-index="0">
                            <td>
                                <div class="input-group">
                                    <input type="number" name="weight_from[]" class="form-control weight-from" value="" min="0" step="0.01" placeholder="0.00" required>
                                    <span class="input-group-addon">kg</span>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="number" name="weight_to[]" class="form-control weight-to" value="" min="0" step="0.01" placeholder="0.00" required>
                                    <span class="input-group-addon">kg</span>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <span class="input-group-addon">€</span>
                                    <input type="number" name="price[]" class="form-control price" value="" min="0" step="0.01" placeholder="0.00" required>
                                </div>
                            </td>
                            <td style="text-align:center; vertical-align:middle;">
                                <button type="button" class="btn btn-danger btn-sm btn-remove-row" title="{l s='Rimuovi riga' mod='spedisciquishipping' js=1}">
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
            <div class="panel-footer" style="padding:10px 0 0 0; border-top:1px solid #ddd; display:flex; gap:10px; flex-wrap:wrap;">
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
    window.SQ_Tariffs = {
        rowIndex:       {if isset($tariff_rows) && $tariff_rows}{$tariff_rows|count}{else}1{/if},
        msgMinRow:      '{l s='Deve essere presente almeno una fascia.' mod='spedisciquishipping' js=1}',
        msgInvalidData: '{l s='Controlla i valori: il peso minimo deve essere inferiore al massimo e il prezzo non può essere negativo.' mod='spedisciquishipping' js=1}',
        labelRemove:    '{l s='Rimuovi riga' mod='spedisciquishipping' js=1}'
    };
</script>