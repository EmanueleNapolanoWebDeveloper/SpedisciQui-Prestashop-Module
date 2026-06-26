<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class SenderRepository
{

    private const TABLE = 'spedisciqui_sender_address';

    private Context $context;

    public function __construct()
    {
        $this->context = Context::getContext();
    }


    //=============================================
    // RECUPERO SENDER DEFAULT
    //=============================================
    public function getDefault(): ?array
    {
        $idShop = (int) $this->context->shop->id;

        $sql = new DbQuery();
        $sql->select('*')
            ->from(self::TABLE)
            ->where('id_shop = ' . (int) $idShop)
            ->where('is_default = 1')
            ->where('is_active = 1');

        $row = Db::getInstance()->getRow($sql);

        return $row ?: null;
    }

    //=============================================
    // RECUPERO TUTTI SENDER 
    //=============================================
    public function getAllSenders(): array
    {

        $sql = new DbQuery();

        $sql->select('s.*,sh.name AS shop_name')
            ->from(self::TABLE, 's')
            ->leftJoin('shop', 'sh', 'sh.id_shop = s.id_shop')
            ->orderBy('s.id_shop ASC, s.id ASC');

        $rows = Db::getInstance()->executeS($sql);

        return $rows ? array_map([$this, 'normalizeRow'], $rows) : [];
    }


    // //=============================================
    // // RECUPERO SENDER 
    // //=============================================
    public function getSenderAddress(): array|false
    {

        $idShop = (int) $this->context->shop->id;

        $sql = new DbQuery();

        try {
            $sql->select('*')
                ->from(self::TABLE)
                ->where('is_active = 1')
                ->where('id_shop = ' . $idShop);

            $sender = Db::getInstance()->getRow($sql);

            if (empty($sender)) {
                PrestaShopLogger::addLog(
                    sprintf(
                        '[SpedisciQui] Nessun mittente attivo trovato per shop #%d',
                        $idShop
                    ),
                    2
                );

                return false;
            }

            return $sender;
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Errore recupero mittente: ' . $e->getMessage(),
                3
            );

            return false;
        }
    }



    // //=============================================
    // // RECUPERO SENDER DA ID
    // //=============================================
    public function getSenderAddressById(int $idSender): array|null
    {

        $idShop = (int) $this->context->shop->id;

        if ($idSender <= 0) {
            return null;
        }

        $sql = new DbQuery();

        try {
            $sql->select('s.*, sh.name AS shop_name')
                ->from(self::TABLE, 's')
                ->leftJoin('shop', 'sh', 'sh.id_shop = s.id_shop')
                ->where('s.id = ' . (int) $idSender);


            $sender = Db::getInstance()->getRow($sql);

            if (empty($sender)) {
                PrestaShopLogger::addLog(
                    sprintf(
                        '[SpedisciQui] Nessun mittente attivo trovato per shop #%d',
                        $idShop
                    ),
                    2
                );

                return null;
            }

            return $sender ? $this->normalizeRow($sender) : null;
        } catch (Throwable $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Errore recupero mittente: ' . $e->getMessage(),
                3
            );

            return null;
        }
    }



    // //=============================================
    // // MODIFICA SENDER 
    // //=============================================
    public function updateSenderAddress(int $idSender, array $data): bool
    {

        if ($idSender <= 0) {
            throw new \InvalidArgumentException('id_sender non valido.');
        }


        // controllo dati
        if (empty($data) || !$data) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Dati per upload Sender Mancanti!',
                3
            );
            return false;
        }

        unset($data['id']);
        unset($data['id_sender']);

        $db = Db::getInstance();

        try {
            $result = $db->update(
                self::TABLE,
                $data,
                '`id` = ' . $idSender
            );

            if (!$result) {
                PrestaShopLogger::addLog(
                    '[SPedisciQUi] Errore durante aggiornamento del Mittente # ' . $idSender,
                    3
                );

                return false;
            }

            return true;
        } catch (Throwable $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Errore dell\'aggiornamento mittente: ' . $e->getMessage(),
                3
            );
            return false;
        }
    }





    //=============================================
    // SALVATAGGIO SENDER
    //=============================================
    public function save(array $data): bool
    {
        $idShop = (int) $this->context->shop->id;

        // se esiste già un default, lo toglie
        Db::getInstance()->update(
            self::TABLE,
            ['is_default' => 0],
            '`id_shop` = ' . $idShop
        );

        return (bool) Db::getInstance()->insert(
            self::TABLE,
            [
                'id_shop' => $idShop,
                'label' => pSQL($data['label'] ?? 'Sede principale'),
                'company' => pSQL($data['company'] ?? ''),
                'firstname' => pSQL($data['firstname'] ?? ''),
                'lastname' => pSQL($data['lastname'] ?? ''),
                'phone' => pSQL($data['phone'] ?? ''),
                'phone_mobile' => pSQL($data['phone_mobile'] ?? ''),
                'email' => pSQL($data['email'] ?? ''),
                'address1' => pSQL($data['address1'] ?? ''),
                'address2' => pSQL($data['address2'] ?? ''),
                'postcode' => pSQL($data['postcode'] ?? ''),
                'city' => pSQL($data['city'] ?? ''),
                'state_code' => pSQL($data['state_code'] ?? ''),
                'country_iso' => pSQL($data['country_iso'] ?? 'IT'),
                'id_country' => (int) ($data['id_country'] ?? 110),
                'vat_number' => pSQL($data['vat_number'] ?? ''),
                'is_default' => pSQL($data['is_default'] ?? ''),
                'is_active' => pSQL($data['is_active'] ?? ''),
            ]
        );
    }


    //=============================================
    // ELIMINAZIONE SENDER
    //=============================================
    public function deleteSender(int $idSender)
    {

        if (!$idSender) {
            throw new \InvalidArgumentException('id_sender non valido.');
        }

        $result = Db::getInstance()->delete(
            self::TABLE,
            'id =' . (int) $idSender,
            1
        );

        if (!$result) {
            throw new \RuntimeException('DELETE su ' . self::TABLE . ' fallita.');
        }
    }




    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Normalizza una riga proveniente dal DB (cast tipi, valori di default).
     *
     * @param  array<string, mixed> $row
     * @return array<string, mixed>
     */
    private function normalizeRow(array $row): array
    {
        return [
            'id_sender' => (int) ($row['id'] ?? 0),
            'id_shop' => (int) ($row['id_shop'] ?? 0),
            'shop_name' => (string) ($row['shop_name'] ?? '—'),
            'label' => (string) ($row['label'] ?? ''),
            'company' => (string) ($row['company'] ?? ''),
            'firstname' => (string) ($row['firstname'] ?? ''),
            'lastname' => (string) ($row['lastname'] ?? ''),
            'address1' => (string) ($row['address1'] ?? ''),
            'address2' => (string) ($row['address2'] ?? ''),
            'postcode' => (string) ($row['postcode'] ?? ''),
            'city' => (string) ($row['city'] ?? ''),
            'state_code' => (string) ($row['state_code'] ?? ''),
            'country_iso' => (string) ($row['country_iso'] ?? 'IT'),
            'id_country' => (int) ($row['id_country'] ?? 110),
            'phone' => (string) ($row['phone'] ?? ''),
            'phone_mobile' => (string) ($row['phone_mobile'] ?? ''),
            'email' => (string) ($row['email'] ?? ''),
            'vat_number' => (string) ($row['vat_number'] ?? ''),
            'is_default' => (bool) ($row['is_default'] ?? false),
            'is_active' => (bool) ($row['is_active'] ?? false),
            'date_add' => (string) ($row['date_add'] ?? ''),
            'date_upd' => (string) ($row['date_upd'] ?? ''),
        ];

    }

}
