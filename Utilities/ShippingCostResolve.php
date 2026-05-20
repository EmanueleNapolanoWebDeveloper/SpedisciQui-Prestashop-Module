<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShippingCostResolve
{
    private CarrierModule $module;
    private SpedisciQuiApi $api;
    private SenderRepository $senderRepo;
    private PackageRepository $packageRepo;

    /** @var array<int, float|false> Cache per evitare chiamate API doppie */
    private array $cache = [];

    public function __construct(CarrierModule $module)
    {
        $this->module = $module;
        $this->api    = new SpedisciQuiApi();
        $this->senderRepo  = new SenderRepository();
        $this->packageRepo = new PackageRepository();
    }

    // ricavo Carrier Id
    private function resolveCarrierId(Cart $cart): int
    {
        // 1. Recupera l'id_carrier dalla tabella PS tramite external_module_name
        $id = (int) Db::getInstance()->getValue(
            'SELECT id_carrier FROM ' . _DB_PREFIX_ . 'carrier'
                . ' WHERE external_module_name = "' . pSQL($this->module->name) . '"'
                . ' AND deleted = 0 AND active = 1'
        );

        if ($id > 0) {
            PrestaShopLogger::addLog('Id Entrrato: ' . $id);
            return $id;
        }

        // 2. Fallback: carrier salvato nel cart
        $id = (int) Db::getInstance()->getValue(
            'SELECT id_carrier FROM ' . _DB_PREFIX_ . 'cart'
                . ' WHERE id_cart = ' . (int) $cart->id
        );

        if ($id > 0) {
            PrestaShopLogger::addLog('[SQ] DB fallback carrier dal cart: ' . $id);
            return $id;
        }

        return 0;
    }

    // ricavo CarrierCode dal mapping
    private function resolveCarrierCode(int $carrierId): string|false
    {
        $carrier     = new Carrier($carrierId);
        $referenceId = (int) $carrier->id_reference;

        if ($referenceId === 0) {
            return false;
        }

        $response = Db::getInstance()->getValue(
            'SELECT serviceId FROM ' . _DB_PREFIX_ . 'spedisciqui_carrier_mapping'
                . ' WHERE carrierReferenceId = ' . $referenceId . ' AND isActive = 1'
        ) ?: false;

        PrestaShopLogger::addLog('SERVICE RICHIAMATO: ' . $response);

        return $response;
    }

    // costruzione payload
    private function buildPayload(Cart $cart): array
    {
        $address = new Address($cart->id_address_delivery);
        $country = new Country($address->id_country);
        $shopId  = Context::getContext()->shop->id;
        $package = $this->packageRepo->getPackage($shopId);
        $sender  = $this->senderRepo->getSender($shopId);

        return [
            'sender'    => $sender,
            'recipient' => [
                'name'    => $address->firstname,
                'surname' => $address->lastname,
                'address' => $address->address1,
                'city'    => $address->city,
                'zip'     => $address->postcode,
                'country' => $country->iso_code,
                'prov'    => $address->id_state
                    ? (new State($address->id_state))->iso_code
                    : '',
                'phone'   => $address->phone ?: $address->phone_mobile,
            ],
            'package' => [
                'weight' => (float) $cart->getTotalWeight(),
                'height' => (float) ($package['height'] ?? 0),
                'length' => (float) ($package['length'] ?? 0),
                'depth'  => (float) ($package['depth']  ?? 0),
            ],
            'insurance'              => false,
            'insurance_value'        => 0.0,
            'cash_on_delivery'       => false,
            'cash_on_delivery_value' => 0.0,
        ];
    }

    // filtro ed estrazione prezzo corriere
    private function extractPrice(array $prices, string $carrierCode): float|false
    {
        foreach ($prices as $price) {
            if (isset($price['carrier_code']) && $price['carrier_code'] === $carrierCode) {
                return (float) $price['price'];
            }
        }

        return false;
    }

    // entry point
    public function resolve(Cart $cart): float|false
    {
        $cartId = (int) $cart->id;
        if (array_key_exists($cartId, $this->cache)) {
            return $this->cache[$cartId];
        }

        $carrierId = $this->resolveCarrierId($cart);

        // LOG TEMPORANEO DI DEBUG
        PrestaShopLogger::addLog('[SQ DEBUG] cart_id=' . $cartId . ' id_carrier=' . $cart->id_carrier);
        PrestaShopLogger::addLog('[SQ DEBUG] carrierId resolved=' . $carrierId);

        $carrierCode = $this->resolveCarrierCode($carrierId);
        PrestaShopLogger::addLog('[SQ DEBUG] carrierCode=' . ($carrierCode ?: 'NULL'));

        if (!$carrierCode) {
            return $this->cache[$cartId] = false;
        }

        $payload  = $this->buildPayload($cart);
        $response = $this->api->request('POST', '/api/calculateshipping', $payload);

        if (!$response || !isset($response['prices']) || !is_array($response['prices'])) {
            return $this->cache[$cartId] = false;
        }

        return $this->cache[$cartId] = $this->extractPrice($response['prices'], $carrierCode);
    }
}
