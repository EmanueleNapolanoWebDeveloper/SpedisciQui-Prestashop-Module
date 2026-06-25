{* views/templates/admin/_partials/initial/sender_form_init.tpl *}

<div class="sq-sender-update-wrap">

    {* ── Alerts ── *}
    {if isset($data.success) && $data.success}
        <div class="sq-alert sq-alert-success">
            <i class="icon-check"></i>
            <span>{l s='Indirizzo mittente salvato con successo.' mod='spedisciquishipping'}</span>
        </div>
    {/if}
    {if isset($data.error) && $data.error}
        <div class="sq-alert sq-alert-danger">
            <i class="icon-warning-sign"></i>
            <span>{$data.error|escape:'html':'UTF-8'}</span>
        </div>
    {/if}

    <form method="POST" action="{$action|escape:'html':'UTF-8'}" id="sq-sender-init-form">

        <input type="hidden" name="submitSpedisciQuiSender" value="1">

        {* ══════════════════════════════════════════
        SEZIONE 1 — Identificazione
        ══════════════════════════════════════════ *}
        <div class="sq-form-section">
            <p class="sq-form-section-title">
                <i class="icon-tag"></i>
                {l s='Identificazione' mod='spedisciquishipping'}
            </p>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-half">
                    <label class="sq-form-label" for="sender_label">
                        {l s='Tipo indirizzo' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <select class="sq-form-control" id="sender_label" name="SQ_SENDER_LABEL" required>
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
                    <p class="sq-form-hint">
                        {l s='Nome interno per riconoscere questo mittente (es. Sede principale, Magazzino Roma).' mod='spedisciquishipping'}
                    </p>
                </div>

                <div class="sq-form-group sq-col-half">
                    <label class="sq-form-label" for="sender_company">
                        {l s='Azienda' mod='spedisciquishipping'}
                    </label>
                    <input type="text" id="sender_company" name="SQ_SENDER_COMPANY" class="sq-form-control" placeholder="Acme SRL" value="{$sender.company|default:''|escape:'html':'UTF-8'}" maxlength="150">
                </div>
            </div>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-half">
                    <label class="sq-form-label" for="sender_vat">
                        {l s='Partita IVA' mod='spedisciquishipping'}
                    </label>
                    <input type="text" id="sender_vat" name="SQ_SENDER_VAT_NUMBER" class="sq-form-control" placeholder="IT12345678901" value="{$sender.vat_number|default:''|escape:'html':'UTF-8'}" maxlength="50">
                </div>
            </div>

        </div>{* /sq-form-section Identificazione *}

        {* ══════════════════════════════════════════
        SEZIONE 2 — Referente
        ══════════════════════════════════════════ *}
        <div class="sq-form-section">
            <p class="sq-form-section-title">
                <i class="icon-user"></i>
                {l s='Referente' mod='spedisciquishipping'}
            </p>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-half">
                    <label class="sq-form-label" for="sender_name">
                        {l s='Nome' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="text" id="sender_name" name="SQ_SENDER_FIRSTNAME" class="sq-form-control" placeholder="Mario" value="{$sender.name|default:''|escape:'html':'UTF-8'}" maxlength="100" required>
                </div>

                <div class="sq-form-group sq-col-half">
                    <label class="sq-form-label" for="sender_surname">
                        {l s='Cognome' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="text" id="sender_surname" name="SQ_SENDER_LASTNAME" class="sq-form-control" placeholder="Rossi" value="{$sender.surname|default:''|escape:'html':'UTF-8'}" maxlength="100" required>
                </div>
            </div>

            <div class="sq-grid sq-grid-2">
                <div class="sq-field">
                    <label class="sq-label" for="sender_phone">
                        {l s='Telefono' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="tel" id="sender_phone" name="SQ_SENDER_PHONE" class="sq-input" placeholder="+39 081 123 4567" value="{$sender.phone|default:''|escape:'html':'UTF-8'}" maxlength="20" required>
                </div>

                <div class="sq-field">
                    <label class="sq-label" for="sender_phone_mobile">
                        {l s='Cellulare' mod='spedisciquishipping'}
                        <span class="sq-opt">{l s='(opzionale)' mod='spedisciquishipping'}</span>
                    </label>
                    <input type="tel" id="sender_phone_mobile" name="SQ_SENDER_PHONE_MOBILE" class="sq-input" placeholder="+39 333 123 4567" value="{$sender.phone_mobile|default:''|escape:'html':'UTF-8'}" maxlength="20">
                </div>
            </div>

            <div class="sq-grid">
                <div class="sq-field">
                    <label class="sq-label" for="sender_email">
                        {l s='Email' mod='spedisciquishipping'}
                        <span class="sq-opt">{l s='(opzionale)' mod='spedisciquishipping'}</span>
                    </label>
                    <input type="email" id="sender_email" name="SQ_SENDER_EMAIL" class="sq-input" placeholder="mario@esempio.it" value="{$sender.email|default:''|escape:'html':'UTF-8'}" maxlength="150">
                </div>
            </div>

        </div>{* /sq-form-section Referente *}

        {* ══════════════════════════════════════════
        SEZIONE 3 — Indirizzo
        ══════════════════════════════════════════ *}
        <div class="sq-form-section">
            <p class="sq-form-section-title">
                <i class="icon-map-marker"></i>
                {l s='Indirizzo' mod='spedisciquishipping'}
            </p>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-full">
                    <label class="sq-form-label" for="sender_address">
                        {l s='Via / Piazza' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="text" id="sender_address" name="SQ_SENDER_ADDRESS1" class="sq-form-control" placeholder="Via Roma 1" value="{$sender.address|default:''|escape:'html':'UTF-8'}" maxlength="255" required>
                </div>
            </div>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-full">
                    <label class="sq-form-label" for="sender_address2">
                        {l s='Indirizzo (riga 2)' mod='spedisciquishipping'}
                        <span class="sq-opt">{l s='(opzionale)' mod='spedisciquishipping'}</span>
                    </label>
                    <input type="text" id="sender_address2" name="SQ_SENDER_ADDRESS2" class="sq-form-control" placeholder="{l s='Interno, scala, piano...' mod='spedisciquishipping'}" value="{$sender.address2|default:''|escape:'html':'UTF-8'}" maxlength="255">
                </div>
            </div>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-quarter">
                    <label class="sq-form-label" for="sender_zip">
                        {l s='CAP' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="text" id="sender_zip" name="SQ_SENDER_POSTCODE" class="sq-form-control" placeholder="80100" value="{$sender.zip|default:''|escape:'html':'UTF-8'}" maxlength="12" required>
                </div>

                <div class="sq-form-group sq-col-half">
                    <label class="sq-form-label" for="sender_city">
                        {l s='Città' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="text" id="sender_city" name="SQ_SENDER_CITY" class="sq-form-control" placeholder="Napoli" value="{$sender.city|default:''|escape:'html':'UTF-8'}" maxlength="100" required>
                </div>

                <div class="sq-form-group sq-col-quarter">
                    <label class="sq-form-label" for="sender_prov">
                        {l s='Provincia' mod='spedisciquishipping'}
                    </label>
                    <input type="text" id="sender_prov" name="SQ_SENDER_STATE" class="sq-form-control" placeholder="NA" value="{$sender.prov|default:''|escape:'html':'UTF-8'}" maxlength="10">
                </div>
            </div>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-quarter">
                    <label class="sq-form-label" for="sender_country">
                        {l s='Paese (ISO)' mod='spedisciquishipping'}
                        <span class="sq-required">*</span>
                    </label>
                    <input type="text" id="sender_country" name="SQ_SENDER_COUNTRY_ISO" class="sq-form-control" placeholder="IT" value="{$sender.country|default:'IT'|escape:'html':'UTF-8'}" maxlength="2" required>
                    <p class="sq-form-hint">
                        {l s='Codice ISO 3166-1 alpha-2 — es. IT, FR, DE' mod='spedisciquishipping'}
                    </p>
                </div>
            </div>

        </div>{* /sq-form-section Indirizzo *}

        {* ══════════════════════════════════════════
        SEZIONE 4 — Impostazioni
        ══════════════════════════════════════════ *}
        <div class="sq-form-section">
            <p class="sq-form-section-title">
                <i class="icon-cog"></i>
                {l s='Impostazioni' mod='spedisciquishipping'}
            </p>

            <div class="sq-form-row">
                <div class="sq-form-group sq-col-half">
                    <div class="sq-toggle-wrap">
                        <label class="sq-toggle-label" for="sender_default">
                            <input type="hidden" name="SQ_SENDER_IS_DEFAULT" value="0">
                            <input type="checkbox" id="sender_default" name="SQ_SENDER_IS_DEFAULT" value="1" {if $sender.is_default}checked{/if}>
                            <span class="sq-toggle-text">
                                {l s='Mittente predefinito' mod='spedisciquishipping'}
                            </span>
                        </label>
                        <p class="sq-form-hint">
                            {l s='Verrà usato come mittente di default per le nuove spedizioni.' mod='spedisciquishipping'}
                        </p>
                    </div>
                </div>

                <div class="sq-form-group sq-col-half">
                    <div class="sq-toggle-wrap">
                        <label class="sq-toggle-label" for="sender_active">
                            <input type="hidden" name="SQ_SENDER_IS_ACTIVE" value="0">
                            <input type="checkbox" id="sender_active" name="SQ_SENDER_IS_ACTIVE" value="1" {if $sender.is_active|default:1}checked{/if}>
                            <span class="sq-toggle-text">
                                {l s='Mittente attivo' mod='spedisciquishipping'}
                            </span>
                        </label>
                        <p class="sq-form-hint">
                            {l s='I mittenti inattivi non vengono proposti nella creazione spedizioni.' mod='spedisciquishipping'}
                        </p>
                    </div>
                </div>
            </div>

        </div>{* /sq-form-section Impostazioni *}

        {* ── Hidden legacy ── *}
        <input type="hidden" name="SQ_SENDER_ID_COUNTRY" value="110">

        {* ── Actions ── *}
        <div class="sq-form-actions">
            <button type="submit" class="sq-btn sq-btn-primary" id="sq-sender-init-submit">
                <i class="icon-arrow-right"></i>
                {l s='Salva e continua' mod='spedisciquishipping'}
            </button>
        </div>

    </form>
</div>