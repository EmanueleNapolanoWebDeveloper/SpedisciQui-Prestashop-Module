{**
* Card sola lettura: Spedizione, Ordine, Destinatario, Corriere
*}

{* RIGA 1 — Spedizione + Ordine — Stile Enterprise *}
<div class="sr-grid">

    {* Card Spedizione — Stile Enterprise *}
    <div class="sr-card" style="flex: 1;">
        <p class="sr-card-title">
            <i class="icon-plane"></i>
            {l s='Informazioni Spedizione' mod='spedisciquishipping'}
        </p>

        <div class="sr-kv" style="margin-top: 15px;">
            <div class="sr-kv-row" style="padding: 9px 0; border-bottom: 1px solid #edf1f5;">
                <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='ID Spedizione' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value" style="font-weight: 600; color: #2e2e2e;">#{$vm.shipment.id_shipment|intval}</span>
            </div>

            <div class="sr-kv-row" style="padding: 9px 0; border-bottom: 1px solid #edf1f5;">
                <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='Stato Spedizione' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value">
                    <span class="sr-badge sr-badge-{$vm.shipment.status_class|escape:'html':'UTF-8'}" style="font-weight: 600;">
                        {$vm.shipment.status_label|escape:'html':'UTF-8'}
                    </span>
                </span>
            </div>

            <div class="sr-kv-row" style="padding: 9px 0; border-bottom: 1px solid #edf1f5;">
                <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='Massa / Peso' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value" style="color: #2e2e2e; font-weight: 500;">{$vm.shipment.weight} kg</span>
            </div>

            <div class="sr-kv-row" style="padding: 9px 0; border-bottom: 1px solid #edf1f5;">
                <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='Oneri di Trasporto' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value" style="font-weight: 600; color: #2e2e2e;">€ {$vm.shipment.shipping_cost|string_format:'%.2f'}</span>
            </div>

            <div class="sr-kv-row" style="padding: 9px 0;">
                <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='Codice Tracking della Spedizione' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value">
                    {if $vm.shipment.tracking_number}
                        <span class="sr-kv-mono" style="font-family: monospace; font-size: 12px; color: #2e2e2e; font-weight: 600;">{$vm.shipment.tracking_number|escape:'html':'UTF-8'}</span>
                    {else}
                        <span style="color: #c8d0db; font-weight: normal;">—</span>
                    {/if}
                </span>
            </div>

            <div class="sr-kv-row" style="padding: 9px 0;">
                <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='Link Tracciamento' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value">
                    {if $vm.shipment.tracking_url}
                        <a href="{$vm.shipment.tracking_url|escape:'html':'UTF-8'}" target="_blank" style="color: #1a6fc4; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                            {l s='Segui Spedizione' mod='spedisciquishipping'}
                            <i class="icon-external-link" style="font-size: 11px; opacity: 0.7;"></i>
                        </a>
                    {else}
                        <span style="color: #c8d0db; font-weight: normal;">—</span>
                    {/if}
                </span>
            </div>
        </div>
    </div>

    {* Card Ordine — Stile Enterprise *}
    <div class="sr-card" style="flex: 1;">
        <p class="sr-card-title">
            <i class="icon-list-ul"></i>
            {l s='Dettagli Ordine' mod='spedisciquishipping'}
        </p>

        <div class="sr-kv" style="margin-top: 15px;">
            <div class="sr-kv-row" style="padding: 9px 0; border-bottom: 1px solid #edf1f5;">
                <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='Codice Riferimento' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value">
                    <a href="{$vm.form.order_detail_url|escape:'html':'UTF-8'}&id_order={$vm.order.id_order|intval}" target="_blank" style="color: #1a6fc4; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 4px;">
                        {$vm.order.reference|escape:'html':'UTF-8'}
                        <i class="icon-external-link" style="font-size: 11px; opacity: 0.7;"></i>
                    </a>
                </span>
            </div>

            <div class="sr-kv-row" style="padding: 9px 0; border-bottom: 1px solid #edf1f5;">
                <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='Data e Ora Ricezione' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value" style="color: #495057;">{$vm.order.date_add|escape:'html':'UTF-8'}</span>
            </div>

            <div class="sr-kv-row" style="padding: 9px 0; border-bottom: 1px solid #edf1f5;">
                <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='Corrispettivo Totale' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value" style="font-weight: 600; color: #2e2e2e;">
                    {$vm.order.total_paid|string_format:'%.2f'} {$vm.order.currency|escape:'html':'UTF-8'}
                </span>
            </div>

            <div class="sr-kv-row" style="padding: 9px 0; border-bottom: 1px solid #edf1f5;">
                <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='Strumento di Pagamento' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value" style="color: #495057;">{$vm.order.payment_method|escape:'html':'UTF-8'}</span>
            </div>

            <div class="sr-kv-row" style="padding: 9px 0;">
                <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='Stato della Transazione' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value">
                    {if $vm.order.payment_status === 'paid'}
                        <span class="sr-badge sr-badge-paid" style="font-weight: 600;">
                            <i class="icon-check" style="font-size: 10px; margin-right: 2px;"></i>
                            {l s='Pagato' mod='spedisciquishipping'}
                        </span>
                    {else}
                        <span class="sr-badge sr-badge-pending-pay" style="font-weight: 600;">
                            <i class="icon-time" style="font-size: 10px; margin-right: 2px;"></i>
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

    {* Card Destinatario — Stile Enterprise *}
    <div class="sr-card" style="flex: 1.5;">
        <p class="sr-card-title">
            <i class="icon-user"></i>
            {l s='Informazioni di Consegna' mod='spedisciquishipping'}
        </p>

        <div style="margin-top: 15px; display: flex; flex-direction: column; gap: 8px;">
            <!-- Intestazione Nominativo -->
            <div style="border-bottom: 1px solid #edf1f5; padding-bottom: 10px; margin-bottom: 5px;">
                <p class="sr-address-name" style="font-size: 15px; font-weight: 600; color: #2e2e2e; margin: 0;">
                    {$vm.recipient.full_name|escape:'html':'UTF-8'}
                </p>
                {if $vm.recipient.company}
                    <p style="font-size: 12px; color: #6c757d; margin: 2px 0 0 0; text-transform: uppercase; letter-spacing: 0.5px;">
                        {$vm.recipient.company|escape:'html':'UTF-8'}
                    </p>
                {/if}
            </div>

            <!-- Blocco Indirizzo strutturato -->
            <div style="font-size: 13px; color: #353535; line-height: 1.6;">
                <div style="display: flex; margin-bottom: 4px;">
                    <span style="width: 80px; color: #8898a5; font-size: 11px; text-transform: uppercase; padding-top: 2px;">{l s='Indirizzo' mod='spedisciquishipping'}</span>
                    <span style="flex: 1; font-weight: 500;">
                        {$vm.recipient.address1|escape:'html':'UTF-8'}
                        {if $vm.recipient.address2}
                            <br><span style="color: #6c757d; font-weight: normal;">{$vm.recipient.address2|escape:'html':'UTF-8'}</span>
                        {/if}
                    </span>
                </div>

                <div style="display: flex; margin-bottom: 4px;">
                    <span style="width: 80px; color: #8898a5; font-size: 11px; text-transform: uppercase;">{l s='Città / CAP' mod='spedisciquishipping'}</span>
                    <span style="flex: 1;">
                        <strong>{$vm.recipient.postcode|escape:'html':'UTF-8'}</strong> {$vm.recipient.city|escape:'html':'UTF-8'}
                        {if $vm.recipient.province}
                            <span style="color: #495057;">({$vm.recipient.province|escape:'html':'UTF-8'})</span>
                        {/if}
                    </span>
                </div>

                <div style="display: flex; margin-bottom: 4px;">
                    <span style="width: 80px; color: #8898a5; font-size: 11px; text-transform: uppercase;">{l s='Paese' mod='spedisciquishipping'}</span>
                    <span style="flex: 1; color: #495057;">{$vm.recipient.country|escape:'html':'UTF-8'}</span>
                </div>

                {if $vm.recipient.phone}
                    <div style="display: flex; margin-top: 10px; padding-top: 10px; border-top: 1px dashed #edf1f5;">
                        <span style="width: 80px; color: #8898a5; font-size: 11px; text-transform: uppercase;">{l s='Telefono' mod='spedisciquishipping'}</span>
                        <span style="flex: 1; font-weight: 600; color: #2e2e2e;">
                            <i class="icon-phone" style="color: #a0aab5; margin-right: 4px;"></i> {$vm.recipient.phone|escape:'html':'UTF-8'}
                        </span>
                    </div>
                {/if}
            </div>
        </div>
    </div>

    {* Card Corriere — Allineamento Rigido Key-Value *}
    <div class="sr-card" style="flex: 1;">
        <p class="sr-card-title">
            <i class="icon-truck"></i>
            {l s='Dati Vettore' mod='spedisciquishipping'}
        </p>
        <div class="sr-kv" style="margin-top: 15px;">
            <div class="sr-kv-row" style="padding: 9px 0; border-bottom: 1px solid #edf1f5;">
                <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='Operatore' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value" style="font-weight: 600; color: #2e2e2e; text-transform: uppercase; letter-spacing: 0.5px;">{$vm.carrier.carrier_code|escape:'html':'UTF-8'}</span>
            </div>

            <div class="sr-kv-row" style="padding: 9px 0; border-bottom: 1px solid #edf1f5;">
                <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='Identificativo Servizio' mod='spedisciquishipping'}</span>
                <span class="sr-kv-value">
                    <span class="sr-kv-mono" style="font-family: monospace; font-size: 12px; color: #2e2e2e; font-weight: 600;">{$vm.carrier.service_code|escape:'html':'UTF-8'}</span>
                </span>
            </div>

            {if $vm.carrier.service_name}
                <div class="sr-kv-row" style="padding: 9px 0; border-bottom: 1px solid #edf1f5;">
                    <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='Descrizione Servizio' mod='spedisciquishipping'}</span>
                    <span class="sr-kv-value" style="color: #495057;">{$vm.carrier.service_name|escape:'html':'UTF-8'}</span>
                </div>
            {/if}

            {if $vm.carrier.estimated_days}
                <div class="sr-kv-row" style="padding: 9px 0;">
                    <span class="sr-kv-label" style="color: #6c757d; font-weight: normal;">{l s='SLA di Consegna' mod='spedisciquishipping'}</span>
                    <span class="sr-kv-value" style="font-weight: 600; color: #2e2e2e;">
                        {$vm.carrier.estimated_days|intval} {l s='gg. lavorativi' mod='spedisciquishipping'}
                    </span>
                </div>
            {/if}
        </div>
    </div>

</div>