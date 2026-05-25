<?php

class CarrierChoise
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

    public function hookDisplayCarrierExtraContent($params)
    {
        // DEBUG carrier
        PrestaShopLogger::addLog(
            '[SPEDISCIQUI] displayCarrierExtraContent eseguito',
            1
        );

        if (!isset($params['carrier'])) {
            PrestaShopLogger::addLog(
                '[SPEDISCIQUI] displayCarrierExtraContent - carrier not set in params',
                3
            );
            return '';
        }

        $carrier = $params['carrier'];

        // Get real saved carriers
        $realCarriers = $this->carrierRepo->getSavedCarriers();

        if (empty($realCarriers)) {
            PrestaShopLogger::addLog(
                '[SPEDISCIQUI] displayCarrierExtraContent - no saved carriers found',
                1
            );
        }

        $savedCarriers = [];
        $prices = [];

        foreach ($realCarriers as $carrierData) {
            $savedCarriers[] = [
                'carrier_name' => $carrierData['name'],
                'carrier_code' => $carrierData['carrier_code'],
            ];
            $prices[$carrierData['carrier_code']] = 0.00; // default price
        }

        $this->context->smarty->assign([
            'savedCarriers' => $savedCarriers,
            'prices'        => $prices,
            'carrier'       => $carrier,
        ]);

        return $this->module->fetch(
            'module:spedisciquishipping/views/templates/hook/checkout/_partials/carrier_extra_content.tpl'
        );
    }
}
