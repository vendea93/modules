<?php

/**
 * Ensures that the module init file can't be accessed directly, only within the application.
 */

defined('BASEPATH') or exit('No direct script access allowed');

if (
	function_exists('fq_saas_is_tenant')
	&& fq_saas_is_tenant()
	&& isset($_SERVER['REQUEST_URI'])
	&& strpos((string)$_SERVER['REQUEST_URI'], '/admin/authentication') !== false
) {
	return;
}

/*
Module Name: Catering Management Module
Description: Catering Management Module
Version: 1.0.0
Requires at least: 3.0.*
*/

const CATERING_MANAGEMENT_MODULE_NAME = 'catering_management_module';
$CI = &get_instance();
$CI->load->helper(CATERING_MANAGEMENT_MODULE_NAME.'/migration_log');
$CI->load->helper(CATERING_MANAGEMENT_MODULE_NAME.'/catering_management');

register_language_files('catering_management_module', ['catering_management_module']);

/**
 * Activation hook
 *
 * @return void
 */
function zegaware_cmm_module_activation_hook(): void
{
	$CI = &get_instance();
	require_once(__DIR__.'/install.php');
}

register_activation_hook(CATERING_MANAGEMENT_MODULE_NAME, 'zegaware_cmm_module_activation_hook');

/**
 * Load libraries
 *
 * @return void
 */
function zegaware_cmm_app_init_required_services(): void
{
	$CI = &get_instance();
	$CI->load->library(CATERING_MANAGEMENT_MODULE_NAME.'/Zegaware_license');
	$CI->load->library(CATERING_MANAGEMENT_MODULE_NAME.'/Events_kanban');
}

hooks()->add_action('app_init', 'zegaware_cmm_app_init_required_services');

function zegaware_cmm_module_register_uninstall_hook()
{
	if (get_option('zegaware_cmm_migrated_database'))
	{
		require_once APP_MODULES_PATH.'catering_management_module/migrations/100_version_100.php';

		$migration = new Migration_Version_100();
		$migration->down();
	}
}

register_uninstall_hook(CATERING_MANAGEMENT_MODULE_NAME, 'zegaware_cmm_module_register_uninstall_hook');

function cmm_add_license_link_to_module_list(array $action_links)
{
	$action_links[] = '<a href="'.admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/license').'">'._l('cmm_zegaware_license').'</a>';

	return $action_links;
}

hooks()->add_filter("module_catering_management_module_action_links", 'cmm_add_license_link_to_module_list');

function zegaware_cmm_menu_item()
{
	$CI = &get_instance();
	$validated = Zegaware_license::is_activated(CATERING_MANAGEMENT_MODULE_NAME);

	if ($validated)
	{
		if (staff_can('view', 'catering_events'))
		{
			$CI->app_menu->add_sidebar_menu_item('catering_management', [
				'slug' => 'events',
				'name' => _l('catering_management'),
				'href' => admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/events'),
				'position' => 30,
				'icon' => 'fa-solid fa-calendar-days',
				'badge' => [],
			]);
			$CI->app_menu->add_sidebar_children_item('catering_management', [
				'slug' => 'events',
				'name' => _l('cmm_events'),
				'href' => admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/events'),
				'position' => 1,
				'badge' => [],
			]);
		}
		if (staff_can('view', 'catering_event_types'))
		{
			$CI->app_menu->add_sidebar_children_item('catering_management', [
				'slug' => 'event_types',
				'name' => _l('cmm_event_types'),
				'href' => admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/event_types'),
				'position' => 2,
				'badge' => [],
			]);

		}

		if (staff_can('view', 'catering_menus'))
		{
			$CI->app_menu->add_sidebar_children_item('catering_management', [
				'slug' => 'menus',
				'name' => _l('menus'),
				'href' => admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menus'),
				'position' => 3,
				'badge' => [],
			]);
		}
		// Add Packages menu item
		if (staff_can('view', 'catering_packages'))
		{
			$CI->app_menu->add_sidebar_children_item('catering_management', [
				'slug' => 'packages',
				'name' => _l('packages'),
				'href' => admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/packages'),
				'position' => 4,
				'badge' => [],
			]);
		}
		if (staff_can('view', 'catering_menu_items'))
		{
			$CI->app_menu->add_sidebar_children_item('catering_management', [
				'slug' => 'menu_items',
				'name' => _l('menu_items'),
				'href' => admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menu_items'),
				'position' => 5,
				'badge' => [],
			]);
		}
		if (staff_can('view', 'catering_categories'))
		{
			$CI->app_menu->add_sidebar_children_item('catering_management', [
				'slug' => 'item_categories',
				'name' => _l('item_categories'),
				'href' => admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/categories'),
				'position' => 6,
				'badge' => [],
			]);
		}
		if (staff_can('view', 'catering_sections'))
		{
			$CI->app_menu->add_sidebar_children_item('catering_management', [
				'slug' => 'menu_sections',
				'name' => _l('menu_sections'),
				'href' => admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/sections'),
				'position' => 7,
				'badge' => [],
			]);
		}
		if (staff_can('view', 'catering_allergens'))
		{
			$CI->app_menu->add_sidebar_children_item('catering_management', [
				'slug' => 'allergens',
				'name' => _l('allergens'),
				'href' => admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/allergens'),
				'position' => 8,
				'badge' => [],
			]);
		}
		if (staff_can('view', 'catering_dietary_types'))
		{
			$CI->app_menu->add_sidebar_children_item('catering_management', [
				'slug' => 'dietary_types',
				'name' => _l('dietary_types'),
				'href' => admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/dietary_types'),
				'position' => 9,
				'badge' => [],
			]);
		}
	}
}

