<?php

/*
 * Inject sidebar menu and links for customtables module
 */
hooks()->add_action('admin_init', function () {

    if (has_permission('customtables', '', 'view')) {
        get_instance()->app_menu->add_setup_menu_item('customtables', [
            'slug' => 'customtables',
            'name' => _l('customtables'),
            'icon' => '',
            'position' => 35,
        ]);
        get_instance()->app_menu->add_setup_children_item('customtables', [
            'slug' => 'tablecustomize',
            'name' => _l('tablecustomize'),
            'href' => admin_url('customtables/index'),
            'position' => 28,
        ]);
        get_instance()->app_menu->add_setup_children_item('customtables', [
            'slug' => 'table_design',
            'name' => _l('table_design'),
            'href' => admin_url('customtables/tableDesign'),
            'position' => 29,
        ]);
    }

    //\modules\customtables\core\Apiinit::ease_of_mind(CUSTOMTABLES_MODULE);
});

hooks()->add_action('module_deactivated', function($module_name) {
    if (CUSTOMTABLES_MODULE == $module_name['system_name']) {
        $url = basename(get_instance()->app_modules->get(CUSTOMTABLES_MODULE)['headers']['uri']) . '-' . trim(preg_replace(['#/admin.*#','#https?://#', '/[^a-zA-Z0-9]+/'], ['', '', '-'], current_full_url()), '-');
        write_file(TEMP_FOLDER . $url . '.lic', '');
        echo '<script>
            var _customtablescss = "' . $url . '.lic"' . ';
            sessionStorage.setItem(_customtablescss, "");
        </script>';
    }
});
