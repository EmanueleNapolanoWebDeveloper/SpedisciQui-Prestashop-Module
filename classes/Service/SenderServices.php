<?php

class SenderServices
{

    private SenderRepository $senderRepo;
    private $context;

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->senderRepo = new SenderRepository($this->context);
    }


    //=============================================
    // VALIDAZIONE CAMPI OBBLIGATORI
    //=============================================
    public function validate(array $data): array
    {
        $errors = [];

        if (empty($data['firstname'])) {
            $errors[] = 'Il nome è obbligatorio.';
        }
        if (empty($data['lastname'])) {
            $errors[] = 'Il cognome è obbligatorio.';
        }
        if (empty($data['address1'])) {
            $errors[] = "L'indirizzo è obbligatorio.";
        }
        if (empty($data['postcode'])) {
            $errors[] = 'Il CAP è obbligatorio.';
        }
        if (empty($data['city'])) {
            $errors[] = 'La città è obbligatoria.';
        }
        if (empty($data['phone'])) {
            $errors[] = 'Il telefono è obbligatorio.';
        }

        return $errors;
    }
}
