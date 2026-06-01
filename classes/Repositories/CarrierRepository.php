<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierRepository
{

    private CarrierApi $api;
    private CredentialsRepositories $credentials;
    private spedisciquishipping $module;


    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(
        CarrierApi $api,
        CredentialsRepositories $credentials,
        spedisciquishipping $module
    ) {
        $this->api = $api;
        $this->credentials = $credentials;
        $this->module = $module;
    }


    // ==========================================
    // RECUPERO CORRIERI DA PIATTAFORMA
    // ==========================================
    public function getCarriers(): ?array
    {
        $credentials = new CredentialServices()->getToken();

        $token       = $credentials['access_token'] ?? '';

        if (empty($token)) {
            PrestaShopLogger::addLog('[SpedisciQui] getCarriers — token mancante', 3);
            return null;
        }

        return $this->api->getCarriers($token);
    }

    // ==========================================
    // CHECK DUPLICATO PER CARRIER id
    // ==========================================
    public function getCarrierById(int $carrierId): array
    {
        $row = Db::getInstance()->getRow(
            'SELECT id_carrier, carrier_code, service_code, carrier_name,id_spedisciqui_carrier
         FROM `' . _DB_PREFIX_ . 'spedisciqui_carrier`
         WHERE id_carrier = ' . (int)$carrierId
        );

        return is_array($row) ? $row : [];
    }



    // ==========================================
    // CHECK DUPLICATO PER CARRIER CODE
    // ==========================================
    public function getCarrierByCode(string $carrierCode): array
    {
        $row = Db::getInstance()->getRow(
            'SELECT id_carrier, carrier_code, service_code, carrier_name
         FROM `' . _DB_PREFIX_ . 'spedisciqui_carrier`
         WHERE carrier_code = \'' . pSQL($carrierCode) . '\'
        '
        );

        return is_array($row) ? $row : [];
    }


    // ==========================================
    // SALVATAGGIO CORRIERI DA PIATTAFORMA su ps_carrier con associazioni
    // ==========================================
    public function saveCarrierInPS(array $carrierData): bool
    {

        // Evita duplicati
        if ($this->getCarrierByCode($carrierData['code'])) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Carrier già esistente, skip: ' . $carrierData['code'],
                1
            );
            return 0;
        }


        $carrier                    = new Carrier();
        $carrier->name              = pSQL($carrierData['name']);
        $carrier->active            = true;
        $carrier->deleted           = false;
        $carrier->shipping_handling = false;
        $carrier->range_behavior    = 0;
        $carrier->shipping_external = 1;
        $carrier->shipping_method   = 1; // per peso
        $carrier->is_module         = true;
        $carrier->external_module_name = 'spedisciquishipping';
        $carrier->need_range        = true;

        // delay per lingua
        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[(int) $lang['id_lang']] = $carrierData['service_title'] ?? $carrierData['name'];
        };

        if (!$carrier->add()) {
            $this->module->displayError($this->module->l('Errore durante la creazione del corriere'));
            return false;
        }

        // associo carrier allo shop
        $carrier->setGroups(array_column(Group::getGroups(true), 'id_group'));

        // associazione corriere a range di zona
        $zones = Zone::getZones(true);
        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }

        // creazione range di peso per ogni zona
        $rangeWeightId = $this->insertRangeWeightSafe((int)$carrier->id, 0, 999);
        if (!$rangeWeightId) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] RangeWeight fallito per carrier: ' . $carrier->id,
                3
            );
            return -1;
        }

        foreach ($zones as $zona) {
            Db::getInstance()->insert(
                'delivery',
                [
                    'id_carrier' => $carrier->id,
                    'id_range_weight' => $rangeWeightId,
                    'id_range_price' => 0,
                    'id_zone' => $zona['id_zone'],
                    'price' => 0,
                ]
            );
        }


        // 5. Tax rules group per ogni shop 
        $shops = Shop::getShops(true);
        $idModule = (int)Module::getModuleIdByName('spedisciquishipping');

        foreach ($shops as $shop) {

            $idShop = (int)$shop['id_shop'];

            // carrier_shop
            Db::getInstance()->insert(
                'carrier_shop',
                ['id_carrier' => (int)$carrier->id, 'id_shop' => $idShop],
                false,
                true,
                Db::INSERT_IGNORE
            );

            // tax roles
            Db::getInstance()->insert(
                'carrier_tax_rules_group_shop',
                [
                    'id_carrier'         => (int)$carrier->id,
                    'id_tax_rules_group' => 0,
                    'id_shop'            => (int)$shop['id_shop'],
                ],
                false,
                true,
                Db::INSERT_IGNORE
            );

            // associazione corriere a tutti gli shop
            $carrier->associateTo($shop['id_shop']);

            Db::getInstance()->insert(
                'module_carrier',
                [
                    'id_module'   => $idModule,
                    'id_shop'     => $shop['id_shop'],
                    'id_reference' => (int)$carrier->id_reference, // ← campo corretto
                ],
                false,
                true,
                Db::INSERT_IGNORE
            );
        }

        // salva anche in mapping
        $mapped = $this->saveCarrierMapping($carrier, $carrierData);

        if (!$mapped) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] saveCarrierMapping fallito per: ' . $carrierData['code'],
                3
            );
            return false;
        }

        PrestaShopLogger::addLog(
            '[SpedisciQui] Carrier creato — id: ' . $carrier->id
                . ' | ref: ' . $carrier->id_reference
                . ' | name: ' . $carrier->name,
            1
        );

        return true;
    }


    //==========================================
    // RANGE PESO PROTETTO DA DUPLICATI
    //==========================================
    private function insertRangeWeightSafe(int $idCarrier, float $from, float $to): int
    {
        $existing = Db::getInstance()->getValue(
            'SELECT id_range_weight FROM `' . _DB_PREFIX_ . 'range_weight`
             WHERE id_carrier = ' . $idCarrier . '
             AND delimiter1 = ' . $from . '
             AND delimiter2 = ' . $to
        );

        if ($existing) {
            return (int)$existing;
        }

        Db::getInstance()->insert('range_weight', [
            'id_carrier'  => $idCarrier,
            'delimiter1'  => $from,
            'delimiter2'  => $to,
        ]);

        return (int)Db::getInstance()->Insert_ID();
    }


    // ==========================================
    // SALVATAGGIO MAPPING su spedisciqui_carrier
    // ==========================================
    private function saveCarrierMapping(Carrier $carrier, array $carrierData): bool
    {
        $isPickupPoint = in_array(
            $carrierData['destination'] ?? '',
            ['pickup_point', 'fermopoint'],
            true
        ) ? 1 : 0;

        $extraData = json_encode([
            'type'        => $carrierData['type']        ?? null,
            'origin'      => $carrierData['origin']      ?? null,
            'destination' => $carrierData['destination'] ?? null,
            'logo_url'    => $carrierData['logo_url']    ?? null,
        ]);

        return (bool) Db::getInstance()->insert(
            'spedisciqui_carrier',
            [
                'id_carrier'      => (int) $carrier->id,
                'carrier_code'    => pSQL($carrierData['code']),
                'carrier_name'    => pSQL($carrierData['name']),
                'service_code'    => pSQL($carrierData['code']),
                'service_name'    => pSQL($carrierData['service_title'] ?? $carrierData['name']),
                'logo'            => pSQL($carrierData['logo_url'] ?? ''),
                'delay'           => pSQL($carrierData['delivery_days'] ?? ''),
                'is_pickup_point' => $isPickupPoint,
                'is_courier'      => 1,
                'position'        => 0,
                'is_active'       => 1,
                'extra_data'      => pSQL($extraData),
                'date_add'        => date('Y-m-d H:i:s'),
                'date_upd'        => date('Y-m-d H:i:s'),
            ],
            false,
            true,
            Db::INSERT_IGNORE
        );
    }


    // ==========================================
    // RIMOZIONE CARRIER DA PS_CARRIER E DA MAPPING
    // ==========================================
    public function removeCarrier(string $carrierCode)
    {

        $db = Db::getInstance();

        // recupero mapping da spedisciqui_carrier
        $mapping = $db->getRow(
            'SELECT `id_carrier`
        FROM `' . _DB_PREFIX_ . 'spedisciqui_carrier` ' .
                ' WHERE `carrier_code` = "' . pSQL($carrierCode) . '"'
        );

        // controllo
        if (!$mapping) {
            PrestaShopLogger::addLog('[SpedisciQui] removeCarrier — mapping non trovato per: ' . $carrierCode, 3);
            return false;
        }

        $idCarrier = (int) $mapping['id_carrier'];
        //$idCarrierReference = (int) $mapping['id_reference'];


        // update a delete 1 invece di eliminarlo 
        $db->update(
            'carrier',
            ['deleted' => 1],
            '`id_carrier` = ' . $idCarrier
        );


        //  array tabelle da pulire
        $tables = [
            'carrier_zone',
            'range_weight',
            'range_price',
            'carrier_group',
            'carrier_shop',
            'carrier_tax_rules_group_shop',
        ];

        foreach ($tables as $table) {
            $db->execute(
                'DELETE FROM `' . _DB_PREFIX_ . $table . '`
             WHERE id_carrier = ' . (int) $idCarrier
            );
        }

        // // eliminazione di module_carrier
        // $db->execute(
        //     'DELETE FROM `' . _DB_PREFIX_ . 'module_carrier`
        //  WHERE `id_reference` = ' . (int) $idCarrierReference
        // );

        // rimozione dal mapping
        $db->delete(
            'spedisciqui_carrier',
            '`carrier_code` = "' . pSQL($carrierCode) . '"'
        );

        return true;
    }


    // ==========================================
    // RIMOZIONE DI TUTTI I CARRIER PER UNINSTALL
    // ==========================================
    public function removeAllCarriers(): void
    {
        $db = Db::getInstance();

        $carriers = $db->executeS(
            'SELECT `carrier_code`
         FROM `' . _DB_PREFIX_ . 'spedisciqui_carrier`'
        );

        if (!$carriers) {
            return;
        }

        foreach ($carriers as $carrier) {

            if (!isset($carrier['carrier_code'])) {
                continue;
            }

            $this->removeCarrier($carrier['carrier_code']);
        }
    }


    // ==========================================
    // RECUPERO DI CARRIER INSTALLATI
    // ==========================================
    public function getSavedCarriers(): array
    {
        $db = Db::getInstance();

        $result = $db->executeS(
            'SELECT c.*,sc.carrier_name, sc.carrier_code, sc.service_name, sc.logo, sc.delay, sc.is_pickup_point,sc.date_add,sc.date_upd
        FROM `' . _DB_PREFIX_ . 'carrier` c
        LEFT JOIN `' . _DB_PREFIX_ . 'spedisciqui_carrier` sc 
            ON c.id_carrier = sc.id_carrier
        WHERE c.`external_module_name` = "' . pSQL($this->module->name) . '"
        AND c.`active` = 1
        AND c.`deleted` = 0'
        );

        if ($result === false) {

            PrestaShopLogger::addLog(
                '[SPEDISCIQUI] Errore recupero corrieri salvati',
                3
            );

            return [];
        }

        if (empty($result)) {

            PrestaShopLogger::addLog(
                '[SPEDISCIQUI] Nessun corriere salvato trovato',
                1
            );

            return [];
        }

        PrestaShopLogger::addLog(
            '[SPEDISCIQUI] result di recupero corrieri salvati',
            print_r($result, true),
            3
        );

        return $result;
    }


    // ==========================================
    // RECUPERO DI CARRIER CONFIGURATI
    // ==========================================

    public function getConfiguredCarrierCodes(): array
    {
        $sql = 'SELECT DISTINCT `service_code`
            FROM `' . _DB_PREFIX_ . 'spedisciqui_weight_tariffs`
            WHERE `id_shop` = ' . (int) Context::getContext()->shop->id .
            ' AND `is_active` = 1';

        $rows = Db::getInstance()->executeS($sql);

        if (!$rows) {
            return [];
        }

        return array_column($rows, 'service_code');
    }
}
