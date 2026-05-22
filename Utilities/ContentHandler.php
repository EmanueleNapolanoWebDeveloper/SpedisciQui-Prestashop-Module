<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class ContentHandler
{

    private spedisciquishipping $module;
    private SpedisciQuiApi $api;
    private PackageRepository $packageRepo;
    private SenderRepository $senderRepo;

    public function __construct(spedisciquishipping $module)
    {
        $this->module = $module;
        $this->api = new SpedisciQuiApi();
        $this->packageRepo = new PackageRepository();
        $this->senderRepo = new SenderRepository();
    }


    // FUNZIONE DI HANDLERS

    public function handle()
    {

        $output = '';

        // INSERIMENTO TOKEN
        if (Tools::isSubmit('submitSpedisciQuiShipping')) {
            $output .= $this->handleTokenSubmit();
        }

        // INSERIMENTO DATI PACCHI
        if (Tools::isSubmit('submitPackageForm')) {
            $output .= $this->handlePackageSubmit();
        }

        // INSERIMENTO MITTENTE DEFAULT
        if (Tools::isSubmit('submitSenderForm')) {
            $output .= $this->handleSenderSubmit();
        }

        // TESTING API
        if (Tools::isSubmit('submitTestApi')) {
            $output .= $this->handleTestApi();
        }

        // HANDLE RESET TOKEN
        if (Tools::isSubmit('submitResetToken')) {
            $this->handleResetToken();
        }

        // HANDLE AGGIUNTA CORRIERE
        if (Tools::isSubmit('submitInstallCarrier')) {
            $output .= $this->handleInstallcarrier();
        }

        // HANDLE RIMOZIONE CORRIERE
        if (Tools::isSubmit('submitRemoveCarrier')) {
            $output .= $this->handleRemoveCarrier();
        }


        return $output . $this->resolveView();
    }

    /*
    ============== HANDLE PER INSERIMENTO ACCESS_TOKEN===================
    */
    private function handleTokenSubmit()
    {
        $token = trim(Tools::getValue('SPEDISCIQUI_ACCESS_TOKEN'));

        // controllo token vuoto
        if (empty($token)) {
            return $this->module->displayError($this->module->l('Client Token obbligatorio!'));
        }

        // controllo token errato/scaduto
        if (!$this->api->validateToken($token)) {
            return $this->module->displayError($this->module->l('Client Token non valido!'));
        }

        // salvataggio token e avanzamaneto step setup
        Configuration::updateValue('SPEDISCIQUI_ACCESS_TOKEN', $token);
        Configuration::updateValue('SPEDISCIQUI_SETUP_STEP', 1);

        return $this->module->displayConfirmation($this->module->l('Token Configurato correttamnte!'));
    }

    /*
    ============== HANDLE PER INSERIMENTO DATI PACKAGE===================
    */

    private function handlePackageSubmit()
    {
        $this->packageRepo->savePackage(Context::getContext()->shop->id, [
            'weight' => Tools::getValue('package_weight'),
            'height' => Tools::getValue('package_height'),
            'width' => Tools::getValue('package_width'),
            'depth'  => Tools::getValue('package_depth'),
        ]);

        Configuration::updateValue('SPEDISCIQUI_SETUP_STEP', 2);
        return $this->module->displayConfirmation($this->module->l('Dimensiona Pacco inserito correttamnte!'));
    }

    /*
    ============== HANDLE PER INSERIMENTO DATI SENDER (MITTENTE)===================
    */

    private function handleSenderSubmit()
    {
        $this->senderRepo->saveSender([
            'name'    => Tools::getValue('sender_name'),
            'surname' => Tools::getValue('sender_surname'),
            'address' => Tools::getValue('sender_address'),
            'phone'   => Tools::getValue('sender_phone'),
            'city'    => Tools::getValue('sender_city'),
            'zip'     => Tools::getValue('sender_zip'),
            'country' => Tools::getValue('sender_country'),
            'prov'    => Tools::getValue('sender_prov'),
        ]);

        Configuration::updateValue('SPEDISCIQUI_SETUP_STEP', 'DONE');
        return $this->module->displayConfirmation($this->module->l('Mittente inserito con successo'));
    }

    /*
    ============ HANDLE PER TEST CONNESSIONE API
    */

    private function handleTestApi()
    {
        $response = $this->api->request('GET', '/api/testing');

        if ($response) {
            return '<div class="alert alert-success"><h4>✅ Risposta da Laravel:</h4><pre>'
                . htmlspecialchars(json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE))
                . '</pre></div>';
        }

        return $this->module->displayError($this->module->l('Nessuna risposta dal server.'));
    }

    /*
    ============ HANDLE PER RESET TOKEN
    */

    private function handleResetToken()
    {
        Configuration::deleteByName('SPEDISCIQUI_ACCESS_TOKEN');
        Configuration::deleteByName('SPEDISCIQUI_SETUP_STEP');
        Tools::redirectAdmin(
            Context::getContext()->link->getAdminLink('AdminModules', true) . '&configure=' . $this->module->name
        );
    }

    /*
    ============ HANDLE PER AGGIUNTA CARRIER
    */
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

        if (!$carrier->id_reference) {
            Db::getInstance()->update('carrier', ['id_reference' => (int) $carrier->id], 'id_carrier = ' . (int) $carrier->id);
            $carrier->id_reference = $carrier->id;
        }

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
        $groups = Group::getGroups(true);
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
        $db->saveCarrierMapping($serviceCode, $carrier->id);

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
