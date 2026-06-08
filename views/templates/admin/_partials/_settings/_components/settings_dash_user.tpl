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