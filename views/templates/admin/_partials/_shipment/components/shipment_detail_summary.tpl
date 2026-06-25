{**
 * Riepilogo costi — incluso dentro il form principale
 *}
<div class="sr-summary">
    <div class="sr-sum-row">
        <span>{l s='Costo base spedizione' mod='spedisciquishipping'}</span>
        <b>€ {$vm.shipment.shipping_cost|string_format:'%.2f'}</b>
    </div>

    <div class="sr-sum-row sr-sum-hidden" id="sr-sum-row-insurance">
        <span>{l s='Assicurazione' mod='spedisciquishipping'}</span>
        <b id="sr-sum-val-insurance">€ 0.00</b>
    </div>

    {if isset($vm.options.available) && $vm.options.available|count > 0}
        {foreach $vm.options.available as $opt}
            {if $opt.enabled}
                <div class="sr-sum-row sr-sum-hidden" id="sr-sum-row-{$opt.type|escape:'html':'UTF-8'}">
                    <span>{$opt.label|escape:'html':'UTF-8'}</span>
                    <b id="sr-sum-val-{$opt.type|escape:'html':'UTF-8'}">€ 0.00</b>
                </div>
            {/if}
        {/foreach}
    {/if}

    <hr class="sr-sum-divider">
    <div class="sr-sum-total">
        <span>{l s='Totale stimato' mod='spedisciquishipping'}</span>
        <span id="sr-grand-total">€ {$vm.shipment.shipping_cost|string_format:'%.2f'}</span>
    </div>
</div>