hooks()->add_action('admin_init', 'zegaware_cmm_menu_item', 20);

/**
 * Add custom permissions
 * @return void
 */
function zegaware_cmm_add_permissions()
{
	// Standard CRUD capabilities
	$capabilities = [
		'capabilities' => [
			'view' => _l('permission_view'),
			'create' => _l('permission_create'),
			'edit' => _l('permission_edit'),
			'delete' => _l('permission_delete'),
		],
	];

	// Register all module permissions
	register_staff_capabilities('catering_events', $capabilities, _l('catering_events'));
	register_staff_capabilities('catering_event_types', $capabilities, _l('event_types'));
	register_staff_capabilities('catering_menus', $capabilities, _l('menus'));
	register_staff_capabilities('catering_menu_items', $capabilities, _l('menu_items'));
	register_staff_capabilities('catering_menu_sections', $capabilities, _l('menu_sections'));
	register_staff_capabilities('catering_categories', $capabilities, _l('menu_categories'));
	register_staff_capabilities('catering_allergens', $capabilities, _l('allergens'));
	register_staff_capabilities('catering_dietary_types', $capabilities, _l('dietary_types'));
	register_staff_capabilities('catering_packages', $capabilities, _l('packages'));

	// Special permission - view only
	$view_only_capabilities = [
		'capabilities' => [
			'view' => _l('permission_view'),
		],
	];
	register_staff_capabilities('catering_view_costs', $view_only_capabilities, _l('catering_view_costs_permission'));
}

hooks()->add_action('admin_init', 'zegaware_cmm_add_permissions', 20);

/**
 * Add CSS in admin head
 */
if (!function_exists('zegaware_cmm_admin_head')) {
	function zegaware_cmm_admin_head()
	{
		$CI = &get_instance();

		if (strpos($_SERVER['REQUEST_URI'], 'catering_management') !== FALSE)
		{
			echo '<link href="'.module_dir_url(CATERING_MANAGEMENT_MODULE_NAME, 'assets/css/catering-management.css').'" rel="stylesheet">';
		}
	}
}

hooks()->add_action('app_admin_head', 'zegaware_cmm_admin_head', 20);

/**
 * Add JS in admin footer
 */
function zegaware_cmm_admin_footer()
{
	$CI = &get_instance();

	if (strpos($_SERVER['REQUEST_URI'], 'catering_management') !== FALSE)
	{
		echo '<script src="'.module_dir_url(CATERING_MANAGEMENT_MODULE_NAME, 'assets/js/catering-management.js').'"></script>';
	}
}

hooks()->add_action('app_admin_footer', 'zegaware_cmm_admin_footer', 20);
