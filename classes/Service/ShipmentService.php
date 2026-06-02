<?php

class ShipmentServices
{
    private CarrierRepository $carrierRepo;
    private CarrierServices $carrierServices;
    private ShipmentRepository $shipmentRepo;
    private Context $context;
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
        Context $context,
        spedisciquishipping $module
    ) {
        $this->carrierRepo    = $carrierRepo;
        $this->carrierServices = $carrierServices;
        $this->shipmentRepo = $shipmentRepo;
        $this->context = $context;
        $this->module = $module;
    }


    private const PAYMENT_STATUS_MAP = [
        'Payment accepted'       => 'paid',
        'Awaiting bank wire payment' => 'pending',
        'Refunded'               => 'refunded',
    ];

    //=================================================
    // CLACOLA COSTO DI SPEDIZIONE TRAMITE PESO
    //=================================================

    public function getRateShippingCost(Cart $cart, int $idCarrier): float|false
    {
        try {
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

            $totalWeight = (float) $cart->getTotalWeight();

            $carrier = $this->carrierRepo->getCarrierById($idCarrier);

            if (empty($carrier) || empty($carrier['service_code'])) {
                PrestaShopLogger::addLog(
                    sprintf('[SpedisciQui] Carrier #%d non trovato | Cart #%d', $idCarrier, $cart->id),
                    3,
                    null,
                    'Cart',
                    (int) $cart->id,
                    true
                );
                return false;
            }

            $carrierCode = (string) $carrier['service_code'];

            $tariff = $this->carrierServices->getApplicableTariff($carrierCode, $totalWeight);

            if ($tariff === false || !isset($tariff['tariff'])) {
                return false;
            }

            $shippingCost = (float) $tariff['tariff'];

            if ($shippingCost < 0.0) {
                PrestaShopLogger::addLog(
                    sprintf('[SpedisciQui] Tariffa negativa (%.4f) | Code: %s | Cart #%d', $shippingCost, $carrierCode, $cart->id),
                    3,
                    null,
                    'Cart',
                    (int) $cart->id,
                    true
                );
                return false;
            }

            return $shippingCost;
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                sprintf(
                    '[SpedisciQui] Eccezione | Cart #%d | %s in %s:%d',
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
        $totalWidth  = 0.0;
        $totalHeight = 0.0;

        if (empty($products)) {
            return [
                'length' => (float) ($defaultPackage['length'] ?? 0),
                'width'  => (float) ($defaultPackage['width']  ?? 0),
                'height' => (float) ($defaultPackage['height'] ?? 0),
            ];
        }

        foreach ($products as $product) {
            $qty = (int) ($product['cart_quantity'] ?? 1);

            // PS salva le dimensioni in ps_product come width, height, depth
            // depth in PS = lunghezza fisica del pacco
            $pLength = (float) ($product['depth']  ?? 0); // PS chiama "depth" la lunghezza
            $pWidth  = (float) ($product['width']  ?? 0);
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
            $totalWidth  = max($totalWidth,  $pWidth);

            // height: sommo per la quantità (prodotti impilati)
            $totalHeight += $pHeight * $qty;
        }

        return [
            'length' => round($totalLength, 2),
            'width'  => round($totalWidth,  2),
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
        return [
            'id_shipment'      => (int)    $row['id_shipment'],
            'id_order'         => (int)    $row['id_order'],
            'tracking_number'  => (string) ($row['tracking_number'] ?? '—'),
            'carrier_code'     => (string) ($row['carrier_code']    ?? '—'),
            'service_code'     => (string) ($row['service_code']    ?? '—'),
            'status'           => (string) $row['status'],
            'status_label'     => $this->getStatusLabel((string) $row['status']),
            'status_class'     => $this->getStatusClass((string) $row['status']),
            'payment_status'   => $this->resolvePaymentStatus((string) ($row['order_state_name'] ?? '')),
            'payment_method'   => (string) ($row['payment_method'] ?? ''),
            'total_paid'       => number_format((float) $row['total_paid'], 2, ',', '.'),
            'currency'         => (string) ($row['shipping_currency'] ?? 'EUR'),
            'customer_name'    => (string) ($row['customer_name']   ?? '—'),
            'customer_email'   => (string) ($row['customer_email']  ?? ''),
            'delivery_city'    => (string) ($row['delivery_city']   ?? ''),
            'delivery_country' => (string) ($row['delivery_country_iso'] ?? ''),
            'weight'           => number_format((float) ($row['weight'] ?? 0), 3, ',', '.'),
            'shipping_cost'    => number_format((float) ($row['shipping_cost'] ?? 0), 2, ',', '.'),
            'date_add'         => $row['date_add']
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
            'pending'          => 'In attesa',
            'label_created'    => 'Label creata',
            'picked_up'        => 'Ritirato',
            'in_transit'       => 'In transito',
            'out_for_delivery' => 'In consegna',
            'delivered'        => 'Consegnato',
            'failed'           => 'Fallito',
            'cancelled'        => 'Annullato',
            'returned'         => 'Reso',
            default            => ucfirst($status),
        };
    }

    /**
     * Classe CSS Bootstrap per badge status.
     */
    public function getStatusClass(string $status): string
    {
        return match ($status) {
            'pending'          => 'warning',
            'label_created'    => 'info',
            'picked_up',
            'in_transit'       => 'primary',
            'out_for_delivery' => 'primary',
            'delivered'        => 'success',
            'failed',
            'cancelled'        => 'danger',
            'returned'         => 'secondary',
            default            => 'secondary',
        };
    }

    //==================================================
    //RISOLVE STATO PAGAMENTO
    //====================================================
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
        $idOrder  = (int) ($shipment['id_order'] ?? 0);
        $order    = $idOrder ? new \Order($idOrder) : null;

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
        $carrier     = $carrierCode
            ? $this->carrierRepo->getCarrierByCode($carrierCode)
            : null;

        // // ─── OPTIONS ─────────────────────────────────────────────────────────────
        // // Opzioni disponibili per questo tipo di spedizione
        // $availableOptions = $this->optionsRepo->getAvailableOptions(
        //     $carrierCode,
        //     $shipment['service_code'] ?? null
        // ) ?? [];

        // // Opzioni già selezionate/salvate per questa spedizione
        // $selectedOptions = $this->optionsRepo->getSelectedOptions($shipmentId) ?? [];



        // ─── FORM URLs ───────────────────────────────────────────────────────────
        $actionUrl = $this->buildAdminLink();

        $backUrl = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token='     . Tools::getAdminTokenLite('AdminModules')
            . '&action=list';

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
                'id_shipment'     => (int) ($shipment['id_shipment'] ?? $shipmentId),
                'status'          => $shipment['status'] ?? 'unknown',
                'status_label'    => $this->getStatusLabel($shipment['status'] ?? ''),
                'status_class'    => $this->getStatusClass($shipment['status'] ?? ''),
                'weight'          => (float) ($shipment['weight'] ?? 0),
                'base_cost'       => $this->formatPrice(
                    (float) ($shipment['base_cost'] ?? 0),
                    $currency
                ),
                'shipping_cost'   => (float) ($shipment['shipping_cost'] ?? 0),
                'tracking_number' => $shipment['tracking_number'] ?? '',
                'date_add'        => $shipment['date_add'] ?? '',
                'note'            => $shipment['note'] ?? '',  // opzionale
            ],

            // ── order ─────────────────────────────────────────────────────────────
            'order' => ($order && \Validate::isLoadedObject($order)) ? [
                'id_order'        => (int) $order->id,
                'reference'       => $order->reference ?? '',
                'date_add'        => $order->date_add ?? '',
                'total_paid'      => $this->formatPrice(
                    (float) $order->total_paid_tax_incl,
                    $currency
                ),
                'currency'        => $currency ? $currency->iso_code : '',
                'payment_method'  => $order->payment ?? '',
                'payment_status'  => $order->getCurrentOrderState()
                    ? $order->getCurrentOrderState()->name[$this->context->language->id] ?? ''
                    : '',
                'payment_label'   => $this->getPaymentLabel($order),
            ] : $this->getEmptyOrder(),

            // ── recipient ─────────────────────────────────────────────────────────
            'recipient' => ($address && \Validate::isLoadedObject($address)) ? [
                'full_name' => trim(
                    ($address->firstname ?? '') . ' ' .
                        ($address->lastname  ?? '')
                ),
                'company'   => $address->company  ?? '',   // opzionale
                'address1'  => $address->address1 ?? '',
                'address2'  => $address->address2 ?? '',   // opzionale
                'city'      => $address->city     ?? '',
                'postcode'  => $address->postcode ?? '',
                'province'  => $address->id_state
                    ? \State::getNameById((int) $address->id_state) ?? ''
                    : '',
                'country'   => $countryName,
                'phone'     => $address->phone ?? $address->phone_mobile ?? '',
            ] : $this->getEmptyRecipient(),

            // ── carrier ───────────────────────────────────────────────────────────
            'carrier' => [
                'carrier_code'   => $shipment['carrier_code']  ?? '',
                'service_code'   => $shipment['service_code']  ?? '',
                'service_name'   => $carrier['service_name']   ?? '',   // opzionale
                'estimated_days' => $carrier['estimated_days'] ?? null, // opzionale
                'logo_url'       => $carrier['logo_url']       ?? '',   // opzionale
            ],

            // ── options ───────────────────────────────────────────────────────────
            'options' => [
                'available' => [],
                'selected'  => [],
            ],

            // ── form ──────────────────────────────────────────────────────────────
            'form' => [
                'action_url'       => $actionUrl,
                'back_url'         => $backUrl,
                'order_detail_url' => $orderDetailUrl,
                'id_shipment'      => (int) ($shipment['id_shipment'] ?? $shipmentId),
                'base_cost_raw'    => (float) ($shipment['base_cost'] ?? 0),
            ],
        ];
    }



    //==================================================
    //HELPERS
    //====================================================


    // PAYLOAD PER API CONFERMA SHIPPING

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
            $senderRepo = new SenderRepository($this->context);

            $sender = $senderRepo->getDefault();

            if (!$sender || empty($sender)) {
                throw new \InvalidArgumentException('Nessun indirizzo mittente trovato nella tabella spedisciqui_sender_address. Configuralo nel modulo.');
            }

            PrestaShopLogger::addLog('parcel : ' . print_r($parcelData, true));

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
            'id_order'       => 0,
            'reference'      => '',
            'date_add'       => '',
            'total_paid'     => '',
            'currency'       => '',
            'payment_method' => '',
            'payment_status' => '',
            'payment_label'  => '',
        ];
    }

    private function getEmptyRecipient(): array
    {
        return [
            'full_name' => '',
            'company'   => '',
            'address1'  => '',
            'address2'  => '',
            'city'      => '',
            'postcode'  => '',
            'province'  => '',
            'country'   => '',
            'phone'     => '',
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

    private function buildAdminLink(): string
    {
        return AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token='     . Tools::getAdminTokenLite('AdminModules');
    }
}
