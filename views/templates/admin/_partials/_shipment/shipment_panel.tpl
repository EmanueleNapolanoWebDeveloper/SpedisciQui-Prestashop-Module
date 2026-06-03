<style>
    .sq-orders {
        font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, sans-serif;
        color: #2c3e50;
    }

    /* ── Header ── */
    .sq-orders-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 24px;
        border-bottom: 1px solid #e8ecef;
        background: #fff;
    }

    .sq-orders-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sq-orders-title {
        font-size: 15px;
        font-weight: 700;
        color: #1a2535;
        margin: 0;
    }

    .sq-orders-subtitle {
        font-size: 12px;
        color: #8a9ab0;
        margin: 2px 0 0;
    }

    .sq-count-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 10px;
        background: #1a6fc4;
        color: #fff;
        font-size: 13px;
        font-weight: 700;
        border-radius: 14px;
    }

    /* ── Alert flash ── */
    .sq-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 6px;
        font-size: 13px;
        margin: 16px 24px;
        border-left: 3px solid transparent;
    }

    .sq-alert-success {
        background: #e6f9ee;
        border-color: #1e7e34;
        color: #155724;
    }

    .sq-alert-danger {
        background: #fdecea;
        border-color: #c0392b;
        color: #721c24;
    }

    .sq-alert i {
        font-size: 15px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    /* ── Filtro ── */
    .sq-filter-bar {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 24px;
        background: #f8f9fb;
        border-bottom: 1px solid #e8ecef;
    }

    .sq-filter-label {
        font-size: 12px;
        font-weight: 600;
        color: #7a8a9a;
        white-space: nowrap;
    }

    .sq-filter-bar select {
        font-size: 13px;
        padding: 6px 10px;
        border: 1px solid #d0d8e4;
        border-radius: 5px;
        background: #fff;
        color: #2c3e50;
        outline: none;
        cursor: pointer;
        min-width: 180px;
    }

    .sq-filter-bar select:focus {
        border-color: #1a6fc4;
        box-shadow: 0 0 0 2px rgba(26, 111, 196, 0.12);
    }

    .sq-filter-btn {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 6px 14px;
        background: #1a6fc4;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
    }

    .sq-filter-btn:hover {
        background: #155da0;
    }

    /* ── Tabella ── */
    .sq-table-wrap {
        overflow-x: auto;
    }

    .sq-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 900px;
        font-size: 13px;
    }

    .sq-table thead tr {
        background: #f5f7fa;
        border-bottom: 2px solid #dde3ea;
    }

    .sq-table thead th {
        padding: 10px 14px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #7a8a9a;
        white-space: nowrap;
        vertical-align: middle;
    }

    .sq-table tbody tr {
        border-bottom: 1px solid #f0f3f6;
        transition: background 0.12s;
    }

    .sq-table tbody tr:last-child {
        border-bottom: none;
    }

    .sq-table tbody tr:hover {
        background: #f7f9fc;
    }

    .sq-table td {
        padding: 12px 14px;
        vertical-align: middle;
        color: #2c3e50;
    }

    /* ── Celle specifiche ── */
    .sq-order-id {
        font-weight: 700;
        font-size: 13px;
        color: #1a6fc4;
        text-decoration: none;
    }

    .sq-order-id:hover {
        text-decoration: underline;
    }

    .sq-customer-name {
        font-weight: 600;
        color: #1a2535;
        font-size: 13px;
    }

    .sq-customer-meta {
        font-size: 11px;
        color: #9aabb8;
        margin-top: 2px;
    }

    .sq-tracking code {
        background: #f0f4f8;
        border: 1px solid #dde3ea;
        border-radius: 4px;
        padding: 2px 7px;
        font-size: 11px;
        color: #3a5276;
        font-family: 'Courier New', monospace;
    }

    .sq-carrier-code {
        display: inline-block;
        padding: 2px 8px;
        background: #eef2f7;
        border: 1px solid #d5dfe9;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 600;
        font-family: 'Courier New', monospace;
        color: #3a5276;
    }

    .sq-service-code {
        font-size: 11px;
        color: #9aabb8;
        margin-top: 3px;
    }

    /* ── Badge status ── */
    .sq-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 3px 9px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        white-space: nowrap;
    }

    .sq-badge-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        display: inline-block;
        flex-shrink: 0;
    }

    .sq-badge-warning {
        background: #fff8e1;
        color: #856404;
        border: 1px solid #ffd966;
    }

    .sq-badge-warning .sq-badge-dot {
        background: #d4a017;
    }

    .sq-badge-info {
        background: #e8f0fb;
        color: #1a6fc4;
        border: 1px solid #bdd4f0;
    }

    .sq-badge-info .sq-badge-dot {
        background: #1a6fc4;
    }

    .sq-badge-primary {
        background: #e8f0fb;
        color: #1a6fc4;
        border: 1px solid #bdd4f0;
    }

    .sq-badge-primary .sq-badge-dot {
        background: #1a6fc4;
    }

    .sq-badge-success {
        background: #e6f9ee;
        color: #1e7e34;
        border: 1px solid #b8e8c8;
    }

    .sq-badge-success .sq-badge-dot {
        background: #1e7e34;
    }

    .sq-badge-danger {
        background: #fdecea;
        color: #c0392b;
        border: 1px solid #f5c0ba;
    }

    .sq-badge-danger .sq-badge-dot {
        background: #c0392b;
    }

    .sq-badge-secondary {
        background: #f0f3f6;
        color: #5a6a7a;
        border: 1px solid #dde3ea;
    }

    .sq-badge-secondary .sq-badge-dot {
        background: #8a9ab0;
    }

    /* Badge pagamento */
    .sq-pay-paid {
        background: #e6f9ee;
        color: #155724;
        border: 1px solid #b8e8c8;
    }

    .sq-pay-refunded {
        background: #fff8e1;
        color: #856404;
        border: 1px solid #ffd966;
    }

    .sq-pay-pending {
        background: #fdecea;
        color: #721c24;
        border: 1px solid #f5c0ba;
    }

    /* ── Totale ── */
    .sq-total {
        font-weight: 700;
        font-size: 13px;
        color: #1a2535;
    }

    .sq-payment-method {
        font-size: 11px;
        color: #9aabb8;
        margin-top: 2px;
    }

    /* ── Azioni ── */
    .sq-action-wrap {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .sq-btn-create {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 13px;
        background: #1a6fc4;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, transform 0.1s;
        white-space: nowrap;
    }

    .sq-btn-create:hover {
        background: #155da0;
    }

    .sq-btn-create:active {
        transform: scale(0.97);
    }

    .sq-btn-cancel {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 7px 13px;
        background: #fff;
        color: #c0392b;
        border: 1px solid #e8b4af;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s;
        white-space: nowrap;
    }

    .sq-btn-cancel:hover {
        background: #fdecea;
        border-color: #c0392b;
    }

    .sq-btn-help {
        font-size: 11px;
        color: #8a9ab0;
        background: none;
        border: none;
        padding: 0;
        cursor: pointer;
        text-decoration: underline;
        text-underline-offset: 2px;
    }

    .sq-btn-help:hover {
        color: #1a6fc4;
    }

    /* ── Tooltip modale ── */
    .sq-modal-backdrop {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(30, 37, 50, 0.45);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }

    .sq-modal-backdrop.active {
        display: flex;
    }

    .sq-modal {
        background: #fff;
        border-radius: 10px;
        padding: 28px 32px;
        max-width: 460px;
        width: 90%;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.18);
        position: relative;
    }

    .sq-modal-title {
        font-size: 15px;
        font-weight: 700;
        color: #1a2535;
        margin: 0 0 16px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .sq-modal-title i {
        color: #1a6fc4;
        font-size: 18px;
    }

    .sq-modal-steps {
        list-style: none;
        padding: 0;
        margin: 0 0 20px;
        display: flex;
        flex-direction: column;
        gap: 10px;
    }

    .sq-modal-step {
        display: flex;
        align-items: flex-start;
        gap: 12px;
        font-size: 13px;
        color: #2c3e50;
    }

    .sq-step-num {
        min-width: 22px;
        height: 22px;
        background: #1a6fc4;
        color: #fff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 11px;
        font-weight: 700;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .sq-modal-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
    }

    .sq-modal-confirm {
        padding: 8px 18px;
        background: #1a6fc4;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
    }

    .sq-modal-confirm:hover {
        background: #155da0;
    }

    .sq-modal-close-btn {
        padding: 8px 16px;
        background: #fff;
        color: #5a6a7a;
        border: 1px solid #dde3ea;
        border-radius: 5px;
        font-size: 13px;
        cursor: pointer;
    }

    .sq-modal-close-btn:hover {
        background: #f5f7fa;
    }

    /* ── Empty state ── */
    .sq-empty {
        padding: 64px 20px;
        text-align: center;
    }

    .sq-empty-icon {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        background: #f0f4f8;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 18px;
        font-size: 28px;
        color: #b0bbc8;
    }

    .sq-empty-title {
        font-size: 16px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 8px;
    }

    .sq-empty-desc {
        font-size: 13px;
        color: #8a9ab0;
        margin: 0;
    }

    /* ── Paginazione ── */
    .sq-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 4px;
        padding: 18px;
        border-top: 1px solid #e8ecef;
        flex-wrap: wrap;
    }

    .sq-page-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 32px;
        height: 32px;
        border-radius: 5px;
        font-size: 13px;
        font-weight: 600;
        border: 1px solid #dde3ea;
        background: #fff;
        color: #5a6a7a;
        text-decoration: none;
        transition: background 0.12s;
    }

    .sq-page-btn:hover {
        background: #f0f4f8;
        color: #1a2535;
    }

    .sq-page-btn.active {
        background: #1a6fc4;
        border-color: #1a6fc4;
        color: #fff;
    }

    .sq-page-info {
        font-size: 12px;
        color: #9aabb8;
        margin-top: 8px;
        text-align: center;
        width: 100%;
    }
