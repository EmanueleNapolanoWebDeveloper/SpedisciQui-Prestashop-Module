<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class PackageRenderer
{


    private spedisciquishipping $module;
    private PackageRepository   $packRepo;


    // =============================================
    // COSTRUTTORE
    // =============================================
    public function __construct(
        spedisciquishipping $module,
        PackageRepository   $packRepo
    ) {
        $this->module   = $module;
        $this->packRepo = $packRepo;
    }



    // =============================================
    // RENDERIZZA FORM PER PACKAGE - INZIO
    // =============================================
    public function renderPackageForm(string $formAction): string
    {

        $this->addCss('package_init_styles.css');

        $context = Context::getContext();

        // Recupera il pacco default (o valori vuoti se non esiste)
        $package = $this->packRepo->getDefault() ?? [
            'name'       => 'Default',
            'weight'     => '1.000',
            'length'     => '30.00',
            'width'      => '20.00',
            'height'     => '10.00',
            'is_default' => 1,
        ];

        

        $context->smarty->assign([
            'package' => $package,
            'action'  => $formAction,
            'setupStep' => SetupSteps::PACKAGE,
        ]);

        $context->controller->setTemplate(
            '../modules/spedisciquishipping/views/templates/admin/setup/package_config.tpl'
        );
    }
    // =============================================
    // RENDERIZZA FORM PER PACKAGE - FINE
    // =============================================


    //==========================================
    // HELPERS - ADD CSS 
    //==========================================
    private function addCss(string $filename): void
    {
        $cssPath = $this->module->getPathUri() . 'views/css/admin/initial';
        $this->context->controller->addCSS($cssPath . $filename, 'all', null, false);
    }
}
