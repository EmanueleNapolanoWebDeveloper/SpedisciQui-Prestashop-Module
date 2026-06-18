<?php

class ShipmentServices
{
    private CarrierRepository $carrierRepo;
    private CarrierServices $carrierServices;
    private ShipmentRepository $shipmentRepo;
    private Context $context;
    private CredentialsRepositories $credentialRepo;
    private spedisciquishipping $module;



    /**
     * Impostato dal modulo principale prima di invocare getRateShippingCost().
     * Corrisponde all'id_carrier attivo nel contesto di getOrderShippingCost().
     *
     * @var int
     */
    public int $id_carrier = 0;

    public function __construct(
        CarrierRepository $carrierRepo,
        CarrierServices $carrierServices,
        ShipmentRepository $shipmentRepo,
        CredentialsRepositories $credentialRepo,
        Context $context,
        spedisciquishipping $module
    ) {
        $this->carrierRepo = $carrierRepo;
        $this->carrierServices = $carrierServices;
        $this->shipmentRepo = $shipmentRepo;
        $this->credentialRepo = $credentialRepo;
        $this->context = $context;
        $this->module = $module;
    }


    private const PAYMENT_STATUS_MAP = [
        'Payment accepted' => 'paid',
        'Awaiting bank wire payment' => 'pending',
        'Refunded' => 'refunded',
    ];
    private const INSURANCE_VALUE_MIN = 0.01;
    private const INSURANCE_VALUE_MAX = 99999.99;

    //=================================================
    // CLACOLA COSTO DI SPEDIZIONE TRAMITE PESO
    //=================================================

