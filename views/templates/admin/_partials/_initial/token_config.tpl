
<div class="sq-cred panel">

    {* ── Header ── *}
    <div class="sq-cred-header">
        <div class="sq-cred-icon">
            <i class="icon-truck"></i>
        </div>
        <div>
            <p class="sq-cred-title">SpedisciQui Shipping</p>
            <p class="sq-cred-subtitle">{l s='Configurazione iniziale — Access Token' mod='spedisciquishipping'}</p>
        </div>
    </div>

    <form method="POST" action="{$formAction|escape:'htmlall':'UTF-8'}">

        {* ── Status token ── *}
        {if $tokenStatus === 'expired'}
            <div class="sq-status-alert sq-alert-danger">
                <i class="icon-warning-sign"></i>
                <span>{l s='Token scaduto. Inserisci un nuovo token per continuare.' mod='spedisciquishipping'}</span>
            </div>
        {elseif $tokenStatus === 'expiring'}
            <div class="sq-status-alert sq-alert-warning">
                <i class="icon-time"></i>
                <span>
                    {l s='Token in scadenza tra' mod='spedisciquishipping'}
                    <strong>{$daysLeft}</strong>
                    {l s='giorni' mod='spedisciquishipping'}
                    &mdash; {$expiresAt}
                </span>
            </div>
        {elseif $tokenStatus === 'active'}
            <div class="sq-status-alert sq-alert-success">
                <i class="icon-check"></i>
                <span>
                    {l s='Token attivo fino al' mod='spedisciquishipping'}
                    <strong>{$expiresAt}</strong>
                </span>
            </div>
        {/if}

        {* ── Campo token ── *}
        <div class="sq-cred-body">
            <label class="sq-field-label" for="spedisciqui_token">
                {l s='Access Token' mod='spedisciquishipping'}
            </label>
            <div class="sq-field-wrap">
                <input type="password" id="spedisciqui_token" name="SPEDISCIQUI_ACCESS_TOKEN" class="sq-token-input"
                    value="{$currentToken|escape:'htmlall':'UTF-8'}" placeholder="es. eyJhbGciOi..." autocomplete="off"
                    required>
                <button type="button" class="sq-toggle-visibility" onclick="sqToggleToken(this)"
                    title="{l s='Mostra/nascondi token' mod='spedisciquishipping'}">
                    <i class="icon-eye" id="sq-eye-icon"></i>
                </button>
            </div>
            <p class="sq-field-help">
                <i class="icon-info-sign"></i>
                {l s='Incolla il token ottenuto dalla piattaforma SpedisciQui. Non condividerlo con nessuno.' mod='spedisciquishipping'}
            </p>
        </div>

        {* ── Footer ── *}
        <div class="sq-cred-footer">
            <a href="https://www.spedisciqui.it/login" target="_blank" rel="noopener noreferrer"
                class="sq-btn-register">
                <i class="icon-external-link"></i>
                {l s='Non hai un account? Iscriviti' mod='spedisciquishipping'}
            </a>
            <button type="submit" name="submitSpedisciQuiCredentials" class="sq-btn-save">
                <i class="icon-check"></i>
                {l s='Salva e verifica' mod='spedisciquishipping'}
            </button>
        </div>

    </form>
</div>

<script>
    function sqToggleToken(btn) {
        var input = document.getElementById('spedisciqui_token');
        var icon = document.getElementById('sq-eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.className = 'icon-eye-close';
        } else {
            input.type = 'password';
            icon.className = 'icon-eye';
        }
    }
</script>