<?php

error_log('__DIR__ = ' . __DIR__);

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

require __DIR__ . '/Utilities/SpedisciQuiApi.php';
require __DIR__ . '/Utilities/DatabaseManager.php';
require __DIR__ . '/Repositories/PackageRepository.php';
require __DIR__ . '/Repositories/SenderRepository.php';
require __DIR__ . '/views/FormRender.php';
require __DIR__ . '/Utilities/ContentHandler.php';
require __DIR__ . '/Utilities/ShippingCostResolve.php';
require __DIR__ . '/Hooks/CarrierHooks.php';

class spedisciquishipping extends CarrierModule
{
    protected SpedisciQuiApi $api;
    protected SenderRepository $senderRepo;
    protected PackageRepository $packageRepo;
    protected DatabaseManager $db;

    // ================================================================
    // COSTRUTTORE
    // ================================================================

    public function __construct()
    {
        $this->name = 'spedisciquishipping';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'Emanuele';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->confirmUninstall = $this->l('Sei sicuro di voler disinstallare?');


        parent::__construct();

        $this->displayName = 'SpedisciQui Shipping Primo';
        $this->description = 'Modulo spedizioni customizzato';
        $this->api = new SpedisciQuiApi();
        $this->packageRepo = new PackageRepository();
        $this->senderRepo = new SenderRepository();
        $this->db = new DatabaseManager();
    }


    // ================================================================
    // INSTALLAZIONE MODULO
    // ================================================================
    public function install(): bool
    {
        try {
            $parentInstall = parent::install();

            $dbResult = $this->db->createAllTableOnInstallation();

            return $parentInstall && $dbResult
                && $this->registerHook('actionCartSave')
                && $this->registerHook('displayCarrierExtraContent')
                && $this->registerHook('actionValidateStepComplete')
                && Configuration::updateValue('SPEDISCIQUI_ACCESS_TOKEN', null)
                && Configuration::updateValue('SPEDISCIQUI_SETUP_STEP', null);
        } catch (\Exception $e) {
            error_log('INSTALL ERROR: ' . $e->getMessage());
            return false;
        }
    }


    // ================================================================
    // DISINSTALLAZIONE MDOULO
    // ================================================================
    public function uninstall(): bool
    {
        return parent::uninstall()
            && $this->db->deleteAllModuleCarrier()
            && $this->db->dropAllSpedisciQuiTables()
            && Configuration::deleteByName('SPEDISCIQUI_ACCESS_TOKEN')
            && Configuration::deleteByName('SPEDISCIQUI_SETUP_STEP');
    }


    // ================================================================
    // FUNZIONE PER PRELEVARE SMARTY 
    // ================================================================
    public function getSmarty(): Smarty
    {
        return $this->context->smarty;
    }


    // ================================================================
    // CONTENT DEL MODULO
    // ================================================================
    public function getContent()
    {
        $handler = new ContentHandler($this);
        return $handler->handle();
    }



    // ================================================================
    // HOOK: CARRIER DEFAULT SUL CART
    // ================================================================
    public function hookActionCartSave(array $params): void
    {
        PrestaShopLogger::addLog('[SQ] hookActionCartSave CHIAMATO - ' . date('H:i:s'));
        (new CarrierHooks($this))->hookActionCarrierProcess($params);
    }

    // ================================================================
    // HOOK: EXTRA CONTENT (CHECKBOX ASSICURAZIONE)
    // ================================================================
    public function hookDisplayCarrierExtraContent(array $params): string
    {
        return (new CarrierHooks($this))->hookDisplayCarrierExtraContent($params);
    }

    // ================================================================
    // HOOK: SALVATAGGIO SCELTA ASSICURAZIONE
    // ================================================================
    public function hookActionValidateStepComplete(array $params): void
    {
        (new CarrierHooks($this))->hookActionValidateStepComplete($params);
    }


    // ================================================================
    // FUNZIONE PER PREZZO FINALE SPEDIZIONE (ABSTRACT METHOD DI CARRIERMODULO)
    // ================================================================
    public function getOrderShippingCost($params, $shippingCost): float|false
    {
        return $shippingCost > 0 ? $shippingCost : 5.0;
    }



    // ================================================================
    // FUNZIONE PER PREZZO FINALE SPEDIZIONE (ABSTRACT METHOD DI CARRIERMODULO)
    // ================================================================
    public function getOrderShippingCostExternal($params): float|false
    {
        return $this->getOrderShippingCost($params, 0);
    }
}
