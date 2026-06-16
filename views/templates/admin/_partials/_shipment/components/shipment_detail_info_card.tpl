{**
 * Card sola lettura: Spedizione, Ordine, Destinatario, Corriere
 *}

{* RIGA 1 — Spedizione + Ordine *}
<div class="sr-grid">

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

    <div class="sr-card">
        <p class="sr-card-title">
            <i class="icon-list-ul"></i>
            {l s='Ordine' mod='spedisciquishipping'}
        </p>
        <div class="sr-kv">
            <div class="sr-kv-row">
                <span class="sr-kv-label">{l s='Riferimento' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value">
                    <a href="{$vm.form.order_detail_url|escape:'html':'UTF-8'}&id_order={$vm.order.id_order|intval}"
                        target="_blank" style="color:#1a6fc4;text-decoration:none;font-weight:700;">
                        {$vm.order.reference|escape:'html':'UTF-8'}
                        <i class="icon-external-link" style="font-size:10px;opacity:.6;"></i>
                    </a>
                </span>
            </div>
            <div class="sr-kv-row">
                <span class="sr-kv-label">{l s='Data ordine' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value">{$vm.order.date_add|escape:'html':'UTF-8'}</span>
            </div>
            <div class="sr-kv-row">
                <span class="sr-kv-label">{l s='Totale' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value">
                    {$vm.order.total_paid|string_format:'%.2f'} {$vm.order.currency|escape:'html':'UTF-8'}
                </span>
            </div>
            <div class="sr-kv-row">
                <span class="sr-kv-label">{l s='Pagamento' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value">{$vm.order.payment_method|escape:'html':'UTF-8'}</span>
            </div>
            <div class="sr-kv-row">
                <span class="sr-kv-label">{l s='Stato pag.' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value">
                    {if $vm.order.payment_status === 'paid'}
                        <span class="sr-badge sr-badge-paid">
                            <i class="icon-check" style="font-size:9px;"></i>
                            {l s='Pagato' mod='spedisciquishipping'}
                        </span>
                    {else}
                        <span class="sr-badge sr-badge-pending-pay">
                            <i class="icon-time" style="font-size:9px;"></i>
                            {$vm.order.payment_label|escape:'html':'UTF-8'}
                        </span>
                    {/if}
                </span>
            </div>
        </div>
    </div>

</div>

{* RIGA 2 — Destinatario + Corriere *}
<div class="sr-grid">

    <div class="sr-card">
        <p class="sr-card-title">
            <i class="icon-user"></i>
            {l s='Destinatario' mod='spedisciquishipping'}
        </p>
        <p class="sr-address-name">{$vm.recipient.full_name|escape:'html':'UTF-8'}</p>
        <p class="sr-address-line">{$vm.recipient.address1|escape:'html':'UTF-8'}</p>
        {if $vm.recipient.address2}
            <p class="sr-address-line">{$vm.recipient.address2|escape:'html':'UTF-8'}</p>
        {/if}
        <p class="sr-address-line">
            {$vm.recipient.postcode|escape:'html':'UTF-8'}
            {$vm.recipient.city|escape:'html':'UTF-8'}
            {if $vm.recipient.province}
                ({$vm.recipient.province|escape:'html':'UTF-8'})
            {/if}
        </p>
        <p class="sr-address-line">{$vm.recipient.country|escape:'html':'UTF-8'}</p>
        {if $vm.recipient.phone}
            <hr class="sr-divider">
            <p class="sr-address-phone">
                <i class="icon-phone"></i>
                {$vm.recipient.phone|escape:'html':'UTF-8'}
            </p>
        {/if}
    </div>

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

</div>