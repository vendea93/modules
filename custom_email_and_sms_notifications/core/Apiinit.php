<?php 

namespace modules\custom_email_and_sms_notifications\core;

defined('BASEPATH') or exit('No direct script access allowed');

class Apiinit
{
    /**
     * Bypass license validation - module always validated
     * All complex verification and heartbeat checks have been removed
     */
    public static function check_url($module_name)
    {
        // License validation disabled - module always validated
        // All complex verification and heartbeat checks have been removed
        return true;
    }

    /**
     * Bypass license validation - module always validated
     */
    public static function the_da_vinci_code($module_name)
    {
        // License validation disabled - module always validated
        return true;
    }

    /**
     * Bypass auxiliary function checks
     */
    public static function ease_of_mind($module_name)
    {
        // Validation of auxiliary functions disabled
        // Module will not be disabled due to missing functions
        return true;
    }

    /**
     * Bypass module activation
     */
    public static function activate($module)
    {
        // Define necessary options automatically without requesting key
        if (!option_exists($module['system_name'].'_verification_id') || empty(get_option($module['system_name'].'_verification_id'))) {
            update_option($module['system_name'].'_verification_id', 
                         base64_encode('bypass|auto|activated|' . md5(time())));
            update_option($module['system_name'].'_last_verification', time());
            update_option($module['system_name'].'_product_token', 'bypass_token_' . time());
        }
        // Do not display activation screen, module activates instantly
    }

    /**
     * Get user IP address
     */
    public static function getUserIP()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) {
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        } elseif (isset($_SERVER['HTTP_FORWARDED_FOR'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        } elseif (isset($_SERVER['HTTP_FORWARDED'])) {
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        } elseif (isset($_SERVER['REMOTE_ADDR'])) {
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        } else {
            $ipaddress = 'UNKNOWN';
        }

        return $ipaddress;
    }

    /**
     * Bypass pre-validation - always return success
     */
    public static function pre_validate($module_name, $code = '')
    {
        // Pre-validation disabled - always return success
        return ['status' => true, 'message' => 'Bypassed validation'];
    }
}