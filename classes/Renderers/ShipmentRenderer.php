<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShipmentRenderer
{
    /**
     * Mappa gli stati ordine PS a etichette leggibili.
     * Estendibile con altri stati custom.
     */
    private const PAYMENT_STATUS_MAP = [
        'Payment accepted'       => 'paid',
        'Awaiting bank wire payment' => 'pending',
        'Refunded'               => 'refunded',
    ];


    //==================================================
    //RECUPERA TUTTI GLI SHIPMENTS
    //====================================================

    public function getShipments(
        int    $idShop = 1,
        string $statusFilter = '',
        int    $limit = 50,
        int    $offset = 0
    ): array {
        $db = Db::getInstance();

        $query = new DbQuery();
        $query->select('
            sh.`id`                  AS id_shipment,
            sh.`id_order`,
            sh.`id_spedisciqui_carrier`,
            sh.`carrier_code`,
            sh.`service_code`,
            sh.`tracking_number`,
            sh.`status`,
            sh.`shipping_cost`,
            sh.`shipping_currency`,
            sh.`weight`,
            sh.`delivery_firstname`,
            sh.`delivery_lastname`,
            sh.`delivery_city`,
            sh.`delivery_country_iso`,
            sh.`date_add`,
            o.`total_paid_tax_incl`  AS total_paid,
            o.`payment`              AS payment_method,
            o.`current_state`        AS id_order_state,
            os.`name`                AS order_state_name,
            CONCAT(c.`firstname`, " ", c.`lastname`) AS customer_name,
            c.`email`                AS customer_email
        ');
        $query->from('spedisciqui_shipments', 'sh');

        // JOIN ordine
        $query->innerJoin(
            'orders',
            'o',
            'o.`id_order` = sh.`id_order`'
        );

        // JOIN stato ordine (con lingua — default 1)
        $query->leftJoin(
            'order_state_lang',
            'os',
            'os.`id_order_state` = o.`current_state`
             AND os.`id_lang` = ' . (int) Configuration::get('PS_LANG_DEFAULT')
        );

        // JOIN cliente
        $query->innerJoin(
            'customer',
            'c',
            'c.`id_customer` = o.`id_customer`'
        );

        // Filtro shop
        $query->where('sh.`id_shop` = ' . (int) $idShop);

        // Filtro status opzionale
        if ($statusFilter !== '') {
            $query->where('sh.`status` = \'' . pSQL($statusFilter) . '\'');
        }

        $query->orderBy('sh.`date_add` DESC');
        $query->limit($limit, $offset);

        $rows = $db->executeS($query);

        if (empty($rows)) {
            return [];
        }

        return array_map([$this, 'formatRow'], $rows);
    }

    //==================================================
    //CONTO DEGLI SHIPMENTS
    //====================================================
    public function countShipments(int $idShop = 1, string $statusFilter = ''): int
    {
        $query = new DbQuery();
        $query->select('COUNT(*)');
        $query->from('spedisciqui_shipments', 'sh');
        $query->where('sh.`id_shop` = ' . (int) $idShop);

        if ($statusFilter !== '') {
            $query->where('sh.`status` = \'' . pSQL($statusFilter) . '\'');
        }

        return (int) Db::getInstance()->getValue($query);
    }

    //==================================================
    //FROMATTAZIOEN RIGA PER OUTPUT
    //====================================================
    private function formatRow(array $row): array
    {
        return [
            'id_shipment'      => (int)    $row['id_shipment'],
            'id_order'         => (int)    $row['id_order'],
            'tracking_number'  => (string) ($row['tracking_number'] ?? '—'),
            'carrier_code'     => (string) ($row['carrier_code']    ?? '—'),
            'service_code'     => (string) ($row['service_code']    ?? '—'),
            'status'           => (string) $row['status'],
            'status_label'     => $this->getStatusLabel((string) $row['status']),
            'status_class'     => $this->getStatusClass((string) $row['status']),
            'payment_status'   => $this->resolvePaymentStatus((string) ($row['order_state_name'] ?? '')),
            'payment_method'   => (string) ($row['payment_method'] ?? ''),
            'total_paid'       => number_format((float) $row['total_paid'], 2, ',', '.'),
            'currency'         => (string) ($row['shipping_currency'] ?? 'EUR'),
            'customer_name'    => (string) ($row['customer_name']   ?? '—'),
            'customer_email'   => (string) ($row['customer_email']  ?? ''),
            'delivery_city'    => (string) ($row['delivery_city']   ?? ''),
            'delivery_country' => (string) ($row['delivery_country_iso'] ?? ''),
            'weight'           => number_format((float) ($row['weight'] ?? 0), 3, ',', '.'),
            'shipping_cost'    => number_format((float) ($row['shipping_cost'] ?? 0), 2, ',', '.'),
            'date_add'         => $row['date_add']
                ? date('d/m/Y H:i', strtotime($row['date_add']))
                : '—',
        ];
    }

    //==================================================
    //RECUPERA ETICHETTE
    //====================================================
    private function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending'          => 'In attesa',
            'label_created'    => 'Label creata',
            'picked_up'        => 'Ritirato',
            'in_transit'       => 'In transito',
            'out_for_delivery' => 'In consegna',
            'delivered'        => 'Consegnato',
            'failed'           => 'Fallito',
            'cancelled'        => 'Annullato',
            'returned'         => 'Reso',
            default            => ucfirst($status),
        };
    }

    /**
     * Classe CSS Bootstrap per badge status.
     */
    private function getStatusClass(string $status): string
    {
        return match ($status) {
            'pending'          => 'warning',
            'label_created'    => 'info',
            'picked_up',
            'in_transit'       => 'primary',
            'out_for_delivery' => 'primary',
            'delivered'        => 'success',
            'failed',
            'cancelled'        => 'danger',
            'returned'         => 'secondary',
            default            => 'secondary',
        };
    }

    //==================================================
    //RISOLVE STATO PAGAMENTO
    //====================================================
    private function resolvePaymentStatus(string $orderStateName): string
    {
        foreach (self::PAYMENT_STATUS_MAP as $keyword => $status) {
            if (stripos($orderStateName, $keyword) !== false) {
                return $status;
            }
        }
        return 'pending';
    }
}
