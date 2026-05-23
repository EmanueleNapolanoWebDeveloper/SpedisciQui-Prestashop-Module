<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class SenderRepository
{
    private Context $context;

    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    //=============================================
    // RECUPERO SENDER DEFAULT
    //=============================================
    public function getDefault(): ?array
    {
        $idShop = (int) $this->context->shop->id;

        $row = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . 'spedisciqui_sender_address`
             WHERE `id_shop` = ' . $idShop . '
             AND `is_default` = 1
             AND `is_active` = 1'
        );

        return $row ?: null;
    }

    //=============================================
    // SALVATAGGIO SENDER
    //=============================================
    public function save(array $data): bool
    {
        $idShop = (int) $this->context->shop->id;

        // se esiste già un default, lo toglie
        Db::getInstance()->update(
            'spedisciqui_sender_address',
            ['is_default' => 0],
            '`id_shop` = ' . $idShop
        );

        return (bool) Db::getInstance()->insert(
            'spedisciqui_sender_address',
            [
                'id_shop'       => $idShop,
                'label'         => pSQL($data['label']        ?? 'Sede principale'),
                'company'       => pSQL($data['company']      ?? ''),
                'firstname'     => pSQL($data['firstname']    ?? ''),
                'lastname'      => pSQL($data['lastname']     ?? ''),
                'phone'         => pSQL($data['phone']        ?? ''),
                'phone_mobile'  => pSQL($data['phone_mobile'] ?? ''),
                'email'         => pSQL($data['email']        ?? ''),
                'address1'      => pSQL($data['address1']     ?? ''),
                'address2'      => pSQL($data['address2']     ?? ''),
                'postcode'      => pSQL($data['postcode']     ?? ''),
                'city'          => pSQL($data['city']         ?? ''),
                'state_code'    => pSQL($data['state_code']   ?? ''),
                'country_iso'   => pSQL($data['country_iso']  ?? 'IT'),
                'id_country'    => (int) ($data['id_country'] ?? 110),
                'vat_number'    => pSQL($data['vat_number']   ?? ''),
                'is_default'    => 1,
                'is_active'     => 1,
            ]
        );
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
