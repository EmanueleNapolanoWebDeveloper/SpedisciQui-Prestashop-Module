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
    // RENDERIZZA LISTA CORRIERI
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
            'views/templates/admin/carrier_list.tpl'
        );
    }
}
