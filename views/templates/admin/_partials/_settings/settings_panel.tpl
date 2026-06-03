{* partials/settings_panel.tpl *}

{* ── INFO UTENTE ─────────────────────────────────────── *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-user"></i>
        {l s='Account SpedisciQui' mod='spedisciquishipping'}
    </div>
    <div class="panel-body">
        {if $user && isset($user.user.name)}
            <table class="table" style="max-width:500px;">
                <tr>
                    <td style="width:160px;"><strong>{l s='Nome' mod='spedisciquishipping'}</strong></td>
                    <td>{$user.user.name|escape:'htmlall':'UTF-8'}</td>
                </tr>
                <tr>
                    <td><strong>{l s='Email' mod='spedisciquishipping'}</strong></td>
                    <td>{$user.user.email|escape:'htmlall':'UTF-8'}</td>
                </tr>
            </table>
        {else}
            <p class="text-muted">
                {l s='Impossibile recuperare i dati utente.' mod='spedisciquishipping'}
            </p>
        {/if}
    </div>
</div>

{* ── MITTENTE (SHOP) ─────────────────────────────────── *}
<div class="panel" style="margin-top:20px;">
    <div class="panel-heading">
        <i class="icon-home"></i>
        {l s='Mittente' mod='spedisciquishipping'}
    </div>
    <div class="panel-body">
        {if $shop}
            <table class="table" style="max-width:500px;">
                {if isset($shop.name) && $shop.name}
                    <tr>
                        <td style="width:160px;"><strong>{l s='Ragione sociale' mod='spedisciquishipping'}</strong></td>
                        <td>{$shop.name|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/if}
                {if isset($shop.address) && $shop.address}
                    <tr>
                        <td><strong>{l s='Indirizzo' mod='spedisciquishipping'}</strong></td>
                        <td>{$shop.address|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/if}
                {if isset($shop.city) && $shop.city}
                    <tr>
                        <td><strong>{l s='Città / CAP / Prov.' mod='spedisciquishipping'}</strong></td>
                        <td>
                            {$shop.city|escape:'htmlall':'UTF-8'}
                            {if isset($shop.postcode) && $shop.postcode}
                                &nbsp;{$shop.postcode|escape:'htmlall':'UTF-8'}
                            {/if}
                            {if isset($shop.state) && $shop.state}
                                &nbsp;({$shop.state|escape:'htmlall':'UTF-8'})
                            {/if}
                        </td>
                    </tr>
                {/if}
                {if isset($shop.phone) && $shop.phone}
                    <tr>
                        <td><strong>{l s='Telefono' mod='spedisciquishipping'}</strong></td>
                        <td>{$shop.phone|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/if}
                {if isset($shop.email) && $shop.email}
                    <tr>
                        <td><strong>{l s='Email' mod='spedisciquishipping'}</strong></td>
                        <td>{$shop.email|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/if}
                {if isset($shop.vat) && $shop.vat}
                    <tr>
                        <td><strong>{l s='P.IVA / C.F.' mod='spedisciquishipping'}</strong></td>
                        <td>{$shop.vat|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/if}
            </table>
        {else}
            <p class="text-muted">
                {l s='Impossibile recuperare i dati dello shop.' mod='spedisciquishipping'}
            </p>
        {/if}
    </div>
</div>