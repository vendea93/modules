<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: LoginFlow
Description: Truly Password-less & OTP-less one-tap login. Add mobile & email-based social login instantly.
Version: 1.0.0
Requires at least: 3.*
Author: Hopperstack
Author URI: https://codecanyon.net/user/hopperstack
*/

define('OTPLESS_MODULE_NAME', 'otpless');

// Register language files
register_language_files(OTPLESS_MODULE_NAME, [OTPLESS_MODULE_NAME]);

// Hook into admin initialization
hooks()->add_action('admin_init', 'otpless_init_menu_items');

// Hook to add OTPless login button on login page
hooks()->add_action('before_admin_login_form_close', 'admin_otpless_button');

/**
 * Initialize OTPless module menu items in the admin sidebar.
 */
function otpless_init_menu_items()
{
    if (is_admin()) {
        $CI = &get_instance();

        $CI->app_menu->add_sidebar_menu_item('otpless', [
            'name'     => _l('otpless'),
            'icon'     => 'fa fa-sign-in',
            'href'     => admin_url('otpless/manage'),
            'position' => 60,
        ]);
    }
}



// Get codeigniter instance
$CI = &get_instance();
// Load module helper file
$CI->load->helper(OTPLESS_MODULE_NAME.'/otpless');


/**
 * Display OTPless login button on the admin login form.
 */
function admin_otpless_button()
{
    if (get_option('otpless_functionality') == '1') {
        echo '<a class="btn btn-default btn-block" href="' . admin_url('otpless/login') . '">Login with OTPless</a>';
    }
}
