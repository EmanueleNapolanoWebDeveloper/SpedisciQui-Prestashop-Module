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


    // ==============================================
    // CALCOLO DIMENSIONI PACCO
    // ============================================
    public function calculatePackageDimensions(Cart $cart, array $defaultPackage): array
    {
        $products = $cart->getProducts();

        $totalLength = 0.0;
        $totalWidth  = 0.0;
        $totalHeight = 0.0;

        if (empty($products)) {
            return [
                'length' => (float) ($defaultPackage['length'] ?? 0),
                'width'  => (float) ($defaultPackage['width']  ?? 0),
                'height' => (float) ($defaultPackage['height'] ?? 0),
            ];
        }

        foreach ($products as $product) {
            $qty = (int) ($product['cart_quantity'] ?? 1);

            // PS salva le dimensioni in ps_product come width, height, depth
            // depth in PS = lunghezza fisica del pacco
            $pLength = (float) ($product['depth']  ?? 0); // PS chiama "depth" la lunghezza
            $pWidth  = (float) ($product['width']  ?? 0);
            $pHeight = (float) ($product['height'] ?? 0);

            // Fallback al package default se il prodotto non ha dimensioni
            if ($pLength <= 0) {
                $pLength = (float) ($defaultPackage['length'] ?? 0);
            }
            if ($pWidth <= 0) {
                $pWidth = (float) ($defaultPackage['width'] ?? 0);
            }
            if ($pHeight <= 0) {
                $pHeight = (float) ($defaultPackage['height'] ?? 0);
            }

            // length/width: prendo il massimo (il pacco deve contenere il prodotto più grande)
            $totalLength = max($totalLength, $pLength);
            $totalWidth  = max($totalWidth,  $pWidth);

            // height: sommo per la quantità (prodotti impilati)
            $totalHeight += $pHeight * $qty;
        }

        return [
            'length' => round($totalLength, 2),
            'width'  => round($totalWidth,  2),
            'height' => round($totalHeight, 2),
        ];
    }


    
}
