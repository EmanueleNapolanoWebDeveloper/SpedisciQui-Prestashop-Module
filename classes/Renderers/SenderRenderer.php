<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class SenderRenderer
{
    private spedisciquishipping $module;
    private SenderRepository    $senderRepo;
    private Context $context;

    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(spedisciquishipping $module, SenderRepository $senderRepo, Context $context)
    {
        $this->module     = $module;
        $this->senderRepo = $senderRepo;
        $this->context = $context;
    }




    //==========================================
    // RENDER FORM PER INSERIMENTO MITTENTE (ADDRESS SHOP) - INIZIO
    //==========================================
    public function renderSenderForm(): string
    {
        $existing = $this->senderRepo->getDefault();

        $sender = [
            'label'        => $existing['label']        ?? 'Sede principale',
            'company'      => $existing['company']      ?? '',
            'name'         => $existing['firstname']    ?? '',
            'surname'      => $existing['lastname']     ?? '',
            'phone'        => $existing['phone']        ?? '',
            'phone_mobile' => $existing['phone_mobile'] ?? '',
            'email'        => $existing['email']        ?? '',
            'address'      => $existing['address1']     ?? '',
            'address2'     => $existing['address2']     ?? '',
            'zip'          => $existing['postcode']     ?? '',
            'city'         => $existing['city']         ?? '',
            'prov'         => $existing['state_code']   ?? '',
            'country'      => $existing['country']      ?? 'IT',
            'vat_number'          => $existing['vat_number']   ?? '',
        ];

        $action = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token=' . Tools::getAdminTokenLite('AdminModules');

        $this->context->smarty->assign([
            'sender' => $sender,
            'action' => $action,
        ]);

        return $this->module->display(
            $this->module->getLocalPath(),
            'views/templates/admin/_partials/initial/sender_form_init.tpl'
        );
    }
    //==========================================
    // RENDER FORM PER INSERIMENTO MITTENTE (ADDRESS SHOP) - FINE
    //==========================================
}
