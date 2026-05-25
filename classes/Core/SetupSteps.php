<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

class SetupSteps
{
    const TOKEN  = 0;
    const SENDER = 1;
    const PACKAGE = 2;
    const CARRIER = 3;
    const DONE   = 4;
}