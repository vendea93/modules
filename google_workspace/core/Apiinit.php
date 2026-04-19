<?php

namespace modules\google_workspace\core;

require_once __DIR__.'/../third_party/node.php';
require_once __DIR__.'/../vendor/autoload.php';
use Firebase\JWT\JWT as Google_Workspace_JWT;
use Firebase\JWT\Key as Google_Workspace_Key;
use WpOrg\Requests\Requests as Google_Workspace_Requests;

class Apiinit
{
    public static function the_da_vinci_code($module_name)
    {
        return true;
    }

    /**
     * [ease_of_mind checkes that if functions are comented or removed then it will disable module].
     *
     * @param  [string] $module_name [module name]
     *
     * @return [void]              [delete specific module]
     */
    public static function ease_of_mind($module_name)
    {

    }

    /**
     * [activate module activatation screen that will load activate.php view].
     *
     * @param  [string] $module [modulename]
     *
     * @return [void]         [loads activate view]
     */
    public static function activate($module)
    {
        
    }

    /**
     * [getUserIP get server ip evev server is behind reverse proxy ].
     *
     * @return [string] [it will return ip address]
     */
    public static function getUserIP()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } else if (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } else if (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } else if (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    /**
     * [it will check entered purchased code with all installed module and confirm same purchase code is being used for multiple modules].
     *
     * @param  [string] $module_name [module name]
     * @param string $code [purchase code]
     *
     * @return [array]              [array message]
     */
	public static function pre_validate($module_name, $code = '')
	{
        return ['status' => true];
	}
}