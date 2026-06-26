<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class AdminSpedisciQuiCarriersController extends ModuleAdminController
{
    private SetupManager $setupManager;
    private CarrierRepository $carrierRepo;
    private CarrierApi $carrierApi;
    private CarrierServices $carrierService;
    private CarrierRenderer $carrierRenderer;

    public function __construct()
    {
        $this->bootstrap = true;

        parent::__construct();

        $configRepo = new ConfigRepositories($this->context);
        $apiClient = new ApiClient($configRepo);
        $this->carrierApi = new CarrierApi($apiClient);
        $credentialsRepo = new CredentialsRepositories($this->context, $apiClient);

        $this->setupManager = new SetupManager($configRepo, $credentialsRepo);
        $this->carrierRepo = new CarrierRepository($this->carrierApi, $credentialsRepo, $this->module);
        $this->carrierService = new CarrierServices($this->carrierRepo);
        $this->carrierRenderer = new CarrierRenderer($this->module, $this->carrierRepo, $this->carrierService);
    }

    // =========================================================
    // initContent — ROUTING GET
    // =========================================================
    public function initContent(): void
    {
        parent::initContent();

        $this->addCSS(
            $this->module->getPathUri() . 'views/css/admin/carriers/carriers_styles.css',
            'all',
            null,
            false
        );

        $this->addJS(
            $this->module->getPathUri() . 'views/js/admin/carriers/carriers_scripts.js',
            'all',
            null,
            false
        );

        if ($this->setupManager->current() !== SetupSteps::DONE) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminSpedisciQuiSetup'));
            return;
        }

        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiCarriers');

        // Sotto-vista: configurazione tariffe corriere
        if (Tools::getValue('carrier_code', '') !== '') {

            $carrierCode = Tools::getValue('carrier_code', '');

            $this->context->smarty->assign([
                'token' => $this->token,
                'formAction' => $formAction
            ]);

            $this->content = $this->carrierRenderer->renderCarrierTariffConfig($carrierCode, $formAction);

            $this->context->smarty->assign('content', $this->content);
            return;
        }

        $this->renderCarriersPage($formAction);
    }

    // =========================================================
    // postProcess — ROUTING POST
    // =========================================================
    public function postProcess(): void
    {
        $formAction = $this->context->link->getAdminLink('AdminSpedisciQuiCarriers');

        // Installa nuovo corriere
        if (Tools::isSubmit('submitSpedisciQuiCarriers')) {
            $this->processCarrierInstall();
            Tools::redirectAdmin($formAction);
            return;
        }

        // Rimozione corriere
        if (Tools::isSubmit('removeSpedisciQuiCarriers')) {
            $this->processCarrierRemove();
            Tools::redirectAdmin($formAction);
            return;
        }

        // Salvataggio tariffe
        if (Tools::isSubmit('saveTariffConfig')) {

            $this->processTariffsSave();

            $carrierCode = Tools::getValue('carrier_code', '');
            $redirectUrl = $carrierCode
                ? $formAction . '&carrier_code=' . urlencode($carrierCode)
                : $formAction;

            //Tools::redirectAdmin($redirectUrl);

            return;
        }

        parent::postProcess();
    }

    // =========================================================
    // AZIONI POST
    // =========================================================
    private function processCarrierInstall(): void
    {
        $selectedCodes = Tools::getValue('selected_carriers', []);

        if (empty($selectedCodes)) {
            $this->errors[] = $this->module->l('Selezionare almeno un corriere da attivare.');
            return;
        }

        $allCarriers = $this->carrierRepo->getCarriers();
        $toSave = array_values(array_filter(
            $allCarriers,
            fn($c) => in_array($c['carrier_code'], $selectedCodes, true)
        ));

        $saved = 0;
        foreach ($toSave as $carrierData) {
            if ($this->carrierRepo->saveCarrierInPS($carrierData)) {
                $saved++;
            }
        }

        if ($saved > 0) {
            $this->confirmations[] = sprintf($this->module->l('%d corriere/i attivato/i.'), $saved);
        } else {
            $this->errors[] = $this->module->l('Nessun corriere installato. Verifica se è già presente.');
        }
    }

    private function processCarrierRemove(): void
    {
        $carrierCode = Tools::getValue('carrier_code', '');

        if (empty($carrierCode)) {
            $this->errors[] = $this->module->l('Codice corriere mancante.');
            return;
        }

        if (!$this->carrierRepo->removeCarrier($carrierCode)) {
            $this->errors[] = $this->module->l('Errore durante la rimozione del corriere.');
            return;
        }

        $this->carrierApi->invalidateCache();
        $this->confirmations[] = sprintf(
            $this->module->l('Corriere %s rimosso correttamente.'),
            $carrierCode
        );
    }

    private function processTariffsSave(): void
    {
        $carrierCode = Tools::getValue('carrier_code', '');

        if (empty($carrierCode)) {
            $this->errors[] = $this->module->l('Impossibile associare le tariffe: codice corriere vuoto.');
            return;
        }

        $weightFromArr = Tools::getValue('weight_from', []);
        $weightToArr = Tools::getValue('weight_to', []);
        $pricesBySender = Tools::getValue('price', []);

        if (!is_array($weightFromArr) || !is_array($weightToArr)) {
            $this->errors[] = $this->module->l('Dati peso non validi.');
            return;
        }

        if (!is_array($pricesBySender) || empty($pricesBySender)) {
            $this->errors[] = $this->module->l('Impossibile associare le tariffe: nessun mittente ricevuto.');
            return;
        }

        $rowCount = count($weightFromArr);

        if ($rowCount === 0 || $rowCount !== count($weightToArr)) {
            $this->errors[] = $this->module->l('Mismatch tra righe peso: weight_from e weight_to hanno lunghezze diverse.');
            return;
        }

        $weightFromArr = array_values($weightFromArr);
        $weightToArr = array_values($weightToArr);

        $rows = [];

        // iterazione per ogni sender
        foreach ($pricesBySender as $idSender => $prices) {
            $idSender = (int) $idSender;

            if ($idSender <= 0) {
                continue;
            }

            if (!is_array($prices)) {
                continue;
            }

            $prices = array_values($prices);

            if (count($prices) !== $rowCount) {
                $this->errors[] = sprintf(
                    $this->module->l('Mismatch tariffe: il mittente %d ha %d prezzi ma ci sono %d righe peso.'),
                    $idSender,
                    count($prices),
                    $rowCount
                );
                return;
            }

            for ($i = 0; $i < $rowCount; $i++) {
                $from = (float) $weightFromArr[$i];
                $to = (float) $weightToArr[$i];
                $tariff = (float) ($prices[$i] ?? 0);

                if ($from < 0 || $to <= 0 || $from >= $to) {
                    $this->errors[] = sprintf(
                        $this->module->l('Intervallo peso non valido alla riga %d: [%.2f, %.2f].'),
                        $i + 1, // 1-based per l'utente
                        $from,
                        $to
                    );
                    return;
                }

                if ($tariff < 0) {
                    $this->errors[] = sprintf(
                        $this->module->l('Tariffa negativa non consentita (mittente %d, riga %d).'),
                        $idSender,
                        $i + 1
                    );
                    return;
                }

                $rows[] = [
                    'id_sender' => $idSender,
                    'weight_from' => $from,
                    'weight_to' => $to,
                    'tariff' => $tariff,
                ];
            }
        }

        if (empty($rows)) {
            $this->errors[] = $this->module->l('Nessuna tariffa da salvare.');
            return;
        }


        try {
            $saved = $this->carrierService->saveTariffs($carrierCode, $rows);
        } catch (Throwable $e) {
            $this->errors[] = $this->module->l('Errore interno durante il salvataggio delle tariffe.');
            PrestaShopLogger::addLog(
                'processTariffsSave error: ' . $e->getMessage(),
                PrestaShopLogger::LOG_SEVERITY_ERROR,
                null,
                'SpedisciQui'
            );
            return;
        }

        if (!$saved) {
            $this->errors[] = $this->module->l('Errore durante il salvataggio delle tariffe.');
            return;
        }

        // in caso di successo invalido cache
        $this->carrierApi->invalidateCache();

        Tools::redirectAdmin(
            $this->context->link->getAdminLink('AdminSpedisciQuiCarriers')
            . '&carrier_code=' . urlencode($carrierCode)
            . '&conf=4'
        );
    }

    // =========================================================
    // RENDERING
    // =========================================================
    private function renderCarriersPage(string $formAction): void
    {
        $carriers = $this->carrierRepo->getCarriers();
        $savedCarriers = $this->carrierRepo->getSavedCarriers();
        $configuredCodes = $this->carrierRepo->getConfiguredCarrierCodes();

        foreach ($savedCarriers as &$carrier) {
            $carrier['configure_url'] = $formAction . '&carrier_code=' . urlencode($carrier['carrier_code']);
        }
        unset($carrier);

        // Mappiamo accuratamente tutte le variabili richieste da carrier_panel.tpl
        $this->context->smarty->assign([
            'action' => $formAction, // Richiesto da carrier_active_dash.tpl
            'formAction' => $formAction, // Richiesto da carrier_list_dash.tpl (attento al case-sensitive)
            'module_action_url' => $formAction, // Fallback se usato all'interno dei componenti
            'module_name' => $this->module->name, // Passa il nome del modulo se richiesto
            'carriers' => $carriers ?? [],
            'savedCarriers' => $savedCarriers ?? [],
            'savedCodes' => array_column($savedCarriers, 'carrier_code'),
            'configuredCodes' => $configuredCodes,
            'token' => $this->token,
        ]);

        $templatePath = _PS_MODULE_DIR_ . 'spedisciquishipping/views/templates/admin/_partials/_carrier/carrier_panel.tpl';
        $this->content = $this->context->smarty->fetch($templatePath);
    }

    public function display(): void
    {
        $this->context->smarty->assign('content', $this->content);
        parent::display();
    }
}