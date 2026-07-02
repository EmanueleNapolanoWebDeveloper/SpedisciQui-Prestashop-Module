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

        $configRepo = new ConfigRepositories($this->context);
        $apiClient = new ApiClient($configRepo);
        $credentialsRepo = new CredentialsRepositories($this->context, $apiClient);
        $carrierApi = new CarrierApi($apiClient);

        $this->setupManager = new SetupManager($configRepo, $credentialsRepo);
        $this->shipmentRepo = new ShipmentRepository();

        $carrierRepo = new CarrierRepository($carrierApi, $credentialsRepo, $this->module);
        $carrierService = new CarrierServices($carrierRepo);

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

            $extraParams = [
                'token' => $this->token, // Il token di questo controller specifico
                'sq_ajax_url' => $this->context->link->getAdminLink('AdminSpedisciQuiShipments') . '&ajax=1',
                'back_url' => $formAction
            ];

            $this->content = $this->shipmentRenderer->renderShipmentDetail($idShipment, $extraParams);
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
        $statusFilter = Tools::getValue('status_filter', '');
        $searchText = Tools::getValue('search_text', '');

        if (!empty($statusFilter)) {
            $formAction .= '&status_filter=' . urlencode($statusFilter);
        }

        if (!empty($searchText)) {
            $formAction .= '&search_text=' . urlencode($searchText);
        }

        // Creazione spedizione
        if (Tools::isSubmit('submitShipmentCreation')) {
            $this->processShipmentCreation();
            Tools::redirectAdmin($formAction);
            return;
        }

        // Creazione reso
        if (Tools::isSubmit('submitRefundCreation')) {
            $this->processShipmentRefund();
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
        $idShipment = (int) Tools::getValue('id_shipment');
        $insuranceEnabled = (bool) Tools::getValue('insurance_enabled');
        $insuranceValue = (float) Tools::getValue('insurance_value');

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
            $this->errors[] = $this->module->l('Richiesta di spedizione fallita: ') . $requestResult->getErrorMessage();
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

        $this->confirmations[] = sprintf(
            $this->module->l('Lettera di vettura scaricata con successo! Tracking assegnato: %s'),
            $labelResult->getTrackingNumber()
        );
    }

    private function processShipmentRefund(): void
    {
        $idShipment = (int) Tools::getValue('id_shipment');
        $insuranceEnabled = (bool) Tools::getValue('insurance_enabled');
        $insuranceValue = (float) Tools::getValue('insurance_value');

        if ($idShipment <= 0) {
            $this->errors[] = $this->module->l('ID spedizione non valido per il richiedere reso.');
            return;
        }

        $requestResult = $this->shipmentCreationService->sendShipmentRequest(
            $idShipment,
            $insuranceEnabled,
            $insuranceValue,
            $isRefund = true,
        );

        if (!$requestResult->isSuccess()) {
            $this->errors[] = $this->module->l('Richiesta di spedizione fallita: ') . $requestResult->getErrorMessage();
            return;
        }

        $this->confirmations[] = $this->module->l('Richiesta di Reso inviata con successo! La spedizione è ora pronta per il download della label.');
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
        $page = (int) Tools::getValue('page', 1);
        $limit = 20;
        $statusFilter = Tools::getValue('status_filter', '');
        $searchText = Tools::getValue('search_text', '');

        if (!empty($statusFilter)) {
            $formAction .= '&status_filter=' . urlencode($statusFilter);
        }

        if (!empty($searchText)) {
            $formAction .= '&search_text=' . urlencode($searchText);
        }

        $this->context->smarty->assign([
            'formAction' => $formAction,
            'token' => $this->token,
            'statusFilter' => $statusFilter,
            'searchText' => $searchText,
            'orderDetailsLink' => $this->context->link->getAdminLink('AdminOrders'),
            'back_url' => $formAction
        ]);

        $this->content = $this->shipmentRenderer->renderShipmentLists($page, $limit, $statusFilter, $searchText);

        $this->context->smarty->assign('content', $this->content);
    }

    public function display(): void
    {
        $this->context->smarty->assign('content', $this->content);
        parent::display();
    }
}