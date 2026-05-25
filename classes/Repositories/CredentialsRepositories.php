<?php

class CredentialsRepositories
{

    private Context $context;
    private ApiClient $apiClient;

    public function __construct(
        Context $context,
        ApiClient $apiClient
    ) 
    {
        $this->context = $context;
        $this->apiClient = $apiClient;
    }

    //=================================================
    // RECUPERO ACCESS_TOKEN
    //=================================================
    public function getToken(): ?array
    {
        $idShop = (int)$this->context->shop->id;

        $row = Db::getInstance()->getRow(
            'SELECT `access_token` FROM `' . _DB_PREFIX_ . 'spedisciqui_api_credentials`
             WHERE `id_shop` = ' . $idShop . '
             AND `is_active` = 1
             '
        );

        return $row ?: null;
    }


    //===========================================
    //CONTROLLO CARATTERI
    //===========================================

    private function isTokenFormatValid(string $token): bool
    {
        return strlen($token) >= 20 && preg_match('/^[a-zA-Z0-9\-_\.]+$/', $token);
    }



    //=================================================
    // TOKEN VALIDO
    //=================================================
    public function validateToken(string $token): bool
    {
        // 1. controllo formato
        if (!$this->isTokenFormatValid($token)) {
            return false;
        }

        $validationApi = $this->apiClient->validateToken($token);

        if(!$validationApi) {
            return false;
        }

        // 2. recupero credenziali salvate
        $credentials = $this->getToken();

        if (!$credentials || empty($credentials['access_token'])) {
            return true; // primo inserimento, quindi valido
        }

        // 3. confronto token (opzionale ma consigliato)
        if ($credentials['access_token'] !== $token) {
            // token diverso da quello salvato
            return true;
        }

        // 4. controllo scadenza
        if (!empty($credentials['expires_at'])) {
            return strtotime($credentials['expires_at']) > time();
        }

        return true;
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
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 month'));

        return Db::getInstance()->insert(
            'spedisciqui_api_credentials',
            [
                'id_shop' => $idShop,
                'access_token' => pSQL($token),
                'token_type' => 'Bearer',
                'expires_at' => $expiresAt,
                'is_active' => 1,
            ],
            false,
            true,
            Db::REPLACE
        );
    }


    //=================================================
    // REVOCA ACCESS_TOKEN
    //=================================================
    public function revoke(): bool
    {
        $idShop = $this->context->shop->id;

        return Db::getInstance()->update(
            'spedisciqui_api_credentials',
            ['is_active' => 0],
            '`id_shop` = ' . $idShop
        );
    }


    //==========================================
    // GIORNI ALLA SCADENZA
    //==========================================
    public function daysUntilExpiry(): ?int
    {
        $credentials = $this->getToken();

        if (!$credentials || empty($credentials['expires_at'])) {
            return null;
        }

        $diff = strtotime($credentials['expires_at']) - time();
        return max(0, (int) ceil($diff / 86400));
    }


    //==========================================
    // ELIMINAZIONE COMPLETA
    //==========================================
    public function delete(): bool
    {
        $idShop = (int) $this->context->shop->id;

        return Db::getInstance()->delete(
            'spedisciqui_api_credentials',
            '`id_shop` = ' . $idShop
        );
    }
}
