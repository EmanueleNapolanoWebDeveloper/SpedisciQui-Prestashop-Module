<?php

class CredentialsHandlers
{

    private spedisciquishipping $module;
    private CredentialsRepositories $credentialsRepo;

    public function __construct(spedisciquishipping $module, CredentialsRepositories $credentialsRepo)
    {
        $this->module = $module;
        $this->credentialsRepo = $credentialsRepo;
    }



    //===========================================
    //SUBMIT TOKEN
    //===========================================
    public function handleSubmit(): string
    {
        $token = trim(Tools::getValue('SPEDISCIQUI_ACCESS_TOKEN', ''));

        if (empty($token)) {
            return $this->module->displayError(
                $this->module->l('Il token non può essere vuoto.')
            );
        }

        // VALIDAZIONE TOKEN
        if (!$this->credentialsRepo->validateToken($token)) {
            return $this->module->displayError(
                $this->module->l('Token non valido o formato errato.')
            );
        }

        // Salva nel repository → scadenza automatica +1 mese
        if (!$this->credentialsRepo->save($token)) {
            return $this->module->displayError(
                $this->module->l('Errore durante il salvataggio del token.')
            );
        }

        return $this->module->displayConfirmation(
            $this->module->l('Token salvato correttamente. Scadenza: ') .
                date('d/m/Y', strtotime('+1 month'))
        );
    }
}
