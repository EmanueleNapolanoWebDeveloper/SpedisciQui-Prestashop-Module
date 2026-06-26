<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class SenderRenderer
{
    private spedisciquishipping $module;
    private Context $context;

    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(
        spedisciquishipping $module,
        Context $context
    ) {
        $this->module = $module;
        $this->context = $context;
    }


    //==========================================
    // RENDER LISTA SENDERS
    //==========================================
    public function renderSendersList(
        array $senders,
        string $formAction,
        bool $showShopColumn = true
    ): string {

        $this->addCss('senders/_components/senders_list.css');

        $this->context->smarty->assign([
            'senders' => $senders,
            'form_action' => $formAction,
            'show_shop_column' => $showShopColumn,
        ]);

        return $this->module->display(
            $this->module->getLocalPath(),
            'views/templates/admin/_partials/_senders/_components/senders_list.tpl'
        );

    }



    //==========================================
    // RENDER FORM PER INSERIMENTO MITTENTE (ADDRESS SHOP) - INIZIO
    //==========================================
    public function renderSenderForm(array $sender, string $formAction): string
    {
        $this->addCss('initial/sender_init_styles.css');

        $this->context->smarty->assign([
            'sender' => $this->preFillSenderKeys($sender),
            'action' => $formAction,
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
    // RENDER FORM PER INSERIMENTO MITTENTE (ADDRESS SHOP) DA DASHBOARD - INIZIO
    //==========================================
    public function renderSenderCreateForm(string $formAction, int $idShop, array $sender = []): string
    {
        $this->addCss('initial/sender_init_styles.css');

        $this->context->smarty->assign([
            'sender' => $this->preFillSenderKeys($sender),
            'id_shop' => $idShop,
            'action' => $formAction,
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
    public function renderSenderUpdateForm(
        array $sender,
        string $formAction,
        array $data = []
    ): string|false {

        $this->addCss('initial/sender_init_styles.css');

        $this->context->smarty->assign([
            'sender' => $sender,
            'data' => $data,
            'action' => $formAction
        ]);

        return $this->context->smarty->fetch(
            'module:spedisciquishipping/views/templates/admin/_partials/_senders/_components/sender_update_form.tpl'
        );
    }

    //==========================================
    // RENDER FORM PER UPDATE MITTENTE (ADDRESS SHOP) - FINE
    //==========================================



    //==========================================
    // HELPERS - ADD CSS 
    //==========================================
    private function addCss(string $filename): void
    {
        $cssPath = $this->module->getPathUri() . 'views/css/admin/' . $filename;

        $this->context->controller->addCSS($cssPath, 'all', null, false);
    }


    private function preFillSenderKeys(array $sender): array
    {
        // Default a stringa vuota per evitare i Warning in creazione
        $defaults = [
            'id_sender' => 0,
            'label' => '',
            'company' => '',
            'vat_number' => '',
            'firstname' => '',
            'lastname' => '',
            'name' => '',
            'surname' => '',
            'phone' => '',
            'phone_mobile' => '',
            'email' => '',
            'address1' => '',
            'address' => '',
            'address2' => '',
            'postcode' => '',
            'zip' => '',
            'city' => '',
            'state_code' => '',
            'prov' => '',
            'country_iso' => 'IT',
            'is_default' => 0,
            'is_active' => 1
        ];

        $sender = array_merge($defaults, $sender);

        // Allineamento bidirezionale delle chiavi (Database <-> Template)
        if (!empty($sender['firstname'])) {
            $sender['name'] = $sender['firstname'];
        }
        if (!empty($sender['name'])) {
            $sender['firstname'] = $sender['name'];
        }

        if (!empty($sender['lastname'])) {
            $sender['surname'] = $sender['lastname'];
        }
        if (!empty($sender['surname'])) {
            $sender['lastname'] = $sender['surname'];
        }

        if (!empty($sender['postcode'])) {
            $sender['zip'] = $sender['postcode'];
        }
        if (!empty($sender['zip'])) {
            $sender['postcode'] = $sender['zip'];
        }

        if (!empty($sender['address1'])) {
            $sender['address'] = $sender['address1'];
        }
        if (!empty($sender['address'])) {
            $sender['address1'] = $sender['address'];
        }

        if (!empty($sender['state_code'])) {
            $sender['prov'] = $sender['state_code'];
        }
        if (!empty($sender['prov'])) {
            $sender['state_code'] = $sender['prov'];
        }

        return $sender;
    }
}
