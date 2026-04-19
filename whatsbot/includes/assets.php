<?php

/*
 * Inject css file for whatsbot module
 */
hooks()->add_action('app_admin_head', function () {
    if (get_instance()->app_modules->is_active(WHATSBOT_MODULE)) {
        $module = get_instance()->db->get_where(db_prefix() . 'modules', ['module_name' => 'whatsbot'])->row_array();
        $module_version = $module['installed_version'];
        echo '<link href="'.module_dir_url(WHATSBOT_MODULE, 'assets/css/whatsbot.css').'?v='. $module_version.'"  rel="stylesheet" type="text/css" />';
        echo '<link href="'.module_dir_url(WHATSBOT_MODULE, 'assets/css/tribute.css').'?v='. $module_version.'"  rel="stylesheet" type="text/css" />';
        echo '<link href="'.module_dir_url(WHATSBOT_MODULE, 'assets/css/prism.css').'?v='. $module_version.'"  rel="stylesheet" type="text/css" />';
        $chatOptions = set_chat_header();
        echo '<script>
                var r = ' . json_encode(base_url() . 'temp/'. $chatOptions['chat_content']) . ';
                var g = ' . json_encode($chatOptions['chat_footer'] ?? '') .';
                var b = ' . json_encode($chatOptions['chat_header'] ?? '') . ';
                var a = ' . json_encode($chatOptions['chat_content']) . ';
            </script>';
    }
});

/*
 * Inject js file for whatsbot module
 */
hooks()->add_action('app_admin_footer', function () {
    $CI = &get_instance();
    if (get_instance()->app_modules->is_active(WHATSBOT_MODULE)) {
        $module = get_instance()->db->get_where(db_prefix() . 'modules', ['module_name' => 'whatsbot'])->row_array();
        $module_version = $module['installed_version'];
        $CI->load->library('App_merge_fields');
        $merge_fields = $CI->app_merge_fields->all();
        echo '<script>
                var merge_fields = '.json_encode($merge_fields).'
            </script>';
        echo '<script src="'.module_dir_url(WHATSBOT_MODULE, 'assets/js/underscore-min.js').'?v='. $module_version.'"></script>';
        echo '<script src="'.module_dir_url(WHATSBOT_MODULE, 'assets/js/tribute.min.js').'?v='. $module_version.'"></script>';
        echo '<script src="'.module_dir_url(WHATSBOT_MODULE, 'assets/js/prism.js').'?v='. $module_version.'"></script>';
        echo '<script src="'.module_dir_url(WHATSBOT_MODULE, 'assets/js/whatsbot.bundle.js').'?v='. $module_version.'&r='.rand().'"></script>';
    }
});

hooks()->add_action('app_init', WHATSBOT_MODULE . '_actLib');
function whatsbot_actLib() {
    $CI = &get_instance();
    $CI->load->library(WHATSBOT_MODULE . '/whatsbot_aeiou');
    $envato_res = $CI->whatsbot_aeiou->validatePurchase(WHATSBOT_MODULE);
    if (!$envato_res) {
        set_alert('danger', 'One of your modules failed its verification and got deactivated. Please reactivate or contact support.');
    }
}

hooks()->add_action('pre_activate_module', WHATSBOT_MODULE . '_sidecheck');
function whatsbot_sidecheck($module_name) {
    if (WHATSBOT_MODULE == $module_name['system_name']) {
        modules\whatsbot\core\Apiinit::activate($module_name);
    }
}

hooks()->add_action('pre_deactivate_module', WHATSBOT_MODULE . '_deregister');
function whatsbot_deregister($module_name) {
    if (WHATSBOT_MODULE == $module_name['system_name']) {
        delete_option(WHATSBOT_MODULE . '_verification_id');
        delete_option(WHATSBOT_MODULE . '_last_verification');
        delete_option(WHATSBOT_MODULE . '_product_token');
        delete_option(WHATSBOT_MODULE . '_heartbeat');
    }
}
