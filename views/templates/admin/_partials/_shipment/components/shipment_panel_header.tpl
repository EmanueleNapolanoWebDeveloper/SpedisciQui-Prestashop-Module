<div class="page-header-wrap" style="margin-bottom: 20px;">
    <div class="panel" style="padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); margin-bottom: 0;">
        <div class="row">
            <div class="col-md-12">

                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 5px;">
                    <h1 style="margin: 0; font-weight: 600; color: #363a41; display: flex; align-items: center; gap: 10px;">
                        <i class="icon-truck" style="color: #25b9d7;"></i>
                        {l s='SpedisciQui Shipments Control Panel' mod='spedisciquishipping'}
                    </h1>

                    {if isset($shipments) && $shipments|count > 0}
                        <div style="display: flex; align-items: center; gap: 8px;">
                            <span style="display: inline-flex; align-items: center; gap: 6px; font-size: 12px; color: #9aabb8; font-weight: 500;">
                                <span style="width: 7px; height: 7px; background-color: #72c279; border-radius: 50%; display: inline-block;"></span>
                                {l s='Sincronizzato' mod='spedisciquishipping'}
                            </span>
                            <span class="label label-info" style="font-size: 13px; font-weight: 600; padding: 3px 10px; border-radius: 12px; background-color: #e6f7fa; color: #1e7e94; border: 1px solid #bce8f1;">
                                {$totalShipments} {l s='Spedizioni' mod='spedisciquishipping'}
                            </span>
                        </div>
                    {/if}
                </div>

                <p class="text-muted" style="font-size: 14px; margin-bottom: 15px;">
                    {l s='Benvenuto nel pannello di monitoraggio logistico. Da questa sezione puoi controllare lo stato delle spedizioni generate, generare nuove lettere di vettura per gli ordini pronti ed elaborare i colli verso la piattaforma SpedisciQui.' mod='spedisciquishipping'}
                </p>

                <hr style="border-top: 1px solid #edf0f5; margin: 15px 0;" />

                {* Box Istruzioni Gestione Spedizioni *}
                <div class="alert alert-info" style="margin-bottom: 0; background-color: #f4fafd; border-left: 4px solid #25b9d7;">
                    <h4 style="margin-top: 0; font-weight: 600; color: #1e7e94;">
                        <i class="icon-info-sign"></i> {l s='Istruzioni per l\'evasione degli ordini:' mod='spedisciquishipping'}
                    </h4>
                    <ul style="margin-bottom: 0; padding-left: 20px; line-height: 1.6;">
                        <li><strong>1. {l s='In Attesa:' mod='spedisciquishipping'}</strong> {l s='Trova un ordine nello stato "In attesa" e clicca su "Crea Spedizione" per rivedere i dettagli dei pacchi.' mod='spedisciquishipping'}</li>
                        <li><strong>2. {l s='Genera Lettera di Vettura:' mod='spedisciquishipping'}</strong> {l s='Invia i dati alle API di SpedisciQui per congelare la tariffa ed ottenere l\'assegnazione del tracking number.' mod='spedisciquishipping'}</li>
                        <li><strong>3. {l s='Scarica Label:' mod='spedisciquishipping'}</strong> {l s='Una volta elaborato, scarica il PDF del foglio di viaggio direttamente dalla riga dell\'ordine per applicarlo sul pacco.' mod='spedisciquishipping'}</li>
                    </ul>
                </div>

            </div>
        </div>
    </div>
</div>