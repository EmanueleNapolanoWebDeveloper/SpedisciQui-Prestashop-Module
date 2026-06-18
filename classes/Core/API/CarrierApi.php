<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class CarrierApi
{

    private const CARRIER_CACHE_KEY = 'SQ_CARRIERS_CACHE';
    private const CARRIER_CACHE_KEY_EXP = 'SQ_CARRIERS_CACHE_EXP';
    private const CARRIER_CACHE_TTL = 3600;

    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    //============================================
    // RICHIAMO CORRIERI DA PIATTAFORMA
    //============================================
    public function getCarriers(string $token): ?array
    {

        PrestaShopLogger::addLog(
            'entrato in funzione getCarriers',
            1
        );

        $exp = (int) ConfigRepositories::get(self::CARRIER_CACHE_KEY_EXP, 0);
        $cached = \ConfigRepositories::get(self::CARRIER_CACHE_KEY, '');

        if ($exp > time() && !empty($cached)) {
            $data = json_decode($cached, true);
            if (is_array($data)) {
                return $data;
            }
        }

        // chiamata api
        $response = $this->apiClient->request('GET', '/api/getCarriers', $token);

        if (!$response || !$response->isSuccess()) {
            \PrestaShopLogger::addLog(  // ← aggiungi
                '[SQ] getCarriers fallito — ' . ($response ? $response->getErrorMessage() : 'risposta null'),
                2
            );
            return null;
        }

        $data = $response->getData();
        $carriers = $data['carriers'] ?? null;

        PrestaShopLogger::addLog(
            '[funzione recupero shipments: ' . print_r($carriers, true),
            1
        );

        // salvo in cache
        if (is_array($carriers) && !empty($carriers)) {
            \ConfigRepositories::set(self::CARRIER_CACHE_KEY, json_encode($carriers));
            \ConfigRepositories::set(self::CARRIER_CACHE_KEY_EXP, time() + self::CARRIER_CACHE_TTL);
        }

        return $carriers;
    }



    // =========================================================================
    // INVALIDAZIONE CACHE
    // =========================================================================
    public function invalidateCache(): void
    {
        ConfigRepositories::set(self::CARRIER_CACHE_KEY, '');
        ConfigRepositories::set(self::CARRIER_CACHE_KEY_EXP, 0);
    }
}
