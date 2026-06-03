{* ── Modale conferma Crea Spedizione ── *}


<div class="sq-modal-backdrop" id="sq-shipment-modal">
    <div class="sq-modal" role="dialog" aria-modal="true" aria-labelledby="sq-modal-title">
        <p class="sq-modal-title" id="sq-modal-title">
            <i class="icon-truck"></i>
            {l s='Conferma creazione spedizione' mod='spedisciquishipping'}
        </p>
        <p style="font-size:13px; color:#5a6a7a; margin:0 0 14px;">
            {l s='Cliccando conferma, il sistema eseguirà automaticamente:' mod='spedisciquishipping'}
        </p>
        <ul class="sq-modal-steps">
            <li class="sq-modal-step">
                <span class="sq-step-num">1</span>
                <span>{l s='Verifica dati ordine e indirizzo di consegna' mod='spedisciquishipping'}</span>
            </li>
            <li class="sq-modal-step">
                <span class="sq-step-num">2</span>
                <span>{l s='Recupero peso pacco, corriere e codice servizio' mod='spedisciquishipping'}</span>
            </li>
            <li class="sq-modal-step">
                <span class="sq-step-num">3</span>
                <span>{l s='Calcolo tariffa di spedizione applicabile' mod='spedisciquishipping'}</span>
            </li>
            <li class="sq-modal-step">
                <span class="sq-step-num">4</span>
                <span>{l s='Invio richiesta all\'API del corriere selezionato' mod='spedisciquishipping'}</span>
            </li>
            <li class="sq-modal-step">
                <span class="sq-step-num">5</span>
                <span>{l s='Generazione numero di tracking e salvataggio spedizione' mod='spedisciquishipping'}</span>
            </li>
        </ul>
        <div class="sq-modal-actions">
            <button type="button" class="sq-modal-close-btn" onclick="sqCloseModal()">
                {l s='Annulla' mod='spedisciquishipping'}
            </button>
            <button type="button" class="sq-modal-confirm" id="sq-modal-confirm-btn">
                <i class="icon-truck"></i>
                {l s='Conferma e crea' mod='spedisciquishipping'}
            </button>
        </div>
    </div>
</div>