 <div class="sq-stepper">
     {assign var="current_step" value=$setup_step|default:0}

     <div class="sq-step {if $current_step > 0}done{elseif $current_step == 0}active{/if}" style="width:120px;">
         <div class="sq-step-circle">{if $current_step > 0}✓{else}1{/if}</div>
         <span class="sq-step-label">Token API</span>
     </div>

     <div class="sq-step {if $current_step > 1}done{elseif $current_step == 1}active{/if}" style="width:120px;">
         <div class="sq-step-circle">{if $current_step > 1}✓{else}2{/if}</div>
         <span class="sq-step-label">Mittente</span>
     </div>

     <div class="sq-step {if $current_step > 2}done{elseif $current_step == 2}active{/if}" style="width:120px;">
         <div class="sq-step-circle">{if $current_step > 2}✓{else}3{/if}</div>
         <span class="sq-step-label">Pacco</span>
     </div>

     <div class="sq-step {if $current_step > 3}done{elseif $current_step == 3}active{/if}" style="width:120px;">
         <div class="sq-step-circle">{if $current_step > 3}✓{else}4{/if}</div>
         <span class="sq-step-label">Corrieri</span>
     </div>
</div>