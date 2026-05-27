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


    // HOOK PER VISUALIZZARE AL CHECKOUT
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

        return $this->module->fetch(
            'module:spedisciquishipping/views/templates/hook/checkout/_partials/carrier_extra_content.tpl'
        );
    }
}
