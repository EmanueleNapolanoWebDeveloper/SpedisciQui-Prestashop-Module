{* partials/carriers_panel.tpl *}



<div class="panel" style="border: 1px solid #d3d8db; border-radius: 4px; padding: 20px;">

    <div class="page-header-wrap" style="margin-bottom: 20px;">
        <div class="panel" style="padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.1);">
            <div class="row">
                <div class="col-md-12">
                    <h1 style="margin-top: 0; font-weight: 600; color: #363a41; display: flex; align-items: center; gap: 10px;">
                        <i class="icon-puzzle-piece" style="color: #25b9d7;"></i>
                        {l s='SpedisciQui Shipping Control Panel' mod='spedisciquishipping'}
                    </h1>
                    <p class="text-muted" style="font-size: 14px; margin-bottom: 15px;">
                        {l s='Benvenuto nel pannello di gestione dei corrieri. Da qui puoi sincronizzare i vettori della piattaforma SpedisciQui, attivarli sul tuo e-commerce PrestaShop e configurare le relative fasce di peso e tariffe.' mod='spedisciquishipping'}
                    </p>

                    <hr style="border-top: 1px solid #edf0f5; margin: 15px 0;" />

                    <div class="alert alert-info" style="margin-bottom: 0; background-color: #f4fafd; border-left: 4px solid #25b9d7;">
                        <h4 style="margin-top: 0; font-weight: 600; color: #1e7e94;">
                            <i class="icon-info-sign"></i> {l s='Istruzioni per la configurazione:' mod='spedisciquishipping'}
                        </h4>
                        <ul style="margin-bottom: 0; padding-left: 20px; line-height: 1.6;">
                            <li><strong>1. {l s='Trova un Corriere:' mod='spedisciquishipping'}</strong> {l s='Scorri la lista dei corrieri disponibili in piattaforma in fondo alla pagina.' mod='spedisciquishipping'}</li>
                            <li><strong>2. {l s='Attiva il Vettore:' mod='spedisciquishipping'}</strong> {l s='Clicca sul pulsante "Aggiungi" per importarlo nativamente su PrestaShop.' mod='spedisciquishipping'}</li>
                            <li><strong>3. {l s='Imposta le Tariffe:' mod='spedisciquishipping'}</strong> {l s='Nel box dei corrieri attivi, clicca su "Modifica Tariffe" per definire i costi di spedizione in base alle fasce di peso (kg).' mod='spedisciquishipping'}</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="active-carriers-section" style="margin-bottom: 30px;">
        {include
            file="module:spedisciquishipping/views/templates/admin/_partials/_carrier/components/carrier_active_dash.tpl"
            savedCarriers=$savedCarriers
            action=(isset($action))
            ?
            $action
            :
            $formAction
            module_action_url=(isset($module_action_url))
            ?
            $module_action_url
            :
            $formAction
            module_name=(isset($module_name))
            ?
            $module_name
            :
            'spedisciquishipping'
        }
    </div>

    <hr style="border-top: 1px solid #edf0f5; margin: 30px 0;" />

    <div class="available-carriers-section">
        {include
            file="module:spedisciquishipping/views/templates/admin/_partials/_carrier/components/carrier_list_dash.tpl"
            carriers=$carriers
            savedCodes=$savedCodes
            action=(isset($formAction))
            ?
            $formAction
            :
            $action
        }
    </div>

</div>