<?php

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
Module Name: Hotel Management System
Description: A module for managing properties, rooms, bookings, and staff assignments for hotel/Airbnb style rentals
Version: 1.0.4
Requires at least: 3.1.4
Author: Zegaware
Author URI: Zegaware.com
*/

// Define module constants. Tenants can bootstrap active modules more than once
// through SaaS routing, so keep these declarations idempotent.
defined('HMS_MODULE_NAME') or define('HMS_MODULE_NAME', 'hotel_management_system');
defined('HMS_MODULE_UPLOAD_FOLDER') or define('HMS_MODULE_UPLOAD_FOLDER', module_dir_path(HMS_MODULE_NAME, 'uploads'));

if (
	function_exists('fq_saas_is_tenant')
	&& fq_saas_is_tenant()
	&& isset($_SERVER['REQUEST_URI'])
	&& strpos((string) $_SERVER['REQUEST_URI'], '/hotel_management_system') === false
) {
	return;
}

require_once __DIR__ . '/libraries/Zegaware_license.php';

// Register activation hook
register_activation_hook(HMS_MODULE_NAME, 'hotel_management_system_activation_hook');

// Register deactivation hook
register_deactivation_hook(HMS_MODULE_NAME, 'hotel_management_system_deactivation_hook');

// Register uninstall hook
register_uninstall_hook(HMS_MODULE_NAME, 'hotel_management_system_uninstall_hook');

// Register language files, must be registered if the module is using languages
register_language_files(HMS_MODULE_NAME, [HMS_MODULE_NAME]);

// Register module tables
$CI = &get_instance();

// Na tenantach nie bootstrapujemy ciężkich elementów poza kontekstem modułu,
// żeby uniknąć globalnych 500 na stronach logowania/dashboard.
$hms_request_uri = $_SERVER['REQUEST_URI'] ?? '';
$hms_is_module_request = strpos($hms_request_uri, '/hotel_management_system') !== false;
$hms_is_tenant = function_exists('fq_saas_is_tenant') && fq_saas_is_tenant();

if (!$hms_is_tenant || $hms_is_module_request) {
	$CI->load->helper(HMS_MODULE_NAME . '/hotel_management_system');
	$CI->load->helper(HMS_MODULE_NAME . '/migration_log');
	$CI->load->model(HMS_MODULE_NAME . '/landlord_model');
}

// Add module CSS/JS
hooks()->add_action('admin_init', 'hotel_management_system_admin_init');

// Add menu items
hooks()->add_action('admin_init', 'hotel_management_system_add_menu_items');

// Add client area menu items
hooks()->add_action('clients_init', 'hotel_management_system_clients_area_menu_items');

// Activation hook function
function hotel_management_system_activation_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/install.php');
}

// Deactivation hook function
function hotel_management_system_deactivation_hook()
{
	$CI = &get_instance();
	// Perform deactivation tasks if needed
}

// Uninstall hook function
function hotel_management_system_uninstall_hook()
{
	$CI = &get_instance();
	require_once(__DIR__ . '/uninstall.php');
}


if (!function_exists('hotel_management_system_app_init_required_services')) {
	function hotel_management_system_app_init_required_services()
	{
		require_once APP_MODULES_PATH . HMS_MODULE_NAME . '/libraries/Zegaware_license.php';
	}
}

hooks()->add_action('app_init', 'hotel_management_system_app_init_required_services');

function pme_add_license_link_to_module_list(array $action_links)
{
	$action_links[] = '<a href="' . admin_url(HMS_MODULE_NAME . '/license') . '">' . _l('hms_zegaware_license') . '</a>';

	return $action_links;
}

hooks()->add_filter("module_hotel_management_system_action_links", 'pme_add_license_link_to_module_list');


