 {assign var="totalPages" value=ceil($totalShipments / $limit)}
 <div class="sq-pagination">
     {for $p=1 to $totalPages}
         <a class="sq-page-btn {if $p == $currentPage}active{/if}" href="?{foreach $smarty.get as $k => $v}
                            {if $k !== 'page'}{$k|escape:'url'}={$v|escape:'url'}&
                            {/if}
                        {/foreach}page={$p}">
             {$p}
         </a>
     {/for}
     <div class="sq-page-info">
         {$totalShipments} {l s='spedizioni totali' mod='spedisciquishipping'} —
         {l s='pagina' mod='spedisciquishipping'} {$currentPage} {l s='di' mod='spedisciquishipping'} {$totalPages}
     </div>
</div>