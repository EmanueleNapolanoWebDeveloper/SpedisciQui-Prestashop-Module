 {* ── Filtro status ── *}
 <div class="sq-filter-bar">
     <span class="sq-filter-label">{l s='Filtra per stato:' mod='spedisciquishipping'}</span>
     <form method="GET" style="display:flex; align-items:center; gap:8px; margin:0;">
         {foreach $smarty.get as $k => $v}
             {if $k !== 'status_filter'}
                 <input type="hidden" name="{$k|escape:'html'}" value="{$v|escape:'html'}">
             {/if}
         {/foreach}
         <select name="status_filter">
             <option value="">{l s='— Tutti gli stati —' mod='spedisciquishipping'}</option>
             <option value="pending" {if $statusFilter === 'pending'}selected{/if}>
                 {l s='In attesa' mod='spedisciquishipping'}</option>
             <option value="label_created" {if $statusFilter === 'label_created'}selected{/if}>
                 {l s='Label creata' mod='spedisciquishipping'}</option>
             <option value="picked_up" {if $statusFilter === 'picked_up'}selected{/if}>
                 {l s='Ritirato' mod='spedisciquishipping'}</option>
             <option value="in_transit" {if $statusFilter === 'in_transit'}selected{/if}>
                 {l s='In transito' mod='spedisciquishipping'}</option>
             <option value="out_for_delivery" {if $statusFilter === 'out_for_delivery'}selected{/if}>
                 {l s='In consegna' mod='spedisciquishipping'}</option>
             <option value="delivered" {if $statusFilter === 'delivered'}selected{/if}>
                 {l s='Consegnato' mod='spedisciquishipping'}</option>
             <option value="failed" {if $statusFilter === 'failed'}selected{/if}>
                 {l s='Fallito' mod='spedisciquishipping'}</option>
             <option value="cancelled" {if $statusFilter === 'cancelled'}selected{/if}>
                 {l s='Annullato' mod='spedisciquishipping'}</option>
             <option value="returned" {if $statusFilter === 'returned'}selected{/if}>
                 {l s='Reso' mod='spedisciquishipping'}</option>
         </select>
         <button type="submit" class="sq-filter-btn">
             <i class="icon-filter"></i>
             {l s='Filtra' mod='spedisciquishipping'}
         </button>
     </form>
</div>