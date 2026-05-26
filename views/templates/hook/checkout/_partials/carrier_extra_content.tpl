<div class="spedisciqui-extra" id="spq-extra-{$carrier.id|intval}">
    {if $savedCarriers}
        <p class="spq-label">Seleziona il servizio di spedizione</p>
        <div class="spq-carrier-list">
            {foreach from=$savedCarriers item=sc name=loop}
                <label class="spq-carrier-item {if $smarty.foreach.loop.first}spq-selected{/if}">
                    <input type="radio" name="spedisciqui_service[{$carrier.id|intval}]"
                        value="{$sc.carrier_code|escape:'htmlall':'UTF-8'}" data-carrier-id="{$carrier.id|intval}"
                        data-service-code="{$sc.carrier_code|escape:'htmlall':'UTF-8'}"
                        {if $smarty.foreach.loop.first}checked{/if} class="spq-radio">
                    <span class="spq-carrier-name">
                        {$sc.carrier_name|escape:'htmlall':'UTF-8'}
                    </span>
                    <span class="spq-carrier-service">
                        {$sc.carrier_code|escape:'htmlall':'UTF-8'}
                    </span>
                    <span class="spq-carrier-price">
                        {if isset($prices[$sc.carrier_code])}
                            {displayPrice price=$prices[$sc.carrier_code]}
                        {else}
                            N/D
                        {/if}
                    </span>
                </label>
            {/foreach}
        </div>
    {else}
        <p class="spq-empty">Nessun servizio disponibile</p>
    {/if}
</div>