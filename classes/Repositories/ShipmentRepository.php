<?php

class ShipmentRepository
{


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
