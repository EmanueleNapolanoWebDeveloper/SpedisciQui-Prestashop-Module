<?php


if (!defined('_PS_VERSION_')) {
    exit;
}

class PackageRepository
{

    const TABLE_NAME = 'spedisciqui_package';

    public function savePackage(int $id_shop, array $data)
    {

        $db = Db::getInstance();
        $pfx = _DB_PREFIX_;

        $row = [
            'height' => (float)$data['height'],
            'depth'  => (float)$data['depth'],
            'length' => (float)$data['length'],
            'weight' => (float)$data['weight']
        ];

        $exists = $db->getRow("
            SELECT id FROM `{$pfx}spedisciqui_package`
            WHERE id_shop = " . (int)$id_shop
        );

        if ($exists) {
            return $db->update(
                'spedisciqui_package',
                $row,
                "id_shop = " . (int)$id_shop
            );
        }

        return $db->insert(
            "spedisciqui_package",
            array_merge(['id_shop' => (int)$id_shop], $row)
        );
    }


    public function getPackage($id_shop = null)
    {
        $id_shop = $id_shop !== null ? (int)$id_shop : (int)Context::getContext()->shop->id;

        $result = Db::getInstance()->getRow("
            SELECT height, depth, length, weight
            FROM `" . _DB_PREFIX_ . "spedisciqui_package`
            WHERE id_shop = " . (int)$id_shop
        );

        return $result ?: null;
    }
}
