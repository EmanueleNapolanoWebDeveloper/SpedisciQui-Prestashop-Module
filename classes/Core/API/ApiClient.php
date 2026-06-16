<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use SpedisciQui\DTO\ApiResponse;

class ApiClient
{
    // ─── Cache token ─────────────────────────────────────────────────────────
    private const TOKEN_CACHE_KEY     = 'sq_token_valid_until';
    private const TOKEN_CACHE_KEY_EXP = 'sq_token_valid_until_exp';
    private const TOKEN_CACHE_TTL     = 300;

    // ─── Timeout (secondi) ───────────────────────────────────────────────────
    private const TIMEOUT_DEFAULT  = 10;
    private const TIMEOUT_LABEL    = 30;
    private const TIMEOUT_VALIDATE = 5;

    // ─── Retry ───────────────────────────────────────────────────────────────
    private const MAX_RETRIES      = 3;
    private const RETRY_STATUSES   = [429, 502, 503, 504];
    private const RETRY_BASE_US    = 500000; // 0.5s — raddoppia ad ogni tentativo

    // ─── Rate limit client-side ──────────────────────────────────────────────
    private const RATE_LIMIT_MAX    = 60;
    private const RATE_LIMIT_WINDOW = 60; // secondi

    // endpoints
    private const ALLOWED_ENDPOINTS = [
        '/api/auth/verify',
        '/api/v1/create_shipment',
        '/api/getCarriers',
    ];

    private string $baseUrl;
    private Client $client;
    private array  $requestTimestamps = [];


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
        // controllo cahce
        $cacheUntil = (int) \ConfigRepositories::get(self::TOKEN_CACHE_KEY);

        if ($cacheUntil > time()) {
            return true;
        }


        try {

            $response = $this->client->get('/api/auth/verify', [
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                ],
            ]);

            PrestaShopLogger::addLog(
                '[SQ] validateToken REQUEST: ' . json_encode([
                    'url' => $this->baseUrl . '/api/auth/verify',
                    'token_first_chars' => substr($token, 0, 15) . '...',
                ]),
                1
            );



            $statusCode = $response->getStatusCode();

            if ($statusCode !== 200) {
                PrestaShopLogger::addLog(
                    '[spedisciqui] fallito reposnse',
                    2
                );
            }

            $responseBody = (string) $response->getBody();

            PrestaShopLogger::addLog(
                '[SpedisciQui] Risposta API - Stato: ' . $statusCode . ' - Body: ' . $responseBody,
                1
            );

            $isValid = $statusCode === 200;

            if ($isValid) {
                \ConfigRepositories::set(self::TOKEN_CACHE_KEY_EXP, time() + self::TOKEN_CACHE_TTL);
            }

