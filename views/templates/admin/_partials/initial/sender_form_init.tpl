{* views/templates/admin/_partials/initial/sender_form_init.tpl *}

<style>
    /* ── Palette verde ── */
    :root {
        --sq-green-50: #eaf3de;
        --sq-green-100: #c0dd97;
        --sq-green-400: #639922;
        --sq-green-600: #3b6d11;
        --sq-green-800: #27500a;
    }

    .sq-card {
        background: var(--color-background-primary);
        border: 0.5px solid var(--color-border-tertiary);
        border-radius: 16px;
        max-width: 540px;
        margin: 2rem auto;
        overflow: hidden;
    }

    /* ── Header ── */
    .sq-card-header {
        background: var(--sq-green-50);
        border-bottom: 0.5px solid var(--sq-green-100);
        padding: 20px 24px 18px;
        display: flex;
        align-items: center;
        gap: 14px;
    }

    .sq-card-header-icon {
        width: 40px;
        height: 40px;
        border-radius: 10px;
        background: var(--sq-green-100);
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }

    .sq-card-header-icon i {
        font-size: 20px;
        color: var(--sq-green-800);
    }

    .sq-card-header h2 {
        margin: 0;
        font-size: 15px;
        font-weight: 500;
        color: var(--sq-green-800);
        line-height: 1.2;
    }

    .sq-card-header p {
        margin: 3px 0 0;
        font-size: 12px;
        color: var(--sq-green-600);
    }

    /* ── Body ── */
    .sq-card-body {
        padding: 24px;
    }

    /* ── Section label con linea ── */
    .sq-section-label {
        font-size: 10px;
        font-weight: 500;
        color: var(--sq-green-400);
        text-transform: uppercase;
        letter-spacing: 0.08em;
        margin: 0 0 14px;
        display: flex;
        align-items: center;
        gap: 7px;
    }

    .sq-section-label i {
        font-size: 13px;
    }

    .sq-section-label::after {
        content: '';
        flex: 1;
        height: 0.5px;
        background: var(--sq-green-100);
    }

    /* ── Divider ── */
    .sq-divider {
        height: 0.5px;
        background: var(--color-border-tertiary);
        margin: 20px 0;
    }

    /* ── Grid ── */
    .sq-grid {
        display: grid;
        gap: 14px;
    }

    .sq-grid-2 {
        grid-template-columns: 1fr 1fr;
    }

    .sq-grid-3 {
        grid-template-columns: 2fr 1fr 1fr;
    }

    /* ── Field ── */
    .sq-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .sq-label {
        font-size: 12px;
        font-weight: 500;
        color: var(--color-text-secondary);
    }

    .sq-opt {
        font-size: 11px;
        color: var(--color-text-tertiary);
        font-weight: 400;
    }

    /* ── Inputs ── */
    .sq-input,
    .sq-select {
        height: 38px;
        border: 1px solid var(--sq-green-100);
        border-radius: 8px;
        padding: 0 12px;
        font-size: 14px;
        background: var(--color-background-primary);
        color: var(--color-text-primary);
        width: 100%;
        box-sizing: border-box;
        outline: none;
        transition: border-color .15s, box-shadow .15s;
    }

    .sq-input:focus,
    .sq-select:focus {
        border-color: var(--sq-green-400);
        box-shadow: 0 0 0 3px rgba(99, 153, 34, .12);
    }

    .sq-input::placeholder {
        color: var(--color-text-tertiary);
    }

    /* ── Hint ── */
    .sq-hint {
        font-size: 11px;
        color: var(--color-text-tertiary);
        margin-top: 3px;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .sq-hint i {
        font-size: 12px;
    }

    /* ── Footer ── */
    .sq-footer {
        padding: 16px 24px;
        border-top: 0.5px solid var(--sq-green-100);
        background: var(--sq-green-50);
        display: flex;
        justify-content: flex-end;
    }

    .sq-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 24px;
        background: var(--sq-green-600);
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #fff;
        cursor: pointer;
        transition: background .15s;
    }

    .sq-btn:hover {
        background: var(--sq-green-800);
    }

    .sq-btn i {
        font-size: 16px;
    }
