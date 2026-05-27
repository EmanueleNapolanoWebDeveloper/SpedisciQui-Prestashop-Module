<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierRenderer
{
    private spedisciquishipping $module;
    private CarrierRepository   $carrierRepo;
    private $context;

    public function __construct(
        spedisciquishipping $module,
        CarrierRepository   $carrierRepo
    ) {
        $this->module      = $module;
        $this->carrierRepo = $carrierRepo;
        $this->context = Context::getContext();
    }

    // =============================================
    // RENDERIZZA LISTA CORRIERI CONFIG
    // =============================================
    public function renderCarrierForm(): string
    {
        $carriers = $this->carrierRepo->getCarriers();

        $action = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token='     . Tools::getAdminTokenLite('AdminModules');

        $this->context->smarty->assign([
            'carriers' => $carriers ?? [],
            'action'   => $action,
        ]);

        return $this->module->display(
            $this->module->getLocalPath(),
            'views/templates/admin/_partials/initial/carrier_list_init.tpl'
        );
    }



    // =============================================
    // RENDERIZZA LISTA CORRIERI DASHBOARD
    // =============================================
    public function renderCarrierDash(): string
    {
        // die('OK FUNZIONE CHIAMATA');
        PrestaShopLogger::addLog('Inizio renderCarrierDash');

        $carriers       = $this->carrierRepo->getCarriers();
        $savedCarriers  = $this->carrierRepo->getSavedCarriers();

        $action = $_SERVER['REQUEST_URI'];

        $this->context->smarty->assign([
            'carriers' => $carriers ?? [],
            'savedCarriers' => $savedCarriers ?? [],
            'message' => 'che palle',
            'action' => $action,
        ]);

        PrestaShopLogger::addLog('carriers debug: ' . json_encode($carriers));
        PrestaShopLogger::addLog('saved debug: ' . json_encode($savedCarriers));


        return $this->module->display(
            $this->module->getLocalPath(),
            'views/templates/admin/_partials/carrier_panel.tpl'
        );
    }
}
