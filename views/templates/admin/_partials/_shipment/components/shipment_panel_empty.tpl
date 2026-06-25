 <div class="sq-empty">
     <div class="sq-empty-icon"><i class="icon-truck"></i></div>
     <p class="sq-empty-title">{l s='Nessuna spedizione trovata' mod='spedisciquishipping'}</p>
     <p class="sq-empty-desc">
         {if $statusFilter}
             {l s='Nessuna spedizione corrisponde al filtro selezionato.' mod='spedisciquishipping'}
         {else}
             {l s='Non ci sono ancora spedizioni registrate.' mod='spedisciquishipping'}
         {/if}
     </p>
</div>