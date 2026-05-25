<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

class PackageRepository
{

    const TABLE_NAME = 'spedisciqui_package';


    //=============================================
    // RECUPERO PACKAGE DEFAULT
    //=============================================
    public function getDefault(?int $idShop = null): ?array
    {

        $idShop = $idShop ?? (int) Context::getContext()->shop->id;

        $row = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . self::TABLE_NAME . '`
         WHERE `id_shop` = ' . (int) $idShop . '
         AND `is_default` = 1'
        );

        return $row ?: null;
    }


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
}
