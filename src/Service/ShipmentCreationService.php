<?php

use SpedisciQui\DTO\ApiResponse;
use SpedisciQui\DTO\ShipmentCreationResult;

class ShipmentCreationService
{
    private const ENDPOINT_CREATE = '/api/v1/create_shipment';
    private const ENDPOINT_GET_LABEL = '/v1/shipment/%s/label';
    
    public const STATUS_PENDING = 'pending';
    public const STATUS_REQUEST_SENT = 'request_send';
    public const STATUS_LABEL_CREATED = 'label_created';
    public const STATUS_FAILED = 'failed';

    private ShipmentRepository $shipmentRepo;
    private PackageServices $packageService;
    private ApiClient $apiClient;
    private CredentialsRepositories $credentialRepo;
    private SenderRepository $senderRepo;
    private LabelService $labelService;

    public function __construct(
        ShipmentRepository $shipmentRepo,
        PackageServices $packageService,
        ApiClient $apiClient,
        CredentialsRepositories $credentialRepo,
        SenderRepository $senderRepo,
        LabelService $labelService
    ) {
        $this->shipmentRepo = $shipmentRepo;
        $this->packageService = $packageService;
        $this->apiClient = $apiClient;
        $this->credentialRepo = $credentialRepo;
        $this->senderRepo = $senderRepo;
        $this->labelService = $labelService;
    }

