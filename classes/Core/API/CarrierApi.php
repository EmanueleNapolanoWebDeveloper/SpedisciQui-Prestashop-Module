<?php


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
        $data = $this->apiClient->request('GET', '/api/getCarriers', $token);

        if (!$data || empty($data['success'])) {
            return null;
        }

        return $data['carriers'] ?? null;
    }


    public function getPriceFromApi(): array
    {
        $token = (new CredentialServices())->getToken()['access_token'];

        $payload = [
            "recipient" => [
                "name"    => "John Smith",
                "address" => "221B Baker Street",
                "city"    => "London",
                "zip"     => "NW1 6XE",
                "country" => "UK"
            ],
            "package" => [
                "weight" => 2.5,
                "height" => 30,
                "length" => 40,
                "depth"  => 20
            ],
            "insurance"              => true,
            "insurance_value"        => 100,
            "cash_on_delivery"       => true,
            "cash_on_delivery_value" => 50
        ];

        // ✅ ApiClient ritorna già array - niente getBody(), niente json_decode
        $data = $this->apiClient->request('POST', '/api/calculateshipping', $token, $payload);

        if (!$data || !isset($data['prices'])) {
            \PrestaShopLogger::addLog('[SPEDISCIQUI] Risposta API non valida o prices mancanti', 3);
            return [];
        }

        $mapped = [];
        foreach ($data['prices'] as $priceRow) {
            if (!isset($priceRow['carrier_code'], $priceRow['price'])) {
                continue;
            }
            $mapped[$priceRow['carrier_code']] = (float) $priceRow['price'];
        }

        return $mapped;
    }
}