</style>






{* ── Modale conferma Crea Spedizione ── *}
<div class="sq-modal-backdrop" id="sq-shipment-modal">
    <div class="sq-modal" role="dialog" aria-modal="true" aria-labelledby="sq-modal-title">
        <p class="sq-modal-title" id="sq-modal-title">
            <i class="icon-truck"></i>
            {l s='Conferma creazione spedizione' mod='spedisciquishipping'}
        </p>
        <p style="font-size:13px; color:#5a6a7a; margin:0 0 14px;">
            {l s='Cliccando conferma, il sistema eseguirà automaticamente:' mod='spedisciquishipping'}
        </p>
        <ul class="sq-modal-steps">
            <li class="sq-modal-step">
                <span class="sq-step-num">1</span>
                <span>{l s='Verifica dati ordine e indirizzo di consegna' mod='spedisciquishipping'}</span>
            </li>
            <li class="sq-modal-step">
                <span class="sq-step-num">2</span>
                <span>{l s='Recupero peso pacco, corriere e codice servizio' mod='spedisciquishipping'}</span>
            </li>
            <li class="sq-modal-step">
                <span class="sq-step-num">3</span>
                <span>{l s='Calcolo tariffa di spedizione applicabile' mod='spedisciquishipping'}</span>
            </li>
            <li class="sq-modal-step">
                <span class="sq-step-num">4</span>
                <span>{l s='Invio richiesta all\'API del corriere selezionato' mod='spedisciquishipping'}</span>
            </li>
            <li class="sq-modal-step">
                <span class="sq-step-num">5</span>
                <span>{l s='Generazione numero di tracking e salvataggio spedizione' mod='spedisciquishipping'}</span>
            </li>
        </ul>
        <div class="sq-modal-actions">
            <button type="button" class="sq-modal-close-btn" onclick="sqCloseModal()">
                {l s='Annulla' mod='spedisciquishipping'}
            </button>
            <button type="button" class="sq-modal-confirm" id="sq-modal-confirm-btn">
                <i class="icon-truck"></i>
                {l s='Conferma e crea' mod='spedisciquishipping'}
            </button>
        </div>
    </div>
