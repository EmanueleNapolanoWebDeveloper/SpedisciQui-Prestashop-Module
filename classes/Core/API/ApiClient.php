<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;

class ApiClient
{
    private string $baseUrl;
    private Client $client;
    private ?CredentialsRepositories $credentialRepo = null;


    //============================================
    // COSTRUTTORE
    //============================================
    public function __construct(
        ConfigRepositories $config
    ) {

        $this->baseUrl = 'http://127.0.0.1:8000';

        $this->client = new Client([
            'base_uri' => rtrim($this->baseUrl, '/') . '/',
            'timeout'  => 10,
            'headers'  => [
                'Accept'       => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    //============================================
    // VALIDAZIONE TOKEN
    //============================================
    public function validateTokenFromApi(string $token): bool
    {
        try {
            $response = $this->client->get('/api/auth/verify', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ]);

            return $response->getStatusCode() === 200;
        } catch (ClientException $e) {
            PrestaShopLogger::addLog('[SPEDISCIQUI] validateToken ClientException: ' . $e->getMessage(), 2);
            return false;
        } catch (ConnectException $e) {
            PrestaShopLogger::addLog('[SPEDISCIQUI] validateToken - server non raggiungibile: ' . $e->getMessage(), 3);
            return false;
        }
    }


    //============================================
    // REQUESTS
    //============================================
    public function request(
        string $method,
        string $endpoint,
        string $token,
        array $payload = []
    ): mixed {

        try {

            $options = [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ];

            if (!empty($payload)) {
                $options['json'] = $payload;
            }

            $response = $this->client->request($method, $endpoint, $options);
            $body     = $response->getBody()->getContents();
            $data     = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                //PrestaShopLogger::addLog('[SPEDISCIQUI] JSON decode error: ' . json_last_error_msg(), 3);
                return null;
            }

            return $data;
        } catch (ClientException $e) {
            $response   = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 0;

            if ($statusCode === 401) {
                Configuration::deleteByName('SPEDISCIQUI_ACCESS_TOKEN');
                $body = $response ? $response->getBody()->getContents() : '';
                //PrestaShopLogger::addLog('[SPEDISCIQUI] Token scaduto o non valido (401): ' . $body, 2);
            }

            PrestaShopLogger::addLog('[SPEDISCIQUI] ClientException (HTTP ' . $statusCode . '): ' . $e->getMessage(), 3);
            return null;
        } catch (ConnectException $e) {
            PrestaShopLogger::addLog('[SPEDISCIQUI] Server non raggiungibile: ' . $e->getMessage(), 3);
            return null;
        } catch (RequestException $e) {
            PrestaShopLogger::addLog('[SPEDISCIQUI] RequestException: ' . $e->getMessage(), 3);
            return null;
        }
    }
}
