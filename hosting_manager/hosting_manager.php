<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Module Name: Hosting Manager
 * Description: Manage hosting with expiry tracking and client/project linking.
 * Version: 1.0.0
 * Requires at least: 3.0.*
 * Author: Hopperstack
 * Author URI: https://codecanyon.net/user/hopperstack
 */

define('HOSTING_MANAGER_MODULE_NAME', 'hosting_manager');
define('VERSION_HOSTING_MANAGER', 100);
define('HOSTING_MANAGER_MODULE_NAME_ITEM_ID', 'Hosting Manager');

// Hooks
hooks()->add_action('admin_init', 'hosting_manager_init_menu_items');
hooks()->add_action('admin_init', 'hosting_manager_define_permissions');
hooks()->add_filter('module_hosting_manager_action_links', 'hosting_manager_add_action_links');
hooks()->add_filter('before_start_render_content', 'hosting_manager_display_verification_warning');

$CI = &get_instance();

// Register language files
register_language_files(HOSTING_MANAGER_MODULE_NAME, [HOSTING_MANAGER_MODULE_NAME]);

// Load helper functions
$CI->load->helper(HOSTING_MANAGER_MODULE_NAME . '/hosting_manager');

/**
 * Initialize Domain Manager module menu items.
 */
function hosting_manager_init_menu_items()
{
    $CI = &get_instance();
    
    if (has_permission(HOSTING_MANAGER_MODULE_NAME, '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item(HOSTING_MANAGER_MODULE_NAME, [
            'name'     => _l('hosting_manager'),
            'icon'     => 'fa fa-server',
            'href'     => admin_url('hosting_manager'),
            'position' => 30,
        ]);



    }

    if (has_permission(HOSTING_MANAGER_MODULE_NAME, '', 'view')) {
        $CI->app_tabs->add_project_tab('hosting_manager_projects', [
            'name'     => _l('hosting_manager_projects'),
            'icon'     => 'fa fa-server',
            'view'     => 'hosting_manager/admin/project_hosting_manager',
            'position' => 10,
        ]);
    }

    if (has_permission(HOSTING_MANAGER_MODULE_NAME, '', 'view')) {
        $CI->app_tabs->add_customer_profile_tab('hosting_manager_projects', [
            'name'     => _l('hosting_manager_projects'),
            'icon'     => 'fa fa-server',
            'view'     => 'hosting_manager/admin/client_hosting_manager',
            'position' => 10,
        ]);
    }
    if (is_admin()) {
        $CI->app_menu->add_setup_menu_item(HOSTING_MANAGER_MODULE_NAME, [
            'slug'     => 'hosting_manager_setting',
            'name'     => _l('hosting_manager_setting'),
            'href'     => admin_url('hosting_manager/setting'),
            'position' => 35,
        ]);
    }
}

/**
 * Module activation hook.
 */
register_activation_hook(HOSTING_MANAGER_MODULE_NAME, 'hosting_manager_activate_module');

function hosting_manager_activate_module()
{
    require_once __DIR__ . '/install.php';
}

/**
 * Display purchase code verification warning.
 */
function hosting_manager_display_verification_warning()
{
    if (get_option('hosting_manager_purchase_is_valid') != 1 && is_admin()) {
        echo '<div class="col-lg-12 mt-2">';
        echo '    <div class="alert alert-warning">';
        echo '        <h4>' . _l('hosting_manager') . ' - ' . _l('verify_purchase_before_proceeding') . '</h4>';
        echo '        <p><a href="' . admin_url('hosting_manager/setting') . '">' . _l('click_here_to_verify') . '</a></p>';
        echo '    </div>';
        echo '</div>';
    }
}

/**
 * Define module permissions.
 */
function hosting_manager_define_permissions()
{
    $capabilities = [
        'capabilities' => [
            'view'   => _l('permission_view') . ' (' . _l('permission_global') . ')',
            'detail_view'   => _l('detail_permission_view') . ' (' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
            'domain_view'   => _l('domain_permission_view') . ' (' . _l('permission_global') . ')',
            'domain_create' => _l('domain_permission_create'),
            'domain_edit'   => _l('domain_permission_edit'),
            'domain_delete' => _l('domain_permission_delete'),
            'database_view'   => _l('database_permission_view') . ' (' . _l('permission_global') . ')',
            'database_create' => _l('database_permission_create'),
            'database_edit'   => _l('database_permission_edit'),
            'database_delete' => _l('database_permission_delete'),
            'ftp_view'   => _l('ftp_permission_view') . ' (' . _l('permission_global') . ')',
            'ftp_create' => _l('ftp_permission_create'),
            'ftp_edit'   => _l('ftp_permission_edit'),
            'ftp_delete' => _l('ftp_permission_delete'),
            
            
            
        ],
    ];

    register_staff_capabilities(HOSTING_MANAGER_MODULE_NAME, $capabilities, _l('hosting_manager'));
}

/**
 * Add settings link in module list.
 *
 * @param array $actions Current actions.
 * @return array
 */
function hosting_manager_add_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('hosting_manager/setting') . '">' . _l('settings') . '</a>';
    return $actions;
}
