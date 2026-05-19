<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class DatabaseManager
{
    private function createConfigTable(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_config') . ' (
            `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop`  INT UNSIGNED NOT NULL DEFAULT 1,
            `key`      VARCHAR(100) NOT NULL,
            `value`    TEXT DEFAULT NULL,
            `date_add` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `shop_key` (`id_shop`, `key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }

    // creazioen tabella default package
    private function createDefaultPackage(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_package') . ' (
            `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop`  INT UNSIGNED NOT NULL DEFAULT 1,
            `height`   DECIMAL(8,2) NOT NULL,
            `depth`    DECIMAL(8,2) NOT NULL,
            `length`   DECIMAL(8,2) NOT NULL,
            `weight`   DECIMAL(8,2) NOT NULL,
            `date_add` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `id_shop` (`id_shop`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }

    // creazioen tabella defualt sender
    private function createDefaultSender(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'spedisciqui_sender` (
            `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop`  INT UNSIGNED NOT NULL DEFAULT 1,
            `name`     VARCHAR(150) NOT NULL,
            `surname`  VARCHAR(150) NOT NULL,
            `address`  VARCHAR(255) NOT NULL,
            `zip`      VARCHAR(10) NOT NULL,
            `city`     VARCHAR(100) NOT NULL,
            `prov`     VARCHAR(5) NOT NULL,
            `country`  VARCHAR(2) NOT NULL DEFAULT "IT",
            `phone`    VARCHAR(20) DEFAULT NULL,
            `date_add` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uniq_shop` (`id_shop`)
        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }

    private function createShipmentsTable(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_shipments') . ' (
            `id`               INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_order`         INT UNSIGNED NOT NULL,
            `id_shop`          INT UNSIGNED NOT NULL DEFAULT 1,
            `tracking_number`  VARCHAR(100) DEFAULT NULL,
            `carrier_code`     VARCHAR(50) DEFAULT NULL,
            `status`           VARCHAR(50) NOT NULL DEFAULT "pending",
            `api_response`     TEXT DEFAULT NULL,
            `date_add`         TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`         TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `id_order` (`id_order`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }


    // CREAZIONE TABELLA CARRIER MAPPING
    private function createCarrierMappingTable(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_carrier_mapping') . ' (
            `id`                 INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `serviceId`          VARCHAR(100) NOT NULL,
            `carrierReferenceId` INT UNSIGNED NOT NULL,
            `isActive`           TINYINT(1) NOT NULL DEFAULT 1,
            `date_add`           TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`           TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_service` (`serviceId`),
            KEY `idx_carrier_ref` (`carrierReferenceId`),
            KEY `idx_is_active` (`isActive`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }

    // GET DI CARRIER MAPPING
    public function getCarrierMapping($serviceId)
    {
        $result = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'spedisciqui_carrier_mapping` 
         WHERE serviceId = \'' . pSQL($serviceId) . '\''
        );

        return $result ?: null;
    }

    // get di tutte le carrier mapping
    public function getAllCarrierMapping()
    {
        $result = Db::getInstance()->executeS(
            'SELECT * FROM ' . _DB_PREFIX_ . 'spedisciqui_carrier_mapping WHERE isActive = 1'
        );

        return is_array($result) ? $result : [];
    }

    // SALVATAGGIO MAPPING TRA SHIPPING E CARRIER
    public function saveCarrierMapping($serviceId, $carrierReferenceId)
    {

        // controllo se esiste
        $exists = Db::getInstance()->getValue(
            'SELECT id FROM ' . _DB_PREFIX_ . 'spedisciqui_carrier_mapping WHERE serviceId = \'' . pSQL($serviceId) . '\''
        );

        if ($exists) {
            return Db::getInstance()->update(
                'spedisciqui_carrier_mapping',
                [
                    'carrierReferenceId' => $carrierReferenceId,
                    'isActive' => 1,
                ],
                'serviceId = \'' . pSQL($serviceId) . '\''
            );
        }

        return Db::getInstance()->insert('spedisciqui_carrier_mapping', [
            'serviceId' => pSQL($serviceId),
            'carrierReferenceId' => $carrierReferenceId,
            'isActive' => 1,
        ]);
    }

    // cancella mapping carrier
    public function deleteCarrierMapping($serviceId)
    {
        return Db::getInstance()->delete(
            'spedisciqui_carrier_mapping',
            'serviceId = \'' . pSQL($serviceId) . '\''
        );
    }


    // CREAZIONE DI TUTTE LE TABELLA ALL'INSTALLAZIONE
    public function createAllTableOnInstallation(): bool
    {
        try {
            $result =
                $this->createConfigTable() &&
                $this->createDefaultPackage() &&
                $this->createDefaultSender() &&
                $this->createShipmentsTable() &&
                $this->createCarrierMappingTable();

            if (!$result) {
                PrestaShopLogger::addLog('[SPEDISCIQUI] Errore creazione tabelle DB', 3);
                return false;
            }

            return true;
        } catch (Exception $e) {
            PrestaShopLogger::addLog('[SPEDISCIQUI] DB Installation Error: ' . $e->getMessage(), 3);
            return false;
        }
    }

    // drop di tutte le tabelle
    public function dropAllSpedisciQuiTables(): bool
    {
        $tables = [
            'spedisciqui_config',
            'spedisciqui_package',
            'spedisciqui_sender',
            'spedisciqui_shipments',
            'spedisciqui_carrier_mapping',
        ];

        foreach ($tables as $table) {
            $result = Db::getInstance()->execute(
                'DROP TABLE IF EXISTS `' . bqSQL(_DB_PREFIX_ . $table) . '`'
            );

            if (!$result) {
                PrestaShopLogger::addLog('[SPEDISCIQUI] Errore DROP tabella: ' . $table, 3);
                return false;
            }
        }

        return true;
    }
}
