<?php

class CarrierHooks
{

    private CarrierModule $module;


    // ================================================================
    // COSTRUTTORE
    // ================================================================
    public function __construct(CarrierModule $module)
    {
        $this->module = $module;
    }


    // ================================================================
    // HOOK PER ASSOCIARE CARRIER A CARRELLO 
    // ================================================================


    public function hookActionCarrierProcess(array $params): void
    {
        $cart = $params['cart'] ?? null;

        PrestaShopLogger::addLog(
            'PARAMS IN HOOK: ' . print_r($cart, true)
        );

        // shippping esistente
        if (!$cart || (int) $cart->id <= 0) {
            return;
        }

        if ($cart->id_carrier > 0) {
            return;
        }

        // se non c'è associa il primo oche trovi in lista
        $firstCarrierId = (int) Db::getInstance()->getValue(
            'SELECT id_carrier FROM ' . _DB_PREFIX_ . 'carrier'
                . ' WHERE external_module_name = "' . pSQL($this->module->name) . '"'
                . ' AND deleted = 0 AND active = 1'
                . ' ORDER BY position ASC LIMIT 1'
        );


        if ($firstCarrierId <= 0) {
            return;
        }


        $deliveryOption = [
            (int)$cart->id_address_delivery => $firstCarrierId . ','
        ];

        $cart->setDeliveryOption($deliveryOption);
    }



    // ================================================================
    // HOOKS PER VISUALIZZARE EXTRA CONTENT
    // ================================================================
    public function hookDisplayCarrierExtraContent(array $params)
    {
        $carrier = $params['carrier'];
        $cart = $params['cart'];

        // mostra extra content solo per carrier del modulo
        $isOurs = Db::getInstance()->getValue(
            'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'carrier'
                . ' WHERE id_carrier = ' . $carrier['id']
                . ' AND external_module_name = "' . pSQL($this->module->name) . '"'
        );

        if (!$isOurs) {
            return '';
        }

        // recupera prezzo assicurazione (testing)
        $ctx = Context::getContext();
        $insurancePrice = 5.70;
        $checked = isset($ctx->cookie->{'sq_insurance_' . $carrier['id']})
            ? $ctx->cookie->{'sq_insurance_' . $carrier['id']}
            : false;

            
        $ctx->smarty->assign([
            'carrier' => $carrier,
            'insurance_price' => $insurancePrice,
            'insurance_checked' => $checked,
        ]);

        return $this->module->display(
            $this->module->getLocalPath() . $this->module->name . '.php',
            'views/templates/hooks/carrier-extra-content.tpl'
        );
    }



    // ================================================================
    // HOOKS PER VISUALIZZARE EXTRA CONTENT
    // ================================================================

    public function hookActionValidateStepComplete(array $params)
    {

        $ctx = Context::getContext();
        $stepName = $params['step_name'] ?? '';

        if ($stepName !== 'delivery') {
            return;
        }

        $cart = $params['cart'];
        $carrierId = $cart->id_carrier;

        $insuranceKey = 'spedisciqui_insurance_' . $carrierId;
        $value = Tools::getValue($insuranceKey, false);

        // salva nel cookie
        $ctx->cookie->{'sq_insurance_' . $carrierId} = $value;
        $ctx->cookie->write();

        // Oppure in una tabella dedicata se vuoi persistenza su DB
        Db::getInstance()->execute(
            'INSERT INTO ' . _DB_PREFIX_ . 'spedisciqui_cart_options
         (id_cart, id_carrier, insurance, insurance_value)
         VALUES (' . (int) $cart->id . ', ' . $carrierId . ', '
                . ($value ? 1 : 0) . ', '
                . (float) Configuration::get('SPEDISCIQUI_INSURANCE_VALUE') . ')
         ON DUPLICATE KEY UPDATE
         insurance = VALUES(insurance),
         insurance_value = VALUES(insurance_value)'
        );
    }
}
