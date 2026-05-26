<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Installation
{
    private SQMigrations $SQMigrations;
    private spedisciquishipping $module;
    private ConfigRepositories $config;

    //=============================================
    // COSTRUTTORE
    //=============================================
    public function __construct(
        spedisciquishipping $module,
        SQMigrations $SQMigrations,
        ConfigRepositories $config
    ) {
        $this->module       = $module;
        $this->SQMigrations = $SQMigrations;
        $this->config       = $config;
    }

    //=============================================
    // HANDLER DI INSTALLAZIONE
    //=============================================
    public function install(): bool
    {
        try {

            if (!$this->SQMigrations->runAll()) {
                throw new Exception('Migrazioni tabelle fallite!');
            }

            $this->registerModuleHooks();

            // 3. Config default
            $this->installDefaultConfig();
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui Install Error] ' . $e->getMessage(),
                3
            );
            return false;
        }

        return true;
    }

    //=============================================
    // REGISTRAZIONE HOOKS
    //=============================================
    private function registerModuleHooks(): void
    {
        $hooks = [
            // 'actionCarrierProcess',
            // 'actionValidateStepComplete',
            // 'actionCartSave',
            'actionCheckoutRender',
            'displayCarrierExtraContent',
        ];

        foreach ($hooks as $hook) {
            if (!$this->module->registerHook($hook)) {
                PrestaShopLogger::addLog(
                    '[SpedisciQui] Hook registration failed: ' . $hook,
                    2 // 2 = warning, 3 = error
                );
            }
        }
    }    

    //=============================================
    // CONFIGURAZIONE DEFAULT
    //=============================================
    private function installDefaultConfig(): void
    {
        $defaults = [
            'SPEDISCIQUI_DEFAULT_CURRENCY' => 'EUR',
            'SPEDISCIQUI_TIMEOUT'          => 30,
            'SPEDISCIQUI_SETUP_STEP'       => 0,
        ];

        foreach ($defaults as $key => $value) {
            try {
                $existing = $this->config->get($key);

                if ($existing !== null && $existing !== '') {
                    continue;
                }

                $this->config->set($key, (string) $value);
            } catch (Exception $e) {
                PrestaShopLogger::addLog(
                    '[SpedisciQui Config Install Error] ' . $key . ' — ' . $e->getMessage(),
                    3
                );
            }
        }
    }



    
}
