<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class DashboardRenderer
{

    private spedisciquishipping $module;
    private Context $context;
    private CarrierRepository $carrierRepo;
    private ShipmentRepository $shipmentRepo;
    private SenderRepository $senderRepo;
    private ShipmentServices $shipmentService;



    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(
        spedisciquishipping $module,
        Context $context,
        CarrierRepository $carrierRepo,
        ShipmentRepository $shipmentRepo,
        SenderRepository $senderRepo,
        ShipmentServices $shipmentService,
    ) {
        $this->module = $module;
        $this->context = $context;
        $this->carrierRepo = $carrierRepo;
        $this->shipmentRepo = $shipmentRepo;
        $this->senderRepo = $senderRepo;
        $this->shipmentService = $shipmentService;
    }


    //==========================================
    // COSTRUTTORE DATI PER DASHBOARD - INIZIO
    //==========================================
    public function buildDashboardData(): array
    {
        $page  = max(1, (int) Tools::getValue('page', 1));

        $limit = (int) Tools::getValue('limit', 20);
        $limit = max(1, min(100, $limit)); // safety cap

        $statusFilter = (string) Tools::getValue('status_filter', '');

        $idShop       = (int) Context::getContext()->shop->id ?: 1;

        $offset = ($page - 1) * $limit;

        $adminLink = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token='     . Tools::getAdminTokenLite('AdminModules');

        // carrier
        $Carriers  = $this->carrierRepo->getCarriers();
        $savedCarriers = $this->carrierRepo->getSavedCarriers();
        $savedCodes   = array_column($savedCarriers, 'carrier_code');

        // shipments
        $shipments      = $this->shipmentRepo->getShipments($idShop, $statusFilter, $limit, $offset);
        $totalShipments = $this->shipmentService->countShipments($idShop, $statusFilter);


        return [
            // carrier panel
            'carriers'        => $Carriers,
            'savedCarriers' => $savedCarriers,
            'savedCodes'      => $savedCodes,
            'action'          => $adminLink,

            // orders panel
            'shipments'       => array_map(
                [$this->shipmentService, 'formatRow'],
                $shipments
            ),
            'totalShipments'  => $totalShipments,
            'currentPage'     => $page,
            'limit'           => $limit,
            'statusFilter'    => $statusFilter,
            'formAction'      => $adminLink,
            'orderDetailLink' => Context::getContext()->link->getAdminLink('AdminOrders'),
        ];
    }
    //==========================================
    // COSTRUTTORE DATI PER DASHBOARD - fine
    //==========================================





    //==========================================
    // RENDER DASHBOARD - INIZIO
    //==========================================
    public function renderDashboard(array $data = []): string
    {

        $this->clearDashboardContext();


        if (!empty($data)) {
            $this->context->smarty->assign($data);
            PrestaShopLogger::addLog(
                '[SQ-DEBUG] assign() eseguito con: ' . json_encode(array_keys($data)),
                1,
                null,
                'SpedisciQuiShipping'
            );
        } else {
            PrestaShopLogger::addLog(
                '[SQ-DEBUG] WARN: data vuoto, assign() saltato',
                2,
                null,
                'SpedisciQuiShipping'
            );
        }

        return $this->context->smarty->fetch(
            'module:spedisciquishipping/views/templates/admin/layouts/dashboard_layout.tpl'
        );
    }
    //==========================================
    // RENDER DASHBOARD - fine
    //==========================================





    //==========================================
    // PULIZIA CONTESTO DASHBOARD -INIZIPO
    //==========================================
    private function clearDashboardContext(): void
    {
        $this->context->smarty->clearAssign('content');
    }
    //==========================================
    // PULIZIA CONTESTO DASHBOARD -FINE
    //==========================================





    //==========================================
    // PRENDERIZAZZIONI DEI PARTIALS (COMPONENTI) - INIZIO
    //==========================================
    private function renderPartials(
        string $template,
        array $data = []
    ): string {

        if (!empty($data)) {
            $this->context->smarty->assign($data);
        }
        return $this->context->smarty->fetch($template);
    }
    //==========================================
    // PRENDERIZAZZIONI DEI PARTIALS (COMPONENTI) - FINE
    //==========================================





    //==========================================
    // PRENDERIZAZZIONI DASH CON CONTENT - INIZIO
    //==========================================
    public function renderWithContent(
        string $content,
        array $data = []
    ): string {
        $this->clearDashboardContext();

        $this->context->smarty->assign(array_merge([
            'content' => $content,
        ], $data));

        return $this->context->smarty->fetch(
            'module:spedisciquishipping/views/templates/admin/layouts/dashboard_layout.tpl'
        );
    }

    //==========================================
    // PRENDERIZAZZIONI DASH CON CONTENT - FINE
    //==========================================
}
