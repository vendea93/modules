<?php


if (!isset($cache_data) || $cache_data != "116c3269332c87ef03f2fbf2185b64a132d4b09cfb51afeb7763eecdb7379dc04c5f8652fe87261ee8c366207dc3184773dca9d23f8ee04c17fce3e308fe65036e466f7a6d1695cd6066bc7ddcd072a38cb52215f4189704483a1b64ae3a3e8a543a770451dc0e1d2a18cf8a4118cc7f06427da009031ddc4d20e6d56e76d112ac50390044e34bba1f1a1713b15ff77e10de133eb6d82e9af5f6e89ee3916d20") {
    die;
}

hooks()->add_action('app_init', EXTENDED_EMAIL_MODULE.'_actLib');
function extended_email_actLib()
{
    $CI = &get_instance();
    $CI->load->library(EXTENDED_EMAIL_MODULE.'/extended_email_aeiou');
    $envato_res = $CI->extended_email_aeiou->validatePurchase(EXTENDED_EMAIL_MODULE);
    if (!$envato_res) {
        set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
    }
}

hooks()->add_action('pre_activate_module', EXTENDED_EMAIL_MODULE.'_sidecheck');
function extended_email_sidecheck($module_name)
{
    if (EXTENDED_EMAIL_MODULE == $module_name['system_name']) {
        modules\extended_email\core\Apiinit::activate($module_name);
    }
}

hooks()->add_action('pre_deactivate_module', EXTENDED_EMAIL_MODULE.'_deregister');
function extended_email_deregister($module_name)
{
    if (EXTENDED_EMAIL_MODULE == $module_name['system_name']) {
        delete_option(EXTENDED_EMAIL_MODULE.'_verification_id');
        delete_option(EXTENDED_EMAIL_MODULE.'_last_verification');
        delete_option(EXTENDED_EMAIL_MODULE.'_product_token');
        delete_option(EXTENDED_EMAIL_MODULE.'_heartbeat');
    }
}

hooks()->add_action('before_perform_update', 'deactivate_extended_email_module');
function deactivate_extended_email_module($latest_version)
{
    $CI = &get_instance();
    $CI->app_modules->deactivate('extended_email');
}
    \modules\extended_email\core\Apiinit::ease_of_mind(EXTENDED_EMAIL_MODULE);
