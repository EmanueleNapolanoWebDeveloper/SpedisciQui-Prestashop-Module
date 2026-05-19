<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class SenderRepository
{

    const TABLE_NAME = 'spedisciqui_sender';


     public function saveSender(array $data, $id_shop = null)
    {

        $db = DB::getInstance();
        $pfx = _DB_PREFIX_;
        $id_shop = $id_shop !== null ? (int)$id_shop : (int)Context::getContext()->shop->id;

        $row = [
            'name'    => pSQL($data['name'] ?? ''),
            'surname' => pSQL($data['surname'] ?? ''),
            'address' => pSQL($data['address'] ?? ''),
            'zip'     => pSQL($data['zip'] ?? ''),
            'city'    => pSQL($data['city'] ?? ''),
            'prov'    => pSQL(strtoupper($data['prov'] ?? '')),
            'country' => pSQL(strtoupper($data['country'] ?? 'IT')),
            'phone'   => pSQL($data['phone'] ?? ''),
        ];

        $exist = $db->getRow("
         SELECT id FROM `{$pfx}spedisciqui_sender`
        WHERE id_shop = {$id_shop}
        ");

        if ($exist) {
            return $db->update(
                'spedisciqui_sender',
                array_merge(['id_shop' => $id_shop], $row)
            );
        };

        return $db->insert(
            'spedisciqui_sender',
            array_merge(['id_shop' => $id_shop], $row)
        );
    }

     public function getSender($id_shop = null)
    {

        $id_shop = $id_shop !== null ? (int)$id_shop : (int)Context::getContext()->shop->id;

        $result = DB::getInstance()->getRow("
            SELECT name, surname, address, zip, city, prov, country, phone
            FROM `" . _DB_PREFIX_ . "spedisciqui_sender`
            WHERE id_shop = {$id_shop}
        ");

        return $result ?: null;
    }
}