            return $isValid;
        } catch (ClientException $e) {
            \PrestaShopLogger::addLog(
                '[SQ] validateToken fallito (HTTP ' . $e->getResponse()->getStatusCode() . ')',
                2
            );
            return false;
        } catch (ConnectException $e) {
            PrestaShopLogger::addLog('[SPEDISCIQUI] validateToken - server non raggiungibile: ' . $e->getMessage(), 3);
            return false;
        }
    }

    //============================================
    // REQUEST PUBBLIC CON RETYR
    //============================================
    public function request(
        string $method,
        string $endpoint,
        string $token,
        array $payload = [],
        int $timeout = self::TIMEOUT_DEFAULT
    ): ?ApiResponse {
        $this->validateEndPoint($endpoint);
        $this->checkRateLimits();

        $attempt = 0;
        $lastResult = null;

        do {
            $attempt++;

            $lastResult = $this->doRequest($method, $endpoint, $token, $payload, $timeout);

            if ($lastResult === null) {
                break;
            }

            if ($lastResult->isSuccess() || !in_array($lastResult->getStatusCode(), self::RETRY_STATUSES, true)) {
                return $lastResult;
            }

            if ($attempt < self::MAX_RETRIES) {
                $delay = (int) pow(2, $attempt - 1) * self::RETRY_BASE_US;

                \PrestaShopLogger::addLog(
                    sprintf(
                        '[SQ] Tentativo %d/%d fallito (HTTP %d) per %s — retry tra %.1fs',
                        $attempt,
                        self::MAX_RETRIES,
                        $lastResult->getStatusCode(),
                        $endpoint,
                        $delay / 1000000
                    ),
                    2
                );

                usleep($delay);
            }
        } while ($attempt < self::MAX_RETRIES);

        return $lastResult;
    }


    //============================================
    // REQUESTS INTERNA
    //============================================
    private function doRequest(
        string $method,
        string $endpoint,
        string $token,
        array $payload = [],
        int $timeout
    ): ?ApiResponse {

        try {

            $options = [
                'timeout' => $timeout,
                'headers' => [
                    'Authorization' => "Bearer {$token}",
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ],
            ];

            if (!empty($payload)) {
                $options['json'] = $payload;
            }

            $response = $this->client->request($method, $endpoint, $options);
            $statusCode = $response->getStatusCode();
            $body = trim($response->getBody()->getContents());


            $data = $this->decodeJson($body, $endpoint);


            if ($data === null) {
                return null;
            }


            PrestaShopLogger::addLog(
                '[SPEDISCIQUI] RAW RESPONSE: ' . $body,
                1
            );

            return ApiResponse::success($statusCode, $data);
        } catch (ClientException $e) {
            $response   = $e->getResponse();
            $statusCode = $response ? $response->getStatusCode() : 0;
            $body = $response ? trim($response->getBody()->getContents()) : '';
            $data = json_decode($body, true);
            $errorMsg = (is_array($data) && isset($data['message'])) ? $data['message'] : $e->getMessage();
            $errorType = match (true) {
                $statusCode === 401 => 'auth',
                $statusCode === 403 => 'forbidden',
                $statusCode === 422 => 'validation',
                $statusCode >= 500  => 'server',
                default             => 'client',
            };

            PrestaShopLogger::addLog('[SPEDISCIQUI] ClientException (HTTP ' . $statusCode . '): ' . $this->sanitizeMessage($e->getMessage()), 3);

            return ApiResponse::failure($statusCode, $errorMsg, $errorType);
        } catch (ConnectException $e) {
            PrestaShopLogger::addLog('[SPEDISCIQUI] Server non raggiungibile: ' . $this->sanitizeMessage($e->getMessage()), 3);
            return ApiResponse::failure(0, $e->getMessage(), 'network');
        } catch (RequestException $e) {
            PrestaShopLogger::addLog('[SPEDISCIQUI] RequestException: ' . $this->sanitizeMessage($e->getMessage()), 3);
            return ApiResponse::failure(0, $e->getMessage(), 'network');
        }
    }



    //=========================================
    // HELPER
    //===========================================
    public function invalidateTokenCache(): void
    {
        ConfigRepositories::set(self::TOKEN_CACHE_KEY_EXP, 0);
    }


    // VALIDAZIONE ENDPOINT
    private function validateEndPoint(string $endpoint): void
    {
        if (!in_array($endpoint, self::ALLOWED_ENDPOINTS, true)) {
            throw new InvalidArgumentException(
                '[SpedisciQui] Endpoint non autorizzato: ' . $endpoint
            );
        }
    }

    // CONTROLLO RATE LIMITS
    private function checkRateLimits(): void
    {
        $now = time();

        $this->requestTimestamps = array_values(array_filter(
            $this->requestTimestamps,
            fn(int $t) => ($now - $t) < self::RATE_LIMIT_WINDOW
        ));

        if (count($this->requestTimestamps) >= self::RATE_LIMIT_MAX) {
            throw new RuntimeException(
                sprintf(
                    '[SpedisciQui] Rate Limit raggiunto: max %d richieste per %ds.',
                    self::RATE_LIMIT_MAX,
                    self::RATE_LIMIT_WINDOW
                )
            );
        }

        $this->requestTimestamps[] = $now;
    }

    // ===========================================
    //  DECODIFICA BODY JSON
    //============================================
    private function decodeJson(string $body, string $endpoint): ?array
    {
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            \PrestaShopLogger::addLog(
                sprintf(
                    '[SQ] JSON decode error su %s: %s | body (primi 200): %s',
                    $endpoint,
                    json_last_error_msg(),
                    substr($body, 0, 200)
                ),
                3
            );
            return null;
        }

        return $data;
    }


    /**
     * Rimuove eventuali Bearer token dai messaggi di errore prima del log.
     */
    private function sanitizeMessage(string $message): string
    {
        return preg_replace(
            '/Bearer\s+[A-Za-z0-9\-._~+\/]+=*/i',
            'Bearer ***',
            $message
        );
    }
}
