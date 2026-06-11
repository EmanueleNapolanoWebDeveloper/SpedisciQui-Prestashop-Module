<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class PackageRenderer
{


    private spedisciquishipping $module;
    private PackageRepository   $packRepo;


    // =============================================
    // COSTRUTTORE
    // =============================================
    public function __construct(
        spedisciquishipping $module,
        PackageRepository   $packRepo
    ) {
        $this->module   = $module;
        $this->packRepo = $packRepo;
    }



    // =============================================
    // RENDERIZZA FORM PER PACKAGE - INZIO
    // =============================================
    public function renderPackageForm(): string
    {
        $context = Context::getContext();

        // Recupera il pacco default (o valori vuoti se non esiste)
        $package = $this->packRepo->getDefault() ?? [
            'name'       => 'Default',
            'weight'     => '1.000',
            'length'     => '30.00',
            'width'      => '20.00',
            'height'     => '10.00',
            'is_default' => 1,
        ];

        $action = AdminController::$currentIndex
            . '&configure=' . $this->module->name
            . '&token='     . Tools::getAdminTokenLite('AdminModules');

        $context->smarty->assign([
            'package' => $package,
            'action'  => $action,
        ]);

        return $this->module->display(
            $this->module->getLocalPath(),
            'views/templates/admin/_partials/_initial/package_init.tpl'
        );
    }
    // =============================================
    // RENDERIZZA FORM PER PACKAGE - FINE
    // =============================================
}
