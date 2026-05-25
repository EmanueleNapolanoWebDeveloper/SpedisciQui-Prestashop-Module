<?php

class ConfigRepositories
{


    //=================================================
    // CONTROLLO TABELLA
    //=================================================
    private static function tableExists(): bool
    {
        $result = Db::getInstance()->executeS(
            'SHOW TABLES LIKE "' . _DB_PREFIX_ . 'spedisciqui_config"'
        );

        return !empty($result);
    }


    //=================================================
    // RECUPERO CHIAVE VALORE CONFIGURAZIONE
    //=================================================

    public static function get(string $key, $default = null)
    {
        if ($key === '') {
            return $default;
        }

        // tabella non ancora creata
        if (!self::tableExists()) {
            return $default;
        }

        $value = Db::getInstance()->getValue(
            'SELECT `value`
        FROM `' . _DB_PREFIX_ . 'spedisciqui_config`
        WHERE `config_key` = "' . pSQL($key) . '"'
        );

        if ($value === false || $value === null) {
            return $default;
        }

        return $value;
    }

    //=================================================
    // SETTAGGIO CHIAVE VALORE CONFIGURAZIONE
    //=================================================

    public static function set(string $key, $value)
    {

        $db = Db::getInstance();

        try {


            if (empty($key)) {
                throw new Exception('La config_key non può essere vuota');
            }

            // tabella non esiste
            if (!self::tableExists()) {
                throw new Exception(
                    'Tabella configurazione inesistente'
                );
            }

            // normalizza valore
            if (is_array($value)) {
                $value = json_encode($value);
            }

            $result = $db->insert(
                'spedisciqui_config',
                [
                    'config_key' => pSQL($key),
                    'value' => pSQL($value),
                ],
                false,
                true,
                Db::REPLACE
            );

            if (!$result) {
                throw new Exception('Inserimento fallito per config_key ' . $key);
            }


            return true;
        } catch (Exception $e) {

            PrestaShopLogger::addLog(
                '[SpedisciQui Config Error] ' .
                    $e->getMessage(),
                3
            );

            return false;
        }
    }


    //=================================================
    // RECUPERO TOKEN
    //=================================================
    public function getToken(): ?string
    {
        return $this->get('SPEDISCIQUI_ACCESS_TOKEN', null);
    }
}
