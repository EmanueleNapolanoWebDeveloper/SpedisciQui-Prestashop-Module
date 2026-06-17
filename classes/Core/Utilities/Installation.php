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
    // HANDLER DI INSTALLAZIONE -INIZIO
    //=============================================
    public function install(): bool
    {
        try {

            if (!$this->SQMigrations->runAll()) {
                throw new Exception('Migrazioni tabelle fallite!');
            }

            if (!$this->installTabs()) {
                throw new Exception('Installazione Tab/Controller fallita!');
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
    // HANDLER DI INSTALLAZIONE -FINE
    //=============================================


    //=============================================
    // REGISTRAZIONE CONTROLLER (TABS)
    //=============================================
    private function installTabs(): bool
    {
        // Definiamo i controller da installare
        $tabsData = [
            [
                'className'  => 'AdminSpedisciQuiDashboard',
                'parentTab'  => 'AdminParentShipping', // Sotto il menu "Spedizione" di PrestaShop
                'name'       => 'SpedisciQui Dashboard',
                'active'     => true
            ],
            [
                'className'  => 'AdminSpedisciQuiSetup',
                'parentTab'  => -1, // -1 significa "Nascosto". L'utente ci arriva solo via redirect
                'name'       => 'SpedisciQui Setup',
                'active'     => true
            ]
        ];

        foreach ($tabsData as $tabSpec) {
            // Se il tab esiste già, evitammo duplicati
            $idTab = (int) Tab::getIdFromClassName($tabSpec['className']);
            if ($idTab > 0) {
                continue;
            }

            $tab = new Tab();
            $tab->class_name = $tabSpec['className'];
            $tab->module     = $this->module->name;
            $tab->active     = $tabSpec['active'];
            
            // Trova l'ID del tab genitore (es. la sezione Spedizioni nativa)
            if (is_string($tabSpec['parentTab'])) {
                $tab->id_parent = (int) Tab::getIdFromClassName($tabSpec['parentTab']);
            } else {
                $tab->id_parent = (int) $tabSpec['parentTab'];
            }

            // Assegna il nome in tutte le lingue installate nel negozio
            $tab->name = [];
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $tabSpec['name'];
            }

            if (!$tab->add()) {
                return false;
            }
        }

        return true;
    }





    //=============================================
    // REGISTRAZIONE HOOKS - INIZIO
    //=============================================
    private function registerModuleHooks(): void
    {
        $hooks = [
            'actionValidateOrder',
            //'displayBackOfficeHeader'
            //'displayCarrierExtraContent',
        ];

        foreach ($hooks as $hook) {
            if (!$this->module->registerHook($hook)) {
                PrestaShopLogger::addLog(
                    '[SpedisciQui] Hook registration failed: ' . $hook,
                    2
                );
            }
        }
    }
    //=============================================
    // REGISTRAZIONE HOOKS - FINE
    //=============================================




    //=============================================
    // CONFIGURAZIONE DEFAULT - INIZIO
    //=============================================
    private function installDefaultConfig(): void
    {
        $defaults = [
            'SPEDISCIQUI_DEFAULT_CURRENCY' => 'EUR',
            'SPEDISCIQUI_TIMEOUT'          => 30,
            'SPEDISCIQUI_SETUP_STEP'       => 0,
            'SPEDISCIQUI_API_URL' => '',
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
    //=============================================
    // CONFIGURAZIONE DEFAULT - FINE
    //=============================================
}
