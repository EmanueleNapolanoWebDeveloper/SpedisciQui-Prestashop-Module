{* layout.tpl *}
<!DOCTYPE html>
<html lang="it">

<head>
    <meta charset="UTF-8">
    <style>
        /* ── NAVBAR ─────────────────────────────── */
        .sq-navbar {
            background-color: #1e7e34;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
        }

        .sq-navbar img {
            height: 38px;
            object-fit: contain;
        }

        .sq-navbar .sq-navbar-title {
            color: #fff;
            font-size: 20px;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* ── STEPPER ─────────────────────────────── */
        .sq-stepper {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0;
            padding: 24px 28px 0;
            background: #f8f9fa;
        }

        .sq-step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            position: relative;
        }

        .sq-step:not(:last-child)::after {
            content: '';
            position: absolute;
            top: 16px;
            left: calc(50% + 16px);
            width: calc(100% - 8px);
            height: 2px;
            background: #dee2e6;
            z-index: 0;
        }

        .sq-step.done::after {
            background: #1e7e34;
        }

        .sq-step-circle {
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: #dee2e6;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 14px;
            z-index: 1;
            position: relative;
        }

        .sq-step.active .sq-step-circle {
            background: #1e7e34;
            color: #fff;
        }

        .sq-step.done .sq-step-circle {
            background: #b8dfc4;
            color: #1e7e34;
        }

        .sq-step-label {
            font-size: 11px;
            color: #6c757d;
            white-space: nowrap;
        }

        .sq-step.active .sq-step-label {
            color: #1e7e34;
            font-weight: 600;
        }

        /* ── MAIN ────────────────────────────────── */
        .sq-main {
            min-height: calc(100vh - 80px);
            background: #f8f9fa;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 40px 20px;
        }

        .sq-content {
            width: 100%;
        }
    </style>
</head>

<body>

    {* NAVBAR *}
    <nav class="sq-navbar">
        {* sostituisci con il tuo logo *}
        {* <img src="{$module_dir}views/img/logo.png" alt="SpedisciQui"> *}
        <span class="sq-navbar-title">🚚 SpedisciQui</span>
    </nav>

    {* STEPPER *}
    {if isset($setup_step)}
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