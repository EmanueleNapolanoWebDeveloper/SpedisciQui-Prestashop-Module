<?php

class SenderServices
{
    private $context;

    public function __construct()
    {
        $this->context = Context::getContext();
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
        if (empty($data['phone']) && Validate::isPhoneNumber($data['phone'])) {
            $errors[] = 'Il telefono è obbligatorio.';
        }

        return $errors;
    }


    // =========================================================
    // ESTRAZIONE DATI DA FORM SETUP
    // =========================================================
    public function extractFromRequest(): array
    {

        return [
            'label' => Tools::getValue('SQ_SENDER_LABEL', 'Sede principale'),
            'company' => Tools::getValue('SQ_SENDER_COMPANY', ''),
            'firstname' => Tools::getValue('SQ_SENDER_FIRSTNAME', ''),
            'lastname' => Tools::getValue('SQ_SENDER_LASTNAME', ''),
            'phone' => Tools::getValue('SQ_SENDER_PHONE', ''),
            'phone_mobile' => Tools::getValue('SQ_SENDER_PHONE_MOBILE', ''),
            'email' => Tools::getValue('SQ_SENDER_EMAIL', ''),
            'address1' => Tools::getValue('SQ_SENDER_ADDRESS1', ''),
            'address2' => Tools::getValue('SQ_SENDER_ADDRESS2', ''),
            'postcode' => Tools::getValue('SQ_SENDER_POSTCODE', ''),
            'city' => Tools::getValue('SQ_SENDER_CITY', ''),
            'state_code' => Tools::getValue('SQ_SENDER_STATE', ''),
            'country_iso' => Tools::getValue('SQ_SENDER_COUNTRY_ISO', 'IT'),
            'id_country' => (int) Tools::getValue('SQ_SENDER_ID_COUNTRY', 110),
            'vat_number' => Tools::getValue('SQ_SENDER_VAT_NUMBER', ''),
        ];
    }


    // =========================================================
    // ESTRAZIONE DATI DA DASHBOARD
    // =========================================================
    public function extractUpdateFromRequest(int $idSender)
    {
        return [
            'id' => $idSender,
            'label' => Tools::getValue('label', ''),
            'company' => Tools::getValue('company', '') ?: null,
            'firstname' => Tools::getValue('firstname', ''),
            'lastname' => Tools::getValue('lastname', ''),
            'phone' => Tools::getValue('phone', ''),
            'phone_mobile' => Tools::getValue('phone_mobile', '') ?: null,
            'email' => Tools::getValue('email', '') ?: null,
            'address1' => Tools::getValue('address1', ''),
            'address2' => Tools::getValue('address2', '') ?: null,
            'postcode' => Tools::getValue('postcode', ''),
            'city' => Tools::getValue('city', ''),
            'state_code' => Tools::getValue('state_code', '') ?: null,
            'country_iso' => Tools::getValue('country_iso', 'IT'),
            'vat_number' => Tools::getValue('vat_number', '') ?: null,
            'is_default' => (int) Tools::getValue('is_default', 0),
            'is_active' => (int) Tools::getValue('is_active', 1),
            'date_upd' => date('Y-m-d H:i:s'),
        ];
    }



    // =========================================================
    // NORMALIZZAZIONE
    // =========================================================
    public function normalizeForView(array $row): array
    {
        return [
            'label' => $row['label'] ?? 'Sede principale',
            'company' => $row['company'] ?? '',
            'firstname' => $row['firstname'] ?? '',
            'lastname' => $row['lastname'] ?? '',
            'phone' => $row['phone'] ?? '',
            'phone_mobile' => $row['phone_mobile'] ?? '',
            'email' => $row['email'] ?? '',
            'address1' => $row['address1'] ?? '',
            'address2' => $row['address2'] ?? '',
            'postcode' => $row['postcode'] ?? '',
            'city' => $row['city'] ?? '',
            'state_code' => $row['state_code'] ?? '',
            'country' => $row['country'] ?? 'IT',
            'vat_number' => $row['vat_number'] ?? '',
            'is_default' => $row['is_default'] ?? '',
        ];
    }
}


