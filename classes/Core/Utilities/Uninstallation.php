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
    // ELIMINAZIONE HOOKS - inizio
    //=============================================
    private function unregisterModuleHooks(): void
    {
        $hooks = [
            'actionValidateOrder',
            'displayBackOfficeHeader'
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
