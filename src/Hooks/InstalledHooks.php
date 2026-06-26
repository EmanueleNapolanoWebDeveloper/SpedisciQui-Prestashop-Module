<?php

use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

if (!defined('_PS_VERSION_')) {
    exit;
}

class InstalledHooks
{

    private spedisciquishipping $module;
    private Context $context;
    private CarrierRepository $carrierRepo;
    private ApiClient $apiClient;
    private PackageRepository $packageRepo;
    private ShipmentServices $shipmentService;

    private SenderRepository $senderRepo;

    public function __construct(
        spedisciquishipping $module,
        CarrierRepository $carrierRepo,
        ApiClient $apiClient,
        PackageRepository $packageRepo,
        ShipmentServices $shipmentService,
        SenderRepository $senderRepo,
    ) {
        $this->module = $module;
        $this->context = Context::getContext();
        $this->carrierRepo = $carrierRepo;
        $this->apiClient = $apiClient;
        $this->packageRepo = $packageRepo;
        $this->shipmentService = $shipmentService;
        $this->senderRepo = $senderRepo;
    }




    //========================================
    // HOOK PER VISUALIZZARE AL CHECKOUT - INIZIO
    //========================================

    // public function hookDisplayCarrierExtraContent($params)
    // {

    //     PrestaShopLogger::addLog('Inizio display carrier');

    //     if (!isset($params['carrier'])) {
    //         return '';
    //     }

    //     $carrier          = $params['carrier'];
    //     $currentCarrierId = (int)($carrier['id'] ?? 0);
    //     $realCarriers     = $this->carrierRepo->getSavedCarriers();

    //     if (empty($realCarriers)) {
    //         PrestaShopLogger::addLog('Non passa realCarriers');
    //         return '';
    //     }

    //     // Ricerca carrier da lista
    //     $matchedCarrier = null;

    //     foreach ($realCarriers as $carrierData) {
    //         if ((int)$carrierData['id_carrier'] === $currentCarrierId) {
    //             $matchedCarrier = $carrierData;
    //             break;
    //         }
    //     }

    //     // se ci sono carrier non del nostro modulo
    //     if (!$matchedCarrier) {
    //         return '';
    //     }

    //     // recupera prezzi solo se necessari
    //     try {
    //         $prices = (new CarrierApi(new ApiClient(new ConfigRepositories)))->getPriceFromApi();
    //     } catch (Throwable $e) {
    //         PrestaShopLogger::addLog('[SPEDISCIQUI] getPriceFromApi: ' . $e->getMessage(), 3);
    //         $prices = [];
    //     }

    //     PrestaShopLogger::addLog(
    //         '[SPEDISCIQUI] prices dump: ' . json_encode($prices),
    //         1
    //     );

    //     $carrierCode = $matchedCarrier['carrier_code'];

    //     // dati del carrier
    //     $carrierPrice = $prices[$carrierCode]['price'] ?? null;
    //     $insurancePrice = $prices[$carrierCode]['insurance'] ?? null;
    //     $insuranceRequired = $prices[$carrierCode]['insurance_required'] ?? false;

    //     // ritorno valori al front
    //     $this->context->smarty->assign([
    //         'spqCarrier'           => $matchedCarrier,
    //         'spqCarrierPrice'      => $carrierPrice !== null
    //             ? Tools::displayPrice($carrierPrice)
    //             : null,
    //         'spqInsurancePrice'    => $insurancePrice !== null
    //             ? Tools::displayPrice($insurancePrice)
    //             : null,
    //         'spqInsuranceRequired' => $insuranceRequired,
    //         'spqCarrierCode'       => $carrierCode,
    //         'carrier'              => $carrier,
    //     ]);

    //     PrestaShopLogger::addLog(
    //         '[SPEDISCIQUI] carrier=' . $carrierCode .
    //             ' price=' . var_export($carrierPrice, true) .
    //             ' insurance=' . var_export($insurancePrice, true)
    //     );

    //     // Inietto CSS
    //     $this->context->controller->registerStylesheet(
    //         'spedisciqui_carrier',
    //         'modules/spedisciquishipping/views/css/checkout_carrier.css',
    //         ['media' => 'all', 'priority' => 150]
    //     );

    //     return $this->module->fetch(
    //         'module:spedisciquishipping/views/templates/hook/checkout/_partials/carrier_extra_content.tpl'
    //     );
    // }
    //========================================
    // HOOK PER VISUALIZZARE AL CHECKOUT - FINE
    //========================================



    // ======================================================
    // HOOK PER VALIDAZIONE ORDINE (PER SALVARE IN TAB SHIPMENT) - inizio
    // ======================================================

