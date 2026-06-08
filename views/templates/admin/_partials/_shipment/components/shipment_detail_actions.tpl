<div class="sr-actions">

    <a href="{$vm.form.back_url|escape:'html':'UTF-8'}" class="sr-btn-back">
        <i class="icon-arrow-left"></i>
        {l s='Annulla' mod='spedisciquishipping'}
    </a>

    {if $vm.shipment.status === 'pending'}

        <button type="submit" class="sr-btn-confirm" id="sr-submit-btn">
            <i class="icon-truck" id="sr-submit-icon"></i>
            <span id="sr-submit-label">
                {l s='Conferma e crea spedizione' mod='spedisciquishipping'}
            </span>
            <div class="sr-spinner" id="sr-spinner"></div>
        </button>

    {else}

        {* MOSTRA PDF *}
        <a href="{$vm.shipment.label_url}" target="_blank" class="sq-btn-show-pdf">
            <i class="icon-eye"></i>
            {l s='Visualizza etichetta' mod='spedisciquishipping'}
        </a>

        {* DOWNLOAD PDF *}
        <a href="{$vm.shipment.label_url}" download class="sq-btn-download-pdf">
            <i class="icon-download"></i>
            {l s='Scarica PDF' mod='spedisciquishipping'}
        </a>

    {/if}

</div>