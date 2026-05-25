{if $savedCarriers}
    <div class="spedisciqui-carrier-extra">
        <h4>Corrieri salvati</h4>
        <ul>
        {foreach from=$savedCarriers item=carrier}
            <li>
                {if $carrier.carrier_name}
                    {$carrier.carrier_name}
                {else}
                    Corriere sconosciuto
                {/if}
                {if isset($prices[$carrier.carrier_code])}
                    - {$prices[$carrier.carrier_code]|currency}
                {/if}
            </li>
        {/foreach}
        </ul>
    </div>
{/if}