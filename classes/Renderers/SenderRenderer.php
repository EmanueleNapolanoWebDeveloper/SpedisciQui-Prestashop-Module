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
            'views/templates/admin/_partials/_initial/sender_form_init.tpl'
        );
    }
    //==========================================
    // RENDER FORM PER INSERIMENTO MITTENTE (ADDRESS SHOP) - FINE
    //==========================================




    //==========================================
    // RENDER FORM PER UPDATE MITTENTE (ADDRESS SHOP) - INIZIO
    //==========================================
    public function renderSenderUpdateForm(int $idSender, array $data): string|false
    {

        if (!$idSender) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Id Sender non torvato',
                3
            );
            return false;
        }

        $sender = $this->senderRepo->getSenderAddressById($idSender);

        if (empty($sender) || !$sender) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Sender non torvato per #ID: ' . $idSender,
                3
            );
            return false;
        }

        // ✅ $action costruita qui con i parametri corretti del tuo routing
        $action = $this->context->link->getAdminLink('AdminModules', true, [], [
            'configure'   => $this->module->name,
            'action'      => 'updateSender',
            'id_sender'   => $idSender,
        ]);

        // caricamento css
        $css = $this->module->getPathUri() . 'views/css/';
        $this->context->controller->addCSS($css . 'admin/settings/sender/sender_update_form.css', 'all', null, false);

        $this->context->smarty->assign([
            'sender' => $sender,
            'data' => $data,
            'action' => $action
        ]);

        return $this->context->smarty->fetch(
            'module:spedisciquishipping/views/templates/admin/_partials/_settings/_sender/sender_update_form.tpl'
        );
    }

    //==========================================
    // RENDER FORM PER UPDATE MITTENTE (ADDRESS SHOP) - FINE
    //==========================================
}