</div>

<div class="sq-orders">

    {* ── Header ── *}
    <div class="sq-orders-header">
        <div class="sq-orders-header-left">
            <i class="icon-truck" style="font-size:20px; color:#1a6fc4;"></i>
            <div>
                <p class="sq-orders-title">{l s='Spedizioni' mod='spedisciquishipping'}</p>
                <p class="sq-orders-subtitle">
                    {l s='Gestione e monitoraggio delle spedizioni attive' mod='spedisciquishipping'}</p>
            </div>
        </div>
        {if isset($shipments)}
            <span class="sq-count-pill">{$shipments|count}</span>
        {/if}
    </div>

    {* ── Flash messages ── *}
    {if isset($smarty.get.conf) && $smarty.get.conf}
        <div class="sq-alert sq-alert-success">
            <i class="icon-check"></i>
            <span>{$smarty.get.conf|escape:'html':'UTF-8'}</span>
        </div>
    {/if}
    {if isset($smarty.get.error) && $smarty.get.error}
        <div class="sq-alert sq-alert-danger">
            <i class="icon-warning-sign"></i>
            <span>{$smarty.get.error|escape:'html':'UTF-8'}</span>
        </div>
    {/if}

    {* ── Filtro status ── *}
    <div class="sq-filter-bar">
        <span class="sq-filter-label">{l s='Filtra per stato:' mod='spedisciquishipping'}</span>
        <form method="GET" style="display:flex; align-items:center; gap:8px; margin:0;">
            {foreach $smarty.get as $k => $v}
                {if $k !== 'status_filter'}
                    <input type="hidden" name="{$k|escape:'html'}" value="{$v|escape:'html'}">
                {/if}
            {/foreach}
            <select name="status_filter">
                <option value="">{l s='— Tutti gli stati —' mod='spedisciquishipping'}</option>
                <option value="pending" {if $statusFilter === 'pending'}selected{/if}>
                    {l s='In attesa' mod='spedisciquishipping'}</option>
                <option value="label_created" {if $statusFilter === 'label_created'}selected{/if}>
                    {l s='Label creata' mod='spedisciquishipping'}</option>
                <option value="picked_up" {if $statusFilter === 'picked_up'}selected{/if}>
                    {l s='Ritirato' mod='spedisciquishipping'}</option>
                <option value="in_transit" {if $statusFilter === 'in_transit'}selected{/if}>
                    {l s='In transito' mod='spedisciquishipping'}</option>
                <option value="out_for_delivery" {if $statusFilter === 'out_for_delivery'}selected{/if}>
                    {l s='In consegna' mod='spedisciquishipping'}</option>
                <option value="delivered" {if $statusFilter === 'delivered'}selected{/if}>
                    {l s='Consegnato' mod='spedisciquishipping'}</option>
                <option value="failed" {if $statusFilter === 'failed'}selected{/if}>
                    {l s='Fallito' mod='spedisciquishipping'}</option>
                <option value="cancelled" {if $statusFilter === 'cancelled'}selected{/if}>
                    {l s='Annullato' mod='spedisciquishipping'}</option>
                <option value="returned" {if $statusFilter === 'returned'}selected{/if}>
                    {l s='Reso' mod='spedisciquishipping'}</option>
            </select>
            <button type="submit" class="sq-filter-btn">
                <i class="icon-filter"></i>
                {l s='Filtra' mod='spedisciquishipping'}
            </button>
        </form>
    </div>

    {* ── Tabella / Empty state ── *}
    {if empty($shipments)}
        <div class="sq-empty">
            <div class="sq-empty-icon"><i class="icon-truck"></i></div>
            <p class="sq-empty-title">{l s='Nessuna spedizione trovata' mod='spedisciquishipping'}</p>
            <p class="sq-empty-desc">
                {if $statusFilter}
                    {l s='Nessuna spedizione corrisponde al filtro selezionato.' mod='spedisciquishipping'}
                {else}
                    {l s='Non ci sono ancora spedizioni registrate.' mod='spedisciquishipping'}
                {/if}
            </p>
        </div>
    {else}
        <div class="sq-table-wrap">
            <table class="sq-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{l s='Ordine' mod='spedisciquishipping'}</th>
                        <th>{l s='Cliente' mod='spedisciquishipping'}</th>
                        <th>{l s='Corriere' mod='spedisciquishipping'}</th>
                        <th>{l s='Tracking' mod='spedisciquishipping'}</th>
                        <th>{l s='Stato spedizione' mod='spedisciquishipping'}</th>
                        <th>{l s='Pagamento' mod='spedisciquishipping'}</th>
                        <th>{l s='Totale' mod='spedisciquishipping'}</th>
                        <th>{l s='Peso' mod='spedisciquishipping'}</th>
                        <th>{l s='Data' mod='spedisciquishipping'}</th>
                        <th>{l s='Azioni' mod='spedisciquishipping'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $shipments as $shipment}
                        <tr>

                            {* ID shipment *}
                            <td>
                                <strong style="color:#1a2535;">#{$shipment.id_shipment}</strong>
                            </td>

                            {* Ordine *}
                            <td>
                                <a href="{$orderDetailLink}&id_order={$shipment.id_order}" target="_blank" class="sq-order-id">
                                    #{$shipment.id_order}
                                    <i class="icon-external-link" style="font-size:10px; opacity:0.6;"></i>
                                </a>
                            </td>

                            {* Cliente *}
                            <td>
                                <div class="sq-customer-name">{$shipment.customer_name|escape:'html':'UTF-8'}</div>
                                <div class="sq-customer-meta">
                                    {$shipment.delivery_city|escape:'html':'UTF-8'}
                                    {if $shipment.delivery_country}
                                        &nbsp;({$shipment.delivery_country|escape:'html':'UTF-8'})
                                    {/if}
                                </div>
                            </td>

                            {* Corriere *}
                            <td>
                                <span class="sq-carrier-code">{$shipment.carrier_code|escape:'html':'UTF-8'}</span>
                                <div class="sq-service-code">{$shipment.service_code|escape:'html':'UTF-8'}</div>
                            </td>

                            {* Tracking *}
                            <td class="sq-tracking">
                                {if $shipment.tracking_number !== '—'}
                                    <code>{$shipment.tracking_number|escape:'html':'UTF-8'}</code>
                                {else}
                                    <span style="color:#c8d0db;">—</span>
                                {/if}
                            </td>

                            {* Stato spedizione *}
                            <td>
                                <span class="sq-badge sq-badge-{$shipment.status_class}">
                                    <span class="sq-badge-dot"></span>
                                    {$shipment.status_label|escape:'html':'UTF-8'}
                                </span>
                            </td>

                            {* Pagamento *}
                            <td>
                                {if $shipment.payment_status === 'paid'}
                                    <span class="sq-badge sq-pay-paid">
                                        <i class="icon-check" style="font-size:10px;"></i>
                                        {l s='Pagato' mod='spedisciquishipping'}
                                    </span>
                                {elseif $shipment.payment_status === 'refunded'}
                                    <span class="sq-badge sq-pay-refunded">
                                        <i class="icon-refresh" style="font-size:10px;"></i>
                                        {l s='Rimborsato' mod='spedisciquishipping'}
                                    </span>
                                {else}
                                    <span class="sq-badge sq-pay-pending">
                                        <i class="icon-time" style="font-size:10px;"></i>
                                        {l s='In attesa' mod='spedisciquishipping'}
                                    </span>
                                {/if}
                            </td>

                            {* Totale *}
                            <td>
                                <div class="sq-total">{$shipment.total_paid} {$shipment.currency}</div>
                                <div class="sq-payment-method">{$shipment.payment_method|escape:'html':'UTF-8'}</div>
                            </td>

                            {* Peso *}
                            <td style="white-space:nowrap; color:#5a6a7a;">
                                {$shipment.weight} kg
                            </td>

                            {* Data *}
                            <td style="white-space:nowrap; font-size:12px; color:#9aabb8;">
                                {$shipment.date_add|escape:'html':'UTF-8'}
                            </td>

                            {* Azioni *}
                            <td>
                                {if $shipment.status === 'pending'}
                                    <div class="sq-action-wrap">
                                        <a href="{$action}&action=shipmentReview&id_shipment={$shipment.id_shipment}"
                                            class="sq-btn-create">
                                            <i class="icon-search"></i>
                                            {l s='Crea spedizione' mod='spedisciquishipping'}
                                        </a>
                                    </div>
                                {elseif $shipment.status === 'label_created'}
                                    <form method="POST" action="{$action}" style="margin:0;">
                                        <input type="hidden" name="id_shipment" value="{$shipment.id_shipment}">
                                        <button type="submit" name="cancelShipment" class="sq-btn-cancel"
                                            onclick="return confirm('{l s='Annullare la spedizione?' mod='spedisciquishipping' js=1}');">
                                            <i class="icon-remove"></i>
                                            {l s='Annulla' mod='spedisciquishipping'}
                                        </button>
                                    </form>

                                {else}
                                    <span style="color:#c8d0db; font-size:13px;">—</span>
                                {/if}
                            </td>

                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>

        {* ── Paginazione ── *}
        {if $totalShipments > $limit}
            {assign var="totalPages" value=ceil($totalShipments / $limit)}
            <div class="sq-pagination">
                {for $p=1 to $totalPages}
                    <a class="sq-page-btn {if $p == $currentPage}active{/if}" href="?{foreach $smarty.get as $k => $v}
                            {if $k !== 'page'}{$k|escape:'url'}={$v|escape:'url'}&
                            {/if}
                        {/foreach}page={$p}">
                        {$p}
                    </a>
                {/for}
                <div class="sq-page-info">
                    {$totalShipments} {l s='spedizioni totali' mod='spedisciquishipping'} —
                    {l s='pagina' mod='spedisciquishipping'} {$currentPage} {l s='di' mod='spedisciquishipping'} {$totalPages}
                </div>
            </div>
        {/if}

    {/if}

</div>

<script>
    var sqPendingFormId = null;

    function sqOpenModal(shipmentId) {
        sqPendingFormId = shipmentId;
        document.getElementById('sq-shipment-modal').classList.add('active');
    }

    function sqCloseModal() {
        document.getElementById('sq-shipment-modal').classList.remove('active');
        sqPendingFormId = null;
    }

    document.getElementById('sq-modal-confirm-btn').addEventListener('click', function() {
        if (sqPendingFormId !== null) {
            var form = document.getElementById('sq-form-' + sqPendingFormId);
            if (form) {
                var btn = document.createElement('input');
                btn.type = 'hidden';
                btn.name = 'createShipment';
                btn.value = '1';
                form.appendChild(btn);
                form.submit();
            }
        }
        sqCloseModal();
    });

    document.getElementById('sq-shipment-modal').addEventListener('click', function(e) {
        if (e.target === this) sqCloseModal();
    });
</script>