<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class SenderProductRepository
{

    const TABLE_NAME = 'spedisciqui_sender_product';

    // ===================================
    // RICERCA SENDER DA PRODOTTO
    // ===================================
    public function findByProduct(int $idProduct)
    {

        if (!$idProduct) {
            throw new InvalidArgumentException('ID Prodotto richiesto per questa operazione');
        }

        try {

            $sql = new DbQuery();

            $sql->select('id_sender')
                ->from(self::TABLE_NAME)
                ->where('id_product = ' . (int) $idProduct);

            $result = Db::getInstance()->getRow($sql);

            return $result['id_sender'] ?? null;

        } catch (Throwable $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Errore ricerca sender da prodotto: ' . $e->getMessage(),
                3
            );

            throw new Exception(
                'Errore durante la ricerca del sender dal prodotto',
                0,
                $e
            );
        }
    }

    // ===================================
    // SALVA RECORD TABELLA ASSOCIATIVA PRODOTTO-SENDER
    // ===================================
    public function save(int $idProduct, int $idSender): bool
    {
        if (!$idProduct || !$idSender) {
            throw new \InvalidArgumentException(
                'idProduct e idSender sono obbligatori e devono essere validi'
            );
        }

        PrestaShopLogger::addLog(
            '[SpedisciQui] SenderProductRepository::save() | id_product=' . $idProduct . ' id_sender=' . $idSender,
            1
        );

        try {
            $db = Db::getInstance();

            // Prova prima un update
            $exists = (int) $db->getValue(
                'SELECT COUNT(*) FROM `' . _DB_PREFIX_ . self::TABLE_NAME . '`
             WHERE `id_product` = ' . (int) $idProduct
            );

            if ($exists) {
                return (bool) $db->update(
                    self::TABLE_NAME,
                    [
                        'id_sender' => (int) $idSender,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ],
                    '`id_product` = ' . (int) $idProduct
                );
            }

            return (bool) $db->insert(
                self::TABLE_NAME,
                [
                    'id_product' => (int) $idProduct,
                    'id_sender' => (int) $idSender,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]
            );

        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] SenderProductRepository::save() ERRORE: ' . $e->getMessage(),
                3
            );
            return false;
        }
    }




    // ===================================
    // ELIMINA RECORD TABELLA ASSOCIATIVA PRODOTTO-SENDER
    // ===================================
    public function delete(int $idProduct): bool
    {
        if (!$idProduct) {
            throw new \InvalidArgumentException('ID Prodotto richiesto per questa operazione!');
        }

        try {
            return (bool) Db::getInstance()->delete(
                self::TABLE_NAME,
                '`id_product` = ' . (int) $idProduct
            );

        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] SenderProductRepository::delete() ERRORE: ' . $e->getMessage(),
                3
            );
            return false;
        }
    }
}