<?php

class ShippingServices
{

    private CarrierRepository $carrierRepo;
    private CarrierServices $carrierServices;

    public function __construct(
        CarrierRepository $carrierRepo,
        CarrierServices $carrierServices
    ) {
        $this->carrierRepo = $carrierRepo;
        $this->carrierServices = $carrierServices;
    }

    public function getRateShippingCost(Cart $cart, string $carrierCode): float|false
    {
        try {

            $totalWeight = (float) $cart->getTotalWeight();

            // FIX: passo carrierCode
            $carrier = $this->carrierRepo->getCarrierByCode($carrierCode);

            if (empty($carrier)) {
                return false;
            }

            $carrierId = (int) $carrier['id_carrier'];

            $tariff = $this->carrierServices->getApplicableTariff(
                $carrierId,
                $carrierCode,
                $totalWeight
            );

            if (!$tariff) {
                return false;
            }

            return (float) $tariff['tariff'];
        } catch (Exception $e) {

            PrestaShopLogger::addLog(
                '[SpedisciQui] ShippingServices error: ' . $e->getMessage(),
                3,
                null,
                'SpedisciQuiShipping'
            );

            return false;
        }
    }
}
