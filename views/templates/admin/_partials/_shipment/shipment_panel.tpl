{include file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_modal.tpl"}

<div class="sq-orders">
    {include file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_header.tpl"}
    {include file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_messages.tpl"}
    {include file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_filter_status.tpl"}

    {if empty($shipments)}
        {include file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_empty.tpl"}
    {else}
        {include file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_shipment_list.tpl"}
        {include file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_pagination.tpl"}
    {/if}
</div>
