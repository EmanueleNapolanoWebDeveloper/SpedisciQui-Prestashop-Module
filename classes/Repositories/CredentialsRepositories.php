<?php

class CredentialsRepositories
{

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
    public function get(): array|false
    {
        $idShop = (int) $this->context->shop->id;

        $sql = new DbQuery();

        $sql->select('access_token')
            ->from('spedisciqui_api_credentials')
            ->where('id_shop = ' . $idShop)
            ->limit(1);

        return Db::getInstance()->getRow($sql);
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
