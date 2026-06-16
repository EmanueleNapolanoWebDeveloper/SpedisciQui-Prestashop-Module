<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class CredentialsRenderer
{

    private spedisciquishipping $module;
    private CredentialsRepositories $credentialsRepo;


    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(spedisciquishipping $module, CredentialsRepositories $credentialsRepo)
    {
        $this->module = $module;
        $this->credentialsRepo = $credentialsRepo;
    }


    //==========================================
    // RENDER FORM - INIZIO
    //==========================================
    public function renderCredentialsForm(array $tokenData, string $formAction): string
    {
        $this->addCss('/credential_init_styles.css');
        
        $smarty = Context::getContext()->smarty;

        $smarty->assign([
            'formAction'   => $formAction,
            'currentToken' => $tokenData['access_token'] ?? '',
            'expiresAt'    => !empty($tokenData['expires_at']) ? date('d/m/Y', strtotime($tokenData['expires_at'])) : null,
            'daysLeft'     => $tokenData['days_left'] ?? 0,
            'tokenStatus'  => $tokenData['status'] ?? 'missing',
            'setupStep'    => SetupSteps::TOKEN,
        ]);

        // CORREZIONE: Usiamo il percorso fisico assoluto tramite la costante di PrestaShop
        $templatePath = _PS_MODULE_DIR_ . 'spedisciquishipping/views/templates/admin/_partials/_initial/token_config.tpl';

        return $smarty->fetch($templatePath);
    }
    //==========================================
    // RENDER FORM - FINE
    //==========================================



   private function addCss(string $filename): void
{
    $cssPath = $this->module->getPathUri() . 'views/css/admin/initial/';
    
    // Recuperiamo il contesto al volo tramite la classe nativa di PrestaShop
    Context::getContext()->controller->addCSS($cssPath . $filename, 'all', null, false);
}
}
