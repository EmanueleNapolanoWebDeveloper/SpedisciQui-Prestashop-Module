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

    // ==============================================
    // RECUPERO SHIPMENT DA ID
    // ============================================

    public function getShipmentById(int $idShipment): array|false
    {
        // 1. Validazione input
        if ($idShipment <= 0) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] getShipmentById: ID non valido (' . $idShipment . ')',
                3
            );
            return false;
        }

        // 2. Query sicura
        try {
            $sql = new DbQuery();
            $sql->select('id, id_order, status, carrier_code, service_code')
                ->from('spedisciqui_shipments')
                ->where('id = ' . (int) $idShipment);

            $row = Db::getInstance()->getRow($sql);

            // 3. Controllo risultato
            if (empty($row) || !is_array($row)) {
                PrestaShopLogger::addLog(
                    '[SpedisciQui] Shipment non trovato ID: ' . $idShipment,
                    2
                );
                return false;
            }

            // 4. Normalizzazione dati (opzionale ma utile)
            return [
                'id'            => (int) $row['id'],
                'id_order'      => (int) $row['id_order'],
                'status'        => (string) $row['status'],
                'carrier_code'  => $row['carrier_code'] ? (string) $row['carrier_code'] : null,
                'service_code'  => $row['service_code'] ? (string) $row['service_code'] : null,
            ];
        } catch (Exception $e) {
            // 5. Gestione errori DB
            PrestaShopLogger::addLog(
                '[SpedisciQui] Errore getShipmentById: ' . $e->getMessage(),
                3
            );

            return false;
        }
    }

    // ==============================================
    // AGGIORNAMENT STATO + OPZIONALI
    // ==============================================
    public function updateShipmentStatus(int $idShipment, string $status, array $extra = []): bool
    {
        // 1. Validazione ID
        if ($idShipment <= 0) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] updateShipmentStatus: ID non valido (' . $idShipment . ')',
                3
            );
            return false;
        }

        // 2. Whitelist stati validi (IMPORTANTISSIMO)
        $allowedStatuses = [
            'pending',
            'label_created',
            'picked_up',
            'in_transit',
            'out_for_delivery',
            'delivered',
            'failed',
            'cancelled',
            'returned'
        ];

        if (!in_array($status, $allowedStatuses, true)) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Stato non valido: ' . $status,
                3
            );
            return false;
        }

        // 3. Sanitizzazione extra fields (anti injection logica)
        $cleanExtra = [];
        foreach ($extra as $key => $value) {
            if (!is_string($key)) {
                continue;
            }

            // whitelist base chiavi consentite (opzionale ma consigliato)
            if (in_array($key, ['tracking_number', 'tracking_url', 'api_shipment_id', 'shipped_at', 'delivered_at'])) {
                $cleanExtra[$key] = pSQL($value);
            }
        }

        // 4. Dati finali
        $data = array_merge(
            ['status' => pSQL($status)],
            $cleanExtra
        );

        try {
            // 5. Update DB
            $result = Db::getInstance()->update(
                'spedisciqui_shipments',
                $data,
                '`id` = ' . (int) $idShipment,
                1
            );

            if (!$result) {
                PrestaShopLogger::addLog(
                    '[SpedisciQui] Update fallito per shipment ID: ' . $idShipment,
                    3
                );
                return false;
            }

            return true;
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Errore updateShipmentStatus: ' . $e->getMessage(),
                3
            );

            return false;
        }
    }
}
