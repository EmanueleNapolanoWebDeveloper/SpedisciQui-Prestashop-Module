{* views/templates/admin/carrier_list.tpl *}

<div class="sq-panel">

    <div class="sq-panel-header">
        <i class="icon-truck"></i>
        <div>
            <h2>{l s='Corrieri disponibili' mod='spedisciquishipping'}</h2>
            <p>{l s='Seleziona i corrieri da attivare per il tuo negozio' mod='spedisciquishipping'}</p>
        </div>
    </div>

    <div class="sq-page-header" style="margin-bottom: 24px;">
        <h1 style="font-size: 24px; font-weight: 600; margin-bottom: 8px;">
            <i class="icon-truck" style="margin-right: 8px;"></i>
            {l s='Salva i tuoi corrieri' mod='spedisciquishipping'}
        </h1>
        <p style="color: #7f8c8d; font-size: 14px; max-width: 600px; line-height: 1.6;">
            {l s='Seleziona i corrieri che vuoi rendere disponibili nel tuo negozio. Potrai modificare questa selezione in qualsiasi momento dalla dashboard. I corrieri attivati saranno visibili ai tuoi clienti durante il checkout.' mod='spedisciquishipping'}
        </p>
    </div>



    {if !isset($carriers) || !$carriers || empty($carriers)}
        <div class="sq-alert">
            <i class="icon-warning-sign"></i>
            {l s='Nessun corriere disponibile. Verifica la connessione API.' mod='spedisciquishipping'}
        </div>
    {else}
        <form method="post" action="{$action|escape:'htmlall':'UTF-8'}">
            <input type="hidden" name="controller" value="AdminSpedisciQuiSetup" />
            <input type="hidden" name="token" value="{$smarty.get.token|escape:'htmlall':'UTF-8'}" />

            <table class="sq-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>{l s='Corriere' mod='spedisciquishipping'}</th>
                        <th>{l s='Servizio' mod='spedisciquishipping'}</th>
                        <th>{l s='Tempi di Consegna' mod='spedisciquishipping'}</th>
                        <th>{l s='Peso Max' mod='spedisciquishipping'}</th>
                        <th>{l s='Destinazione' mod='spedisciquishipping'}</th>
                        <th>{l s='Assicurazione' mod='spedisciquishipping'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$carriers item=carrier}
                        {assign
                            var="carrier_name"
                            value=$carrier.carrier_name|default:'Corriere'|escape:'htmlall':'UTF-8'
                        }
                        {assign var="carrier_code" value=$carrier.carrier_code|default:''|escape:'htmlall':'UTF-8'}
                        {assign var="service_name" value=$carrier.service_name|default:''|escape:'htmlall':'UTF-8'}

                        <tr>
                            <td>
                                {if $carrier_code}
                                    <input type="checkbox" name="selected_carriers[]" value="{$carrier_code}" data-name="{$carrier_name}" {if isset($carrier.active) && $carrier.active}checked{/if}>
                                {/if}
                            </td>

                            <td>
                                {if isset($carrier.logo) && $carrier.logo}
                                    <img src="{$carrier.logo|escape:'htmlall':'UTF-8'}" alt="{$carrier_name}" style="height:20px; margin-right:6px; vertical-align:middle;">
                                {/if}
                                <span style="font-weight:bold;">{$carrier_name}</span>
                                <div style="font-size:11px; color:#7f8c8d;">{$carrier_code}</div>
                            </td>

                            <td>
                                <span class="sq-badge sq-badge-default">{$service_name}</span>
                            </td>

                            <td>
                                <span class="sq-delivery-days">
                                    <i class="icon-time"></i>
                                    {$carrier.delay|default:'--'|escape:'htmlall':'UTF-8'}
                                </span>
                            </td>

                            <td>
                                {$carrier.max_weight_kg|default:'--'|escape:'htmlall':'UTF-8'} kg
                            </td>

                            <td>
                                {if isset($carrier.is_pickup_point) && $carrier.is_pickup_point}
                                    <span class="sq-badge sq-badge-pickup" style="background-color:#e67e22; color:white; padding:3px 6px; border-radius:3px;">
                                        <i class="icon-map-marker"></i> {l s='Punto ritiro' mod='spedisciquishipping'}
                                    </span>
                                {else}
                                    <span class="sq-badge sq-badge-home" style="background-color:#2ecc71; color:white; padding:3px 6px; border-radius:3px;">
                                        <i class="icon-home"></i> {l s='Domicilio' mod='spedisciquishipping'}
                                    </span>
                                {/if}
                            </td>

                            <td style="text-align:center;">
                                {if isset($carrier.is_insurance_available) && $carrier.is_insurance_available}
                                    <span style="color:#2ecc71;"><i class="icon-check"></i> {l s='Sì' mod='spedisciquishipping'}</span>
                                {else}
                                    <span style="color:#95a5a6;"><i class="icon-remove"></i> {l s='No' mod='spedisciquishipping'}</span>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>

            <div class="sq-footer" style="margin-top:20px;">
                <button type="submit" name="submitSpedisciQuiCarriers" class="sq-btn">
                    <i class="icon-arrow-right"></i>
                    {l s='Salva corrieri selezionati' mod='spedisciquishipping'}
                </button>
            </div>

        </form>
    {/if}

</div>