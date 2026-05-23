<?php
// ContentHandler.php
if (!defined('_PS_VERSION_')) {
    exit;
}

class ContentHandler
{
    private spedisciquishipping  $module;
    private CredentialsRepositories $credentialsRepo;
    private CredentialsHandlers  $credentialsHandler;
    private CredentialsRenderer  $credentialsRenderer;
     private Context                 $context;

    public function __construct(spedisciquishipping $module)
    {
        $this->module = $module;

        // costruzione dipendenze in ordine
        $configRepo   = new ConfigRepositories(Context::getContext());
        $apiClient    = new ApiClient($configRepo);

        // unica istanza condivisa tra handler e renderer
        $this->credentialsRepo    = new CredentialsRepositories(Context::getContext(), $apiClient);
        $this->credentialsHandler = new CredentialsHandlers($module, $this->credentialsRepo);
        $this->credentialsRenderer = new CredentialsRenderer($module, $this->credentialsRepo);
        $this->context = Context::getContext();
    }

    public function handle(): string
    {
        $output = '';

        //========================================================
        //ACCESS_TOKEN SUBMIT
        //========================================================

        if (Tools::isSubmit('submitSpedisciQuiCredentials')) {
            $output .= $this->credentialsHandler->handleSubmit();
        }

        $this->context->smarty->assign('content', $this->resolveView());

        return $output . $this->module->display(
            $this->module->getLocalPath(),
            'views/templates/admin/config.tpl'
        );


        //========================================================
        //PACKAGE SUBMIT
        //========================================================
    }

    private function resolveView(): string
    {
        // ✅ usa il repo già istanziato — nessuna seconda istanza
        $credentials = $this->credentialsRepo->get();

        if (!$credentials) {
            return $this->credentialsRenderer->renderCredentialsForm();
        }

        // placeholder → qui aggiungerai dashboard, step pacco, step mittente ecc.
        return $this->credentialsRenderer->renderCredentialsForm();
    }
}
