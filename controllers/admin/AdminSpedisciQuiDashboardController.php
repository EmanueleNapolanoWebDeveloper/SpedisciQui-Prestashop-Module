<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminSpedisciQuiDashboardController extends ModuleAdminController
{
    // Layer di Business Logic (ereditati dal tuo ContentHandler)
    private SetupManager $setupManager;
    private CarrierRepository $carrierRepo;
    private ShipmentRepository $shipmentRepo;
    private SenderRepository $senderRepo;
    private PackageRepository $packRepo;

    private CarrierApi $carrierApi;

    private CarrierServices $carrierService;
    private PackageServices $packageService;
    private ShipmentCreationService $shipmentCreationService;

    // Handlers & Renderers
    private CarrierHandlers $carrierHandler;
    private ShipmentHandler $shipmentHandler;
    private SenderHandler $senderHandler;

    private DashboardRenderer $dashboardRenderer;
    private CarrierRenderer $carrierRenderer;
    private ShipmentRenderer $shipmentRenderer;
    private SenderRenderer $senderRenderer;

    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();

        // 1. Inizializzazione Repository e API Client
        $configRepo = new ConfigRepositories($this->context);
        $apiClient = new ApiClient($configRepo);
        $carrierApi = new CarrierApi($apiClient);
        $credentialsRepo = new CredentialsRepositories($this->context, $apiClient);

        $this->carrierRepo = new CarrierRepository($carrierApi, $credentialsRepo, $this->module);
        $this->senderRepo = new SenderRepository($this->context);
        $this->packRepo = new PackageRepository($this->context);
        $this->shipmentRepo = new ShipmentRepository();

        $this->setupManager = new SetupManager($configRepo, $credentialsRepo);

        // 2. Inizializzazione Servizi
        $this->carrierService = new CarrierServices($this->carrierRepo);
        $this->packageService = new PackageServices();
        $this->senderService = new SenderServices();

        $shipmentService = new ShipmentServices(
            $this->carrierRepo,
            $this->carrierService,
            $this->shipmentRepo,
            $credentialsRepo,
            $this->context,
            $this->module
        );

        $this->shipmentCreationService = new ShipmentCreationService(
            $this->shipmentRepo,
            $this->packageService,
            $apiClient,
            $credentialsRepo,
            $this->senderRepo,
            new LabelService()
        );

        // 3. Inizializzazione Renderers
        $this->carrierRenderer = new CarrierRenderer($this->module, $this->carrierRepo, $this->carrierService);
        $this->senderRenderer = new SenderRenderer($this->module, $this->context);
        $this->shipmentRenderer = new ShipmentRenderer($this->shipmentRepo, $this->module, $this->context, $shipmentService);
    }


    // =========================================================
    // initContent — ROUTING GET (Visualizzazione Viste / Sotto-viste)
    // =========================================================
    public function initContent(): void
    {
        parent::initContent();


        $this->addCSS(
            $this->module->getPathUri() . 'views/css/admin/layouts/dashboard_styles.css',
            'all',
            null,
            false
        );

        if ($this->setupManager->current() !== SetupSteps::DONE) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSpedisciQuiSetup'));
            return;
        }

        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiDashboard');

        if (Tools::getValue('carrier_code', '') !== '') {
            $carrierCode = Tools::getValue('carrier_code', '');
            $this->content = $this->carrierRenderer->renderCarrierTariffConfig($carrierCode, $formAction);
            return;
        }

        // ---------------------------------------------------------
        // SOTTO-VISTA: Modifica Indirizzo Mittente (FIX FIRMA)
        // ---------------------------------------------------------
        if (Tools::getValue('action') === 'editSender') {
            $idSender = (int) Tools::getValue('id_sender');

            // Recuperiamo i dati del mittente specifico da passare al form di modifica
            $senderData = $this->senderRepo->getSenderAddress();

            // FIX: Adesso passiamo i parametri allineati alla firma del Renderer (array $sender, string $formAction, array $data)
            $this->content = $this->senderRenderer->renderSenderUpdateForm(
                $senderData,
                $formAction,
                ['back_url' => $formAction . '&active_tab=senders']
            );
            return;
        }

        if (Tools::getValue('action') === 'shipmentReview' || Tools::getValue('action') === 'ShipmentDetails') {
            $idShipment = (int) Tools::getValue('id_shipment', Tools::getValue('is_shipment', 0));

            if ($idShipment <= 0) {
                $this->errors[] = $this->module->l('ID Spedizione non valido.');
                $this->renderMainDashboard($formAction);
                return;
            }

            $this->content = $this->shipmentRenderer->renderShipmentDetail($idShipment);
            return;
        }

        $this->renderMainDashboard($formAction);
    }



    // =========================================================
    // postProcess — ROUTING POST (Azioni dirette sui Service)
    // =========================================================
    public function postProcess(): void
    {
        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiDashboard');

        // ====== SENDER
        // salva/aggiorna mittente
        if (Tools::isSubmit('submitSpedisciQuiSender') || Tools::isSubmit('updateSpedisciQuiSender')) {
            $this->processUpdateSenderSave();
            Tools::redirectAdmin($formAction . '&active_tab=sender');
        }



        // ====== CARRIER

        // // Installa nuovo corriere
        if (Tools::isSubmit('submitSpedisciQuiCarriers')) {
            $this->processCarrierInstall();
            Tools::redirectAdmin($formAction . '&active_tab=carriers');
        }

        // // rimozione carrier
        if (Tools::isSubmit('removeSpedisciQuiCarriers')) {
            $this->processRemoveCarrier();
            Tools::redirectAdmin($formAction . '&active_tab=carriers');
        }

        // // salvare configurazione tariffe corriere
        if (Tools::isSubmit('saveTariffConfig')) {
            $this->processTariffSave();
            $carrierCode = Tools::getValue('carrier_code', '');
            $redirectUrl = $carrierCode
                ? $formAction . '&carrier_code=' . urlencode($carrierCode)
                : $formAction . '&active_tab=carriers';
        }


        // ====== SHIPMENT
        // creazione
        if (Tools::isSubmit('submitShipmentCreation')) {
            $this->processShipmentCreation();
            Tools::redirectAdmin($formAction . '&active_tab=shipments');
        }

        if (Tools::isSubmit('fetchShipmentLabel')) {
            $this->processShipmentLabelDownload();
            Tools::redirectAdmin($formAction . '&active_tab=shipments');
        }

        // rimozione
        if (Tools::isSubmit('cancelShipment')) {
            $this->processShipmentCancellation();
            Tools::redirectAdmin($formAction . '&active_tab=shipments');
        }

        parent::postProcess();
    }



    // =========================================================
    // LOGICHE DI ELABORAZIONE DEI FORM 
    // =========================================================


    // SENDER
    private function processUpdateSenderSave(): void
    {
        $data = $this->senderService->extractFromRequest();
        $errors = $this->senderService->validate($data);

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->errors[] = $this->module->l($error);
            }
            return;
        }

        if (!$this->senderRepo->save($data)) {
            $this->errors[] = $this->module->l('Errore durante il salvataggio del mittente.');
            return;
        }

        $this->confirmations[] = $this->module->l('Indirizzo mittente aggiornato con successo.');
    }


    // CARRIER
    private function processCarrierInstall(): void
    {
        $selectedCodes = Tools::getValue('selected_carriers', []);

        if (empty($selectedCodes)) {
            $this->errors[] = $this->module->l('Selezionare almeno un corriere da attivare.');
            return;
        }

        $allCarriers = $this->carrierRepo->getCarriers();
        $toSave = array_values(array_filter($allCarriers, fn($c) => in_array($c['code'], $selectedCodes, true)));

        $saved = 0;
        foreach ($toSave as $carrierData) {
            if ($this->carrierRepo->saveCarrierInPS($carrierData)) {
                $saved++;
            }
        }

        if ($saved > 0) {
            $this->confirmations[] = sprintf($this->module->l('%d nuovo/i corriere/i attivato/i.'), $saved);
        } else {
            $this->errors[] = $this->module->l('Nessun corriere installato. Verifica se è già presente.');
        }
    }

    private function processCarrierRemove(): void
    {
        $carrierCode = Tools::getValue('carrier_code', '');

        if (empty($carrierCode)) {
            $this->errors[] = $this->module->l('Codice corriere mancante.');
            return;
        }

        if (!$this->carrierRepo->removeCarrier($carrierCode)) {
            $this->errors[] = $this->module->l('Errore durante la rimozione del corriere.');
            return;
        }

        $this->carrierApi->invalidateCache();
        $this->context->controller->confirmations[] = sprintf($this->module->l('Corriere %s rimosso correttamente.'), $carrierCode);
    }


    private function processTariffsSave(): void
    {
        $carrierCode = Tools::getValue('carrier_code', '');

        if (empty($carrierCode)) {
            $this->errors[] = $this->module->l('Impossibile associare le tariffe: codice corriere vuoto.');
            return;
        }

        $weightFromArr = Tools::getValue('weight_from', []);
        $weightToArr = Tools::getValue('weight_to', []);
        $priceArr = Tools::getValue('price', []);
        $activeArr = Tools::getValue('active', []);

        if (!is_array($weightFromArr)) {
            $weightFromArr = [];
        }

        $rows = [];
        foreach (array_keys($weightFromArr) as $i) {
            $rows[] = [
                'weight_from' => $weightFromArr[$i] ?? '0',
                'weight_to' => $weightToArr[$i] ?? '0',
                'tariff' => $priceArr[$i] ?? '0',
                'is_active' => isset($activeArr[$i]) ? 1 : 0,
            ];
        }

        if ($this->carrierService->saveTariffs($carrierCode, $rows)) {
            $this->carrierApi->invalidateCache();
            $this->context->controller->confirmations[] = $this->module->l('Tariffe per fasce di peso aggiornate correttamente.');
        } else {
            $this->errors[] = $this->module->l('Errore durante il salvataggio delle tariffe.');
        }
    }


    // SHIPMENTS
    private function processShipmentCreation(): void
    {
        $idShipment = (int) Tools::getValue('id_shipment');
        $insuranceEnabled = (bool) Tools::getValue('insurance_enabled');
        $insuranceValue = (float) Tools::getValue('insurance_value');

        if ($idShipment <= 0) {
            $this->errors[] = $this->module->l('ID spedizione non valido.');
            return;
        }

        // --- STEP 1: Invia la richiesta di instradamento all'infrastruttura SpedisciQui ---
        $requestResult = $this->shipmentCreationService->sendShipmentRequest($idShipment, $insuranceEnabled, $insuranceValue);

        if (!$requestResult->isSuccess()) {
            $this->errors[] = $this->module->l('Fase 1 fallita: ') . $requestResult->getErrorMessage();
            return;
        }

        $this->confirmations[] = $this->module->l('Richiesta inviata con successo! La spedizione è ora pronta per il download della label.');
    }


    private function processShipmentLabelDownload(): void
    {
        $idShipment = (int) Tools::getValue('id_shipment');

        if ($idShipment <= 0) {
            $this->errors[] = $this->module->l('ID spedizione non valido per il recupero della label.');
            return;
        }

        // Esegue lo Step 2: scarica il PDF, estrae il tracking e aggiorna lo stato in 'label_created'
        $labelResult = $this->shipmentCreationService->fetchShipmentDataAndLabel($idShipment);

        if (!$labelResult->isSuccess()) {
            $this->errors[] = $this->module->l('Impossibile generare la lettera di vettura: ') . $labelResult->getErrorMessage();
            return;
        }

        $data = $labelResult->getData();
        $this->confirmations[] = sprintf(
            $this->module->l('Lettera di vettura scaricata con successo! Tracking assegnato: %s'),
            $data['tracking_number']
        );
    }



    // =========================================================
    // RENDERING DELLA VISTA CON I 3 TAB (OTTIMIZZATA)
    // =========================================================
    private function renderMainDashboard(string $formAction): void
    {
        $activeTab = Tools::getValue('active_tab', 'shipments');

        // Configurazione iniziale "Scudo di sicurezza" per Smarty
        $tplData = [
            'formAction' => $formAction,
            'active_tab' => $activeTab,
            'module_name' => $this->module->name,

            // Strutture Dati Generali
            'user' => null,
            'carriers' => [],
            'savedCarriers' => [],
            'savedCodes' => [],
            'senders' => [],
            'sender' => [], // <-- Inizializzato come array vuoto per evitare crash
            'shipments' => [],

            // Variabili di Filtro e Stato dei Sotto-Componenti
            'statusFilter' => Tools::getValue('status_filter', ''),
            'searchText' => Tools::getValue('search_text', ''),

            // Variabili di Routing
            'action' => Tools::getValue('action', ''),
            'token' => $this->token,
            'module_action_url' => $formAction,
            'editSenderUrl' => $formAction . '&action=editSender',
        ];

        // Recupero globale/preventivo dei dati del mittente (serve sia nel tab specifico che nei widget layout)
        $senderAddressData = $this->senderRepo->getSenderAddress();
        if (is_array($senderAddressData) && !empty($senderAddressData)) {
            $tplData['sender'] = $senderAddressData;  // Singolo mittente attivo configurato
            $tplData['senders'] = [$senderAddressData]; // Collezione per iterazioni {foreach}
        }

        // Caricamento condizionale delle restanti sorgenti dati
        switch ($activeTab) {
            case 'shipments':
                $tplData['shipments'] = $this->shipmentRepo->getShipments();
                break;

            case 'carriers':
                $carriers = $this->carrierRepo->getCarriers();
                $savedCarriers = $this->carrierRepo->getSavedCarriers();
                $savedCodes = array_column($savedCarriers, 'carrier_code');

                foreach ($savedCarriers as &$carrier) {
                    $carrier['configure_url'] = $formAction . '&carrier_code=' . urlencode($carrier['carrier_code']);
                }
                unset($carrier);

                $tplData['carriers'] = $carriers ?? [];
                $tplData['savedCarriers'] = $savedCarriers ?? [];
                $tplData['savedCodes'] = $savedCodes;
                break;

            case 'senders':
                // savedCarriers serve anche nel settings panel
                $savedCarriers = $this->carrierRepo->getSavedCarriers();
                foreach ($savedCarriers as &$carrier) {
                    $carrier['configure_url'] = $formAction . '&carrier_code=' . urlencode($carrier['carrier_code']);
                }
                unset($carrier);
                $tplData['savedCarriers'] = $savedCarriers ?? [];
                $tplData['savedCodes'] = array_column($savedCarriers, 'carrier_code');
                break;
        }

        $this->context->smarty->assign($tplData);

        $templatePath = _PS_MODULE_DIR_ . 'spedisciquishipping/views/templates/admin/layouts/dashboard_layout.tpl';
        $this->content = $this->context->smarty->fetch($templatePath);
    }


    public function display(): void
    {
        $this->context->smarty->assign('content', $this->content);
        parent::display();
    }
}
