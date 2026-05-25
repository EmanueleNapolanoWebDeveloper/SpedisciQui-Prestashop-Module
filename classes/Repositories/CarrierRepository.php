<?php

class CarrierRepository
{

    private ApiClient $api;
    private CredentialsRepositories $credentials;
    private spedisciquishipping $module;


    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(
        ApiClient $api,
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
        $credentials = $this->credentials->get();
        $token       = $credentials['access_token'] ?? '';

        if (empty($token)) {
            PrestaShopLogger::addLog('[SpedisciQui] getCarriers — token mancante', 3);
            return null;
        }

        return $this->api->getCarriers($token);
    }


    // ==========================================
    // SALVATAGGIO CORRIERI DA PIATTAFORMA su ps_carrier con associazioni
    // ==========================================
    public function saveCarrierInPS(array $carrierData): bool
    {

        $serviceId = Tools::getValue('service_code');
        $serviceName = Tools::getValue('service_name');
        $serviceCode = Tools::getValue('service_code');

        if (!$serviceId || !$serviceName) {
            return $this->module->displayError($this->module->l('Dari Corriere mancanti'));
        }

        // impostazioni per tabella ps_carrier

        $carrier                    = new Carrier();
        $carrier->name              = pSQL($carrierData['name']);
        $carrier->active            = true;
        $carrier->deleted           = false;
        $carrier->shipping_handling = false;
        $carrier->range_behavior    = 0;
        $carrier->shipping_method   = 2; // per peso
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
        $rangeWeight = new RangeWeight();
        $rangeWeight->id_carrier = $carrier->id;
        $rangeWeight->delimiter1 = 0;
        $rangeWeight->delimiter2 = 999;
        $rangeWeight->add();

        foreach ($zones as $zona) {
            Db::getInstance()->insert(
                'delivery',
                [
                    'id_carrier' => $carrier->id,
                    'id_range_weight' => $rangeWeight->id,
                    'id_range_price' => 0,
                    'id_zone' => $zona['id_zone'],
                    'price' => 0,
                ]
            );
        }

        // associazione corriere a tutti gli shop
        $shops = Shop::getShops(true);
        foreach ($shops as $shop) {
            $carrier->associateTo($shop['id_shop']);
        }

        // salva anche in mapping
        $this->saveCarrierMapping($carrier, $carrierData);

        $this->module->displayConfirmation(
            $this->module->l('Corriere "' . $serviceName . '"aggiunto correttamente')
        );

        return true;
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
            ]
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
        FROM `' . _DB_PREFIX_ . 'spedisciqui_carrier `
        WHERE `carrier_code` = "' . pSQL($carrierCode) . '"'
        );

        // controllo
        if (!$mapping) {
            PrestaShopLogger::addLog('[SpedisciQui] removeCarrier — mapping non trovato per: ' . $carrierCode, 3);
            return false;
        }

        $idCarrier = (int) $mapping['id_carrier'];

        // update a delete 1 invece di eliminarlo 
        $db->update(
            'carrier',
            ['deleted' => 1],
            '`id_carrier` = ' . $idCarrier
        );


        // pulizia corriere da gruppi,zone,range,ecc.
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'carrier_zone`  WHERE `id_carrier` = ' . $idCarrier);
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'range_weight`  WHERE `id_carrier` = ' . $idCarrier);
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'range_price`   WHERE `id_carrier` = ' . $idCarrier);
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'delivery`      WHERE `id_carrier` = ' . $idCarrier);
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'carrier_group` WHERE `id_carrier` = ' . $idCarrier);
        $db->execute('DELETE FROM `' . _DB_PREFIX_ . 'carrier_shop`  WHERE `id_carrier` = ' . $idCarrier);

        // rimozione dal mapping
        $db->delete(
            'spedisciqui_carrier',
            '`carrier_code` = "' . pSQL($carrierCode) . '"'
        );

        return true;
    }
}
