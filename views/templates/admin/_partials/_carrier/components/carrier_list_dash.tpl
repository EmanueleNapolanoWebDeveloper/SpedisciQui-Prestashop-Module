<style>
    .sq-avail-panel {
        background: #fff;
        border: 1px solid #dde3ea;
        border-radius: 4px;
        margin-top: 20px;
        overflow: hidden;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
        font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .sq-avail-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 18px 24px;
        background: #fff;
        border-bottom: 1px solid #e8ecef;
    }

    .sq-avail-header-left {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sq-avail-icon {
        width: 38px;
        height: 38px;
        border-radius: 8px;
        background: #e8f0fb;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #1a6fc4;
        font-size: 17px;
        flex-shrink: 0;
    }

    .sq-avail-title {
        font-size: 15px;
        font-weight: 700;
        color: #1a2535;
        margin: 0 0 2px 0;
        line-height: 1.2;
    }

    .sq-avail-subtitle {
        font-size: 12px;
        color: #8a9ab0;
        margin: 0;
        line-height: 1.3;
    }

    .sq-avail-header-right {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .sq-sync-indicator {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 5px 11px;
        background: #e6f9ee;
        border: 1px solid #b8e8c8;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        color: #1e7e34;
    }

    .sq-sync-dot {
        width: 6px;
        height: 6px;
        border-radius: 50%;
        background: #1e7e34;
        animation: sq-pulse 2s infinite;
    }

    @keyframes sq-pulse {

        0%,
        100% {
            opacity: 1;
        }

        50% {
            opacity: 0.4;
        }
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

    /* Table */
    .sq-avail-table-wrap {
        overflow-x: auto;
    }

    .sq-avail-table {
        width: 100%;
        border-collapse: collapse;
        min-width: 780px;
    }

    .sq-avail-table thead tr {
        background: #f5f7fa;
        border-bottom: 2px solid #dde3ea;
    }

    .sq-avail-table thead th {
        padding: 11px 16px;
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        color: #7a8a9a;
        white-space: nowrap;
        vertical-align: middle;
    }

    .sq-avail-table tbody tr {
        border-bottom: 1px solid #f0f3f6;
        transition: background 0.14s;
    }

    .sq-avail-table tbody tr:last-child {
        border-bottom: none;
    }

    .sq-avail-table tbody tr:hover {
        background: #f7f9fc;
    }

    .sq-avail-table td {
        padding: 14px 16px;
        vertical-align: middle;
        font-size: 13px;
        color: #2c3e50;
    }

    /* Logo */
    .sq-logo-box {
        width: 50px;
        height: 50px;
        border-radius: 6px;
        border: 1px solid #e4e9f0;
        background: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.07);
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        flex-shrink: 0;
    }

    .sq-logo-box img {
        max-width: 44px;
        max-height: 44px;
        object-fit: contain;
    }

    .sq-logo-placeholder {
        width: 50px;
        height: 50px;
        border-radius: 6px;
        border: 1px dashed #c8d0db;
        background: #f5f7fa;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #b0bbc8;
        font-size: 20px;
        flex-shrink: 0;
    }

    /* Carrier identity */
    .sq-carrier-name {
        font-size: 14px;
        font-weight: 700;
        color: #1a2535;
        margin: 0 0 3px 0;
        line-height: 1.2;
    }

    .sq-carrier-code {
        display: inline-block;
        padding: 2px 7px;
        border-radius: 3px;
        font-size: 11px;
        font-weight: 600;
        font-family: 'Courier New', monospace;
        background: #eef2f7;
        color: #3a5276;
        border: 1px solid #d5dfe9;
        white-space: nowrap;
    }

    /* Service */
    .sq-service-title {
        font-size: 13px;
        color: #2c3e50;
        font-weight: 500;
        margin: 0 0 4px 0;
    }

    .sq-delivery-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 3px 9px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        background: #f0f4f8;
        color: #4a6280;
        border: 1px solid #dde3ea;
        white-space: nowrap;
    }

    /* Badges */
    .sq-badge {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 0.1px;
        white-space: nowrap;
    }

    .sq-badge-national {
        background: #e6f9ee;
        color: #1e7e34;
        border: 1px solid #b8e8c8;
    }

    .sq-badge-intl {
        background: #e8f0fb;
        color: #1a6fc4;
        border: 1px solid #bdd4f0;
    }

    .sq-badge-home {
        background: #e8f0fb;
        color: #1a6fc4;
        border: 1px solid #bdd4f0;
    }

    .sq-badge-pickup {
        background: #fff4e5;
        color: #d46b08;
        border: 1px solid #fcd4a0;
    }

    .sq-badge-installed {
        background: #e6f9ee;
        color: #1e7e34;
        border: 1px solid #b8e8c8;
    }

    .sq-badge i {
        font-size: 11px;
    }

    /* Add button */
    .sq-btn-add {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 7px 14px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
        background: #1a6fc4;
        color: #fff;
        border: none;
        cursor: pointer;
        box-shadow: 0 1px 3px rgba(26, 111, 196, 0.18);
        transition: background 0.15s, box-shadow 0.15s, transform 0.1s;
        white-space: nowrap;
        line-height: 1;
    }

    .sq-btn-add:hover {
        background: #155da0;
        box-shadow: 0 2px 6px rgba(26, 111, 196, 0.28);
        color: #fff;
    }

    .sq-btn-add:active {
        transform: scale(0.97);
    }

    .sq-btn-add i {
        font-size: 11px;
    }

    /* Empty state */
    .sq-avail-empty {
        padding: 64px 20px;
        text-align: center;
    }

    .sq-avail-empty-icon {
        width: 76px;
        height: 76px;
        border-radius: 50%;
        background: #f0f4f8;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 22px auto;
        font-size: 30px;
        color: #b0bbc8;
    }

    .sq-avail-empty-title {
        font-size: 17px;
        font-weight: 700;
        color: #2c3e50;
        margin: 0 0 10px 0;
    }

    .sq-avail-empty-desc {
        font-size: 13px;
        color: #8a9ab0;
        margin: 0 auto;
        max-width: 360px;
        line-height: 1.6;
    }

    .sq-logo-col {
        width: 66px;
    }

    .sq-action-col {
        width: 130px;
    }
</style>

<div class="sq-avail-panel">

    <div class="sq-avail-header">
        <div class="sq-avail-header-left">
            <div class="sq-avail-icon">
                <i class="icon-cloud"></i>
            </div>
            <div>
                <p class="sq-avail-title">{l s='Corrieri disponibili sulla piattaforma' mod='spedisciquishipping'}</p>
                <p class="sq-avail-subtitle">
                    {l s='Sincronizzati in tempo reale tramite API SpedisciQui' mod='spedisciquishipping'}</p>
            </div>
        </div>
        {if $carriers}
            <div class="sq-avail-header-right">
                <span class="sq-sync-indicator">
                    <span class="sq-sync-dot"></span>
                    {l s='API connessa' mod='spedisciquishipping'}
                </span>
                <span class="sq-count-pill">{$carriers|count}</span>
            </div>
        {/if}
    </div>

    {if !$carriers}

        <div class="sq-avail-empty">
            <div class="sq-avail-empty-icon">
                <i class="icon-cloud"></i>
            </div>
            <p class="sq-avail-empty-title">{l s='Nessun corriere disponibile' mod='spedisciquishipping'}</p>
            <p class="sq-avail-empty-desc">
                {l s='Impossibile recuperare i corrieri dalla piattaforma SpedisciQui. Verifica la connessione API e le credenziali configurate.' mod='spedisciquishipping'}
            </p>
        </div>

    {else}

        <div class="sq-avail-table-wrap">
            <table class="sq-avail-table">
                <thead>
                    <tr>
                        <th class="sq-logo-col"></th>
                        <th>{l s='Corriere' mod='spedisciquishipping'}</th>
                        <th>{l s='Servizio' mod='spedisciquishipping'}</th>
                        <th>{l s='Consegna' mod='spedisciquishipping'}</th>
                        <th>{l s='Tipo' mod='spedisciquishipping'}</th>
                        <th>{l s='Destinazione' mod='spedisciquishipping'}</th>
                        <th class="sq-action-col">{l s='Stato' mod='spedisciquishipping'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$carriers item=carrier}
                        <tr>

                            <td class="sq-logo-col">
                                {if $carrier.logo_url}
                                    <div class="sq-logo-box">
                                        <img src="{$carrier.logo_url|escape:'htmlall':'UTF-8'}"
                                            alt="{$carrier.name|escape:'htmlall':'UTF-8'}">
                                    </div>
                                {else}
                                    <div class="sq-logo-placeholder">
                                        <i class="icon-truck"></i>
                                    </div>
                                {/if}
                            </td>

                            <td>
                                <p class="sq-carrier-name">{$carrier.name|escape:'htmlall':'UTF-8'}</p>
                                <span class="sq-carrier-code">{$carrier.code|escape:'htmlall':'UTF-8'}</span>
                            </td>

                            <td>
                                <p class="sq-service-title">{$carrier.service_title|escape:'htmlall':'UTF-8'}</p>
                            </td>

                            <td>
                                <span class="sq-delivery-badge">
                                    <i class="icon-time"></i>
                                    {$carrier.delivery_days|escape:'htmlall':'UTF-8'} {l s='gg' mod='spedisciquishipping'}
                                </span>
                            </td>

                            <td>
                                {if $carrier.type === 'national'}
                                    <span class="sq-badge sq-badge-national">
                                        <i class="icon-flag"></i>
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
                                {if $carrier.destination === 'home'}
                                    <span class="sq-badge sq-badge-home">
                                        <i class="icon-home"></i>
                                        {l s='Domicilio' mod='spedisciquishipping'}
                                    </span>
                                {else}
                                    <span class="sq-badge sq-badge-pickup">
                                        <i class="icon-map-marker"></i>
                                        {l s='Punto ritiro' mod='spedisciquishipping'}
                                    </span>
                                {/if}
                            </td>

                            <td>
                                {if in_array($carrier.code, $savedCodes)}
                                    <span class="sq-badge sq-badge-installed">
                                        <i class="icon-check"></i>
                                        {l s='Installato' mod='spedisciquishipping'}
                                    </span>
                                {else}
                                    <form method="POST" action="{$action|escape:'htmlall':'UTF-8'}">
                                        <input type="hidden" name="selected_carriers[]"
                                            value="{$carrier.code|escape:'htmlall':'UTF-8'}">
                                        <button type="submit" name="submitSpedisciQuiCarriers" class="sq-btn-add">
                                            <i class="icon-plus"></i>
                                            {l s='Aggiungi' mod='spedisciquishipping'}
                                        </button>
                                    </form>
                                {/if}
                            </td>

                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>

    {/if}

</div>