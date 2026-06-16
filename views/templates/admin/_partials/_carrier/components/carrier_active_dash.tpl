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

       {include file="module:spedisciquishipping/views/templates/admin/_partials/_carrier/components/carrier_active_table.tpl"}
    {/if}

</div>