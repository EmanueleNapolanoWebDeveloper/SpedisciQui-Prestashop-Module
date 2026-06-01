<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class DashboardHandlers
{

    private CarrierRepository $carrierRepo;
    private spedisciquishipping $module;
    private ShipmentRepository $shipmentRepo;
    private SenderRepository $senderRepo;
    private ShipmentServices $shipmentService;

    public function __construct(
        CarrierRepository $carrierRepo,
        spedisciquishipping $module,
        ShipmentRepository $shipmentRepo,
        SenderRepository $senderRepo,
        ShipmentServices $shipmentService,
    ) {
        $this->carrierRepo = $carrierRepo;
        $this->module = $module;
        $this->shipmentRepo = $shipmentRepo;
        $this->senderRepo = $senderRepo;
        $this->shipmentService = $shipmentService;
    }


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
}
