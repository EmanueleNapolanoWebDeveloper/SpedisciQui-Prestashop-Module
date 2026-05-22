{* package_config.tpl *}

<div style="display:flex; justify-content:center; align-items:center; min-height:400px;">
    <form method="post" action="{$action}" style="width:400px; padding:20px; border:1px solid #ddd; border-radius:10px;">

        <h3 style="text-align:center;">Dati Pacco &nbsp;📦</h3>

        <label>Peso (kg)</label>
        <input type="number" step="0.01" name="package_weight" class="form-control" value="{$package.weight|escape:'htmlall':'UTF-8'}" required>

        <label>Altezza (cm)</label>
        <input type="number" name="package_height" class="form-control" value="{$package.height|escape:'htmlall':'UTF-8'}" required>

        <label>Larghezza (cm)</label>
        <input type="number" name="package_width" class="form-control" value="{$package.width|escape:'htmlall':'UTF-8'}" required>

        <label>Profondità (cm)</label>
        <input type="number" name="package_depth" class="form-control" value="{$package.depth|escape:'htmlall':'UTF-8'}" required>

        <br>

        <button type="submit" name="submitPackageForm" class="btn btn-primary" style="width:100%;">
            Salva Pacco
        </button>

    </form>
</div>