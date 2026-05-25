<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class SetupManager
{
    private const CONFIG_KEY = 'SPEDISCIQUI_SETUP_STEP';

    private ConfigRepositories      $config;
    private CredentialsRepositories $credentialsRepo;

    //=============================================
    // COSTRUTTORE
    //=============================================
    public function __construct(
        ConfigRepositories      $config,
        CredentialsRepositories $credentialsRepo
    ) {
        $this->config          = $config;
        $this->credentialsRepo = $credentialsRepo;
    }

    //=============================================
    // STEP CORRENTE
    //=============================================
    public function current(): int
    {
        return (int) $this->config->get(self::CONFIG_KEY, SetupSteps::TOKEN);
    }

    //=============================================
    // AVANZA STEP
    //=============================================
    public function advance(): void
    {
        $next = min($this->current() + 1, SetupSteps::DONE);
        $result = $this->config->set(self::CONFIG_KEY, (string) $next);

        if (!$result) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] advance() FALLITO — set() ha restituito false. Step rimasto: ' . $this->current(),
                3
            );
        } else {
            PrestaShopLogger::addLog(
                '[SpedisciQui] advance() OK — nuovo step: ' . $next,
                1
            );
        }
    }

    //=============================================
    // RESET STEP
    //=============================================
    public function reset(): void
    {
        $this->config->set(self::CONFIG_KEY, (string) SetupSteps::TOKEN);
    }

    //=============================================
    // SETUP COMPLETATO?
    //=============================================
    public function isComplete(): bool
    {
        return $this->current() >= SetupSteps::DONE;
    }

    //=============================================
    // RICALCOLA STEP DA DATI REALI
    //=============================================
    public function recalculate(): void
    {
        $credentials = $this->credentialsRepo->get();

        if (!$credentials || empty($credentials['access_token'])) {
            $this->reset();
            return;
        }

        $step = $this->current();

        if ($step < SetupSteps::SENDER) {
            $this->config->set(self::CONFIG_KEY, (string) SetupSteps::SENDER);
        }
    }
}
