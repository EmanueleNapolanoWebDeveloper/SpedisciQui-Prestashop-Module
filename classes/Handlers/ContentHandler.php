<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

class ContentHandler
{
    private spedisciquishipping     $module;
    private Context                 $context;
    private SetupManager            $setupManager;

    // repositories
    private CredentialsRepositories $credentialsRepo;
    private SenderRepository        $senderRepo;
    private PackageRepository       $packRepo;
    private CarrierRepository       $carrierRepo;
    private ShipmentRepository      $shipmentRepo;

    // handlers
    private CredentialsHandlers     $credentialsHandler;
    private SenderHandler           $senderHandler;
    private PackageHandler          $packHandler;
    private CarrierHandlers         $carrierHandler;
    private ShipmentHandler         $shipmentHandler;
    private DashboardHandlers        $dashboardHandler;

    // renderes
    private CredentialsRenderer     $credentialsRenderer;
    private SenderRenderer          $senderRenderer;
    private PackageRenderer         $packageRenderer;
    private CarrierRenderer         $carrierRenderer;
    private DashboardRenderer $dashboardRender;
    private ShipmentRenderer $shipmentRenderer;

    // servies
    private PackageServices $packageService;
    private ShipmentCreationService $shipmentCreationService;


    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(spedisciquishipping $module)
    {
        $this->module  = $module;
        $this->context = Context::getContext();

        // 1. configurazione base
        $configRepo    = new ConfigRepositories($this->context);
        $apiClient     = new ApiClient($configRepo);
        $moduleAdminLink = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token='     . Tools::getAdminTokenLite('AdminModules');


        // 2. repositories (tutti prima degli handler/renderer)
        $this->credentialsRepo = new CredentialsRepositories($this->context, $apiClient);
        $this->senderRepo      = new SenderRepository($this->context);
        $this->packRepo        = new PackageRepository($this->context);
        $this->carrierRepo     = new CarrierRepository(new CarrierApi($apiClient), $this->credentialsRepo, $this->module);
        $this->shipmentRepo = new ShipmentRepository();

        // 3. setup manager (ora $credentialsRepo esiste)
        $this->setupManager = new SetupManager($configRepo, $this->credentialsRepo);

        // 4. services condivisi (ora $carrierRepo esiste)
        $carrierServices = new CarrierServices($this->carrierRepo);

        $shipmentService = new ShipmentServices(
            $this->carrierRepo,
            $carrierServices,
            $this->shipmentRepo,
            $this->credentialsRepo,
            $this->context,
            $this->module
        );

        $this->packageService = new PackageServices();



        // 5. handlers
        $this->credentialsHandler = new CredentialsHandlers($module, $this->credentialsRepo, $this->setupManager);
        $this->senderHandler      = new SenderHandler($module, $this->senderRepo, $this->setupManager);
        $this->packHandler        = new PackageHandler($module, $this->packRepo, $this->setupManager);
        $this->carrierHandler     = new CarrierHandlers($this->module, $this->carrierRepo, $this->setupManager, $carrierServices);
        $this->dashboardHandler = new DashboardHandlers($this->carrierRepo, $this->module, $this->shipmentRepo, $this->senderRepo, $shipmentService);


        // 6. renderers
        $this->credentialsRenderer = new CredentialsRenderer($module, $this->credentialsRepo);
        $this->senderRenderer      = new SenderRenderer($module, $this->senderRepo, $this->context);
        $this->packageRenderer     = new PackageRenderer($this->module, $this->packRepo);
        $this->carrierRenderer     = new CarrierRenderer($this->module, $this->carrierRepo, $carrierServices);
        $this->dashboardRender     = new DashboardRenderer($this->module, $this->context);

        $this->shipmentRenderer    = new ShipmentRenderer(
            $this->shipmentRepo,
            $this->module,
            $this->context,
            $shipmentService
        );

        $this->shipmentCreationService = new ShipmentCreationService(
            $this->shipmentRepo,
            $this->packageService,
            $apiClient,
            $this->credentialsRepo,
            $this->senderRepo
        );

        $this->shipmentHandler = new ShipmentHandler(
            $moduleAdminLink,
            $this->shipmentCreationService,
            $this->shipmentRepo,
            $this->shipmentRenderer,
            $this->packageService,
            $apiClient,
        );

        $this->shipmentHandler = new ShipmentHandler(
            $moduleAdminLink,
            $this->shipmentCreationService,
            $this->shipmentRepo,
            $this->shipmentRenderer,
            $this->packageService,
            $apiClient
        );

        PrestaShopLogger::addLog(
            '[SQ-DEBUG] ContentHandler costruito. ShipmentRepo class: ' . get_class($this->shipmentRepo),
            1,
            null,
            'SpedisciQuiShipping'
        );
    }






