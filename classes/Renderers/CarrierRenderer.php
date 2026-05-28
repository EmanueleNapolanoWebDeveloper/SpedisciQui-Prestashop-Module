<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierRenderer
{
    private spedisciquishipping $module;
    private CarrierRepository   $carrierRepo;
    private $context;
    private CarrierServices $carrierService;

    public function __construct(
        spedisciquishipping $module,
        CarrierRepository   $carrierRepo,
        CarrierServices $carrierService
    ) {
        $this->module      = $module;
        $this->carrierRepo = $carrierRepo;
        $this->context = Context::getContext();
        $this->carrierService = $carrierService;
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
        $carriers      = $this->carrierRepo->getCarriers();
        $savedCarriers = $this->carrierRepo->getSavedCarriers();
        $savedCodes    = array_column($savedCarriers, 'carrier_code');

        $baseUrl = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token=' . Tools::getAdminTokenLite('AdminModules');

        // Costruisci gli URL di configurazione per ogni carrier
        foreach ($savedCarriers as &$carrier) {
            $carrier['configure_url'] = $baseUrl . '&configureCarrierTariff=1&carrier_code=' . urlencode($carrier['carrier_code']);
        }

        $this->context->smarty->assign([
            'carriers'          => $carriers ?? [],
            'savedCarriers'     => $savedCarriers ?? [],
            'savedCodes'        => $savedCodes,
            'module_name'       => $this->module->name,
            'module_action_url' => $baseUrl,
            'action'            => $baseUrl,
        ]);

        return $this->module->display(
            $this->module->getLocalPath(),
            'views/templates/admin/_partials/carrier_panel.tpl'
        );
    }


    // =============================================
    // RENDERIZZA CONFIGURAZIONE CORREIRE
    // =============================================
    public function renderCarrierTariffConfig(
        string $carrierCode
    ): string {

        PrestaShopLogger::addLog(
            '[SpedisciQui] renderCarrierTariffConfig attivato',
            1,
            null,
            'SpedisciQuiShipping'
        );

        // recupero dati carrier
        $carrier = $this->carrierRepo->getCarrierByCode($carrierCode);

        if (empty($carrier)) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] renderCarrierTariffConfig: carrier non trovato → ' . $carrierCode,
                3,
                null,
                'SpedisciQuiShipping'
            );
            $this->context->smarty->assign('sq_errors', ['Carrier non trovato.']);
            return $this->renderCarrierDash();
        }

        PrestaShopLogger::addLog(
            '[SpedisciQui] carrier trovato: ' . print_r($carrier, true),
            1,
            null,
            'SpedisciQuiShipping'
        );

        // recupera tariffe esistenti tramite CarrierService
        $tariffRows = $this->carrierService->getTariffByCarrierId(
            (int) $carrier['id_carrier'],
            $carrier['service_code'],
            null
        );

        PrestaShopLogger::addLog(
            '[SpedisciQui] tariffe trovato: ' . print_r($tariffRows, true),
            1,
            null,
            'SpedisciQuiShipping'
        );

        // URL action
        $actionUrl = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token=' . Tools::getAdminTokenLite('AdminModules')
            . '&carrier_code=' . urlencode(($carrierCode));

        // backLink
        $backLink = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&view=dashboard_layout'
            . '&token=' . Tools::getAdminTokenLite('AdminModules');

        $this->context->smarty->assign([
            'carrier_code' => $carrier['carrier_code'],
            'carrier_name' => $carrier['carrier_name'],
            'service_code' => $carrier['service_code'],
            'tariff_rows' => $tariffRows,
            'action' => $actionUrl,
            'backLink' => $backLink,
            'sq_errors' => [],
            'sq_confirmation' => [],
        ]);

        return $this->module->display(
            $this->module->getLocalPath(),
            'views/templates/admin/_partials/_carrier/config_tariffs.tpl'
        );
    }
}
