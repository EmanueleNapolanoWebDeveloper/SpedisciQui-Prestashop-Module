<?php

class CarrierServices
{

    private Db $db;
    private CarrierRepository $carrierRepo;

    // =============================================
    // COSTRUTTORE
    // =============================================
    public function __construct(
        CarrierRepository $carrierRepo
    ) {
        $this->db = Db::getInstance();
        $this->carrierRepo = $carrierRepo;
    }


    // =============================================
    // PRELEVA TARIFFE TRAMITE ID
    // =============================================


    public function getTariffByCarrierId(
        int $carrierId,
        ?string $serviceCode,
        ?float $weight
    ) {
        $sql = new DbQuery();
        $sql->select('*')
            ->from('spedisciqui_weight_tariffs')
            ->where('id_carrier = ' . (int) $carrierId)
            ->where('is_active = 1');

        if ($serviceCode !== null) {
            $sql->where('service_code = \'' . pSQL($serviceCode) . '\'');
        }

        if ($weight !== null) {
            $sql->where('weight_from <= ' . (float) $weight)
                ->where('weight_to > ' . (float) $weight);
        }

        $sql->orderBy('weight_from ASC');

        $result = $this->db->executeS($sql);

        return is_array($result) ? $result : [];
    }


    // =============================================
    // RECUPERO TARIFFA IN BASE AL PESO CART
    // =============================================


    public function getApplicableTariff(
        string $serviceCode,
        float $weight,
    ): array|false {

        $sql = new DbQuery();

        $sql->select('*')
            ->from('spedisciqui_weight_tariffs')
            ->where('service_code = \'' . pSQL($serviceCode) . '\'')
            ->where('is_active = 1')
            ->where('weight_from <= ' . (float) $weight)
            ->where('weight_to > ' . (float) $weight);

        $sql->limit(1);

        $result = $this->db->executeS($sql);

        return !empty($result) ? $result[0] : false;
    }


    // =============================================
    // CONTROLLO SOVRAPPOSIZIONE PREZZI AL SALVATAGGIO/UPDATE
    // =============================================

    public function hasOverlappingRange(
        int $carrierId,
        string $carrierCode,
        float $weightFrom,
        float $weightTo,
        ?int $excludeId = null
    ): bool {

        $sql = new DbQuery();

        $sql->select('COUNT(*)')
            ->from('spedisciqui_weight_tariffs')
            ->where('id_carrier = ' . (int) ($carrierId))
            ->where('service_code = \'' . pSQL($carrierCode) . '\'')
            ->where('is_active = 1')
            ->where('weight_from < ' . (float) $weightTo)
            ->where('weight_to > ' . (float) $weightFrom);

        if ($excludeId !== null) {
            $sql->where('id != ' . (int) $excludeId);
        }

        return (bool) $this->db->getValue($sql);
    }


    // =============================================
    // SALVATAGGIO TARIFFE
    // =============================================