    //========================================================
    // HANDLE - inizio
    //========================================================
    public function handle(): string
    {

        $this->shipmentHandler->handleRequest();

        $this->handleSubmits();

        $output = $this->credentialsHandler->getOutput()
            . $this->senderHandler->getOutput()
            . $this->packHandler->getOutput()
            . $this->carrierHandler->getOutput();



        // ----------------------------------------------
        // setup incompleto
        // ----------------------------------------------

        if ($this->setupManager->current() !== SetupSteps::DONE) {

            $this->context->smarty->assign([
                'content'    => $this->resolveSetupView(),
                'setup_step' => $this->setupManager->current(),
                'module_dir' => $this->module->getLocalPath(),
            ]);

            return $output . $this->module->display(
                $this->module->getLocalPath(),
                'views/templates/admin/layouts/initial_config_layout.tpl'
            );
        }

        // ----------------------------------------------
        // VIEW CONFIGURAZIOEN TARIFFA CORRIERE
        // ----------------------------------------------
        if ($this->isContextView()) {

            $carrierCode = Tools::getValue('carrier_code', '');

            return $output . $this->dashboardRender->renderWithContent(
                $this->carrierRenderer->renderCarrierTariffConfig($carrierCode)
            );
        }

        //detaglio psedizione
        if ($this->isShipmentReview()) {
            return $output . $this->shipmentHandler->handleShipmentReview();
        }

        $dashboardData = $this->dashboardHandler->buildDashboardData();

        // dashboard
        return $output . $this->dashboardRender->renderDashboard($dashboardData);
    }
    //========================================================
    // HANDLE - fine
    //========================================================







    //========================================================
    // SUBMITS - INIZIO
    //========================================================
    private function handleSubmits(): void
    {

        //===========> CREDENTIALS<======================
        if (Tools::isSubmit('submitSpedisciQuiCredentials')) {
            $this->credentialsHandler->handleSubmit();
            $this->redirectAfterSubmit();
        }


        //===========> SENDER <======================

        // submit Default Sender
        if (Tools::isSubmit('submitSpedisciQuiSender')) {
            $this->senderHandler->handleSubmit();
            $this->redirectAfterSubmit();
        }


        //===========> PACKAGE <======================

        // submit Default Package
        if (Tools::isSubmit('submitPackageForm')) {
            $this->packHandler->handleSubmit();
            $this->redirectAfterSubmit();
        }

        //===========> CARRIERS <======================
        // submit install carrier
        if (Tools::isSubmit('submitSpedisciQuiCarriers')) {
            $this->carrierHandler->handleSubmit();
            $this->redirectAfterSubmit();
        }

        // submit rimozione carrier
        if (Tools::isSubmit('removeSpedisciQuiCarriers')) {
            $this->carrierHandler->handleRemove();
            $this->redirectAfterSubmit();
        }

        // submit salvataggio Peso/tariffa
        if (Tools::isSubmit('saveTariffConfig')) {
            $this->carrierHandler->handleConfigureTariff();
            $this->redirectAfterSubmit();
        }

        //===========> SHIPMENTS <======================
        if (Tools::isSubmit('shipmentReview')) {
        }
    }
    //========================================================
    // SUBMITS - FINE
    //========================================================





    //========================================================
    // RESOLVE VIEW - INIZIO
    //========================================================
    private function resolveSetupView(): string
    {

        switch ($this->setupManager->current()) {

            case SetupSteps::TOKEN:
                return $this->credentialsRenderer->renderCredentialsForm();

            case SetupSteps::SENDER:
                return $this->senderRenderer->renderSenderForm();

            case SetupSteps::PACKAGE:
                return $this->packageRenderer->renderPackageForm();

            case SetupSteps::CARRIER:
                return $this->carrierRenderer->renderCarrierForm();

            case SetupSteps::DONE:
                return $this->carrierRenderer->renderCarrierDash();


            default:
                $this->setupManager->reset();
                return $this->credentialsRenderer->renderCredentialsForm();
        }
    }
    //========================================================
    // RESOLVE VIEW - FINE
    //========================================================





    // ─────────────────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────────────────


    //========================================================
    // VIEW DEL CONTESTO? - INIZIO
    //========================================================
    private function isContextView(): bool
    {
        return Tools::getValue('carrier_code', '') !== '';
    }
    //========================================================
    // VIEW DEL CONTESTO? - FINE
    //========================================================



    //========================================================
    // VIEW DEL SHIPMENT? - INIZIO
    //========================================================
    private function isShipmentReview(): bool
    {
        return Tools::getValue('action', '') === 'shipmentReview';
    }

    //========================================================
    // VIEW DEL SHIPMENT? - FINE
    //========================================================




    //========================================================
    // REINDIRIZZAMENTO DOPO UN SUBMIT - INIZIO
    //========================================================

    private function redirectAfterSubmit(): void
    {
        $url = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token=' . Tools::getAdminTokenLite('AdminModules');

        // carrier_code se presente va preservato
        $carrierCode = Tools::getValue('carrier_code', '');
        if ($carrierCode !== '') {
            $url .= '&carrier_code=' . urlencode($carrierCode);
        }

        Tools::redirectAdmin($url);
    }
    //========================================================
    // REINDIRIZZAMENTO DOPO UN SUBMIT - FINE
    //========================================================
}
