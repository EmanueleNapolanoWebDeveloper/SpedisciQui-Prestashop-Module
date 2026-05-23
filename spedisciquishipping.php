<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

require __DIR__ . '/classes/Migrations/SQMigrations.php';
require __DIR__ . '/classes/Core/ApiClient.php';
require __DIR__ . '/classes/Core/Installation.php';
require __DIR__ . '/classes/Repositories/ConfigRepositories.php';
require __DIR__ . '/classes/Repositories/CredentialsRepositories.php';
require __DIR__ . '/classes/Handlers/ContentHandler.php';
require __DIR__ . '/classes/Handlers/CredentialsHandlers.php';
require __DIR__ . '/classes/Renderers/CredentialsRenderer.php';
require __DIR__ . '/classes/Renderers/SenderRenderer.php';
require __DIR__ . '/classes/Repositories/SenderRepository.php';
require __DIR__ . '/classes/Core/SetupManage.php';
require __DIR__ . '/classes/Core/SetupSteps.php';
require __DIR__ . '/classes/Handlers/SendersHandler.php';


class spedisciquishipping extends CarrierModule
{

    protected SQMigrations $SQMigrations;
    protected ConfigRepositories $config;

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

        $this->SQMigrations = new SQMigrations();
        $this->config       = new ConfigRepositories(Context::getContext());
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

        $uninstallation = new Installation(
            $this,
            $this->SQMigrations,
            $this->config,
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
}
