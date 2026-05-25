{* partials/carriers_panel.tpl *}

{* ── CORRIERI DISPONIBILI DA API ─────────────────────── *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-cloud"></i>
        {l s='Corrieri disponibili sulla piattaforma' mod='spedisciquishipping'}
    </div>
    <div class="panel-body">

        {if !$carriers}
            <p class="text-muted text-center" style="padding:20px 0;">
                <i class="icon-warning-sign" style="font-size:20px;"></i><br>
                {l s='Nessun corriere disponibile. Verifica la connessione API.' mod='spedisciquishipping'}
            </p>
        {else}
            <table class="table">
                <thead>
                    <tr>
                        <th style="width:80px;">{l s='Logo' mod='spedisciquishipping'}</th>
                        <th>{l s='Corriere' mod='spedisciquishipping'}</th>
                        <th>{l s='Servizio' mod='spedisciquishipping'}</th>
                        <th>{l s='Consegna' mod='spedisciquishipping'}</th>
                        <th>{l s='Tipo' mod='spedisciquishipping'}</th>
                        <th>{l s='Destinazione' mod='spedisciquishipping'}</th>
                        <th style="width:120px;">{l s='Stato' mod='spedisciquishipping'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$carriers item=carrier}
                        <tr>
                            <td style="vertical-align:middle;">
                                {if $carrier.logo_url}
                                    <img src="{$carrier.logo_url|escape:'htmlall':'UTF-8'}"
                                        alt="{$carrier.name|escape:'htmlall':'UTF-8'}"
                                        style="max-height:32px; max-width:70px; object-fit:contain;">
                                {/if}
                            </td>
                            <td style="vertical-align:middle;">
                                <strong>{$carrier.name|escape:'htmlall':'UTF-8'}</strong><br>
                                <small class="text-muted">{$carrier.code|escape:'htmlall':'UTF-8'}</small>
                            </td>
                            <td style="vertical-align:middle;">
                                {$carrier.service_title|escape:'htmlall':'UTF-8'}
                            </td>
                            <td style="vertical-align:middle;">
                                <span class="badge">{$carrier.delivery_days|escape:'htmlall':'UTF-8'} gg</span>
                            </td>
                            <td style="vertical-align:middle;">
                                {if $carrier.type === 'national'}
                                    <span class="label label-success">{l s='Nazionale' mod='spedisciquishipping'}</span>
                                {else}
                                    <span class="label label-info">{l s='Internazionale' mod='spedisciquishipping'}</span>
                                {/if}
                            </td>
                            <td style="vertical-align:middle;">
                                {if $carrier.destination === 'home'}
                                    <span class="label label-primary">
                                        <i class="icon-home"></i> {l s='Domicilio' mod='spedisciquishipping'}
                                    </span>
                                {else}
                                    <span class="label label-warning">
                                        <i class="icon-map-marker"></i> {l s='Punto ritiro' mod='spedisciquishipping'}
                                    </span>
                                {/if}
                            </td>
                            <td style="vertical-align:middle;">
                                {if $carrier.isInstalled}
                                    <span class="label label-success">
                                        <i class="icon-check"></i> {l s='Attivo' mod='spedisciquishipping'}
                                    </span>
                                {else}
                                    <form method="POST" action="{$action|escape:'htmlall':'UTF-8'}">
                                        <input type="hidden" name="selected_carriers[]"
                                            value="{$carrier.code|escape:'htmlall':'UTF-8'}">
                                        <button type="submit" name="submitSpedisciQuiCarriers"
                                            class="btn btn-primary btn-sm">
                                            <i class="icon-plus"></i> {l s='Aggiungi' mod='spedisciquishipping'}
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

{* ── CORRIERI INSTALLATI ─────────────────────────────── *}
<div class="panel" style="margin-top:20px;">
    <div class="panel-heading">
        <i class="icon-check"></i>
        {l s='Corrieri attivi sul negozio' mod='spedisciquishipping'}
        {if $savedCarriers}
            <span class="badge" style="margin-left:8px; background:#1e7e34;">{$savedCarriers|count}</span>
        {/if}
    </div>
    <div class="panel-body">

        {if !$savedCarriers}
            <p class="text-muted text-center" style="padding:20px 0;">
                <i class="icon-warning-sign" style="font-size:20px;"></i><br>
                {l s='Nessun corriere ancora aggiunto.' mod='spedisciquishipping'}
            </p>
        {else}
            <table class="table">
                <thead>
                    <tr>
                        <th>{l s='ID PS' mod='spedisciquishipping'}</th>
                        <th>{l s='Nome' mod='spedisciquishipping'}</th>
                        <th>{l s='Codice API' mod='spedisciquishipping'}</th>
                        <th>{l s='Azioni' mod='spedisciquishipping'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$savedCarriers item=sc}
                        <tr>
                            <td style="vertical-align:middle;"><code>#{$sc.id_carrier|intval}</code></td>
                            <td style="vertical-align:middle;"><strong>{$sc.carrier_name|escape:'htmlall':'UTF-8'}</strong></td>
                            <td style="vertical-align:middle;">
                                <span class="label label-info">{$sc.carrier_code|escape:'htmlall':'UTF-8'}</span>
                            </td>
                            <td style="vertical-align:middle;">
                                <form method="POST" action="{$action|escape:'htmlall':'UTF-8'}">
                                    <input type="hidden" name="carrier_code"
                                        value="{$sc.carrier_code|escape:'htmlall':'UTF-8'}">
                                    <button type="submit" name="removeSpedisciQuiCarriers"
                                        class="btn btn-danger btn-sm"
                                        onclick="return confirm('{l s='Rimuovere il corriere?' mod='spedisciquishipping' js=1}');">
                                        <i class="icon-trash"></i> {l s='Rimuovi' mod='spedisciquishipping'}
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