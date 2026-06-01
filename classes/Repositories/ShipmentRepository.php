<?php

class ShipmentRepository
{

    private ?ShipmentServices $shipmentService = null;


    public function __construct() {}

    public function setShipmentService(ShipmentServices $shipmentService): void
    {
        $this->shipmentService = $shipmentService;
    }


    // ==============================================
    // RECUPERO SHIPMENT DA ID
    // ============================================

    public function getShipmentById(int $idShipment): array|false
    {
        // 1. Validazione input
        if ($idShipment <= 0) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] getShipmentById: ID non valido (' . $idShipment . ')',
                3
            );
            return false;
        }

        // 2. Query sicura
        try {
            $sql = new DbQuery();
            $sql->select('id, id_order, status, carrier_code, service_code,weight,shipping_cost')
                ->from('spedisciqui_shipments')
                ->where('id = ' . (int) $idShipment);

            $row = Db::getInstance()->getRow($sql);

            // 3. Controllo risultato
            if (empty($row) || !is_array($row)) {
                PrestaShopLogger::addLog(
                    '[SpedisciQui] Shipment non trovato ID: ' . $idShipment,
                    2
                );
                return false;
            }

            // 4. Normalizzazione dati (opzionale ma utile)
            return [
                'id_shipment'            => (int) $row['id'],
                'id_order'      => (int) $row['id_order'],
                'status'        => (string) $row['status'],
                'carrier_code'  => $row['carrier_code'] ? (string) $row['carrier_code'] : null,
                'service_code'  => $row['service_code'] ? (string) $row['service_code'] : null,
                'weight' => $row['weight'] ? (float) $row['weight'] : null,
                'shipping_cost' => $row['shipping_cost'] ? (string) $row['shipping_cost'] : null
            ];
        } catch (Exception $e) {
            // 5. Gestione errori DB
            PrestaShopLogger::addLog(
                '[SpedisciQui] Errore getShipmentById: ' . $e->getMessage(),
                3
            );

            return false;
        }
    }



    // ==============================================
    // AGGIORNAMENT STATO + OPZIONALI
    // ==============================================
    public function updateShipmentStatus(int $idShipment, string $status, array $extra = []): bool
    {
        // 1. Validazione ID
        if ($idShipment <= 0) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] updateShipmentStatus: ID non valido (' . $idShipment . ')',
                3
            );
            return false;
        }

        // 2. Whitelist stati validi (IMPORTANTISSIMO)
        $allowedStatuses = [
            'pending',
            'label_created',
            'picked_up',
            'in_transit',
            'out_for_delivery',
            'delivered',
            'failed',
            'cancelled',
            'returned'
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Stato non valido: ' . $status,
                3
            );
            return false;
        }

        // 3. Sanitizzazione extra fields (anti injection logica)
        $cleanExtra = [];
        foreach ($extra as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            // whitelist base chiavi consentite (opzionale ma consigliato)
            if (in_array($key, ['tracking_number', 'tracking_url', 'api_shipment_id', 'shipped_at', 'delivered_at'])) {
                $cleanExtra[$key] = pSQL($value);
            }
        }

        // 4. Dati finali
        $data = array_merge(
            ['status' => pSQL($status)],
            $cleanExtra
        );

        try {
            // 5. Update DB
            $result = Db::getInstance()->update(
                'spedisciqui_shipments',
                $data,
                '`id` = ' . (int) $idShipment,
                1
            );

            if (!$result) {
                PrestaShopLogger::addLog(
                    '[SpedisciQui] Update fallito per shipment ID: ' . $idShipment,
                    3
                );
                return false;
            }

            return true;
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Errore updateShipmentStatus: ' . $e->getMessage(),
                3
            );

            return false;
        }
    }

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

        return is_array($rows) ? $rows : [];
    }


    // ===============================================
    // CREAZIOEN SHIPMENT
    // =================================================

    public function createShipment(array $data): int|false
    {
        $insert = [
            'id_order'               => (int)   ($data['id_order'] ?? 0),
            'id_shop'                => (int)   ($data['id_shop'] ?? 1),
            'id_spedisciqui_carrier' => !empty($data['id_spedisciqui_carrier'])
                ? (int) $data['id_spedisciqui_carrier']
                : null,
            'shipment_type'          => pSQL($data['shipment_type'] ?? 'outbound'),
            'carrier_code'           => pSQL($data['carrier_code'] ?? ''),
            'service_code'           => pSQL($data['service_code'] ?? ''),
            'status'                 => pSQL($data['status'] ?? 'pending'),
            'delivery_firstname'     => pSQL($data['delivery_firstname'] ?? ''),
            'delivery_lastname'      => pSQL($data['delivery_lastname'] ?? ''),
            'delivery_address1'      => pSQL($data['delivery_address1'] ?? ''),
            'delivery_address2'      => pSQL($data['delivery_address2'] ?? ''),
            'delivery_postcode'      => pSQL($data['delivery_postcode'] ?? ''),
            'delivery_city'          => pSQL($data['delivery_city'] ?? ''),
            'delivery_country_iso'   => pSQL($data['delivery_country_iso'] ?? ''),
            'weight'                 => (float) ($data['weight'] ?? 0),
            'length'                 => (float) ($data['length'] ?? 0),
            'width'                 => (float) ($data['width'] ?? 0),
            'height'                 => (float) ($data['height'] ?? 0),
            'shipping_cost'          => (float) ($data['shipping_cost'] ?? 0),
            'shipping_currency'      => pSQL($data['shipping_currency'] ?? 'EUR'),
        ];

        $result = Db::getInstance()->insert('spedisciqui_shipments', $insert);

        if (!$result) {
            PrestaShopLogger::addLog(
                sprintf(
                    '[SpedisciQui] ShipmentRepository::createShipment — Insert fallito | Order #%d',
                    (int) ($data['id_order'] ?? 0)
                ),
                3,
                null,
                'Order',
                (int) ($data['id_order'] ?? 0),
                true
            );
            return false;
        }

        return (int) Db::getInstance()->Insert_ID();
    }

    // ===============================================
    // AGGIORNA TRACKING 
    // =================================================
    public function updateTracking(int $id, string $trackingNumber, string $trackingUrl = ''): bool
    {
        return Db::getInstance()->update(
            'spedisciqui_shipments',
            [
                'tracking_number' => pSQL($trackingNumber),
                'tracking_url'    => pSQL($trackingUrl),
                'status'          => 'label_created',
                'label_generated' => 1,
            ],
            '`id` = ' . (int) $id
        );
    }

    // ===============================================
    // AGGIORNAMENTO STATS
    // =================================================
    public function updateStatus(int $id, string $status, ?string $datetimeField = null): bool
    {
        $updateData = ['status' => pSQL($status)];

        // Se lo status ha una data dedicata, la aggiorna contestualmente
        $dateMap = [
            'picked_up'   => 'shipped_at',
            'in_transit'  => 'shipped_at',
            'delivered'   => 'delivered_at',
        ];

        if ($datetimeField !== null) {
            $updateData[$datetimeField] = date('Y-m-d H:i:s');
        } elseif (isset($dateMap[$status])) {
            $updateData[$dateMap[$status]] = date('Y-m-d H:i:s');
        }

        return Db::getInstance()->update(
            'spedisciqui_shipments',
            $updateData,
            '`id` = ' . (int) $id
        );
    }
}
