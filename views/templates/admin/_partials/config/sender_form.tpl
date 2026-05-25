{* views/templates/admin/sender_address.tpl *}

<style>
    .sq-card {
        background: var(--color-background-primary);
        border: 0.5px solid var(--color-border-tertiary);
        border-radius: var(--border-radius-lg);
        max-width: 520px;
        margin: 2rem auto;
        overflow: hidden;
    }

    .sq-card-header {
        padding: 20px 24px 16px;
        border-bottom: 0.5px solid var(--color-border-tertiary);
        background: var(--color-background-secondary);
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .sq-card-header i {
        font-size: 20px;
        color: var(--color-text-secondary);
    }

    .sq-card-header h2 {
        margin: 0;
        font-size: 16px;
        font-weight: 500;
    }

    .sq-card-header p {
        margin: 2px 0 0;
        font-size: 13px;
        color: var(--color-text-secondary);
    }

    .sq-card-body {
        padding: 24px;
    }

    .sq-section-label {
        font-size: 11px;
        font-weight: 500;
        color: var(--color-text-tertiary);
        text-transform: uppercase;
        letter-spacing: 0.06em;
        margin: 0 0 12px;
    }

    .sq-divider {
        height: 0.5px;
        background: var(--color-border-tertiary);
        margin: 20px 0;
    }

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

    .sq-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .sq-label {
        font-size: 13px;
        color: var(--color-text-secondary);
    }

    .sq-opt {
        font-size: 12px;
        color: var(--color-text-tertiary);
    }

    .sq-input {
        height: 36px;
        border: 0.5px solid var(--color-border-tertiary);
        border-radius: 8px;
        padding: 0 12px;
        font-size: 14px;
        background: #fff;
        color: #222;
        width: 100%;
        box-sizing: border-box;
        outline: none;
    }

    .sq-input:focus {
        border-color: #aaa;
    }

    .sq-select {
        height: 36px;
        border: 0.5px solid var(--color-border-tertiary);
        border-radius: 8px;
        padding: 0 12px;
        font-size: 14px;
        background: #fff;
        color: #222;
        width: 100%;
        box-sizing: border-box;
        outline: none;
    }

    .sq-hint {
        font-size: 12px;
        color: var(--color-text-tertiary);
        margin-top: 2px;
    }

    .sq-footer {
        padding: 16px 24px;
        border-top: 0.5px solid var(--color-border-tertiary);
        display: flex;
        justify-content: flex-end;
    }

    .sq-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 9px 22px;
        background: #fff;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        color: #222;
        cursor: pointer;
    }

    .sq-btn:hover {
        background: #f5f5f5;
    }
</style>

