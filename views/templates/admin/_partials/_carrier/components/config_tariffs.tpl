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
            {l s='Definisci il prezzo di spedizione per ogni mittente in base al peso del pacco.' mod='spedisciquishipping'}
        </p>

        <form method="POST" action="{$action|escape:'htmlall':'UTF-8'}" id="tariff-form">

            <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">
            <input type="hidden" name="carrier_code" value="{$carrier_code|escape:'htmlall':'UTF-8'}">

            <table class="table" id="tariff-table">
                <thead>
                    <tr>
                        {* Colonne fisse peso *}
                        <th style="width:20%;">{l s='Peso min (kg)' mod='spedisciquishipping'}</th>
                        <th style="width:20%;">{l s='Peso max (kg)' mod='spedisciquishipping'}</th>

                        {* Colonna dinamica per ogni sender *}
                        {foreach from=$senders item=sender}
                            <th style="text-align:center;">
                                <i class="icon-map-marker"></i>
                                {$sender.label|escape:'htmlall':'UTF-8'}
                                <br>
                                <small class="text-muted">{$sender.city|escape:'htmlall':'UTF-8'}</small>
                            </th>
                        {/foreach}

                        <th style="width:8%; text-align:center;">{l s='Rimuovi' mod='spedisciquishipping'}</th>
                    </tr>
                </thead>
                <tbody id="tariff-rows">

                    {if isset($tariff_rows) && !empty($tariff_rows)}
                        {foreach from=$tariff_rows item=row name=rowLoop}
                            <tr class="tariff-row" data-index="{$smarty.foreach.rowLoop.index}">

                                {* Peso minimo *}
                                <td>
                                    <div class="input-group">
                                        <input type="number" name="weight_from[]" class="form-control weight-from" value="{$row.weight_from|floatval}" min="0" step="0.01" placeholder="0.00" required>
                                        <span class="input-group-addon">kg</span>
                                    </div>
                                </td>

                                {* Peso massimo *}
                                <td>
                                    <div class="input-group">
                                        <input type="number" name="weight_to[]" class="form-control weight-to" value="{$row.weight_to|floatval}" min="0" step="0.01" placeholder="0.00" required>
                                        <span class="input-group-addon">kg</span>
                                    </div>
                                </td>

                                {* Prezzo per ogni sender *}
                                {foreach from=$senders item=sender}
                                    <td>
                                        <div class="input-group">
                                            <span class="input-group-addon">€</span>
                                            <input type="number" name="price[{$sender.id_sender}][]" class="form-control price" value="{$row.prices[$sender.id_sender]|default:'0.00'|floatval}" min="0" step="0.01" placeholder="0.00" required>
                                        </div>
                                    </td>
                                {/foreach}

                                <td style="text-align:center; vertical-align:middle;">
                                    <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                                        <i class="icon-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        {/foreach}
                    {else}
                        {* Riga vuota di default *}
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
                            {foreach from=$senders item=sender}
                                <td>
                                    <div class="input-group">
                                        <span class="input-group-addon">€</span>
                                        <input type="number" name="price[{$sender.id_sender}][]" class="form-control price" value="" min="0" step="0.01" placeholder="0.00" required>
                                    </div>
                                </td>
                            {/foreach}
                            <td style="text-align:center; vertical-align:middle;">
                                <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                                    <i class="icon-trash"></i>
                                </button>
                            </td>
                        </tr>
                    {/if}

                </tbody>
            </table>

            <div style="margin-bottom:20px;">
                <button type="button" id="btn-add-row" class="btn btn-default">
                    <i class="icon-plus"></i>
                    {l s='Aggiungi fascia' mod='spedisciquishipping'}
                </button>
            </div>

            <div class="panel-footer" style="padding:10px 0 0 0; border-top:1px solid #ddd; display:flex; gap:10px;">
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

{* Template riga da clonare via JS *}
<script type="text/template" id="tariff-row-template">
    <tr class="tariff-row" data-index="__INDEX__">
        <td>
            <div class="input-group">
                <input type="number" name="weight_from[]" class="form-control weight-from"
                       value="" min="0" step="0.01" placeholder="0.00" required>
                <span class="input-group-addon">kg</span>
            </div>
        </td>
        <td>
            <div class="input-group">
                <input type="number" name="weight_to[]" class="form-control weight-to"
                       value="" min="0" step="0.01" placeholder="0.00" required>
                <span class="input-group-addon">kg</span>
            </div>
        </td>
        {foreach from=$senders item=sender}
        <td>
            <div class="input-group">
                <span class="input-group-addon">€</span>
                <input type="number"
                       name="price[{$sender.id_sender}][]"
                       class="form-control price"
                       value="" min="0" step="0.01" placeholder="0.00" required>
            </div>
        </td>
        {/foreach}
        <td style="text-align:center; vertical-align:middle;">
            <button type="button" class="btn btn-danger btn-sm btn-remove-row">
                <i class="icon-trash"></i>
            </button>
        </td>
    </tr>
</script>

<script>
    window.SQ_Tariffs = {
        rowIndex: { $tariff_rows | @count | default: 1 },
        msgMinRow: "{l s='Deve essere presente almeno una fascia.' mod='spedisciquishipping' js=1}",
        msgInvalidData: "{l s='Controlla i valori inseriti.' mod='spedisciquishipping' js=1}",
        labelRemove: "{l s='Rimuovi riga' mod='spedisciquishipping' js=1}"
    };
</script>