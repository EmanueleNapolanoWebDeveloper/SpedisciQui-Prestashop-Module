  {* ── Header ── *}
  <div class="sq-orders-header">
      <div class="sq-orders-header-left">
          <i class="icon-truck" style="font-size:20px; color:#1a6fc4;"></i>
          <div>
              <p class="sq-orders-title">{l s='Spedizioni' mod='spedisciquishipping'}</p>
              <p class="sq-orders-subtitle">
                  {l s='Gestione e monitoraggio delle spedizioni attive' mod='spedisciquishipping'}</p>
          </div>
      </div>
      {if isset($shipments)}
          <span class="sq-count-pill">{$shipments|count}</span>
      {/if}
</div>