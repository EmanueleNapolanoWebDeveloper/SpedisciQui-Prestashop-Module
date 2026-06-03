<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;


class CarrierApi
{
    private ApiClient $apiClient;
    private $context;

    public function __construct(ApiClient $apiClient)
    {
        $this->context = Context::getContext();
        $this->apiClient = $apiClient;
    }

    //============================================
    // RICHIAMO CORRIERI DA PIATTAFORMA
    //============================================
    public function getCarriers(string $token): ?array
    {
        $response = $this->apiClient->request('GET', '/api/getCarriers', $token);

        if (!$response || !$response->isSuccess()) {
            return null;
        }

        $data = $response->getData();

        return $data['carriers'] ?? null;
    }
}
