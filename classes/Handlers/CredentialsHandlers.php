<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class CredentialsHandlers
{
    private spedisciquishipping     $module;
    private CredentialsRepositories $credentialsRepo;
    private SetupManager            $setupManager;
    private string                  $output = '';



    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(
        spedisciquishipping     $module,
        CredentialsRepositories $credentialsRepo,
        SetupManager            $setupManager
    ) {
        $this->module          = $module;
        $this->credentialsRepo = $credentialsRepo;
        $this->setupManager    = $setupManager;
    }





    //===========================================
    // OUTPUT -INIZIO
    //===========================================
    public function getOutput(): string
    {
        return $this->output;
    }
    //===========================================
    // OUTPUT -FINE
    //===========================================






    //===========================================
    // SUBMIT TOKEN - INIZIO
    //===========================================
    public function handleSubmit(): void
    {
        $token = trim(Tools::getValue('SPEDISCIQUI_ACCESS_TOKEN', ''));

        if (empty($token)) {
            $this->output = $this->module->displayError(
                $this->module->l('Il token non può essere vuoto.')
            );
            return; // ← stop, non avanza
        }

        if (!new CredentialServices()->validateToken($token)) {
            $this->output = $this->module->displayError(
                $this->module->l('Token non valido o formato errato.')
            );
            return; // ← stop
        }

        if (!$this->credentialsRepo->save($token)) {
            $this->output = $this->module->displayError(
                $this->module->l('Errore durante il salvataggio del token.')
            );
            return; // ← stop
        }

        // Solo se tutto OK: avanza e mostra conferma
        $this->setupManager->advance();
        $this->output = $this->module->displayConfirmation(
            $this->module->l('Token salvato correttamente. Scadenza: ') .
                date('d/m/Y', strtotime('+1 month'))
        );
    }

    //===========================================
    // SUBMIT TOKEN - FINE
    //===========================================
    
}
