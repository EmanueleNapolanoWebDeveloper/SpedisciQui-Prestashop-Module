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
        $this->module = $module;
        $this->SQMigrations = $SQMigrations;
        $this->config = $config;
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
        $tabsData = [
            [
                'className' => 'AdminSpedisciQuiCarriers',
                'parentTab' => 'AdminParentShipping',
                'name' => 'Corrieri SpedisciQui',
                'active' => true,
            ],
            [
                'className' => 'AdminSpedisciQuiShipments',
                'parentTab' => 'AdminParentOrders',
                'name' => 'Ordini SpedisciQui',
                'active' => true,
            ],
            [
                'className' => 'AdminSpedisciQuiSender',
                'parentTab' => 'ShopParameters',
                'name' => 'Mittente SpedisciQui',
                'active' => true,
            ],
            [
                'className' => 'AdminSpedisciQuiSetup',
                'parentTab' => -1,
                'name' => 'SpedisciQui Setup',
                'active' => true,
            ],
        ];

        foreach ($tabsData as $tabSpec) {
            $idTab = (int) Tab::getIdFromClassName($tabSpec['className']);

            if ($idTab > 0) {
                $existingTab = new Tab($idTab);
                if ($existingTab->module !== $this->module->name) {
                    PrestaShopLogger::addLog(
                        sprintf('[SpedisciQui] Tab orfano %s — eliminazione in corso.', $tabSpec['className']),
                        2
                    );
                    $existingTab->delete();
                } else {
                    PrestaShopLogger::addLog(
                        sprintf('[SpedisciQui] Tab %s già installato correttamente.', $tabSpec['className']),
                        1
                    );
                    continue;
                }
            }

            $tab = new Tab();
            $tab->class_name = $tabSpec['className'];
            $tab->module = $this->module->name;
            $tab->active = (bool) $tabSpec['active'];

            if (is_string($tabSpec['parentTab'])) {
                $parentId = (int) Tab::getIdFromClassName($tabSpec['parentTab']);
                $tab->id_parent = $parentId > 0 ? $parentId : 0;
            } else {
                $tab->id_parent = (int) $tabSpec['parentTab'];
            }

            $tab->name = [];
            foreach (Language::getLanguages(true) as $lang) {
                $tab->name[$lang['id_lang']] = $tabSpec['name'];
            }

            if (!$tab->add()) {
                PrestaShopLogger::addLog(
                    sprintf('[SpedisciQui] Errore salvataggio tab %s.', $tabSpec['className']),
                    3
                );
                return false;
            }

            PrestaShopLogger::addLog(
                sprintf('[SpedisciQui] Tab %s installato con successo.', $tabSpec['className']),
                1
            );
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
            'actionProductFormBuilderModifier',
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
            'SPEDISCIQUI_TIMEOUT' => 30,
            'SPEDISCIQUI_SETUP_STEP' => 0,
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
