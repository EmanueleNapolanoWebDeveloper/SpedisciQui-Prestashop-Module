<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class CredentialsRenderer
{

    private spedisciquishipping $module;
    private CredentialsRepositories $credentialsRepo;

    public function __construct(spedisciquishipping $module, CredentialsRepositories $credentialsRepo)
    {
        $this->module = $module;
        $this->credentialsRepo = $credentialsRepo;
    }


    //==========================================
    // RENDER FORM
    //==========================================
    public function renderCredentialsForm(): string
    {
        $credentials = new CredentialServices()->getToken();
        $currentToken = $credentials['access_token'] ?? '';
        $expiresAt    = $credentials['expires_at']    ?? null;
        $daysLeft     = new CredentialServices()->daysUntilExpiry();

        $helper                  = new HelperForm();
        $helper->module          = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->token           = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex    = AdminController::$currentIndex . '&configure=' . $this->module->name;
        $helper->submit_action   = 'submitSpedisciQuiCredentials';
        $helper->fields_value    = [
            'SPEDISCIQUI_ACCESS_TOKEN' => $currentToken,
        ];

        // --- Descrizione dinamica in base allo stato del token ---
        $desc = $this->module->l('Incolla il token ottenuto dalla piattaforma SpedisciQui.');

        if ($expiresAt) {
            if ($daysLeft === 0) {
                $desc = '⛔ ' . $this->module->l('Token SCADUTO. Inserisci un nuovo token.');
            } elseif ($daysLeft <= 7) {
                $desc = '⚠️ ' . sprintf(
                    $this->module->l('Token in scadenza tra %d giorni (%s). Rinnovalo presto.'),
                    $daysLeft,
                    date('d/m/Y', strtotime($expiresAt))
                );
            } else {
                $desc = '✅ ' . sprintf(
                    $this->module->l('Token attivo — scade il %s (%d giorni rimanenti).'),
                    date('d/m/Y', strtotime($expiresAt)),
                    $daysLeft
                );
            }
        }

        $formFields = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Configurazione API SpedisciQui'),
                    'icon'  => 'icon-key',
                ],
                'input' => [
                    [
                        'type'     => 'text',
                        'label'    => $this->module->l('Access Token'),
                        'name'     => 'SPEDISCIQUI_ACCESS_TOKEN',
                        'required' => true,
                        'class'    => 'fixed-width-xxl',
                        'desc'     => $desc,
                    ],
                ],
                'submit' => [
                    'title' => $this->module->l('Salva e verifica'),
                    'icon'  => 'process-icon-save',
                ],
            ],
        ];

        return $helper->generateForm([$formFields]);
    }
}
