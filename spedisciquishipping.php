<?php

error_log('__DIR__ = ' . __DIR__);

if (!defined('_PS_VERSION_')) {
    exit;
}

if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require __DIR__ . '/../vendor/autoload.php';
}

require __DIR__ . '/Utilities/SpedisciQuiApi.php';
require __DIR__ . '/Utilities/DatabaseManager.php';
require __DIR__ . '/Repositories/PackageRepository.php';
require __DIR__ . '/Repositories/SenderRepository.php';
require __DIR__ . '/views/FormRender.php';
require __DIR__ . '/Utilities/ContentHandler.php'; 

class spedisciquishipping extends CarrierModule
{
    protected SpedisciQuiApi $api;
    protected SenderRepository $senderRepo;
    protected PackageRepository $packageRepo;
    protected DatabaseManager $db;


    public function __construct()
    {
        $this->name = 'spedisciquishipping';
        $this->tab = 'shipping_logistics';
        $this->version = '1.0.0';
        $this->author = 'Emanuele';
        $this->need_instance = 0;
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = 'SpedisciQui Shipping Primo';
        $this->description = 'Modulo spedizioni customizzato';
        $this->api = new SpedisciQuiApi();
        $this->packageRepo = new PackageRepository();
        $this->senderRepo = new SenderRepository();
        $this->db = new DatabaseManager();
    }

    public function install(): bool
    {
        try {
            $parentInstall = parent::install();
            error_log('parent::install() = ' . ($parentInstall ? 'true' : 'false'));

            $dbResult = $this->db->createAllTableOnInstallation();
            error_log('createAllTableOnInstallation() = ' . ($dbResult ? 'true' : 'false'));

            return $parentInstall && $dbResult
                && Configuration::updateValue('SPEDISCIQUI_ACCESS_TOKEN', null)
                && Configuration::updateValue('SPEDISCIQUI_SETUP_STEP', null);
        } catch (\Exception $e) {
            error_log('INSTALL ERROR: ' . $e->getMessage());
            return false;
        }
    }

    public function uninstall(): bool
    {
        return parent::uninstall()
            && $this->db->dropAllSpedisciQuiTables()
            && Configuration::deleteByName('SPEDISCIQUI_ACCESS_TOKEN')
            && Configuration::deleteByName('SPEDISCIQUI_SETUP_STEP');
    }

    public function getSmarty(): Smarty
    {
        return $this->context->smarty;
    }

    public function getContent()
    {
        $handler = new ContentHandler($this);
        return $handler->handle();
    }

    public function getOrderShippingCost($params, $shippingCost)
    {
        return 5.0;
    }

    public function getOrderShippingCostExternal($params): float|false
    {
        return $this->getOrderShippingCost($params, 0);
    }
}
