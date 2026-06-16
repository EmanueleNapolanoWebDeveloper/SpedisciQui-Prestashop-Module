<?php

use ModuleAdminController;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminSpedisciQuiSetupController extends ModuleAdminController
{
    // =========================================================
    // DIPENDENZE
    // =========================================================
    private SetupManager $setupManager;
    private ApiClient $apiClient;

    private CarrierApi $carrierApi;


    // credentials
    private CredentialsRepositories $credentialRepo;
    private CredentialServices $credentialService;
    private CredentialsRenderer $credentialRenderer;

    // senders
    private SenderRepository $senderRepo;
    private SenderServices $senderService;
    private SenderRenderer $senderRenderer;

    // package
    private PackageRepository $packRepo;
    private PackageServices $packageService;
    private PackageRenderer $packageRenderer;

    // carriers
    private CarrierRepository $carrierRepo;
    private CarrierServices $carrierService;
    private CarrierRenderer $carrierRenderer;


    // =========================================================
    // COSTRUTTORE
    // =========================================================
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'configuration';
        $this->identifier = 'id_configuration';

        parent::__construct();

        $context = Context::getContext();

        $configRepo = new ConfigRepositories(Context::getContext());

        $apiClient = new ApiClient($configRepo);

        $carrierApi = new CarrierApi($apiClient);

        // credenziali
        $this->credentialRepo = new CredentialsRepositories($context,$apiClient);
        $this->credentialService = new CredentialServices(
            $this->credentialRepo,
            $apiClient
        );
        $this->credentialRenderer = new CredentialsRenderer($this->module,$this->credentialRepo);
        $this->setupManager = new SetupManager(
            $configRepo,
            $this->credentialRepo
        );

        // sender
        $this->senderRepo = new SenderRepository($this->context);
        $this->senderService = new SenderServices();
        $this->senderRenderer = new SenderRenderer($this->module, $this->context);

        // package
        $this->packRepo = new PackageRepository();
        $this->packageService = new PackageServices();
        $this->packageRenderer = new PackageRenderer($this->module, $this->packRepo);

        // carriers
        $this->carrierRepo = new CarrierRepository(
            $carrierApi,
            $this->credentialRepo,
            $this->module
        );
        $this->carrierService = new CarrierServices($this->carrierRepo);
        $this->carrierRenderer = new CarrierRenderer($this->module, $this->carrierRepo, $this->carrierService);
    }



    // =========================================================
    // initContent — routing GET
    // =========================================================

    public function initContent(): void
    {
        parent::initContent();

        // step da mostrare
        $currentStep = $this->setupManager->current();


        // in caso di Setup Completato, redirect al controller principale
        if ($currentStep === SetupSteps::DONE) {
            Tools::redirectAdmin(
                $this->context->link->getAdminLink('AdminSpedisciQuiDashboard')
            );
            return;
        }

        // renderizza step corrente se non è cpmpletato
        $this->renderSetupSteps($currentStep);
    }


    // =========================================================
    // postProcess — routing POST
    // =========================================================
    public function postProcess(): void
    {
        // credentials
        if (Tools::isSubmit('submitSpedisciQuiCredentials')) {
            $this->processTokenSubmit();
            return;
        }

        // sender
        if (Tools::isSubmit('submitSpedisciQuiSender')) {
            $this->processSenderSubmit();
            return;
        }

        // package
        if (Tools::isSubmit('submitSpedisciQuiDefaultPackage')) {
            $this->processDefaultPackageSubmit();
        }

        // CARRIERS
        if (Tools::isSubmit('submitSpedisciQuiCarriers')) {
            $this->processCarrierSubmit();
            return;
        }

        parent::postProcess();
    }



    // =========================================================
    // AZIONI POST
    // =========================================================


    // =========================================================
    // AZIONE: salvataggio token
    // =========================================================
    public function processTokenSubmit(): void
    {

        $token = trim(Tools::getValue('SPEDISCIQUI_ACCESS_TOKEN', ''));

        // GUARD - campo vuoto
        if (empty($token)) {
            $this->errors[] = $this->l('Il token non può essere vuoto.');
            return;
        }


        // GUARD -formato non valido
        if (!$this->credentialService->validateToken($token)) {
            $this->errors[] = $this->l('Token non valido o formato errato.');
            return;
        }

        // SALVATAGGIO TOKEN
        if (!$this->credentialRepo->save($token)) {
            $this->errors[] = $this->module->l('Errore durante il salvataggio del token.');
            return;
        }

        // Solo se tutto OK: avanza e mostra conferma
        $this->setupManager->advance();

        $expiresAt = $this->credentialService->computeExpiryDate(1);

        // ── Messaggio di successo e redirect PRG ──
        $this->confirmations[] = sprintf(
            $this->module->l('Token salvato correttamente. Scadenza: %s'),
            date('d/m/Y', strtotime($expiresAt))
        );

        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminSpedisciQuiSetup')
        );
    }


    // =========================================================
    // AZIONE: salvataggio sender
    // =========================================================
    private function processSenderSubmit(): void
    {

        $data = $this->senderService->extractFromRequest();
        $errors = $this->senderService->validate($data);

        // caso di errori
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->errors[] = $this->module->l($error);
            }
            return;
        }

        if (!$this->senderRepo->save($data)) {
            $this->errors[] = $this->l('Errore durante il salvataggio del mittente.');
            return;
        }

        // avanza 
        $this->setupManager->advance();

        $this->confirmations[] = $this->l('Indirizzo mittente salvato correttamente');

        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminSpedisciQuiSetup')
        );
    }



    // =========================================================
    // AZIONE: salvataggio default package
    // =========================================================
    private function processDefaultPackageSubmit(): void
    {

        $data = [
            'name' => trim(Tools::getValue('package_name', 'Default')),
            'weight' => Tools::getValue('package_weight', '1.000'),
            'length' => Tools::getValue('package_length', '30.00'),
            'width' => Tools::getValue('package_width', '20.00'),
            'height' => Tools::getValue('package_height', '10.00'),
            'is_default' => Tools::getValue('package_is_default', 0) ? 1 : 0,
        ];

        // Validazione
        $errors = $this->packageService->validate($data);

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->errors[] = $this->module->l($error);
            }
            return;
        }

        // Salvataggio
        if (!$this->packRepo->savePackage(null, $data)) {
            $this->errors[] = $this->module->l('Errore durante il salvataggio del pacco.');
            return;
        }

        // Avanza step setup
        $this->setupManager->advance();

        $this->confirmations[] = $this->module->l('Dati pacco salvato con successo.');

        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminSpedisciQuiSetup')
        );
    }

    // =========================================================
    // AZIONE: salvataggio corrieri (init config)
    // =========================================================
    private function processCarrierSubmit(): void
    {
        $selectedCodes = Tools::getValue('selected_carriers', []);

        if (empty($selectedCodes)) {
            $this->errors[] = $this->module->l('Selezionare almeno un corriere');
        }

        $allCarriers = $this->carrierRepo->getCarriers();

        if (empty($allCarriers)) {
            $this->errors[] = $this->module->l('Impossibile recuperare i corrieri dalla piattaforma.');
            return;
        }

        $toSave = array_values(array_filter(
            $allCarriers,
            fn($c) => in_array($c['code'], $selectedCodes, true)
        ));

        if (empty($toSave)) {
            $this->errors[] = $this->module->l('Nessun Corriere Selezionato trovato');
            return;
        }

        $saved = 0;
        $errors = 0;

        foreach ($toSave as $carrierData) {
            if ($this->carrierRepo->saveCarrierInPS($carrierData)) {
                $saved++;
            } else {
                $errors++;
                PrestaShopLogger::addLog('[SpedisciQui] saveCarrierInPS fallito per: ' . $carrierData['code'], 3);
            }
        }

        if ($errors > 0) {
            $this->errors[] = sprintf($this->module->l('%d corriere/i non salvato/i. Controlla i log.'), $errors);
        }

        if ($saved > 0 && $errors === 0) {
            $this->setupManager->advance();
            $this->confirmations[] = sprintf($this->module->l('%d corriere/i attivato/i correttamente.'), $saved);

            // ─── REDIRECT DIRETTO ALLA DASHBOARD ───
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSpedisciQuiDashboard'));
            return;
        } elseif ($saved > 0) {
            $this->confirmations[] = sprintf($this->module->l('%d corriere/i attivato/i parzialmente con errori.'), $saved);
        }

        // in caso di erori bloccanti
        Tools::redirectAdmin($this->context->link->getAdminLink('AdminSpedisciQuiSetup'));
    }

    // =========================================================
    // AZIONI POST - FINE
    // =========================================================




    // =========================================================
    // PREPARAZIONE DATI PER RENDERER
    // =========================================================

    // CREDENTIALS
    private function renderCredentialStep(): void
    {

        $credentials = $this->credentialRepo->get();
        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiSetup');

        $tokenData = [
            'access_token' => $credentials['access_token'] ?? '',
            'expires_at' => $credentials['expires_at'] ?? null,
            'days_left' => $this->credentialService->daysUntilExpiry(),
            'status' => $this->credentialService->getTokenStatus(),
        ];

       $this->credentialRenderer->renderCredentialsForm($tokenData, $formAction);
    }



    // SENDER
    private function renderSenderStep(): void
    {
        $existing = $this->senderRepo->getDefault();
        $sender = $this->senderService->normalizeForView($existing ?? []);
        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiSetup');

        $this->senderRenderer->renderSenderForm($sender, $formAction);
    }


    // PACKAGE
    private function renderPackageStep(): void
    {

        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiStep');

        $this->packageRenderer->renderPackageForm($formAction);
    }


    // CARRIERS
    private function renderCarriersStep(): void
    {

        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiStep');

        $this->carrierRenderer->renderCarrierForm($formAction);
    }



    // =========================================================
    // PREPARAZIONE DATI PER RENDERER - FINE
    // =========================================================



    // =========================================================
    // RENDERING DEGLI STEP
    // =========================================================

    private function renderSetupSteps(string $step): void
    {
        switch ($step) {

            // CASO 1 - TOKEN
            case SetupSteps::TOKEN:
                $this->renderCredentialStep();
                break;

            // CASO 2 - INSERIMENTO MITTENTE
            case SetupSteps::SENDER:
                $this->renderSenderStep();
                break;

            // CASO 3 - INSERIMENTO PACCHI DEFAULT
            case SetupSteps::PACKAGE:
                $this->renderPackageStep();
                break;

            // CASO 4 - LISTA CARRIER DA API E SALVATAGGIO
            case SetupSteps::CARRIER:
                $this->renderCarriersStep();
                break;

            // DEFAULT - RICOMINCIA DA TOKEN
            default:
                $this->setupManager->reset();
                $this->renderTokenStep;
                break;
        }
        ;
    }
}