<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class ShipmentRenderer
{

    private ShipmentRepository $shipmentRepo;
    private spedisciquishipping $module;
    private Context $context;
    private ShipmentServices $shipmentService;


    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(
        ShipmentRepository $shipmentRepo,
        spedisciquishipping $module,
        Context $context,
        ShipmentServices $shipmentService
    ) {
        $this->module = $module;
        $this->shipmentRepo = $shipmentRepo;
        $this->context = $context;
        $this->shipmentService = $shipmentService;
    }





    // ========================================================================
    // LISTA SPEDIZIONI (dashboard) - INIZIO
    // ========================================================================

    public function renderShipmentLists(
        int    $page         = 1,
        int    $limit        = 20,
        string $statusFilter = ''
    ) {
            PrestaShopLogger::addLog('entro in render shipmentlist');

        $shipments = $this->shipmentRepo->getShipments();

        PrestaShopLogger::addLog('shipments :', print_r($shipments, true));

        $totalShipments = $this->shipmentService->countShipments();

        if (empty($shipments)) {
            PrestaShopLogger::addLog('shipments vuoti');
            return false;
        }

        $action = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token='     . Tools::getAdminTokenLite('AdminModules');

        $this->context->smarty->assign([
            'shipments'       => $shipments,
            'totalShipments'  => $totalShipments,
            'currentPage'     => $page,
            'limit'           => $limit,
            'statusFilter'    => $statusFilter,
            'action' => $this->buildAdminLink(),
            'orederDetailsLink' => $this->context->link->getAdminLink('AdminOrders'),
        ]);

        return $this->module->display(
            $this->module->getLocalPath(),
            'views/templates/admin/_partials/_shipment/shipment_panel.tpl'
        );
    }
    // ========================================================================
    // LISTA SPEDIZIONI (dashboard) - FINE
    // ========================================================================





    // ========================================================================
    // DETTAGLIO SPEDIZIONE - INIZIO
    // ========================================================================
    public function renderShipmentDetail(int $shipmentId)
    {

        $vm = $this->shipmentService->buildViewModel($shipmentId);

        if ($vm === null) {
            PrestaShopLogger::addLog('Ordine non trovato per : ' . $shipmentId, 3);
            return '';
        }

        $this->context->smarty->assign('vm', $vm);

        return $this->module->display(
            $this->module->getLocalPath(),
            'views/templates/admin/_partials/_shipment/shipment_detail.tpl'
        );
    }
    // ========================================================================
    // DETTAGLIO SPEDIZIONE - FINE
    // ========================================================================



    // =========================================================
    // HELPERS
    // =========================================================

    private function buildAdminLink(): string
    {
        return AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token='     . Tools::getAdminTokenLite('AdminModules');
    }
}
