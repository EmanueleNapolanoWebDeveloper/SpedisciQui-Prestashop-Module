<?php

if (!defined('_PS_VERSION_')) {
    exit;
}


class SpedisciQuiShipment
{

    protected $module;

    public function __construct()
    {
        $this->module = new spedisciquishipping();
    }
}
