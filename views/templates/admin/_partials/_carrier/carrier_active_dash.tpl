<div class="sq-dashboard sq-panel">

    <div class="sq-header">
        <div class="sq-header-left">
            <i class="icon-truck" style="font-size:20px;color:#1a6fc4;"></i>
            <div>
                <p class="sq-header-title">{l s='I Tuoi Corrieri Installati' mod='spedisciquishipping'}</p>
                <p class="sq-header-subtitle">
                    {l s='Gestione e configurazione dei corrieri attivi' mod='spedisciquishipping'}</p>
            </div>
        </div>
        {if $savedCarriers}
            <span class="sq-count-badge">{$savedCarriers|count}</span>
        {/if}
    </div>

    {if !$savedCarriers}

        <div class="sq-empty">
            <div class="sq-empty-icon">
                <i class="icon-truck"></i>
            </div>
            <p class="sq-empty-title">{l s='Nessun corriere configurato' mod='spedisciquishipping'}</p>
            <p class="sq-empty-desc">
                {l s='Non hai ancora aggiunto nessun corriere SpedisciQui. Importa i corrieri disponibili per iniziare a configurare le spedizioni.' mod='spedisciquishipping'}
            </p>
        </div>

    {else}

        <div class="sq-table-wrap">
            <table class="sq-table">
                <thead>
                    <tr>
                        <th class="sq-logo-cell"></th>
                        <th>{l s='Corriere' mod='spedisciquishipping'}</th>
                        <th>{l s='Codice Servizio' mod='spedisciquishipping'}</th>
                        <th>{l s='Consegna' mod='spedisciquishipping'}</th>
                        <th>{l s='Stato' mod='spedisciquishipping'}</th>
                        <th>{l s='Date' mod='spedisciquishipping'}</th>
                        <th>{l s='Azioni' mod='spedisciquishipping'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$savedCarriers item=sc}
                        <tr>

                            <td class="sq-logo-cell">
                                {if $sc.logo}
                                    <div class="sq-logo-wrap">
                                        <img src="{$sc.logo|escape:'htmlall':'UTF-8'}"
                                            alt="{$sc.carrier_name|escape:'htmlall':'UTF-8'}">
                                    </div>
                                {else}
                                    <div class="sq-logo-placeholder">
                                        <i class="icon-truck"></i>
                                    </div>
                                {/if}
                            </td>

                            {* nome *}
                            <td>
                                <p class="sq-carrier-name">{$sc.carrier_name|escape:'htmlall':'UTF-8'}</p>
                                {if $sc.service_name}
                                    <p class="sq-carrier-service text-black">{$sc.service_name|escape:'htmlall':'UTF-8'}</p>
                                {/if}
                            </td>

                            <td>
                                <div class="sq-codes-stack">
                                    <span class="sq-code">{$sc.carrier_code|escape:'htmlall':'UTF-8'}</span>
                                    {if $sc.service_code}
                                        <span class="sq-code-small">{$sc.service_code|escape:'htmlall':'UTF-8'}</span>
                                    {/if}
                                </div>
                            </td>

                            <td>
                                <span class="sq-id-badge">
                                    <i class="icon-tag" style="font-size:10px;"></i>
                                    #{$sc.delay}
                                </span>
                            </td>

                            <td>
                                <div class="sq-badges-stack">
                                    {if empty($configuredCodes) || !in_array($sc.carrier_code, $configuredCodes)}
                                        <span class="sq-badge sq-badge-pickup">
                                            <span class="sq-badge-dot"></span>
                                            {l s='Da Configurare!' mod='spedisciquishipping'}
                                        </span>
                                    {else}
                                        <span class="sq-badge sq-badge-courier">
                                            <span class="sq-badge-dot"></span>
                                            {l s='Configurato!' mod='spedisciquishipping'}
                                        </span>
                                    {/if}
                                </div>
                            </td>

                            <td>
                                <div class="sq-meta">
                                    <div><span
                                            class="sq-meta-label">{l s='Aggiunto:' mod='spedisciquishipping'}</span>{$sc.date_add|escape:'htmlall':'UTF-8'|truncate:10:''}
                                    </div>
                                    <div><span
                                            class="sq-meta-label">{l s='Aggiornato:' mod='spedisciquishipping'}</span>{$sc.date_upd|escape:'htmlall':'UTF-8'|truncate:10:''}
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="sq-actions">


                                    {* configura *}
                                    <form method="GET" action="index.php" style="display:inline; margin:0;">
                                        <input type="hidden" name="controller" value="AdminModules">
                                        <input type="hidden" name="configure" value="spedisciquishipping">
                                        <input type="hidden" name="token" value="{$smarty.get.token|escape:'htmlall':'UTF-8'}">
                                        <input type="hidden" name="carrier_code"
                                            value="{$sc.carrier_code|escape:'htmlall':'UTF-8'}">
                                        <button type="submit" class="sq-btn sq-btn-configure">
                                            <i class="icon-cog"></i>
                                            {l s='Configura' mod='spedisciquishipping'}
                                        </button>
                                    </form>

                                    {* rimuovi *}
                                    <form method="POST" action="{$action|escape:'htmlall':'UTF-8'}">
                                        <input type="hidden" name="carrier_code"
                                            value="{$sc.carrier_code|escape:'htmlall':'UTF-8'}">
                                        <button type="submit" name="removeSpedisciQuiCarriers" class="sq-btn sq-btn-remove"
                                            onclick="return confirm('{l s='Rimuovere il corriere?' mod='spedisciquishipping' js=1}');">
                                            <i class="icon-trash"></i>
                                            {l s='Rimuovi' mod='spedisciquishipping'}
                                        </button>
                                    </form>

                                </div>
                            </td>

                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>

    {/if}

</div>