{**
 * SpedisciQui — Shipment Review
 * Template: views/templates/admin/_partials/_orders/shipment_review.tpl
 *}
<div class="sr-page">

    <a href="{$vm.form.back_url|escape:'html':'UTF-8'}" class="sr-back">
        <i class="icon-arrow-left"></i>
        {l s='Torna alla lista spedizioni' mod='spedisciquishipping'}
    </a>

    <p class="sr-page-title">
        {l s='Revisione spedizione' mod='spedisciquishipping'}
        <span>#{$vm.shipment.id_shipment|intval}</span>
    </p>
    <p class="sr-page-sub">
        {l s='Verifica tutti i dati prima di creare la spedizione. Le operazioni non sono reversibili.' mod='spedisciquishipping'}
    </p>

    {if isset($smarty.get.sq_error) && $smarty.get.sq_error}
        <div class="sr-alert sr-alert-danger">
            <i class="icon-warning-sign"></i>
            <span>{$smarty.get.sq_error|escape:'html':'UTF-8'}</span>
        </div>
    {/if}

    {* ── Dati JS — DEVE stare PRIMA dei componenti ── *}
    <script>
        window.SQ_Review = {
            order_id:      {$sq_order_id|intval},
            token:         '{$sq_token|escape:'javascript'}',
            currency_sign: '{$sq_currency_sign|escape:'javascript'}',
            carriers:      {$sq_carriers_json|default:'[]'},
            ajax_url:      '{$sq_ajax_url|escape:'javascript'}'
        };
    </script>

    {* ── Card info (sola lettura) ── *}
    {include file='module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_detail_info_card.tpl'}

    {* ── Form principale ── *}
    <form method="POST" action="{$vm.form.action_url|escape:'html':'UTF-8'}" id="sq-review-form"
        onsubmit="return sqReviewSubmit(event)">

        <input type="hidden" name="createShipment" value="1">
        <input type="hidden" name="id_shipment" value="{$vm.form.id_shipment|intval}">

        {include file='module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_detail_insurance.tpl'}
        {include file='module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_detail_options.tpl'}
        {include file='module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_detail_summary.tpl'}
        {include file='module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_detail_actions.tpl'}

    </form>

</div>