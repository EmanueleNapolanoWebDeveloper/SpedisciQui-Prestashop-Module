{* carrier_list.tpl *}
<div class="panel" style="full-width; margin:30px auto;">

    <div class="panel-heading">
        <i class="icon-truck"></i>
        {l s='Seleziona i corrieri da attivare' mod='spedisciquishipping'}
    </div>

    {if !$carriers}
        <div class="alert alert-warning">
            {l s='Nessun corriere disponibile. Verifica la connessione API.' mod='spedisciquishipping'}
        </div>
    {else}
        <form method="post" action="{$action|escape:'htmlall':'UTF-8'}">

            <table class="table tableDnD">
                <thead>
                    <tr>
                        <th style="width:40px;"></th>
                        <th style="width:80px;">{l s='Logo' mod='spedisciquishipping'}</th>
                        <th>{l s='Corriere' mod='spedisciquishipping'}</th>
                        <th>{l s='Servizio' mod='spedisciquishipping'}</th>
                        <th>{l s='Tipo' mod='spedisciquishipping'}</th>
                        <th>{l s='Consegna' mod='spedisciquishipping'}</th>
                        <th>{l s='Destinazione' mod='spedisciquishipping'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$carriers item=carrier}
                        <tr>
                            <td style="text-align:center; vertical-align:middle;">
                                <input type="checkbox" name="selected_carriers[]"
                                    value="{$carrier.code|escape:'htmlall':'UTF-8'}"
                                    data-name="{$carrier.name|escape:'htmlall':'UTF-8'}"
                                    style="width:18px; height:18px; cursor:pointer;">
                            </td>
                            <td style="vertical-align:middle;">
                                {if $carrier.logo_url}
                                    <img src="{$carrier.logo_url|escape:'htmlall':'UTF-8'}"
                                        alt="{$carrier.name|escape:'htmlall':'UTF-8'}"
                                        style="max-height:36px; max-width:80px; object-fit:contain;">
                                {else}
                                    <span class="label label-default">{$carrier.name|escape:'htmlall':'UTF-8'}</span>
                                {/if}
                            </td>
                            <td style="vertical-align:middle;">
                                <strong>{$carrier.name|escape:'htmlall':'UTF-8'}</strong><br>
                                <small class="text-muted">{$carrier.code|escape:'htmlall':'UTF-8'}</small>
                            </td>
                            <td style="vertical-align:middle;">
                                {$carrier.service_title|escape:'htmlall':'UTF-8'}
                            </td>
                            <td style="vertical-align:middle;">
                                {if $carrier.type === 'national'}
                                    <span class="label label-success">
                                        {l s='Nazionale' mod='spedisciquishipping'}
                                    </span>
                                {else}
                                    <span class="label label-info">
                                        {l s='Internazionale' mod='spedisciquishipping'}
                                    </span>
                                {/if}
                            </td>
                            <td style="vertical-align:middle;">
                                <i class="icon-time"></i>
                                {$carrier.delivery_days|escape:'htmlall':'UTF-8'}
                                {l s='giorni' mod='spedisciquishipping'}
                            </td>
                            <td style="vertical-align:middle;">
                                {if $carrier.destination === 'home'}
                                    <span class="label label-primary">
                                        <i class="icon-home"></i>
                                        {l s='Domicilio' mod='spedisciquishipping'}
                                    </span>
                                {elseif $carrier.destination === 'pickup_point'}
                                    <span class="label label-warning">
                                        <i class="icon-map-marker"></i>
                                        {l s='Punto ritiro' mod='spedisciquishipping'}
                                    </span>
                                {else}
                                    <span class="label label-default">
                                        {$carrier.destination|escape:'htmlall':'UTF-8'}
                                    </span>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>

            <div style="margin-top:20px; display:flex; justify-content:flex-end;">
                <button type="submit" name="submitSpedisciQuiCarriers" class="btn btn-primary btn-lg">
                    <i class="icon-save"></i>
                    {l s='Salva corrieri selezionati' mod='spedisciquishipping'}
                </button>
            </div>

        </form>
    {/if}
</div>