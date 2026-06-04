<?php
class SQMigrations
{
    //==========================================
    // CONFIGURAZIONE GENERALE MODULO
    //==========================================
    private function createConfigTable(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_config') . ' (
            `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop`    INT UNSIGNED NOT NULL DEFAULT 1,
            `config_key` VARCHAR(100) NOT NULL,
            `value`      TEXT DEFAULT NULL,
            `date_add`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`   DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `shop_key` (`id_shop`, `config_key`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }

    //==========================================
    // CREDENZIALI API
    //==========================================
    private function createApiCredentialsTable(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_api_credentials') . ' (
            `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop`       INT UNSIGNED NOT NULL DEFAULT 1,
            `access_token`  VARCHAR(512) NOT NULL,
            `token_iv` VARCHAR(64) DEFAULT NULL,
            `token_type`    VARCHAR(50) DEFAULT \'Bearer\',
            `expires_at`    DATETIME DEFAULT NULL,
            `refresh_token` TEXT DEFAULT NULL,
            `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
            `date_revoked` DATETIME NULL DEFAULT NULL,
            `date_add`      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`      DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_shop` (`id_shop`),
            KEY `idx_expires` (`expires_at`),
        KEY `idx_shop_active_expires` (`id_shop`, `is_active`, `expires_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }

    //==========================================
    // DIMENSIONI DEFAULT PACCO
    //==========================================
    private function createSpedisciQuiPackageTable(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_package') . ' (
            `id`         INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop`    INT UNSIGNED NOT NULL DEFAULT 1,
            `name`       VARCHAR(100) NOT NULL DEFAULT \'Default\',
            `weight`     DECIMAL(10,3) NOT NULL DEFAULT 1.000 COMMENT \'kg\',
            `length`     DECIMAL(10,2) NOT NULL DEFAULT 30.00 COMMENT \'cm\',
            `width`      DECIMAL(10,2) NOT NULL DEFAULT 20.00 COMMENT \'cm\',
            `height`     DECIMAL(10,2) NOT NULL DEFAULT 10.00 COMMENT \'cm\',
            `is_default` TINYINT(1) NOT NULL DEFAULT 0,
            `date_add`   DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`   DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_shop`    (`id_shop`),
            KEY `idx_default` (`id_shop`, `is_default`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }

    //==========================================
    // INDIRIZZI MITTENTE
    //==========================================
    private function createSenderAddressTable(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_sender_address') . ' (
            `id`           INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop`      INT UNSIGNED NOT NULL DEFAULT 1,
            `label`        VARCHAR(100) NOT NULL DEFAULT \'Sede principale\',
            `company`      VARCHAR(150) DEFAULT NULL,
            `firstname`    VARCHAR(100) NOT NULL,
            `lastname`     VARCHAR(100) NOT NULL,
            `phone`        VARCHAR(20) NOT NULL,
            `phone_mobile` VARCHAR(20) DEFAULT NULL,
            `email`        VARCHAR(150) DEFAULT NULL,
            `address1`     VARCHAR(255) NOT NULL,
            `address2`     VARCHAR(255) DEFAULT NULL,
            `postcode`     VARCHAR(12) NOT NULL,
            `city`         VARCHAR(100) NOT NULL,
            `state_code`   VARCHAR(10) DEFAULT NULL COMMENT \'es: NA, RM\',
            `id_country`   INT UNSIGNED NOT NULL DEFAULT 110 COMMENT \'110 = Italia in PS\',
            `country_iso`  CHAR(2) NOT NULL DEFAULT \'IT\',
            `vat_number`   VARCHAR(50) DEFAULT NULL,
            `is_default`   TINYINT(1) NOT NULL DEFAULT 0,
            `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
            `date_add`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`     DATETIME NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_shop`    (`id_shop`),
            KEY `idx_default` (`id_shop`, `is_default`),
            KEY `idx_active`  (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }

    //==========================================
    // REGISTRAZIONE CORRIERI
    //==========================================
    private function createSpedisciQuiCarriers(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_carrier') . ' (
            `id_spedisciqui_carrier` INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_carrier`             INT UNSIGNED NOT NULL,
            `carrier_code`           VARCHAR(50) NOT NULL,
            `carrier_name`           VARCHAR(100) NOT NULL,
            `service_code`           VARCHAR(50) DEFAULT NULL,
            `service_name`           VARCHAR(100) DEFAULT NULL,
            `logo`                   VARCHAR(255) DEFAULT NULL,
            `delay`                  VARCHAR(255) DEFAULT NULL,
            `is_pickup_point`        TINYINT(1) NOT NULL DEFAULT 0,
            `is_courier`             TINYINT(1) NOT NULL DEFAULT 1,
            `position`               INT NOT NULL DEFAULT 0,
            `is_active`              TINYINT(1) NOT NULL DEFAULT 1,
            `extra_data`             JSON DEFAULT NULL,
            `date_add`               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_spedisciqui_carrier`),
            UNIQUE KEY `uniq_carrier_service` (`id_carrier`, `carrier_code`),
            KEY `idx_carrier` (`id_carrier`),
            KEY `idx_active`  (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }

    //==========================================
    // TABELLA CART CUSTOM
    //==========================================
    private function createSpedisciQuiCart(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_cart') . ' (
            `id_cart`         INT UNSIGNED NOT NULL,
            `id_carrier`      INT UNSIGNED NOT NULL,
            `service_code`    VARCHAR(64) NOT NULL,
            `has_insurance`   TINYINT(1) NOT NULL DEFAULT 0,
            `insurance_value` DECIMAL(10,2) DEFAULT NULL,
            `has_cod`         TINYINT(1) NOT NULL DEFAULT 0,
            `cod_amount`      DECIMAL(10,2) DEFAULT NULL,
            `date_add`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`        DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_cart`),
            KEY `idx_carrier` (`id_carrier`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }

    //==========================================
    // TABELLA RANGE PESO/TARIFFE
    //==========================================
    private function createSpedisciQuiRangeWeightPrice(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_weight_tariffs') . ' (
            `id_tariff`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop`      INT UNSIGNED NOT NULL DEFAULT 1,
            `id_carrier`   INT UNSIGNED NOT NULL,
            `service_code` VARCHAR(64) NOT NULL,
            `weight_from`  DECIMAL(20,6) NOT NULL DEFAULT 0.000 COMMENT \'kg incluso\',
            `weight_to`    DECIMAL(20,6) NOT NULL DEFAULT 0.000 COMMENT \'kg escluso\',
            `tariff`       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
            `currency_iso` CHAR(3) NOT NULL DEFAULT \'EUR\' COMMENT \'ISO 4217\',
            `is_active`    TINYINT(1) NOT NULL DEFAULT 1,
            `date_add`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id_tariff`),
            KEY `idx_shop`        (`id_shop`),
            KEY `idx_carrier`     (`id_carrier`),
            KEY `idx_service`     (`service_code`),
            KEY `idx_weight_range`(`weight_from`, `weight_to`),
            KEY `idx_active`      (`is_active`),
            KEY `idx_lookup`      (`id_shop`, `id_carrier`, `service_code`, `is_active`),
            CONSTRAINT `fk_tariff_carrier`
                FOREIGN KEY (`id_carrier`)
                REFERENCES ' . bqSQL(_DB_PREFIX_ . 'carrier') . ' (`id_carrier`)
                ON DELETE CASCADE
                ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }

    //==========================================
    // REGISTRAZIONE SHIPMENTS
    //==========================================
    private function createSpedisciQuiShipments(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_shipments') . ' (
            `id`                     INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_order`               INT UNSIGNED NOT NULL,
            `id_shop`                INT UNSIGNED NOT NULL DEFAULT 1,
            `id_spedisciqui_carrier` INT UNSIGNED DEFAULT NULL,
            `shipment_type`          ENUM(\'outbound\',\'return\',\'partial\') NOT NULL DEFAULT \'outbound\',
            `carrier_code`           VARCHAR(50) DEFAULT NULL,
            `service_code`           VARCHAR(50) DEFAULT NULL,
            `tracking_number`        VARCHAR(100) DEFAULT NULL,
            `tracking_url`           VARCHAR(500) DEFAULT NULL,
            `label_path`             VARCHAR(500) DEFAULT NULL COMMENT \'path relativo da PS root\',
            `label_generated`        TINYINT(1) NOT NULL DEFAULT 0,
            `label_pages`            TINYINT NOT NULL DEFAULT 1,
            `status`                 ENUM(
                \'pending\',
                \'label_created\',
                \'picked_up\',
                \'in_transit\',
                \'out_for_delivery\',
                \'delivered\',
                \'failed\',
                \'cancelled\',
                \'returned\'
            ) NOT NULL DEFAULT \'pending\',
            `delivery_firstname`     VARCHAR(100) DEFAULT NULL,
            `delivery_lastname`      VARCHAR(100) DEFAULT NULL,
            `delivery_address1`      VARCHAR(255) DEFAULT NULL,
            `delivery_address2`      VARCHAR(255) DEFAULT NULL,
            `delivery_postcode`      VARCHAR(12) DEFAULT NULL,
            `delivery_city`          VARCHAR(100) DEFAULT NULL,
            `delivery_country_iso`   CHAR(2) DEFAULT NULL,
            `weight`                 DECIMAL(10,3) DEFAULT NULL COMMENT \'kg\',
            `length`                 DECIMAL(10,2) DEFAULT NULL COMMENT \'cm\',
            `width`                  DECIMAL(10,2) DEFAULT NULL COMMENT \'cm\',
            `height`                 DECIMAL(10,2) DEFAULT NULL COMMENT \'cm\',
            `shipping_cost`          DECIMAL(10,2) DEFAULT NULL,
            `shipping_currency`      CHAR(3) DEFAULT NULL COMMENT \'ISO 4217\',
            `insurance_enabled`      TINYINT(1) NOT NULL DEFAULT 0,
            `insurance_value`        DECIMAL(20,6) NOT NULL DEFAULT 0,
            `api_response`           JSON DEFAULT NULL,
            `api_shipment_id`        VARCHAR(100) DEFAULT NULL COMMENT \'ID spedizione lato corriere\',
            `shipped_at`             DATETIME DEFAULT NULL COMMENT \'data effettiva ritiro\',
            `delivered_at`           DATETIME DEFAULT NULL COMMENT \'data effettiva consegna\',
            `date_add`               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`               DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_order`       (`id_order`),
            KEY `idx_shop`        (`id_shop`),
            KEY `idx_carrier`     (`id_spedisciqui_carrier`),
            KEY `idx_tracking`    (`tracking_number`),
            KEY `idx_status`      (`status`),
            KEY `idx_api_shipment`(`api_shipment_id`),
            KEY `idx_status_shop` (`id_shop`, `status`),
            KEY `idx_label`       (`label_generated`),
            KEY `idx_date`        (`date_add`),
            CONSTRAINT `fk_shipment_carrier`
                FOREIGN KEY (`id_spedisciqui_carrier`)
                REFERENCES ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_carrier') . ' (`id_spedisciqui_carrier`)
                ON DELETE SET NULL
                ON UPDATE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }

    //==========================================
    // ESEGUI TUTTE LE MIGRATION
    //==========================================
    public function runAll(): bool
    {
        $steps = [
            'createConfigTable',
            'createApiCredentialsTable',
            'createSpedisciQuiPackageTable',
            'createSenderAddressTable',
            'createSpedisciQuiCarriers',          // ← prima delle tabelle con FK verso carrier
            'createSpedisciQuiCart',
            'createSpedisciQuiRangeWeightPrice',  // ← dopo carrier
            'createSpedisciQuiShipments',         // ← dopo carrier
        ];

        foreach ($steps as $method) {
            if (!$this->$method()) {
                PrestaShopLogger::addLog(
                    '[SpedisciQui] Migration fallita: ' . $method,
                    3
                );
                return false;
            }
        }

        return true;
    }

    //==========================================
    // ELIMINA TUTTE LE MIGRATION
    //==========================================
    public function deleteAll(): bool
    {
        try {
            $tables = [
                'spedisciqui_shipments',       // prima le tabelle con FK
                'spedisciqui_weight_tariffs',  // prima le tabelle con FK
                'spedisciqui_cart',
                'spedisciqui_carrier',         // poi la tabella referenziata
                'spedisciqui_sender_address',
                'spedisciqui_package',
                'spedisciqui_api_credentials',
                'spedisciqui_config',
            ];

            $db = Db::getInstance();
            $db->execute('SET FOREIGN_KEY_CHECKS = 0');

            foreach ($tables as $table) {
                $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $table . '`';
                if (!$db->execute($sql)) {
                    throw new Exception('Failed dropping table: ' . $table);
                }
            }

            $db->execute('SET FOREIGN_KEY_CHECKS = 1');

            return true;
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui deleteAll Error] ' . $e->getMessage(),
                3
            );
            return false;
        }
    }
}
