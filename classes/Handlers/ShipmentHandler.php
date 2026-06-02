<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * ShipmentHandler
 *
 * Responsabilità: SOLO azioni POST.
 * Riceve id_shipment, valida, aggiorna DB, logga, redirecta.
 */
class ShipmentHandler
{
    private string $moduleAdminLink;
    private ShipmentServices $shipmentService;
    private ShipmentRepository $shipmentRepo;
    private ShipmentRenderer $shipmentRenderer;
    private PackageRepository $packageRepo;



    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(
        string $moduleAdminLink,
        ShipmentServices $shipmentService,
        ShipmentRepository $shipmentRepo,
        ShipmentRenderer $shipmentRenderer,
        PackageRepository $packageRepo
    ) {
        $this->moduleAdminLink = $moduleAdminLink;
        $this->shipmentService = $shipmentService;
        $this->shipmentRepo = $shipmentRepo;
        $this->shipmentRenderer = $shipmentRenderer;
        $this->packageRepo = $packageRepo;
    }

    //=================================================
    //ENTRY POINT - INIZIO
    //=================================================
    public function handleRequest(): void
    {
        if (!$this->isPost()) {
            return;
        }

        if (Tools::isSubmit('createShipment')) {
            $this->handleCreateShipment();
            return;
        }

        if (Tools::isSubmit('cancelShipment')) {
            $this->handleCancelShipment();
            return;
        }
    }
    //=================================================
    //ENTRY POINT - fine
    //=================================================




    // ─────────────────────────────────────────────────────────────────────────
    // AZIONI
    // ─────────────────────────────────────────────────────────────────────────


    public function handleShipmentReview(): string
    {
        $idShipment = (int) Tools::getValue('id_shipment', 0);

        if ($idShipment <= 0) {
            $this->redirectWithError('ID Spedizione non valido');
        }

        $result = $this->shipmentRenderer->renderShipmentDetail($idShipment);

        if ($result === false) {
            $this->redirectWithError('Spedizione #' . $idShipment . ' non è stata trovata!');
        };

        return $result;
    }

    //=================================================
    //HANDLE PER CREAZIOEN SPEDIZIONE TRAMITE API - INIZIO
    //=================================================
    private function handleCreateShipment(): void
    {

    PrestaShopLogger::addLog('Sono entrato in create shipment');
        // recupero id sgipment
        $idShipment = (int) Tools::getValue('id_shipment');

        // controllo
        if ($idShipment <= 0) {
            $this->redirectWithError('ID spedizione non valido.');
            return;
        }

        // recupero shipment
        $shipment = $this->shipmentRepo->getShipmentById($idShipment);

        // controllo
        if (empty($shipment)) {
            $this->redirectWithError('Spedizione #' . $idShipment . ' non trovata.');
            return;
        }

        // controllo status
        if ($shipment['status'] !== 'pending') {
            $this->redirectWithError(
                'Spedizione #' . $idShipment . ' non è in stato pending (stato attuale: ' . $shipment['status'] . ').'
            );
            return;
        }

        try {

            // Carico Ordine
            $order = new Order((int) $shipment['id_order']);

            if (!Validate::isLoadedObject($order)) {
                throw new \RuntimeException('Ordine #' . $shipment['id_order'] . 'non trovato');
            }

            // carico parcel data
            $parcelData = $this->packageRepo->getParcelData($order);

            // costruzione payload
            $payload = $this->shipmentService->buildShipmentPayload($order,$parcelData);

            PrestaShopLogger::addLog(
                'Payload alla psedizione: ' . print_r($payload,true)
            );

            $updated = $this->shipmentRepo->updateShipmentStatus(
                $idShipment,
                'label_created',
                [
                    // 'tracking_number' => $trackingNumber,  // da API corriere
                    // 'tracking_url'    => $trackingUrl,
                    // 'api_shipment_id' => $apiResult['id'],
                ]
            );

            if (!$updated) {
                $this->redirectWithError('Errore aggiornamento spedizione #' . $idShipment . '.');
                return;
            }

            PrestaShopLogger::addLog(
                sprintf(
                    '[SpedisciQui] Shipment #%d | pending → label_created | Order #%d',
                    $idShipment,
                    (int) $shipment['id_order']
                ),
                1,
                null,
                'Order',
                (int) $shipment['id_order'],
                true
            );

            $this->redirectWithSuccess('Spedizione #' . $idShipment . ' creata con successo.');
        } catch (Exception $e) {
            $this->redirectWithError('Errore aggiornamento spedizione #' . $idShipment . '.');
            return;
        }

        // ── Placeholder: qui chiamerai le API del corriere ──────────────────
        // $apiResult = $this->carrierApi->createShipment($shipment);
        // $trackingNumber = $apiResult['tracking_number'];
        // ────────────────────────────────────────────────────────────────────


    }
    //=================================================
    //HANDLE PER CREAZIOEN SPEDIZIONE TRAMITE API - fine
    //=================================================





    //=============================================================
    // Annulla spedizione: qualsiasi stato → cancelled -INIZIO
    //=============================================================
    private function handleCancelShipment(): void
    {
        $idShipment = (int) Tools::getValue('id_shipment');

        if ($idShipment <= 0) {
            $this->redirectWithError('ID spedizione non valido.');
            return;
        }

        $shipment = $this->shipmentRepo->getShipmentById($idShipment);

        if (empty($shipment)) {
            $this->redirectWithError('Spedizione #' . $idShipment . ' non trovata.');
            return;
        }

        // Non si può annullare una spedizione già consegnata
        if (in_array($shipment['status'], ['delivered', 'cancelled'], true)) {
            $this->redirectWithError(
                'Impossibile annullare spedizione in stato: ' . $shipment['status']
            );
            return;
        }

        $updated = $this->shipmentRepo->updateShipmentStatus($idShipment, 'cancelled');

        if (!$updated) {
            $this->redirectWithError('Errore annullamento spedizione #' . $idShipment . '.');
            return;
        }

        PrestaShopLogger::addLog(
            sprintf(
                '[SpedisciQui] Shipment #%d annullato | Order #%d',
                $idShipment,
                (int) $shipment['id_order']
            ),
            2,
            null,
            'Order',
            (int) $shipment['id_order'],
            true
        );

        $this->redirectWithSuccess('Spedizione #' . $idShipment . ' annullata.');
    }

    //=============================================================
    // Annulla spedizione: qualsiasi stato → cancelled -FINE
    //=============================================================




    // ─────────────────────────────────────────────────────────────────────────
    // REDIRECT HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    private function redirectWithSuccess(string $message): void
    {
        Tools::redirectAdmin(
            $this->moduleAdminLink . '&conf=' . urlencode($message)
        );
    }

    private function redirectWithError(string $message): void
    {
        Tools::redirectAdmin(
            $this->moduleAdminLink . '&error=' . urlencode($message)
        );
    }

    private function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }
}
