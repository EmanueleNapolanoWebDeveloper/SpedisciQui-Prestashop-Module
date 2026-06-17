{* layout.tpl *}
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
</head>

<body>

    {* NAVBAR *}
    <nav class="sq-navbar">
        {* sostituisci con il tuo logo *}
        {* <img src="{$module_dir}views/img/logo.png" alt="SpedisciQui"> *}
        <span class="sq-navbar-title">🚚 SpedisciQui</span>
    </nav>

    {* STEPPER *}
    {if isset($setupStep)}
        {include file='module:spedisciquishipping/views/templates/admin/_partials/_components/stepper_initial_confi.tpl'}
    {/if}

    {* MAIN CONTENT *}
    <main class="sq-main">
        <div class="sq-content">
            {$content nofilter}
        </div>
    </main>

</body>

</html>