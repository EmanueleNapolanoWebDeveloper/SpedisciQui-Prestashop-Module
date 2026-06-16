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
        Context $context)
    {
        $this->module     = $module;
        $this->context = $context;
    }




    //==========================================
    // RENDER FORM PER INSERIMENTO MITTENTE (ADDRESS SHOP) - INIZIO
    //==========================================
    public function renderSenderForm(array $sender , string $formAction): string
    {

        // CSS per componente
        $this->addCss('/views/css/admin/initial/sender_init_styles.css');

        $this->context->smarty->assign([
            'sender' => $sender,
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
        array $data): string|false
    {

    $this->addCss('/sender_init_styles.css');

        $this->context->smarty->assign([
            'sender' => $sender,
            'data' => $data,
            'action' => $formAction
        ]);

        return $this->context->smarty->fetch(
            'module:spedisciquishipping/views/templates/admin/_partials/_settings/_sender/sender_update_form.tpl'
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
        $cssPath = $this->module->getPathUri() . 'views/css/admin/initial/';
        $this->context->controller->addCSS($cssPath . $filename, 'all', null, false);
    }
}
