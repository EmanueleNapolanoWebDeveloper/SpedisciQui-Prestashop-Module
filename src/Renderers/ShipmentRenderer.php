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
        int $page = 1,
        int $limit = 20,
        string $statusFilter = '',
        string $searchText = '',
    ) {

        $idShop = (int) Context::getContext()->shop->id ?: 1;
        $limit = max(1, min(100, $limit));
        $offset = ($page - 1) * $limit;

        $shipments = $this->shipmentRepo->getShipments($idShop, $statusFilter, $searchText, $limit, $offset);

        $totalShipments = $this->shipmentService->countShipments($idShop, $statusFilter);

        if (empty($shipments)) {
            PrestaShopLogger::addLog('shipments vuoti');
            $shipments = [];
        } else {
            $shipments = array_map(
                [$this->shipmentService, 'formatRow'],
                $shipments
            );
        }

        $adminLink = $this->context->link->getAdminLink('AdminSpedisciQuiShipments');

        $this->context->smarty->assign([
            'shipments' => $shipments,
            'totalShipments' => $totalShipments,
            'currentPage' => $page,
            'limit' => $limit,
            'statusFilter' => $statusFilter,
            'statusText' => $searchText,
            'action' => $this->buildAdminLink(),
            'back_url' => $adminLink,
            'token' => Tools::getAdminTokenLite('AdminSpedisciQuiShipments'),
            'orderDetailsLink' => $this->context->link->getAdminLink('AdminOrders'),
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
    public function renderShipmentDetail(int $shipmentId, array $extraParams = []): string
    {
        // Recuperiamo il ViewModel dal Service
        $vm = $this->shipmentService->buildViewModel($shipmentId);

        PrestaShopLogger::addLog(
            '[buildViewModel di spedisciqui per shipment details: ' . print_r($vm, true),
            1
        );

        if ($vm === null) {
            PrestaShopLogger::addLog('Ordine non trovato per : ' . $shipmentId, 3);
            return '';
        }

        // Iniettiamo nel ViewModel i link di fallback se non sono già definiti nel Service
        if (!isset($vm['form']['back_url']) && isset($extraParams['back_url'])) {
            $vm['form']['back_url'] = $extraParams['back_url'];
        }

        $token = $extraParams['token'] ?? Tools::getAdminTokenLite('AdminSpedisciQuiShipments');

        // Eseguiamo l'assegnazione a Smarty
        $this->context->smarty->assign([
            'vm' => $vm,
            // Dati isolati estratti dal ViewModel + parametri del Controller
            'sq_order_id' => (int) ($vm['shipment']['id_order'] ?? 0),
            'sq_currency_sign' => $vm['shipment']['currency'] ?? '€',
            'sq_carriers_json' => json_encode($vm['carriers'] ?? []),
            // Dati diretti inviati dal Controller
            'sq_token' => $token,
            'token' => $token,
            'back_url' => $extraParams['back_url'] ?? $this->context->link->getAdminLink('AdminSpedisciQuiShipments'),
            'sq_ajax_url' => $extraParams['sq_ajax_url'] ?? ''
        ]);

        // Caricamento CSS
        $css = $this->module->getPathUri() . 'views/css/';
        $js = $this->module->getPathUri() . 'views/js/';

        $this->context->controller->addCSS($css . 'admin/shipment/shipment_detail_styles.css', 'all', null, false);
        $this->context->controller->addCSS($css . 'admin/shipment/shipment_detail_action_styles.css', 'all', null, false);

        $this->context->controller->addJS($js . 'admin/shipment/shipment_review.js', 'all', null, false);


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
            . '&token=' . Tools::getAdminTokenLite('AdminSpedisciQuiShipments');
    }
}
