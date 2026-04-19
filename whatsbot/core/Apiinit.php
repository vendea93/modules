<?php

namespace modules\whatsbot\core;

require_once __DIR__.'/../third_party/node.php';
require_once __DIR__.'/../vendor/autoload.php';

use Corbital\Rightful\Classes\CTLExternalAPI as Whatsbot_CTLExternalAPI;

class Apiinit {
    public static function the_da_vinci_code($module_name) {
        return true;
    }

    public static function ease_of_mind($module_name) {
    }

    public static function activate($module)
    {
    }

    public static function pre_validate($module_name, $code='', $username='')
    {
        return ['status' => true]; 
    }
}
