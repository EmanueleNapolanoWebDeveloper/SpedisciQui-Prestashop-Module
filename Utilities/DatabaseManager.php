<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class DatabaseManager
{


    // ================================================================
    // CREAZIONE TABELLA CONFIGURAZIONE
    // ================================================================
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




    // ================================================================
    // CREAZIONE TABELLA PER DEFAULT PACKAGE
    // ================================================================
    private function createDefaultPackage(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_package') . ' (
            `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop`  INT UNSIGNED NOT NULL DEFAULT 1,
            `height`   DECIMAL(8,2) NOT NULL,
            `depth`    DECIMAL(8,2) NOT NULL,
            `width`   DECIMAL(8,2) NOT NULL,
            `weight`   DECIMAL(8,2) NOT NULL,
            `date_add` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `id_shop` (`id_shop`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }



    // ================================================================
    // CREAZIOEN TABELLA PER SENDERS
    // ================================================================
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



    // ================================================================
    // TABELLA SHIPMENTS
    // ================================================================
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


    // ================================================================
    // CREAZIONE TABELLA DI MAPPING
    // ================================================================
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


    // ================================================================
    // CREAZIONE TABELLA ESTENSIONE DI CART
    // ================================================================
    private function createSpedisciQuiCart(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'spedisciqui_cart` (
        `id_cart` int(10) unsigned NOT NULL,
        `is_oversized` tinyint(1) NOT NULL DEFAULT 0,
        `is_call_before_delivery` tinyint(1) NOT NULL DEFAULT 0,
        `is_fragile` tinyint(1) NOT NULL DEFAULT 0,
        `packs` int(10) unsigned NOT NULL DEFAULT 1,
        `is_cod` tinyint(1) NOT NULL DEFAULT 0,
        `weight` decimal(10,2) DEFAULT NULL,
        `volume` decimal(10,2) DEFAULT NULL,
        `cod_amount` decimal(10,2) DEFAULT NULL,
        `is_pickup` tinyint(1) NOT NULL DEFAULT 0,
        `id_pickup_point` varchar(30) COLLATE utf8_unicode_ci DEFAULT NULL,
        `label_number` text COLLATE utf8_unicode_ci NULL,
        `error` text COLLATE utf8_unicode_ci DEFAULT NULL,
        `comment` text COLLATE utf8_unicode_ci DEFAULT NULL,
        `id_spedisciqui_manifest` int(10) unsigned DEFAULT NULL,
        PRIMARY KEY (`id_cart`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci';

        return Db::getInstance()->execute($sql);
    }


    // ================================================================
    // CREAZIONE TABELLA STORE (PER MULTI STORE)
    // ================================================================
    private function createSpedisciQuiStores(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'spedisciqui_store` (
        `id_spedisciqui_store` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
        `title` VARCHAR(255) NOT NULL,
        `postcode` VARCHAR(255) NOT NULL,
        `city` VARCHAR(255) NOT NULL,
        `phone` VARCHAR(255) NOT NULL,
        `country_code` VARCHAR(2) NOT NULL,
        `address` VARCHAR(255) NOT NULL,
        `pick_start` VARCHAR(255) NOT NULL,
        `pick_finish` VARCHAR(255) NOT NULL,
        `id_shop` INT(11) NOT NULL,
        `is_default` TINYINT(1) NOT NULL DEFAULT 0,
        `active` TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY (`id_spedisciqui_store`)
    ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

        return Db::getInstance()->execute($sql);
    }




    // ================================================================
    // PRELEVA CARRIER_MAPPING
    // ================================================================
    public function getCarrierMapping($serviceId)
    {
        $result = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'spedisciqui_carrier_mapping` 
         WHERE serviceId = \'' . pSQL($serviceId) . '\''
        );

        return $result ?: null;
    }



    // ================================================================
    // PRELEVA TUTTI I CARRIER_MAPPING
    // ================================================================
    public function getAllCarrierMapping()
    {
        $result = Db::getInstance()->executeS(
            'SELECT * FROM `' . _DB_PREFIX_ . 'spedisciqui_carrier_mapping` WHERE isActive = 1'
        );

        return is_array($result) ? $result : [];
    }




    // ================================================================
    // SALVATAGGIO MAPPING TRA SHIPPING E CARRIER
    // ================================================================
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



    // ================================================================
    // CANCELLA SERVIZIO CARRIER DA CARRIER_MAPPING
    // ================================================================
    public function deleteCarrierMapping($serviceId)
    {
        return Db::getInstance()->delete(
            'spedisciqui_carrier_mapping',
            'serviceId = \'' . pSQL($serviceId) . '\''
        );
    }






    // ================================================================
    // PULIZIA DA TUTTE LE TABELLE PER UN CARRIER
    // ================================================================
    public function deleteAllModuleCarrier()
    {

        // recupera carriers
        $carriers = Db::getInstance()->executeS(
            'SELECT id_carrier 
         FROM ' . _DB_PREFIX_ . 'carrier 
         WHERE external_module_name = "spedisciquishipping" 
         AND deleted = 0'
        );

        if (!is_array($carriers)) {
            return true;
        }

        foreach ($carriers as $row) {
            $idCarrier = $row['id_carrier'];

            // marca DLETED 
            Db::getInstance()->update('carrier', ['deleted' => 1], 'id_carrier = ' . $idCarrier);

            // rimuovi associazione a zone
            Db::getInstance()->execute(
                'DELETE FROM ' . _DB_PREFIX_ . 'carrier_zone WHERE id_carrier = ' . $idCarrier
            );

            // rimuovere range peso
            Db::getInstance()->execute(
                'DELETE FROM ' . _DB_PREFIX_ . 'range_weight WHERE id_carrier = ' . $idCarrier
            );

            // rimuovere range prezzo
            Db::getInstance()->execute(
                'DELETE FROM ' . _DB_PREFIX_ . 'range_price WHERE id_carrier = ' . $idCarrier
            );

            // rimuovere prezzi delivery
            Db::getInstance()->execute(
                'DELETE FROM ' . _DB_PREFIX_ . 'delivery WHERE id_carrier = ' . $idCarrier
            );

            // rimuovere associazione a gruppi
            Db::getInstance()->execute(
                'DELETE FROM ' . _DB_PREFIX_ . 'carrier_group WHERE id_carrier = ' . $idCarrier
            );

            // rimuovere associazione a shop
            Db::getInstance()->execute(
                'DELETE FROM ' . _DB_PREFIX_ . 'carrier_shop WHERE id_carrier = ' . $idCarrier
            );

            //PrestaShopLogger::addLog('[SPEDISCIQUI] Carrier id=' . $idCarrier . 'rimosso', 1);
        }

        // Rimuovi dalla configuration SPEDISCIQUI_CARRIER_*
        Db::getInstance()->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'configuration` 
             WHERE name LIKE \'SPEDISCIQUI_CARRIER_%\''
        );

        // pulizia tabella mapping
        Db::getInstance()->execute(
            'DELETE FROM ' . _DB_PREFIX_ . 'spedisciqui_carrier_mapping WHERE 1'
        );

        return true;
    }




    // ================================================================
    // DROP DI TUTTE LE TABELLE
    // ================================================================
    public function dropAllSpedisciQuiTables(): bool
    {
        $tables = [
            'spedisciqui_config',
            'spedisciqui_package',
            'spedisciqui_sender',
            'spedisciqui_shipments',
            'spedisciqui_carrier_mapping',
            'spedisciqui_store',
            'spedisciqui_cart'
        ];

        foreach ($tables as $table) {
            $result = Db::getInstance()->execute(
                'DROP TABLE IF EXISTS `' . bqSQL(_DB_PREFIX_ . $table) . '`'
            );

            if (!$result) {
                //PrestaShopLogger::addLog('[SPEDISCIQUI] Errore DROP tabella: ' . $table, 3);
                return false;
            }
        }

        return true;
    }
}