    //==================================================
    // GENERAZIONE PAYLOAD DI SPEDIZIONE
    //==================================================
    public function buildShipmentPayload(
        \Order $order,
        array $parcelData,
        string $carrierCode,
        array $extraOptions = []
    ): array {
        try {
            $deliveryAddress = new \Address((int) $order->id_address_delivery);
            if (!Validate::isLoadedObject($deliveryAddress)) {
                throw new InvalidArgumentException('Indirizzo di spedizione dell\'ordine non valido!');
            }

            $country = new Country((int) $deliveryAddress->id_country);
            $country_iso = $country->iso_code ? strtoupper($country->iso_code) : 'IT';

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
                throw new \InvalidArgumentException('Il CAP del destinatario è obbligatorio.');
            }
            if (empty($recipientPhone)) {
                throw new \InvalidArgumentException('Il numero di telefono del destinatario è obbligatorio.');
            }

            $sender = $this->senderRepo->getDefault();
            if (!$sender) {
                throw new \InvalidArgumentException('Nessun indirizzo mittente trovato. Configuralo nel modulo.');
            }

            if (empty($sender['firstname']) || empty($sender['lastname']) || empty($sender['address1']) || empty($sender['city']) || empty($sender['postcode'])) {
                throw new \InvalidArgumentException('I dati del mittente estratti dal database sono incompleti.');
            }

            $weight = (float) ($parcelData['weights'][0] ?? 0);
            $width  = (float) ($parcelData['widths'][0] ?? 0);
            $length = (float) ($parcelData['lengths'][0] ?? 0);
            $height = (float) ($parcelData['heights'][0] ?? 0);

            if ($weight <= 0 || $width <= 0 || $length <= 0 || $height <= 0) {
                throw new \InvalidArgumentException('Dimensioni e peso del pacco devono essere maggiori di zero.');
            }

            $insuranceEnabled = !empty($extraOptions['insurance_enabled']);
            $insuranceValue   = $insuranceEnabled ? round((float) ($extraOptions['insurance_value'] ?? 0), 2) : 0.0;

            $isCod    = ($order->module === 'ps_cashondelivery' || !empty($extraOptions['cod_enabled']));
            $codValue = $isCod ? round((float) $order->total_paid_tax_incl, 2) : 0.0;

            return [
                'carrier_code' => $carrierCode,
                'order_reference' => $order->reference,
                'sender' => [
                    'name' => substr((string) $sender['firstname'], 0, 64),
                    'surname' => substr((string) $sender['lastname'], 0, 64),
                    'address' => substr((string) $sender['address1'], 0, 100),
                    'city' => (string) $sender['city'],
                    'postcode' => (string) $sender['postcode'],
                    'country' => !empty($sender['country_iso']) ? strtoupper((string) $sender['country_iso']) : 'IT',
                ],
                'recipient' => [
                    'name' => substr($recipientName, 0, 64),
                    'address' => substr($deliveryAddress->address1 . ' ' . $deliveryAddress->address2, 0, 100),
                    'city' => $deliveryAddress->city,
                    'postcode' => $deliveryAddress->postcode,
                    'country' => $country_iso,
                    'phone' => preg_replace('/[^0-9+]/', '', $recipientPhone),
                ],
                'parcel' => [
                    'width' => $width,
                    'length' => $length,
                    'height' => $height,
                    'weight' => $weight,
                ],
                'insurance' => [
                    'enabled' => $insuranceEnabled,
                    'value' => $insuranceValue,
                ],
                'cod' => [
                    'enabled' => $isCod,
                    'value' => $codValue,
                ]
            ];
        } catch (\InvalidArgumentException $e) {
            \PrestaShopLogger::addLog('[SpedisciQui] Validazione fallita Ordine #' . (int)$order->id . ': ' . $e->getMessage(), 2, null, 'Order', (int)$order->id, true);
            throw $e;
        } catch (\Throwable $e) {
            \PrestaShopLogger::addLog('[SpedisciQui] Errore critico payload Ordine #' . (int)$order->id . ': ' . $e->getMessage(), 3, null, 'Order', (int)$order->id, true);
            throw new \Exception('Errore tecnico interno durante la preparazione dei dati di spedizione.');
        }
    }

    //==================================================
    // STEP 1: INVIO RICHIESTA CREAZIONE SPEDIZIONE
    //==================================================
    public function sendShipmentRequest(int $idShipment, bool $insuranceEnabled, float $insuranceValue): ShipmentCreationResult
    {
        if ($idShipment <= 0) {
            return ShipmentCreationResult::failure('ID spedizione non valido.');
        }

        $shipment = $this->shipmentRepo->getShipmentById($idShipment);
        if (!$shipment) {
            return ShipmentCreationResult::failure('Spedizione non trovata.');
        }

        if ($shipment['status'] !== self::STATUS_PENDING) {
            return ShipmentCreationResult::failure(sprintf('Stato non valido per l\'invio. Stato attuale: %s', $shipment['status']));
        }

        if (empty($shipment['carrier_code'])) {
            return ShipmentCreationResult::failure('Nessun corriere associato a questo ordine.');
        }

        $order = new Order((int) $shipment['id_order']);
        if (!Validate::isLoadedObject($order)) {
            return ShipmentCreationResult::failure('Ordine Prestashop non trovato.');
        }

        if ($insuranceEnabled) {
            $orderTotal = (float) $order->total_paid;
            if ($insuranceValue <= 0 || $insuranceValue > $orderTotal) {
                return ShipmentCreationResult::failure(sprintf('Valore assicurazione non valido. Deve essere tra 0 e %.2f.', $orderTotal));
            }
            $insuranceValue = round($insuranceValue, 2);
        } else {
            $insuranceValue = 0.0;
        }

        $parcelData = $this->packageService->getParcelData($order);
        if (empty($parcelData)) {
            return ShipmentCreationResult::failure('Impossibile recuperare i dati del collo.');
        }

        try {
            $payload = $this->buildShipmentPayload($order, $parcelData, $shipment['carrier_code'], [
                'insurance_enabled' => $insuranceEnabled,
                'insurance_value' => $insuranceValue,
            ]);
        } catch (\Exception $e) {
            return ShipmentCreationResult::failure($e->getMessage());
        }

        $tokenData = $this->credentialRepo->get();
        $token = (string) ($tokenData['access_token'] ?? '');

        $apiResponse = $this->apiClient->request('POST', self::ENDPOINT_CREATE, $token, $payload);

        if ($apiResponse === null || !$apiResponse->isSuccess()) {
            return $this->handleApiFailure($apiResponse, $idShipment, $shipment, 'Errore durante l\'invio della richiesta.');
        }

        $responseData = $apiResponse->getData();
        $remoteShipmentId = $responseData['shipment_id'] ?? null;

        if (empty($remoteShipmentId)) {
            return $this->handleApiFailure($apiResponse, $idShipment, $shipment, 'Risposta API priva di ID remoto.');
        }

        // Salva lo stato intermedio e aggancia l'ID remoto
        $updated = $this->shipmentRepo->updateStatus($idShipment, self::STATUS_REQUEST_SENT, [
            'remote_shipment_id' => $remoteShipmentId,
            'insurance_enabled'  => $insuranceEnabled ? 1 : 0,
            'insurance_value'    => $insuranceValue
        ]);

        if (!$updated) {
            return ShipmentCreationResult::failure('Richiesta inviata su API ma fallito aggiornamento stato locale.');
        }

        return ShipmentCreationResult::success([
            'id_shipment' => $idShipment,
            'remote_shipment_id' => $remoteShipmentId,
            'status' => self::STATUS_REQUEST_SENT,
        ]);
    }

    //==================================================
    // STEP 2: DOWNLOAD ETICHETTA E GENERAZIONE TRACKING
    //==================================================
    public function fetchShipmentDataAndLabel(int $idShipment): ShipmentCreationResult
    {
        if ($idShipment <= 0) {
            return ShipmentCreationResult::failure('ID Spedizione non valido.');
        }

        $shipment = $this->shipmentRepo->getShipmentById($idShipment);
        if (!$shipment) {
            return ShipmentCreationResult::failure('Spedizione locale non trovata.');
        }

        $remoteShipmentId = $shipment['remote_shipment_id'] ?? null;
        if (empty($remoteShipmentId)) {
            return ShipmentCreationResult::failure('ID spedizione remoto mancante. Impossibile richiedere la label.');
        }

        $credentials = $this->credentialRepo->get();
        $token = $credentials['access_token'] ?? '';

        $endpoint = sprintf(self::ENDPOINT_GET_LABEL, $remoteShipmentId);
        $apiResponse = $this->apiClient->get($endpoint, [], $token);

        if ($apiResponse === null || !$apiResponse->isSuccess()) {
            return $this->handleApiFailure($apiResponse, $idShipment, $shipment, 'Impossibile recuperare la vettura dal server.');
        }

        $responseData = $apiResponse->getData();
        $innerData = $responseData['data'] ?? [];

        $trackingNumber = (string) ($innerData['tracking_number'] ?? '');
        $labelBase64    = (string) ($innerData['label_pdf'] ?? '');

        if (empty($trackingNumber) || empty($labelBase64)) {
            return $this->handleApiFailure($apiResponse, $idShipment, $shipment, 'Risposta di download corrotta (Dati mancanti).');
        }

        $savedLabelPath = $this->labelService->saveLabelPdf($labelBase64, $trackingNumber, (int)$shipment['id_order']);
        if (!$savedLabelPath) {
            \PrestaShopLogger::addLog(sprintf('SpedisciQui: Impossibile salvare fisicamente il PDF dello shipment #%d', $idShipment), 3, null, 'Order', (int)$shipment['id_order'], true);
        }

        return $this->persistSuccess(
            $idShipment,
            $shipment,
            (int) ($shipment['insurance_enabled'] ?? 0),
            (float) ($shipment['insurance_value'] ?? 0.0),
            $trackingNumber,
            $remoteShipmentId,
            $savedLabelPath
        );
    }

    // =========================================================
    // PERSISTENZA TRANSAZIONALE (COMMIT/ROLLBACK)
    // =========================================================
    private function persistSuccess(
        int $idShipment,
        array $shipment,
        int $insuranceEnabled,
        float $insuranceValue,
        string $trackingNumber,
        string $remoteShipmentId,
        ?string $pdfPath
    ): ShipmentCreationResult {
        $db = Db::getInstance();
        $db->execute('START TRANSACTION');

        try {
            if (!$this->shipmentRepo->updateTracking($idShipment, $remoteShipmentId, $trackingNumber, $pdfPath)) {
                throw new RuntimeException("updateTracking fallito.");
            }

            if (!$this->shipmentRepo->updateInsurance($idShipment, $insuranceEnabled, $insuranceValue)) {
                throw new RuntimeException("updateInsurance fallito.");
            }

            if (!$this->shipmentRepo->updateStatus($idShipment, self::STATUS_LABEL_CREATED, [])) {
                throw new RuntimeException("updateStatus finale fallito.");
            }

            $db->execute('COMMIT');

            \PrestaShopLogger::addLog(sprintf('[SpedisciQui] Spedizione #%d completata con successo. Tracking: %s', $idShipment, $trackingNumber), 1, null, 'Order', (int)$shipment['id_order'], true);

            return ShipmentCreationResult::success([
                'id_shipment' => $idShipment,
                'remote_shipment_id' => $remoteShipmentId,
                'tracking_number' => $trackingNumber,
                'label_pdf_path' => $pdfPath, // Corretto: usava una variabile inesistente
                'status' => self::STATUS_LABEL_CREATED,
            ]);
        } catch (RuntimeException $e) {
            $db->execute('ROLLBACK');
            \PrestaShopLogger::addLog('[SpedisciQui] ROLLBACK spedizione #' . $idShipment . ': ' . $e->getMessage(), 3, null, 'Order', (int)$shipment['id_order'], true);
            return ShipmentCreationResult::failure($e->getMessage());
        }
    }

    // =========================================================
    // GESTORE FALLIMENTI API
    // =========================================================
    private function handleApiFailure(
        ?ApiResponse $apiResponse,
        int $idShipment,
        array $shipment,
        string $contextMessage
    ): ShipmentCreationResult {
        $apiError = $apiResponse !== null 
            ? sprintf('HTTP %d | %s', $apiResponse->getStatusCode(), $apiResponse->getErrorMessage())
            : 'Nessuna risposta dal server remoto.';

        $logMsg = sprintf('[SpedisciQui] Fallimento su Spedizione #%d: %s. Dettaglio: %s', $idShipment, $contextMessage, $apiError);
        \PrestaShopLogger::addLog($logMsg, 3, null, 'Order', (int)$shipment['id_order'], true);

        $this->shipmentRepo->updateStatus($idShipment, self::STATUS_FAILED, [
            'error_message' => $apiResponse ? $apiResponse->getErrorMessage() : 'Timeout API',
        ]);

        return ShipmentCreationResult::failure($contextMessage . ' ' . ($apiResponse ? $apiResponse->getErrorMessage() : ''));
    }
}