    public function getRateShippingCost(Cart $cart, int $idCarrier): float|false
    {
        try {
            // 1. Validazione ID Corriere di base
            if ($idCarrier <= 0) {
                PrestaShopLogger::addLog(
                    sprintf('[SpedisciQui] id_carrier non valido: %d | Cart #%d', $idCarrier, $cart->id),
                    3,
                    null,
                    'Cart',
                    (int) $cart->id,
                    true
                );
                return false;
            }

            // 2. Controllo Carrello e Peso (Protezione contro carrelli vuoti)
            if (!$cart->id || !Cart::getNbProducts($cart->id)) {
                return false; // Silenzioso, è normale durante l'inizializzazione del checkout
            }

            $totalWeight = (float) $cart->getTotalWeight();

            // 💡 NOTA DI DEBUG: Assicurati che getCarrierById interroghi il DB 
            // usando l'id_reference se l'id_carrier nativo di PS cambia ad ogni modifica.
            $carrier = $this->carrierRepo->getCarrierById($idCarrier);

            if (empty($carrier)) {
                PrestaShopLogger::addLog(
                    sprintf('[SpedisciQui] Carrier #%d non trovato nel mapping del modulo | Cart #%d', $idCarrier, $cart->id),
                    2,
                    null,
                    'Cart',
                    (int) $cart->id,
                    true // Abbassato a Warning (2) perché può succedere con i carrier nativi disattivati
                );
                return false;
            }

            // Recuperiamo i codici (Usa preferibilmente il codice univoco del corriere)
            $carrierCode = !empty($carrier['carrier_code']) ? (string) $carrier['carrier_code'] : (string) $carrier['service_code'];

            if (empty($carrierCode)) {
                PrestaShopLogger::addLog(
                    sprintf('[SpedisciQui] Codice identificativo mancante per Carrier #%d | Cart #%d', $idCarrier, $cart->id),
                    3,
                    null,
                    'Cart',
                    (int) $cart->id,
                    true
                );
                return false;
            }

            // 3. Calcolo Tariffa applicabile
            $tariff = $this->carrierServices->getApplicableTariff($carrierCode, $totalWeight);

            if ($tariff === false || !isset($tariff['tariff'])) {
                // Log informativo (Severity 1): utilissimo in debug per capire se mancano le fasce di peso nel DB
                PrestaShopLogger::addLog(
                    sprintf('[SpedisciQui] Nessuna fascia tariffaria trovata per Code: %s e Peso: %.3f kg | Cart #%d', $carrierCode, $totalWeight, $cart->id),
                    1,
                    null,
                    'Cart',
                    (int) $cart->id,
                    true
                );
                return false;
            }

            $shippingCost = (float) $tariff['tariff'];

            // 4. Controllo anomalie sul prezzo
            if ($shippingCost < 0.0) {
                PrestaShopLogger::addLog(
                    sprintf('[SpedisciQui] Tariffa negativa rilevata (%.4f) | Code: %s | Cart #%d', $shippingCost, $carrierCode, $cart->id),
                    3,
                    null,
                    'Cart',
                    (int) $cart->id,
                    true
                );
                return false;
            }

            // Ritorna il costo finale corretto
            return $shippingCost;

        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                sprintf(
                    '[SpedisciQui] Eccezione critica | Cart #%d | %s in %s:%d',
                    $cart->id,
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ),
                4,
                null,
                'Cart',
                (int) $cart->id,
                true
            );
            return false;
        }
    }


    // ==============================================
    // CALCOLO DIMENSIONI PACCO
    // ============================================
    public function calculatePackageDimensions(Cart $cart, array $defaultPackage): array
    {
        $products = $cart->getProducts();

        $totalLength = 0.0;
        $totalWidth = 0.0;
        $totalHeight = 0.0;

        if (empty($products)) {
            return [
                'length' => (float) ($defaultPackage['length'] ?? 0),
                'width' => (float) ($defaultPackage['width'] ?? 0),
                'height' => (float) ($defaultPackage['height'] ?? 0),
            ];
        }

        foreach ($products as $product) {
            $qty = (int) ($product['cart_quantity'] ?? 1);

            // PS salva le dimensioni in ps_product come width, height, depth
            // depth in PS = lunghezza fisica del pacco
            $pLength = (float) ($product['depth'] ?? 0); // PS chiama "depth" la lunghezza
            $pWidth = (float) ($product['width'] ?? 0);
            $pHeight = (float) ($product['height'] ?? 0);

            // Fallback al package default se il prodotto non ha dimensioni
            if ($pLength <= 0) {
                $pLength = (float) ($defaultPackage['length'] ?? 0);
            }
            if ($pWidth <= 0) {
                $pWidth = (float) ($defaultPackage['width'] ?? 0);
            }
            if ($pHeight <= 0) {
                $pHeight = (float) ($defaultPackage['height'] ?? 0);
            }

            // length/width: prendo il massimo (il pacco deve contenere il prodotto più grande)
            $totalLength = max($totalLength, $pLength);
            $totalWidth = max($totalWidth, $pWidth);

            // height: sommo per la quantità (prodotti impilati)
            $totalHeight += $pHeight * $qty;
        }

        return [
            'length' => round($totalLength, 2),
            'width' => round($totalWidth, 2),
            'height' => round($totalHeight, 2),
        ];
    }


    //==================================================
    //CONTO DEGLI SHIPMENTS
    //====================================================
    public function countShipments(int $idShop = 1, string $statusFilter = ''): int
    {
        $query = new DbQuery();
        $query->select('COUNT(*)');
        $query->from('spedisciqui_shipments', 'sh');
        $query->where('sh.`id_shop` = ' . (int) $idShop);

        if ($statusFilter !== '') {
            $query->where('sh.`status` = \'' . pSQL($statusFilter) . '\'');
        }

        return (int) Db::getInstance()->getValue($query);
    }


    //==================================================
    //FROMATTAZIOEN RIGA PER OUTPUT
    //====================================================
    public function formatRow(array $row): array
    {

        $labelUrl = '';
        if (!empty($row['label_path'])) {
            $labelUrl = str_replace(
                _PS_ROOT_DIR_,                    // /var/www/html/prestatest
                Context::getContext()->shop->getBaseURL(true), // https://tuodominio.com/
                $row['label_path']
            );
        }

        return [
            'id_shipment' => (int) $row['id_shipment'],
            'id_order' => (int) $row['id_order'],
            'tracking_number' => (string) ($row['tracking_number'] ?? '—'),
            'label_path' => $row['label_path'] ?? null,
            'label_url' => $this->buildLabelUrl($row['label_path'] ?? null),
            'carrier_code' => (string) ($row['carrier_code'] ?? '—'),
            'service_code' => (string) ($row['service_code'] ?? '—'),
            'status' => (string) $row['status'],
            'status_label' => $this->getStatusLabel((string) $row['status']),
            'status_class' => $this->getStatusClass((string) $row['status']),
            'payment_status' => $this->resolvePaymentStatus((string) ($row['order_state_name'] ?? '')),
            'payment_method' => (string) ($row['payment_method'] ?? ''),
            'total_paid' => number_format((float) $row['total_paid'], 2, ',', '.'),
            'currency' => (string) ($row['shipping_currency'] ?? 'EUR'),
            'customer_name' => (string) ($row['customer_name'] ?? '—'),
            'customer_email' => (string) ($row['customer_email'] ?? ''),
            'delivery_city' => (string) ($row['delivery_city'] ?? ''),
            'delivery_country' => (string) ($row['delivery_country_iso'] ?? ''),
            'weight' => number_format((float) ($row['weight'] ?? 0), 3, ',', '.'),
            'shipping_cost' => number_format((float) ($row['shipping_cost'] ?? 0), 2, ',', '.'),
            'date_add' => $row['date_add']
                ? date('d/m/Y H:i', strtotime($row['date_add']))
                : '—',
        ];
    }

    //==================================================
    //RECUPERA ETICHETTE
    //====================================================
    public function getStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'In attesa',
            'label_created' => 'Label creata',
            'picked_up' => 'Ritirato',
            'in_transit' => 'In transito',
            'out_for_delivery' => 'In consegna',
            'delivered' => 'Consegnato',
            'failed' => 'Fallito',
            'cancelled' => 'Annullato',
            'returned' => 'Reso',
            default => ucfirst($status),
        };
    }

    /**
     * Classe CSS Bootstrap per badge status.
     */
    public function getStatusClass(string $status): string
    {
        return match ($status) {
            'pending' => 'warning',
            'label_created' => 'info',
            'picked_up',
            'in_transit' => 'primary',
            'out_for_delivery' => 'primary',
            'delivered' => 'success',
            'failed',
            'cancelled' => 'danger',
            'returned' => 'secondary',
            default => 'secondary',
        };
    }

    //==================================================
    //RISOLVE STATO PAGAMENTO
    //==================================================
    public function resolvePaymentStatus(string $orderStateName): string
    {
        foreach (self::PAYMENT_STATUS_MAP as $keyword => $status) {
            if (stripos($orderStateName, $keyword) !== false) {
                return $status;
            }
        }
        return 'pending';
    }


    //==================================================
    // Costruisce il ViewModel completo per il template orders_detail.tpl
    //==================================================

    public function buildViewModel(int $shipmentId): ?array
    {
        // ─── SHIPMENT ────────────────────────────────────────────────────────────
        $shipment = $this->shipmentRepo->getShipmentById($shipmentId);

        if (!$shipment) {
            return null;
        }

        // ─── ORDER (PrestaShop nativo) ───────────────────────────────────────────
        $idOrder = (int) ($shipment['id_order'] ?? 0);
        $order = $idOrder ? new \Order($idOrder) : null;

        // Valuta collegata all'ordine
        $currency = ($order && \Validate::isLoadedObject($order))
            ? new \Currency((int) $order->id_currency)
            : null;

        // ─── ADDRESS / RECIPIENT ─────────────────────────────────────────────────
        $idAddress = ($order && \Validate::isLoadedObject($order))
            ? (int) $order->id_address_delivery
            : 0;

        $address = $idAddress ? new \Address($idAddress) : null;

        // Country label leggibile
        $countryName = ($address && \Validate::isLoadedObject($address))
            ? \Country::getNameById(
                $this->context->language->id,
                (int) $address->id_country
            )
            : '';

        // ─── CARRIER (repository custom del modulo) ───────────────────────────────
        // Assumiamo che il carrier sia identificato da carrier_code nella spedizione
        $carrierCode = $shipment['carrier_code'] ?? null;
        $carrier = $carrierCode
            ? $this->carrierRepo->getCarrierByCode($carrierCode)
            : null;


        // ─── FORM URLs ───────────────────────────────────────────────────────────
        $actionUrl = $this->buildAdminLink();

        $backUrl = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token=' . Tools::getAdminTokenLite('AdminModules')
            . '&action=list';

        // url per mostrare/scaericare label
        $labelUrl = $this->buildLabelUrl($shipment['label_path'] ?? null);


        $orderDetailUrl = ($idOrder && $order && \Validate::isLoadedObject($order))
            ? $this->context->link->getAdminLink(
                'AdminOrders',
                true,
                [],
                ['id_order' => $idOrder, 'vieworder' => 1]
            )
            : '#';

        // ─── ASSEMBLAGGIO ViewModel ───────────────────────────────────────────────
        return [

            // ── shipment ──────────────────────────────────────────────────────────
            'shipment' => [
                'id_shipment' => (int) ($shipment['id_shipment'] ?? $shipmentId),
                'status' => $shipment['status'] ?? 'unknown',
                'status_label' => $this->getStatusLabel($shipment['status'] ?? ''),
                'status_class' => $this->getStatusClass($shipment['status'] ?? ''),
                'weight' => (float) ($shipment['weight'] ?? 0),
                'base_cost' => $this->formatPrice(
                    (float) ($shipment['base_cost'] ?? 0),
                    $currency
                ),
                'shipping_cost' => (float) ($shipment['shipping_cost'] ?? 0),
                'tracking_number' => $shipment['tracking_number'] ?? '',
                'label_path' => $row['label_path'] ?? null,
                'label_url' => $labelUrl,
                'date_add' => $shipment['date_add'] ?? '',
                'note' => $shipment['note'] ?? '',  // opzionale
            ],

            // ── order ─────────────────────────────────────────────────────────────
            'order' => ($order && \Validate::isLoadedObject($order)) ? [
                'id_order' => (int) $order->id,
                'reference' => $order->reference ?? '',
                'date_add' => $order->date_add ?? '',
                'total_paid' => $this->formatPrice(
                    (float) $order->total_paid_tax_incl,
                    $currency
                ),
                'total_paid_raw' => round((float) $order->total_paid_tax_incl, 2), // ← aggiungi
                'currency' => $currency ? $currency->iso_code : '',
                'payment_method' => $order->payment ?? '',
                'payment_status' => $order->getCurrentOrderState()
                    ? $order->getCurrentOrderState()->name[$this->context->language->id] ?? ''
                    : '',
                'payment_label' => $this->getPaymentLabel($order),
            ] : $this->getEmptyOrder(),

            // ── recipient ─────────────────────────────────────────────────────────
            'recipient' => ($address && \Validate::isLoadedObject($address)) ? [
                'full_name' => trim(
                    ($address->firstname ?? '') . ' ' .
                    ($address->lastname ?? '')
                ),
                'company' => $address->company ?? '',   // opzionale
                'address1' => $address->address1 ?? '',
                'address2' => $address->address2 ?? '',   // opzionale
                'city' => $address->city ?? '',
                'postcode' => $address->postcode ?? '',
                'province' => $address->id_state
                    ? \State::getNameById((int) $address->id_state) ?? ''
                    : '',
                'country' => $countryName,
                'phone' => $address->phone ?? $address->phone_mobile ?? '',
            ] : $this->getEmptyRecipient(),

            // ── carrier ───────────────────────────────────────────────────────────
            'carrier' => [
                'carrier_code' => $shipment['carrier_code'] ?? '',
                'service_code' => $shipment['service_code'] ?? '',
                'service_name' => $carrier['service_name'] ?? '',   // opzionale
                'estimated_days' => $carrier['estimated_days'] ?? null, // opzionale
                'logo_url' => $carrier['logo_url'] ?? '',   // opzionale
            ],

            // ── options ───────────────────────────────────────────────────────────
            'options' => [
                'available' => [],
                'selected' => [],
            ],

            // ── form ──────────────────────────────────────────────────────────────
            'form' => [
                'action_url' => $actionUrl,
                'back_url' => $backUrl,
                'order_detail_url' => $orderDetailUrl,
                'id_shipment' => (int) ($shipment['id_shipment'] ?? $shipmentId),
                'base_cost_raw' => (float) ($shipment['base_cost'] ?? 0),
            ],
        ];
    }


    //==================================================
    //HELPERS
    //==================================================

    // HELPER FORMATTAZION EPREZZO
    private function formatPrice(float $amount, ?\Currency $currency): string
    {
        if (!$currency || !\Validate::isLoadedObject($currency)) {
            return number_format($amount, 2) . ' €';
        }
        return \Tools::displayPrice($amount, $currency);
    }

    private function getEmptyOrder(): array
    {
        return [
            'id_order' => 0,
            'reference' => '',
            'date_add' => '',
            'total_paid' => '',
            'currency' => '',
            'payment_method' => '',
            'payment_status' => '',
            'payment_label' => '',
            'total_paid_raw' => 0.0,
        ];
    }

    private function getEmptyRecipient(): array
    {
        return [
            'full_name' => '',
            'company' => '',
            'address1' => '',
            'address2' => '',
            'city' => '',
            'postcode' => '',
            'province' => '',
            'country' => '',
            'phone' => '',
        ];
    }

    private function getPaymentLabel(\Order $order): string
    {
        try {
            $state = $order->getCurrentOrderState();
            if ($state && isset($state->name[$this->context->language->id])) {
                return $state->name[$this->context->language->id];
            }
        } catch (\Exception $e) {
            // log se necessario
        }
        return '';
    }


    private function buildLabelUrl(?string $path): string
    {
        if (!$path) {
            return '';
        }

        return _PS_BASE_URL_ . __PS_BASE_URI__ . 'upload/spedisciqui/labels/' . basename($path);
    }

    private function buildAdminLink(): string
    {
        return AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token=' . Tools::getAdminTokenLite('AdminModules');
    }


    //========================================================
    // Valida e sanitizza il valore assicurato.
    //========================================================

    public function validateInsuranceValue(array $postData, int $orderId): array
    {
        // 1. Presenza del campo
        if (!isset($postData['insurance_value']) || $postData['insurance_value'] === '') {
            return ['value' => 0.0, 'error' => null]; // campo opzionale: 0 = nessuna assicurazione
        }

        // 2. Tipo numerico
        $raw = $postData['insurance_value'];
        if (!is_numeric($raw)) {
            return ['value' => 0.0, 'error' => $this->module->l('Il valore assicurato non è valido.')];
        }

        $value = (float) $raw;

        // 3. Minimo assoluto
        if ($value < self::INSURANCE_VALUE_MIN) {
            return ['value' => 0.0, 'error' => $this->module->l('Il valore assicurato deve essere almeno 0,01 €.')];
        }

        // 4. Massimo assoluto (guard di sicurezza indipendente dall'ordine)
        if ($value > self::INSURANCE_VALUE_MAX) {
            return [
                'value' => 0.0,
                'error' => $this->module->l('Il valore assicurato supera il massimo consentito.')
            ];
        }

        // 5. Massimo relativo all'ordine — recuperato fresh dal DB, MAI dal POST
        $order = new Order((int) $orderId);
        if (!Validate::isLoadedObject($order)) {
            return ['value' => 0.0, 'error' => $this->module->l('Ordine non trovato.')];
        }

        $orderTotal = (float) $order->total_paid; // o total_products, dipende dalla tua logica
        if ($value > $orderTotal) {
            // Correggi silenziosamente al massimo (stesso comportamento del JS)
            // Oppure restituisci errore: scelta tua in base all'UX desiderata
            $value = $orderTotal;
        }

        // 6. Arrotonda a 2 decimali per evitare floating-point drift nel DB
        $value = round($value, 2);

        return ['value' => $value, 'error' => null];
    }
}