<form method="post" action="{$action}">
    <div class="sq-card">

        <div class="sq-card-header">
            <i class="icon-home"></i>
            <div>
                <h2>{l s='Indirizzo mittente' mod='spedisciquishipping'}</h2>
                <p>{l s='Usato come mittente predefinito per le spedizioni' mod='spedisciquishipping'}</p>
            </div>
        </div>

        <div class="sq-card-body">

            {* IDENTIFICAZIONE *}
            <p class="sq-section-label">{l s='Identificazione' mod='spedisciquishipping'}</p>
            <div class="sq-grid sq-grid-2">
                <div class="sq-field">
                    <label class="sq-label" for="sender_label">
                        {l s='Tipo indirizzo' mod='spedisciquishipping'}
                        <span style="color:#e74c3c"> *</span>
                    </label>
                    <select class="sq-select" id="sender_label" name="sender_label" required>
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
                    <input class="sq-input" type="text" id="sender_company" name="sender_company" placeholder="Acme SRL"
                        value="{$sender.company|escape:'htmlall':'UTF-8'}">
                </div>
            </div>

            <div class="sq-divider"></div>

            {* INTESTAZIONE *}
            <p class="sq-section-label">{l s='Intestazione' mod='spedisciquishipping'}</p>
            <div class="sq-grid sq-grid-2">
                <div class="sq-field">
                    <label class="sq-label" for="sender_name">
                        {l s='Nome' mod='spedisciquishipping'}
                        <span style="color:#e74c3c"> *</span>
                    </label>
                    <input class="sq-input" type="text" id="sender_name" name="sender_name" placeholder="Mario"
                        value="{$sender.name|escape:'htmlall':'UTF-8'}" required>
                </div>
                <div class="sq-field">
                    <label class="sq-label" for="sender_surname">
                        {l s='Cognome' mod='spedisciquishipping'}
                        <span style="color:#e74c3c"> *</span>
                    </label>
                    <input class="sq-input" type="text" id="sender_surname" name="sender_surname" placeholder="Rossi"
                        value="{$sender.surname|escape:'htmlall':'UTF-8'}" required>
                </div>
            </div>

            <div class="sq-divider"></div>

            {* CONTATTI *}
            <p class="sq-section-label">{l s='Contatti' mod='spedisciquishipping'}</p>
            <div class="sq-grid sq-grid-2">
                <div class="sq-field">
                    <label class="sq-label" for="sender_phone">
                        {l s='Telefono' mod='spedisciquishipping'}
                        <span style="color:#e74c3c"> *</span>
                    </label>
                    <input class="sq-input" type="tel" id="sender_phone" name="sender_phone"
                        placeholder="+39 081 123 4567" value="{$sender.phone|escape:'htmlall':'UTF-8'}" required>
                </div>
                <div class="sq-field">
                    <label class="sq-label" for="sender_email">
                        {l s='Email' mod='spedisciquishipping'}
                        <span class="sq-opt">({l s='opzionale' mod='spedisciquishipping'})</span>
                    </label>
                    <input class="sq-input" type="email" id="sender_email" name="sender_email"
                        placeholder="mario@esempio.it" value="{$sender.email|escape:'htmlall':'UTF-8'}">
                </div>
            </div>

            <div class="sq-divider"></div>

            {* INDIRIZZO *}
            <p class="sq-section-label">{l s='Indirizzo' mod='spedisciquishipping'}</p>
            <div class="sq-grid" style="margin-bottom:14px;">
                <div class="sq-field">
                    <label class="sq-label" for="sender_address">
                        {l s='Via / Piazza' mod='spedisciquishipping'}
                        <span style="color:#e74c3c"> *</span>
                    </label>
                    <input class="sq-input" type="text" id="sender_address" name="sender_address"
                        placeholder="Via Roma 1" value="{$sender.address|escape:'htmlall':'UTF-8'}" required>
                </div>
            </div>

            <div class="sq-grid sq-grid-3">
                <div class="sq-field">
                    <label class="sq-label" for="sender_city">
                        {l s='Città' mod='spedisciquishipping'}
                        <span style="color:#e74c3c"> *</span>
                    </label>
                    <input class="sq-input" type="text" id="sender_city" name="sender_city" placeholder="Napoli"
                        value="{$sender.city|escape:'htmlall':'UTF-8'}" required>
                </div>
                <div class="sq-field">
                    <label class="sq-label" for="sender_zip">
                        {l s='CAP' mod='spedisciquishipping'}
                        <span style="color:#e74c3c"> *</span>
                    </label>
                    <input class="sq-input" type="text" id="sender_zip" name="sender_zip" placeholder="80100"
                        value="{$sender.zip|escape:'htmlall':'UTF-8'}" required>
                </div>
                <div class="sq-field">
                    <label class="sq-label" for="sender_prov">
                        {l s='Provincia' mod='spedisciquishipping'}
                        <span style="color:#e74c3c"> *</span>
                    </label>
                    <input class="sq-input" type="text" id="sender_prov" name="sender_prov" placeholder="NA"
                        maxlength="5" value="{$sender.prov|escape:'htmlall':'UTF-8'}" required>
                </div>
            </div>

            <div class="sq-grid" style="margin-top:14px;">
                <div class="sq-field">
                    <label class="sq-label" for="sender_country">
                        {l s='Paese' mod='spedisciquishipping'}
                    </label>
                    <input class="sq-input" type="text" id="sender_country" name="sender_country" placeholder="IT"
                        maxlength="2" value="{$sender.country|default:'IT'|escape:'htmlall':'UTF-8'}">
                    <span class="sq-hint">
                        {l s='Codice ISO 3166-1 alpha-2 — es. IT, FR, DE' mod='spedisciquishipping'}
                    </span>
                </div>
            </div>

        </div>

        <div class="sq-footer">
            <button type="submit" name="submitSenderForm" class="sq-btn">
                <i class="icon-arrow-right"></i>
                {l s='Salva e continua' mod='spedisciquishipping'}
            </button>
        </div>

    </div>
</form>