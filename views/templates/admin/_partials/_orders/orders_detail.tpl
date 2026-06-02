{**
 * SpedisciQui — Shipment Review
 * Template: views/templates/admin/_partials/_orders/shipment_review.tpl
 *}

<div class="sr-page">

    {* ── Breadcrumb back ── *}
    <a href="{$vm.form.back_url|escape:'html':'UTF-8'}" class="sr-back">
        <i class="icon-arrow-left"></i>
        {l s='Torna alla lista spedizioni' mod='spedisciquishipping'}
    </a>

    {* ── Titolo ── *}
    <p class="sr-page-title">
        {l s='Revisione spedizione' mod='spedisciquishipping'}
        <span>#{$vm.shipment.id_shipment|intval}</span>
    </p>
    <p class="sr-page-sub">
        {l s='Verifica tutti i dati prima di creare la spedizione. Le operazioni non sono reversibili.' mod='spedisciquishipping'}
    </p>

    {* ── Errori flash ── *}
    {if isset($smarty.get.sq_error) && $smarty.get.sq_error}
        <div class="sr-alert sr-alert-danger">
            <i class="icon-warning-sign"></i>
            <span>{$smarty.get.sq_error|escape:'html':'UTF-8'}</span>
        </div>
    {/if}

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

    {* ════════════════════════════════════════
       FORM PRINCIPALE
    ════════════════════════════════════════ *}
    <form method="POST" action="{$vm.form.action_url|escape:'html':'UTF-8'}" id="sq-review-form"
        onsubmit="return sqReviewSubmit(event)">

        <input type="hidden" name="createShipment" value="1">
        <input type="hidden" name="id_shipment" value="{$vm.form.id_shipment|intval}">

        {* ── Assicurazione ── *}
        <div class="sr-card" style="margin-bottom:12px;" id="sr-insurance-block">
            <p class="sr-card-title">
                <i class="icon-shield"></i>
                {l s='Assicurazione' mod='spedisciquishipping'}
            </p>

            {* Riga checkbox *}
            <div style="display:flex;align-items:flex-start;gap:12px;">
                <input type="checkbox" id="sr-chk-insurance" name="insurance_enabled" value="1"
                    style="width:16px;height:16px;margin-top:3px;cursor:pointer;flex-shrink:0;"
                    onchange="sqToggleInsurance(this.checked)">
                <div>
                    <label for="sr-chk-insurance"
                        style="font-size:13px;font-weight:700;color:#1a2535;cursor:pointer;display:block;margin-bottom:3px;">
                        {l s='Aggiungi assicurazione alla spedizione' mod='spedisciquishipping'}
                    </label>
                    <p style="font-size:12px;color:#7a8a9a;margin:0;line-height:1.5;">
                        {l s='Copre il valore dichiarato della merce in caso di smarrimento o danneggiamento.' mod='spedisciquishipping'}
                    </p>
                </div>
            </div>

            {* Campo importo — visibile solo se checkbox attiva *}
            <div id="sr-insurance-body"
                style="display:none;margin-top:14px;padding-top:14px;border-top:1px solid #edf0f3;">
                <label class="sr-field-label" for="sr-insurance-value">
                    {l s='Valore dichiarato da assicurare (€)' mod='spedisciquishipping'}
                </label>
                <div style="display:flex;align-items:center;gap:14px;">
                    <div class="sr-euro-wrap">
                        <span class="sr-euro-sym">€</span>
                        <input type="number" id="sr-insurance-value" name="insurance_value" class="sr-value-input"
                            min="0.01" max="99999.99" step="0.01" value="{$vm.order.total_paid|floatval}"
                            placeholder="0.00" oninput="sqUpdateInsuranceSummary()">
                    </div>
                    <span id="sr-insurance-cost-preview" style="font-size:12px;color:#5a6a7a;"></span>
                </div>
                <div class="sr-notice" style="margin-top:10px;">
                    <i class="icon-info-circle"></i>
                    <span>
                        {l s='Il valore assicurato non può superare il valore reale della merce. Verrà verificato in fase di sinistro.' mod='spedisciquishipping'}
                    </span>
                </div>
            </div>
        </div>

        {* ── Servizi aggiuntivi ── *}
        {if isset($vm.options.available) && $vm.options.available|count > 0}
            <div class="sr-card" style="margin-bottom:12px;">
                <p class="sr-card-title">
                    <i class="icon-plus-sign"></i>
                    {l s='Servizi aggiuntivi' mod='spedisciquishipping'}
                </p>
                {foreach $vm.options.available as $opt}
                    {assign var='opt_selected' value=false}
                    {if isset($vm.options.selected[$opt.type]) && $vm.options.selected[$opt.type]}
                        {assign var='opt_selected' value=true}
                    {/if}
                    <div class="sr-opt{if $opt_selected} sr-opt-active{/if}{if !$opt.enabled} sr-opt-disabled{/if}"
                        id="sr-opt-{$opt.type|escape:'html':'UTF-8'}">
                        <div class="sr-opt-header" {if $opt.enabled}onclick="sqToggleOption('{$opt.type|escape:'js':'UTF-8'}')"
                            {/if}>
                            <div class="sr-opt-check">
                                <input type="checkbox" name="option_{$opt.type|escape:'html':'UTF-8'}"
                                    id="sr-chk-{$opt.type|escape:'html':'UTF-8'}" value="1" {if $opt_selected}checked{/if}
                                    {if !$opt.enabled}disabled{/if} data-type="{$opt.type|escape:'html':'UTF-8'}"
                                    data-cost-rate="{$opt.cost_rate|floatval}"
                                    data-cost-type="{$opt.cost_formula|default:'percentage'|escape:'html':'UTF-8'}"
                                    onchange="sqOnCheckChange('{$opt.type|escape:'js':'UTF-8'}')">
                            </div>
                            <div class="sr-opt-info">
                                <p class="sr-opt-label">
                                    {$opt.label|escape:'html':'UTF-8'}
                                    {if isset($opt.tag) && $opt.tag}
                                        <span class="sr-opt-tag {if $opt.enabled}sr-opt-tag-rec{else}sr-opt-tag-soon{/if}">
                                            {$opt.tag|escape:'html':'UTF-8'}
                                        </span>
                                    {/if}
                                </p>
                                <p class="sr-opt-desc">{$opt.description|escape:'html':'UTF-8'}</p>
                            </div>
                        </div>
                        {if $opt.has_value_field}
                            <div class="sr-opt-body{if $opt_selected} sr-opt-body-visible{/if}"
                                id="sr-body-{$opt.type|escape:'html':'UTF-8'}">
                                <label class="sr-field-label" for="sr-val-{$opt.type|escape:'html':'UTF-8'}">
                                    {$opt.value_label|escape:'html':'UTF-8'}
                                </label>
                                <div class="sr-input-row">
                                    <div class="sr-euro-wrap">
                                        <span class="sr-euro-sym">€</span>
                                        <input type="number" class="sr-value-input" id="sr-val-{$opt.type|escape:'html':'UTF-8'}"
                                            name="option_{$opt.type|escape:'html':'UTF-8'}_value" min="{$opt.value_min|floatval}"
                                            max="{$opt.value_max|floatval}" step="0.01" value="{$vm.order.total_paid|floatval}"
                                            placeholder="0.00" oninput="sqCalcOptionCost('{$opt.type|escape:'js':'UTF-8'}')">
                                    </div>
                                    <span class="sr-cost-preview" id="sr-cost-{$opt.type|escape:'html':'UTF-8'}"></span>
                                </div>
                                {if isset($opt.notice) && $opt.notice}
                                    <div class="sr-notice">
                                        <i class="icon-info-circle"></i>
                                        <span>{$opt.notice|escape:'html':'UTF-8'}</span>
                                    </div>
                                {/if}
                            </div>
                        {/if}
                    </div>
                {/foreach}
            </div>
        {/if}

        {* ── Riepilogo costi ── *}
        <div class="sr-summary">
            <div class="sr-sum-row">
                <span>{l s='Costo base spedizione' mod='spedisciquishipping'}</span>
                <b>€ {$vm.shipment.shipping_cost|string_format:'%.2f'}</b>
            </div>

            {* Riga assicurazione — mostrata/nascosta via JS *}
            <div class="sr-sum-row sr-sum-hidden" id="sr-sum-row-insurance">
                <span>{l s='Assicurazione' mod='spedisciquishipping'}</span>
                <b id="sr-sum-val-insurance">€ 0.00</b>
            </div>

            {if isset($vm.options.available) && $vm.options.available|count > 0}
                {foreach $vm.options.available as $opt}
                    {if $opt.enabled}
                        <div class="sr-sum-row sr-sum-hidden" id="sr-sum-row-{$opt.type|escape:'html':'UTF-8'}">
                            <span>{$opt.label|escape:'html':'UTF-8'}</span>
                            <b id="sr-sum-val-{$opt.type|escape:'html':'UTF-8'}">€ 0.00</b>
                        </div>
                    {/if}
                {/foreach}
            {/if}

            <hr class="sr-sum-divider">
            <div class="sr-sum-total">
                <span>{l s='Totale stimato' mod='spedisciquishipping'}</span>
                <span id="sr-grand-total">€ {$vm.shipment.shipping_cost|string_format:'%.2f'}</span>
            </div>
        </div>

        {* ── Azioni ── *}
        <div class="sr-actions">
            {* back link *}
            <a href="{$vm.form.back_url|escape:'html':'UTF-8'}" class="sr-btn-back">
                <i class="icon-arrow-left"></i>
                {l s='Annulla' mod='spedisciquishipping'}
            </a>
            {* conferma shipping *}
            <form action={$vm.form.action} name='createShipment'>
                <button type="submit" class="sr-btn-confirm" id="sr-submit-btn">
                    <i class="icon-truck" id="sr-submit-icon"></i>
                    <span id="sr-submit-label">
                        {l s='Conferma e crea spedizione' mod='spedisciquishipping'}
                    </span>
                    <div class="sr-spinner" id="sr-spinner"></div>
                </button>
            </form>
        </div>

    </form>

