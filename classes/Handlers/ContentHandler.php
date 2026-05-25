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

    public function __construct(spedisciquishipping $module)
    {
        $this->module  = $module;
        $this->context = Context::getContext();


        $configRepo    = new ConfigRepositories($this->context);
        $apiClient     = new ApiClient($configRepo);
        $this->credentialsRepo = new CredentialsRepositories($this->context, $apiClient);
        $apiClient->setCredentialsRepo($this->credentialsRepo);

        // repositories
        $this->credentialsRepo = new CredentialsRepositories($this->context, $apiClient);
        $this->setupManager    = new SetupManager($configRepo, $this->credentialsRepo);
        $this->senderRepo      = new SenderRepository($this->context);
        $this->packRepo        = new PackageRepository($this->context);
        $this->carrierRepo     = new CarrierRepository($apiClient, $this->credentialsRepo, $this->module);

        // handlers
        $this->credentialsHandler  = new CredentialsHandlers($module, $this->credentialsRepo, $this->setupManager);
        $this->senderHandler       = new SenderHandler($module, $this->senderRepo, $this->setupManager);
        $this->packHandler         = new PackageHandler($module, $this->packRepo, $this->setupManager);
        $this->carrierHandler      = new CarrierHandlers($this->module, $this->carrierRepo, $this->setupManager);

        // renderers
        $this->credentialsRenderer = new CredentialsRenderer($module, $this->credentialsRepo);
        $this->senderRenderer      = new SenderRenderer($module, $this->senderRepo);
        $this->packageRenderer = new PackageRenderer($this->module, $this->packRepo);
        $this->carrierRenderer = new CarrierRenderer($this->module, $this->carrierRepo);
    }

    //========================================================
    // HANDLE
    //========================================================
    public function handle(): string
    {
        $this->handleSubmits();

        $this->context->smarty->assign([
            'content'    => $this->resolveView(),
            'setup_step' => $this->setupManager->current(),
            'module_dir' => $this->module->getLocalPath(),
        ]);

        $output = $this->credentialsHandler->getOutput()
            . $this->senderHandler->getOutput()
            . $this->packHandler->getOutput()
            . $this->carrierHandler->getOutput();

        $layoutTemplate = $this->setupManager->current() == SetupSteps::DONE
            ? 'views/templates/admin/dashboard_layout.tpl'
            : 'views/templates/admin/initial_config_layout.tpl';

        return $output . $this->module->display(
            $this->module->getLocalPath(),
            $layoutTemplate
        );
    }

    //========================================================
    // SUBMIT
    //========================================================
    private function handleSubmits(): void
    {

        //===========> ACCESS TOKEN <======================
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
    }

    //========================================================
    // RESOLVE VIEW
    //========================================================
    private function resolveView(): string
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
                return $this->module->display(
                    $this->module->getLocalPath(),
                    'views/templates/admin/dashboard_layout.tpl'
                );

            default:
                $this->setupManager->reset();
                return $this->credentialsRenderer->renderCredentialsForm();
        }
    }
}
