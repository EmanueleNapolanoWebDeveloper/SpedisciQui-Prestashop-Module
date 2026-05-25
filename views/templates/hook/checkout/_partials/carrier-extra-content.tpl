<div class="spedisciqui-extra">

    <h4>
        Servizi disponibili
    </h4>

    {if $savedCarriers}

        <div class="spq-carrier-list">

            {foreach from=$savedCarriers item=sc}

                <label class="spq-carrier-item">

                    <input type="radio" name="spedisciqui_service" value="{$sc.carrier_code|escape:'htmlall':'UTF-8'}">

                    <strong>
                        {$sc.carrier_name|escape:'htmlall':'UTF-8'}
                    </strong>

                    {if isset($prices[$sc.carrier_code])}
                        - € {$prices[$sc.carrier_code]|floatval}
                    {/if}

                </label>

                <br>

            {/foreach}

        </div>

    {else}

        <p>Nessun servizio disponibile</p>

    {/if}

</div>