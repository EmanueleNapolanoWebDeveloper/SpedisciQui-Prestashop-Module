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
    private ShipmentRepository $shipmentRepo;
    private ShipmentCreationService $shipCreationService;
    private ShipmentRenderer $shipmentRenderer;
    private PackageServices $packageService;
    private ApiClient $apiClient;



    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(
        string $moduleAdminLink,
        ShipmentCreationService $shipCreationService,
        ShipmentRepository $shipmentRepo,
        ShipmentRenderer $shipmentRenderer,
        PackageServices $packageService,
        ApiClient $apiClient,
    ) {
        $this->moduleAdminLink = $moduleAdminLink;
        $this->shipmentRepo = $shipmentRepo;
        $this->shipmentRenderer = $shipmentRenderer;
        $this->packageService = $packageService;
        $this->apiClient = $apiClient;
        $this->shipCreationService = $shipCreationService;
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
        // recupero id sgipment
        $idShipment = (int) Tools::getValue('id_shipment');

        if ($idShipment <= 0) {
            $this->redirectWithError('ID spedizione non valido.');
            return;
        }


        $result = $this->shipCreationService->createShipment(($idShipment));

        if (!$result->isSuccess()) {
            $this->redirectWithError($result->getErrorMessage());
            return;
        }

        $this->redirectWithSuccess(
            sprintf(
                'Spedizione creata. Tracking: ',
                $idShipment,
                $result->getTrackingNumber()
            )
        );
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

        $updated = $this->shipmentRepo->updateStatus($idShipment, 'cancelled');

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


    private function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    // ─────────────────────────────────────────────────────────────────────────
    // REDIRECT HELPERS
    // ─────────────────────────────────────────────────────────────────────────

    public function redirectWithSuccess(string $message): void
    {
        Tools::redirectAdmin(
            $this->moduleAdminLink . '&conf=' . urlencode($message)
        );
    }

    public function redirectWithError(string $message): void
    {
        Tools::redirectAdmin(
            $this->moduleAdminLink . '&error=' . urlencode($message)
        );
    }
}
