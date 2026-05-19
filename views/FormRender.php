<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class FormRenderer
{

    private spedisciquishipping $module;
    private SpedisciQuiApi $api;
    private PackageRepository $packageRepo;
    private SenderRepository $senderRepo;

    public function __construct(spedisciquishipping $module)
    {
        $this->module      = $module;
        $this->api         = new SpedisciQuiApi();
        $this->packageRepo = new PackageRepository();
        $this->senderRepo  = new SenderRepository();
    }


    /*
    ============== RENDER FORM PER ACCESS TOKEN =========
    */
    public function renderTokenForm()
    {
        $helper = new HelperForm();
        $helper->module = $this->module;
        $helper->name_controller = $this->module->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->module->name;
        $helper->submit_action = 'submitSpedisciQuiShipping';
        $helper->fields_value = [
            'SPEDISCIQUI_ACCESS_TOKEN' => Configuration::get('SPEDISCIQUI_ACCESS_TOKEN') ?: '',
        ];

        // CAMPI INPUT DEL FORM
        $formFields = [
            'form' => [
                'legend' => [
                    'title' => $this->module->l('Configurazione Api'),
                ],
                'input' => [
                    [
                        'type'     => 'text',
                        'label'    => $this->module->l('Chiave Segreta'),
                        'name'     => 'SPEDISCIQUI_ACCESS_TOKEN',
                        'required' => true,
                        'desc'     => $this->module->l('Incolla il token ottenuto dalla piattaforma SpedisciQui.'),
                    ],
                ],
                'submit' => [
                    'label' => $this->module->l('Salva e verifica'),
                ],
            ],
        ];

        return $helper->generateForm([$formFields]);
    }

    /*
    ============== RENDER FORM PER PACKAGE =========
    */
    public function renderPackageForm()
    {
        $id_shop = (int) Context::getContext()->shop->id;

        $this->module->getSmarty()->assign([
            'action'  => AdminController::$currentIndex . '&configure=' . $this->module->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'token'   => Tools::getAdminTokenLite('AdminModules'),
            'package' => $this->packageRepo->getPackage($id_shop) ?: [],
        ]);

        return $this->module->display($this->module->getLocalPath(), 'views/templates/admin/package_form.tpl');
    }

    /*
    ============== RENDER FORM PER SENDER =========
    */
    public function renderSenderForm()
    {
        $id_shop = (int) Context::getContext()->shop->id;

        $this->module->getSmarty()->assign([
            'action' => AdminController::$currentIndex . '&configure=' . $this->module->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'token'  => Tools::getAdminTokenLite('AdminModules'),
            'sender' => $this->senderRepo->getSender($id_shop) ?? [],
        ]);

        return $this->module->display($this->module->getLocalPath(), 'views/templates/admin/sender_form.tpl');
    }

    /*
    ============== RENDER DASHBOARD =========
    */
    public function renderDashboard()
    {
        $response = $this->api->request('GET', '/api/getCarriers');
        $carriers = ($response && is_array($response)) ? ($response['carriers'] ?? []) : [];

        file_put_contents(
            '/tmp/spedisciqui_debug.log',
            "RESPONSE: " . print_r($response, true) . "\n" .
                "CARRIERS: " . print_r($carriers, true) . "\n"
        );
        $rows = Db::getInstance()->executeS(
            'SELECT name, value FROM `' . _DB_PREFIX_ . 'configuration` WHERE name LIKE \'SPEDISCIQUI_CARRIER_%\''
        );

        if (!is_array($rows)) {
            $rows = [];
        }

        $savedCarriers  = is_array($rows) ? $rows : [];
        $installedCodes = array_column($rows, 'value') ?: [];

        foreach ($carriers as &$carrier) {
            $carrier['isInstalled'] = in_array($carrier['code'], $installedCodes);
        }
        unset($carrier);


        // user — dati utente dall'API
        $user = $this->api->request('GET', '/api/auth/user');

        $this->module->getSmarty()->assign([
            'user'         => $user ?? [],
            'savedCarriers' => $savedCarriers,
            'carriers' => $carriers,
            'action'   => AdminController::$currentIndex . '&configure=' . $this->module->name . '&token=' . Tools::getAdminTokenLite('AdminModules'),
            'token'    => Tools::getAdminTokenLite('AdminModules'),
        ]);

        return $this->module->display($this->module->getLocalPath(), 'views/templates/admin/dashboard.tpl');
    }
}
