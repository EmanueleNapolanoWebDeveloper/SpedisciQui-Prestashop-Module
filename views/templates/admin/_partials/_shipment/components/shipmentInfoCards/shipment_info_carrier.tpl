<div class="sr-card">
    <p class="sr-card-title">
        <i class="icon-truck"></i>
        {l s='Corriere' mod='spedisciquishipping'}
    </p>
    <div class="sr-kv">
        <div class="sr-kv-row">
            <span class="sr-kv-label">{l s='Corriere' mod='spedisciquishipping'}</span>
            <span class="sr-kv-value">{$vm.carrier.carrier_code|escape:'html':'UTF-8'}</span>
        </div>
        <div class="sr-kv-row">
            <span class="sr-kv-label">{l s='Codice servizio' mod='spedisciquishipping'}</span>
            <span class="sr-kv-value">
                <span class="sr-kv-mono">{$vm.carrier.service_code|escape:'html':'UTF-8'}</span>
            </span>
        </div>
        {if $vm.carrier.service_name}
            <div class="sr-kv-row">
                <span class="sr-kv-label">{l s='Servizio' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value">{$vm.carrier.service_name|escape:'html':'UTF-8'}</span>
            </div>
        {/if}
        {if $vm.carrier.estimated_days}
            <div class="sr-kv-row">
                <span class="sr-kv-label">{l s='Consegna stimata' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value">
                    {$vm.carrier.estimated_days|intval}
                    {l s='giorno/i lavorativo/i' mod='spedisciquishipping'}
                </span>
            </div>
        {/if}
    </div>
</div>