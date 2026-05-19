<?php

error_log('__DIR__ = ' . __DIR__);

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

require __DIR__ . '/Utilities/SpedisciQuiApi.php';
require __DIR__ . '/Utilities/DatabaseManager.php';
require __DIR__ . '/Repositories/PackageRepository.php';
require __DIR__ . '/Repositories/SenderRepository.php';
require __DIR__ . '/views/FormRender.php';
require __DIR__ . '/Utilities/ContentHandler.php';

class spedisciquishipping extends CarrierModule
{
    protected SpedisciQuiApi $api;
    protected SenderRepository $senderRepo;
    protected PackageRepository $packageRepo;
    protected DatabaseManager $db;


    public function __construct()
    {
        $this->name = 'spedisciquishipping';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'Emanuele';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'SpedisciQui Shipping Primo';
        $this->description = 'Modulo spedizioni customizzato';
        $this->api = new SpedisciQuiApi();
        $this->packageRepo = new PackageRepository();
        $this->senderRepo = new SenderRepository();
        $this->db = new DatabaseManager();
    }

    public function install(): bool
    {
        try {
            $parentInstall = parent::install();

            $dbResult = $this->db->createAllTableOnInstallation();

            return $parentInstall && $dbResult
                && Configuration::updateValue('SPEDISCIQUI_ACCESS_TOKEN', null)
                && Configuration::updateValue('SPEDISCIQUI_SETUP_STEP', null);
        } catch (\Exception $e) {
            error_log('INSTALL ERROR: ' . $e->getMessage());
            return false;
        }
    }

    public function uninstall(): bool
    {
        return parent::uninstall()
            && $this->db->deleteAllModuleCarrier()
            && $this->db->dropAllSpedisciQuiTables()
            && Configuration::deleteByName('SPEDISCIQUI_ACCESS_TOKEN')
            && Configuration::deleteByName('SPEDISCIQUI_SETUP_STEP');
    }

    public function getSmarty(): Smarty
    {
        return $this->context->smarty;
    }

    public function getContent()
    {
        $handler = new ContentHandler($this);
        return $handler->handle();
    }

    public function getOrderShippingCost($params, $shippingCost)
    {
        $cart = $params;

        // CORRIER
        $currentCarrierId = ($this->id_carrier ?? 0);
        $carrier          = new Carrier($currentCarrierId);
        $referenceId      = $carrier->id_reference;

        // Recupera il codice API dal mapping
        $carrierCode = Db::getInstance()->getValue(
            'SELECT serviceId 
            FROM `' . _DB_PREFIX_ . 'spedisciqui_carrier_mapping`
            WHERE carrierReferenceId = ' . (int) $referenceId . '
            AND isActive = 1'
        );

        if (!$carrierCode) {
            return false;
        }

        // DESTINATARIO
        $address = new Address($cart->id_address_delivery);
        $country = new Country($address->id_country);
        $recipient = [
            'name' => $address->firstname,
            'surname' => $address->lastname,
            'address' => $address->address1,
            'city' => $address->city,
            'zip' => $address->postcode,
            'country' => $country->iso_code,
            'prov' => $address->id_state ? (new State($address->id_state))->iso_code : '',
            'phone' => $address->phone ?: $address->phone_mobile,
        ];

        // MITTENTE
        $senderRepo = new SenderRepository();
        $sender = $senderRepo->getSender(Context::getContext()->shop->id);

        // DIMENSIONI PACCO
        $packageRepo = new PackageRepository();
        $package = $packageRepo->getPackage(Context::getContext()->shop->id);

        // PESO CARRELLO
        $weight = $cart->getTotalWeight();

        // VALORE ORDINE
        $orderValue = $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS);

        // PAYLOAD PER API
        $payload = [
            'sender' => $sender,
            'recipient' => $recipient,
            'package' => [
                'weight' => $weight,
                'height' => $package['height'] ?? 0,
                'length' => $package['length'] ?? 0,
                'depth' => $package['depth'] ?? 0,
            ],
            'insurance' => false,
            'insurance_value' => 0.0,
            'cash_on_delivery' => false,
            'cash_on_delivery_value' => 0.0,
        ];

        // chiamata api TEST DI VERIFICA REQUEST
        $api = new SpedisciQuiApi();
        $response = $api->request('POST', '/api/calculateshipping', $payload);

        if (!$response || !isset($response['prices']) || !is_array($response['prices'])) {
            return false;
        }

        // ── FILTRA IL PREZZO PER QUESTO CARRIER ──────────────────
        foreach ($response['prices'] as $priceData) {
            if ($priceData['carrier_code'] === $carrierCode) {
                return (float) $priceData['price'];
            }
        }

        return false;
    }


    // public function getOrderShippingCost($params, $shipping_cost)
    // {
    //     return 7.50;
    // }

    public function getOrderShippingCostExternal($params): float|false
    {
        return $this->getOrderShippingCost($params, 0);
    }
}
