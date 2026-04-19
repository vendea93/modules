<?php

defined('BASEPATH') || exit('No direct script access allowed');
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__.'/../third_party/node.php';
use Firebase\JWT\JWT as Customemailandsmsnotifications_JWT;
use Firebase\JWT\Key as Customemailandsmsnotifications_Key;
use WpOrg\Requests\Requests as Customemailandsmsnotifications_Requests;

class Customemailandsmsnotifications_aeiou
{
    public static function getPurchaseData($code)
    {
        return false;
    }

    public static function verifyPurchase($code)
    {
        return true;
    }

    public function validatePurchase($module_name)
    {
        return true;
    }
}
