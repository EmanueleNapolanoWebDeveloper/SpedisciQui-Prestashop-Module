<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class CredentialServices
{

    private ApiClient $apiClient;
    private $context;
    private CredentialsRepositories $credentialRepo;


    public function __construct()
    {
        $this->apiClient = new ApiClient(new ConfigRepositories());
        $this->context = Context::getContext();
        $this->credentialRepo = new CredentialsRepositories($this->context, $this->apiClient);
    }



    //===========================================
    //CONTROLLO CARATTERI
    //===========================================
    private function isTokenFormatValid(string $token): bool
    {
         if (empty($token)) {
            return false;
        }


            return (bool) preg_match('/^\d+\|[a-zA-Z0-9]{40,}$/', $token);
    }



    //===========================================
    //CALCOLO GIORNI SCADENZA
    //===========================================
    public function computeExpiryDate(int $months = 1): string
    {
        return date('Y-m-d H:i:s', strtotime('+' . $months . ' month'));
    }


    //===========================================
    //STATO DEL TOKEN
    //===========================================
      public function getTokenStatus(): string
    {
        $credentials = $this->credentialRepo->get();

        if ($credentials === null) {
            return 'none';
        }

        $daysLeft = $this->daysUntilExpiry();

        if ($daysLeft === null)  return 'none';
        if ($daysLeft === 0)     return 'expired';
        if ($daysLeft <= 7)      return 'expiring';

        return 'active';
    }



    //=================================================
    // TOKEN VALIDO
    //=================================================
    public function validateToken(string $token): bool
    {
        // 1. controllo formato
        if (!$this->isTokenFormatValid($token)) {
            PrestaShopLogger::addLog(
                '[spedisciqui] errroe isTokenFormatValid',
                2
            );
            return false;
        }

        $validationApi = $this->apiClient->validateTokenFromApi($token);

        if (!$validationApi) {
            PrestaShopLogger::addLog(
                '[spedisciqui] errroe validationAPi',
                2
            );
            return false;
        }

        // 2. recupero credenziali salvate
        $credentials = $this->credentialRepo->get();

        if (!$credentials || empty($credentials['access_token'])) {
            PrestaShopLogger::addLog(
                '[spedisciqui] errroe credentials',
                2
            );
            return true; // primo inserimento, quindi valido
        }

        // 3. confronto token (opzionale ma consigliato)
        if ($credentials['access_token'] !== $token) {
            PrestaShopLogger::addLog(
                '[spedisciqui] errroe token diverso',
                2
            );
            // token diverso da quello salvato
            return true;
        }

        // 4. controllo scadenza
        if (!empty($credentials['expires_at'])) {
            return strtotime($credentials['expires_at']) > time();
        }

        return true;
    }



    //==========================================
    // GIORNI ALLA SCADENZA
    //==========================================
    public function daysUntilExpiry(): ?int
    {
        $credentials = $this->credentialRepo->get();

        if (!$credentials || empty($credentials['expires_at'])) {
            return null;
        }

        $expiryTs = strtotime($credentials['expires_at']);

        if ($expiryTs === false) {
            return null;
        }

        $diff = $expiryTs - time();
        return $diff > 0 ? (int) ceil($diff / 86400) : 0;
    }
}
