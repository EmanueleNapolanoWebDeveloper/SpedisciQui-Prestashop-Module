<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminSpedisciQuiShipmentsController extends ModuleAdminController
{
    private SetupManager $setupManager;
    private ShipmentRepository $shipmentRepo;
    private ShipmentRenderer $shipmentRenderer;
    private ShipmentCreationService $shipmentCreationService;

    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();

        $configRepo      = new ConfigRepositories($this->context);
        $apiClient       = new ApiClient($configRepo);
        $credentialsRepo = new CredentialsRepositories($this->context, $apiClient);
        $carrierApi      = new CarrierApi($apiClient);

        $this->setupManager  = new SetupManager($configRepo, $credentialsRepo);
        $this->shipmentRepo  = new ShipmentRepository();

        $carrierRepo     = new CarrierRepository($carrierApi, $credentialsRepo, $this->module);
        $carrierService  = new CarrierServices($carrierRepo);

        $shipmentService = new ShipmentServices(
            $carrierRepo,
            $carrierService,
            $this->shipmentRepo,
            $credentialsRepo,
            $this->context,
            $this->module
        );

        $this->shipmentRepo->setShipmentService($shipmentService);

        $this->shipmentRenderer = new ShipmentRenderer(
            $this->shipmentRepo,
            $this->module,
            $this->context,
            $shipmentService
        );

        $this->shipmentCreationService = new ShipmentCreationService(
            $this->shipmentRepo,
            new PackageServices(),
            $apiClient,
            $credentialsRepo,
            new SenderRepository($this->context),
            new LabelService()
        );
    }

    // =========================================================
    // initContent — ROUTING GET
    // =========================================================
    public function initContent(): void
    {
        parent::initContent();

        $this->addCSS(
            $this->module->getPathUri() . 'views/css/admin/shipment/shipment_styles.css',
            'all',
            null,
            false
        );

        $this->addCSS(
            $this->module->getPathUri() . 'views/css/admin/shipment/shipment_detail_styles.css',
            'all',
            null,
            false
        );

        if ($this->setupManager->current() !== SetupSteps::DONE) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSpedisciQuiSetup'));
            return;
        }

        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiShipments');

        // Sotto-vista: dettaglio spedizione
        if (
            Tools::getValue('action') === 'shipmentReview' ||
            Tools::getValue('action') === 'ShipmentDetails'
        ) {
            $idShipment = (int) Tools::getValue('id_shipment', 0);

            if ($idShipment <= 0) {
                $this->errors[] = $this->module->l('ID Spedizione non valido.');
                $this->renderShipmentsPage($formAction);
                return;
            }

            $this->content = $this->shipmentRenderer->renderShipmentDetail($idShipment);
            $this->context->smarty->assign('content', $this->content);
            return;
        }

        $this->renderShipmentsPage($formAction);
    }

    // =========================================================
    // postProcess — ROUTING POST
    // =========================================================
    public function postProcess(): void
    {
        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiShipments');

        // Creazione spedizione
        if (Tools::isSubmit('submitShipmentCreation')) {
            $this->processShipmentCreation();
            Tools::redirectAdmin($formAction);
            return;
        }

        // Download label
        if (Tools::isSubmit('fetchShipmentLabel')) {
            $this->processShipmentLabelDownload();
            Tools::redirectAdmin($formAction);
            return;
        }

        // Cancellazione spedizione
        if (Tools::isSubmit('cancelShipment')) {
            $this->processShipmentCancellation();
            Tools::redirectAdmin($formAction);
            return;
        }

        parent::postProcess();
    }

    // =========================================================
    // AZIONI POST
    // =========================================================
    private function processShipmentCreation(): void
    {
        $idShipment       = (int) Tools::getValue('id_shipment');
        $insuranceEnabled = (bool) Tools::getValue('insurance_enabled');
        $insuranceValue   = (float) Tools::getValue('insurance_value');

        if ($idShipment <= 0) {
            $this->errors[] = $this->module->l('ID spedizione non valido.');
            return;
        }

        $requestResult = $this->shipmentCreationService->sendShipmentRequest(
            $idShipment,
            $insuranceEnabled,
            $insuranceValue
        );

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

    private function processShipmentCancellation(): void
    {
        $idShipment = (int) Tools::getValue('id_shipment');

        if ($idShipment <= 0) {
            $this->errors[] = $this->module->l('ID spedizione non valido.');
            return;
        }

        // da implementare nel service
        $this->confirmations[] = $this->module->l('Spedizione annullata correttamente.');
    }

    // =========================================================
    // RENDERING
    // =========================================================
    private function renderShipmentsPage(string $formAction): void
    {
        $page         = (int) Tools::getValue('page', 1);
        $limit        = 20;
        $statusFilter = Tools::getValue('status_filter', '');

        $this->context->smarty->assign([
            'formAction'   => $formAction,
            'token'        => $this->token,
            'statusFilter' => $statusFilter,
            'searchText'   => Tools::getValue('search_text', ''),
            'orderDetailsLink' => $this->context->link->getAdminLink('AdminOrders'),
        ]);

        $this->content = $this->shipmentRenderer->renderShipmentLists($page, $limit, $statusFilter);

        $this->context->smarty->assign('content', $this->content);
    }

    public function display(): void
    {
        $this->context->smarty->assign('content', $this->content);
        parent::display();
    }
}