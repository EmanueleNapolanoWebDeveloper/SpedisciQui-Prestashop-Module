{* sender_address.tpl *}

<div style="display:flex; justify-content:center; align-items:center; min-height:500px;">

    <form method="post" action="{$action}"
        style="width:450px; padding:20px; border:1px solid #ddd; border-radius:10px; background:#fff;">

        <h3 style="text-align:center; margin-bottom:20px;">
            Indirizzo Mittente &nbsp;🏠
        </h3>

        <div class="form-group">
            <label>Nome</label>
            <input type="text" name="sender_name" class="form-control" value="{$sender.name|escape:'htmlall':'UTF-8'}" required>
        </div>

        <div class="form-group">
            <label>Cognome</label>
            <input type="text" name="sender_surname" class="form-control" value="{$sender.surname|escape:'htmlall':'UTF-8'}" required>
        </div>

        <div class="form-group">
            <label>Telefono</label>
            <input type="text" name="sender_phone" class="form-control" value="{$sender.phone|escape:'htmlall':'UTF-8'}">
        </div>

        <div class="form-group">
            <label>Indirizzo</label>
            <input type="text" name="sender_address" class="form-control" value="{$sender.address|escape:'htmlall':'UTF-8'}" required>
        </div>

        <div class="form-group">
            <label>Città</label>
            <input type="text" name="sender_city" class="form-control" value="{$sender.city|escape:'htmlall':'UTF-8'}" required>
        </div>

        <div class="form-group">
            <label>CAP</label>
            <input type="text" name="sender_zip" class="form-control" value="{$sender.zip|escape:'htmlall':'UTF-8'}" required>
        </div>

        <div class="form-group">
            <label>Paese (codice ISO 2)</label>
            <input type="text" name="sender_country" class="form-control" value="{$sender.country|escape:'htmlall':'UTF-8'|default:'IT'}" maxlength="2" placeholder="es: IT">
            <small class="text-muted">Usa il codice ISO 3166-1 alpha-2 (es. IT, FR, DE)</small>
        </div>

        <div class="form-group">
            <label>Provincia</label>
            <input type="text" name="sender_prov" class="form-control" value="{$sender.prov|escape:'htmlall':'UTF-8'}" maxlength="5" required>
        </div>

        <br>

        <button type="submit" name="submitSenderForm" class="btn btn-success" style="width:100%;">

            Salva Mittente
        </button>

    </form>

</div>
