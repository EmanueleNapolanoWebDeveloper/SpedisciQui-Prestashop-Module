<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierRenderer
{
    private spedisciquishipping $module;
    private CarrierRepository $carrierRepo;
    private $context;
    private CarrierServices $carrierService;



    // =============================================
    // COSTRUTTORE
    // =============================================
    public function __construct(
        spedisciquishipping $module,
        CarrierRepository $carrierRepo,
        CarrierServices $carrierService
    ) {
        $this->module = $module;
        $this->carrierRepo = $carrierRepo;
        $this->context = Context::getContext();
        $this->carrierService = $carrierService;
    }




    // =============================================
    // RENDERIZZA LISTA CORRIERI CONFIG - INIZIO
    // =============================================
    public function renderCarrierForm(string $formAction, array $carriers): string
    {

        $this->addCss('carrier_init_styles.css');

        $this->context->smarty->assign([
            'carriers' => $carriers ?? [],
            'action' => $formAction,
            'setupStep' => SetupSteps::CARRIER
        ]);

        $templatePath = _PS_MODULE_DIR_ . 'spedisciquishipping/views/templates/admin/_partials/_initial/carrier_list_init.tpl';

        return $this->context->smarty->fetch($templatePath);
    }
    // =============================================
    // RENDERIZZA LISTA CORRIERI CONFIG - FINE
    // =============================================



    // =============================================
    // RENDERIZZA LISTA CORRIERI DASHBOARD - INIZIO
    // =============================================
    public function renderCarrierDash(): string
    {
        $carriers = $this->carrierRepo->getCarriers();
        $savedCarriers = $this->carrierRepo->getSavedCarriers();
        $savedCodes = array_column($savedCarriers, 'carrier_code');

        $baseUrl = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token=' . Tools::getAdminTokenLite('AdminModules');

        // Costruisci gli URL di configurazione per ogni carrier
        foreach ($savedCarriers as &$carrier) {
            $carrier['configure_url'] = $baseUrl . '&carrier_code=' . urlencode($carrier['carrier_code']);

            PrestaShopLogger::addLog(
                '[SQ] configure_url generata: ' . $carrier['configure_url'],
                1,
                null,
                'SpedisciQui'
            );
        }
        unset($carrier);

        $actionUrl = $baseUrl;

        $this->context->smarty->assign([
            'carriers' => $carriers ?? [],
            'savedCarriers' => $savedCarriers ?? [],
            'savedCodes' => $savedCodes,
            'module_name' => $this->module->name,
            'module_action_url' => $baseUrl,
            'action' => $actionUrl,
        ]);

        return '';
    }
    // =============================================
    // RENDERIZZA LISTA CORRIERI DASHBOARD - FINE
    // =============================================






    // =============================================
    // RENDERIZZA CONFIGURAZIONE CORREIRE - INIZIO
    // =============================================
    public function renderCarrierTariffConfig(
        string $carrierCode,
        string $formAction
    ): string {

        // aggiungo js
        $this->addJs('tariff.js');


        // 1. Recupero dati carrier
        $carrier = $this->carrierRepo->getCarrierByCode($carrierCode);
        $senderRepo = new SenderRepository();
        $senders = $senderRepo->getAllSenders();

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

        // 2. Recupera tariffe esistenti tramite CarrierService
        $tariffRows = $this->carrierService->getTariffByCarrierId(
            (int) $carrier['id_carrier'],
            $carrier['carrier_code'],
            null
        );

        // Recuperiamo il token nativo del nostro Controller specifico
        $controllerToken = Tools::getAdminTokenLite('AdminSpedisciQuiCarriers');

        // 3. Costruiamo gli URL corretti per non uscire dal flusso del Controller custom
        $actionUrl = $formAction . '&token=' . $controllerToken . '&carrier_code=' . urlencode($carrierCode);
        $backLink = $formAction . '&token=' . $controllerToken;

        // 4. Passiamo TUTTE le variabili a Smarty (incluso il token richiesto dal TPL)
        $this->context->smarty->assign([
            'senders' => $senders,
            'carrier_code' => $carrier['carrier_code'],
            'carrier_name' => $carrier['carrier_name'],
            'service_code' => $carrier['service_code'],
            'tariff_rows' => $tariffRows,
            'action' => $actionUrl,
            'backLink' => $backLink,
            'token' => $controllerToken,
            'sq_errors' => [],
            'sq_confirmation' => [],
        ]);

        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'spedisciquishipping/views/templates/admin/_partials/_carrier/components/config_tariffs.tpl'
        );
    }
    // =============================================
    // RENDERIZZA CONFIGURAZIONE CORREIRE - FINE
    // =============================================



    // =============================================
    //HELPERS
    // =============================================
    private function addCss(string $filename): void
    {
        $cssPath = $this->module->getPathUri() . 'views/css/admin/initial/';
        $this->context->controller->addCSS($cssPath . $filename, 'all', null, false);
    }

    private function addJs(string $filename): void
    {
        $jsPath = $this->module->getPathUri() . 'views/js/admin/carriers/';
        $this->context->controller->addJS($jsPath . $filename, 'all', null, false);
    }
}
