<?php

class ShipmentRepository
{

    private ?ShipmentServices $shipmentService = null;


    //------------------------------------------------------------
    // ------------ COSTANTI 
    //------------------------------------------------------------
    private const ALLOWED_STATUSES = [
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

    private const ALLOWED_EXTRA_FIELDS = [
        'tracking_number',
        'tracking_url',
        'api_shipment_id',
        'remote_shipment_id',
        'label_path',
        'label_generated',
        'error_message',
        'shipped_at',
        'delivered_at',
    ];

    private const DATE_MAP = [
        'picked_up'  => 'shipped_at',
        'in_transit' => 'shipped_at',
        'delivered'  => 'delivered_at',
    ];


    //==========================================
    // COSTRUTTORE
    //==========================================
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
            $sql->select('id, 
            id_order, 
            status, 
            carrier_code, 
            service_code,
            weight,
            shipping_cost,
            tracking_number,
            label_path')
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

            // normalizza link per path
            $labelPath = $row['label_path'] ? (string)$row['label_path'] : null;

            // 4. Normalizzazione dati (opzionale ma utile)
            return [
                'id_shipment'            => (int) $row['id'],
                'id_order'      => (int) $row['id_order'],
                'status'        => (string) $row['status'],
                'carrier_code'  => $row['carrier_code'] ? (string) $row['carrier_code'] : null,
                'service_code'  => $row['service_code'] ? (string) $row['service_code'] : null,
                'weight' => $row['weight'] ? (float) $row['weight'] : null,
                'shipping_cost' => $row['shipping_cost'] ? (string) $row['shipping_cost'] : null,
                'tracking_number' => $row['tracking_number'] ? (string) $row['tracking_number'] : null,
                'label_path' => $labelPath,
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
    // RECUPERO SHIPMENT DA ID - fine
    // ============================================








    //==================================================
    //RECUPERA TUTTI GLI SHIPMENTS
    //====================================================

    public function getShipments(
        int    $idShop = 1,
        string $statusFilter = '',
        int    $limit = 50,
        int    $offset = 0
    ): array {

        $limit  = max(1, min($limit, 200));
        $offset = max(0, $offset);

        if ($idShop <= 0) {
            $this->log('getShipments: idShop non valido (' . $idShop . ')', 3);
            return [];
        }

        if ($statusFilter !== '' && !in_array($statusFilter, self::ALLOWED_STATUSES, true)) {
            $this->log('getShipments: statusFilter non valido (' . $statusFilter . ')', 3);
            return [];
        }

        try {
            $db = Db::getInstance();

            $query = new DbQuery();
            $query->select('
            sh.`id`                  AS id_shipment,
            sh.`id_order`,
            sh.`id_spedisciqui_carrier`,
            sh.`carrier_code`,
            sh.`service_code`,            
            sh.`tracking_number`,
            sh.`label_path`,
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
        } catch (Exception $e) {
            $this->log('Errore getShipments: ' . $e->getMessage(), 3);
            return [];
        }
    }






    // ===============================================
    // CREAZIOEN SHIPMENT
    // =================================================

    public function createShipment(array $data): int|false
    {

        // Validazione preliminare PRIMA di aprire la transazione
        $idOrder = (int) ($data['id_order'] ?? 0);
        if ($idOrder <= 0) {
            $this->log('createShipment: id_order non valido', 3);
            return false;
        }

        $idShop = (int) ($data['id_shop'] ?? 1);
        if ($idShop <= 0) {
            $this->log('createShipment: id_shop non valido', 3);
            return false;
        }

        $status = $data['status'] ?? 'pending';
        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            $this->log('createShipment: status non valido (' . $status . ')', 3);
            return false;
        }

        $db = Db::getInstance();

        try {
            $db->execute('START TRANSACTION');


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
                'width'                  => (float) ($data['width'] ?? 0),
                'height'                 => (float) ($data['height'] ?? 0),
                'shipping_cost'          => (float) ($data['shipping_cost'] ?? 0),
                'shipping_currency'      => pSQL($data['shipping_currency'] ?? 'EUR'),
                'insurance_enabled'      => pSQL($data['insurance_enabled'] ?? 0),
                'insurance_value'        => pSQL($data['insurance_value'] ?? 0)
            ];

            $result = $db->insert('spedisciqui_shipments', $insert);

            if (!$result) {
                $db->execute('ROLLBACK');
                $this->log('createShipment: insert fallito | Order #' . $idOrder, 3, 'Order', $idOrder);
                return false;
            }

            $newId = (int) $db->Insert_ID();

            if ($newId < 0) {
                $db->execute('ROLLBACK');
                $this->log('createShipment: InsertId non valido dopo insert| Order #' . $idOrder, 3);
                return false;
            }

            $db->execute('COMMIT');
            return $newId;
        } catch (Exception $e) {
            $db->execute('ROLLBACK');
            $this->log('createShipment: eccezione — ' . $e->getMessage(), 3, 'Order', $idOrder);
            return false;
        }
    }
    // ===============================================
    // CREAZIOEN SHIPMENT - FINE
    // =================================================



    // ===============================================
    // AGGIORNA ASSICURAZIONE "INSURANCE" 
    // ===============================================
    public function updateInsurance(
        int $id,
        int $insurance_enabled = 0,
        float $insurance_value = 0.00
    ) {

        PrestaShopLogger::addLog('entrato in updateInsurance');

        if ($id <= 0) {
            $this->log('updateInsurance: ID non valido (' . $id . ')', 3);
            return false;
        }

        if ($insurance_value < 0) {
            $this->log('updateInsurance: valore assicurazione non valido (' . $insurance_value . ')', 3);
            return false;
        }

        try {

            $result = Db::getInstance()->update(
                'spedisciqui_shipments',
                [
                    'insurance_enabled' => (int) $insurance_enabled,
                    'insurance_value' => (float)$insurance_value,
                    'date_upd' => date('Y-m-d H:i:s')
                ],
                '`id` = ' . (int) $id
            );

            if (!$result) {
                $this->log(
                    'updateInsurance: fallito il tentativo di aggiornamento per ID shipment : ' . $id,
                    3
                );
                return false;
            }

            return true;
        } catch (Exception $e) {
            $this->Log('updateInsurance: errrore - ' . $e->getMessage(), 3);
            return false;
        }
    }




    // ===============================================
    // AGGIORNA TRACKING 
    // ===============================================
    public function updateTracking(
        int $id,
        string $trackingNumber,
        ?string $labelPath,
        string $trackingUrl = ''
    ): bool {

        if ($id <= 0) {
            $this->log('updateTracking: ID non valido (' . $id . ')', 3);
            return false;
        }

        // Formato tracking: alfanumerico con trattini/underscore (adattare al corriere)
        if (!preg_match('/^[A-Za-z0-9\-_]{3,50}$/', $trackingNumber)) {
            $this->log('updateTracking: tracking_number non valido (' . $trackingNumber . ')', 3);
            return false;
        }

        // Validazione URL opzionale
        if ($trackingUrl !== '' && !filter_var($trackingUrl, FILTER_VALIDATE_URL)) {
            $this->log('updateTracking: tracking_url non valida (' . $trackingUrl . ')', 2);
            $trackingUrl = ''; // fallback sicuro invece di bloccare
        }

        try {
            $result = Db::getInstance()->update(
                'spedisciqui_shipments',
                [
                    'tracking_number' => pSQL($trackingNumber),
                    'tracking_url'    => pSQL($trackingUrl),
                    'status'          => 'label_created',
                    'label_path' => pSQL($labelPath),
                    'label_generated' => 1,
                ],
                '`id` = ' . (int) $id
            );

            if (!$result) {
                $this->log('updateTracking: update Falllito per id: ' . $id, 3);
                return false;
            }

            return true;
        } catch (Exception $e) {
            $this->log('updateTracking: eccezione — ' . $e->getMessage(), 3);
            return false;
        }
    }
    // ===============================================
    // AGGIORNA TRACKING - fine
    // =================================================




    // ===============================================
    // AGGIORNAMENTO STATS
    // ===============================================
    public function updateStatus(
        int $id,
        string $status,
        array $extra = [],
        ?string $datetimeField = null
    ): bool {

        if ($id <= 0) {
            $this->log('updateStatus: ID non valido (' . $id . ')', 3);
            return false;
        }

        if (!in_array($status, self::ALLOWED_STATUSES, true)) {
            $this->log('updateStatus: stato non valido (' . $status . ')', 3);
            return false;
        }

        // Campi extra con whitelist
        $cleanExtra = [];
        foreach ($extra as $key => $value) {
            if (is_string($key) && in_array($key, self::ALLOWED_EXTRA_FIELDS, true)) {
                $cleanExtra[$key] = pSQL((string) $value);
            }
        }


        $updateData = ['status' => pSQL($status)];

        if ($datetimeField !== null) {
            $updateData[$datetimeField] = date('Y-m-d H:i:s');
        } elseif (isset(self::DATE_MAP[$status])) {
            $updateData[self::DATE_MAP[$status]] = date('Y-m-d H:i:s');
        }

        $updateData = array_merge($updateData, $cleanExtra);


        PrestaShopLogger::addLog('updateStatus | id=' . $id . ' | status=' . $status . ' | extra=' . json_encode($extra) . ' | updateData=' . json_encode($updateData));
        try {

            $result = Db::getInstance()->update(
                'spedisciqui_shipments',
                $updateData,
                '`id` = ' . (int)$id,

            );

            PrestaShopLogger::addLog('updateStatus result=' . var_export($result, true) . ' | affected=' . Db::getInstance()->Affected_Rows() . ' | id=' . $id);

            if (!$result) {
                $this->log('updateStatus: update fallito per ID ' . $id, 3);
                return false;
            }

            return true;
        } catch (Exception $e) {
            $this->log('updateStatus: eccezione — ' . $e->getMessage(), 3);
            return false;
        }
    }
    // ===============================================
    // AGGIORNAMENTO STATS - FINE
    // ===============================================





    // ===============================================
    // HELPER PER PRESTALOGGER
    // =================================================
    private function log(
        string  $message,
        int     $severity = 3,
        string  $objectType = '',
        int     $objectId = 0
    ): void {
        PrestaShopLogger::addLog(
            '[SpedisciQui] ' . $message,
            $severity,
            null,
            $objectType ?: null,
            $objectId ?: null,
            true
        );
    }
}
