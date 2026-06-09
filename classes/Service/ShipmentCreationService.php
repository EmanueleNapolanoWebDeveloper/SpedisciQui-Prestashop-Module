<?php

use SpedisciQui\DTO\ApiResponse;
use SpedisciQui\DTO\ShipmentCreationResult;

class ShipmentCreationService
{

    private const ENDPOINT_CREATE = '/api/v1/create_shipment';
    private const STATUS_PENDING       = 'pending';
    private const STATUS_LABEL_CREATED = 'label_created';
    private const STATUS_FAILED        = 'failed';


    private ShipmentRepository    $shipmentRepo;
    private PackageServices     $packageService;
    private ApiClient             $apiClient;
    private CredentialsRepositories $credentialRepo;
    private SenderRepository $senderRepo;
    private LabelService $labelService;

    public function __construct(
        ShipmentRepository    $shipmentRepo,
        PackageServices     $packageService,
        ApiClient             $apiClient,
        CredentialsRepositories $credentialRepo,
        SenderRepository        $senderRepo,
        LabelService            $labelService
    ) {
        $this->shipmentRepo = $shipmentRepo;
        $this->packageService = $packageService;
        $this->apiClient = $apiClient;
        $this->credentialRepo = $credentialRepo;
        $this->senderRepo = $senderRepo;
        $this->labelService   = $labelService;
    }

    //------------------------------------------------
    // PAYLOAD PER API CONFERMA SHIPPING
    //------------------------------------------------

