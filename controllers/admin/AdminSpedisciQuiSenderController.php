<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminSpedisciQuiSenderController extends ModuleAdminController
{
    private SetupManager $setupManager;
    private SenderRepository $senderRepo;
    private SenderServices $senderService;
    private SenderRenderer $senderRenderer;

    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();

        $configRepo = new ConfigRepositories($this->context);
        $apiClient = new ApiClient($configRepo);
        $credentialsRepo = new CredentialsRepositories($this->context, $apiClient);

        $this->setupManager = new SetupManager($configRepo, $credentialsRepo);
        $this->senderRepo = new SenderRepository($this->context);
        $this->senderService = new SenderServices();
        $this->senderRenderer = new SenderRenderer($this->module, $this->context);
    }

    // =========================================================
    // initContent — ROUTING GET
    // =========================================================
    public function initContent(): void
    {
        parent::initContent();

        $this->addCSS(
            $this->module->getPathUri() . 'views/css/admin/settings/settings_styles.css',
            'all',
            null,
            false
        );

        $this->addCSS(
            $this->module->getPathUri() . 'views/css/admin/settings/sender/sender_update_form.css',
            'all',
            null,
            false
        );

        if ($this->setupManager->current() !== SetupSteps::DONE) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSpedisciQuiSetup'));
            return;
        }

        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiSender');

        $this->renderSettingsPage($formAction);
    }

    // =========================================================
    // postProcess — ROUTING POST
    // =========================================================
    public function postProcess(): void
    {
        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiSender');

        if (Tools::isSubmit('submitSpedisciQuiSender')) {
            $this->processSenderSave();
            //Tools::redirectAdmin($formAction);
            return;
        }

        parent::postProcess();
    }

    // =========================================================
    // AZIONI POST
    // =========================================================
    private function processSenderSave(): void
    {
        $data = $this->senderService->extractFromRequest();
        $errors = $this->senderService->validate($data);

        PrestaShopLogger::addLog(
            print_r($data, true),
            1
        );

        $idSender = (int) Tools::getValue('id_sender');

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->errors[] = $this->module->l($error);
            }
            return;
        }

        if ($idSender > 0) {
            $data['id'] = $idSender;
            $success = $this->senderRepo->updateSenderAddress($data);
        } else {
            $success = $this->senderRepo->save($data);
        }

        if (!$success) {
            $this->errors[] = $this->module->l('Errore durante il salvataggio del mittente.');
        }

        $this->confirmations[] = $this->module->l('Indirizzo mittente aggiornato con successo.');

        $this->context->smarty->assign('data', ['success' => true]);
    }

    // =========================================================
    // RENDERING
    // =========================================================
    private function renderSettingsPage(string $formAction): void
    {
        $senderData = $this->senderRepo->getSenderAddress();

        $this->content = $this->senderRenderer->renderSenderUpdateForm(
            $senderData ?? [],
            $formAction,
            [
                'back_url' => $formAction,
            ]
        );

        $this->context->smarty->assign('content', $this->content);
    }

    public function display(): void
    {
        $this->context->smarty->assign('content', $this->content);
        parent::display();
    }
}