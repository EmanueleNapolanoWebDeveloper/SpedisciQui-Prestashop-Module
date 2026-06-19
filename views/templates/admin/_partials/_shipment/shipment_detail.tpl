{**
* SpedisciQui — Shipment Review
* Template: views/templates/admin/_partials/_orders/shipment_review.tpl
**}
<div class="sr-page">

    <!-- Pulsante di ritorno standardizzato -->
    <div style="margin-bottom: 20px;">
        <a href="{$vm.form.back_url|escape:'html':'UTF-8'}" class="sr-back" style="color: #1a6fc4; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center; gap: 6px; font-size: 13px;">
            <i class="icon-arrow-left"></i>
            {l s='Torna alla lista spedizioni' mod='spedisciquishipping'}
        </a>
    </div>

    <!-- Struttura principale in Panel PrestaShop Enterprise -->
    <div class="panel" style="border: 1px solid #d3d8db; border-radius: 4px; padding: 20px; background-color: #fff;">

        <!-- HEADER DEL COMPONENTE: Pannello di Controllo Validazione -->
        <div class="page-header-wrap" style="margin-bottom: 25px;">
            <div class="panel" style="padding: 20px; border-radius: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.05); border: 1px solid #edf1f5; background-color: #fcfdfe; margin-bottom: 0;">
                <div class="row">
                    <div class="col-md-12">
                        <h1 style="margin-top: 0; font-size: 20px; font-weight: 600; color: #363a41; display: inline-flex; align-items: center; gap: 10px; letter-spacing: -0.5px;">
                            <i class="icon-check-sign" style="color: #25b9d7;"></i>
                            {l s='Pannello Validazione e Revisione Spedizione' mod='spedisciquishipping'}
                            <span style="color: #8898a5; font-weight: 400; font-size: 16px;">#{$vm.shipment.id_shipment|intval}</span>
                        </h1>
                        <p class="text-muted" style="font-size: 13px; color: #6c757d; margin-top: 5px; margin-bottom: 15px;">
                            {l s='Benvenuto nella schermata di audit pre-invio. Questa interfaccia centralizza i dati logistici del vettore, le informazioni fiscali dell\'ordine e l\'anagrafica di consegna per consentire una verifica di conformità prima dell\'emissione della lettera di vettura.' mod='spedisciquishipping'}
                        </p>

                        <hr style="border-top: 1px solid #edf0f5; margin: 15px 0;" />

                        <!-- Alert Istruzioni di Processo -->
                        <div class="alert alert-info" style="margin-bottom: 0; background-color: #f4fafd; border-left: 4px solid #25b9d7; padding: 15px; border-radius: 0 4px 4px 0;">
                            <h4 style="margin-top: 0; font-size: 14px; font-weight: 600; color: #1e7e94; display: inline-flex; align-items: center; gap: 6px;">
                                <i class="icon-info-sign"></i> {l s='Protocollo operativo per l\'emissione del documento:' mod='spedisciquishipping'}
                            </h4>
                            <ul style="margin-bottom: 0; padding-left: 18px; line-height: 1.6; color: #334155; font-size: 13px;">
                                <li><strong>1. {l s='Audit Anagrafiche:' mod='spedisciquishipping'}</strong> {l s='Ispeziona le schede riassuntive "Informazioni Spedizione", "Dettagli Ordine" e "Informazioni di Consegna".' mod='spedisciquishipping'}</li>
                                <li><strong>2. {l s='Opzioni Accessorie:' mod='spedisciquishipping'}</strong> {l s='Seleziona eventuali servizi integrativi (Assicurazione del massimale, opzioni di consegna al piano o su appuntamento).' mod='spedisciquishipping'}</li>
                                <li><strong>3. {l s='Consolidamento Tariffario:' mod='spedisciquishipping'}</strong> {l s='Controlla il riepilogo economico del nolo e clicca su "Genera Lettera di Vettura". Le operazioni non sono reversibili.' mod='spedisciquishipping'}</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Gestione Errori Piattaforma API -->
        {if isset($smarty.get.sq_error) && $smarty.get.sq_error}
            <div class="alert alert-danger" style="margin-bottom: 20px; border-left: 4px solid #dd2c00; background-color: #fff5f5; color: #b71c1c; padding: 15px; border-radius: 0 4px 4px 0;">
                <h4 style="margin-top: 0; font-size: 14px; font-weight: 600; color: #b71c1c;"><i class="icon-warning-sign"></i> {l s='Errore di Sincronizzazione API' mod='spedisciquishipping'}</h4>
                <span style="font-size: 13px;">{$smarty.get.sq_error|escape:'html':'UTF-8'}</span>
            </div>
        {/if}

        {* ── Dati JS — Richiesto dal backend per logiche di calcolo asincrono ── *}
       <script>
            window.SQ_Review = {
                order_id:      {$sq_order_id|intval},
                token:         '{$sq_token|escape:'html':'UTF-8'}',
                currency_sign: '{$sq_currency_sign|escape:'html':'UTF-8'}',
                carriers:      {$sq_carriers_json|default:'[]'},
                ajax_url:      '{$sq_ajax_url|escape:'html':'UTF-8'}'
            };
        </script>

        <!-- Blocco Sola Lettura: Griglie Riepilogo Dati (Stile Enterprise con separatori rigidi) -->
        <div class="shipment-detail-cards-section" style="margin-bottom: 25px;">
            {include
                file='module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_detail_info_card.tpl'
            }
        </div>

        <hr style="border-top: 1px solid #edf1f5; margin: 25px 0;" />

        <!-- Form Operativo Principale per Servizi Opzionali e Submit -->
        <form method="POST" action="{$vm.form.action_url|escape:'html':'UTF-8'}" id="sq-review-form" onsubmit="return sqReviewSubmit(event)">

            <input type="hidden" name="createShipment" value="1">
            <input type="hidden" name="id_shipment" value="{$vm.form.id_shipment|intval}">

            <div class="form-components-wrapper" style="display: flex; flex-direction: column; gap: 20px;">

                <!-- Componente Valore Assicurato Mercie -->
                <div class="insurance-section">
                    {include
                        file='module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_detail_insurance.tpl'
                    }
                </div>

                <!-- Componente Servizi Accessori (Consegna Sabato, Piano, Appuntamento) -->
                <div class="options-section">
                    {include
                        file='module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_detail_options.tpl'
                    }
                </div>

                <!-- Componente Quadro Economico Consolidato -->
                <div class="summary-section">
                    {include
                        file='module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_detail_summary.tpl'
                    }
                </div>

                <!-- Componente Cta di Invio e Annullamento Processo -->
                <div class="actions-section" style="margin-top: 10px;">
                    {include
                        file='module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_detail_actions.tpl'
                    }
                </div>

            </div>
        </form>

    </div>
</div>