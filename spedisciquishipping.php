<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

// Core e API
require __DIR__ . '/classes/Core/Database/SQMigrations.php';
require __DIR__ . '/classes/Core/API/ApiClient.php';
require __DIR__ . '/classes/Core/API/CarrierApi.php';

// Utilities
require __DIR__ . '/classes/Core/Utilities/Installation.php';
require __DIR__ . '/classes/Core/Utilities/Uninstallation.php';
require __DIR__ . '/classes/Core/Utilities/SetupManage.php';
require __DIR__ . '/classes/Core/Utilities/SetupSteps.php';

// Repositories
require __DIR__ . '/classes/Repositories/ConfigRepositories.php';
require __DIR__ . '/classes/Repositories/CredentialsRepositories.php';
require __DIR__ . '/classes/Repositories/SenderRepository.php';
require __DIR__ . '/classes/Repositories/CarrierRepository.php';
require __DIR__ . '/classes/Repositories/ShipmentRepository.php';
require __DIR__ . '/classes/Repositories/PackageRepository.php';

// Services
require __DIR__ . '/src/Service/CredentialServices.php';
require __DIR__ . '/src/Service/PackageServices.php';
require __DIR__ . '/src/Service/CarrierServices.php';
require __DIR__ . '/src/Service/SenderServices.php';
require __DIR__ . '/src/Service/ShipmentService.php';
require __DIR__ . '/src/Service/ShipmentCreationService.php';
require __DIR__ . '/src/Service/LabelService.php';

// Renderers (Solo quelli effettivamente utilizzati)
require __DIR__ . '/src/Renderers/CredentialsRenderer.php';
require __DIR__ . '/src/Renderers/SenderRenderer.php';
require __DIR__ . '/src/Renderers/CarrierRenderer.php';
require __DIR__ . '/src/Renderers/ShipmentRenderer.php';
require __DIR__ . '/src/Renderers/PackageRenderer.php';

// Hooks
require __DIR__ . '/src/Hooks/InstalledHooks.php';

// DTO
require __DIR__ . '/classes/Core/API/DTO/ApiResponse.php';
require __DIR__ . '/classes/Core/API/DTO/ShipmentCreationResult.php';

class spedisciquishipping extends CarrierModule
{
    protected SQMigrations $SQMigrations;
    protected ConfigRepositories $config;
    protected InstalledHooks $installedHooks;
    protected ApiClient $apiClient;
    protected CredentialsRepositories $credentials;
    protected CarrierRepository $carrierRepo;
    protected CarrierServices $carrierService;
    protected ShipmentServices $shipmentService; // Corretto nome classe (Services)
    protected ShipmentRepository $shipmentRepo;
    protected PackageRepository $packRepo;
    public int $id_carrier = 0;

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
        $this->displayName = 'SpedisciQui Shipping';
        $this->description = 'Modulo spedizioni customizzato';

        parent::__construct();

        try {
            $context = Context::getContext();

            $this->config = new ConfigRepositories($context);
            $this->apiClient = new ApiClient($this->config);
            $this->credentials = new CredentialsRepositories($context, $this->apiClient);
            $this->SQMigrations = new SQMigrations();

            $this->carrierRepo = new CarrierRepository(
                new CarrierApi($this->apiClient),
                $this->credentials,
                $this
            );

            $this->packRepo = new PackageRepository();
            $this->carrierService = new CarrierServices($this->carrierRepo);
            $this->shipmentRepo = new ShipmentRepository();

            $this->shipmentService = new ShipmentServices(
                $this->carrierRepo,
                $this->carrierService,
                $this->shipmentRepo,
                $this->credentials,
                $context,
                $this
            );

            $this->shipmentRepo->setShipmentService($this->shipmentService);

            $this->installedHooks = new InstalledHooks(
                $this,
                $this->carrierRepo,
                $this->apiClient,
                $this->packRepo,
                $this->shipmentService
            );
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] COSTRUTTORE CRASH: ' . $e->getMessage()
                    . ' in ' . $e->getFile() . ':' . $e->getLine(),
                3
            );
        }
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
            $this->config
        );

        return $installation->install();
    }

    // ================================================================
    // DISINSTALLAZIONE MODULO
    // ================================================================
    public function uninstall(): bool
    {
        if (!parent::uninstall()) {
            return false;
        }

        $uninstallation = new Uninstallation(
            $this,
            $this->SQMigrations,
            new CarrierRepository(new CarrierApi($this->apiClient), $this->credentials, $this)
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
    // CONTENT DEL MODULO (Pulsante "Configura" nel BO)
    // ================================================================
    public function getContent()
    {
        $setupManager = new SetupManager($this->config, $this->credentials);

        // Se lo step di configurazione iniziale non è completato, manda l'utente al Setup
        if ($setupManager->current() !== SetupSteps::DONE) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSpedisciQuiSetup'));
        } else {
            // Altrimenti mandalo direttamente alla Dashboard di controllo principale
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSpedisciQuiDashboard'));
        }
        
        return '';
    }

    // ================================================================
    // FUNZIONE PER PREZZO FINALE SPEDIZIONE (ABSTRACT METHOD DI CARRIERMODULE)
    // ================================================================
    public function getOrderShippingCost($params, $shippingCost): float|false
    {
        $cart = $params;
        $carrierId = (int) $this->id_carrier;

        if ($carrierId <= 0) {
            PrestaShopLogger::addLog(
                sprintf('[SpedisciQui] getOrderShippingCost — id_carrier non disponibile | Cart #%d', (int) $cart->id),
                3,
                null,
                'Cart',
                (int) $cart->id,
                true
            );
            return false;
        }

        return $this->shipmentService->getRateShippingCost($cart, $carrierId);
    }

    // ================================================================
    // FUNZIONE PER PREZZO FINALE SPEDIZIONE ESTERNA
    // ================================================================
    public function getOrderShippingCostExternal($params): float|false
    {
        return $this->getOrderShippingCost($params, 0);
    }

    // ================================================================
    // HOOKS
    // ================================================================
    public function hookActionValidateOrder($params)
    {
        if (!$this->installedHooks) {
            PrestaShopLogger::addLog('[SpedisciQui] installedHooks è NULL', 3);
            return '';
        }

        return $this->installedHooks->hookActionValidateOrder($params);
    }

    public function hookDisplayBackOfficeHeader($params)
    {
        if (!$this->installedHooks) {
            PrestaShopLogger::addLog('[SpedisciQui] installedHooks è NULL', 3);
            return '';
        }

        return $this->installedHooks->hookDisplayBackOfficeHeader($params);
    }
}