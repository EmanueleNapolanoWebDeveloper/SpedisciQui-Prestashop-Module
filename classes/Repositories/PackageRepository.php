<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

class PackageRepository
{

    const TABLE_NAME = 'spedisciqui_package';


    // ================================================================
    // SALVA DATI PACKAGE DEFAULT (INSERT O UPDATE)
    // ================================================================
    public function savePackage(?int $idShop, array $data): bool
    {
        $idShop = $idShop ?: (int) Context::getContext()->shop->id;

        $db  = Db::getInstance();
        $pfx = _DB_PREFIX_;

        $row = [
            'name'       => pSQL(trim($data['name'] ?? 'Default')),
            'height'     => (float) ($data['height'] ?? 1.0),
            'length'      => (float) ($data['length'] ?? 30.0),
            'width'      => (float) ($data['width'] ?? 20.0),
            'weight'     => (float) ($data['weight'] ?? 10.0),
            'is_default' => (int) ($data['is_default'] ?? 0),
        ];

        $exists = $db->getRow(
            '
        SELECT `id`
        FROM `' . $pfx . self::TABLE_NAME . '`
        WHERE `id_shop` = ' . (int) $idShop . '
        AND `is_default` = 1
        '
        );

        if ($exists) {
            return (bool) $db->update(
                self::TABLE_NAME,
                $row,
                '`id_shop` = ' . (int) $idShop
            );
        }

        return (bool) $db->insert(
            self::TABLE_NAME,
            array_merge(
                ['id_shop' => (int) $idShop],
                $row
            )
        );
    }



    // ================================================================
    // PRENDI DATI DI PACKAGE DEFAULT
    // ================================================================
    public function getPackage(?int $idShop = null): ?array
    {
        $idShop = $idShop ?? (int) Context::getContext()->shop->id;

        $result = Db::getInstance()->getRow(
            '
        SELECT `height`, `length`, `width`, `weight`
        FROM `' . _DB_PREFIX_ . self::TABLE_NAME . '`
        WHERE `id_shop` = ' . (int) $idShop
        );

        return $result ?: null;
    }


    // ================================================================
    // PRENDI DATI DI PACKAGE PER SHIPMENT (PARCELDATA)
    // ================================================================
    public function getParcelData(\Order $order)
    {

        try {
            $products = $order->getProducts();
            $dimensionDefault = new PackageServices()->getDefault();

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
                        '[SpedisciQui] Prodotto "' . $productId . '" peso a zero → default ' . $dimensionDefault['weight']. 'kg | Ordine #' . (int)$order->id,
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
