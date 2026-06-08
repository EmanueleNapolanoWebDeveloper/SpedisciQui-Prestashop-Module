<div class="sq-avail-panel">

    <div class="sq-avail-header">
        <div class="sq-avail-header-left">
            <div class="sq-avail-icon">
                <i class="icon-cloud"></i>
            </div>
            <div>
                <p class="sq-avail-title">{l s='Corrieri disponibili sulla piattaforma' mod='spedisciquishipping'}</p>
                <p class="sq-avail-subtitle">
                    {l s='Sincronizzati in tempo reale tramite API SpedisciQui' mod='spedisciquishipping'}</p>
            </div>
        </div>
        {if $carriers}
            <div class="sq-avail-header-right">
                <span class="sq-sync-indicator">
                    <span class="sq-sync-dot"></span>
                    {l s='API connessa' mod='spedisciquishipping'}
                </span>
                <span class="sq-count-pill">{$carriers|count}</span>
            </div>
        {/if}
    </div>

    {if !$carriers}

        <div class="sq-avail-empty">
            <div class="sq-avail-empty-icon">
                <i class="icon-cloud"></i>
            </div>
            <p class="sq-avail-empty-title">{l s='Nessun corriere disponibile' mod='spedisciquishipping'}</p>
            <p class="sq-avail-empty-desc">
                {l s='Impossibile recuperare i corrieri dalla piattaforma SpedisciQui. Verifica la connessione API e le credenziali configurate.' mod='spedisciquishipping'}
            </p>
        </div>

    {else}

        <div class="sq-avail-table-wrap">
            <table class="sq-avail-table">
                <thead>
                    <tr>
                        <th class="sq-logo-col"></th>
                        <th>{l s='Corriere' mod='spedisciquishipping'}</th>
                        <th>{l s='Servizio' mod='spedisciquishipping'}</th>
                        <th>{l s='Consegna' mod='spedisciquishipping'}</th>
                        <th>{l s='Tipo' mod='spedisciquishipping'}</th>
                        <th>{l s='Destinazione' mod='spedisciquishipping'}</th>
                        <th class="sq-action-col">{l s='Stato' mod='spedisciquishipping'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$carriers item=carrier}
                        <tr>

                            <td class="sq-logo-col">
                                {if $carrier.logo_url}
                                    <div class="sq-logo-box">
                                        <img src="{$carrier.logo_url|escape:'htmlall':'UTF-8'}"
                                            alt="{$carrier.name|escape:'htmlall':'UTF-8'}">
                                    </div>
                                {else}
                                    <div class="sq-logo-placeholder">
                                        <i class="icon-truck"></i>
                                    </div>
                                {/if}
                            </td>

                            <td>
                                <p class="sq-carrier-name">{$carrier.name|escape:'htmlall':'UTF-8'}</p>
                                <span class="sq-carrier-code">{$carrier.code|escape:'htmlall':'UTF-8'}</span>
                            </td>

                            <td>
                                <p class="sq-service-title">{$carrier.service_title|escape:'htmlall':'UTF-8'}</p>
                            </td>

                            <td>
                                <span class="sq-delivery-badge">
                                    <i class="icon-time"></i>
                                    {$carrier.delivery_days|escape:'htmlall':'UTF-8'} {l s='gg' mod='spedisciquishipping'}
                                </span>
                            </td>

                            <td>
                                {if $carrier.type === 'national'}
                                    <span class="sq-badge sq-badge-national">
                                        <i class="icon-flag"></i>
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
                                {if $carrier.destination === 'home'}
                                    <span class="sq-badge sq-badge-home">
                                        <i class="icon-home"></i>
                                        {l s='Domicilio' mod='spedisciquishipping'}
                                    </span>
                                {else}
                                    <span class="sq-badge sq-badge-pickup">
                                        <i class="icon-map-marker"></i>
                                        {l s='Punto ritiro' mod='spedisciquishipping'}
                                    </span>
                                {/if}
                            </td>

                            <td>
                                {if in_array($carrier.code, $savedCodes)}
                                    <span class="sq-badge sq-badge-installed">
                                        <i class="icon-check"></i>
                                        {l s='Installato' mod='spedisciquishipping'}
                                    </span>
                                {else}
                                    <form method="POST" action="{$action|escape:'htmlall':'UTF-8'}">
                                        <input type="hidden" name="selected_carriers[]"
                                            value="{$carrier.code|escape:'htmlall':'UTF-8'}">
                                        <button type="submit" name="submitSpedisciQuiCarriers" class="sq-btn-add">
                                            <i class="icon-plus"></i>
                                            {l s='Aggiungi' mod='spedisciquishipping'}
                                        </button>
                                    </form>
                                {/if}
                            </td>

                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>

    {/if}

</div>