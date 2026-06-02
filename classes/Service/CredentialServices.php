<?php

class CredentialServices
{

    private ApiClient $apiClient;
    private $context;
    private CredentialsRepositories $credentialRepo;


    public function __construct()
    {
        $this->apiClient = new ApiClient(new ConfigRepositories());
        $this->context = Context::getContext();
        $this->credentialRepo = new CredentialsRepositories($this->context,$this->apiClient);
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

        $validationApi = $this->apiClient->validateTokenFromApi($token);

        if (!$validationApi) {
            return false;
        }

        // 2. recupero credenziali salvate
        $credentials = $this->credentialRepo->get();

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



    //==========================================
    // GIORNI ALLA SCADENZA
    //==========================================
    public function daysUntilExpiry(): ?int
    {
        $credentials = $this->credentialRepo->get();

        if (!$credentials || empty($credentials['expires_at'])) {
            return null;
        }

        $diff = strtotime($credentials['expires_at']) - time();
        return max(0, (int) ceil($diff / 86400));
    }
}
