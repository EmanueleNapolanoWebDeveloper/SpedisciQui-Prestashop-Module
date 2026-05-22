<?php

if (!defined('_PS_VERSION_')) {
    exit;
};

namespace Prestashop\SpedisciQui;

use Carrier;
use Cart;
use Configuration;
use Db;
use spedisciquishipping;
use Tools;
use Validate;

class SpedisciQuiCart
{

    protected $db;
    protected $module;



    // ================================================================
    // COSTRUTTORE
    // ================================================================

    public function __construct()
    {
        $this->db = Db::getInstance();
        $this->module = new spedisciquishipping();
    }


    // ================================================================
    // RECUPERO DATI SPEDIZIONE DI UN CARRELLO
    // ================================================================
    public function getOrderSpedisciCartInfo($id_cart)
    {

        $sql = "SELECT * FROM " . _DB_PREFIX_ . "spedisciqui_cart"
            . " WHERE id_cart= $id_cart";

        return $this->db->getRow($sql);
    }


    // ================================================================
    // CREA/AGGIORNA RECORD IN SPEDISCIQUI_CART QUANDO VIENE CREATO UN ORDINE
    // ================================================================

    public function saveOrder($orderObj)
    {
        $cartObj = new Cart($orderObj->id_cart);
        $carrier = new Carrier($orderObj->id_carrier);
        $totalWeight = $cartObj->getTotalWeight();
        $cod_modules = explode(',', Configuration::get('SPEDISCIQUI_COD_MODULES')); //verifica se pagamento è in contrassegno
        $is_pickup = $carrier->id_reference == Configuration::get('SPEDISCIQUI_PICKUP_POINT_ID_REFERENCE') ? 1 : 0; //controlla se corriere è pickup

        // preparazione array per DB
        $cart = array(
            'id_cart' => $orderObj->id_cart, //carrello
            'packs' => 1, //numero pacchi
            'weight' => $totalWeight <= 0 ? 1 : $totalWeight, //peso
            'is_cod' => in_array($orderObj->module, $cod_modules), //contrassegno
            'cod_amount' => $orderObj->total_paid_tax_incl,
            'is_pickup' => $is_pickup,
            'label_number' => '',
            'is_oversized' => 0,
            'is_call_before_delivery' => 0,
            'is_fragile' => 0,
            'error' => NULL,
            'id_spedisci_manifest' => NULL,
        );


        $existingCart = Db::getInstance()->getValue(
            "SELECT 1 FROM " . _DB_PREFIX_ . "spedisciqui_cart"
                . " WHERE id_cart = " . (int)$orderObj->id_cart
        );



        // INSERT/UPDATE RECORD
        if (!$existingCart) {
            $this->db->insert('spedisciqui_cart', $cart, true, false);
        } else {
            unset($cart['id_cart']);
            $this->db->update('spedisciqui_cart', $cart, 'id_cart = ' . (int)$orderObj->id_cart, 0, true, false);
        }
    }




    // ================================================================
    // AGGIORNA INFORMAZIONI SPEDIZIONE
    // ================================================================

    public function updateCarrier($id_cart, $is_pickup)
    {
        $row = Db::getInstance()->getValue(
            "SELECT 1 FROM " . _DB_PREFIX_ . "spedisciqui_cart"
                . " WHERE id_cart = " . pSQL($id_cart)
        );

        if ($row) {
            $this->db->execute(
                "UPDATE " . _DB_PREFIX_ . "spedisciqui_cart"
                    . " SET 'is_pickup' = " . pSQL($is_pickup)
                    . " WHERE id_cart = " . pSQL($id_cart),
                false
            );
        }
    }


