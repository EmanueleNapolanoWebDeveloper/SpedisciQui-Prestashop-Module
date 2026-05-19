{* ============================================================
   SpedisciQui — Dashboard
   views/templates/admin/dashboard.tpl
   ============================================================ *}

{if !defined('_PS_VERSION_')}
{/if}

<div class="panel">

    {* ── HEADER PANEL ─────────────────────────────────────────── *}
    <div class="panel-heading">
        <i class="icon-truck"></i>
        {l s='SpedisciQui — Dashboard' mod='spedisciquishipping'}
    </div>

    <div class="panel-body">

        {* ── SEZIONE UTENTE ───────────────────────────────────────── *}
        <div class="row" style="margin-bottom:20px;">
            <div class="col-lg-12">
                <div class="alert alert-info" style="margin-bottom:0;">
                    <i class="icon-user"></i>
                    {if $user && isset($user.user.name)}
                        {l s='Connesso come' mod='spedisciquishipping'}
                        <strong>{$user.user.name|escape:'htmlall':'UTF-8'}</strong>
                        &mdash; {$user.user.email|escape:'htmlall':'UTF-8'}
                    {else}
                        {l s='Impossibile recuperare i dati utente dalla piattaforma.' mod='spedisciquishipping'}
                    {/if}
                </div>
            </div>
        </div>

        {* ── SEZIONE CORRIERI ─────────────────────────────────────── *}
        <div class="panel" style="margin-top:20px;">

            <div class="panel-heading">
                <i class="icon-truck"></i>
                {l s='Corrieri disponibili' mod='spedisciquishipping'}
                {if $carriers}
                    <span class="badge" style="margin-left:8px;">{$carriers|count}</span>
                {/if}
            </div>

            <div class="panel-body">

                {if !$carriers}

                    <p class="text-muted text-center" style="padding:20px 0;">
                        <i class="icon-warning-sign" style="font-size:20px;"></i><br>
                        {l s='Nessun corriere disponibile. Verifica la connessione API.' mod='spedisciquishipping'}
                    </p>

                {else}

                    <div style="margin-bottom:16px; font-size:12px; color:#888;
                                display:flex; flex-wrap:wrap; gap:10px; align-items:center;">
                        <span><strong>{l s='Legenda:' mod='spedisciquishipping'}</strong></span>
                        <span class="label"
                            style="background:#337ab7; color:#fff;">{l s='Nazionale' mod='spedisciquishipping'}</span>
                        <span class="label"
                            style="background:#8e44ad; color:#fff;">{l s='Internazionale' mod='spedisciquishipping'}</span>
                        <span class="label"
                            style="background:#27ae60; color:#fff;">{l s='Ritiro a domicilio' mod='spedisciquishipping'}</span>
                        <span class="label"
                            style="background:#e67e22; color:#fff;">{l s='Consegna a domicilio' mod='spedisciquishipping'}</span>
                        <span class="label"
                            style="background:#7f8c8d; color:#fff;">{l s='Ritiro a deposito' mod='spedisciquishipping'}</span>
                        <span class="label"
                            style="background:#2980b9; color:#fff;">{l s='Punto di ritiro' mod='spedisciquishipping'}</span>
                    </div>

                    <table class="table">
                        <thead>
                            <tr>
                                <th>{l s='Corriere' mod='spedisciquishipping'}</th>
                                <th>{l s='Servizio' mod='spedisciquishipping'}</th>
                                <th>{l s='Consegna' mod='spedisciquishipping'}</th>
                                <th>{l s='Tipo' mod='spedisciquishipping'}</th>
                                <th>{l s='Origine' mod='spedisciquishipping'}</th>
                                <th>{l s='Destinazione' mod='spedisciquishipping'}</th>
                                <th width="140">{l s='Azioni' mod='spedisciquishipping'}</th>
                            </tr>
                        </thead>

                        <tbody>
                            {foreach from=$carriers item=carrier}

                                {assign var='typeLabel'  value=($carrier.type == 'international') ? 'Internazionale' : 'Nazionale'}
                                {assign var='originText' value=($carrier.origin == 'pickup')      ? 'Ritiro domicilio' : 'Deposito'}
                                {assign var='destText'   value=($carrier.destination == 'home')   ? 'Consegna domicilio' : 'Punto ritiro'}

                                <tr>

                                    <td style="vertical-align:middle;">
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            {if $carrier.logo_url}
                                                <img src="{$carrier.logo_url|escape:'htmlall':'UTF-8'}"
                                                    alt="{$carrier.name|escape:'htmlall':'UTF-8'}"
                                                    style="width:40px; height:40px; object-fit:contain;">
                                            {/if}
                                            <strong>{$carrier.name|escape:'htmlall':'UTF-8'}</strong>
                                        </div>
                                    </td>

                                    <td style="vertical-align:middle;">
                                        {$carrier.service_title|escape:'htmlall':'UTF-8'}
                                    </td>

                                    <td style="vertical-align:middle;">
                                        <span class="badge">
                                            {$carrier.delivery_days|escape:'htmlall':'UTF-8'} gg
                                        </span>
                                    </td>

                                    <td style="vertical-align:middle;">
                                        {$typeLabel|escape:'htmlall':'UTF-8'}
                                    </td>

                                    <td style="vertical-align:middle;">
                                        {$originText|escape:'htmlall':'UTF-8'}
                                    </td>

                                    <td style="vertical-align:middle;">
                                        {$destText|escape:'htmlall':'UTF-8'}
                                    </td>

                                    <td style="vertical-align:middle;">

                                        {if $carrier.isInstalled}
                                            <span class="badge badge-success">✅ Installato</span>
                                        {else}
                                            <form method="POST" action="{$action}">
                                                <input type="hidden" name="service_id" value="{$carrier.code}">
                                                <input type="hidden" name="service_name" value="{$carrier.name}">
                                                <input type="hidden" name="service_code" value="{$carrier.code}">
                                                <input type="hidden" name="token" value="{$token}">
                                                <button type="submit" name="submitInstallCarrier" class="btn btn-primary btn-sm">
                                                    Aggiungi
                                                </button>
                                            </form>
                                        {/if}

                                    </td>

                                </tr>

                            {/foreach}
                        </tbody>
                    </table>

                {/if}

            </div>
        </div>

        <div class="panel" style="margin-top:20px;">

            <div class="panel-heading">
                <i class="icon-check"></i>
                {l s='Corrieri attivi sul tuo negozio' mod='spedisciquishipping'}
                {if $savedCarriers}
                    <span class="badge" style="margin-left:8px;">{$savedCarriers|count}</span>
                {/if}
            </div>

            <div class="panel-body">

                {if !$savedCarriers}

                    <p class="text-muted text-center" style="padding:20px 0;">
                        <i class="icon-warning-sign" style="font-size:20px;"></i><br>
                        {l s='Nessun corriere ancora aggiunto. Usa la tabella sopra per aggiungerne uno.' mod='spedisciquishipping'}
                    </p>

                {else}

                    <table class="table">
                        <thead>
                            <tr>
                                <th>{l s='ID PrestaShop' mod='spedisciquishipping'}</th>
                                <th>{l s='Nome corriere' mod='spedisciquishipping'}</th>
                                <th>{l s='Codice API' mod='spedisciquishipping'}</th>
                                <th>{l s='Stato' mod='spedisciquishipping'}</th>
                                <th>{l s='Azioni' mod='spedisciquishipping'}</th>
                            </tr>
                        </thead>
                        <tbody>
                            {foreach from=$savedCarriers item=sc}
                                <tr>
                                    <td style="vertical-align:middle;">
                                        <code>#{$sc.id_carrier|intval}</code>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <strong>{$sc.name|escape:'htmlall':'UTF-8'}</strong>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <span class="label label-info">
                                            {$sc.carrier_code|escape:'htmlall':'UTF-8'}
                                        </span>
                                    </td>
                                    <td style="vertical-align:middle;">
                                        {if $sc.active}
                                            <span class="label label-success">
                                                {l s='Attivo' mod='spedisciquishipping'}
                                            </span>
                                        {else}
                                            <span class="label label-danger">
                                                {l s='Disattivo' mod='spedisciquishipping'}
                                            </span>
                                        {/if}
                                    </td>
                                    <td style="vertical-align:middle;">
                                        <form method="POST" action="{$action|escape:'htmlall':'UTF-8'}"
                                            style="display:inline-block;">
                                            <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">
                                            <input type="hidden" name="carrier_code"
                                                value="{$sc.carrier_code|escape:'htmlall':'UTF-8'}">
                                            <button type="submit" name="submitRemoveCarrier" class="btn btn-danger btn-sm"
                                                onclick="return confirm('{l s='Rimuovere il corriere?' mod='spedisciquishipping' js=1}');">
                                                <i class="icon-trash"></i>
                                                {l s='Rimuovi' mod='spedisciquishipping'}
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            {/foreach}
                        </tbody>
                    </table>

                {/if}

            </div>
        </div>


        <div class="panel-footer">
            <div class="row">
                <div class="col-lg-12" style="display:flex; gap:10px; align-items:center;">

                    <form method="POST" action="{$action|escape:'htmlall':'UTF-8'}">
                        <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">
                        <input type="hidden" name="submitTestApi" value="1">
                        <button type="submit" class="btn btn-default">
                            <i class="icon-refresh"></i>
                            {l s='Testa connessione API' mod='spedisciquishipping'}
                        </button>
                    </form>

                    <form method="POST" action="{$action|escape:'htmlall':'UTF-8'}">
                        <input type="hidden" name="token" value="{$token|escape:'htmlall':'UTF-8'}">
                        <input type="hidden" name="submitResetToken" value="1">
                        <button type="submit" class="btn btn-warning"
                            onclick="return confirm('{l s='Vuoi riconfigurare l\'access token?' mod='spedisciquishipping' js=1}');">
                            <i class="icon-key"></i>
                            {l s='Riconfigura Access Token' mod='spedisciquishipping'}
                        </button>
                    </form>

                </div>
            </div>
        </div>

    </div>
</div>