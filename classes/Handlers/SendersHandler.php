<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

class SenderHandler
{
    private spedisciquishipping $module;
    private SenderRepository    $senderRepo;
    private SetupManager        $setupManager;
    private string              $output = '';

    public function __construct(
        spedisciquishipping $module,
        SenderRepository    $senderRepo,
        SetupManager        $setupManager
    ) {
        $this->module       = $module;
        $this->senderRepo   = $senderRepo;
        $this->setupManager = $setupManager;
    }



    //=============================================
    // RITORNA OUTPUT
    //=============================================
    public function getOutput(): string
    {
        return $this->output;
    }

    
    //=============================================
    // SUBMIT DEFUALT/MAIN SENDER
    //=============================================
    public function handleSubmit(): void
    {
        $data = [
            'label'        => Tools::getValue('SQ_SENDER_LABEL',        'Sede principale'),
            'company'      => Tools::getValue('SQ_SENDER_COMPANY',      ''),
            'firstname'    => Tools::getValue('SQ_SENDER_FIRSTNAME',    ''),
            'lastname'     => Tools::getValue('SQ_SENDER_LASTNAME',     ''),
            'phone'        => Tools::getValue('SQ_SENDER_PHONE',        ''),
            'phone_mobile' => Tools::getValue('SQ_SENDER_PHONE_MOBILE', ''),
            'email'        => Tools::getValue('SQ_SENDER_EMAIL',        ''),
            'address1'     => Tools::getValue('SQ_SENDER_ADDRESS1',     ''),
            'address2'     => Tools::getValue('SQ_SENDER_ADDRESS2',     ''),
            'postcode'     => Tools::getValue('SQ_SENDER_POSTCODE',     ''),
            'city'         => Tools::getValue('SQ_SENDER_CITY',         ''),
            'state_code'   => Tools::getValue('SQ_SENDER_STATE',        ''),
            'country_iso'  => Tools::getValue('SQ_SENDER_COUNTRY_ISO',  'IT'),
            'id_country'   => Tools::getValue('SQ_SENDER_ID_COUNTRY',   110),
            'vat_number'   => Tools::getValue('SQ_SENDER_VAT_NUMBER',          ''),
        ];

        // validazione
        $errors = new SenderServices()->validate($data);

        if (!empty($errors)) {
            foreach ($errors as $error) {
                $this->output .= $this->module->displayError(
                    $this->module->l($error)
                );
            }
            return;
        }

        if (!$this->senderRepo->save($data)) {
            $this->output .= $this->module->displayError(
                $this->module->l('Errore durante il salvataggio del mittente.')
            );
            return;
        }

        // ✅ avanza a step 3 (DONE o step successivo)
        $this->setupManager->advance();

        $this->output .= $this->module->displayConfirmation(
            $this->module->l('Indirizzo mittente salvato correttamente.')
        );
    }
}
