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