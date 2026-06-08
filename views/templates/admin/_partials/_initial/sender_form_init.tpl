{* views/templates/admin/_partials/initial/sender_form_init.tpl *}


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