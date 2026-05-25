{* partials/settings_panel.tpl *}

{* ── INFO UTENTE ─────────────────────────────────────── *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-user"></i>
        {l s='Account' mod='spedisciquishipping'}
    </div>
    <div class="panel-body">
        {if $user && isset($user.user.name)}
            <table class="table" style="max-width:500px;">
                <tr>
                    <td><strong>{l s='Nome' mod='spedisciquishipping'}</strong></td>
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

{* ── AZIONI ─────────────────────────────────────────── *}
<div class="panel" style="margin-top:20px;">
    <div class="panel-heading">
        <i class="icon-cogs"></i>
        {l s='Azioni' mod='spedisciquishipping'}
    </div>
    <div class="panel-body">
        <div style="display:flex; gap:12px; flex-wrap:wrap;">

            <form method="POST" action="{$action|escape:'htmlall':'UTF-8'}">
                <button type="submit" name="submitTestApi" class="btn btn-default">
                    <i class="icon-refresh"></i>
                    {l s='Testa connessione API' mod='spedisciquishipping'}
                </button>
            </form>

            <form method="POST" action="{$action|escape:'htmlall':'UTF-8'}">
                <button type="submit" name="submitResetToken" class="btn btn-warning"
                    onclick="return confirm('{l s='Riconfigurare il token?' mod='spedisciquishipping' js=1}');">
                    <i class="icon-key"></i>
                    {l s='Riconfigura Access Token' mod='spedisciquishipping'}
                </button>
            </form>

        </div>
    </div>
</div>