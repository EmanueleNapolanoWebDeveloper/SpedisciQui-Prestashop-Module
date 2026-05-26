<?php

class PackageServices
{

    private PackageRepository $packRepo;


    public function __construct()
    {
        $this->packRepo = new PackageRepository;
    }


    //=============================================
    // RECUPERO PACKAGE DEFAULT
    //=============================================
    public function getDefault(?int $idShop = null): ?array
    {

        $idShop = $idShop ?? (int) Context::getContext()->shop->id;

        $row = Db::getInstance()->getRow(
            'SELECT * FROM `' . _DB_PREFIX_ . $this->packRepo::TABLE_NAME . '`
         WHERE `id_shop` = ' . (int) $idShop . '
         AND `is_default` = 1'
        );

        return $row ?: null;
    }

    // ================================================================
    // VALIDAZIONE
    // ================================================================
    public function validate(array $data): array
    {
        $errors = [];

        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = 'Il nome profilo è obbligatorio.';
        }
        if (empty($data['weight']) || (float) $data['weight'] <= 0) {
            $errors[] = 'Il peso deve essere maggiore di 0.';
        }
        if (empty($data['length']) || (float) $data['length'] <= 0) {
            $errors[] = 'La lunghezza deve essere maggiore di 0.';
        }
        if (empty($data['width']) || (float) $data['width'] <= 0) {
            $errors[] = 'La larghezza deve essere maggiore di 0.';
        }
        if (empty($data['height']) || (float) $data['height'] <= 0) {
            $errors[] = 'L\'altezza deve essere maggiore di 0.';
        }

        return $errors;
    }
}
