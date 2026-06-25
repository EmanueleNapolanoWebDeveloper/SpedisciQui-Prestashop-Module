<div class="spq-extra" id="spq-extra-{$carrier.id|intval}">

    {* Prezzo spedizione *}
    {if $spqCarrierPrice !== null}
        <div class="spq-row">
            <span class="spq-row-label">Prezzo di Spedizione</span>
            <span class="spq-row-value">{$spqCarrierPrice}</span>
        </div>
    {/if}

    {* Assicurazione *}
    {if $spqInsurancePrice !== null}
        <div class="spq-insurance-row">
            <div class="spq-insurance-left">
                <span class="spq-insurance-title">Assicurazione</span>
                <span class="spq-insurance-price">{$spqInsurancePrice}</span>
            </div>
            <div class="spq-insurance-right">
                <label class="spq-checkbox-label">
                    <input type="checkbox" name="spedisciqui_insurance[{$carrier.id|intval}]" value="1"
                        data-carrier-id="{$carrier.id|intval}" data-insurance-price="{$spqInsurancePrice}"
                        class="spq-insurance-check" {if $spqInsuranceRequired}checked disabled{/if}>
                    {if $spqInsuranceRequired}
                        Assicurazione obbligatoria
                    {else}
                        Assicura pacco
                    {/if}
                </label>
            </div>
        </div>
    {/if}

    <input type="hidden" name="spedisciqui_service[{$carrier.id|intval}]"
        value="{$spqCarrierCode|escape:'htmlall':'UTF-8'}">
</div>