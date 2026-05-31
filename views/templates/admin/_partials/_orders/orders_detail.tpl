{**
 * SpedisciQui — Shipment Review
 * Template: views/templates/admin/shipment_review.tpl
 *
 * Variabili Smarty attese (da ShipmentReviewViewModel):
 *   $vm.shipment   → id_shipment, status, status_label, status_class, weight, base_cost, tracking_number
 *   $vm.order      → id_order, reference, date_add, total_paid, currency, payment_method, payment_status, payment_label
 *   $vm.recipient  → full_name, address1, address2, city, postcode, province, country, phone
 *   $vm.carrier    → carrier_code, service_code, service_name, estimated_days
 *   $vm.options    → available[] (type, label, description, has_value_field, value_label, value_min, value_max, cost_rate, enabled, tag)
 *                    selected{} (keyed by type → value)
 *   $vm.form       → action_url, back_url, id_shipment, base_cost_raw
 *}

<style>
    .sr-page *{box-sizing:border-box}
    .sr-page{padding:1.5rem 0;font-family:'Open Sans',-apple-system,BlinkMacSystemFont,sans-serif;color:#2c3e50}

    .sr-back{display:inline-flex;align-items:center;gap:6px;font-size:13px;color:#7a8a9a;text-decoration:none;margin-bottom:1.25rem;background:none;border:none;padding:0;cursor:pointer}
    .sr-back:hover{color:#1a2535}
    .sr-back i{font-size:13px}

    .sr-page-title{font-size:20px;font-weight:700;color:#1a2535;margin-bottom:4px}
    .sr-page-title span{color:#8a9ab0;font-weight:400}
    .sr-page-sub{font-size:13px;color:#8a9ab0;margin-bottom:1.5rem}

    .sr-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px}
    @media(max-width:860px){.sr-grid{grid-template-columns:1fr}}

    .sr-card{background:#fff;border:0.5px solid #e0e6ed;border-radius:10px;padding:1rem 1.25rem}
    .sr-card-title{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:.7px;color:#9aabb8;margin-bottom:12px;display:flex;align-items:center;gap:6px}
    .sr-card-title i{font-size:13px;color:#b0bcc8}

    .sr-kv{display:flex;flex-direction:column;gap:8px}
    .sr-kv-row{display:flex;justify-content:space-between;align-items:flex-start;gap:8px;font-size:13px}
    .sr-kv-label{color:#8a9ab0;white-space:nowrap;flex-shrink:0}
    .sr-kv-value{font-weight:600;text-align:right;word-break:break-word;color:#1a2535}
    .sr-kv-mono{font-family:'Courier New',monospace;font-size:12px;background:#f0f4f8;padding:1px 6px;border-radius:4px;color:#3a5276}

    .sr-divider{border:none;border-top:0.5px solid #edf0f3;margin:10px 0}

    .sr-badge{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:11px;font-weight:700;white-space:nowrap}
    .sr-badge-dot{width:5px;height:5px;border-radius:50%;background:currentColor;flex-shrink:0}
    .sr-badge-pending{background:#fff8e1;color:#856404;border:1px solid #ffd966}
    .sr-badge-label_created{background:#e6f9ee;color:#1e7e34;border:1px solid #b8e8c8}
    .sr-badge-error{background:#fdecea;color:#c0392b;border:1px solid #f5c0ba}
    .sr-badge-cancelled{background:#f0f3f6;color:#5a6a7a;border:1px solid #dde3ea}
    .sr-badge-paid{background:#e6f9ee;color:#155724;border:1px solid #b8e8c8}
    .sr-badge-pending-pay{background:#fdecea;color:#721c24;border:1px solid #f5c0ba}

    .sr-address-name{font-weight:700;font-size:14px;color:#1a2535;margin-bottom:3px}
    .sr-address-line{font-size:13px;color:#5a6a7a;line-height:1.6}
    .sr-address-phone{display:flex;align-items:center;gap:5px;font-size:13px;color:#5a6a7a;margin-top:8px}

    .sr-options-wrap{margin-bottom:12px}

    .sr-opt{border:1px solid #e0e6ed;border-radius:8px;overflow:hidden;margin-bottom:8px;transition:border-color .15s,box-shadow .15s}
    .sr-opt:last-child{margin-bottom:0}
    .sr-opt.sr-opt-active{border-color:#1a6fc4;box-shadow:0 0 0 2px rgba(26,111,196,.08)}
    .sr-opt.sr-opt-disabled{opacity:.55;pointer-events:none}

    .sr-opt-header{display:flex;align-items:flex-start;gap:12px;padding:14px 16px;cursor:pointer;user-select:none;background:#fff}
    .sr-opt-disabled .sr-opt-header{cursor:default}

    .sr-opt-check{margin-top:2px;flex-shrink:0}
    .sr-opt-check input[type="checkbox"]{width:16px;height:16px;cursor:pointer;accent-color:#1a6fc4}
    .sr-opt-check input[type="checkbox"]:disabled{cursor:not-allowed}

    .sr-opt-info{flex:1;min-width:0}
    .sr-opt-label{font-size:14px;font-weight:700;color:#1a2535;margin-bottom:3px;display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .sr-opt-desc{font-size:12px;color:#7a8a9a;line-height:1.5}

    .sr-opt-tag{font-size:10px;font-weight:700;padding:2px 8px;border-radius:99px;letter-spacing:.3px}
    .sr-opt-tag-rec{background:#e8f0fb;color:#1a6fc4;border:1px solid #bdd4f0}
    .sr-opt-tag-soon{background:#f0f3f6;color:#7a8a9a;border:1px solid #dde3ea}

    .sr-opt-body{border-top:1px solid #f0f3f6;background:#f8f9fb;padding:14px 16px 14px 44px;display:none}
    .sr-opt-body.sr-opt-body-visible{display:block}

    .sr-field-label{font-size:12px;font-weight:600;color:#7a8a9a;margin-bottom:5px;display:block}
    .sr-input-row{display:flex;align-items:center;gap:10px;flex-wrap:wrap}
    .sr-euro-wrap{position:relative;display:inline-flex;align-items:center}
    .sr-euro-sym{position:absolute;left:10px;font-size:13px;color:#9aabb8;pointer-events:none;z-index:1}
    .sr-value-input{padding:8px 10px 8px 26px;font-size:14px;border:1px solid #d0d8e4;border-radius:6px;width:150px;background:#fff;color:#1a2535;outline:none;transition:border-color .15s,box-shadow .15s}
    .sr-value-input:focus{border-color:#1a6fc4;box-shadow:0 0 0 3px rgba(26,111,196,.1)}
    .sr-cost-preview{font-size:12px;color:#8a9ab0}
    .sr-cost-preview strong{color:#1e7e34}

    .sr-notice{display:flex;align-items:flex-start;gap:8px;padding:10px 13px;border-radius:6px;background:#e8f0fb;border:1px solid #bdd4f0;font-size:12px;color:#1a5590;margin-top:10px;line-height:1.55}
    .sr-notice i{font-size:13px;flex-shrink:0;margin-top:1px}

    .sr-summary{background:#f5f7fa;border:1px solid #e8ecef;border-radius:10px;padding:1rem 1.25rem;margin-bottom:12px}
    .sr-sum-row{display:flex;justify-content:space-between;align-items:center;font-size:13px;padding:4px 0;color:#7a8a9a}
    .sr-sum-row b{color:#1a2535;font-weight:600}
    .sr-sum-row.sr-sum-hidden{display:none}
    .sr-sum-divider{border:none;border-top:1px solid #e0e6ed;margin:8px 0}
    .sr-sum-total{display:flex;justify-content:space-between;align-items:center;font-size:16px;font-weight:700;color:#1a2535}

    .sr-actions{display:flex;align-items:center;justify-content:space-between;gap:12px;padding-top:6px}
    .sr-btn-back{display:inline-flex;align-items:center;gap:6px;padding:9px 18px;font-size:13px;font-weight:600;border:1px solid #d0d8e4;border-radius:6px;background:#fff;color:#5a6a7a;cursor:pointer;transition:background .13s,color .13s;text-decoration:none}
    .sr-btn-back:hover{background:#f5f7fa;color:#1a2535;border-color:#b0bcc8}
    .sr-btn-confirm{display:inline-flex;align-items:center;gap:7px;padding:10px 22px;font-size:13px;font-weight:700;border-radius:6px;border:none;background:#1a6fc4;color:#fff;cursor:pointer;transition:background .13s,transform .1s}
    .sr-btn-confirm:hover{background:#155da0}
    .sr-btn-confirm:active{transform:scale(.98)}
    .sr-btn-confirm:disabled{opacity:.55;cursor:not-allowed;transform:none}

    .sr-spinner{width:14px;height:14px;border:2px solid rgba(255,255,255,.3);border-top-color:#fff;border-radius:50%;display:none;animation:sr-spin .7s linear infinite;flex-shrink:0}
    @keyframes sr-spin{to{transform:rotate(360deg)}}

    .sr-alert{display:flex;align-items:flex-start;gap:8px;padding:10px 14px;border-radius:6px;font-size:13px;margin-bottom:14px;border-left:3px solid transparent}
    .sr-alert-danger{background:#fdecea;border-color:#c0392b;color:#721c24}
    .sr-alert i{font-size:14px;flex-shrink:0;margin-top:1px}
</style>

<div class="sr-page">

    {* ── Breadcrumb back ── *}
    <a href="{$vm.form.back_url|escape:'html':'UTF-8'}" class="sr-back">
        <i class="icon-arrow-left"></i>
        {l s='Torna alla lista spedizioni' mod='spedisciquishipping'}
    </a>

    {* ── Titolo pagina ── *}
    <p class="sr-page-title">
        {l s='Revisione spedizione' mod='spedisciquishipping'}
        <span>#{$vm.shipment.id_shipment|intval}</span>
    </p>
    <p class="sr-page-sub">
        {l s='Verifica tutti i dati prima di creare la spedizione. Le operazioni non sono reversibili.' mod='spedisciquishipping'}
    </p>

    {* ── Errori di validazione (re-render dopo submit fallito) ── *}
    {if isset($vm.errors) && $vm.errors|count > 0}
        {foreach $vm.errors as $err}
            <div class="sr-alert sr-alert-danger">
                <i class="icon-warning-sign"></i>
                <span>{$err|escape:'html':'UTF-8'}</span>
            </div>
        {/foreach}
    {/if}

    {* ════════════════════════════════════════════════════════════
       RIGA 1 — Spedizione + Ordine
    ════════════════════════════════════════════════════════════ *}
    <div class="sr-grid">

        {* Card spedizione *}
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
                    <span class="sr-kv-value">{$vm.shipment.weight|escape:'html':'UTF-8'} kg</span>
                </div>
                <div class="sr-kv-row">
                    <span class="sr-kv-label">{l s='Costo base' mod='spedisciquishipping'}</span>
                    <span class="sr-kv-value">€ {$vm.shipment.base_cost|string_format:'%.2f'}</span>
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

        {* Card ordine *}
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
                           target="_blank"
                           style="color:#1a6fc4;text-decoration:none;font-weight:700;">
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

    {* ════════════════════════════════════════════════════════════
       RIGA 2 — Destinatario + Corriere
    ════════════════════════════════════════════════════════════ *}
    <div class="sr-grid">

        {* Card destinatario *}
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

        {* Card corriere *}
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
                <div class="sr-kv-row">
                    <span class="sr-kv-label">{l s='Servizio' mod='spedisciquishipping'}</span>
                    <span class="sr-kv-value">{$vm.carrier.service_name|escape:'html':'UTF-8'}</span>
                </div>
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

    {* ════════════════════════════════════════════════════════════
       FORM PRINCIPALE — avvolge opzioni + azioni
    ════════════════════════════════════════════════════════════ *}
    <form method="POST"
          action="{$vm.form.action_url|escape:'html':'UTF-8'}"
          id="sq-review-form"
          onsubmit="return sqReviewSubmit(event)">

        <input type="hidden" name="submitShipmentReview" value="1">
        <input type="hidden" name="id_shipment" value="{$vm.form.id_shipment|intval}">

        {* ════════════════════════════════════════════════════════
           SERVIZI AGGIUNTIVI
        ════════════════════════════════════════════════════════ *}
        <div class="sr-card sr-options-wrap" style="margin-bottom:12px;">
            <p class="sr-card-title">
                <i class="icon-shield"></i>
                {l s='Servizi aggiuntivi' mod='spedisciquishipping'}
            </p>

            {if isset($vm.options.available) && $vm.options.available|count > 0}

                {foreach $vm.options.available as $opt}

                    {* Determina se l'opzione era già selezionata (ritorno indietro) *}
                    {assign var="opt_selected" value=false}
                    {if isset($vm.options.selected[$opt.type]) && $vm.options.selected[$opt.type]}
                        {assign var="opt_selected" value=true}
                    {/if}

                    {* Valore pre-compilato per il campo numerico *}
                    {assign var="opt_value_key" value="{$opt.type}_value"}
                    {assign var="opt_saved_value" value=''}
                    {if isset($vm.options.selected[$opt_value_key])}
                        {assign var="opt_saved_value" value=$vm.options.selected[$opt_value_key]}
                    {/if}

                    <div class="sr-opt{if $opt_selected} sr-opt-active{/if}{if !$opt.enabled} sr-opt-disabled{/if}"
                         id="sr-opt-{$opt.type|escape:'html':'UTF-8'}">

                        <div class="sr-opt-header"
                             {if $opt.enabled}onclick="sqToggleOption('{$opt.type|escape:'js':'UTF-8'}')"{/if}>

                            <div class="sr-opt-check">
                                <input type="checkbox"
                                       name="option_{$opt.type|escape:'html':'UTF-8'}"
                                       id="sr-chk-{$opt.type|escape:'html':'UTF-8'}"
                                       value="1"
                                       {if $opt_selected}checked{/if}
                                       {if !$opt.enabled}disabled{/if}
                                       data-type="{$opt.type|escape:'html':'UTF-8'}"
                                       data-has-value="{if $opt.has_value_field}1{else}0{/if}"
                                       data-cost-rate="{$opt.cost_rate|floatval}"
                                       data-cost-type="{$opt.cost_formula|default:'percentage'|escape:'html':'UTF-8'}"
                                       onchange="sqOnCheckChange('{$opt.type|escape:'js':'UTF-8'}')">
                            </div>

                            <div class="sr-opt-info">
                                <p class="sr-opt-label">
                                    {$opt.label|escape:'html':'UTF-8'}
                                    {if isset($opt.tag) && $opt.tag}
                                        {if !$opt.enabled}
                                            <span class="sr-opt-tag sr-opt-tag-soon">
                                                {$opt.tag|escape:'html':'UTF-8'}
                                            </span>
                                        {else}
                                            <span class="sr-opt-tag sr-opt-tag-rec">
                                                {$opt.tag|escape:'html':'UTF-8'}
                                            </span>
                                        {/if}
                                    {/if}
                                </p>
                                <p class="sr-opt-desc">{$opt.description|escape:'html':'UTF-8'}</p>
                            </div>

                        </div>

                        {* Body con campo valore — visibile solo se has_value_field e selezionato *}
                        {if $opt.has_value_field}
                            <div class="sr-opt-body{if $opt_selected} sr-opt-body-visible{/if}"
                                 id="sr-body-{$opt.type|escape:'html':'UTF-8'}">
                                <label class="sr-field-label"
                                       for="sr-val-{$opt.type|escape:'html':'UTF-8'}">
                                    {$opt.value_label|escape:'html':'UTF-8'}
                                </label>
                                <div class="sr-input-row">
                                    <div class="sr-euro-wrap">
                                        <span class="sr-euro-sym">€</span>
                                        <input type="number"
                                               class="sr-value-input"
                                               id="sr-val-{$opt.type|escape:'html':'UTF-8'}"
                                               name="option_{$opt.type|escape:'html':'UTF-8'}_value"
                                               min="{$opt.value_min|floatval}"
                                               max="{$opt.value_max|floatval}"
                                               step="0.01"
                                               value="{if $opt_saved_value}{$opt_saved_value|floatval}{else}{$vm.order.total_paid|floatval}{/if}"
                                               placeholder="0.00"
                                               oninput="sqCalcOptionCost('{$opt.type|escape:'js':'UTF-8'}')">
                                    </div>
                                    <span class="sr-cost-preview" id="sr-cost-{$opt.type|escape:'html':'UTF-8'}">
                                        {* popolato da JS *}
                                    </span>
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

            {else}
                <p style="font-size:13px;color:#9aabb8;margin:0;">
                    {l s='Nessun servizio aggiuntivo disponibile per questo corriere.' mod='spedisciquishipping'}
                </p>
            {/if}

        </div>

        {* ════════════════════════════════════════════════════════
           RIEPILOGO COSTI
        ════════════════════════════════════════════════════════ *}
        <div class="sr-summary">
            <div class="sr-sum-row">
                <span>{l s='Costo base spedizione' mod='spedisciquishipping'}</span>
                <b>€ {$vm.shipment.base_cost|string_format:'%.2f'}</b>
            </div>

            {* Righe aggiuntive per ogni opzione con costo — generate dal JS tramite id *}
            {foreach $vm.options.available as $opt}
                {if $opt.has_value_field && $opt.enabled}
                    {assign var="opt_sel" value=false}
                    {if isset($vm.options.selected[$opt.type]) && $vm.options.selected[$opt.type]}
                        {assign var="opt_sel" value=true}
                    {/if}
                    <div class="sr-sum-row{if !$opt_sel} sr-sum-hidden{/if}"
                         id="sr-sum-row-{$opt.type|escape:'html':'UTF-8'}">
                        <span id="sr-sum-label-{$opt.type|escape:'html':'UTF-8'}">
                            {$opt.label|escape:'html':'UTF-8'}
                        </span>
                        <b id="sr-sum-val-{$opt.type|escape:'html':'UTF-8'}">€ 0.00</b>
                    </div>
                {/if}
            {/foreach}

            <hr class="sr-sum-divider">
            <div class="sr-sum-total">
                <span>{l s='Totale stimato' mod='spedisciquishipping'}</span>
                <span id="sr-grand-total">€ {$vm.shipment.base_cost|string_format:'%.2f'}</span>
            </div>
        </div>

        {* ════════════════════════════════════════════════════════
           AZIONI
        ════════════════════════════════════════════════════════ *}
        <div class="sr-actions">
            <a href="{$vm.form.back_url|escape:'html':'UTF-8'}" class="sr-btn-back">
                <i class="icon-arrow-left"></i>
                {l s='Annulla' mod='spedisciquishipping'}
            </a>
            <button type="submit" class="sr-btn-confirm" id="sr-submit-btn">
                <i class="icon-truck" id="sr-submit-icon"></i>
                <span id="sr-submit-label">
                    {l s='Conferma e crea spedizione' mod='spedisciquishipping'}
                </span>
                <div class="sr-spinner" id="sr-spinner"></div>
            </button>
        </div>

    </form>

</div>

<script>
(function () {

    var BASE_COST = parseFloat('{$vm.form.base_cost_raw|floatval}') || 0;

    {* Costruisce da PHP/Smarty un oggetto JS con i rate delle opzioni *}
    var SQ_OPTIONS = {ldelim};
        {foreach $vm.options.available as $opt}
            {if $opt.has_value_field && $opt.enabled}
            '{$opt.type|escape:'js':'UTF-8'}': {ldelim}
                rate: {$opt.cost_rate|floatval},
                formula: '{$opt.cost_formula|default:'percentage'|escape:'js':'UTF-8'}',
                label: '{$opt.label|escape:'js':'UTF-8'}',
                min: {$opt.value_min|floatval},
                max: {$opt.value_max|floatval}
            {rdelim},
            {/if}
        {/foreach}
    {rdelim};

    {* ── Calcola costo singola opzione ── *}
    window.sqCalcOptionCost = function (type) {
        if (!SQ_OPTIONS[type]) return;
        var cfg     = SQ_OPTIONS[type];
        var input   = document.getElementById('sr-val-' + type);
        if (!input) return;

        var val = Math.min(parseFloat(input.value) || 0, cfg.max);
        var cost = 0;

        if (cfg.formula === 'percentage') {
            cost = parseFloat((val * cfg.rate / 100).toFixed(2));
        } else if (cfg.formula === 'flat') {
            cost = cfg.rate;
        }

        var preview = document.getElementById('sr-cost-' + type);
        if (preview) {
            preview.innerHTML = '{l s='Premio stimato:' mod='spedisciquishipping' js=1} <strong>€ ' + cost.toFixed(2) + '</strong>';
        }

        var sumVal = document.getElementById('sr-sum-val-' + type);
        if (sumVal) sumVal.textContent = '€ ' + cost.toFixed(2);

        sqUpdateTotal();
    };

    {* ── Aggiorna totale ── *}
    window.sqUpdateTotal = function () {
        var total = BASE_COST;

        Object.keys(SQ_OPTIONS).forEach(function (type) {
            var chk = document.getElementById('sr-chk-' + type);
            if (!chk || !chk.checked) return;

            var cfg   = SQ_OPTIONS[type];
            var input = document.getElementById('sr-val-' + type);
            var val   = input ? Math.min(parseFloat(input.value) || 0, cfg.max) : 0;
            var cost  = 0;

            if (cfg.formula === 'percentage') {
                cost = parseFloat((val * cfg.rate / 100).toFixed(2));
            } else if (cfg.formula === 'flat') {
                cost = cfg.rate;
            }
            total += cost;
        });

        var el = document.getElementById('sr-grand-total');
        if (el) el.textContent = '€ ' + total.toFixed(2);
    };

    {* ── Toggle check da click sul wrapper ── *}
    window.sqToggleOption = function (type) {
        var chk = document.getElementById('sr-chk-' + type);
        if (!chk || chk.disabled) return;
        chk.checked = !chk.checked;
        sqOnCheckChange(type);
    };

    {* ── Reazione al cambio checkbox ── *}
    window.sqOnCheckChange = function (type) {
        var chk   = document.getElementById('sr-chk-' + type);
        var block = document.getElementById('sr-opt-' + type);
        var body  = document.getElementById('sr-body-' + type);
        var sumRow = document.getElementById('sr-sum-row-' + type);

        if (!chk) return;

        if (chk.checked) {
            if (block)  block.classList.add('sr-opt-active');
            if (body)   body.classList.add('sr-opt-body-visible');
            if (sumRow) sumRow.classList.remove('sr-sum-hidden');
        } else {
            if (block)  block.classList.remove('sr-opt-active');
            if (body)   body.classList.remove('sr-opt-body-visible');
            if (sumRow) sumRow.classList.add('sr-sum-hidden');
        }

        sqCalcOptionCost(type);
    };

    {* ── Anti double-submit ── *}
    window.sqReviewSubmit = function (e) {
        var btn    = document.getElementById('sr-submit-btn');
        var icon   = document.getElementById('sr-submit-icon');
        var label  = document.getElementById('sr-submit-label');
        var spinner = document.getElementById('sr-spinner');

        if (btn.disabled) {
            e.preventDefault();
            return false;
        }

        btn.disabled         = true;
        icon.style.display   = 'none';
        spinner.style.display = 'inline-block';
        label.textContent    = '{l s='Invio in corso...' mod='spedisciquishipping' js=1}';

        return true;
    };

    {* ── Init: calcola tutti i costi al caricamento ── *}
    document.addEventListener('DOMContentLoaded', function () {
        Object.keys(SQ_OPTIONS).forEach(function (type) {
            sqCalcOptionCost(type);
        });
        sqUpdateTotal();
    });

}());
</script>