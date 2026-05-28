{* dashboard_layout.tpl *}
<div class="panel">
    <div class="panel-heading">
        <i class="icon-truck"></i>
        {l s='SpedisciQui — Dashboard' mod='spedisciquishipping'}
        {if $user && isset($user.user.name)}
            <span class="badge" style="margin-left:10px; background:#1e7e34;">
                {$user.user.name|escape:'htmlall':'UTF-8'}
            </span>
        {/if}
    </div>
    <div class="panel-body" style="padding:0;">

        {* ── SE c'è una view contestuale (es. config tariffe) la mostra direttamente *}
        {if isset($content) && $content !== ''}

            <div style="padding:24px;">
                {$content nofilter}
            </div>

            {* ── ALTRIMENTI mostra la dashboard normale con i tab *}
        {else}

            <ul class="nav nav-tabs" style="padding:0 20px; background:#f8f9fa; border-bottom:2px solid #1e7e34;">
                <li class="active">
                    <a href="#tab-orders" data-toggle="tab">
                        <i class="icon-truck"></i>
                        {l s='Ordini' mod='spedisciquishipping'}
                    </a>
                </li>
                <li>
                    <a href="#tab-carriers" data-toggle="tab">
                        <i class="icon-truck"></i>
                        {l s='Corrieri' mod='spedisciquishipping'}
                        {if $carriers}
                            <span class="badge" style="background:#1e7e34;">{$carriers|count}</span>
                        {/if}
                    </a>
                </li>
                <li>
                    <a href="#tab-settings" data-toggle="tab">
                        <i class="icon-cogs"></i>
                        {l s='Impostazioni' mod='spedisciquishipping'}
                    </a>
                </li>
            </ul>

            <div class="tab-content" style="padding:24px;">
                <div class="tab-pane active" id="tab-carriers">
                    {include file="./_partials/order_panel.tpl"}
                </div>
                <div class="tab-pane active" id="tab-carriers">
                    {include file="./_partials/carrier_panel.tpl"}
                </div>
                <div class="tab-pane" id="tab-settings">
                    {include file="./_partials/settings_panel.tpl"}
                </div>
            </div>

        {/if}

    </div>
</div>