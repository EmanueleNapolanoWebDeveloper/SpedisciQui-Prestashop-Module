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
    private ShippingServices $shippingService;

    public function __construct(
        string $moduleAdminLink,
        ShippingServices $shippingService
    ) {
        // Link alla dashboard admin del modulo per il redirect finale
        $this->moduleAdminLink = $moduleAdminLink;
        $this->shippingService = $shippingService;
    }

    /**
     * Entry point — smista le azioni POST in arrivo.
     * Da chiamare nel controller admin del modulo.
     */
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

    // ─────────────────────────────────────────────────────────────────────────
    // AZIONI
    // ─────────────────────────────────────────────────────────────────────────

    /**
     * Crea spedizione: pending → label_created
     */
    private function handleCreateShipment(): void
    {
        $idShipment = (int) Tools::getValue('id_shipment');

        if ($idShipment <= 0) {
            $this->redirectWithError('ID spedizione non valido.');
            return;
        }

        $shipment = $this->shippingService->getShipmentById($idShipment);

        if (empty($shipment)) {
            $this->redirectWithError('Spedizione #' . $idShipment . ' non trovata.');
            return;
        }

        if ($shipment['status'] !== 'pending') {
            $this->redirectWithError(
                'Spedizione #' . $idShipment . ' non è in stato pending (stato attuale: ' . $shipment['status'] . ').'
            );
            return;
        }

        // ── Placeholder: qui chiamerai le API del corriere ──────────────────
        // $apiResult = $this->carrierApi->createShipment($shipment);
        // $trackingNumber = $apiResult['tracking_number'];
        // ────────────────────────────────────────────────────────────────────

        $updated = $this->shippingService->updateShipmentStatus(
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
    }

    //=============================================================
    // Annulla spedizione: qualsiasi stato → cancelled
    //=============================================================
    private function handleCancelShipment(): void
    {
        $idShipment = (int) Tools::getValue('id_shipment');

        if ($idShipment <= 0) {
            $this->redirectWithError('ID spedizione non valido.');
            return;
        }

        $shipment = $this->shippingService->getShipmentById($idShipment);

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

        $updated = $this->shippingService->updateShipmentStatus($idShipment, 'cancelled');

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
