<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: AI Agent Chat for Perfex CRM
Description: No-code OpenAI ChatKit integration. Add a branded chat bubble to Admin & Client areas, connect Workflows, assign visibility to staff/customers, and theme it with a live builder.
Version: 1.0.0
Author: Lenzcreative
Author URI: https://codecanyon.net/user/lenzcreativee/portfolio
Requires at least: 1.0.*
*/

define('AIAGENTCHAT_MODULE_NAME', 'aiagentchat');

hooks()->add_action('admin_init', 'aiagentchat_module_init_menu_items');
hooks()->add_action('admin_init', 'aiagentchat_permissions');
hooks()->add_action('aiagentchat_init', AIAGENTCHAT_MODULE_NAME . '_appint');
hooks()->add_action('pre_activate_module', AIAGENTCHAT_MODULE_NAME . '_preactivate');
hooks()->add_action('pre_deactivate_module', AIAGENTCHAT_MODULE_NAME . '_predeactivate');
hooks()->add_action('pre_uninstall_module', AIAGENTCHAT_MODULE_NAME . '_uninstall');

/**
 * Load the module helper
 */
$CI = &get_instance();
$CI->load->helper(AIAGENTCHAT_MODULE_NAME . '/aiagentchat');

function aiagentchat_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
        'view' => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
        'assign_chat' => _l('aiagentchat_assignments'),
    ];
    register_staff_capabilities('aiagentchat', $capabilities, _l('aiagentchat'));
}

/**
 * Register activation module hook
 */
register_activation_hook(AIAGENTCHAT_MODULE_NAME, 'aiagentchat_module_activation_hook');

function aiagentchat_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(AIAGENTCHAT_MODULE_NAME, [AIAGENTCHAT_MODULE_NAME]);

/**
 * Init module menu items in setup in admin_init hook
 * @return null
 */
function aiagentchat_module_init_menu_items()
{
    $CI = &get_instance();

    if (has_permission('aiagentchat', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('aiagentchat', [
            'slug' => 'aiagentchat',
            'name' => _l('aiagentchat_menu_title'),
            'position' => 6,
            'icon' => 'fas fa-robot',
        ]);

        $CI->app_menu->add_sidebar_children_item('aiagentchat', [
            'slug' => 'aiagentchat-manage',
            'name' => _l('aiagentchat_back_to_manage'),
            'position' => 1,
            'icon' => 'fas fa-comments',
            'href' => admin_url('aiagentchat'),
        ]);
    }

    if (is_admin()) {
        $CI->app_menu->add_sidebar_children_item('aiagentchat', [
            'slug' => 'aiagentchat-settings',
            'name' => _l('settings'),
            'position' => 2,
            'icon' => 'fas fa-sliders-h',
            'href' => admin_url('aiagentchat/settings'),
        ]);
    }
}

hooks()->add_filter('module_' . AIAGENTCHAT_MODULE_NAME . '_action_links', 'aiagentchat_add_action_links');
function aiagentchat_add_action_links($action_links)
{
    $settingsUrl = admin_url(AIAGENTCHAT_MODULE_NAME . '/settings');
    $settingsLabel = _l('settings');

    $action_links[] = '<a href="' . $settingsUrl . '">' . $settingsLabel . '</a>';

    $action_links[] = '<a href="https://lenzcreative.net/perfex-crm-development-maintenance-services/" target="_blank">CRM Support</a>';

    $action_links[] = '<a href="mailto:lenzcreativee@hotmail.com">Email Lenzcreative</a>';

    return $action_links;
}

hooks()->add_action('app_admin_footer', 'aiagentchat_load_js');
function aiagentchat_load_js()
{
    include_once module_dir_path(AIAGENTCHAT_MODULE_NAME, 'views/admin/chat_widget.php');
}

hooks()->add_action('app_customers_footer', function () {
    if (!is_client_logged_in()) {
        return;
    }
    include_once module_dir_path(AIAGENTCHAT_MODULE_NAME, 'views/admin/chat_widget.php');
});

function aiagentchat_appint()
{
  
}

function aiagentchat_preactivate($module_name)
{
    if ($module_name['system_name'] == AIAGENTCHAT_MODULE_NAME) {

    }
}

function aiagentchat_predeactivate($module_name)
{
    if ($module_name['system_name'] == AIAGENTCHAT_MODULE_NAME) {

    }
}

function aiagentchat_uninstall($module_name)
{
    if ($module_name['system_name'] == AIAGENTCHAT_MODULE_NAME) {

    }
}
