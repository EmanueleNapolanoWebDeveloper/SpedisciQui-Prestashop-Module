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
            $sql = 'INSERT INTO `' . _DB_PREFIX_ . self::TABLE_NAME . '`
                    (`id_product`, `id_sender`, `created_at`, `updated_at`)
                VALUES
                    (' . (int) $idProduct . ', ' . (int) $idSender . ', NOW(), NOW())
                ON DUPLICATE KEY UPDATE
                    `id_sender`  = ' . (int) $idSender . ',
                    `updated_at` = NOW()';

            return (bool) Db::getInstance()->execute($sql);

        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] SenderProductRepository::save() ERRORE: ' . $e->getMessage(),
                3
            );
            return false; // non rilanciare — lascia che il chiamante gestisca il false
        }
    }




    // ===================================
    // ELIMINA RECORD TABELLA ASSOCIATIVA PRODOTTO-SENDER
    // ===================================
    public function delete(int $idProduct)
    {

        if (!$idProduct) {
            throw new InvalidArgumentException('ID Prodotto richiesto per questa operazione!');
        }

        try {

            $sql = new DbQuery();

            $sql->from(self::TABLE_NAME)
                ->where('id_product = ' . (int) $idProduct);

            return (bool) Db::getInstance()->execute($sql);
        } catch (Throwable $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] Error deleting product sender: ' . $e->getMessage(),
                3
            );

            throw new Exception(
                'Errore durante la cancellazione del sender del prodotto',
                0,
                $e
            );
        }
    }
}