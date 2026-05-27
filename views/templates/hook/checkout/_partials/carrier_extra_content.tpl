<div class="spedisciqui-extra" id="spq-extra-{$carrier.id|intval}">



    {* Prezzo spedizione *}
    {if $spqCarrierPrice !== null}
        <span class="spq-value">{$spqCarrierPrice}</span>
    {else}
        <p>Non passa nulla</p>
    {/if}

    {* Assicurazione *}
    {if $spqInsurancePrice !== null}
        <div class="spq-row">
            <span class="spq-label">Assicurazione:</span>
            <span class="spq-value">{$spqInsurancePrice}</span>
        </div>

        <div class="spq-row spq-insurance-wrap">
            <label class="spq-checkbox-label">
                <input type="checkbox" name="spedisciqui_insurance[{$carrier.id|intval}]" value="1"
                    data-carrier-id="{$carrier.id|intval}" data-insurance-price="{$spqInsurancePrice}"
                    class="spq-insurance-check" {if $spqInsuranceRequired}checked disabled{/if}>
                {if $spqInsuranceRequired}
                    Assicurazione inclusa (obbligatoria)
                {else}
                    Aggiungi assicurazione spedizione
                {/if}
            </label>
        </div>
    {/if}

    {* Campo hidden con il codice servizio selezionato *}
    <input type="hidden" name="spedisciqui_service[{$carrier.id|intval}]"
        value="{$spqCarrierCode|escape:'htmlall':'UTF-8'}">

</div>