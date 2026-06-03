<style>
    .sq-cred {
        font-family: 'Open Sans', -apple-system, BlinkMacSystemFont, sans-serif;
    }

    .sq-cred-header {
        display: flex;
        align-items: center;
        gap: 14px;
        padding: 20px 24px;
        border-bottom: 1px solid #e8ecef;
        background: #fff;
    }

    .sq-cred-icon {
        width: 44px;
        height: 44px;
        background: #e8f0fb;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sq-cred-icon i {
        font-size: 20px;
        color: #1a6fc4;
    }

    .sq-cred-title {
        font-size: 15px;
        font-weight: 700;
        color: #1a2535;
        margin: 0;
    }

    .sq-cred-subtitle {
        font-size: 12px;
        color: #8a9ab0;
        margin: 2px 0 0;
    }

    /* ── Alert status ── */
    .sq-status-alert {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        padding: 12px 16px;
        border-radius: 6px;
        font-size: 13px;
        margin: 20px 24px 0;
        border-left: 3px solid transparent;
    }

    .sq-status-alert i {
        font-size: 15px;
        flex-shrink: 0;
        margin-top: 1px;
    }

    .sq-alert-danger {
        background: #fdecea;
        border-color: #c0392b;
        color: #721c24;
    }

    .sq-alert-warning {
        background: #fff8e1;
        border-color: #d4a017;
        color: #856404;
    }

    .sq-alert-success {
        background: #e6f9ee;
        border-color: #1e7e34;
        color: #155724;
    }

    .sq-alert-danger i {
        color: #c0392b;
    }

    .sq-alert-warning i {
        color: #d4a017;
    }

    .sq-alert-success i {
        color: #1e7e34;
    }

    /* ── Body form ── */
    .sq-cred-body {
        padding: 24px 24px 0;
    }

    .sq-field-label {
        display: block;
        font-size: 12px;
        font-weight: 700;
        color: #5a6a7a;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 7px;
    }

    .sq-field-wrap {
        position: relative;
    }

    .sq-token-input {
        width: 100%;
        padding: 10px 44px 10px 14px;
        border: 1px solid #d0d8e4;
        border-radius: 6px;
        font-size: 13px;
        color: #1a2535;
        background: #fff;
        font-family: 'Courier New', monospace;
        outline: none;
        box-sizing: border-box;
        transition: border-color 0.15s, box-shadow 0.15s;
    }

    .sq-token-input:focus {
        border-color: #1a6fc4;
        box-shadow: 0 0 0 3px rgba(26, 111, 196, 0.12);
    }

    .sq-toggle-visibility {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        color: #9aabb8;
        font-size: 16px;
        padding: 0;
        line-height: 1;
        transition: color 0.15s;
    }

    .sq-toggle-visibility:hover {
        color: #1a6fc4;
    }

    .sq-field-help {
        font-size: 12px;
        color: #9aabb8;
        margin: 7px 0 0;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .sq-field-help i {
        font-size: 12px;
    }

    /* ── Footer ── */
    .sq-cred-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 18px 24px;
        margin-top: 24px;
        border-top: 1px solid #e8ecef;
        background: #f8f9fb;
    }

    .sq-btn-register {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 8px 16px;
        background: #fff;
        color: #5a6a7a;
        border: 1px solid #dde3ea;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 600;
        text-decoration: none;
        transition: background 0.15s, border-color 0.15s;
    }

    .sq-btn-register:hover {
        background: #f0f4f8;
        border-color: #b0bbc8;
        color: #2c3e50;
        text-decoration: none;
    }

    .sq-btn-save {
        display: inline-flex;
        align-items: center;
        gap: 7px;
        padding: 9px 20px;
        background: #1a6fc4;
        color: #fff;
        border: none;
        border-radius: 5px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, transform 0.1s;
    }

    .sq-btn-save:hover {
        background: #155da0;
    }

    .sq-btn-save:active {
        transform: scale(0.97);
    }
</style>

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