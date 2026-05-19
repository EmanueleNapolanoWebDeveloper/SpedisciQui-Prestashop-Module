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
            'length' => Tools::getValue('package_length'),
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

        // salva carrier in mapping
        Configuration::updateValue('SPEDISCIQUI_CARRIER_' . strtoupper($serviceCode), $carrier->id_reference);

        // salvo nella tabella mapping
        Db::getInstance()->insert('spedisciqui_carrier_mapping', [
            'serviceId' => pSQL($serviceId),
            'carrierreferenceId' => $carrier->id_reference,
            'isActive' => 1,
        ]);

        return $this->module->displayConfirmation(
            $this->module->l('Corriere "' . $serviceName . '"aggiunto correttamente')
        );
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