    public function buildShipmentPayload(
        \Order $order,
        array $parcelData,
        array $extraOptions = []
    ): array {
        try {

            // indirizzo destinatario
            $deliveryAddress = new \Address((int)$order->id_address_delivery);

            if (!Validate::isLoadedObject($deliveryAddress)) {
                throw new InvalidArgumentException(('Indirizzo di spedizione dell\' ordine non valido!'));
            }

            $customer = new Customer((int) $order->id_customer);
            $country = new Country((int)$deliveryAddress->id_country);
            $country_iso = $country->iso_code ? strtoupper($country->iso_code) : 'IT';

            // destinatario
            $recipientName = trim($deliveryAddress->firstname . ' ' . $deliveryAddress->lastname);
            $recipientPhone = !empty($deliveryAddress->phone_mobile) ? $deliveryAddress->phone_mobile : $deliveryAddress->phone;

            if (empty($recipientName)) {
                throw new \InvalidArgumentException('Il nome del destinatario è vuoto o non valido.');
            }
            if (empty($deliveryAddress->address1)) {
                throw new \InvalidArgumentException('L\'indirizzo (Via/Piazza) del destinatario è obbligatorio.');
            }
            if (empty($deliveryAddress->city)) {
                throw new \InvalidArgumentException('La città del destinatario è mancante.');
            }
            if (empty($deliveryAddress->postcode)) {
                throw new \InvalidArgumentException('Il CAP del destinatario è obbligatorio per la spedizione.');
            }
            if (empty($recipientPhone)) {
                throw new \InvalidArgumentException('Il numero di telefono del destinatario è obbligatorio per i corrieri.');
            }

            // recupero mittente
            $sender = $this->senderRepo->getDefault();

            if (!$sender || empty($sender)) {
                throw new \InvalidArgumentException('Nessun indirizzo mittente trovato nella tabella spedisciqui_sender_address. Configuralo nel modulo.');
            }

            if (empty($sender['firstname']) || empty($sender['lastname']) || empty($sender['address1']) || empty($sender['city']) || empty($sender['postcode'])) {
                throw new \InvalidArgumentException('I dati del mittente estratti dal database sono incompleti (Nome, Indirizzo, Città o CAP mancanti).');
            }

            // DATI PACCO
            $weight = (float) ($parcelData['weights'][0] ?? 0);
            $width  = (float) ($parcelData['widths'][0] ?? 0);
            $length = (float) ($parcelData['lengths'][0] ?? 0);
            $height = (float) ($parcelData['heights'][0] ?? 0);

            if ($weight <= 0) {
                throw new \InvalidArgumentException(sprintf('Il peso del pacco deve essere maggiore di zero. Rilevato: %s kg', $weight));
            }
            if ($width <= 0 || $length <= 0 || $height <= 0) {
                throw new \InvalidArgumentException('Le dimensioni del pacco (larghezza, lunghezza, profondità) devono essere maggiori di zero.');
            }

            // ASSICURAZIONE
            $insuranceEnabled = !empty($extraOptions['insurance_enabled']);
            $insuranceValue   = $insuranceEnabled ? round((float)($extraOptions['insurance_value'] ?? 0), 2) : 0.0;

            // contrassegno
            $isCod = ($order->module === 'ps_cashondelivery' || !empty($extraOptions['cod_enabled']));
            $codValue = $isCod ? round((float)$order->total_paid_tax_incl, 2) : 0.0;

            // costruzione payload
            return [
                'sender' => [
                    'name'      => substr((string)$sender['firstname'], 0, 64),
                    'surname'      => substr((string)$sender['lastname'], 0, 64),
                    'address'   => substr((string)$sender['address1'], 0, 100),
                    'city'      => (string)$sender['city'],
                    'postcode'  => (string)$sender['postcode'],
                    'country'   => !empty($sender['country_iso']) ? strtoupper((string)$sender['country_iso']) : 'IT',
                ],

                'recipient' => [
                    'name'      => substr($recipientName, 0, 64),
                    'address'   => substr($deliveryAddress->address1 . ' ' . $deliveryAddress->address2, 0, 100),
                    'city'      => $deliveryAddress->city,
                    'postcode'  => $deliveryAddress->postcode,
                    'country'   => $country_iso,
                    'phone'     => preg_replace('/[^0-9+]/', '', $recipientPhone), // Mantiene solo numeri e l'eventuale prefisso '+'
                ],

                'parcel' => [
                    'width'  => $width,
                    'length' => $length,
                    'height'  => $height,
                    'weight' => $weight,
                ],

                'insurance' => [
                    'enabled' => $insuranceEnabled,
                    'value'   => $insuranceValue,
                ],

                'cod' => [
                    'enabled' => $isCod,
                    'value'   => $codValue,
                ]
            ];
        } catch (\InvalidArgumentException $e) {
            // Intercettiamo gli errori di validazione (campi vuoti, incongruenze nei dati inseriti)
            \PrestaShopLogger::addLog(
                '[SpedisciQui] Errore validazione dati per spedizione Ordine #' . (int)$order->id . ': ' . $e->getMessage(),
                2, // Severity 2 = Warning (Significa che il codice funziona, ma i dati utente sono errati)
                null,
                'Order',
                (int)$order->id,
                true
            );
            // Rilanciamo l'eccezione in modo che il controller possa catturarla e mostrare un messaggio d'errore all'utente
            throw $e;
        } catch (\Throwable $e) {
            // Intercettiamo errori gravi di sistema (es: errori di sintassi SQL nel Repository, database down, ecc.)
            \PrestaShopLogger::addLog(
                '[SpedisciQui] Eccezione critica durante la generazione del payload dell\'Ordine #' . (int)$order->id .
                    ' - Errore: ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine(),
                3, // Severity 3 = Error
                null,
                'Order',
                (int)$order->id,
                true
            );
            // Mascheriamo l'errore tecnico per non mostrare dettagli del database all'utente finale
            throw new \Exception('Si è verificato un errore tecnico interno durante la preparazione dei dati di spedizione.');
        }
    }