</style>

<form method="post" action="{$action}">
    <div class="sq-card">

        <div class="sq-card-header">
            <div class="sq-card-header-icon">
                <i class="icon-home"></i>
            </div>
            <div>
                <h2>{l s='Indirizzo mittente' mod='spedisciquishipping'}</h2>
                <p>{l s='Usato come mittente predefinito per le spedizioni' mod='spedisciquishipping'}</p>
            </div>
        </div>

        <div class="sq-card-body">

            {* IDENTIFICAZIONE *}
            <p class="sq-section-label">
                <i class="icon-credit-card"></i>
                {l s='Identificazione' mod='spedisciquishipping'}
            </p>
            <div class="sq-grid sq-grid-2">
                <div class="sq-field">
                    <label class="sq-label" for="sender_label">
                        {l s='Tipo indirizzo' mod='spedisciquishipping'}
                        <span style="color:#c0392b"> *</span>
                    </label>
                    <select class="sq-select" id="sender_label" name="SQ_SENDER_LABEL" required>
                        <option value="Sede principale" {if $sender.label == 'Sede principale'}selected{/if}>
                            {l s='Sede principale' mod='spedisciquishipping'}
                        </option>
                        <option value="Magazzino" {if $sender.label == 'Magazzino'}selected{/if}>
                            {l s='Magazzino' mod='spedisciquishipping'}
                        </option>
                        <option value="Deposito" {if $sender.label == 'Deposito'}selected{/if}>
                            {l s='Deposito' mod='spedisciquishipping'}
                        </option>
                        <option value="Filiale" {if $sender.label == 'Filiale'}selected{/if}>
                            {l s='Filiale' mod='spedisciquishipping'}
                        </option>
                        <option value="Altro" {if $sender.label == 'Altro'}selected{/if}>
                            {l s='Altro' mod='spedisciquishipping'}
                        </option>
                    </select>
                </div>
                <div class="sq-field">
                    <label class="sq-label" for="sender_company">
                        {l s='Azienda' mod='spedisciquishipping'}
                        <span class="sq-opt">({l s='opzionale' mod='spedisciquishipping'})</span>
                    </label>
                    <input class="sq-input" type="text" id="sender_company" name="SQ_SENDER_COMPANY"
                        placeholder="Acme SRL" value="{$sender.company|escape:'htmlall':'UTF-8'}">
                </div>
            </div>

            <div class="sq-divider"></div>

            {* INTESTAZIONE *}
            <p class="sq-section-label">
                <i class="icon-user"></i>
                {l s='Intestazione' mod='spedisciquishipping'}
            </p>
            <div class="sq-grid sq-grid-2">
                <div class="sq-field">
                    <label class="sq-label" for="sender_name">
                        {l s='Nome' mod='spedisciquishipping'}
                        <span style="color:#c0392b"> *</span>
                    </label>
                    <input class="sq-input" type="text" id="sender_name" name="SQ_SENDER_FIRSTNAME" placeholder="Mario"
                        value="{$sender.name|escape:'htmlall':'UTF-8'}" required>
                </div>
                <div class="sq-field">
                    <label class="sq-label" for="sender_surname">
                        {l s='Cognome' mod='spedisciquishipping'}
                        <span style="color:#c0392b"> *</span>
                    </label>
                    <input class="sq-input" type="text" id="sender_surname" name="SQ_SENDER_LASTNAME"
                        placeholder="Rossi" value="{$sender.surname|escape:'htmlall':'UTF-8'}" required>
                </div>
            </div>

            <div class="sq-divider"></div>

            {* CONTATTI *}
            <p class="sq-section-label">
                <i class="icon-phone"></i>
                {l s='Contatti' mod='spedisciquishipping'}
            </p>
            <div class="sq-grid sq-grid-2">
                <div class="sq-field">
                    <label class="sq-label" for="sender_phone">
                        {l s='Telefono' mod='spedisciquishipping'}
                        <span style="color:#c0392b"> *</span>
                    </label>
                    <input class="sq-input" type="tel" id="sender_phone" name="SQ_SENDER_PHONE"
                        placeholder="+39 081 123 4567" value="{$sender.phone|escape:'htmlall':'UTF-8'}" required>
                </div>
                <div class="sq-field">
                    <label class="sq-label" for="sender_email">
                        {l s='Email' mod='spedisciquishipping'}
                        <span class="sq-opt">({l s='opzionale' mod='spedisciquishipping'})</span>
                    </label>
                    <input class="sq-input" type="email" id="sender_email" name="SQ_SENDER_EMAIL"
                        placeholder="mario@esempio.it" value="{$sender.email|escape:'htmlall':'UTF-8'}">
                </div>
            </div>

            <div class="sq-divider"></div>

            {* INDIRIZZO *}
            <p class="sq-section-label">
                <i class="icon-map-marker"></i>
                {l s='Indirizzo' mod='spedisciquishipping'}
            </p>
            <div class="sq-field" style="margin-bottom:14px;">
                <label class="sq-label" for="sender_address">
                    {l s='Via / Piazza' mod='spedisciquishipping'}
                    <span style="color:#c0392b"> *</span>
                </label>
                <input class="sq-input" type="text" id="sender_address" name="SQ_SENDER_ADDRESS1"
                    placeholder="Via Roma 1" value="{$sender.address|escape:'htmlall':'UTF-8'}" required>
            </div>

            <div class="sq-grid sq-grid-3">
                <div class="sq-field">
                    <label class="sq-label" for="sender_city">
                        {l s='Città' mod='spedisciquishipping'}
                        <span style="color:#c0392b"> *</span>
                    </label>
                    <input class="sq-input" type="text" id="sender_city" name="SQ_SENDER_CITY" placeholder="Napoli"
                        value="{$sender.city|escape:'htmlall':'UTF-8'}" required>
                </div>
                <div class="sq-field">
                    <label class="sq-label" for="sender_zip">
                        {l s='CAP' mod='spedisciquishipping'}
                        <span style="color:#c0392b"> *</span>
                    </label>
                    <input class="sq-input" type="text" id="sender_zip" name="SQ_SENDER_POSTCODE" placeholder="80100"
                        value="{$sender.zip|escape:'htmlall':'UTF-8'}" required>
                </div>
                <div class="sq-field">
                    <label class="sq-label" for="sender_prov">
                        {l s='Provincia' mod='spedisciquishipping'}
                        <span style="color:#c0392b"> *</span>
                    </label>
                    <input class="sq-input" type="text" id="sender_prov" name="SQ_SENDER_STATE" placeholder="NA"
                        maxlength="5" value="{$sender.prov|escape:'htmlall':'UTF-8'}" required>
                </div>
            </div>

            <div class="sq-field" style="margin-top:14px;">
                <label class="sq-label" for="sender_country">
                    {l s='Paese' mod='spedisciquishipping'}
                </label>
                <input class="sq-input" type="text" id="sender_country" name="SQ_SENDER_COUNTRY_ISO" placeholder="IT"
                    maxlength="2" style="max-width:90px;"
                    value="{$sender.country|default:'IT'|escape:'htmlall':'UTF-8'}">
                <span class="sq-hint">
                    <i class="icon-info-sign"></i>
                    {l s='Codice ISO 3166-1 alpha-2 — es. IT, FR, DE' mod='spedisciquishipping'}
                </span>
            </div>

        </div>

        <div class="sq-footer">
            {* Campi non visibili ma letti dall'handler *}
            <input type="hidden" name="SQ_SENDER_PHONE_MOBILE" value="{$sender.phone_mobile|escape:'htmlall':'UTF-8'}">
            <input type="hidden" name="SQ_SENDER_ADDRESS2" value="{$sender.address2|escape:'htmlall':'UTF-8'}">
            <input type="hidden" name="SQ_SENDER_VAT_NUMBER" value="{$sender.vat_number|escape:'htmlall':'UTF-8'}">
            <input type="hidden" name="SQ_SENDER_ID_COUNTRY" value="110">

            <button type="submit" name="submitSpedisciQuiSender" class="sq-btn">
                <i class="icon-arrow-right"></i>
                {l s='Salva e continua' mod='spedisciquishipping'}
            </button>
        </div>

    </div>
</form>