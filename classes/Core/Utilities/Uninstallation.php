<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class Uninstallation
{

    private spedisciquishipping $module;
    private SQMigrations $SQMigrations;
    private CarrierRepository $carrierRepo;



    //=============================================
    // COSTRUTTORE
    //=============================================
    public function __construct(
        spedisciquishipping $module,
        SQMigrations $SQMigrations,
        CarrierRepository $carrierRepo
    ) {
        $this->module = $module;
        $this->SQMigrations = $SQMigrations;
        $this->carrierRepo = $carrierRepo;
    }




    //=============================================
    // DISINSTALLAZIONE - INIZIO
    //=============================================
    public function uninstall(): bool
    {
        try {

            if (!$this->uninstallTabs()) {
                \PrestaShopLogger::addLog('[SpedisciQui UNINSTALL] Errore durante la rimozione dei Tab.', 2);
            }

            // rimozione dati carrier da tabelle
            $this->carrierRepo->removeAllCarriers();

            // rimuovi hook (non obbligatorio ma pulito)
            $this->unregisterModuleHooks();

            // elimina dati DB modulo
            $this->SQMigrations->deleteAll();

            PrestaShopLogger::addLog(
                '[SpedisciQui] Module uninstalled successfully',
                1
            );

            return true;
        } catch (Exception $e) {

            PrestaShopLogger::addLog(
                '[SpedisciQui UNINSTALL ERROR] ' . $e->getMessage(),
                3
            );

            return false;
        }
    }
    //=============================================
    // DISINSTALLAZIONE - FINE
    //=============================================




    //=============================================
    // RIMOZIONE CONTROLLER (TABS)
    //=============================================
    private function uninstallTabs(): bool
    {
        $classNames = [
            'AdminSpedisciQuiCarriers',
            'AdminSpedisciQuiShipments',
            'AdminSpedisciQuiSettings',
            'AdminSpedisciQuiSetup',
            // legacy — rimossi nel refactor ma potrebbero esistere in installazioni precedenti
            'AdminSpedisciQuiDashboard',
        ];

        foreach ($classNames as $className) {
            $idTab = (int) Tab::getIdFromClassName($className);

            if ($idTab <= 0) {
                continue;
            }

            $tab = new Tab($idTab);

            if (!$tab->delete()) {
                PrestaShopLogger::addLog(
                    sprintf('[SpedisciQui UNINSTALL] Impossibile eliminare il tab %s.', $className),
                    2
                );
                return false;
            }

            PrestaShopLogger::addLog(
                sprintf('[SpedisciQui UNINSTALL] Tab %s rimosso.', $className),
                1
            );
        }

        return true;
    }




    //=============================================
    // ELIMINAZIONE HOOKS - inizio
    //=============================================
    private function unregisterModuleHooks(): void
    {
        $hooks = [
            'actionValidateOrder',
            'actionProductFormBuilderModifier',
            'actionAfterCreateProductFormHandler',
            'actionAfterUpdateProductFormHandler',
            //'displayCarrierExtraContent',
        ];

        foreach ($hooks as $hook) {
            try {
                $this->module->unregisterHook($hook);
            } catch (Exception $e) {
                PrestaShopLogger::addLog(
                    '[SpedisciQui] Hook unregister failed: ' . $hook,
                    2
                );
            }
        }
    }
    //=============================================
    // ELIMINAZIONE HOOKS - fine
    //=============================================
}