    public function hookActionValidateOrder(array $params): void
    {
        try {
            $order = $params['order'] ?? null;
            $cart = $params['cart'] ?? null;

            // Guard — params obbligatori
            if (!$order instanceof Order || !$cart instanceof Cart) {
                PrestaShopLogger::addLog(
                    '[SpedisciQui] hookActionValidateOrder — params order/cart mancanti',
                    3,
                    null,
                    'Order',
                    0,
                    true
                );
                return;
            }

            // ─────────────────────────────────────────────────────────────────
            // Carrier va recuperato dall'ordine, NON da $params['carrier']
            // che non esiste in questo hook.
            // ─────────────────────────────────────────────────────────────────
            $idCarrier = (int) $order->id_carrier;

            if ($idCarrier <= 0) {
                PrestaShopLogger::addLog(
                    sprintf('[SpedisciQui] hookActionValidateOrder — id_carrier non valido | Order #%d', $order->id),
                    3,
                    null,
                    'Order',
                    (int) $order->id,
                    true
                );
                return;
            }

            // Verifica che il carrier appartenga a questo modulo
            $carrierData = $this->carrierRepo->getCarrierById($idCarrier);

            if (empty($carrierData)) {
                // Carrier non gestito da SpedisciQui — skip silenzioso
                return;
            }

            // Recupera indirizzo di consegna dall'ordine
            $address = new Address((int) $order->id_address_delivery);
            $country = new Country((int) $address->id_country);

            $shipmentRepo = new ShipmentRepository();

            PrestaShopLogger::addLog(
                sprintf(
                    '[SpedisciQui] carrierData dump | id_carrier: %d | data: %s',
                    $idCarrier,
                    json_encode($carrierData)
                ),
                1,
                null,
                'Order',
                (int) $order->id,
                true
            );

            // dimensiona pacchi default          
            $defaultPackage = $this->packageRepo->getDefault((int) $order->id_shop);

            // calcolo dimensioni carrello
            $dimensions = $this->shipmentService->calculatePackageDimensions($cart, $defaultPackage);

            $idShipment = $shipmentRepo->createShipment([
                'id_order' => (int) $order->id,
                'id_shop' => (int) $order->id_shop,
                'id_spedisciqui_carrier' => !empty($carrierData['id_spedisciqui_carrier'])
                    ? (int) $carrierData['id_spedisciqui_carrier']
                    : null,
                'shipment_type' => 'outbound',
                'carrier_code' => (string) ($carrierData['carrier_code'] ?? ''),
                'service_code' => (string) ($carrierData['service_code'] ?? ''),
                'status' => 'pending',

                // Indirizzo di consegna
                'delivery_firstname' => (string) ($address->firstname ?? ''),
                'delivery_lastname' => (string) ($address->lastname ?? ''),
                'delivery_address1' => (string) ($address->address1 ?? ''),
                'delivery_address2' => (string) ($address->address2 ?? ''),
                'delivery_postcode' => (string) ($address->postcode ?? ''),
                'delivery_city' => (string) ($address->city ?? ''),
                'delivery_country_iso' => (string) ($country->iso_code ?? ''),

                // Peso e costo
                'weight' => (float) $cart->getTotalWeight(),
                'length' => $dimensions['length'],
                'width' => $dimensions['width'],
                'height' => $dimensions['height'],
                'shipping_cost' => (float) $order->total_shipping,
                'shipping_currency' => $this->getCurrencyIso((int) $order->id_currency),
            ]);

            if ($idShipment === false) {
                return; // già loggato nel repository
            }

            PrestaShopLogger::addLog(
                sprintf(
                    '[SpedisciQui] Shipment #%d creato | Order #%d | %s | Peso: %.3f kg | Costo: %.2f €',
                    $idShipment,
                    $order->id,
                    $carrierData['service_code'],
                    $cart->getTotalWeight(),
                    $order->total_shipping
                ),
                1,
                null,
                'Order',
                (int) $order->id,
                true
            );
        } catch (\Throwable $e) {
            PrestaShopLogger::addLog(
                sprintf(
                    '[SpedisciQui] hookActionValidateOrder — Eccezione | %s in %s:%d',
                    $e->getMessage(),
                    $e->getFile(),
                    $e->getLine()
                ),
                4,
                null,
                'Order',
                (int) ($params['order']->id ?? 0),
                true
            );
        }
    }

    // ======================================================
    // HOOK PER VALIDAZIONE ORDINE (PER SALVARE IN TAB SHIPMENT) - FINE
    // ======================================================



    // ======================================================
    // HOOK PER VISUALIZAZZIONE MITTENTE PER PRODOTTO - inizio
    // ======================================================
    public function hookActionProductFormBuilderModifier(array $params): void
    {
        $formBuilder = $params['form_builder'] ?? null;
        $idProduct = (int) ($params['id'] ?? 0);

        if (!$formBuilder) {
            PrestaShopLogger::addLog(
                '[SpedisciQui] hookActionProductFormBuilderModifier — form_builder mancante',
                2,
                null,
                null,
                true
            );
            return;
        }

        // recupero senders
        $senders = $this->senderRepo->getAllSenders();

        // costruzione delle scelte
        $choices = [];
        foreach ($senders as $sender) {
            $label = $sender['label'] . ' - ' . $sender['company'] . ' - ' . $sender['city'];
            $choices[$label] = (int) $sender['id_sender'];
        }

        // recupera sender associato al prodotto
        // $currentSenderId = 0;
        // if ($idProduct > 0) {
        //     $currentSenderId = $this->senderProductRepo->findByProduct($idProduct);
        // }

        // aggiungi campo 
        $formBuilder->get('shipping')->add(
            'spedisciqui_sender',
            ChoiceType::class,
            [
                'label' => $this->module->l('Mittente Spedizione'),
                'choices' => $choices,
                'required' => false,
                'placeholder' => $this->module->l('-- Nessun Mittente --'),
                // 'data'        => $currentSenderId,
            ]
        );

        PrestaShopLogger::addLog(
            sprintf(
                '[SpedisciQui] hookActionProductFormBuilderModifier — campo aggiunto | id_product=%d | sender corrente=%s',
                $idProduct,
                $currentSenderId ?? 'null'
            ),
            1,
            null,
            null,
            true
        );
    }
    // ======================================================
    // HOOK PER VISUALIZAZZIONE MITTENTE PER PRODOTTO - fine
    // ======================================================




    //===========================================================
    // HELOPERS
    // =========================================================
    private function getCurrencyIso(int $idCurrency): string
    {
        $currency = new Currency($idCurrency);
        return $currency->iso_code ?? 'EUR';
    }
}
