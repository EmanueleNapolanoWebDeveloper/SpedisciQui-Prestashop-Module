 <div class="sr-card">
     <p class="sr-card-title">
         <i class="icon-list-ul"></i>
         {l s='Ordine' mod='spedisciquishipping'}
     </p>
     <div class="sr-kv">
         <div class="sr-kv-row">
             <span class="sr-kv-label">{l s='Riferimento' mod='spedisciquishipping'}</span>
             <span class="sr-kv-value">
                 <a href="{$vm.form.order_detail_url|escape:'html':'UTF-8'}&id_order={$vm.order.id_order|intval}"
                     target="_blank" style="color:#1a6fc4;text-decoration:none;font-weight:700;">
                     {$vm.order.reference|escape:'html':'UTF-8'}
                     <i class="icon-external-link" style="font-size:10px;opacity:.6;"></i>
                 </a>
             </span>
         </div>
         <div class="sr-kv-row">
             <span class="sr-kv-label">{l s='Data ordine' mod='spedisciquishipping'}</span>
             <span class="sr-kv-value">{$vm.order.date_add|escape:'html':'UTF-8'}</span>
         </div>
         <div class="sr-kv-row">
             <span class="sr-kv-label">{l s='Totale' mod='spedisciquishipping'}</span>
             <span class="sr-kv-value">
                 {$vm.order.total_paid|string_format:'%.2f'} {$vm.order.currency|escape:'html':'UTF-8'}
             </span>
         </div>
         <div class="sr-kv-row">
             <span class="sr-kv-label">{l s='Pagamento' mod='spedisciquishipping'}</span>
             <span class="sr-kv-value">{$vm.order.payment_method|escape:'html':'UTF-8'}</span>
         </div>
         <div class="sr-kv-row">
             <span class="sr-kv-label">{l s='Stato pag.' mod='spedisciquishipping'}</span>
             <span class="sr-kv-value">
                 {if $vm.order.payment_status === 'paid'}
                     <span class="sr-badge sr-badge-paid">
                         <i class="icon-check" style="font-size:9px;"></i>
                         {l s='Pagato' mod='spedisciquishipping'}
                     </span>
                 {else}
                     <span class="sr-badge sr-badge-pending-pay">
                         <i class="icon-time" style="font-size:9px;"></i>
                         {$vm.order.payment_label|escape:'html':'UTF-8'}
                     </span>
                 {/if}
             </span>
         </div>
     </div>
</div>