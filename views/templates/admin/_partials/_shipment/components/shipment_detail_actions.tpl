<div class="sr-actions">
    <a href="{$vm.form.back_url|escape:'html':'UTF-8'}" class="sr-btn-back">
        <i class="icon-arrow-left"></i>
        {l s='Annulla' mod='spedisciquishipping'}
    </a>

    {if $vm.shipment.status === 'pending'}
        <form action="{$back_url}" method="post">
            <input type="hidden" name="token" value="{$token}" />
            <input type="hidden" name="id_shipment" value="{$vm.form.id_shipment|intval}" />
            <button type="submit" name="submitShipmentCreation" class="sr-btn-confirm">
                <span class="sr-spinner"></span>
                {l s='Conferma Creazione' mod='spedisciquishipping'}
            </button>
        </form>

    {elseif $vm.shipment.status === 'request_send'}
        <div class="sq-action-group">
            {* <form action="{$back_url}" method="post" class="sq-inline-form">
                <input type="hidden" name="token" value="{$token}" />
                <input type="hidden" name="id_shipment" value="{$vm.form.id_shipment|intval}" />
                <button type="submit" name="cancelShipment" class="sq-btn-cancel-shipment" onclick="return confirm('{l s='Confermi di voler annullare questa spedizione?' mod='spedisciquishipping' js=1}')">
                    <i class="icon-remove"></i>
                    {l s='Annulla' mod='spedisciquishipping'}
                </button>
            </form> *}

            <form action="{$back_url}" method="post" class="sq-inline-form">
                <input type="hidden" name="token" value="{$token}" />
                <input type="hidden" name="id_shipment" value="{$vm.form.id_shipment|intval}" />
                <button type="submit" name="fetchShipmentLabel" class="sq-btn-fetch-label">
                    <i class="icon-download"></i>
                    {l s='Scarica Label' mod='spedisciquishipping'}
                </button>
            </form>
        </div>

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