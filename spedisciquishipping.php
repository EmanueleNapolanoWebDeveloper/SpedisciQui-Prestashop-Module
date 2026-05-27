<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

require __DIR__ . '/classes/Core/Database/SQMigrations.php';
require __DIR__ . '/classes/Core/API/ApiClient.php';
require __DIR__ . '/classes/Core/API/CarrierApi.php';


// utilities
require __DIR__ . '/classes/Core/Utilities/Installation.php';
require __DIR__ . '/classes/Core/Utilities/Uninstallation.php';
require __DIR__ . '/classes/Core/Utilities/SetupManage.php';
require __DIR__ . '/classes/Core/Utilities/SetupSteps.php';

// repositories
require __DIR__ . '/classes/Repositories/ConfigRepositories.php';
require __DIR__ . '/classes/Repositories/CredentialsRepositories.php';
require __DIR__ . '/classes/Repositories/SenderRepository.php';
require __DIR__ . '/classes/Repositories/CarrierRepository.php';

// services
require __DIR__ . '/classes/Service/CredentialServices.php';
require __DIR__ . '/classes/Service/PackageServices.php';
require __DIR__ . '/classes/Service/CarrierServices.php';
require __DIR__ . '/classes/Service/SenderServices.php';




// renderets
require __DIR__ . '/classes/Renderers/CredentialsRenderer.php';
require __DIR__ . '/classes/Renderers/SenderRenderer.php';
require __DIR__ . '/classes/Renderers/CarrierRenderer.php';

// handlers
require __DIR__ . '/classes/Handlers/ContentHandler.php';
require __DIR__ . '/classes/Handlers/CredentialsHandlers.php';
require __DIR__ . '/classes/Handlers/SendersHandler.php';
require __DIR__ . '/classes/Handlers/CarrierHandlers.php';

// hooks
require __DIR__ . '/classes/Hooks/checkout/CustomCheckout.php';



class spedisciquishipping extends CarrierModule
{

    protected SQMigrations $SQMigrations;
    protected ConfigRepositories $config;
    protected $customCheckout;

    // ================================================================
    // COSTRUTTORE
    // ================================================================

    public function __construct()
    {
        $this->name = 'spedisciquishipping';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'SpedisciQui';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
        $this->bootstrap = true;
        $this->confirmUninstall = $this->l('Sei sicuro di voler disinstallare?');
        $this->displayName = 'SpedisciQui Shipping Primo';
        $this->description = 'Modulo spedizioni customizzato';


        parent::__construct();

        try {
            $context     = Context::getContext();
            $config      = new ConfigRepositories($context);
            $credentials = new CredentialsRepositories($context, new ApiClient($config));
            $apiClient   = new ApiClient($config);

            $this->SQMigrations = new SQMigrations();
            $this->config       = $config;
            $this->customCheckout = new CustomCheckout(
                $this,
                new CarrierRepository(new CarrierApi($apiClient), $credentials, $this)
            );
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] COSTRUTTORE CRASH: ' . $e->getMessage()
                    . ' in ' . $e->getFile() . ':' . $e->getLine(),
                3
            );
        };
    }


    // ================================================================
    // INSTALLAZIONE MODULO
    // ================================================================
    public function install(): bool
    {

        if (!parent::install()) {
            return false;
        }

        $installation = new Installation(
            $this,
            $this->SQMigrations,
            $this->config,
        );

        return $installation->install();
    }


    // ================================================================
    // DISINSTALLAZIONE MDOULO
    // ================================================================
    public function uninstall(): bool
    {
        if (!parent::uninstall()) {
            return false;
        }

        $uninstallation = new Uninstallation(
            $this,
            $this->SQMigrations,
        );

        return $uninstallation->uninstall();
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

    


    // ================================================================
    // ================================================================
    // HOOKS
    // ================================================================
    // ================================================================


    public function hookDisplayCarrierExtraContent($params)
    {
        if (!$this->customCheckout) {
            PrestaShopLogger::addLog('[SpedisciQui] customCheckout è NULL', 3);
            return '';
        }

        return $this->customCheckout->hookDisplayCarrierExtraContent($params);
    }


}
