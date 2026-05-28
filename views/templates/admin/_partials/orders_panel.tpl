{**
 * SpedisciQui — Dashboard Spedizioni
 * Template: views/templates/admin/shipment_dashboard.tpl
 *}

<div class="panel spedisciqui-dashboard">

    {* ── Header ────────────────────────────────────────────────────────────── *}
    <div class="panel-heading">
        <i class="icon-truck"></i>
        Dashboard Spedizioni
        <span class="badge">{$shipments|count}</span>
    </div>

    {* ── Messaggi flash ─────────────────────────────────────────────────────── *}
    {if isset($smarty.get.conf) && $smarty.get.conf}
        <div class="alert alert-success">
            <i class="icon-check"></i> {$smarty.get.conf|escape:'html':'UTF-8'}
        </div>
    {/if}

    {if isset($smarty.get.error) && $smarty.get.error}
        <div class="alert alert-danger">
            <i class="icon-warning-sign"></i> {$smarty.get.error|escape:'html':'UTF-8'}
        </div>
    {/if}

    {* ── Filtro status ──────────────────────────────────────────────────────── *}
    <div class="row" style="margin-bottom: 15px;">
        <div class="col-md-4">
            <form method="GET" class="form-inline">
                {foreach $smarty.get as $k => $v}
                    {if $k !== 'status_filter'}
                        <input type="hidden" name="{$k|escape:'html'}" value="{$v|escape:'html'}">
                    {/if}
                {/foreach}
                <div class="input-group">
                    <select name="status_filter" class="form-control input-sm">
                        <option value="">— Tutti gli stati —</option>
                        <option value="pending" {if $statusFilter === 'pending'}selected{/if}>In attesa</option>
                        <option value="label_created" {if $statusFilter === 'label_created'}selected{/if}>Label creata
                        </option>
                        <option value="picked_up" {if $statusFilter === 'picked_up'}selected{/if}>Ritirato</option>
                        <option value="in_transit" {if $statusFilter === 'in_transit'}selected{/if}>In transito</option>
                        <option value="out_for_delivery" {if $statusFilter === 'out_for_delivery'}selected{/if}>In
                            consegna</option>
                        <option value="delivered" {if $statusFilter === 'delivered'}selected{/if}>Consegnato</option>
                        <option value="failed" {if $statusFilter === 'failed'}selected{/if}>Fallito</option>
                        <option value="cancelled" {if $statusFilter === 'cancelled'}selected{/if}>Annullato</option>
                        <option value="returned" {if $statusFilter === 'returned'}selected{/if}>Reso</option>
                    </select>
                    <span class="input-group-btn">
                        <button type="submit" class="btn btn-default btn-sm">
                            <i class="icon-filter"></i> Filtra
                        </button>
                    </span>
                </div>
            </form>
        </div>
    </div>

    {* ── Tabella ────────────────────────────────────────────────────────────── *}
    {if empty($shipments)}
        <div class="alert alert-info">
            <i class="icon-info-sign"></i> Nessuna spedizione trovata.
        </div>
    {else}
        <div class="table-responsive">
            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Ordine</th>
                        <th>Cliente</th>
                        <th>Corriere</th>
                        <th>Tracking</th>
                        <th>Stato spedizione</th>
                        <th>Pagamento</th>
                        <th>Totale</th>
                        <th>Peso</th>
                        <th>Data</th>
                        <th>Azioni</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach $shipments as $shipment}
                        <tr>

                            {* ID Shipment *}
                            <td>
                                <strong>#{$shipment.id_shipment}</strong>
                            </td>

                            {* ID Order con link *}
                            <td>
                                <a href="{$orderDetailLink}&id_order={$shipment.id_order}" target="_blank">
                                    #{$shipment.id_order}
                                </a>
                            </td>

                            {* Cliente *}
                            <td>
                                {$shipment.customer_name|escape:'html':'UTF-8'}
                                <br>
                                <small class="text-muted">
                                    {$shipment.delivery_city|escape:'html':'UTF-8'}
                                    {if $shipment.delivery_country}
                                        ({$shipment.delivery_country|escape:'html':'UTF-8'})
                                    {/if}
                                </small>
                            </td>

                            {* Corriere *}
                            <td>
                                <span class="label label-default">
                                    {$shipment.carrier_code|escape:'html':'UTF-8'}
                                </span>
                                <br>
                                <small>{$shipment.service_code|escape:'html':'UTF-8'}</small>
                            </td>

                            {* Tracking *}
                            <td>
                                {if $shipment.tracking_number !== '—'}
                                    <code>{$shipment.tracking_number|escape:'html':'UTF-8'}</code>
                                {else}
                                    <span class="text-muted">—</span>
                                {/if}
                            </td>

                            {* Status spedizione *}
                            <td>
                                <span class="badge badge-{$shipment.status_class}">
                                    {$shipment.status_label|escape:'html':'UTF-8'}
                                </span>
                            </td>

                            {* Status pagamento *}
                            <td>
                                {if $shipment.payment_status === 'paid'}
                                    <span class="badge badge-success">
                                        <i class="icon-check"></i> Pagato
                                    </span>
                                {elseif $shipment.payment_status === 'refunded'}
                                    <span class="badge badge-warning">
                                        <i class="icon-refresh"></i> Rimborsato
                                    </span>
                                {else}
                                    <span class="badge badge-danger">
                                        <i class="icon-time"></i> In attesa
                                    </span>
                                {/if}
                            </td>

                            {* Totale ordine *}
                            <td>
                                <strong>{$shipment.total_paid} {$shipment.currency}</strong>
                                <br>
                                <small class="text-muted">{$shipment.payment_method|escape:'html':'UTF-8'}</small>
                            </td>

                            {* Peso *}
                            <td>{$shipment.weight} kg</td>

                            {* Data *}
                            <td>
                                <small>{$shipment.date_add|escape:'html':'UTF-8'}</small>
                            </td>

                            {* Azioni *}
                            <td>
                                {if $shipment.status === 'pending'}
                                    <form method="POST" action="{$formAction}" style="display:inline;">
                                        <input type="hidden" name="id_shipment" value="{$shipment.id_shipment}">
                                        <button type="submit" name="createShipment" class="btn btn-primary btn-sm"
                                            onclick="return confirm('Creare spedizione #{$shipment.id_shipment}?');">
                                            <i class="icon-truck"></i> Crea Spedizione
                                        </button>
                                    </form>
                                {elseif $shipment.status === 'label_created'}
                                    <form method="POST" action="{$formAction}" style="display:inline;">
                                        <input type="hidden" name="id_shipment" value="{$shipment.id_shipment}">
                                        <button type="submit" name="cancelShipment" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Annullare la spedizione #{$shipment.id_shipment}?');">
                                            <i class="icon-remove"></i> Annulla
                                        </button>
                                    </form>
                                {else}
                                    <span class="text-muted">—</span>
                                {/if}
                            </td>

                        </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>

        {* ── Paginazione ────────────────────────────────────────────────────────── *}
        {if $totalShipments > $limit}
            <div class="text-center">
                <ul class="pagination pagination-sm">
                    {assign var="totalPages" value=ceil($totalShipments / $limit)}
                    {for $p=1 to $totalPages}
                        <li class="{if $p == $currentPage}active{/if}">
                            <a
                                href="?{foreach $smarty.get as $k => $v}
                                    {if $k !== 'page'}{$k|escape:'url'}={$v|escape:'url'}&
                                    {/if}
                                {/foreach}page={$p}">
                                {$p}
                            </a>
                        </li>
                    {/for}
                </ul>
                <p class="text-muted text-center">
                    {$totalShipments} spedizioni totali — pagina {$currentPage} di {$totalPages}
                </p>
            </div>
        {/if}

    {/if}

</div>