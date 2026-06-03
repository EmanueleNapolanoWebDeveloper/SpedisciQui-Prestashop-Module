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
    public function renderCredentialsForm(): string
    {
        $credentials  = $this->credentialsRepo->get();
        $currentToken = $credentials['access_token'] ?? '';
        $expiresAt    = $credentials['expires_at']    ?? null;
        $daysLeft     = (new CredentialServices())->daysUntilExpiry();

        $tokenStatus = 'none';
        if ($expiresAt) {
            if ($daysLeft === 0)   $tokenStatus = 'expired';
            elseif ($daysLeft <= 7) $tokenStatus = 'expiring';
            else                    $tokenStatus = 'active';
        }

        Context::getContext()->smarty->assign([
            'formAction'   => AdminController::$currentIndex
                . '&configure=' . $this->module->name
                . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'currentToken' => $currentToken,
            'expiresAt'    => $expiresAt ? date('d/m/Y', strtotime($expiresAt)) : null,
            'daysLeft'     => $daysLeft,
            'tokenStatus'  => $tokenStatus,
        ]);

        return Context::getContext()->smarty->fetch(
            'module:spedisciquishipping/views/templates/admin/_partials/_initial/token_config.tpl'
        );
    }
    //==========================================
    // RENDER FORM - FINE
    //==========================================
}