    // ================================================================
    // AGGIORNA CARRELLO
    // ================================================================
    public function updateSpedisciCart()
    {

        // controllo che ci siano dati POST
        if (empty($_POST)) {
            return array('errors' => array($this->module->l('Non ci sono dati per modificare carrello')));
        }

        // recupero dati di input
        $id_order = Tools::getValue('id_order', NULL);
        $id_cart = Tools::getValue('id_cart', NULL);
        $packs = Tools::getValue('packs', 1);
        $weight = Tools::getValue('weight', 1);
        $volume = Tools::getValue('volume', 0);
        $is_cod = Tools::getValue('is_cod', 0);
        $cod_amount = Tools::getValue('cod_amount', NULL);
        $is_pickup = Tools::getValue('is_pickup', null);
        $id_pickup_point = Tools::getValue('id_pickup_point', NULL);
        $spedisciqui_extra = Tools::getValue('spedisciqui_extra', NULL);
        $spedisciqui_comment = Tools::getValue('spedisciqui_comment', NULL);

        // normalizazzioen numeri float (trasforma , in .)
        $weight = str_replace(',', '.', $weight);
        $volume = str_replace(',', '.', $volume);

        // -----------> VALIDAZIONI
        $errors = array();

        // ID_CART NON VALIDO
        if (empty($id_cart) || !Validate::isUnsignedInt($id_cart)) {
            $errors[] = $this->module->l('Id Carrello non valido!', "SpedisciQuiCart");
        }

        // NUMERO PACCHI NON VALID
        if (empty($packs) || !Validate::isUnsignedInt($packs) || (int) $packs < 1) {
            $errors[] = $this->module->l('Numero pacchi non valido', "SpedisciQuiCart");
        }

        // PESO NON VALIDO
        if (!Validate::isUnsignedFloat($weight)) {
            $errors[] = $this->module->l('Peso non valido', "SpedisciQuiCart");
        }

        // VOLUME NON VALIDO
        if (!Validate::isUnsignedFloat($volume)) {
            $errors[] = $this->module->l('Volume non valido', "SpedisciQuiCart");
        }

        // CONTRASSEGNO VALIDO
        if (!in_array($is_cod, array('0', '1'))) {
            $errors[] = $this->module->l('Contrassegno non valido', "SpedisciQuiCart");
        }

        // VALORE CONTRASSEGNO VALIDO
        if ($is_cod == '1' && (empty($cod_amount)) && !Validate::isFloat($cod_amount)) {
            $errors[] = $this->module->l('Importo contrassegno non valido', 'SpedisciQuiCart') . $cod_amount . ' ' . $is_cod;
        }

        // PICK-UP VALIDO
        if (!in_array($is_pickup, array('0', '1'))) {
            $errors[] = $this->module->l('PickUp non valido', 'SpedisciQuiCart');
        }

        // locazione pickup valido
        if ($is_pickup === '1' && (!$this->module->isLocation($id_pickup_point))) {
            $errors = $this->module->l('Pickup Point invalido', 'SpedisciQuiCart');
        }

        // se ci sono errori termina e mostra $errors
        if (!empty($errors)) {
            return array('errors' => $errors);
        }

        // -------> SE VALIDAZIONI OK - COSTRUZIONE ARRAY CART
        $cart = array(
            'packs' => $is_pickup == 0 ? pSQL($packs) : 1,
            'weight' => pSQL($weight),
            'volume' => pSQL($volume),
            'is_cod' => pSQL($is_cod),
            'cod_amount' => pSQL($cod_amount),
            'is_pickup' => pSQL($is_pickup),
            'label_numer' => '',
            'is_oversized' => 0,
            'is_call_before_delivery' => 0,
            'is_fragile' => 0,
            'error' => NULL,
            'comment' => pSQL($spedisciqui_comment),
            'id_spedisciqui_manifest' => NULL,
        );


        // se is_pickup è TRUE allora sssociamo l'id del punto di ritiro
        if ($is_pickup === '1') {
            $cart['id_pickup_point'] = pSQL($id_pickup_point);
        }

        // check per servizi extra (è oversized,fragile, chiamata dopo il delivery)
        if (is_array($spedisciqui_extra)) {
            foreach ($spedisciqui_extra as $extra) {
                if ($is_pickup == 0 && in_array($extra, array('is_oversized', 'is_call_before_delivery', 'is_fragile'))) {
                    $cart[$extra] = '1';
                }
            }
        }


        // INSERIMENTO O AGGIORNAMENTO DENTRO SPEDISCIQUI_CART
        if (!Db::getInstance()->getValue(
            "SELECT 1 FROM " . _DB_PREFIX_ . "spedisciqui_cart"
                . " WHERE id_cart = " . pSQL($id_cart)
        )) {
            $cart['id_cart'] = pSQL($id_cart);
            $result = Db::getInstance()->insert('spedisciqui_cart', $cart, 'id_cart = ' . pSQL($id_cart), 0, true, false);
        } else {
            $result = Db::getInstance()->update('spedisciqui_cart', $cart, 'id_cart = ' . pSQL($id_cart), 0, true, false);
        }

        // controllo
        if (!$result) {
            return array('errors' => array($this->module->l('Inserimento carrello fallito', 'SpedisciQuiCart')), 'data' => $cart, 'id_order' => $id_order);
        }

        return array('success' => $this->module->l('Carrelo aggiornato correttamente!', 'SpedisciQuiCart'), 'data' => $cart, 'id_order' => $id_order, 'id_cart' => $id_cart);
    }


    // ================================================================
    // SALVATAGGIO ERRORI SU DB
    // ================================================================

    public function saveError($id_cart, $error_msg)
    {
        if (Db::getInstance()->getValue("SELECT 1 FROM " . _DB_PREFIX_ . "spedisciqui_cart WHERE id_cart = " . pSQL($id_cart))) {
            $this->db->execute("UPDATE " . _DB_PREFIX_ . "spedisciqui_cart SET `error` = '" . pSQL($error_msg) . "' WHERE id_cart = " . pSQL($id_cart), false);
        }
    }
}
