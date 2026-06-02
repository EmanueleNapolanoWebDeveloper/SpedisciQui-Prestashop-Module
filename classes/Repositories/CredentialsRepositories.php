<?php

class CredentialsRepositories
{

    private const CIPHER_ALGO = 'AES-256-CBC';


    private Context $context;
    private ApiClient $apiClient;

    public function __construct(
        Context $context,
        ApiClient $apiClient
    ) {
        $this->context = $context;
        $this->apiClient = $apiClient;
    }


    //=================================================
    //      recuper ACCESS_TOKEN
    //=================================================
    public function get(): ?array
    {
        $idShop = (int) $this->context->shop->id;

        $sql = new DbQuery();

        $sql->select('access_token, token_iv,expires_at')
            ->from('spedisciqui_api_credentials')
            ->where('id_shop = ' . $idShop)
            ->where('is_active = 1')
            ->where('expires_at > Now()');

        $row = Db::getInstance()->getRow($sql);

        if (!$row || empty($row['access_token'])) {
            return null;
        }

        $decripted = $this->decryptToken($row['access_token'], $row['token_iv']);

        return [
            'access_token' => $decripted,
            'expires_at' => $row['expires_at']
        ];
    }




    //=================================================
    // SETTAGGIO ACCESS_TOKEN
    //=================================================
    public function save(string $token)
    {

        if (empty(trim($token))) {
            return false;
        }

        $idShop = $this->context->shop->id;
        $db = Db::getInstance();
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 month'));


        $ivLength = openssl_cipher_iv_length(self::CIPHER_ALGO);
        $iv = openssl_random_pseudo_bytes($ivLength);
        $encrypted = $this->encryptToken($token, $iv);

        if ($encrypted === false) {
            $this->log('Cifratura token fallita', 3);
            return false;
        }

        $db->execute('START TRANSACTION');

        try {
            // revoca token precedenti
            $revoked = $db->update(
                'spedisciqui_api_credentials',
                ['is_active' => 0],
                '`id_shop` =' . $idShop
            );

            if ($revoked === false) {
                throw new RuntimeException('Revoca token precendenti fallita per shop ' . $idShop);
            }

            // inserisco nuovo token cifrato
            $inserted = $db->insert(
                'spedisciqui_api_credentials',
                [
                    'id_shop' => $idShop,
                    'access_token' => pSQL($encrypted),
                    'token_iv' => pSQL(base64_encode($iv)),
                    'token_type' => 'Bearer',
                    'expires_at' => pSQL($expiresAt),
                    'is_active' => 1
                ],
                false,
                true,
                Db::REPLACE
            );

            if (!$inserted) {
                throw new RuntimeException('Inserimento token fallito per shop ' . $idShop);
            }

            $db->execute('COMMIT');
            return true;
        } catch (Throwable $e) {
            $db->execute('ROLLBACK');
            $this->log($e->getMessage(), 3);
            return false;
        }
    }


    //=================================================
    // REVOCA ACCESS_TOKEN
    //=================================================
    public function revoke(): bool
    {
        $idShop = $this->context->shop->id;

        $result =  Db::getInstance()->update(
            'spedisciqui_api_credentials',
            [
                'is_active' => 0,
                'date_revoked' => date('Y-m-d H:i:s')
            ],
            '`id_shop` = ' . $idShop
        );

        if ($result === false) {
            $this->log('Revoca token fallita per shop: ' . $idShop, 3);
        }

        return (bool) $result;
    }



    //==========================================
    // ELIMINAZIONE COMPLETA
    //==========================================
    public function delete(): bool
    {
        $idShop = (int) $this->context->shop->id;

        $result = Db::getInstance()->delete(
            'spedisciqui_api_credentials',
            '`id_shop` = ' . $idShop
        );

        if ($result === false) {
            $this->log('Eliminazione token fallita per shop ' . $idShop, 3);
        }

        return (bool) $result;
    }

    //=============================================
    //============0HELPERS=========================
    //===========================================0=

    // =================================================
    //  Cifratura TOKEN
    // =================================================
    private function encryptToken(string $plainToken, string $iv): string|false
    {
        $key = $this->getEncryptionKey();
        $encrypted = openssl_encrypt($plainToken, self::CIPHER_ALGO, $key, 0, $iv);

        return $encrypted;
    }


    // =================================================
    //  DECIFRATURA TOKEN
    // =================================================

    private function decryptToken(string $token, string $ivBase): string
    {
        $key = $this->getEncryptionKey();
        $iv = base64_decode($ivBase);
        $decripted = openssl_decrypt($token, self::CIPHER_ALGO, $key, 0, $iv);

        return $decripted !== false ? $decripted : null;
    }





    // =================================================
    //  Cifratura KEY
    // =================================================
    private function getEncryptionKey(): string
    {
        $base = defined('_COOKIE_KEY_') ? _COOKIE_KEY_ : 'fallback-change-me-in-production';
        // hash() restituisce sempre 64 char hex → 32 byte con hex2bin → perfetto per AES-256
        return hex2bin(hash('sha256', $base));
    }









    private function log(string $message, int $severity = 2): void
    {
        PrestaShopLogger::addLog(
            '[CredentialsRepository] ' . $message,
            $severity,
            null,
            'CredentialsRepository',
            0,
            true
        );
    }
}
