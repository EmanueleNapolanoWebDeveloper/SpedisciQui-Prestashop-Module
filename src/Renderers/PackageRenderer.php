<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class PackageRenderer
{


    private spedisciquishipping $module;
    private PackageRepository $packRepo;
    private Context $context;


    // =============================================
    // COSTRUTTORE
    // =============================================
    public function __construct(
        spedisciquishipping $module,
        PackageRepository $packRepo,
        Context $context,
    ) {
        $this->module = $module;
        $this->packRepo = $packRepo;
        $this->context = $context;
    }



    // =============================================
    // RENDERIZZA FORM PER PACKAGE - INZIO
    // =============================================
    public function renderPackageForm(array $package, string $formAction): string
    {
        // 1. Carica il CSS specifico usando l'helper interno
        $this->addCss('package_init_styles.css');

        // 2. Utilizza il contesto iniettato nella classe (evita Context::getContext() statico)
        $smarty = $this->context->smarty;

        // 3. Assegna le variabili a Smarty
        $smarty->assign([
            'package' => $package,
            'action' => $formAction,
            // Assicurati che la classe SetupSteps sia importata o usa il namespace corretto
            'setupStep' => SetupSteps::PACKAGE,
        ]);

        // 4. Definisci il percorso assoluto usando la costante nativa di PrestaShop
        $templatePath = _PS_MODULE_DIR_ . 'spedisciquishipping/views/templates/admin/_partials/_initial/package_init.tpl';

        // 5. RITORNA la stringa HTML (soddisfa il tipo di ritorno : string)
        return $smarty->fetch($templatePath);
    }
    // =============================================
    // RENDERIZZA FORM PER PACKAGE - FINE
    // =============================================


    //==========================================
    // HELPERS - ADD CSS 
    //==========================================
    private function addCss(string $filename): void
    {
        $cssPath = $this->module->getPathUri() . 'views/css/admin/initial/' . $filename;

        $this->context->controller->addCSS($cssPath, 'all', null, false);
    }
}
