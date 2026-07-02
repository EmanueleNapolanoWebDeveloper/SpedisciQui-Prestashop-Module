<div class="sq-shipment-panel">

    {* COLONNA SINISTRA - Richiesta Reso *}
    {if $vm.shipment.status === 'label_created'}
        <div class="sq-box sq-box-refund">
            <div class="sq-box-header">
                <i class="icon-reply"></i>
                <h3>{l s='Richiesta Reso' mod='spedisciquishipping'}</h3>
            </div>
            <div class="sq-box-body">
                <p class="sq-box-hint">{l s='Hai ricevuto un reso per questa spedizione? Avvia la richiesta.' mod='spedisciquishipping'}</p>
                <form action="{$back_url}" method="post" class="sq-inline-form">
                    <input type="hidden" name="token" value="{$token}" />
                    <input type="hidden" name="id_shipment" value="{$vm.form.id_shipment|intval}" />
                    <button type="submit" name="submitRefundCreation" class="sq-btn sq-btn-danger">
                        <i class="icon-undo"></i>
                        {l s='Richiedi Reso' mod='spedisciquishipping'}
                    </button>
                </form>
            </div>
        </div>
    {/if}

    {* COLONNA DESTRA - Avanzamento Spedizione *}
    <div class="sq-box sq-box-progress">
        <div class="sq-box-header">
            <i class="icon-truck"></i>
            <h3>{l s='Avanzamento Spedizione' mod='spedisciquishipping'}</h3>
        </div>
        <div class="sq-box-body">

            {if $vm.shipment.status === 'pending'}
                <p class="sq-box-hint">{l s='La spedizione è pronta per essere creata presso il vettore.' mod='spedisciquishipping'}</p>
                <form action="{$back_url}" method="post">
                    <input type="hidden" name="token" value="{$token}" />
                    <input type="hidden" name="id_shipment" value="{$vm.form.id_shipment|intval}" />
                    <button type="submit" name="submitShipmentCreation" class="sq-btn sq-btn-primary">
                        <span class="sq-spinner"></span>
                        <i class="icon-plus-circle"></i>
                        {l s='Crea Richiesta di Spedizione' mod='spedisciquishipping'}
                    </button>
                </form>

            {elseif $vm.shipment.status === 'request_send'}
                <p class="sq-box-hint">{l s='Richiesta inviata al vettore, in attesa di conferma.' mod='spedisciquishipping'}</p>
                <form action="{$back_url}" method="post">
                    <input type="hidden" name="token" value="{$token}" />
                    <input type="hidden" name="id_shipment" value="{$vm.form.id_shipment|intval}" />
                    <button type="submit" name="fetchShipmentLabel" class="sq-btn sq-btn-primary">
                        <i class="icon-download"></i>
                        {l s='Scarica Conferma Richiesta' mod='spedisciquishipping'}
                    </button>
                </form>

            {elseif $vm.shipment.status === 'label_created'}
                <p class="sq-box-hint">{l s='Etichetta generata e pronta per la stampa.' mod='spedisciquishipping'}</p>
                <div class="sq-action-group">
                    <a href="{$vm.shipment.label_url}" target="_blank" class="sq-btn sq-btn-secondary">
                        <i class="icon-eye"></i>
                        {l s='Leggi PDF' mod='spedisciquishipping'}
                    </a>
                    <a href="{$vm.shipment.label_url}" download class="sq-btn sq-btn-primary">
                        <i class="icon-download"></i>
                        {l s='Scarica PDF' mod='spedisciquishipping'}
                    </a>
                </div>
            {/if}

        </div>
    </div>

</div>