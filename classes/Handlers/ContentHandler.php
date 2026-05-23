<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

class ContentHandler
{
    private spedisciquishipping     $module;
    private Context                 $context;
    private SetupManager            $setupManager;
    private CredentialsRepositories $credentialsRepo;
    private SenderRepository        $senderRepo;
    private CredentialsHandlers     $credentialsHandler;
    private SenderHandler           $senderHandler;
    private CredentialsRenderer     $credentialsRenderer;
    private SenderRenderer          $senderRenderer;

    public function __construct(spedisciquishipping $module)
    {
        $this->module  = $module;
        $this->context = Context::getContext();

        $configRepo    = new ConfigRepositories($this->context);
        $apiClient     = new ApiClient($configRepo);

        $this->credentialsRepo = new CredentialsRepositories($this->context, $apiClient);
        $this->senderRepo      = new SenderRepository($this->context);
        $this->setupManager    = new SetupManager($configRepo, $this->credentialsRepo);

        $this->credentialsHandler  = new CredentialsHandlers($module, $this->credentialsRepo, $this->setupManager);
        $this->senderHandler       = new SenderHandler($module, $this->senderRepo, $this->setupManager);

        $this->credentialsRenderer = new CredentialsRenderer($module, $this->credentialsRepo);
        $this->senderRenderer      = new SenderRenderer($module, $this->senderRepo);
    }

    //========================================================
    // HANDLE
    //========================================================
    public function handle(): string
    {
        $this->handleSubmits();

        $this->context->smarty->assign('content', $this->resolveView());

        $output = $this->credentialsHandler->getOutput()
                . $this->senderHandler->getOutput();

        return $output . $this->module->display(
            $this->module->getLocalPath(),
            'views/templates/admin/config.tpl'
        );
    }

    //========================================================
    // SUBMIT
    //========================================================
    private function handleSubmits(): void
    {
        if (Tools::isSubmit('submitSpedisciQuiCredentials')) {
            $this->credentialsHandler->handleSubmit();
        }

        if (Tools::isSubmit('submitSpedisciQuiSender')) {
            $this->senderHandler->handleSubmit();
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

            case SetupSteps::DONE:
                // return $this->dashboardRenderer->renderDashboard();
                return $this->senderRenderer->renderSenderForm(); // placeholder

            default:
                $this->setupManager->reset();
                return $this->credentialsRenderer->renderCredentialsForm();
        }
    }
}