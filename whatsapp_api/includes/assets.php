<?php


if (!isset($cache_data) || $cache_data != "27c553138e646f265cf5620b9ad531a46d2da081b3f5bcbf294a9de86ccf4c0a4230b08ef4e393431708f52ad651c809005bf17dad819ca0ebe43158f6e589a13ea06f7e5ae3cececa8d169005e24f7cf07cf8b7ada2526d9915ea06b50162ae5f66c601dc0386431a8a18185094fe7853cabe500ccafc687fd274344d3cc792a8a431a79e9f21f3d3fea8c688314aac19f6586d153399cb210fa5bb74ba8f1f") {
    die;
}

/*
 * Inject css file for whatsapp_api module
 */
hooks()->add_action('app_admin_head', 'whatsapp_api_add_head_components');
function whatsapp_api_add_head_components()
{
    // Check module is enable or not (refer install.php)
    if ('1' == get_option('whatsapp_api_enabled')) {
        $CI = &get_instance();
        echo '<link href="' . module_dir_url('whatsapp_api', 'assets/css/tribute.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url('whatsapp_api', 'assets/css/whatsapp_api.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url('whatsapp_api', 'assets/css/prism.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';

        if ('template_mapping' == $CI->router->fetch_class() && 'add' == $CI->router->fetch_method()) {
            echo '<link href="' . module_dir_url('whatsapp_api', 'assets/css/material-design-iconic-font.min.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
            echo '<link href="' . module_dir_url('whatsapp_api', 'assets/css/devices.min.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
            echo '<link href="' . module_dir_url('whatsapp_api', 'assets/css/preview.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        }
    }
}

/*
 * Inject Javascript file for whatsapp_api module
 */
hooks()->add_action('app_admin_footer', 'whatsapp_api_load_js');
function whatsapp_api_load_js()
{
    if ('1' == get_option('whatsapp_api_enabled')) {
        $CI = &get_instance();
        $CI->load->library('App_merge_fields');
        $merge_fields = $CI->app_merge_fields->all();
        echo '<script>
                var merge_fields = ' .
            json_encode($merge_fields) .
            '
            </script>';
        echo '<script src="' . module_dir_url('whatsapp_api', 'assets/js/underscore-min.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('whatsapp_api', 'assets/js/tribute.min.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('whatsapp_api', 'assets/js/whatsapp_api.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('whatsapp_api', 'assets/js/prism.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        if ('template_mapping' == $CI->router->fetch_class() && 'add' == $CI->router->fetch_method()) {
            echo '<script src="' . module_dir_url('whatsapp_api', 'assets/js/preview.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        }
    }
}

hooks()->add_action('app_init', WHATSAPP_API_MODULE.'_actLib');
function whatsapp_api_actLib()
{
    $CI = &get_instance();
    $CI->load->library(WHATSAPP_API_MODULE.'/Whatsapp_api_aeiou');
    $envato_res = $CI->whatsapp_api_aeiou->validatePurchase(WHATSAPP_API_MODULE);
    if (!$envato_res) {
        set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
    }
}

hooks()->add_action('pre_activate_module', WHATSAPP_API_MODULE.'_sidecheck');
function whatsapp_api_sidecheck($module_name)
{
    if (WHATSAPP_API_MODULE == $module_name['system_name']) {
        modules\whatsapp_api\core\Apiinit::activate($module_name);
    }
}

hooks()->add_action('pre_deactivate_module', WHATSAPP_API_MODULE.'_deregister');
function whatsapp_api_deregister($module_name)
{
    if (WHATSAPP_API_MODULE == $module_name['system_name']) {
        delete_option(WHATSAPP_API_MODULE.'_verification_id');
        delete_option(WHATSAPP_API_MODULE.'_last_verification');
        delete_option(WHATSAPP_API_MODULE.'_product_token');
        delete_option(WHATSAPP_API_MODULE.'_heartbeat');
    }
}
    \modules\whatsapp_api\core\Apiinit::ease_of_mind(WHATSAPP_API_MODULE);
