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

    // handlers
    private CredentialsHandlers     $credentialsHandler;
    private SenderHandler           $senderHandler;
    private PackageHandler          $packHandler;
    private CarrierHandlers         $carrierHandler;

    // renderes
    private CredentialsRenderer     $credentialsRenderer;
    private SenderRenderer          $senderRenderer;
    private PackageRenderer         $packageRenderer;
    private CarrierRenderer         $carrierRenderer;
    private DashboardRenderer $dashboardRender;

    public function __construct(spedisciquishipping $module)
    {
        $this->module  = $module;
        $this->context = Context::getContext();

        // 1. infrastruttura base
        $configRepo    = new ConfigRepositories($this->context);
        $apiClient     = new ApiClient($configRepo);

        // 2. repositories (tutti prima degli handler/renderer)
        $this->credentialsRepo = new CredentialsRepositories($this->context, $apiClient);
        $this->senderRepo      = new SenderRepository($this->context);
        $this->packRepo        = new PackageRepository($this->context);
        $this->carrierRepo     = new CarrierRepository(new CarrierApi($apiClient), $this->credentialsRepo, $this->module);

        // 3. setup manager (ora $credentialsRepo esiste)
        $this->setupManager = new SetupManager($configRepo, $this->credentialsRepo);

        // 4. services condivisi (ora $carrierRepo esiste)
        $carrierServices = new CarrierServices($this->carrierRepo);

        // 5. handlers
        $this->credentialsHandler = new CredentialsHandlers($module, $this->credentialsRepo, $this->setupManager);
        $this->senderHandler      = new SenderHandler($module, $this->senderRepo, $this->setupManager);
        $this->packHandler        = new PackageHandler($module, $this->packRepo, $this->setupManager);
        $this->carrierHandler     = new CarrierHandlers($this->module, $this->carrierRepo, $this->setupManager, $carrierServices);

        // 6. renderers
        $this->credentialsRenderer = new CredentialsRenderer($module, $this->credentialsRepo);
        $this->senderRenderer      = new SenderRenderer($module, $this->senderRepo);
        $this->packageRenderer     = new PackageRenderer($this->module, $this->packRepo);
        $this->carrierRenderer     = new CarrierRenderer($this->module, $this->carrierRepo, $carrierServices);
        $this->dashboardRender     = new DashboardRenderer($this->module, $this->context);
    }

    //========================================================
    // HANDLE
    //========================================================
    public function handle(): string
    {

        PrestaShopLogger::addLog(
            '[SQ] carrier_code=' . Tools::getValue('carrier_code', 'VUOTO')
                . ' | isContextView=' . ($this->isContextView() ? 'TRUE' : 'FALSE')
                . ' | setupStep=' . $this->setupManager->current()
                . ' | GET=' . http_build_query($_GET),
            1,
            null,
            'SpedisciQui'
        );

        $this->handleSubmits();

        $output = $this->credentialsHandler->getOutput()
            . $this->senderHandler->getOutput()
            . $this->packHandler->getOutput()
            . $this->carrierHandler->getOutput();

        // setup incompleto
        if ($this->setupManager->current() !== SetupSteps::DONE) {

            $this->context->smarty->assign([
                'content'    => $this->resolveSetupView(),
                'setup_step' => $this->setupManager->current(),
                'module_dir' => $this->module->getLocalPath(),
            ]);

            return $output . $this->module->display(
                $this->module->getLocalPath(),
                'views/templates/admin/initial_config_layout.tpl'
            );
        }

        // dashboard
        if ($this->isContextView()) {

            $carrierCode = Tools::getValue('carrier_code', '');

            return $output . $this->dashboardRender->renderWithContent(
                $this->carrierRenderer->renderCarrierTariffConfig($carrierCode)
            );
        }

        return $output . $this->dashboardRender->renderDashboard([
            'carriers' => $this->carrierRepo->getCarriers(),
            'savedCodes' => $this->carrierRepo->getSavedCarriers(),
            'savedCarriers' => $this->carrierRepo->getSavedCarriers(),
        ]);
    }


    private function isContextView(): bool
    {
        return Tools::getValue('carrier_code', '') !== '';
    }

    //========================================================
    // SUBMIT
    //========================================================
    private function handleSubmits(): void
    {

        //===========> CREDENTIALS<======================
        if (Tools::isSubmit('submitSpedisciQuiCredentials')) {
            $this->credentialsHandler->handleSubmit();
        }


        //===========> SENDER <======================

        // submit Default Sender
        if (Tools::isSubmit('submitSpedisciQuiSender')) {
            $this->senderHandler->handleSubmit();
        }


        //===========> PACKAGE <======================

        // submit Default Package
        if (Tools::isSubmit('submitPackageForm')) {
            $this->packHandler->handleSubmit();
        }

        //===========> CARRIERS <======================
        // submit install carrier
        if (Tools::isSubmit('submitSpedisciQuiCarriers')) {
            $this->carrierHandler->handleSubmit();
        }

        // submit rimozione carrier
        if (Tools::isSubmit('removeSpedisciQuiCarriers')) {
            $this->carrierHandler->handleRemove();
        }

        // submit salvataggio Peso/tariffa
        if (Tools::isSubmit('saveTariffConfig')) {
            $this->carrierHandler->handleConfigureTariff();
        }
    }

    //========================================================
    // RESOLVE VIEW
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
}
