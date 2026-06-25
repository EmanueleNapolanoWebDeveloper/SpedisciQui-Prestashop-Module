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


        $serviceCode = !empty($carrier['carrier_code']) ? $carrier['carrier_code'] : $carrier['service_code'];
        $serviceCodeEscaped = pSQL($serviceCode);

        // Validazione
        try {
            $this->validateRows($data, $serviceCodeEscaped);
        } catch (Exception $e) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] saveTariffs validazione fallita: ' . $e->getMessage(),
                3,
                null,
                'SpedisciQuiShipping'
            );
            return false;
        }

        $this->db->execute('START TRANSACTION');

        try {
            // Elimina fasce esistenti
            $this->db->execute(
                'DELETE FROM `' . _DB_PREFIX_ . 'spedisciqui_weight_tariffs`'
                . ' WHERE `id_carrier` = ' . $idCarrier
            );

            // Inserimento nuove fasce
            foreach ($data as $row) {
                $weightFrom = (float) str_replace(',', '.', $row['weight_from'] ?? 0);
                $weightTo = (float) str_replace(',', '.', $row['weight_to'] ?? 0);

                // 🔥 CORREZIONE BUG 1: Cerchiamo 'price' (come definito nel TPL) o 'tariff' come fallback
                $priceValue = isset($row['price']) ? $row['price'] : ($row['tariff'] ?? 0);
                $tariff = (float) str_replace(',', '.', $priceValue);

                // 🔥 CORREZIONE BUG 2: Forziamo a 1 (Attivo) se non è specificato nel form, così il checkout lo vede subito
                $active = (isset($row['is_active']) && $row['is_active'] !== '') ? (int) (bool) $row['is_active'] : 1;

                $now = date('Y-m-d H:i:s');

                $inserted = $this->db->insert(
                    'spedisciqui_weight_tariffs',
                    [
                        'id_carrier' => $idCarrier,
                        'service_code' => $serviceCodeEscaped,
                        'weight_from' => number_format($weightFrom, 6, '.', ''),
                        'weight_to' => number_format($weightTo, 6, '.', ''),
                        'tariff' => number_format($tariff, 3, '.', ''),
                        'is_active' => $active,
                        'date_add' => $now,
                        'date_upd' => $now,
                    ]
                );

                if (!$inserted) {
                    throw new RuntimeException('Errore nell\'inserimento della tariffa');
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
    // PRIVATE — VALIDAZIONE RIGHE
    // =========================================================================

    private function validateRows(array $rows, string $serviceCode)
    {
        if (empty($rows)) {
            return;
        }

        $ranges = [];

        foreach ($rows as $i => $row) {
            $label = 'Fascia #' . ($i + 1) . ' [' . $serviceCode . ']';
            $weightFrom = (float) str_replace(',', '.', $row['weight_from'] ?? '');
            $weightTo = (float) str_replace(',', '.', $row['weight_to'] ?? '');
            $tariff = (float) str_replace(',', '.', $row['tariff'] ?? '');

            if ($weightFrom < 0 || $weightTo < 0) {
                throw new InvalidArgumentException(($label . ':pesi non possono essere negativi'));
            }

            if ($tariff < 0) {
                throw new InvalidArgumentException(($label . ':la tariffa non può essere negativa'));
            }

            if ($weightFrom >= $weightTo) {
                throw new InvalidArgumentException(
                    $label . ': "weight_from" deve essere minore di "weight_to".'
                );
            }

            $ranges[] = ['from' => $weightFrom, 'to' => $weightTo, 'label' => $label];
        }

        // controllo sovrapposizioni
        $n = count($ranges);

        for ($a = 0; $a < $n; $a++) {
            for ($b = $a + 1; $b < $n; $b++) {
                if (
                    $ranges[$a]['from'] < $ranges[$b]['to']
                    && $ranges[$b]['from'] < $ranges[$a]['to']
                ) {
                    throw new InvalidArgumentException(
                        'Sovrapposizione tra ' . $ranges[$a]['label']
                        . ' e ' . $ranges[$b]['label'] . '.'
                    );
                }
            }
        }
    }
}
