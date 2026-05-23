<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class CarrierHandlers
{

    private spedisciquishipping $module;

    //==========================================
    // COSTRUTTORE
    //==========================================
    public function __construct(spedisciquishipping $module)
    {
        $this->module = $module;
    }



    //==========================================
    // INSTALLAZIONE CARRIER IN PS_CARRIER E TABELLA CUSTON spedisciqui_carrier
    //==========================================
    private function handleInstallcarrier()
    {
        $serviceId = Tools::getValue('service_code');
        $serviceName = Tools::getValue('service_name');
        $serviceCode = Tools::getValue('service_code');

        if (!$serviceId || !$serviceName) {
            return $this->module->displayError($this->module->l('Dari Corriere mancanti'));
        }

        // creazione corriere
        $carrier = new Carrier();
        $carrier->name                = $serviceName;
        $carrier->active              = true;
        $carrier->deleted             = false;
        $carrier->shipping_handling   = false;
        $carrier->range_behavior      = 0;
        $carrier->is_module           = true;
        $carrier->is_free             = false;
        $carrier->shipping_external   = true;
        $carrier->need_range          = true;
        $carrier->external_module_name = $this->module->name;

        // array per lingua
        foreach (Language::getLanguages() as $lang) {
            $carrier->delay[$lang['id_lang']] = '2-3- giorni lavorativi';
        }

        if (!$carrier->add()) {
            return $this->module->displayError($this->module->l('Errore durante la creazione del corriere'));
        }

        // associo carrier allo shop
        $carrier->setGroups(array_column(Group::getGroups(true), 'id_group'));

        // associazione corriere a range di zona
        $zones = Zone::getZones(true);
        foreach ($zones as $zone) {
            $carrier->addZone($zone['id_zone']);
        }

        // creazione range di peso per ogni zona
        $rangeWeight = new RangeWeight();
        $rangeWeight->id_carrier = $carrier->id;
        $rangeWeight->delimiter1 = 0;
        $rangeWeight->delimiter2 = 999;
        $rangeWeight->add();

        foreach ($zones as $zona) {
            Db::getInstance()->insert(
                'delivery',
                [
                    'id_carrier' => $carrier->id,
                    'id_range_weight' => $rangeWeight->id,
                    'id_range_price' => 0,
                    'id_zone' => $zona['id_zone'],
                    'price' => 0,
                ]
            );
        }

        // associazione corriere a tutti gli shop
        $shops = Shop::getShops(true);
        foreach ($shops as $shop) {
            $carrier->associateTo($shop['id_shop']);
        }

        // salva carrier in mapping
        Configuration::updateValue('SPEDISCIQUI_CARRIER_' . strtoupper($serviceCode), $carrier->id_reference);

        // salvo nella tabella mapping
        $db = new DatabaseManager();
        $db->saveCarrierMapping($serviceCode, $carrier->id_reference);

        return $this->module->displayConfirmation(
            $this->module->l('Corriere "' . $serviceName . '"aggiunto correttamente')
        );
    }

    /*
    ============ HANDLE PER RIMOZIONE CARRIER
    */

    private function handleRemoveCarrier()
    {

        $serviceCode = Tools::getValue('carrier_code');

        if (empty($serviceCode)) {
            return $this->module->displayError($this->module->l('Codice Corriere Mancante'));
        }

        $db = new DatabaseManager();

        // recupero mapping
        $mapping = $db->getCarrierMapping($serviceCode);

        if (!$mapping) {
            return $this->module->displayError('Corriere non trovato');
        }

        $referenceId = $mapping['carrierReferenceId'];

        // ricerca di tutti i carrier con id_reference
        $carriers = Db::getInstance()->executeS(
            'SELECT id_carrier FROM ' . _DB_PREFIX_ . 'carrier WHERE id_reference = ' . $referenceId . ' AND deleted = 0'
        );

        // rimozione da tabelle collegate
        if (is_array($carriers)) {
            foreach ($carriers as $row) {
                $idCarrier = (int) $row['id_carrier'];

                // Marca come deleted — mai cancellare fisicamente
                Db::getInstance()->update('carrier', ['deleted' => 1], 'id_carrier = ' . $idCarrier);

                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'carrier_zone`  WHERE id_carrier = ' . $idCarrier);
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'range_weight`  WHERE id_carrier = ' . $idCarrier);
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'range_price`   WHERE id_carrier = ' . $idCarrier);
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'delivery`      WHERE id_carrier = ' . $idCarrier);
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'carrier_group` WHERE id_carrier = ' . $idCarrier);
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'carrier_shop`  WHERE id_carrier = ' . $idCarrier);
            }
        };

        // rimuozione da configuration
        Configuration::deleteByName('SPEDISCIQUI_CARRIER_' . strtoupper($serviceCode));

        // rimozione dal mapping
        $db->deleteCarrierMapping($serviceCode);

        return $this->module->displayConfirmation('Corriere ' . $serviceCode . ' rimosso correttamente');
    }

    private function resolveView(): string
    {
        $renderer = new FormRenderer($this->module);
        $token    = Configuration::get('SPEDISCIQUI_ACCESS_TOKEN');
        $step     = Configuration::get('SPEDISCIQUI_SETUP_STEP');

        if (!$token)     return $renderer->renderTokenForm();
        if ($step == 1)  return $renderer->renderPackageForm();
        if ($step == 2)  return $renderer->renderSenderForm();

        return $renderer->renderDashboard();
    }
}
