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
        $credentialService = new CredentialServices();
        $credentials       = $credentialService->getToken();

        $token       = $credentials['access_token'] ?? '';

        if (empty($token)) {
            PrestaShopLogger::addLog('[SpedisciQui] getCarriers — token mancante', 3);
            return null;
        }

        return $this->api->getCarriers($token);
    }
    // ==========================================
    // RECUPERO CORRIERI DA PIATTAFORMA -FINE
    // ==========================================







    // ==========================================
    // CHECK DUPLICATO PER CARRIER id
    // ==========================================
    public function getCarrierById(int $carrierId): array
    {

        if ($carrierId <= 0) {
            $this->log('getCarrierById: ID non valido (' . $carrierId . ')', 3);
            return [];
        }

        try {

            $sql = new DbQuery();
            $sql->select('id_carrier, carrier_code, service_code, carrier_name,id_spedisciqui_carrier')
                ->from('spedisciqui_carrier')
                ->where('`id_carrier` = ' . (int)$carrierId);

            $row = Db::getInstance()->getRow($sql);
            return is_array($row) ? $row : [];
        } catch (Exception $e) {
            $this->log('getCarrierById: eccezione — ' . $e->getMessage(), 3);
            return [];
        }
    }
    // ==========================================
    // CHECK DUPLICATO PER CARRIER id - FINE
    // ==========================================






    // ==========================================
    // CHECK DUPLICATO PER CARRIER CODE
    // ==========================================
    public function getCarrierByCode(string $carrierCode): array
    {

        if (empty($carrierCode)) {
            $this->log('getCarrierByCode: carrierCode vuoto', 3);
            return [];
        }

        try {

            $sql = new DbQuery();
            $sql->select('id_carrier, carrier_code, service_code, carrier_name')
                ->from('spedisciqui_carrier')
                ->where('`carrier_code` =\'' . pSQL($carrierCode . '\''));

            $row = Db::getInstance()->getRow($sql);

            return is_array($row) ? $row : [];
        } catch (Exception $e) {
            $this->log('getCarrierByCode: eccezione — ' . $e->getMessage(), 3);
            return [];
        }
    }
    // ==========================================
    // CHECK DUPLICATO PER CARRIER code -FINE
    // ==========================================






    // ==========================================
    // SALVATAGGIO CORRIERI DA PIATTAFORMA A PS_cARRIER E PS_SPEDISCIQUI_CARRIER -INIZIO
    // ==========================================
    public function saveCarrierInPS(array $carrierData): bool
    {

        // Validazione campi obbligatori
        if (empty($carrierData['code']) || empty($carrierData['name'])) {
            $this->log('saveCarrierInPS: campi obbligatori mancanti (code/name)', 3);
            return false;
        }

        // Evita duplicati
        if ($this->getCarrierByCode($carrierData['code'])) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Carrier già esistente, skip: ' . $carrierData['code'],
                1
            );
            return 0;
        }

        $db = Db::getInstance();

        // inizia transaction DB
        $db->execute('START TRANSACTION');

        try {

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
                $inserted = $db->insert(
                    'delivery',
                    [
                        'id_carrier' => $carrier->id,
                        'id_range_weight' => $rangeWeightId,
                        'id_range_price' => 0,
                        'id_zone' => $zona['id_zone'],
                        'price' => 0,
                    ]
                );

                if (!$inserted) {
                    throw new RuntimeException('Errore insert delivery per zona: ' . $zona['id_zone']);
                }
            }


            // 5. Tax rules group per ogni shop 
            $shops = Shop::getShops(true);
            $idModule = (int)Module::getModuleIdByName('spedisciquishipping');

            foreach ($shops as $shop) {

                $idShop = (int)$shop['id_shop'];

                // carrier_shop
                $db->insert(
                    'carrier_shop',
                    ['id_carrier' => (int)$carrier->id, 'id_shop' => $idShop],
                    false,
                    true,
                    Db::INSERT_IGNORE
                );

                // tax roles
                $db->insert(
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
                $carrier->associateTo($idShop);

                $db->insert(
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

            // Salvataggio nel mapping spedisciqui_carrier
            if (!$this->saveCarrierMapping($carrier, $carrierData)) {
                throw new RuntimeException('saveCarrierMapping fallito per: ' . $carrierData['code']);
            }

            // verificare che il COMMIT abbia effettivamente successo
            if (!$db->execute('COMMIT')) {
                throw new RuntimeException('COMMIT fallito per carrier: ' . $carrierData['code']);
            }

            PrestaShopLogger::addLog(
                '[SpedisciQui] Carrier creato — id: ' . $carrier->id
                    . ' | ref: ' . $carrier->id_reference
                    . ' | name: ' . $carrier->name,
                1
            );

            return true;
        } catch (Exception $e) {
            $db->execute('ROLLBACK');
            PrestaShopLogger::addLog(
                '[SpedisciQui] saveCarrierInPS — rollback eseguito: ' . $e->getMessage(),
                3
            );
            return false;
        }
    }
    // ==========================================
    // SALVATAGGIO CORRIERI DA PIATTAFORMA A PS_cARRIER E PS_SPEDISCIQUI_CARRIER -FONE
    // ==========================================





    //==========================================
    // RANGE PESO PROTETTO DA DUPLICATI
    //==========================================
    private function insertRangeWeightSafe(int $idCarrier, float $from, float $to): int
    {
        try {
            $sql = new DbQuery();
            $sql->select('`id_range_weight`')
                ->from('range_weight')
                ->where('`id_carrier` = ' . $idCarrier)
                ->where('`delimiter1` = ' . $from)
                ->where('`delimiter2` = ' . $to);

            $existing = Db::getInstance()->getValue($sql);

            if ($existing) {
                return (int) $existing;
            }

            Db::getInstance()->insert('range_weight', [
                'id_carrier' => $idCarrier,
                'delimiter1' => $from,
                'delimiter2' => $to,
            ]);

            return (int) Db::getInstance()->Insert_ID();
        } catch (Exception $e) {
            $this->log('insertRangeWeightSafe: eccezione — ' . $e->getMessage(), 3);
            return 0;
        }
    }
    //==========================================
    // RANGE PESO PROTETTO DA DUPLICATI - fine
    //==========================================





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
    // SALVATAGGIO MAPPING su spedisciqui_carrier - fine
    // ==========================================





    // ==========================================
    // RIMOZIONE CARRIER DA PS_CARRIER E DA MAPPING 
    // ==========================================
    public function removeCarrier(string $carrierCode)
    {

        if (empty($carrierCode)) {
            $this->log('removeCarrier: carrierCode vuoto', 3);
            return false;
        }

        $db = Db::getInstance();

        // recupero mapping da spedisciqui_carrier
        $mapping = $this->getCarrierByCode($carrierCode);

        // controllo
        if (!$mapping) {
            $this->log('[SpedisciQui] removeCarrier — mapping non trovato per: ' . $carrierCode, 3);
            return false;
        }

        $idCarrier = (int) $mapping['id_carrier'];

        $db = Db::getInstance();

        $db->execute('START TRANSACTION');

        try {
            // update a delete 1 invece di eliminarlo 
            $updated = $db->update(
                'carrier',
                ['deleted' => 1],
                '`id_carrier` = ' . $idCarrier
            );

            if (!$updated) {
                throw new RuntimeException('Impossibile soft-delete carrier id: ' . $idCarrier);
            }



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
            $deleted = $db->delete(
                'spedisciqui_carrier',
                '`carrier_code` = "' . pSQL($carrierCode) . '"'
            );

            if (!$deleted) {
                throw new RuntimeException('Impossibile rimuovere mapping per: ' . $carrierCode);
            }


            if (!$db->execute('COMMIT')) {
                throw new RuntimeException('COMMIT fallito per removeCarrier: ' . $carrierCode);
            }

            $this->log(
                '[SpedisciQui] Carrier rimosso con successo — id: ' . $idCarrier,
                1
            );

            return true;
        } catch (Exception $e) {
            $db->execute('ROLLBACK');

            $this->log(
                '[SpedisciQui] Errore critico durante il salvataggio del carrier: ' . $e->getMessage(),
                3
            );
            return false;
        }
    }
    // ==========================================
    // RIMOZIONE CARRIER DA PS_CARRIER E DA MAPPING - fine
    // ==========================================






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

        $errors = 0;

        foreach ($carriers as $carrier) {
            if (!isset($carrier['carrier_code'])) {
                continue;
            }

            // FIX: controlla il risultato e logga i fallimenti
            if (!$this->removeCarrier($carrier['carrier_code'])) {
                $errors++;
                PrestaShopLogger::addLog(
                    '[SpedisciQui] removeAllCarriers — fallito per: ' . $carrier['carrier_code'],
                    3
                );
            }
        }

        if ($errors > 0) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] removeAllCarriers completato con ' . $errors . ' errori',
                2
            );
        }
        try {
            $sql = new DbQuery();
            $sql->select('`carrier_code`')
                ->from('spedisciqui_carrier');

            $carriers = Db::getInstance()->executeS($sql);
        } catch (Exception $e) {
            $this->log('removeAllCarriers: eccezione query — ' . $e->getMessage(), 3);
            return;
        }

        if (empty($carriers)) {
            return;
        }

        $errors = 0;

        foreach ($carriers as $carrier) {
            if (empty($carrier['carrier_code'])) {
                continue;
            }

            if (!$this->removeCarrier($carrier['carrier_code'])) {
                $errors++;
                $this->log('removeAllCarriers — fallito per: ' . $carrier['carrier_code'], 3);
            }
        }

        if ($errors > 0) {
            $this->log('removeAllCarriers completato con ' . $errors . ' errori', 2);
        }
    }
    // ==========================================
    // RIMOZIONE DI TUTTI I CARRIER PER UNINSTALL  - fine
    // ==========================================







    // ==========================================
    // RECUPERO DI CARRIER INSTALLATI SU DB 
    // ==========================================
    public function getSavedCarriers(): array
    {
        try {
            $sql = new DbQuery();
            $sql->select('c.*, sc.carrier_name, sc.carrier_code, sc.service_name,
                          sc.logo, sc.delay, sc.is_pickup_point, sc.date_add, sc.date_upd')
                ->from('carrier', 'c')
                ->leftJoin(
                    'spedisciqui_carrier',
                    'sc',
                    'c.`id_carrier` = sc.`id_carrier`'
                )
                ->where('c.`external_module_name` = \'' . pSQL($this->module->name) . '\'')
                ->where('c.`active` = 1')
                ->where('c.`deleted` = 0');

            $result = Db::getInstance()->executeS($sql);

            if ($result === false) {
                $this->log('getSavedCarriers: query fallita', 3);
                return [];
            }

            return is_array($result) ? $result : [];
        } catch (Exception $e) {
            // BUG ORIGINALE: il log finale usava print_r come secondo argomento
            // (severity), causando un log silenziosamente sbagliato
            $this->log('getSavedCarriers: eccezione — ' . $e->getMessage(), 3);
            return [];
        }
    }
    // ==========================================
    // RECUPERO DI CARRIER INSTALLATI SU DB -FINE
    // ==========================================






    // ==========================================
    // RECUPERO DI CARRIER CONFIGURATI
    // ==========================================

    public function getConfiguredCarrierCodes(): array
    {
        try {
            $sql = new DbQuery();
            $sql->select('DISTINCT `service_code`')
                ->from('spedisciqui_weight_tariffs')
                ->where('`id_shop` = ' . (int) Context::getContext()->shop->id)
                ->where('`is_active` = 1');

            $rows = Db::getInstance()->executeS($sql);
            return $rows ? array_column($rows, 'service_code') : [];
        } catch (Exception $e) {
            $this->log('getConfiguredCarrierCodes: eccezione — ' . $e->getMessage(), 3);
            return [];
        }
    }
    // ==========================================
    // RECUPERO DI CARRIER CONFIGURATI - fine
    // ==========================================


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
