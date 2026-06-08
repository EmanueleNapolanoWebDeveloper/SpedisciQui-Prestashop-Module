{* views/templates/admin/carrier_list.tpl *}

<div class="sq-panel">

    <div class="sq-panel-header">
        <i class="icon-truck"></i>
        <div>
            <h2>{l s='Corrieri disponibili' mod='spedisciquishipping'}</h2>
            <p>{l s='Seleziona i corrieri da attivare per il tuo negozio' mod='spedisciquishipping'}</p>
        </div>
    </div>

    {if !$carriers}
        <div class="sq-alert">
            <i class="icon-warning-sign"></i>
            {l s='Nessun corriere disponibile. Verifica la connessione API.' mod='spedisciquishipping'}
        </div>
    {else}
        <form method="post" action="{$action|escape:'htmlall':'UTF-8'}">

            <table class="sq-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>{l s='Logo' mod='spedisciquishipping'}</th>
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
                            <td>
                                <input type="checkbox"
                                    name="selected_carriers[]"
                                    value="{$carrier.code|escape:'htmlall':'UTF-8'}"
                                    data-name="{$carrier.name|escape:'htmlall':'UTF-8'}">
                            </td>
                            <td>
                                {if $carrier.logo_url}
                                    <img class="sq-carrier-logo"
                                        src="{$carrier.logo_url|escape:'htmlall':'UTF-8'}"
                                        alt="{$carrier.name|escape:'htmlall':'UTF-8'}">
                                {else}
                                    <span class="sq-badge sq-badge-default">
                                        {$carrier.name|escape:'htmlall':'UTF-8'}
                                    </span>
                                {/if}
                            </td>
                            <td>
                                <div class="sq-carrier-name">
                                    {$carrier.name|escape:'htmlall':'UTF-8'}
                                </div>
                                <div class="sq-carrier-code">
                                    {$carrier.code|escape:'htmlall':'UTF-8'}
                                </div>
                            </td>
                            <td>{$carrier.service_title|escape:'htmlall':'UTF-8'}</td>
                            <td>
                                {if $carrier.type === 'national'}
                                    <span class="sq-badge sq-badge-national">
                                        <i class="icon-map-marker"></i>
                                        {l s='Nazionale' mod='spedisciquishipping'}
                                    </span>
                                {else}
                                    <span class="sq-badge sq-badge-intl">
                                        <i class="icon-globe"></i>
                                        {l s='Internazionale' mod='spedisciquishipping'}
                                    </span>
                                {/if}
                            </td>
                            <td>
                                <span class="sq-delivery-days">
                                    <i class="icon-time"></i>
                                    {$carrier.delivery_days|escape:'htmlall':'UTF-8'}
                                    {l s='giorni' mod='spedisciquishipping'}
                                </span>
                            </td>
                            <td>
                                {if $carrier.destination === 'home'}
                                    <span class="sq-badge sq-badge-home">
                                        <i class="icon-home"></i>
                                        {l s='Domicilio' mod='spedisciquishipping'}
                                    </span>
                                {elseif $carrier.destination === 'pickup_point'}
                                    <span class="sq-badge sq-badge-pickup">
                                        <i class="icon-map-marker"></i>
                                        {l s='Punto ritiro' mod='spedisciquishipping'}
                                    </span>
                                {else}
                                    <span class="sq-badge sq-badge-default">
                                        {$carrier.destination|escape:'htmlall':'UTF-8'}
                                    </span>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>

            <div class="sq-footer">
                <button type="submit" name="submitSpedisciQuiCarriers" class="sq-btn">
                    <i class="icon-arrow-right"></i>
                    {l s='Salva corrieri selezionati' mod='spedisciquishipping'}
                </button>
            </div>

        </form>
    {/if}

</div>