    //==================================================
    //CREAZIONE SHIPMENT
    //==================================================
    public function createShipment(
        int $idShipment,
        int $insuranceEnabled,
        float $insuranceValue
    ) {

        // controllo
        if ($idShipment <= 0) {
            return ShipmentCreationResult::failure(
                'ID spedizione non valido.'
            );
        }

        // recupero shipment
        $shipment = $this->shipmentRepo->getShipmentById($idShipment);

        // controllo
        if (empty($shipment)) {
            return ShipmentCreationResult::failure(
                "Spedizione #{$idShipment} non trovata."
            );
        }

        // controllo status
        if ($shipment['status'] !== self::STATUS_PENDING) {
            return ShipmentCreationResult::failure(
                sprintf(
                    'Spedizione #%d non è in stato pending (stato attuale: %s).',
                    $idShipment,
                    $shipment['status']
                )
            );
        }

        // Carico Ordine
        $order = new Order((int) $shipment['id_order']);

        if (!Validate::isLoadedObject($order)) {
            throw new \RuntimeException('Ordine #' . $shipment['id_order'] . 'non trovato');
        }

        // validazione assicurazione
        if ($insuranceEnabled) {

            $orderTotal = (float) $order->total_paid;

            if ($insuranceValue <= 0) {
                return ShipmentCreationResult::failure(
                    'Il Valore deve essere maggiore di 0.'
                );
            }

            if ($insuranceValue > $orderTotal) {
                return ShipmentCreationResult::failure(
                    sprintf(
                        'Il valore assicurato (%.2f€) supera il totale dell\'ordine (%.2f€).',
                        $insuranceValue,
                        $orderTotal
                    )
                );
            }

            $insuranceValue = round($insuranceValue, 2);
        } else {
            $insuranceValue = 0.0;
        }

        // carico parcel data
        $parcelData = $this->packageService->getParcelData($order);

        // costruzione payload
        $payload = $this->buildShipmentPayload(
            $order,
            $parcelData,
            [
                'insurance_enabled' => $insuranceEnabled,
                'insurance_value'   => $insuranceValue,
            ]
        );

        // recuper token per requesrt
        $token = (string) $this->credentialRepo->get()['access_token'];

        // maschera token per sicurezza (opzionale ma consigliato)
        $maskedToken = substr($token, 0, 6) . '***' . substr($token, -4);


        PrestaShopLogger::addLog(
            sprintf(
                "[SpedisciQui] Payload spedizione #%d:\n%s",
                $idShipment,
                json_encode(
                    $payload,
                    JSON_PRETTY_PRINT |
                        JSON_UNESCAPED_UNICODE |
                        JSON_UNESCAPED_SLASHES
                )
            )
        );

        $apiResponse = $this->apiClient->request(
            'POST',
            self::ENDPOINT_CREATE,
            $token,
            $payload,
        );

        if ($apiResponse === null) {
            PrestaShopLogger::addLog(
                sprintf('[SpedisciQui] ApiClient ha restituito null per spedizione #%d', $idShipment),
                3,
                null,
                'Order',
                (int) $shipment['id_order'],
                true
            );
            return ShipmentCreationResult::failure(
                "Errore di comunicazione con l'API per spedizione #{$idShipment}."
            );
        }

        if (!$apiResponse->isSuccess()) {
            PrestaShopLogger::addLog('nessun successo dell api response');
            return $this->handleApiFailure($apiResponse, $idShipment, $shipment);
        }

        PrestaShopLogger::addLog('avvenuto successo dell api response');


        // estrai dati da risposta
        $responseData = $apiResponse->getData();

        $data = $responseData['data'] ?? [];

        // estrazione tracking
        $trackingNumber = (string) ($data['tracking_number'] ?? '');
        $remoteShipmentId = (string) ($data['shipment_id'] ?? '');
        $labelBase64      = (string) ($data['label_pdf'] ?? '');  // ← aggiunto

        // Salva label — non bloccante, fuori transazione
        $savedPdf = null;
        if (!empty($labelBase64)) {
            $savedPdf = $this->labelService->saveLabelPdf(
                $labelBase64,
                $trackingNumber,
                (int) $shipment['id_order']
            );
        }


        // transazioen dati
        return $this->persistSuccess(
            $idShipment,
            $shipment,
            $insuranceEnabled,
            $insuranceValue,
            $trackingNumber,
            $remoteShipmentId,
            $savedPdf
        );
    }



