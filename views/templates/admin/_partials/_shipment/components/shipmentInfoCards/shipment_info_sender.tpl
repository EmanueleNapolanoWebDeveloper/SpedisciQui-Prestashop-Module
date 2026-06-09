<div class="sr-card">
    <p class="sr-card-title">
        <i class="icon-user"></i>
        {l s='Destinatario' mod='spedisciquishipping'}
    </p>
    <p class="sr-address-name">{$vm.recipient.full_name|escape:'html':'UTF-8'}</p>
    <p class="sr-address-line">{$vm.recipient.address1|escape:'html':'UTF-8'}</p>
    {if $vm.recipient.address2}
        <p class="sr-address-line">{$vm.recipient.address2|escape:'html':'UTF-8'}</p>
    {/if}
    <p class="sr-address-line">
        {$vm.recipient.postcode|escape:'html':'UTF-8'}
        {$vm.recipient.city|escape:'html':'UTF-8'}
        {if $vm.recipient.province}
            ({$vm.recipient.province|escape:'html':'UTF-8'})
        {/if}
    </p>
    <p class="sr-address-line">{$vm.recipient.country|escape:'html':'UTF-8'}</p>
    {if $vm.recipient.phone}
        <hr class="sr-divider">
        <p class="sr-address-phone">
            <i class="icon-phone"></i>
            {$vm.recipient.phone|escape:'html':'UTF-8'}
        </p>
    {/if}
</div>