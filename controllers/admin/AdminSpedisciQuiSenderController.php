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

        if ($this->setupManager->current() !== SetupSteps::DONE) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSpedisciQuiSetup'));
            return;
        }

        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiSender');

        $action = Tools::getValue('action', '');
        $idSender = (int) Tools::getValue('id_sender', 0);

        if ($idSender > 0) {
            $this->renderSenderUpdateForm($idSender, $formAction);
        } elseif ($action === 'create') {
            $this->renderSenderCreateForm($formAction);
        } else {
            $this->renderSendersList($formAction);
        }
    }

    // =========================================================
    // postProcess — ROUTING POST
    // =========================================================
    public function postProcess(): void
    {
        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiSender');

        // creaziomne nuovo shop
        if (Tools::isSubmit('submitCreateSender')) {
            $this->processSenderCreate();
            return;
        }

        // update Shop
        if (Tools::isSubmit('submitUpdateSender')) {
            $idSender = Tools::getValue('id_sender', 0);
            $this->processSenderUpdate((int) $idSender);
            return;
        }

        // delte Shop
        if (Tools::isSubmit('submitDeleteSender')) {
            $idSender = Tools::getValue('id_sender', 0);
            $this->processDeleteSender($idSender);
            return;
        }

        parent::postProcess();
    }

    // =========================================================
    // AZIONI POST
    // =========================================================

    // crea sender
    private function processSenderCreate(): void
    {

        $formAction = $this->getFormAction();

        try {
            $data = $this->senderService->extractFromRequest();
            $data['id_shop'] = (int) $this->context->shop->id;


            $errors = $this->senderService->validate($data);
            if (!empty($errors)) {
                foreach ($errors as $err) {
                    $this->errors[] = $err;
                }
                $this->content = $this->senderRenderer->renderSenderCreateForm(
                    $formAction,
                    (int) $data['id_shop'],
                    $data
                );
                return;
            }


            $this->senderRepo->save($data);
            $this->confirmations[] = $this->module->l('Mittente creato con successo');

            $this->redirectToList();
        } catch (Throwable $e) {
            $this->errors[] = $this->module->l('Errore nella creazione: ') . $e->getMessage();
        }

        $this->redirectToList();
    }


    // aggiorna sender
    private function processSenderUpdate(int $idSender): void
    {
        $formAction = $this->getFormAction();

        if ($idSender <= 0) {
            $this->errors[] = $this->module->l('ID Mittente non valido');
            $this->redirectToList();
            return;
        }

        try {
            $data = $this->senderService->extractFromRequest();
            $data['id_sender'] = $idSender;
            $data['id_shop'] = (int) $this->context->shop->id;

            $errors = $this->senderService->validate($data);
            if (!empty($errors)) {
                foreach ($errors as $err) {
                    $this->errors[] = $err;
                }
                $existingSender = $this->senderRepo->getSenderAddressById($idSender);
                $merged = array_merge($existingSender ?? [], $data);
                $this->content = $this->renderSenderUpdateForm($merged['id'], $formAction);
                return;
            }

            $this->senderRepo->updateSenderAddress($idSender, $data);
            $this->confirmations[] = $this->module->l('Mittente aggiornato con successo');
            $this->redirectToList();
        } catch (Throwable $e) {
            $this->errors[] = $this->module->l('Errore nell\'aggiornamento mittente: ') . $e->getMessage();
        }

        $this->redirectToList();
    }


    // elimina sender
    private function processDeleteSender(int $idSender): void
    {

        if (!$idSender) {
            $this->errors[] = $this->module->l('ID mittente non valido.');
            $this->redirectToList();
            return;
        }

        try {
            $this->senderRepo->deleteSender($idSender);
            $this->confirmations[] = $this->module->l('Mittente eliminato con successso');
        } catch (\Throwable $e) {
            $this->errors[] = $this->module->l('Errore nell\' eliminazione: ') . $e->getMessage();
        }

        $this->redirectToList();
    }

    // =========================================================
    // RENDERING
    // =========================================================


    // mostra lista di tutti i senders
    private function renderSendersList(string $formAction): void
    {
        $idShop = (int) $this->context->shop->id;

        try {
            $senders = $this->isMultishopContext()
                ? $this->senderRepo->getAllSenders()
                : $this->senderRepo->getSenderAddressById($idShop);

            if (!empty($senders) && isset($senders['id_shop'])) {
                $senders = [$senders];
            }
        } catch (Throwable $e) {
            $this->errors[] = $this->module->l('Errore nel recupero dei mittenti: ') . $e->getMessage();
            $senders = [];
        }

        $this->content = $this->senderRenderer->renderSendersList(
            $senders,
            $formAction,
            $this->isMultishopContext()
        );
    }

    // mostra form creazione nuovo senders
    private function renderSenderCreateForm(string $formAction)
    {
        $this->content = $this->senderRenderer->renderSenderCreateForm(
            $formAction,
            (int) $this->context->shop->id,
        );
    }


    // aggiornamento sender
    private function renderSenderUpdateForm(int $idSender, string $formAction): void
    {
        $sender = $this->senderRepo->getSenderAddressById($idSender);
        if (!$sender) {
            $this->errors[] = $this->module->l('Mittente non trovato.');
            $this->renderSendersList($formAction);
            return;
        }
        $this->content = $this->senderRenderer->renderSenderUpdateForm($sender, $formAction);
    }


    // =========================================================
    // HELPERS
    // =========================================================
    public function display(): void
    {
        $this->context->smarty->assign('content', $this->content);
        parent::display();
    }

    private function getFormAction(): string
    {
        return $this->context->link->getAdminLink(
            'AdminSpedisciQuiSender',
            true
        );
    }



    private function redirectToList(): void
    {
        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminSpedisciQuiSender', true)
        );
    }

    /**
     * Controlla se siamo in contesto multishop "all shops".
     */
    private function isMultishopContext(): bool
    {
        return Shop::getContext() === Shop::CONTEXT_ALL
            || $this->context->employee->isSuperAdmin();
    }





}