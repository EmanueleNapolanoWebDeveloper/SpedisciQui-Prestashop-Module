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

        if ($idShop <= 0) {
            $this->log('savePackage: id_shop non valido', 3);
            return false;
        }


        $row = [
            'name'       => pSQL(trim($data['name'] ?? 'Default')),
            'height'     => (float) ($data['height'] ?? 1.0),
            'length'      => (float) ($data['length'] ?? 30.0),
            'width'      => (float) ($data['width'] ?? 20.0),
            'weight'     => (float) ($data['weight'] ?? 10.0),
            'is_default' => (int) ($data['is_default'] ?? 0),
        ];

        $db  = Db::getInstance();


        try {

            $db->execute('START TRANSACTION');

            $exists = $this->findDefaultPackage($idShop);

            if ($exists) {
                return (bool) $db->update(
                    self::TABLE_NAME,
                    $row,
                    '`id_shop` = ' . (int) $idShop
                );
            } else {
                $result = $db->insert(
                    self::TABLE_NAME,
                    array_merge(['id_shop' => (int) $idShop], $row)
                );
            }

            if (!$result) {
                $db->execute('ROLLBACK');
                $this->log('savePackage: ' . ($exists ? 'update' : 'insert') . ' fallito per shop #' . $idShop, 3);
                return false;
            }

            $db->execute('COMMIT');
            return true;
        } catch (Exception $e) {
            $db->execute('ROLLBACK');
            $this->log('savePackage: eccezione — ' . $e->getMessage(), 3);
            return false;
        }
    }



    // ================================================================
    // PRENDI DATI DI PACKAGE DEFAULT
    // ================================================================
    public function getPackage(?int $idShop = null): ?array
    {
        $idShop = $idShop ?? (int) Context::getContext()->shop->id;

        if ($idShop <= 0) {
            $this->log('getPackage: id_shop non valido', 3);
            return null;
        }

        try {

            $sql = new DbQuery();
            $sql->select('`name`, `height`, `length`, `width`, `weight`, `is_default`')
                ->from(self::TABLE_NAME)
                ->where('`id_shop` = ' . (int) $idShop)
                ->where('`is_default` = 1');

            $result = Db::getInstance()->getRow($sql);

            if (empty($result)) {
                return null;
            }

            return $this->normalizePackageRow($result);
        } catch (Exception $e) {
            $this->log('getPackage: eccezione — ' . $e->getMessage(), 3);
            return null;
        }
    }



    // ===============================================
    // HELPER PER PRESTALOGGER
    // =================================================
    private function log(
        string  $message,
        int     $severity = 3,
        string  $objectType = '',
        int     $objectId = 0
    ): void {
        PrestaShopLogger::addLog(
            '[SpedisciQui] ' . $message,
            $severity,
            null,
            $objectType ?: null,
            $objectId ?: null,
            true
        );
    }

    /**
     * Cerca il package default per uno shop — usato internamente da savePackage.
     */
    private function findDefaultPackage(int $idShop): array|false
    {
        return Db::getInstance()->getRow(
            'SELECT `id`
             FROM `' . _DB_PREFIX_ . self::TABLE_NAME . '`
             WHERE `id_shop` = ' . (int) $idShop . '
             AND `is_default` = 1'
        );
    }

    /**
     * Normalizza e tipizza il risultato raw del DB.
     */
    private function normalizePackageRow(array $row): array
    {
        return [
            'name'       => (string) $row['name'],
            'height'     => (float)  $row['height'],
            'length'     => (float)  $row['length'],
            'width'      => (float)  $row['width'],
            'weight'     => (float)  $row['weight'],
            'is_default' => (int)    $row['is_default'],
        ];
    }
}
