 <div class="sq-table-wrap">
     <table class="sq-table">
         <thead>
             <tr>
                 <th class="sq-logo-cell"></th>
                 <th>{l s='Corriere' mod='spedisciquishipping'}</th>
                 <th>{l s='Codice Servizio' mod='spedisciquishipping'}</th>
                 <th>{l s='Consegna' mod='spedisciquishipping'}</th>
                 <th>{l s='Stato' mod='spedisciquishipping'}</th>
                 <th>{l s='Date' mod='spedisciquishipping'}</th>
                 <th>{l s='Azioni' mod='spedisciquishipping'}</th>
             </tr>
         </thead>
         <tbody>
             {foreach from=$savedCarriers item=sc}
               {include file="module:spedisciquishipping/views/templates/admin/_partials/_carrier/components/carriere_active_table_row.tpl"}
             {/foreach}
         </tbody>
     </table>
</div>