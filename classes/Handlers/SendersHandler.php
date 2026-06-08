<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

class SenderHandler
{
    private spedisciquishipping $module;
    private SenderRepository    $senderRepo;
    private SetupManager        $setupManager;
    private SenderRenderer $senderRender;
    private string              $output = '';


    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(
        spedisciquishipping $module,
        SenderRepository    $senderRepo,
        SetupManager        $setupManager,
        SenderRenderer $senderRender,
    ) {
        $this->module       = $module;
        $this->senderRepo   = $senderRepo;
        $this->setupManager = $setupManager;
        $this->senderRender = $senderRender;
    }



    //=============================================
    // RITORNA OUTPUT - INIZIO
    //=============================================
    public function getOutput(): string
    {
        return $this->output;
    }
    //=============================================
    // RITORNA OUTPUT - fine
    //=============================================





    //=============================================
    // SUBMIT DEFUALT/MAIN SENDER - INIZIO
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
    //=============================================
    // SUBMIT DEFUALT/MAIN SENDER - fine
    //=============================================



    //=============================================
    // SUBMIT UPDATE SENDER - INIZIO
    //=============================================
    public function handleUpdateSender()
    {
        $idSender = (int) Tools::getValue('id_sender');

        if (!$idSender) {
            PrestaShopLogger::addLog('[SpedisciQui] handleUpdate — id_sender mancante', 3);
            return;
        }

        $data = [
            'id'           => $idSender, // ✅ deve esserci
            'label'        => Tools::getValue('label'),
            'company'      => Tools::getValue('company') ?: null,
            'firstname'    => Tools::getValue('firstname'),
            'lastname'     => Tools::getValue('lastname'),
            'phone'        => Tools::getValue('phone'),
            'phone_mobile' => Tools::getValue('phone_mobile') ?: null,
            'email'        => Tools::getValue('email') ?: null,
            'address1'     => Tools::getValue('address1'),
            'address2'     => Tools::getValue('address2') ?: null,
            'postcode'     => Tools::getValue('postcode'),
            'city'         => Tools::getValue('city'),
            'state_code'   => Tools::getValue('state_code') ?: null,
            'country_iso'  => Tools::getValue('country_iso'),
            'vat_number'   => Tools::getValue('vat_number') ?: null,
            'is_default'   => (int) Tools::getValue('is_default'),
            'is_active'    => (int) Tools::getValue('is_active'),
            'date_upd'     => date('Y-m-d H:i:s'),
        ];

        $result = $this->senderRepo->updateSenderAddress($data);

        if ($result) {
            $this->output .= $this->module->displayConfirmation(
                $this->module->l('Mittente aggiornato con successo.', 'spedisciquishipping')
            );
        } else {
            $this->output .= $this->module->displayError(
                $this->module->l('Errore durante l\'aggiornamento del mittente.', 'spedisciquishipping')
            );
        }
    }
    //=============================================
    // SUBMIT UPDATE SENDER - fine
    //=============================================
}
