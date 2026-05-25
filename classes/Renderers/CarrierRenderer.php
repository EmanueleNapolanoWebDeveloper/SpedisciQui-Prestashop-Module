<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierRenderer
{
    private spedisciquishipping $module;
    private CarrierRepository   $carrierRepo;

    public function __construct(
        spedisciquishipping $module,
        CarrierRepository   $carrierRepo
    ) {
        $this->module      = $module;
        $this->carrierRepo = $carrierRepo;
    }

    // =============================================
    // RENDERIZZA LISTA CORRIERI CONFIG
    // =============================================
    public function renderCarrierForm(): string
    {
        $context  = Context::getContext();
        $carriers = $this->carrierRepo->getCarriers();

        $action = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token='     . Tools::getAdminTokenLite('AdminModules');

        $context->smarty->assign([
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
        $context = Context::getContext();

        $carriers       = $this->carrierRepo->getCarriers();
        $savedCarriers  = $this->carrierRepo->getSavedCarriers();

        $action = $_SERVER['REQUEST_URI'];

        $context->smarty->assign([
            'carriers' => $carriers,
            'savedCarriers' => $savedCarriers,
            'action' => $action,
        ]);

        return $this->module->fetch(
            'module:spedisciquishipping/views/templates/admin/carrier_panel.tpl'
        );
    }
}
