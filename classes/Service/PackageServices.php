<?php

class PackageServices
{

    private PackageRepository $packRepo;


    public function __construct()
    {
        $this->packRepo = new PackageRepository;
    }


    // ================================================================
    // VALIDAZIONE
    // ================================================================
    public function validate(array $data): array
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = 'Il nome profilo è obbligatorio.';
        }
        if (empty($data['weight']) || (float) $data['weight'] <= 0) {
            $errors[] = 'Il peso deve essere maggiore di 0.';
        }
        if (empty($data['length']) || (float) $data['length'] <= 0) {
            $errors[] = 'La lunghezza deve essere maggiore di 0.';
        }
        if (empty($data['width']) || (float) $data['width'] <= 0) {
            $errors[] = 'La larghezza deve essere maggiore di 0.';
        }
        if (empty($data['height']) || (float) $data['height'] <= 0) {
            $errors[] = 'L\'altezza deve essere maggiore di 0.';
        }

        return $errors;
    }


    // ================================================================
    // PRENDI DATI DI PACKAGE PER SHIPMENT (PARCELDATA)
    // ================================================================
    public function getParcelData(\Order $order)
    {

        try {
            $products = $order->getProducts();
            $dimensionDefault = $this->packRepo->getDefault();

            if (empty($products)) {
                throw new \RuntimeException((
                    'Nessun prodotto trovato per l\' ordine #' . (int)$order->id
                ));
            }

            $weights = [];
            $lengths = [];
            $widths  = [];
            $heights = [];
            $totalWeight = 0.0;

            foreach ($products as $product) {
                $productId = (int)$product['id_product'];
                $quantity = (int)($product['product_quantity'] ?? 1);
                $productName = $product['product_name'] ?? 'Prodotto #' . $productId;


                // -- peso ----------------------------
                $weight = (float)($product['product_weight'] ?? 0);

                if ($weight <= 0) {

                    PrestaShopLogger::addLog(
                        '[SpedisciQui] Prodotto "' . $productId . '" peso a zero → default ' . $dimensionDefault['weight'] . 'kg | Ordine #' . (int)$order->id,
                        2,
                        null,
                        'Order',
                        (int)$order->id,
                        true
                    );

                    $weight = $dimensionDefault['weight'];
                }


                // ── larghezza ───────────────────────────────────────────
                $width = (float)($product['width'] ?? 0);
                if ($width <= 0) {
                    \PrestaShopLogger::addLog(
                        '[SpedisciQui] Prodotto "' . $productId . '" width a zero → default ' . $dimensionDefault['weight'] . 'cm | Ordine #' . (int)$order->id,
                        2,
                        null,
                        'Order',
                        (int)$order->id,
                        true
                    );
                    $width = $dimensionDefault['weight'];
                }

                // ── lunghezza ───────────────────────────────────────────
                $length = (float)($product['depth'] ?? 0);
                if ($length <= 0) {
                    \PrestaShopLogger::addLog(
                        '[SpedisciQui] Prodotto "' . $productId . '" length a zero → default ' . $dimensionDefault['weight'] . 'cm | Ordine #' . (int)$order->id,
                        2,
                        null,
                        'Order',
                        (int)$order->id,
                        true
                    );
                    $length = $dimensionDefault['weight'];
                }

                // ── altezza ─────────────────────────────────────────────
                $height = (float)($product['height'] ?? 0);
                if ($height <= 0) {
                    \PrestaShopLogger::addLog(
                        '[SpedisciQui] Prodotto "' . $productId . '" height a zero → default ' . $dimensionDefault['weight'] . 'cm | Ordine #' . (int)$order->id,
                        2,
                        null,
                        'Order',
                        (int)$order->id,
                        true
                    );
                    $height = $dimensionDefault['weight'];
                }

                // ── accumulo per ogni unità del prodotto ────────────────
                for ($i = 0; $i < $quantity; $i++) {
                    $weights[] = round($weight, 3);
                    $widths[]  = round($width, 2);
                    $lengths[] = round($length, 2);
                    $heights[] = round($height, 2);
                }

                $totalWeight += $weight * $quantity;
            }

            return [
                'weights'      => $weights,
                'widths'       => $widths,
                'lengths'      => $lengths,            // [10, 10, 30]
                'heights'      => $heights,            // [10, 10, 15]
                'total_weight' => round($totalWeight, 3), // 2.2
                'items_count'  => count($weights),     // 3
            ];
        } catch (\Throwable $e) {
            \PrestaShopLogger::addLog(
                '[SpedisciQui] Errore getParcelData() Ordine #' . (int)$order->id . ': ' . $e->getMessage(),
                3,
                null,
                'Order',
                (int)$order->id,
                true
            );
            throw $e;
        }
    }
}
