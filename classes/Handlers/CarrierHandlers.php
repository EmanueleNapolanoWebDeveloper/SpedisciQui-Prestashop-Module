<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierHandlers
{
    private spedisciquishipping $module;
    private CarrierRepository   $carrierRepo;
    private SetupManager        $setupManager;
    private string $output = '';

    public function __construct(
        spedisciquishipping $module,
        CarrierRepository   $carrierRepo,
        SetupManager        $setupManager
    ) {
        $this->module       = $module;
        $this->carrierRepo  = $carrierRepo;
        $this->setupManager = $setupManager;
    }

    // ==========================================
    // OUTPUT
    // ==========================================
    public function getOutput(): string
    {
        return $this->output;
    }

    // ==========================================
    // SUBMIT SELEZIONE CORRIERI
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
        $toSave = array_filter(
            $allCarriers,
            fn($c) => in_array($c['code'], $selectedCodes, true)
        );

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

        if ($saved > 0) {
            $this->setupManager->advance();
            $this->output .= $this->module->displayConfirmation(
                sprintf(
                    $this->module->l('%d corriere/i attivato/i correttamente.'),
                    $saved
                )
            );
        }
    }

    // ==========================================
    // RIMOZIONE CORRIERE
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

        $this->output = $this->module->displayConfirmation(
            sprintf($this->module->l('Corriere %s rimosso correttamente.'), $code)
        );
    }
}