</div>

{* ── Variabili JS iniettate da Smarty ── *}
<script>
    window.shipping_cost       = parseFloat('{$vm.form.shipping_cost_raw|floatval}') || 0;
    window.INSURANCE_RATE  = 0.5; {* % sul valore dichiarato — adattare o passare da $vm *}
    window.SQ_OPTIONS      = {ldelim}
    {if isset($vm.options.available) && $vm.options.available|count > 0}
        {foreach $vm.options.available as $opt}
            {if isset($opt.has_value_field) && $opt.has_value_field && $opt.enabled}
                '{$opt.type|escape:'js':'UTF-8'}': {ldelim}
                rate:    {$opt.cost_rate|floatval},
                formula: '{$opt.cost_formula|default:'percentage'|escape:'js':'UTF-8'}',
                min:     {$opt.value_min|floatval},
                max:     {$opt.value_max|floatval}
                {rdelim},
            {/if}
        {/foreach}
    {/if}
    {rdelim};

    {* Funzioni assicurazione *}
    window.sqToggleInsurance = function(checked) {
        var body = document.getElementById('sr-insurance-body');
        var sumRow = document.getElementById('sr-sum-row-insurance');
        var block = document.getElementById('sr-insurance-block');
        if (body) body.style.display = checked ? 'block' : 'none';
        if (sumRow) sumRow.classList.toggle('sr-sum-hidden', !checked);
        if (block) block.style.borderColor = checked ? '#1a6fc4' : '';
        sqUpdateInsuranceSummary();
    };

    window.sqUpdateInsuranceSummary = function() {
        var input = document.getElementById('sr-insurance-value');
        var preview = document.getElementById('sr-insurance-cost-preview');
        var sumVal = document.getElementById('sr-sum-val-insurance');
        var chk = document.getElementById('sr-chk-insurance');
        if (!chk || !chk.checked || !input) return;
        var declared = parseFloat(input.value) || 0;
        var cost = parseFloat((declared * window.INSURANCE_RATE / 100).toFixed(2));
        if (preview) preview.innerHTML = '{l s="Premio stimato:" mod="spedisciquishipping"} <strong>€ ' + cost.toFixed(2) + '</strong>';
        if (sumVal) sumVal.textContent = '€ ' + cost.toFixed(2);
        sqUpdateTotal();
    };
</script>