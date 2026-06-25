{include
    file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_modal.tpl"
}

<div class="sq-orders">
    {* 1. Header della pagina *}
    {include
        file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_header.tpl"
    }

    {* 2. Messaggi di errore o conferma *}
    {include
        file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_messages.tpl"
    }

    {* 3. La barra di filtro (Ora è SOPRA la tabella, dove deve stare!) *}
    {include
        file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_filter_status.tpl"
    }

    {* 4. Tabella o stato vuoto *}
    {if empty($shipments)}
        {include
            file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_empty.tpl"
        }
    {else}
        <div class="sq-table-wrap" style="margin-top: 15px;">
            <table class="sq-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{l s='Codice' mod='spedisciquishipping'}</th>
                        <th>{l s='Cliente' mod='spedisciquishipping'}</th>
                        <th>{l s='Corriere' mod='spedisciquishipping'}</th>
                        <th>{l s='Tracking' mod='spedisciquishipping'}</th>
                        <th>{l s='Stato spedizione' mod='spedisciquishipping'}</th>
                        <th>{l s='Pagamento' mod='spedisciquishipping'}</th>
                        <th>{l s='Totale' mod='spedisciquishipping'}</th>
                        <th>{l s='Peso' mod='spedisciquishipping'}</th>
                        <th>{l s='Data' mod='spedisciquishipping'}</th>
                        <th>{l s='Azioni' mod='spedisciquishipping'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $shipments as $shipment}
                        {include
                            file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_shipment_row.tpl"
                            shipment=$shipment
                        }
                    {/foreach}
                </tbody>
            </table>
        </div>

        {* 5. Paginazione sotto la tabella *}
        {include
            file="module:spedisciquishipping/views/templates/admin/_partials/_shipment/components/shipment_panel_pagination.tpl"
        }
    {/if}
</div>