<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierHandlers
{
    private spedisciquishipping $module;
    private CarrierRepository   $carrierRepo;
    private SetupManager        $setupManager;
    private CarrierServices $carrierService;
    private CarrierApi $carrierApi;
    private string $output = '';


    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(
        spedisciquishipping $module,
        CarrierRepository   $carrierRepo,
        SetupManager        $setupManager,
        CarrierServices $carrierService,
        CarrierApi $carrierApi
    ) {
        $this->module       = $module;
        $this->carrierRepo  = $carrierRepo;
        $this->setupManager = $setupManager;
        $this->carrierService = $carrierService;
        $this->carrierApi = $carrierApi;
    }




    //==========================================
    // ENTRY POINT - INZIO
    //==========================================
    public function handle(): string
    {
        if (Tools::isSubmit('submit_carriers')) {
            $this->handleSubmit();
        }

        if (Tools::isSubmit('remove_carrier')) {
            $this->handleRemove();
        }

        return $this->output;
    }
    //==========================================
    // ENTRY POINT - FINE
    //==========================================





    // ==========================================
    // OUTPUT - inizio
    // ==========================================
    public function getOutput(): string
    {
        return $this->output;
    }
    // ==========================================
    // OUTPUT - fine
    // ==========================================




    // ==========================================
    // SUBMIT SELEZIONE CORRIERI - INIZIO
    // ==========================================
    public function handleSubmit(): void
    {
        $selectedCodes = Tools::getValue('selected_carriers', []);

        if (empty($selectedCodes)) {
            $this->output = $this->module->displayError(
                $this->module->l('Seleziona almeno un corriere.')
            );
            return;
        }

        // recupera lista completa dall'API
        $allCarriers = $this->carrierRepo->getCarriers();

        if (empty($allCarriers)) {
            $this->output = $this->module->displayError(
                $this->module->l('Impossibile recuperare i corrieri dalla piattaforma.')
            );
            return;
        }

        // filtra solo i corrieri selezionati
        $toSave = array_values(array_filter(
            $allCarriers,
            fn($c) => in_array($c['code'], $selectedCodes, true)
        ));

        if (empty($toSave)) {
            $this->output = $this->module->displayError(
                $this->module->l('Nessun corriere selezionato trovato nella piattaforma.')
            );
            return;
        }

        $saved  = 0;
        $errors = 0;

        foreach ($toSave as $carrierData) {
            if ($this->carrierRepo->saveCarrierInPS($carrierData)) {
                $saved++;
            } else {
                $errors++;
                PrestaShopLogger::addLog(
                    '[SpedisciQui] saveCarrierInPS fallito per: ' . $carrierData['code'],
                    3
                );
            }
        }

        if ($errors > 0) {
            $this->output .= $this->module->displayError(
                sprintf(
                    $this->module->l('%d corriere/i non salvato/i. Controlla i log.'),
                    $errors
                )
            );
        }

        // Successo parziale — non avanzare
        if ($saved > 0 && $errors > 0) {
            $this->output .= $this->module->displayWarning(
                sprintf(
                    $this->module->l('%d attivato/i, %d fallito/i. Riprova per i corrieri mancanti.'),
                    $saved,
                    $errors
                )
            );
        }

        // Feedback successo — avanza solo se zero errori
        if ($saved > 0 && $errors === 0) {
            $this->carrierApi->invalidateCache();
            $this->setupManager->advance();
            $this->output .= $this->module->displayConfirmation(
                sprintf(
                    $this->module->l('%d corriere/i attivato/i correttamente.'),
                    $saved
                )
            );
            return;
        }
    }
    // ==========================================
    // SUBMIT SELEZIONE CORRIERI - FINE
    // ==========================================





    // ==========================================
    // RIMOZIONE CORRIERE - INZIIO
    // ==========================================
    public function handleRemove(): void
    {
        $code = Tools::getValue('carrier_code', '');

        if (empty($code)) {
            $this->output = $this->module->displayError(
                $this->module->l('Codice corriere mancante.')
            );
            return;
        }

        if (!$this->carrierRepo->removeCarrier($code)) {
            $this->output = $this->module->displayError(
                $this->module->l('Errore durante la rimozione del corriere.')
            );
            return;
        }

        $this->carrierApi->invalidateCache();

        $this->output = $this->module->displayConfirmation(
            sprintf($this->module->l('Corriere %s rimosso correttamente.'), $code)
        );
    }
    // ==========================================
    // RIMOZIONE CORRIERE - FINE
    // ==========================================



    // ==========================================
    // CONFIGURA CORRIERE - inizio
    // ==========================================
    public function handleConfigureTariff(): void
    {
        $carrierCode = Tools::getValue('carrier_code', '');


        if (empty($carrierCode)) {
            $this->output = $this->module->displayError(
                $this->module->l('Codice corriere mancante.')
            );
            return;
        }

        $weightFromArr = Tools::getValue('weight_from', []);
        $weightToArr = Tools::getValue('weight_to', []);
        $priceArr = Tools::getValue('price', []);
        $activeArr = Tools::getValue('active', []);

        if (!is_array($weightFromArr)) {
            $weightFromArr = [];
        }


        $rows = [];

        foreach (array_keys($weightFromArr) as $i) {
            $rows[] = [
                'weight_from' => $weightFromArr[$i] ?? '0',
                'weight_to' => $weightToArr[$i] ?? '0',
                'tariff' => $priceArr[$i] ?? '0',
                'active' => isset($activeArr[$i]) ? 1 : 0,
            ];
        }

        $success = $this->carrierService->saveTariffs($carrierCode, $rows);

        if ($success) {
            $this->carrierApi->invalidateCache();
            $this->output .= $this->module->displayConfirmation(
                $this->module->l('Tariffe salvate con successo.')
            );
        } else {
            $this->output .= $this->module->displayError(
                $this->module->l('Errore durante il salvataggio delle tariffe.')
            );
        }
    }
    // ==========================================
    // CONFIGURA CORRIERE - FINE
    // ==========================================
}
