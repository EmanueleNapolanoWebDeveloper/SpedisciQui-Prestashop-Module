<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class SenderRenderer
{
    private spedisciquishipping $module;
    private SenderRepository    $senderRepo;

    public function __construct(spedisciquishipping $module, SenderRepository $senderRepo)
    {
        $this->module     = $module;
        $this->senderRepo = $senderRepo;
    }

    public function renderSenderForm(): string
    {
        $existing = $this->senderRepo->getDefault();

        $helper                  = new HelperForm();
        $helper->module          = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex . '&configure=' . $this->module->name;
        $helper->submit_action   = 'submitSpedisciQuiSender';
        $helper->fields_value    = [
            'SQ_SENDER_LABEL'        => $existing['label']       ?? 'Sede principale',
            'SQ_SENDER_COMPANY'      => $existing['company']     ?? '',
            'SQ_SENDER_FIRSTNAME'    => $existing['firstname']   ?? '',
            'SQ_SENDER_LASTNAME'     => $existing['lastname']    ?? '',
            'SQ_SENDER_PHONE'        => $existing['phone']       ?? '',
            'SQ_SENDER_PHONE_MOBILE' => $existing['phone_mobile']?? '',
            'SQ_SENDER_EMAIL'        => $existing['email']       ?? '',
            'SQ_SENDER_ADDRESS1'     => $existing['address1']    ?? '',
            'SQ_SENDER_ADDRESS2'     => $existing['address2']    ?? '',
            'SQ_SENDER_POSTCODE'     => $existing['postcode']    ?? '',
            'SQ_SENDER_CITY'         => $existing['city']        ?? '',
            'SQ_SENDER_STATE'        => $existing['state_code']  ?? '',
            'SQ_SENDER_VAT'          => $existing['vat_number']  ?? '',
        ];

        $formFields = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Indirizzo mittente predefinito'),
                    'icon'  => 'icon-home',
                ],
                'input' => [
                    [
                        'type'  => 'text',
                        'label' => $this->module->l('Etichetta'),
                        'name'  => 'SQ_SENDER_LABEL',
                        'desc'  => $this->module->l('Es: Sede principale, Magazzino...'),
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->module->l('Azienda'),
                        'name'  => 'SQ_SENDER_COMPANY',
                    ],
                    [
                        'type'     => 'text',
                        'label'    => $this->module->l('Nome'),
                        'name'     => 'SQ_SENDER_FIRSTNAME',
                        'required' => true,
                    ],
                    [
                        'type'     => 'text',
                        'label'    => $this->module->l('Cognome'),
                        'name'     => 'SQ_SENDER_LASTNAME',
                        'required' => true,
                    ],
                    [
                        'type'     => 'text',
                        'label'    => $this->module->l('Telefono'),
                        'name'     => 'SQ_SENDER_PHONE',
                        'required' => true,
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->module->l('Cellulare'),
                        'name'  => 'SQ_SENDER_PHONE_MOBILE',
                    ],
                    [
                        'type'  => 'email',
                        'label' => $this->module->l('Email'),
                        'name'  => 'SQ_SENDER_EMAIL',
                    ],
                    [
                        'type'     => 'text',
                        'label'    => $this->module->l('Indirizzo'),
                        'name'     => 'SQ_SENDER_ADDRESS1',
                        'required' => true,
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->module->l('Indirizzo (riga 2)'),
                        'name'  => 'SQ_SENDER_ADDRESS2',
                    ],
                    [
                        'type'     => 'text',
                        'label'    => $this->module->l('CAP'),
                        'name'     => 'SQ_SENDER_POSTCODE',
                        'required' => true,
                        'class'    => 'fixed-width-sm',
                    ],
                    [
                        'type'     => 'text',
                        'label'    => $this->module->l('Città'),
                        'name'     => 'SQ_SENDER_CITY',
                        'required' => true,
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->module->l('Provincia'),
                        'name'  => 'SQ_SENDER_STATE',
                        'desc'  => $this->module->l('Es: NA, RM, MI'),
                        'class' => 'fixed-width-sm',
                    ],
                    [
                        'type'  => 'text',
                        'label' => $this->module->l('P. IVA'),
                        'name'  => 'SQ_SENDER_VAT',
                    ],
                ],
                'submit' => [
                    'title' => $this->module->l('Salva e continua'),
                    'icon'  => 'process-icon-save',
                ],
            ],
        ];

        return $helper->generateForm([$formFields]);
    }
}