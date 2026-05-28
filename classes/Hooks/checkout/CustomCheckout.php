<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class CustomCheckout
{

    private spedisciquishipping $module;
    private Context $context;
    private CarrierRepository $carrierRepo;

    public function __construct(
        spedisciquishipping $module,
        CarrierRepository $carrierRepo
    ) {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->carrierRepo = $carrierRepo;
    }

    //========================================
    // HOOK PER VISUALIZZARE AL CHECKOUT
    //========================================

    public function hookDisplayCarrierExtraContent($params)
    {

        PrestaShopLogger::addLog('Inizio display carrier');

        if (!isset($params['carrier'])) {
            return '';
        }

        $carrier          = $params['carrier'];
        $currentCarrierId = (int)($carrier['id'] ?? 0);
        $realCarriers     = $this->carrierRepo->getSavedCarriers();

        if (empty($realCarriers)) {
            PrestaShopLogger::addLog('Non passa realCarriers');
            return '';
        }

        // Ricerca carrier da lista
        $matchedCarrier = null;

        foreach ($realCarriers as $carrierData) {
            if ((int)$carrierData['id_carrier'] === $currentCarrierId) {
                $matchedCarrier = $carrierData;
                break;
            }
        }

        // se ci sono carrier non del nostro modulo
        if (!$matchedCarrier) {
            return '';
        }

        // recupera prezzi solo se necessari
        try {
            $prices = (new CarrierApi(new ApiClient(new ConfigRepositories)))->getPriceFromApi();
        } catch (Throwable $e) {
            PrestaShopLogger::addLog('[SPEDISCIQUI] getPriceFromApi: ' . $e->getMessage(), 3);
            $prices = [];
        }

        PrestaShopLogger::addLog(
            '[SPEDISCIQUI] prices dump: ' . json_encode($prices),
            1
        );

        $carrierCode = $matchedCarrier['carrier_code'];

        // dati del carrier
        $carrierPrice = $prices[$carrierCode]['price'] ?? null;
        $insurancePrice = $prices[$carrierCode]['insurance'] ?? null;
        $insuranceRequired = $prices[$carrierCode]['insurance_required'] ?? false;

        // ritorno valori al front
        $this->context->smarty->assign([
            'spqCarrier'           => $matchedCarrier,
            'spqCarrierPrice'      => $carrierPrice !== null
                ? Tools::displayPrice($carrierPrice)
                : null,
            'spqInsurancePrice'    => $insurancePrice !== null
                ? Tools::displayPrice($insurancePrice)
                : null,
            'spqInsuranceRequired' => $insuranceRequired,
            'spqCarrierCode'       => $carrierCode,
            'carrier'              => $carrier,
        ]);

        PrestaShopLogger::addLog(
            '[SPEDISCIQUI] carrier=' . $carrierCode .
                ' price=' . var_export($carrierPrice, true) .
                ' insurance=' . var_export($insurancePrice, true)
        );

        // Inietto CSS
        $this->context->controller->registerStylesheet(
            'spedisciqui_carrier',
            'modules/spedisciquishipping/views/css/checkout_carrier.css',
            ['media' => 'all', 'priority' => 150]
        );

        return $this->module->fetch(
            'module:spedisciquishipping/views/templates/hook/checkout/_partials/carrier_extra_content.tpl'
        );
    }


    //========================================
    // HOOK PER ACTIONPROCESSCARRIER
    //========================================

    public function hookActionCarrierProcess($params)
    {
        $db = Db::getInstance();
        $cart = $params['cart'];

        // recupero id carrier da cart
        $idCarrier = $cart->id_carrier;

        // controllo se dati dal modulo ci sono
        if (Tools::isSubmit('spedisciqui_service')) {

            // recupero array servizi
            $services = Tools::getValue('spedisciqui_service');

            $serviceCode = null;

            if (is_array($services) && isset($services[$idCarrier])) {
                $serviceCode = pSQL($services[$idCarrier]);
            }

            // insurance checkbox
            $insurance = Tools::getValue('spedisciqui_insurance');

            $acceptedInsurance = is_array($insurance) && isset($insurance[$idCarrier]);

            // salvo dati in tabella
            $db->insert(
                'spedisciqui_cart',
                [
                    'id_cart' => (int) $cart->id,
                    'id_carrier' => (int) $idCarrier,
                    'service_code' => pSQL($serviceCode),
                    'has_insurance' => (int) $insurance,
                ],
                false,
                true,
                Db::REPLACE
            );
        }
    }


    //========================================
    // HOOK PER FILTRARE DELIVERY
    //========================================
    public function hookActionFilterDeliveryOptionList($params)
    {
        $list = $params['delivery_option_list'];
        $cart = $params['cart'];

        $idAddress = (int) $cart->id_address_delivery;

        foreach ($list[$idAddress] as $key => $deliveryOption) {

            foreach ($deliveryOption['carrier_list'] as $carrierId => $carrier) {
                $hasService = Db::getInstance()->getValue(
                    'SELECT service_code
                 FROM ' . _DB_PREFIX_ . 'spedisciqui_cart
                 WHERE id_cart = ' . (int)$cart->id
                );

                if (!$hasService) {
                    unset($list[$idAddress][$key]);
                }
            }
        }
    }
}
