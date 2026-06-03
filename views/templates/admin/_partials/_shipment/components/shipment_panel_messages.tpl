 {* ── Flash messages ── *}
 {if isset($smarty.get.conf) && $smarty.get.conf}
     <div class="sq-alert sq-alert-success">
         <i class="icon-check"></i>
         <span>{$smarty.get.conf|escape:'html':'UTF-8'}</span>
     </div>
 {/if}
 {if isset($smarty.get.error) && $smarty.get.error}
     <div class="sq-alert sq-alert-danger">
         <i class="icon-warning-sign"></i>
         <span>{$smarty.get.error|escape:'html':'UTF-8'}</span>
     </div>
{/if}