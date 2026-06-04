  <tr>

      {* logo *}
      <td class="sq-logo-cell">
          {if $sc.logo}
              <div class="sq-logo-wrap">
                  <img src="{$sc.logo|escape:'htmlall':'UTF-8'}" alt="{$sc.carrier_name|escape:'htmlall':'UTF-8'}">
              </div>
          {else}
              <div class="sq-logo-placeholder">
                  <i class="icon-truck"></i>
              </div>
          {/if}
      </td>

      {* nome corrier*}
      <td>
          <p class="sq-carrier-name">{$sc.carrier_name|escape:'htmlall':'UTF-8'}</p>
          {if $sc.service_name}
              <p class="sq-carrier-service text-black">{$sc.service_name|escape:'htmlall':'UTF-8'}</p>
          {/if}
      </td>

      {* service code corrier *}
      <td>
          <div class="sq-codes-stack">
              <span class="sq-code">{$sc.carrier_code|escape:'htmlall':'UTF-8'}</span>
              {if $sc.service_code}
                  <span class="sq-code-small">{$sc.service_code|escape:'htmlall':'UTF-8'}</span>
              {/if}
          </div>
      </td>

      {* tempi di consegna *}
      <td>
          <span class="sq-id-badge">
              <i class="icon-tag" style="font-size:10px;"></i>
              #{$sc.delay}
          </span>
      </td>

      {* stato configurazione *}
      <td>
          <div class="sq-badges-stack">
              {if empty($configuredCodes) || !in_array($sc.carrier_code, $configuredCodes)}
                  <span class="sq-badge sq-badge-pickup">
                      <span class="sq-badge-dot"></span>
                      {l s='Da Configurare!' mod='spedisciquishipping'}
                  </span>
              {else}
                  <span class="sq-badge sq-badge-courier">
                      <span class="sq-badge-dot"></span>
                      {l s='Configurato!' mod='spedisciquishipping'}
                  </span>
              {/if}
          </div>
      </td>

      {* data di aggiunta *}
      <td>
          <div class="sq-meta">
              <div><span
                      class="sq-meta-label">{l s='Aggiunto:' mod='spedisciquishipping'}</span>{$sc.date_add|escape:'htmlall':'UTF-8'|truncate:10:''}
              </div>
              <div><span
                      class="sq-meta-label">{l s='Aggiornato:' mod='spedisciquishipping'}</span>{$sc.date_upd|escape:'htmlall':'UTF-8'|truncate:10:''}
              </div>
          </div>
      </td>

      <td>
          <div class="sq-actions">


              {* configura *}
              <form method="GET" action="index.php" style="display:inline; margin:0;">
                  <input type="hidden" name="controller" value="AdminModules">
                  <input type="hidden" name="configure" value="spedisciquishipping">
                  <input type="hidden" name="token" value="{$smarty.get.token|escape:'htmlall':'UTF-8'}">
                  <input type="hidden" name="carrier_code" value="{$sc.carrier_code|escape:'htmlall':'UTF-8'}">
                  <button type="submit" class="sq-btn sq-btn-configure">
                      <i class="icon-cog"></i>
                      {l s='Configura' mod='spedisciquishipping'}
                  </button>
              </form>

              {* rimuovi *}
              <form method="POST" action="{$action|escape:'htmlall':'UTF-8'}">
                  <input type="hidden" name="carrier_code" value="{$sc.carrier_code|escape:'htmlall':'UTF-8'}">
                  <button type="submit" name="removeSpedisciQuiCarriers" class="sq-btn sq-btn-remove"
                      onclick="return confirm('{l s='Rimuovere il corriere?' mod='spedisciquishipping' js=1}');">
                      <i class="icon-trash"></i>
                      {l s='Rimuovi' mod='spedisciquishipping'}
                  </button>
              </form>

          </div>
      </td>

</tr>