    // ================================
    // HELPERS
    // ================================

    //=============================================================
    // Persiste il risultato positivo in una transazione DB.
    // Se qualcosa fallisce → rollback, nessuna modifica locale.
    //??==========================================================
    private function persistSuccess(
        int    $idShipment,
        array  $shipment,
        int $insuranceEnabled,
        float $insuranceValue,
        string $trackingNumber,
        string $remoteShipmentId,
        ?string $pdfPath
    ): ShipmentCreationResult {


        $db = Db::getInstance();
        $db->execute('START TRANSACTION');

        try {

            $trackingUpdated = $this->shipmentRepo->updateTracking(
                $idShipment,
                $trackingNumber,
                $pdfPath
            );

            if (!$trackingUpdated) {
                throw new RuntimeException("updateTracking fallito per spedizione #{$idShipment}.");
            }

            PrestaShopLogger::addLog('updateTracking OK');

            $updateInsurance = $this->shipmentRepo->updateInsurance(
                $idShipment,
                $insuranceEnabled,
                $insuranceValue
            );

            if (!$updateInsurance) {
                throw new RuntimeException("updateInsurance fallito per spedizione #{$idShipment}.");
            }

            PrestaShopLogger::addLog('updateInsurance OK');

            // aggiorna remote_shipment_id separatamente
            $statusUpdated = $this->shipmentRepo->updateStatus(
                $idShipment,
                self::STATUS_LABEL_CREATED,
            );

            if (!$statusUpdated) {
                throw new RuntimeException("updateStatus (remote_shipment_id) fallito per spedizione #{$idShipment}.");
            }

            PrestaShopLogger::addLog('Successo updated');

            $db->execute('COMMIT');

            PrestaShopLogger::addLog(
                sprintf(
                    '[SpedisciQui] Shipment #%d | %s → %s | Order #%d | Tracking: %s',
                    $idShipment,
                    self::STATUS_PENDING,
                    self::STATUS_LABEL_CREATED,
                    (int) $shipment['id_order'],
                    $trackingNumber
                ),
                1,
                null,
                'Order',
                (int) $shipment['id_order'],
                true
            );

            return ShipmentCreationResult::success($trackingNumber, $remoteShipmentId);
        } catch (RuntimeException $e) {
            $db->execute('ROLLBACK');

            PrestaShopLogger::addLog(
                '[SpedisciQui] ROLLBACK spedizione #' . $idShipment . ': ' . $e->getMessage(),
                3,
                null,
                'Order',
                (int) $shipment['id_order'],
                true
            );

            return ShipmentCreationResult::failure($e->getMessage());
        }
    }




    //===================================================================
    // Gestisce la risposta API negativa: log, eventuale pulizia token, stato fallito.
    //====================================================================
    private function handleApiFailure(
        ApiResponse $apiResponse,
        int         $idShipment,
        array       $shipment
    ): ShipmentCreationResult {
        $errorMsg = sprintf(
            '[SpedisciQui] API error per spedizione #%d | type: %s | HTTP %d | %s',
            $idShipment,
            $apiResponse->getErrorType(),
            $apiResponse->getStatusCode(),
            $apiResponse->getErrorMessage()
        );

        PrestaShopLogger::addLog(
            $errorMsg,
            3,
            null,
            'Order',
            (int) $shipment['id_order'],
            true
        );


        // Aggiorniamo lo stato a 'failed' per tracciabilità (best-effort, fuori transazione)
        $this->shipmentRepo->updateStatus($idShipment, self::STATUS_FAILED, [
            'error_message' => $apiResponse->getErrorMessage(),
        ]);

        return ShipmentCreationResult::failure(
            "Errore API durante creazione spedizione #{$idShipment}: " . $apiResponse->getErrorMessage()
        );
    }
}
