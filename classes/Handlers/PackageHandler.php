<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

class PackageHandler
{
    private spedisciquishipping $module;
    private PackageRepository   $packRepo;
    private SetupManager        $setup;
    private string $output = '';



    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(
        spedisciquishipping $module,
        PackageRepository   $packRepo,
        SetupManager        $setup
    ) {
        $this->module   = $module;
        $this->packRepo = $packRepo;
        $this->setup    = $setup;
    }



    //===========================================
    // OUTPUT -INIZIO
    //===========================================
    public function getOutput(): string
    {
        return $this->output;
    }
    //===========================================
    // OUTPUT -FINE
    //===========================================





    // =============================================
    // SUBMIT DEFAULT PACKAGE - INIZIO
    // =============================================
    public function handleSubmit(): void
    {
        $data = [
            'name'       => trim(Tools::getValue('package_name',       'Default')),
            'weight'     => Tools::getValue('package_weight',          '1.000'),
            'length'     => Tools::getValue('package_length',          '30.00'),
            'width'      => Tools::getValue('package_width',           '20.00'),
            'height'     => Tools::getValue('package_height',          '10.00'),
            'is_default' => Tools::getValue('package_is_default', 0) ? 1 : 0,
        ];

        // Validazione
        $$packageService = new PackageServices();
        $errors = $packageService->validate($data);
        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->output .= $this->module->displayError($error);
            }
            return;
        }

        // Salvataggio
        if (!$this->packRepo->savePackage(null, $data)) {
            $this->output = $this->module->displayError(
                $this->module->l('Errore durante il salvataggio del pacco.')
            );
            return;
        }

        // Avanza step setup
        $this->setup->advance();

        $this->output = $this->module->displayConfirmation(
            $this->module->l('Dati pacco salvati correttamente.')
        );
    }
    // =============================================
    // SUBMIT DEFAULT PACKAGE - fineF
    // =============================================
}
