{* views/templates/admin/package_config.tpl *}

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

    .sq-field {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }

    .sq-label {
        font-size: 13px;
        color: var(--color-text-secondary);
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

    .sq-checkbox-row {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 14px 16px;
        border: 0.5px solid var(--color-border-tertiary);
        border-radius: var(--border-radius-md);
        background: var(--color-background-secondary);
    }

    .sq-checkbox-row input[type="checkbox"] {
        width: 16px;
        height: 16px;
        cursor: pointer;
    }

    .sq-checkbox-row label {
        font-size: 14px;
        color: var(--color-text-primary);
        margin: 0;
        cursor: pointer;
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
                <input class="sq-input" type="text" id="package_name" name="package_name" placeholder="Default"
                    maxlength="100" value="{$package.name|default:'Default'|escape:'htmlall':'UTF-8'}" required>
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
                    <input class="sq-input" type="number" step="0.01" min="0" id="package_length" name="package_length"
                        placeholder="30.00" value="{$package.length|default:'30.00'|escape:'htmlall':'UTF-8'}" required>
                </div>
                <div class="sq-field">
                    <label class="sq-label" for="package_width">
                        {l s='Larghezza (cm)' mod='spedisciquishipping'}
                        <span style="color:#e74c3c"> *</span>
                    </label>
                    <input class="sq-input" type="number" step="0.01" min="0" id="package_width" name="package_width"
                        placeholder="20.00" value="{$package.width|default:'20.00'|escape:'htmlall':'UTF-8'}" required>
                </div>
            </div>
            <div class="sq-grid" style="margin-top:14px;">
                <div class="sq-field">
                    <label class="sq-label" for="package_height">
                        {l s='Altezza (cm)' mod='spedisciquishipping'}
                        <span style="color:#e74c3c"> *</span>
                    </label>
                    <input class="sq-input" type="number" step="0.01" min="0" id="package_height" name="package_height"
                        placeholder="10.00" value="{$package.height|default:'10.00'|escape:'htmlall':'UTF-8'}" required>
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
                <input class="sq-input" type="number" step="0.001" min="0" id="package_weight" name="package_weight"
                    placeholder="1.000" value="{$package.weight|default:'1.000'|escape:'htmlall':'UTF-8'}" required>
            </div>

            <div class="sq-divider"></div>

            {* DEFAULT *}
            <div class="sq-checkbox-row">
                <input type="checkbox" name="package_is_default" id="package_is_default" value="1"
                    {if $package.is_default}checked{/if}>
                <label for="package_is_default">
                    {l s='Usa come pacco predefinito' mod='spedisciquishipping'}
                </label>
            </div>

        </div>

        <div class="sq-footer">
            <button type="submit" name="submitPackageForm" class="sq-btn">
                <i class="icon-arrow-right"></i>
                {l s='Salva e continua' mod='spedisciquishipping'}
            </button>
        </div>

    </div>
</form>