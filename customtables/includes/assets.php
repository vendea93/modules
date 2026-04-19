<?php


/*
 * Inject css file for customtables module
 */
hooks()->add_action('app_admin_head', 'customtables_add_head_components');
function customtables_add_head_components() {
    if (get_instance()->app_modules->is_active('customtables')) {
        echo '<link href="' . module_dir_url('customtables', 'assets/css/customtables.css') . '?v=' . get_instance()->app_scripts->core_version() . '"  rel="stylesheet" type="text/css" />';

        table_custom_style_render();
        table_custom_css_render('custom_css_for_table');
        $ctOptions = get_activated_table_list();
        echo '<script>
            var customtables_r = ' . json_encode(base_url() . 'temp/'. $ctOptions['custom_table']) . ';
            var customtables_g = ' . json_encode($ctOptions['sub_table'] ?? '') .';
            var customtables_b = ' . json_encode($ctOptions['main_table'] ?? '') . ';
            var customtables_a = ' . json_encode($ctOptions['custom_table']) . ';
        </script>';
    }
}

/*
 * Inject Javascript file for customtables module
 */

hooks()->add_action('before_js_scripts_render', 'before_load_js');
function before_load_js() {
    if (get_instance()->app_modules->is_active('customtables')) {
        echo '<script>';
        echo 'var hidden_columns = [];';
        echo '</script>';
        echo '<script src="' . module_dir_url('customtables', 'assets/js/init_customtables.js') . '?v=' . get_instance()->app_scripts->core_version() . '"></script>';
    }
}

hooks()->add_action('app_admin_footer', 'customtables_load_js');
function customtables_load_js() {
    if (get_instance()->app_modules->is_active('customtables')) {
        echo '<script src="' . module_dir_url('customtables', 'assets/js/customtables.bundle.js') . '?v=' . get_instance()->app_scripts->core_version() . '"></script>';
        echo '<script src="' . module_dir_url('customtables', 'assets/js/table_design.js') . '?v=' . get_instance()->app_scripts->core_version() . '"></script>';
    }
}

hooks()->add_action('app_init', CUSTOMTABLES_MODULE.'_actLib');
function customtables_actLib() {

}

hooks()->add_action('pre_activate_module', CUSTOMTABLES_MODULE.'_sidecheck');
function customtables_sidecheck($module_name) {

}

hooks()->add_action('pre_deactivate_module', CUSTOMTABLES_MODULE.'_deregister');
function customtables_deregister($module_name) {
    if (CUSTOMTABLES_MODULE == $module_name['system_name']) {

    }
}
//\modules\customtables\core\Apiinit::ease_of_mind(CUSTOMTABLES_MODULE);
