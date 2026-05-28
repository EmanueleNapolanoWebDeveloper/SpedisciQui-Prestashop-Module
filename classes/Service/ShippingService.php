<?php

class ShippingServices
{
    private CarrierRepository $carrierRepo;
    private CarrierServices $carrierServices;

    /**
     * Impostato dal modulo principale prima di invocare getRateShippingCost().
     * Corrisponde all'id_carrier attivo nel contesto di getOrderShippingCost().
     *
     * @var int
     */
    public int $id_carrier = 0;

    public function __construct(
        CarrierRepository $carrierRepo,
        CarrierServices $carrierServices
    ) {
        $this->carrierRepo    = $carrierRepo;
        $this->carrierServices = $carrierServices;
    }

    //=================================================
    // CLACOLA COSTO DI SPEDIZIONE TRAMITE PESO
    //=================================================
    
    public function getRateShippingCost(Cart $cart, int $idCarrier): float|false
    {
        try {
            if ($idCarrier <= 0) {
                PrestaShopLogger::addLog(
                    sprintf('[SpedisciQui] id_carrier non valido: %d | Cart #%d', $idCarrier, $cart->id),
                    3,
                    null,
                    'Cart',
                    (int) $cart->id,
                    true
                );
                return false;
            }

            $totalWeight = (float) $cart->getTotalWeight();

            $carrier = $this->carrierRepo->getCarrierById($idCarrier);

            if (empty($carrier) || empty($carrier['service_code'])) {
                PrestaShopLogger::addLog(
                    sprintf('[SpedisciQui] Carrier #%d non trovato | Cart #%d', $idCarrier, $cart->id),
                    3,
                    null,
                    'Cart',
                    (int) $cart->id,
                    true
                );
                return false;
            }

            $carrierCode = (string) $carrier['service_code'];

            $tariff = $this->carrierServices->getApplicableTariff($carrierCode, $totalWeight);

            if ($tariff === false || !isset($tariff['tariff'])) {
                return false;
            }

            $shippingCost = (float) $tariff['tariff'];

            if ($shippingCost < 0.0) {
                PrestaShopLogger::addLog(
                    sprintf('[SpedisciQui] Tariffa negativa (%.4f) | Code: %s | Cart #%d', $shippingCost, $carrierCode, $cart->id),
                    3,
                    null,
                    'Cart',
                    (int) $cart->id,
                    true
                );
                return false;
            }

            return $shippingCost;
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                sprintf(
                    '[SpedisciQui] Eccezione | Cart #%d | %s in %s:%d',
                    $cart->id,
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ),
                4,
                null,
                'Cart',
                (int) $cart->id,
                true
            );
            return false;
        }
    }
}
