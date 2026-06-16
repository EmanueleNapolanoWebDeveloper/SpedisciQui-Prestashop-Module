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


    // //=============================================
    // // RECUPERO SENDER 
    // //=============================================
    public function getSenderAddress(): array|false
    {

        $idShop = (int) $this->context->shop->id;

        $sql = new DbQuery();

        try {
            $sql->select('*')
                ->from('spedisciqui_sender_address')
                ->where('is_active = 1')
                ->where('id_shop = ' . $idShop);

            $sender =  Db::getInstance()->getRow($sql);

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
    public function getSenderAddressById(int $idSender): array|false
    {

        $idShop = (int) $this->context->shop->id;

        $sql = new DbQuery();

        try {
            $sql->select('*')
                ->from('spedisciqui_sender_address')
                ->where('is_active = 1')
                ->where('id = ' . (int)$idSender)
                ->where('id_shop = ' . (int)$idShop);

            $sender =  Db::getInstance()->getRow($sql);

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
    // // MODIFICA SENDER 
    // //=============================================
    public function updateSenderAddress(array $data): bool
    {

        PrestaShopLogger::addLog(
            print_r($data, true),
            3
        );

        // controllo dati
        if (empty($data) || !$data) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Dati per upload Sender Mancanti!',
                3
            );
            return false;
        }

        // controllo id sender
        if (empty($data['id'])) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] ID Sender mancante!',
                3
            );

            return false;
        }

        $idSenderAddress = (int) $data['id'];
        $db = Db::getInstance();

        unset($data['id']);

        try {
            $result = $db->update(
                'spedisciqui_sender_address',
                $data,
                '`id` = ' . $idSenderAddress
            );

            if (!$result) {
                PrestaShopLogger::addLog(
                    '[SPedisciQUi] Errore durante aggiornamento del Mittente # ' . $idSenderAddress,
                    3
                );

                return false;
            }

            return true;
        } catch (Exception $e) {
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
}
