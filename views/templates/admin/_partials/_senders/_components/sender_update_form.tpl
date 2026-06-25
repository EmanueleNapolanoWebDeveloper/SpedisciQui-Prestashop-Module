{**
* SpedisciQui — Update Sender Address
* Template: views/templates/admin/_partials/_settings/_sender/sender_update_form.tpl
**}

<div class="sq-sender-update-wrap">

    {* ── Gestione Alert Nativa / Custom ── *}
    {if isset($data.success) && $data.success}
        <div class="alert alert-success">
            <i class="icon-check"></i>
            <span>{l s='Indirizzo mittente aggiornato con successo.' mod='spedisciquishipping'}</span>
        </div>
    {/if}

    <form method="POST" action="{$action|escape:'html':'UTF-8'}" id="sq-sender-update-form">

        <input type="hidden" name="id_sender" value="{$sender.id_sender|intval}">

        {* ── SEZIONE 1 — Identificazione ── *}
        <div class="sq-form-section">
            <p class="sq-form-section-title">
                <i class="icon-tag"></i>
                {l s='Identificazione' mod='spedisciquishipping'}
            </p>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-full">
                    <label class="sq-form-label" for="sq-sender-label">
                        {l s='Etichetta' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="text" id="sq-sender-label" name="SQ_SENDER_LABEL" class="sq-form-control" value="{$sender.label|escape:'html':'UTF-8'}" maxlength="100" required>
                    <p class="sq-form-hint">
                        {l s='Nome interno per riconoscere questo mittente (es. Sede principale, Magazzino Roma).' mod='spedisciquishipping'}
                    </p>
                </div>
            </div>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-half">
                    <label class="sq-form-label" for="sq-sender-company">
                        {l s='Azienda' mod='spedisciquishipping'}
                    </label>
                    <input type="text" id="sq-sender-company" name="SQ_SENDER_COMPANY" class="sq-form-control" value="{$sender.company|default:''|escape:'html':'UTF-8'}" maxlength="150">
                </div>

                <div class="sq-form-group sq-col-half">
                    <label class="sq-form-label" for="sq-sender-vat">
                        {l s='Partita IVA' mod='spedisciquishipping'}
                    </label>
                    <input type="text" id="sq-sender-vat" name="SQ_SENDER_VAT_NUMBER" class="sq-form-control" value="{$sender.vat_number|default:''|escape:'html':'UTF-8'}" maxlength="50" placeholder="IT12345678901">
                </div>
            </div>
        </div>

        {* ── SEZIONE 2 — Referente ── *}
        <div class="sq-form-section">
            <p class="sq-form-section-title">
                <i class="icon-user"></i>
                {l s='Referente' mod='spedisciquishipping'}
            </p>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-half">
                    <label class="sq-form-label" for="sq-sender-firstname">
                        {l s='Nome' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="text" id="sq-sender-firstname" name="SQ_SENDER_FIRSTNAME" class="sq-form-control" value="{$sender.firstname|escape:'html':'UTF-8'}" maxlength="100" required>
                </div>

                <div class="sq-form-group sq-col-half">
                    <label class="sq-form-label" for="sq-sender-lastname">
                        {l s='Cognome' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="text" id="sq-sender-lastname" name="SQ_SENDER_LASTNAME" class="sq-form-control" value="{$sender.lastname|escape:'html':'UTF-8'}" maxlength="100" required>
                </div>
            </div>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-half">
                    <label class="sq-form-label" for="sq-sender-phone">
                        {l s='Telefono' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="tel" id="sq-sender-phone" name="SQ_SENDER_PHONE" class="sq-form-control" value="{$sender.phone|escape:'html':'UTF-8'}" maxlength="20" required>
                </div>

                <div class="sq-form-group sq-col-half">
                    <label class="sq-form-label" for="sq-sender-phone-mobile">
                        {l s='Cellulare' mod='spedisciquishipping'}
                    </label>
                    <input type="tel" id="sq-sender-phone-mobile" name="SQ_SENDER_PHONE_MOBILE" class="sq-form-control" value="{$sender.phone_mobile|default:''|escape:'html':'UTF-8'}" maxlength="20">
                </div>
            </div>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-full">
                    <label class="sq-form-label" for="sq-sender-email">
                        {l s='Email' mod='spedisciquishipping'}
                    </label>
                    <input type="email" id="sq-sender-email" name="SQ_SENDER_EMAIL" class="sq-form-control" value="{$sender.email|default:''|escape:'html':'UTF-8'}" maxlength="150">
                </div>
            </div>
        </div>

        {* ── SEZIONE 3 — Indirizzo ── *}
        <div class="sq-form-section">
            <p class="sq-form-section-title">
                <i class="icon-map-marker"></i>
                {l s='Indirizzo' mod='spedisciquishipping'}
            </p>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-full">
                    <label class="sq-form-label" for="sq-sender-address1">
                        {l s='Indirizzo' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="text" id="sq-sender-address1" name="SQ_SENDER_ADDRESS1" class="sq-form-control" value="{$sender.address1|escape:'html':'UTF-8'}" maxlength="255" required>
                </div>
            </div>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-full">
                    <label class="sq-form-label" for="sq-sender-address2">
                        {l s='Indirizzo (riga 2)' mod='spedisciquishipping'}
                    </label>
                    <input type="text" id="sq-sender-address2" name="SQ_SENDER_ADDRESS2" class="sq-form-control" value="{$sender.address2|default:''|escape:'html':'UTF-8'}" maxlength="255" placeholder="{l s='Interno, scala, piano... (opzionale)' mod='spedisciquishipping'}">
                </div>
            </div>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-quarter">
                    <label class="sq-form-label" for="sq-sender-postcode">
                        {l s='CAP' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="text" id="sq-sender-postcode" name="SQ_SENDER_POSTCODE" class="sq-form-control" value="{$sender.postcode|escape:'html':'UTF-8'}" maxlength="12" required>
                </div>

                <div class="sq-form-group sq-col-half">
                    <label class="sq-form-label" for="sq-sender-city">
                        {l s='Città' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="text" id="sq-sender-city" name="SQ_SENDER_CITY" class="sq-form-control" value="{$sender.city|escape:'html':'UTF-8'}" maxlength="100" required>
                </div>

                <div class="sq-form-group sq-col-quarter">
                    <label class="sq-form-label" for="sq-sender-state">
                        {l s='Provincia' mod='spedisciquishipping'}
                    </label>
                    <input type="text" id="sq-sender-state" name="SQ_SENDER_STATE" class="sq-form-control" value="{$sender.state_code|default:''|escape:'html':'UTF-8'}" maxlength="10" placeholder="NA">
                </div>
            </div>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-quarter">
                    <label class="sq-form-label" for="sq-sender-country">
                        {l s='Paese (ISO)' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="text" id="sq-sender-country" name="SQ_SENDER_COUNTRY_ISO" class="sq-form-control" value="{$sender.country_iso|escape:'html':'UTF-8'}" maxlength="2" placeholder="IT" required>
                    <input type="hidden" name="SQ_SENDER_ID_COUNTRY" value="{$sender.id_country|default:110|intval}">
                </div>
            </div>
        </div>

        {* ── SEZIONE 4 — Impostazioni ── *}
        <div class="sq-form-section">
            <p class="sq-form-section-title">
                <i class="icon-cog"></i>
                {l s='Impostazioni' mod='spedisciquishipping'}
            </p>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-half">
                    <div class="sq-toggle-wrap">
                        <label class="sq-toggle-label" for="sq-sender-default">
                            <input type="hidden" name="is_default" value="0">
                            <input type="checkbox" id="sq-sender-default" name="is_default" value="1" {if $sender.is_default}checked{/if}>
                            <span class="sq-toggle-text">{l s='Mittente predefinito' mod='spedisciquishipping'}</span>
                        </label>
                        <p class="sq-form-hint">{l s='Verrà usato come mittente di default per le nuove spedizioni.' mod='spedisciquishipping'}</p>
                    </div>
                </div>

                <div class="sq-form-group sq-col-half">
                    <div class="sq-toggle-wrap">
                        <label class="sq-toggle-label" for="sq-sender-active">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" id="sq-sender-active" name="is_active" value="1" {if $sender.is_active}checked{/if}>
                            <span class="sq-toggle-text">{l s='Mittente attivo' mod='spedisciquishipping'}</span>
                        </label>
                        <p class="sq-form-hint">{l s='I mittenti inattivi non vengono proposti nella creazione spedizioni.' mod='spedisciquishipping'}</p>
                    </div>
                </div>
            </div>
        </div>

        {* ── Actions con NAME Valorizzato per aggancio su PHP ── *}
        <div class="sq-form-actions">
            <button type="submit" name="submitUpdateSender" class="btn btn-primary sq-btn sq-btn-primary" id="sq-sender-submit">
                <i class="icon-save"></i>
                {l s='Salva modifiche' mod='spedisciquishipping'}
            </button>
            <a href="{$data.back_url|default:''|escape:'html':'UTF-8'}" class="btn btn-default sq-btn sq-btn-secondary">
                <i class="icon-arrow-left"></i>
                {l s='Annulla' mod='spedisciquishipping'}
            </a>
        </div>

    </form>
</div>