    public function saveTariffs(string $carrierCode, array $data): bool
    {
        $carrier = $this->carrierRepo->getCarrierByCode($carrierCode);

        if (empty($carrier)) {
            PrestaShopLogger::addLog('[SpedisciQui] Carrier non trovato per il codice: ' . $carrierCode, 3);
            return false;
        }

        $idCarrier = (int) $carrier['id_carrier'];


        $serviceCode = !empty($carrier['carrier_code'])
            ? $carrier['carrier_code']
            : $carrier['service_code'];


        // normalizza dati in entrata
        $normalizedRow = $this->normalizeRows($data);


        // Validazione
        try {
            $this->validateRows($normalizedRow, $serviceCode);
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] saveTariffs validazione fallita: ' . $e->getMessage(),
                3,
                null,
                'SpedisciQuiShipping'
            );
            return false;
        }

        // recuper sender coinvolti
        $senderIds = array_unique(array_column($normalizedRow, 'id_sender'));

        PrestaShopLogger::addLog(
            'sendersId: ' . print_r($senderIds, true),
            1
        );

        $this->db->execute('START TRANSACTION');

        try {
            // Elimina fasce dei sender esistenti
            foreach ($senderIds as $idSender) {
                $this->db->execute(
                    'DELETE FROM `' . _DB_PREFIX_ . 'spedisciqui_weight_tariffs`'
                    . ' WHERE `id_carrier` = ' . (int) $idCarrier
                    . ' AND `id_sender` = ' . (int) $idSender
                );
            }

            $now = date('Y-m-d H:i:s');


            // Inserimento nuove fasce
            foreach ($normalizedRow as $row) {

                $inserted = $this->db->insert(
                    'spedisciqui_weight_tariffs',
                    [
                        'id_carrier' => $idCarrier,
                        'id_sender' => $row['id_sender'],
                        'service_code' => pSQL($serviceCode),
                        'weight_from' => $row['weight_from'],
                        'weight_to' => $row['weight_to'],
                        'tariff' => $row['tariff'],
                        'is_active' => $row['is_active'],
                        'date_add' => $now,
                        'date_upd' => $now,
                    ]

                );

                if (!$inserted) {
                    throw new RuntimeException(
                        sprintf(
                            'Inserimento fallito: sender %d, fascia [%.2f-%.2f]',
                            $row['id_sender'],
                            $row['weight_from'],
                            $row['weight_to']
                        )
                    );
                }

            }

            $this->db->execute('COMMIT');
            return true;

        } catch (Exception $e) {
            $this->db->execute('ROLLBACK');

            PrestaShopLogger::addLog(
                '[SpedisciQui] saveTariffs errore DB: ' . $e->getMessage(),
                3,
                null,
                'SpedisciQuiShipping'
            );

            return false;
        }
    }


    // =========================================================================
    // ELIMINA TUTTE LE TARIFFE DI UN CARRIER
    // =========================================================================



    public function deleteTariffsByCarrierCode(
        string $carrierCode
    ): bool {

        $carrier = $this->carrierRepo->getCarrierByCode($carrierCode);

        if (empty($carrier)) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] getCarrierByCode non trovato CarrierCode: ',
                3,
            );
            return false;
        }

        return (bool) $this->db->execute(
            'DELETE FROM `' . _DB_PREFIX_ . 'spedisciqui_weight_tariffs`
             WHERE `id_carrier` = ' . $carrier['id_carrier']
        );
    }


    // =========================================================================
    // PRIVATE — NORMALIZZAZIONE
    // =========================================================================

    /**
     * Converte i valori grezzi del POST in tipi e formati coerenti.
     * Gestisce sia 'price' (chiave TPL) sia 'tariff' (chiave interna).
     */
    private function normalizeRows(array $data): array
    {
        $rows = [];

        foreach ($data as $row) {
            $priceValue = $row['price'] ?? $row['tariff'] ?? 0;

            $rows[] = [
                'id_sender' => (int) ($row['id_sender'] ?? 0),
                'weight_from' => (float) number_format(
                    (float) str_replace(',', '.', $row['weight_from'] ?? 0),
                    6,
                    '.',
                    ''
                ),
                'weight_to' => (float) number_format(
                    (float) str_replace(',', '.', $row['weight_to'] ?? 0),
                    6,
                    '.',
                    ''
                ),
                'tariff' => (float) number_format(
                    (float) str_replace(',', '.', $priceValue),
                    3,
                    '.',
                    ''
                ),
                'is_active' => isset($row['is_active']) && $row['is_active'] !== ''
                    ? (int) (bool) $row['is_active']
                    : 1,
            ];
        }

        return $rows;
    }


    // =========================================================================
    // PRIVATE — VALIDAZIONE RIGHE
    // =========================================================================

    private function validateRows(array $rows, string $serviceCode): void
    {
        if (empty($rows)) {
            return;
        }

        // --- Validazione scalare per riga ---
        foreach ($rows as $i => $row) {
            $label = sprintf('Fascia #%d [%s] (mittente %d)', $i + 1, $serviceCode, $row['id_sender']);

            if ($row['id_sender'] <= 0) {
                throw new InvalidArgumentException($label . ': id_sender non valido.');
            }

            if ($row['weight_from'] < 0 || $row['weight_to'] < 0) {
                throw new InvalidArgumentException($label . ': i pesi non possono essere negativi.');
            }

            if ($row['weight_from'] >= $row['weight_to']) {
                throw new InvalidArgumentException(
                    $label . ': weight_from deve essere minore di weight_to.'
                );
            }

            if ($row['tariff'] < 0) {
                throw new InvalidArgumentException($label . ': la tariffa non può essere negativa.');
            }
        }

        // --- Controllo sovrapposizioni raggruppato per sender ---
        $bySender = [];
        foreach ($rows as $row) {
            $bySender[$row['id_sender']][] = $row;
        }

        foreach ($bySender as $idSender => $senderRows) {
            $count = count($senderRows);

            for ($a = 0; $a < $count; $a++) {
                for ($b = $a + 1; $b < $count; $b++) {
                    $ra = $senderRows[$a];
                    $rb = $senderRows[$b];

                    if ($ra['weight_from'] < $rb['weight_to'] && $rb['weight_from'] < $ra['weight_to']) {
                        throw new InvalidArgumentException(
                            sprintf(
                                'Sovrapposizione per mittente %d [%s]: '
                                . 'fascia [%.2f-%.2f] si sovrappone a [%.2f-%.2f].',
                                $idSender,
                                $serviceCode,
                                $ra['weight_from'],
                                $ra['weight_to'],
                                $rb['weight_from'],
                                $rb['weight_to']
                            )
                        );
                    }
                }
            }
        }
    }

}
