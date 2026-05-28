<div class="panel">

    <div class="panel-heading" style="display:flex; align-items:center; justify-content:space-between;">

        <div style="display:flex; align-items:center; gap:12px;">
            <div style="
                    width:42px;
                    height:42px;
                    background:#f8f8f8;
                    border:1px solid #e5e5e5;
                    border-radius:10px;
                    display:flex;
                    align-items:center;
                    justify-content:center;
                ">
                <i class="icon-truck" style="font-size:20px;"></i>
            </div>

            <div>
                <div style="font-size:18px; font-weight:600;">
                    SpedisciQui Shipping
                </div>

                <div style="font-size:12px; color:#777;">
                    Configurazione iniziale modulo
                </div>
            </div>
        </div>

    </div>

    <div class="panel-body">

        <form method="POST" action="{$formAction|escape:'htmlall':'UTF-8'}">

            {* STATUS *}
            {if $tokenStatus === 'expired'}
                <div class="alert alert-danger">
                    <i class="icon-warning-sign"></i>
                    {l s='Token scaduto. Inserisci un nuovo token.' mod='spedisciquishipping'}
                </div>

            {elseif $tokenStatus === 'expiring'}
                <div class="alert alert-warning">
                    <i class="icon-time"></i>

                    {l s='Token in scadenza tra' mod='spedisciquishipping'}

                    <strong>{$daysLeft}</strong>

                    {l s='giorni' mod='spedisciquishipping'}

                    ({$expiresAt})
                </div>

            {elseif $tokenStatus === 'active'}
                <div class="alert alert-success">
                    <i class="icon-check"></i>

                    {l s='Token attivo fino al' mod='spedisciquishipping'}

                    <strong>{$expiresAt}</strong>
                </div>
            {/if}

            {* TOKEN *}
            <div class="form-group">

                <label for="spedisciqui_token">
                    {l s='Access Token' mod='spedisciquishipping'}
                </label>

                <input type="text" id="spedisciqui_token" name="SPEDISCIQUI_ACCESS_TOKEN" class="form-control"
                    value="{$currentToken|escape:'htmlall':'UTF-8'}" required>

                <p class="help-block">
                    {l s='Incolla il token ottenuto dalla piattaforma SpedisciQui.' mod='spedisciquishipping'}
                </p>

            </div>

            {* FOOTER *}
            <div class="panel-footer" style="
                    display:flex;
                    justify-content:space-between;
                    align-items:center;
                    gap:12px;
                ">

                <a href="https://www.spedisciqui.it/login" target="_blank" class="btn btn-default">
                    <i class="icon-external-link"></i>

                    {l s='Iscriviti' mod='spedisciquishipping'}
                </a>

                <button type="submit" name="submitSpedisciQuiCredentials" class="btn btn-primary">
                    <i class="process-icon-save"></i>

                    {l s='Salva e verifica' mod='spedisciquishipping'}
                </button>

            </div>

        </form>

    </div>

</div>