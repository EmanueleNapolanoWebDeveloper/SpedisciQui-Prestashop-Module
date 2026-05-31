{* views/templates/admin/carrier_list.tpl *}

<style>
    .sq-panel {
        background: var(--color-background-primary);
        border: 0.5px solid var(--color-border-tertiary);
        border-radius: var(--border-radius-lg);
        overflow: hidden;
        margin: 0 auto;
    }

    .sq-panel-header {
        padding: 20px 24px 16px;
        border-bottom: 0.5px solid var(--color-border-tertiary);
        background: var(--color-background-secondary);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sq-panel-header i {
        font-size: 20px;
        color: var(--color-text-secondary);
    }

    .sq-panel-header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 500;
    }

    .sq-panel-header p {
        margin: 2px 0 0;
        font-size: 13px;
        color: var(--color-text-secondary);
    }

    .sq-alert {
        margin: 24px;
        padding: 14px 16px;
        border: 0.5px solid var(--color-border-tertiary);
        border-radius: var(--border-radius-md);
        background: var(--color-background-secondary);
        font-size: 13px;
        color: var(--color-text-secondary);
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sq-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }

    .sq-table thead tr {
        border-bottom: 0.5px solid var(--color-border-tertiary);
    }

    .sq-table thead th {
        padding: 10px 16px;
        text-align: left;
        font-size: 11px;
        font-weight: 500;
        color: var(--color-text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        white-space: nowrap;
        background: var(--color-background-secondary);
    }

    .sq-table thead th:first-child {
        width: 48px;
        text-align: center;
    }

    .sq-table tbody tr {
        border-bottom: 0.5px solid var(--color-border-tertiary);
        transition: background 0.12s;
    }

    .sq-table tbody tr:last-child {
        border-bottom: none;
    }

    .sq-table tbody tr:hover {
        background: var(--color-background-secondary);
    }

    .sq-table td {
        padding: 12px 16px;
        vertical-align: middle;
        color: var(--color-text-primary);
    }

    .sq-table td:first-child {
        text-align: center;
    }

    .sq-table td input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .sq-carrier-logo {
        max-height: 32px;
        max-width: 72px;
        object-fit: contain;
        display: block;
    }

    .sq-carrier-name {
        font-weight: 500;
        font-size: 13px;
        line-height: 1.3;
        color: var(--color-text-primary);
    }

    .sq-carrier-code {
        font-size: 11px;
        color: var(--color-text-tertiary);
        margin-top: 2px;
    }

    .sq-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
        white-space: nowrap;
    }

    .sq-badge-national {
        background: #eaf3de;
        color: #3b6d11;
    }

    .sq-badge-intl {
        background: #e6f1fb;
        color: #185fa5;
    }

    .sq-badge-home {
        background: #e6f1fb;
        color: #185fa5;
    }

    .sq-badge-pickup {
        background: #faeeda;
        color: #854f0b;
    }

    .sq-badge-default {
        background: var(--color-background-secondary);
        color: var(--color-text-secondary);
    }

    .sq-delivery-days {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
        color: var(--color-text-secondary);
    }

    .sq-delivery-days i {
        font-size: 14px;
        color: var(--color-text-tertiary);
    }

    .sq-footer {
        padding: 16px 24px;
        border-top: 0.5px solid var(--color-border-tertiary);
        display: flex;
        justify-content: flex-end;
    }

    .sq-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 22px;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #222;
        cursor: pointer;
    }

    .sq-btn:hover {
        background: #f5f5f5;
    }
</style>

<div class="sq-panel">

    <div class="sq-panel-header">
        <i class="icon-truck"></i>
        <div>
            <h2>{l s='Corrieri disponibili' mod='spedisciquishipping'}</h2>
            <p>{l s='Seleziona i corrieri da attivare per il tuo negozio' mod='spedisciquishipping'}</p>
        </div>
    </div>

    {if !$carriers}
        <div class="sq-alert">
            <i class="icon-warning-sign"></i>
            {l s='Nessun corriere disponibile. Verifica la connessione API.' mod='spedisciquishipping'}
        </div>
    {else}
        <form method="post" action="{$action|escape:'htmlall':'UTF-8'}">

            <table class="sq-table">
                <thead>
                    <tr>
                        <th></th>
                        <th>{l s='Logo' mod='spedisciquishipping'}</th>
                        <th>{l s='Corriere' mod='spedisciquishipping'}</th>
                        <th>{l s='Servizio' mod='spedisciquishipping'}</th>
                        <th>{l s='Tipo' mod='spedisciquishipping'}</th>
                        <th>{l s='Consegna' mod='spedisciquishipping'}</th>
                        <th>{l s='Destinazione' mod='spedisciquishipping'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$carriers item=carrier}
                        <tr>
                            <td>
                                <input type="checkbox"
                                    name="selected_carriers[]"
                                    value="{$carrier.code|escape:'htmlall':'UTF-8'}"
                                    data-name="{$carrier.name|escape:'htmlall':'UTF-8'}">
                            </td>
                            <td>
                                {if $carrier.logo_url}
                                    <img class="sq-carrier-logo"
                                        src="{$carrier.logo_url|escape:'htmlall':'UTF-8'}"
                                        alt="{$carrier.name|escape:'htmlall':'UTF-8'}">
                                {else}
                                    <span class="sq-badge sq-badge-default">
                                        {$carrier.name|escape:'htmlall':'UTF-8'}
                                    </span>
                                {/if}
                            </td>
                            <td>
                                <div class="sq-carrier-name">
                                    {$carrier.name|escape:'htmlall':'UTF-8'}
                                </div>
                                <div class="sq-carrier-code">
                                    {$carrier.code|escape:'htmlall':'UTF-8'}
                                </div>
                            </td>
                            <td>{$carrier.service_title|escape:'htmlall':'UTF-8'}</td>
                            <td>
                                {if $carrier.type === 'national'}
                                    <span class="sq-badge sq-badge-national">
                                        <i class="icon-map-marker"></i>
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
                                <span class="sq-delivery-days">
                                    <i class="icon-time"></i>
                                    {$carrier.delivery_days|escape:'htmlall':'UTF-8'}
                                    {l s='giorni' mod='spedisciquishipping'}
                                </span>
                            </td>
                            <td>
                                {if $carrier.destination === 'home'}
                                    <span class="sq-badge sq-badge-home">
                                        <i class="icon-home"></i>
                                        {l s='Domicilio' mod='spedisciquishipping'}
                                    </span>
                                {elseif $carrier.destination === 'pickup_point'}
                                    <span class="sq-badge sq-badge-pickup">
                                        <i class="icon-map-marker"></i>
                                        {l s='Punto ritiro' mod='spedisciquishipping'}
                                    </span>
                                {else}
                                    <span class="sq-badge sq-badge-default">
                                        {$carrier.destination|escape:'htmlall':'UTF-8'}
                                    </span>
                                {/if}
                            </td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>

            <div class="sq-footer">
                <button type="submit" name="submitSpedisciQuiCarriers" class="sq-btn">
                    <i class="icon-arrow-right"></i>
                    {l s='Salva corrieri selezionati' mod='spedisciquishipping'}
                </button>
            </div>

        </form>
    {/if}

</div>