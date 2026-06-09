{**
 * Componente: Assicurazione
 *}

{if $vm.shipment.status === 'pending'}

    <div class="sr-card" style="margin-bottom:12px;" id="sr-insurance-block">
        <p class="sr-card-title">
            <i class="icon-shield"></i>
            {l s='Assicurazione' mod='spedisciquishipping'}
        </p>
        <div style="display:flex;align-items:flex-start;gap:12px;">
            <input type="checkbox" id="sq-insurance-toggle" name="insurance_enabled" value="1"
                style="width:16px;height:16px;margin-top:3px;cursor:pointer;flex-shrink:0;" onchange="sqToggleInsurance()">
            <div>
                <label for="sq-insurance-toggle"
                    style="font-size:13px;font-weight:700;color:#1a2535;cursor:pointer;display:block;margin-bottom:3px;">
                    {l s='Aggiungi assicurazione alla spedizione' mod='spedisciquishipping'}
                </label>
                <p style="font-size:12px;color:#7a8a9a;margin:0;line-height:1.5;">
                    {l s='Copre il valore dichiarato della merce in caso di smarrimento o danneggiamento.' mod='spedisciquishipping'}
                </p>
            </div>
        </div>

        {* IMPORTO VALORE ASSICURATIVO *}
        {assign var="insuranceMax" value=$vm.order.total_paid_raw|floatval}
        {assign var="insuranceVal" value=$vm.order.total_paid_raw|floatval}
        {* Il valore iniziale non può superare il max *}
        {if $insuranceVal > $insuranceMax}
            {assign var="insuranceVal" value=$insuranceMax}
        {/if}

        <div id="sq-insurance-section" style="display:none;margin-top:14px;padding-top:14px;border-top:1px solid #edf0f3;">
            <label class="sr-field-label" for="sq-insured-value">
                {l s='Valore dichiarato da assicurare (€)' mod='spedisciquishipping'}
            </label>
            <div style="display:flex;align-items:center;gap:14px;">
                <div class="sr-euro-wrap">
                    <span class="sr-euro-sym">€</span>
                    <input type="number" id="sq-insured-value" name="insurance_value" class="sr-value-input" min="0.01"
                        max="{$insuranceMax|string_format:'%.2f'}" step="0.01" value="{$insuranceVal|string_format:'%.2f'}"
                        placeholder="0.00" data-max="{$insuranceMax|string_format:'%.2f'}"
                        oninput="sqUpdateInsuranceSummary()">
                </div>
                <span id="sq-insurance-summary" style="font-size:12px;color:#5a6a7a;"></span>
            </div>
            <div class="sr-notice" style="margin-top:10px;">
                <i class="icon-info-circle"></i>
                <span>
                    {l s='Il valore assicurato non può superare il valore reale della merce (%s€). Verrà verificato in fase di sinistro.' sprintf=[$insuranceMax|string_format:'%.2f'] mod='spedisciquishipping'}
                </span>
            </div>
        </div>

        {* FINE IMPORT ASSICURATIVO *}
    </div>
{else}
    {* REVIEW ASSICURAZIONE *}
    <div class="sr-card" style="margin-bottom:12px;">
        <p class="sr-card-title">
            <i class="icon-shield"></i>
            {l s='Assicurazione' mod='spedisciquishipping'}
        </p>

        <div class="sr-grid">

            <div class="sr-field">
                <span class="sr-field-label">
                    {l s='Stato' mod='spedisciquishipping'}
                </span>

                <span class="sr-field-value">
                    {if $vm.shipment.insurance_enabled}
                        {l s='Assicurazione aggiunta' mod='spedisciquishipping'}
                    {else}
                        {l s='Assicurazione non aggiunta' mod='spedisciquishipping'}
                    {/if}
                </span>
            </div>

            {if $vm.shipment.insurance_enabled && $vm.shipment.insurance_value > 0}
                <div class="sr-field">
                    <span class="sr-field-label">
                        {l s='Valore assicurato' mod='spedisciquishipping'}
                    </span>

                    <span class="sr-field-value">
                        € {$vm.shipment.insurance_value|string_format:"%.2f"}
                    </span>
                </div>
            {/if}

        </div>
    </div>
{/if}