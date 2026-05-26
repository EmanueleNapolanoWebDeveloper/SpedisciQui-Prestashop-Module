<?php

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
        if (!isset($params['carrier'])) {
            return '';
        }

        $carrier          = $params['carrier'];
        $currentCarrierId = (int)($carrier['id'] ?? 0);
        $realCarriers     = $this->carrierRepo->getSavedCarriers();

        if (empty($realCarriers)) {
            return '';
        }

        try {
            $prices = (new CarrierApi(new ApiClient(new ConfigRepositories())))->getPriceFromApi();
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog('[SPEDISCIQUI] getPriceFromApi: ' . $e->getMessage(), 3);
            $prices = [];
        }

        $savedCarriers = [];
        $isOurCarrier  = false;

        foreach ($realCarriers as $carrierData) {
            if ((int)$carrierData['id_carrier'] === $currentCarrierId) {
                $isOurCarrier = true;
            }
            $savedCarriers[] = [
                'carrier_name' => $carrierData['service_name'],
                'carrier_code' => $carrierData['carrier_code'],
            ];
        }

        if (!$isOurCarrier) {
            return '';
        }

        $savedCarriers = array_values(
            array_filter($savedCarriers, fn($sc) => isset($prices[$sc['carrier_code']]))
        );

        $this->context->smarty->assign([
            'savedCarriers' => $savedCarriers,
            'prices'        => $prices,
            'carrier'       => $carrier,
        ]);

        return $this->module->fetch(
            'module:spedisciquishipping/views/templates/hook/checkout/_partials/carrier_extra_content.tpl'
        );
    }


    public function hookactionCheckoutRender($params)
    {
        $cartId = $this->context->cart->id;

        if (Cache::getInstance()->isStored('shipping_'.$cartId)) {
            return;
        }

        // costruzione payload
        $price = new CarrierApi(new ApiClient(new ConfigRepositories()))->getPriceFromApi();

        if (!empty($price)) {
            cache::getInstance()->store('shipping_'.$cartId , $price);
        }
    }
}
