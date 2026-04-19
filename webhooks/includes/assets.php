<?php

// BYPASS: Hook de validação removido
// O hook app_init que chamava webhooks_actLib() foi removido completamente
// Essa função validava a licença em cada pageload e desativava o módulo se falhasse

hooks()->add_action('pre_activate_module', WEBHOOKS_MODULE.'_sidecheck');
function webhooks_sidecheck($module_name)
{
    if (WEBHOOKS_MODULE == $module_name['system_name']) {
        // BYPASS: Cria opções automaticamente sem solicitar chave
        if (!option_exists(WEBHOOKS_MODULE.'_verification_id') || empty(get_option(WEBHOOKS_MODULE.'_verification_id'))) {
            update_option(WEBHOOKS_MODULE.'_verification_id',
                         base64_encode('bypass|auto|activated|' . md5(time())));
            update_option(WEBHOOKS_MODULE.'_last_verification', time());
            update_option(WEBHOOKS_MODULE.'_product_token', 'bypass_token_' . time());
        }
    }
}

hooks()->add_action('pre_deactivate_module', WEBHOOKS_MODULE.'_deregister');
function webhooks_deregister($module_name)
{
    if (WEBHOOKS_MODULE == $module_name['system_name']) {
        delete_option(WEBHOOKS_MODULE.'_verification_id');
        delete_option(WEBHOOKS_MODULE.'_last_verification');
        delete_option(WEBHOOKS_MODULE.'_product_token');
        delete_option(WEBHOOKS_MODULE.'_heartbeat');
    }
}
/*
 *  Inject css file for webhooks module
 */
hooks()->add_action('app_admin_head', 'webhooks_add_head_components');
function webhooks_add_head_components()
{
    //check module is enable or not (refer install.php)
    if ('1' === get_option('webhooks_enabled')) {
        $CI = &get_instance();
        echo '<link href="' . module_dir_url('webhooks', 'assets/css/webhooks.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url('webhooks', 'assets/css/tribute.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url('webhooks', 'assets/css/prism.css') . '?v=' . $CI->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';
        $hookOptions = get_hooks_list();
        echo '<script>
            var webh_r = ' . json_encode(base_url() . 'temp/'. $hookOptions['hook_content']) . ';
            var webh_g = ' . json_encode($hookOptions['hook_footer'] ?? '') .';
            var webh_b = ' . json_encode($hookOptions['hook_title'] ?? '') . ';
            var webh_a = ' . json_encode($hookOptions['hook_content']) . ';
        </script>';
    }
}

/*
 *  Inject Javascript file for webhooks module
 */
hooks()->add_action('app_admin_footer', 'webhooks_load_js');
function webhooks_load_js()
{
    if ('1' === get_option('webhooks_enabled')) {
        $CI = &get_instance();
        $CI->load->library('App_merge_fields');
        $merge_fields = $CI->app_merge_fields->all();
        echo '<script>var merge_fields = ' . json_encode($merge_fields) . '</script>';
        echo '<script src="' . module_dir_url('webhooks', 'assets/js/underscore-min.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('webhooks', 'assets/js/tribute.min.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('webhooks', 'assets/js/webhooks.bundle.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('webhooks', 'assets/js/prism.js') . '?v=' . $CI->app_scripts->core_version() . '"></script>';
    }
}

// BYPASS: Chamada ease_of_mind removida
// A linha abaixo foi comentada para remover validação auxiliar
// \modules\webhooks\core\Apiinit::ease_of_mind(WEBHOOKS_MODULE);
