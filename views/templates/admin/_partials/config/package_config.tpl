{* package_config.tpl *}
<div style="display:flex; justify-content:center; align-items:center; min-height:400px;">
    <form method="post" action="{$action}"
        style="width:460px; padding:28px; border:1px solid #ddd; border-radius:10px;">
        <h3 style="text-align:center;">Dati Pacco &nbsp;📦</h3>

        <label>Nome profilo</label>
        <input type="text" name="package_name" class="form-control" maxlength="100"
            value="{$package.name|default:'Default'|escape:'htmlall':'UTF-8'}" required>

        <label style="margin-top:14px;">Peso (kg)</label>
        <input type="number" step="0.001" min="0" name="package_weight" class="form-control"
            value="{$package.weight|default:'1.000'|escape:'htmlall':'UTF-8'}" required>

        <label style="margin-top:14px;">Lunghezza (cm)</label>
        <input type="number" step="0.01" min="0" name="package_length" class="form-control"
            value="{$package.length|default:'30.00'|escape:'htmlall':'UTF-8'}" required>

        <label style="margin-top:14px;">Larghezza (cm)</label>
        <input type="number" step="0.01" min="0" name="package_width" class="form-control"
            value="{$package.width|default:'20.00'|escape:'htmlall':'UTF-8'}" required>

        <label style="margin-top:14px;">Altezza (cm)</label>
        <input type="number" step="0.01" min="0" name="package_height" class="form-control"
            value="{$package.height|default:'10.00'|escape:'htmlall':'UTF-8'}" required>

        <div style="margin-top:16px; display:flex; align-items:center; gap:10px;">
            <input type="checkbox" name="package_is_default" id="package_is_default" value="1"
                {if $package.is_default}checked{/if}>
            <label for="package_is_default" style="margin:0;">
                Usa come pacco predefinito
            </label>
        </div>

        <br>
        <button type="submit" name="submitPackageForm" class="btn btn-primary" style="width:100%;">
            💾 Salva Pacco
        </button>
    </form>
</div>