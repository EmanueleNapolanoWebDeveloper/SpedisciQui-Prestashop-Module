 <tr>

     {* ID shipment *}
     <td>
         <strong style="color:#1a2535;">#{$shipment.id_shipment}</strong>
     </td>

     {* Ordine *}
     <td>
         <a href="{$orderDetailLink}&id_order={$shipment.id_order}" target="_blank" class="sq-order-id">
             #{$shipment.id_order}
             <i class="icon-external-link" style="font-size:10px; opacity:0.6;"></i>
         </a>
     </td>

     {* Cliente *}
     <td>
         <div class="sq-customer-name">{$shipment.customer_name|escape:'html':'UTF-8'}</div>
         <div class="sq-customer-meta">
             {$shipment.delivery_city|escape:'html':'UTF-8'}
             {if $shipment.delivery_country}
                 &nbsp;({$shipment.delivery_country|escape:'html':'UTF-8'})
             {/if}
         </div>
     </td>

     {* Corriere *}
     <td>
         <span class="sq-carrier-code">{$shipment.carrier_code|escape:'html':'UTF-8'}</span>
         <div class="sq-service-code">{$shipment.service_code|escape:'html':'UTF-8'}</div>
     </td>

     {* Tracking *}
     <td class="sq-tracking">
         {if $shipment.tracking_number !== '—'}
             <code>{$shipment.tracking_number|escape:'html':'UTF-8'}</code>
         {else}
             <span style="color:#c8d0db;">—</span>
         {/if}
     </td>

     {* Stato spedizione *}
     <td>
         <span class="sq-badge sq-badge-{$shipment.status_class}">
             <span class="sq-badge-dot"></span>
             {$shipment.status_label|escape:'html':'UTF-8'}
         </span>
     </td>

     {* Pagamento *}
     <td>
         {if $shipment.payment_status === 'paid'}
             <span class="sq-badge sq-pay-paid">
                 <i class="icon-check" style="font-size:10px;"></i>
                 {l s='Pagato' mod='spedisciquishipping'}
             </span>
         {elseif $shipment.payment_status === 'refunded'}
             <span class="sq-badge sq-pay-refunded">
                 <i class="icon-refresh" style="font-size:10px;"></i>
                 {l s='Rimborsato' mod='spedisciquishipping'}
             </span>
         {else}
             <span class="sq-badge sq-pay-pending">
                 <i class="icon-time" style="font-size:10px;"></i>
                 {l s='In attesa' mod='spedisciquishipping'}
             </span>
         {/if}
     </td>

     {* Totale *}
     <td>
         <div class="sq-total">{$shipment.total_paid} {$shipment.currency}</div>
         <div class="sq-payment-method">{$shipment.payment_method|escape:'html':'UTF-8'}</div>
     </td>

     {* Peso *}
     <td style="white-space:nowrap; color:#5a6a7a;">
         {$shipment.weight} kg
     </td>

     {* Data *}
     <td style="white-space:nowrap; font-size:12px; color:#9aabb8;">
         {$shipment.date_add|escape:'html':'UTF-8'}
     </td>

     {* Azioni *}
     <td>
         {if $shipment.status === 'pending'}
             <div class="sq-action-wrap">
                 <a href="{$action}&action=shipmentReview&id_shipment={$shipment.id_shipment}" class="sq-btn-create">
                     <i class="icon-search"></i>
                     {l s='Crea spedizione' mod='spedisciquishipping'}
                 </a>
             </div>
         {elseif $shipment.status === 'label_created'}
             <form method="POST" action="{$action}" style="margin:0;">
                 <input type="hidden" name="id_shipment" value="{$shipment.id_shipment}">

                 <button type="submit" name="cancelShipment" class="sq-btn-cancel"
                     onclick="return confirm('{l s='Annullare la spedizione?' mod='spedisciquishipping' js=1}');">
                     <i class="icon-remove"></i>
                     {l s='Annulla' mod='spedisciquishipping'}
                 </button>
             </form>

             {* ── PDF ACTIONS ── *}
             {if $shipment.status === 'label_created' && $shipment.label_path}
                 <div class="sq-action-wrap" style="margin-top:6px;">

                     {* VIEW PDF *}
                     <a href="{$shipment.label_path}" target="_blank" class="sq-btn-create" style="background:#5a6a7a;">
                         <i class="icon-eye"></i>
                         {l s='Visualizza etichetta' mod='spedisciquishipping'}
                     </a>

                     {* DOWNLOAD PDF *}
                     <a href="{$shipment.label_path}" download class="sq-btn-create" style="background:#1e7e34;">
                         <i class="icon-download"></i>
                         {l s='Scarica PDF' mod='spedisciquishipping'}
                     </a>

                 </div>
             {/if}
         {/if}
     </td>

</tr>