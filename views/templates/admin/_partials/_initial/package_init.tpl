{* views/templates/admin/package_config.tpl *}

<form method="post" action="{$action}">
    <div class="sq-card">

        <div class="sq-card-header">
            <i class="icon-archive"></i>
            <div>
                <h2>{l s='Dati pacco' mod='spedisciquishipping'}</h2>
                <p>{l s='Profilo dimensioni e peso predefiniti' mod='spedisciquishipping'}</p>
            </div>
        </div>

        <div class="sq-card-body">

            {* IDENTIFICAZIONE *}
            <p class="sq-section-label">{l s='Identificazione' mod='spedisciquishipping'}</p>
            <div class="sq-field">
                <label class="sq-label" for="package_name">
                    {l s='Nome profilo' mod='spedisciquishipping'}
                    <span style="color:#e74c3c"> *</span>
                </label>
                <input class="sq-input" type="text" id="package_name" name="package_name" placeholder="Default" maxlength="100" value="{$package.name|default:'Default'|escape:'htmlall':'UTF-8'}" required>
            </div>

            <div class="sq-divider"></div>

            {* DIMENSIONI *}
            <p class="sq-section-label">{l s='Dimensioni' mod='spedisciquishipping'}</p>
            <div class="sq-grid sq-grid-2">
                <div class="sq-field">
                    <label class="sq-label" for="package_length">
                        {l s='Lunghezza (cm)' mod='spedisciquishipping'}
                        <span style="color:#e74c3c"> *</span>
                    </label>
                    <input class="sq-input" type="number" step="0.01" min="0" id="package_length" name="package_length" placeholder="30.00" value="{$package.length|default:'30.00'|escape:'htmlall':'UTF-8'}" required>
                </div>
                <div class="sq-field">
                    <label class="sq-label" for="package_width">
                        {l s='Larghezza (cm)' mod='spedisciquishipping'}
                        <span style="color:#e74c3c"> *</span>
                    </label>
                    <input class="sq-input" type="number" step="0.01" min="0" id="package_width" name="package_width" placeholder="20.00" value="{$package.width|default:'20.00'|escape:'htmlall':'UTF-8'}" required>
                </div>
            </div>
            <div class="sq-grid" style="margin-top:14px;">
                <div class="sq-field">
                    <label class="sq-label" for="package_height">
                        {l s='Altezza (cm)' mod='spedisciquishipping'}
                        <span style="color:#e74c3c"> *</span>
                    </label>
                    <input class="sq-input" type="number" step="0.01" min="0" id="package_height" name="package_height" placeholder="10.00" value="{$package.height|default:'10.00'|escape:'htmlall':'UTF-8'}" required>
                </div>
            </div>

            <div class="sq-divider"></div>

            {* PESO *}
            <p class="sq-section-label">{l s='Peso' mod='spedisciquishipping'}</p>
            <div class="sq-field">
                <label class="sq-label" for="package_weight">
                    {l s='Peso (kg)' mod='spedisciquishipping'}
                    <span style="color:#e74c3c"> *</span>
                </label>
                <input class="sq-input" type="number" step="0.001" min="0" id="package_weight" name="package_weight" placeholder="1.000" value="{$package.weight|default:'1.000'|escape:'htmlall':'UTF-8'}" required>
            </div>

            <div class="sq-divider"></div>

            {* DEFAULT *}
            <div class="sq-checkbox-row">
                <input type="checkbox" name="package_is_default" id="package_is_default" value="1" {if isset($package.is_default) && $package.is_default == 1}checked{/if}>
                <label for="package_is_default">
                    {l s='Usa come pacco predefinito' mod='spedisciquishipping'}
                </label>
            </div>

        </div>

        <div class="sq-footer">
            <button type="submit" name="submitSpedisciQuiDefaultPackage" class="sq-btn">
                <i class="icon-arrow-right"></i>
                {l s='Salva e continua' mod='spedisciquishipping'}
            </button>
        </div>

    </div>
</form>