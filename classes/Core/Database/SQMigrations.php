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
            `date_add`   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`   TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
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
            `access_token`  TEXT NOT NULL,
            `token_type`    VARCHAR(50) DEFAULT "Bearer",
            `expires_at`    DATETIME DEFAULT NULL,
            `refresh_token` TEXT DEFAULT NULL,
            `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
            `date_add`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_shop` (`id_shop`),
            KEY `idx_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }

    //==========================================
    // DIMENSIONI DEFAULT PACCO
    //==========================================
    private function creatSpedisciQuiPackageTable(): bool
    {
        $sql = 'CREATE TABLE IF NOT EXISTS ' . bqSQL(_DB_PREFIX_ . 'spedisciqui_package') . ' (
            `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop`       INT UNSIGNED NOT NULL DEFAULT 1,
            `name`          VARCHAR(100) NOT NULL DEFAULT "Default",
            `weight`        DECIMAL(10,3) NOT NULL DEFAULT 1.000 COMMENT "kg",
            `length`        DECIMAL(10,2) NOT NULL DEFAULT 30.00 COMMENT "cm",
            `width`         DECIMAL(10,2) NOT NULL DEFAULT 20.00 COMMENT "cm",
            `height`        DECIMAL(10,2) NOT NULL DEFAULT 10.00 COMMENT "cm",
            `is_default`    TINYINT(1) NOT NULL DEFAULT 0,
            `date_add`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_shop` (`id_shop`),
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
            `id`            INT UNSIGNED NOT NULL AUTO_INCREMENT,
            `id_shop`       INT UNSIGNED NOT NULL DEFAULT 1,
            `label`         VARCHAR(100) NOT NULL DEFAULT "Sede principale",
            `company`       VARCHAR(150) DEFAULT NULL,
            `firstname`     VARCHAR(100) NOT NULL,
            `lastname`      VARCHAR(100) NOT NULL,
            `phone`         VARCHAR(20) NOT NULL,
            `phone_mobile`  VARCHAR(20) DEFAULT NULL,
            `email`         VARCHAR(150) DEFAULT NULL,
            `address1`      VARCHAR(255) NOT NULL,
            `address2`      VARCHAR(255) DEFAULT NULL,
            `postcode`      VARCHAR(12) NOT NULL,
            `city`          VARCHAR(100) NOT NULL,
            `state_code`    VARCHAR(10) DEFAULT NULL COMMENT "es: NA, RM",
            `id_country`    INT UNSIGNED NOT NULL DEFAULT 110 COMMENT "110 = Italia in PS",
            `country_iso`   CHAR(2) NOT NULL DEFAULT "IT",
            `vat_number`    VARCHAR(50) DEFAULT NULL,
            `is_default`    TINYINT(1) NOT NULL DEFAULT 0,
            `is_active`     TINYINT(1) NOT NULL DEFAULT 1,
            `date_add`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `date_upd`      TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `idx_shop` (`id_shop`),
            KEY `idx_default` (`id_shop`, `is_default`),
            KEY `idx_active` (`is_active`)
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
            KEY `idx_active` (`is_active`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';

        return (bool) Db::getInstance()->execute($sql);
    }



    //==========================================
    // REGISTRAZIONE SHIPMENTS
    //==========================================
    private function createSpedisciQuiShipments(): bool
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

    //==========================================
    // ESEGUI TUTTE LE MIGRATION
    //==========================================
    public function runAll(): bool
    {
        try {
            $result =  $this->createConfigTable()
                && $this->createApiCredentialsTable()
                && $this->creatSpedisciQuiPackageTable()
                && $this->createSenderAddressTable()
                && $this->createSpedisciQuiCarriers()
                && $this->createSpedisciQuiShipments();

            if (!$result) {
                return false;
            }

            return true;
        } catch (Exception $e) {
            return false;
        }
    }

    


    //==========================================
    // ELIMINA TUTTE LE MIGRATION
    //==========================================
    public function deleteAll(): bool
    {
        try {
            $tables = [
                'spedisciqui_config',
                'spedisciqui_api_credentials',
                'spedisciqui_package',
                'spedisciqui_sender_address',
                'spedisciqui_carrier',
                'spedisciqui_shipments'
            ];

            $db = Db::getInstance();

            foreach ($tables as $table) {
                $sql = 'DROP TABLE IF EXISTS `' . _DB_PREFIX_ . $table . '`';
                $result = $db->execute($sql);

                if (!$result) {
                    throw new Exception('Failed dropping table: ' . $table);
                }
            }

            return true;
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui deleteAll Error] ' . $e->getMessage(),
                3
            );

            error_log($e->getMessage());

            return false;
        }
    }
}