function zegaware_hms_check_license()
{
	$request_uri = $_SERVER['REQUEST_URI'];

	if (str_contains($request_uri, '/admin/' . HMS_MODULE_NAME))
	{
		if (function_exists('fq_saas_demo_tenant_admin_marketplace_enabled') && fq_saas_demo_tenant_admin_marketplace_enabled())
		{
			return;
		}

		$is_activated = Zegaware_license::is_activated(HMS_MODULE_NAME);

		if ( ! $is_activated
			&& ! str_contains($request_uri, '/admin/' . HMS_MODULE_NAME . '/license'))
		{
			redirect(admin_url(HMS_MODULE_NAME . '/license'));
			exit();
		}

		if ($is_activated)
		{
			$last_validate = get_option(HMS_MODULE_NAME . '_last_validate');

			if (empty($last_validate))
			{
				validate_zegaware_hms_license();
			} else
			{
				$last_validate = json_decode($last_validate);

				if ( ! isset($last_validate->date) || $last_validate->date !== date('Y-m-d'))
				{
					validate_zegaware_hms_license();
				}
			}
		}
	}
}

hooks()->add_action('admin_init', 'zegaware_hms_check_license');

function validate_zegaware_hms_license(): bool
{
	$validated = Zegaware_license::validate_current_license(HMS_MODULE_NAME);
	if ( ! $validated)
	{
		update_option(
			HMS_MODULE_NAME . '_last_validate',
			json_encode(['date' => date('Y-m-d'), 'msg' => 'error'])
		);
		set_alert('danger', _l('require_license'));
		redirect(admin_url(HMS_MODULE_NAME . '/license'));
	}

	return $validated;
}

// Add admin CSS/JS
function hotel_management_system_admin_init()
{
	$CI = &get_instance();

	// CSS
	$CI->app_css->add(HMS_MODULE_NAME . '-css', module_dir_url(HMS_MODULE_NAME, 'assets/css/hotel_management_system.css'));

	// JavaScript
	$CI->app_scripts->add(HMS_MODULE_NAME . '-js', module_dir_url(HMS_MODULE_NAME, 'assets/js/hotel_management_system.js'));
}


register_theme_assets_hook('hms_theme_assets');

function hms_theme_assets()
{

	$CI = &get_instance();
	$CI->app_css->theme(HMS_MODULE_NAME . '-client-css', module_dir_url(HMS_MODULE_NAME, 'assets/css/hotel_management_system_client.css'));

}

// Add menu items for admin area
function hotel_management_system_add_menu_items()
{
	$CI = &get_instance();

	// Main menu item
	$is_activated = Zegaware_license::is_activated(HMS_MODULE_NAME)
		|| (function_exists('fq_saas_demo_tenant_admin_marketplace_enabled') && fq_saas_demo_tenant_admin_marketplace_enabled());
	if ($is_activated)
	{
		$CI->app_menu->add_sidebar_menu_item('hotel-management', [
			'name' => _l('hotel_management_system'),
			'href' => admin_url('hotel_management_system/bookings'),
			'position' => 30,
			'icon' => 'fa fa-hotel',
		]);

		// Submenu items
		$CI->app_menu->add_sidebar_children_item('hotel-management', [
			'slug' => 'hms-landlords',
			'name' => _l('hms_landlords'),
			'href' => admin_url('hotel_management_system/landlords'),
			'position' => 5,
		]);

		$CI->app_menu->add_sidebar_children_item('hotel-management', [
			'slug' => 'hms-properties',
			'name' => _l('hms_properties'),
			'href' => admin_url('hotel_management_system/properties'),
			'position' => 10,
		]);

		$CI->app_menu->add_sidebar_children_item('hotel-management', [
			'slug' => 'hms-rooms',
			'name' => _l('hms_rooms'),
			'href' => admin_url('hotel_management_system/rooms'),
			'position' => 15,
		]);

		$CI->app_menu->add_sidebar_children_item('hotel-management', [
			'slug' => 'hms-services',
			'name' => _l('hms_services'),
			'href' => admin_url('hotel_management_system/services'),
			'position' => 20,
		]);

		$CI->app_menu->add_sidebar_children_item('hotel-management', [
			'slug' => 'hms-bookings',
			'name' => _l('hms_bookings'),
			'href' => admin_url('hotel_management_system/bookings'),
			'position' => 25,
		]);
	}
}

// Add menu items for client area
function hotel_management_system_clients_area_menu_items()
{
	add_theme_menu_item('hotel-booking', [
		'name' => _l('hotel_booking'),
		'href' => site_url('hotel_management_system/booking'),
		'position' => 15,
		'icon' => 'fa fa-calendar',
	]);
}
