<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class DashboardRenderer
{

    private ApiClient $api;
    private spedisciquishipping $module;


    public function __construct(
        ApiClient $api,
        spedisciquishipping $module,
    ) {
        $this->api = $api;
        $this->module = $module;
    }



    public function renderDashboard()
    {

        $savedCarriers  = [];
        $installedCodes = [];

        $carrierResponse = $this->api->request('GET', 'api/getCarriers');
        $carriers = $carrierResponse ?? [];

        // Recupero dei mapping dal DB
        $mappings = Db::getInstance()->executeS(
            'SELECT m.serviceId, m.carrierReferenceId, c.id_carrier, c.name, c.active
     FROM `' . _DB_PREFIX_ . 'spedisciqui_carrier` m
     LEFT JOIN `' . _DB_PREFIX_ . 'carrier` c 
         ON c.id_reference = m.carrierReferenceId AND c.deleted = 0
     WHERE m.isActive = 1'
        );

        if (is_array($mappings)) {
            foreach ($mappings as $row) {
                $savedCarriers[] = [
                    'id_carrier'   => (int) $row['id_carrier'],
                    'name'         => $row['name'],
                    'carrier_code' => $row['serviceId'],
                    'active'       => (bool) $row['active'],
                ];
                $installedCodes[] = $row['serviceId'];
            }
        }


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
    }
}
