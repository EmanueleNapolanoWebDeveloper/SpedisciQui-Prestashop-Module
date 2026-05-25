{* views/templates/admin/configure.tpl *}

<div class="panel">
    <div class="panel-heading" style="display:flex; align-items:center; justify-content:space-between;">
        <div style="display:flex; align-items:center; gap:12px;">
            {* sostituisci con <img> quando hai il logo *}
            <div
                style="width:36px; height:36px; background:var(--color-background-secondary); border:0.5px solid var(--color-border-tertiary); border-radius:8px; display:flex; align-items:center; justify-content:center;">
                <i class="icon-truck"></i>
            </div>
            <span style="font-size:16px; font-weight:500;">SpedisciQui Shipping</span>
        </div>
        <a href="https://www.spedisciqui.it/login" target="_blank" class="btn btn-default btn-sm">
            <i class="icon-external-link"></i>
            {l s='Iscriviti alla piattaforma' mod='spedisciquishipping'}
        </a>
    </div>

    <div class="panel-body">
        {$content}
    </div>
</div>