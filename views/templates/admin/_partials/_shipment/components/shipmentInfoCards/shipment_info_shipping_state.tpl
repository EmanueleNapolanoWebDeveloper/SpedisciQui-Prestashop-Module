<div class="sr-card">
    <p class="sr-card-title">
        <i class="icon-plane"></i>
        {l s='Spedizione' mod='spedisciquishipping'}
    </p>
    <div class="sr-kv">
        <div class="sr-kv-row">
            <span class="sr-kv-label">{l s='ID shipment' mod='spedisciquishipping'}</span>
            <span class="sr-kv-value">#{$vm.shipment.id_shipment|intval}</span>
        </div>
        <div class="sr-kv-row">
            <span class="sr-kv-label">{l s='Stato' mod='spedisciquishipping'}</span>
            <span class="sr-kv-value">
                <span class="sr-badge sr-badge-{$vm.shipment.status_class|escape:'html':'UTF-8'}">
                    <span class="sr-badge-dot"></span>
                    {$vm.shipment.status_label|escape:'html':'UTF-8'}
                </span>
            </span>
        </div>
        <div class="sr-kv-row">
            <span class="sr-kv-label">{l s='Peso' mod='spedisciquishipping'}</span>
            <span class="sr-kv-value">{$vm.shipment.weight} kg</span>
        </div>
        <div class="sr-kv-row">
            <span class="sr-kv-label">{l s='Costo Shipping' mod='spedisciquishipping'}</span>
            <span class="sr-kv-value">€ {$vm.shipment.shipping_cost|string_format:'%.2f'}</span>
        </div>
        <div class="sr-kv-row">
            <span class="sr-kv-label">{l s='Tracking' mod='spedisciquishipping'}</span>
            <span class="sr-kv-value">
                {if $vm.shipment.tracking_number}
                    <span class="sr-kv-mono">{$vm.shipment.tracking_number|escape:'html':'UTF-8'}</span>
                {else}
                    <span style="color:#c8d0db;">—</span>
                {/if}
            </span>
        </div>
    </div>
</div>