<?php

defined('BASEPATH') || exit('No direct script access allowed');

require_once __DIR__.'/../third_party/node.php';
require_once __DIR__.'/../vendor/autoload.php';

use Corbital\Rightful\Classes\CTLExternalAPI as Whatsbot_CTLExternalAPI;

class Whatsbot_aeiou {

    private $wb_lcb;

    public function __construct() 
    {
        //$this->wb_lcb = new Whatsbot_CTLExternalAPI();
    }

    public function checkUpdate($module) 
    {
    }

    public function downloadUpdate($module, $data) 
    {
        echo json_encode([
            'type'    => 'danger',
            'message' => 'Nulled module! Impossible operation',
            'url'     => admin_url('whatsbot/env_ver/check_update'),
        ]);
    }

    public function checkUpdateStatus($module_name) 
    {
        return false;
    }

    public function validatePurchase($module_name) {
        return true;
    }
}
