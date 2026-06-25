{* ── Barra dei Filtri Avanzata (Stato + Ricerca Testuale) ── *}
<div class="sq-filter-bar" style="margin-bottom: 15px; background: #fff; padding: 12px; border-radius: 4px; border: 1px solid #e3e3e3; display: flex; align-items: center; justify-content: space-between;">
    <form method="GET" action="{$smarty.server.REQUEST_URI|escape:'html':'UTF-8'}" style="display:flex; align-items:center; gap:12px; margin:0; width: 100%; flex-wrap: wrap;">

        {* Ripristina tutti i parametri fondamentali di PrestaShop *}
        {foreach from=$smarty.get key=k item=v}
            {if $k !== 'status_filter' && $k !== 'search_text' && $k !== 'submitFilter'}
                <input type="hidden" name="{$k|escape:'html':'UTF-8'}" value="{$v|escape:'html':'UTF-8'}">
            {/if}
        {/foreach}

        {* Blocco ricerca testuale *}
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="font-weight: bold;">{l s='Cerca:' mod='spedisciquishipping'}</span>
            <input type="text" name="search_text" value="{$searchText|escape:'html':'UTF-8'}" placeholder="{l s='ID Ordine, Tracking, Cliente...' mod='spedisciquishipping'}" style="padding: 6px 10px; border-radius: 4px; border: 1px solid #ccc; width: 220px;">
        </div>

        {* Blocco filtro stato *}
        <div style="display: flex; align-items: center; gap: 6px;">
            <span style="font-weight: bold;">{l s='Stato:' mod='spedisciquishipping'}</span>
            <select name="status_filter" onchange="this.form.submit();" style="padding: 6px; border-radius: 4px; border: 1px solid #ccc;">
                <option value="">{l s='— Tutti gli stati —' mod='spedisciquishipping'}</option>
                <option value="pending" {if $statusFilter === 'pending'}selected{/if}>{l s='In attesa' mod='spedisciquishipping'}</option>
                <option value="request_send" {if $statusFilter === 'request_send'}selected{/if}>{l s='Richiesta inviata' mod='spedisciquishipping'}</option>
                <option value="label_created" {if $statusFilter === 'label_created'}selected{/if}>{l s='Label creata' mod='spedisciquishipping'}</option>
                <option value="picked_up" {if $statusFilter === 'picked_up'}selected{/if}>{l s='Ritirato' mod='spedisciquishipping'}</option>
                <option value="in_transit" {if $statusFilter === 'in_transit'}selected{/if}>{l s='In transito' mod='spedisciquishipping'}</option>
                <option value="out_for_delivery" {if $statusFilter === 'out_for_delivery'}selected{/if}>{l s='In consegna' mod='spedisciquishipping'}</option>
                <option value="delivered" {if $statusFilter === 'delivered'}selected{/if}>{l s='Consegnato' mod='spedisciquishipping'}</option>
                <option value="failed" {if $statusFilter === 'failed'}selected{/if}>{l s='Fallito' mod='spedisciquishipping'}</option>
                <option value="cancelled" {if $statusFilter === 'cancelled'}selected{/if}>{l s='Annullato' mod='spedisciquishipping'}</option>
                <option value="returned" {if $statusFilter === 'returned'}selected{/if}>{l s='Reso' mod='spedisciquishipping'}</option>
            </select>
        </div>

        <button type="submit" class="btn btn-primary sq-filter-btn" style="padding: 6px 12px;">
            <i class="icon-search"></i> {l s='Filtra' mod='spedisciquishipping'}
        </button>
    </form>
</div>