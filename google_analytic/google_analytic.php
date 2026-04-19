<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Google Analytics
Description: This module provides in-depth knowledge of tracking and analyzing website performance and covers concepts like custom dimensions, event tracking, and advanced reporting
Version: 1.0.0
Requires at least: 2.3.*
Author: GreenTech Solutions
Author URI: https://codecanyon.net/user/greentech_solutions
*/

define('GOOGLE_ANALYTIC_MODULE_NAME', 'google_analytic');
define('GOOGLE_ANALYTIC_REVISION', 1001);
define('GOOGLE_ANALYTIC_UPLOAD_FOLDER', module_dir_path(GOOGLE_ANALYTIC_MODULE_NAME, 'uploads'));

hooks()->add_action('admin_init', 'google_analytic_module_init_menu_items');
hooks()->add_action('admin_init', 'google_analytic_permissions');
hooks()->add_action('app_admin_head', 'google_analytic_head_components');
hooks()->add_action('app_admin_footer', 'google_analytic_add_footer_components');
hooks()->add_action('admin_navbar_start', 'google_analytic_navbar_components');
hooks()->add_action('customers_navigation_end', 'ga_module_init_client_menu_items');
hooks()->add_action('app_customers_footer', 'ga_client_add_footer_components');
hooks()->add_action('app_customers_head', 'ga_client_add_head_components');
hooks()->add_action('before_customers_area_sub_menu_start', 'ga_customers_area_sub_menu_start');
hooks()->add_action('google_analytic_init',GOOGLE_ANALYTIC_MODULE_NAME.'_appint');
hooks()->add_action('pre_activate_module', GOOGLE_ANALYTIC_MODULE_NAME.'_preactivate');
hooks()->add_action('pre_deactivate_module', GOOGLE_ANALYTIC_MODULE_NAME.'_predeactivate');
/**
 * Register activation module hook
 */
register_activation_hook(GOOGLE_ANALYTIC_MODULE_NAME, 'google_analytic_module_activation_hook');

function google_analytic_module_activation_hook() {
	$CI = &get_instance();
	require_once __DIR__ . '/install.php';
}

/**
* Load the module helper
*/
$CI = & get_instance();
$CI->load->helper(GOOGLE_ANALYTIC_MODULE_NAME . '/google_analytic');

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(GOOGLE_ANALYTIC_MODULE_NAME, [GOOGLE_ANALYTIC_MODULE_NAME]);

/**
 * Init google_analytic module menu items in setup in admin_init hook
 * @return null
 */
function google_analytic_module_init_menu_items() {
	if (has_permission('google_analytic', '', 'view')) {
		$CI = &get_instance();
		$CI->app_menu->add_sidebar_menu_item('google_analytic', [
			'name' => _l('google_analytic'),
			'icon' => 'fa fa-calendar',
			'position' => 30,
		]);


		$CI->app_menu->add_sidebar_children_item('google_analytic', [
			'slug' => 'social-analytic-analytics',
			'name' => _l('analytics'),
			'icon' => 'fas fa-chart-area',
			'href' => admin_url('google_analytic/analytics'),
			'position' => 1,
		]);

		$CI->app_menu->add_sidebar_children_item('google_analytic', [
			'slug' => 'social-analytic-workspaces',
			'name' => _l('workspaces'),
			'icon' => 'fas fa-network-wired',
			'href' => admin_url('google_analytic/workspaces'),
			'position' => 1,
		]);

		$CI->app_menu->add_sidebar_children_item('google_analytic', [
			'slug' => 'social-analytic-accounts',
			'name' => _l('properties'),
			'icon' => 'fas fa-pager',
			'href' => admin_url('google_analytic/accounts'),
			'position' => 1,
		]);

		$CI->app_menu->add_sidebar_children_item('google_analytic', [
			'slug' => 'social-analytic-settings',
			'name' => _l('settings'),
			'icon' => 'fa fa-cog',
			'href' => admin_url('google_analytic/setting'),
			'position' => 2,
		]);
	}
}


/**
 * resource workload permissions
 * @return
 */
function google_analytic_permissions() {
	$capabilities = [];

	$capabilities['capabilities'] = [
		'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
	];

	register_staff_capabilities('google_analytic', $capabilities, _l('google_analytic'));
}

/**
 * add head components
 */
function google_analytic_head_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	if (!(strpos($viewuri, 'admin/google_analytic') === false)) {
		echo '<link href="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/css/ga_custom_style.css') . '?v=' . GOOGLE_ANALYTIC_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
}

/**
 * add footer components
 * @return
 */
function google_analytic_add_footer_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	if (!(strpos($viewuri, 'admin/google_analytic') === false)) {
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/js/ga_main.js') . '?v=' . GOOGLE_ANALYTIC_REVISION . '"></script>';
	}

	if (!(strpos($viewuri, '/admin/google_analytic/analytics') === false)) {
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/plugins/highcharts/highcharts.js') . '"></script>';
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/plugins/highcharts/modules/variable-pie.js') . '"></script>';
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/plugins/highcharts/modules/export-data.js') . '"></script>';
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/plugins/highcharts/modules/accessibility.js') . '"></script>';
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/plugins/highcharts/modules/exporting.js') . '"></script>';
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/plugins/highcharts/highcharts-3d.js') . '"></script>';
	}
}


function google_analytic_navbar_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	if (!(strpos($viewuri, 'admin/google_analytic') === false)) {
		$CI->load->model('google_analytic/google_analytic_model');
	    $staffid = get_staff_user_id();
		if(!is_admin()){
        	$CI->db->where('(super_admin = "'.$staffid.'" OR ' . db_prefix() . 'ga_workspaces.id in (SELECT ' . db_prefix() . 'ga_workspace_members.workspace_id FROM ' . db_prefix() . 'ga_workspace_members WHERE ' . db_prefix() . 'ga_workspace_members.workspace_id = ' . db_prefix() . 'ga_workspaces.id AND type = "staff" AND member_id = "'.$staffid.'"))');
		}

        $CI->db->order_by('name', 'asc');
        $workspaces = $CI->db->get(db_prefix() . 'ga_workspaces')->result_array();

		$workspace_id = ga_get_base_workspace_id();

	    echo '<li class="">';
	    echo '<div class="navbar_base_workspace mtop15 min-width-200px">';
	    echo render_select('ga_base_workspace', $workspaces, array('id', 'name'), '', $workspace_id,array(),array(),'','',false);
	    echo '</div>';
	    echo '</li>';
	}
}


function ga_module_init_client_menu_items()
{
    if(ga_check_workspace_client(get_client_user_id())){
	    $menu = '';
	    if (is_client_logged_in()) {
	    	$menu .= '<li class="customers-nav-item-social-analytic">
	                  <a href="'.site_url('google_analytic/google_analytic_client').'">
	                    <i class=""></i> '
	                    . _l('google_analytic').'
	                  </a>
	               </li>';
	    }
    	echo html_entity_decode($menu);
    }
}

function ga_client_add_head_components()
{
    $CI      = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];

    if (!(strpos($viewuri, 'google_analytic/google_analytic_client') === false)) {
		echo '<link href="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/css/ga_custom_style.css') . '?v=' . GOOGLE_ANALYTIC_REVISION . '"  rel="stylesheet" type="text/css" />';
    }
}


function ga_client_add_footer_components(){
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];
    if (!(strpos($viewuri, '/google_analytic/google_analytic_client') === false)) {
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/js/clients/ga_client_main.js')  . '?v=' . GOOGLE_ANALYTIC_REVISION . '"></script>';
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/plugins/highcharts/highcharts.js') . '"></script>';
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/plugins/highcharts/modules/variable-pie.js') . '"></script>';
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/plugins/highcharts/modules/export-data.js') . '"></script>';
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/plugins/highcharts/modules/accessibility.js') . '"></script>';
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/plugins/highcharts/modules/exporting.js') . '"></script>';
		echo '<script src="' . module_dir_url(GOOGLE_ANALYTIC_MODULE_NAME, 'assets/plugins/highcharts/highcharts-3d.js') . '"></script>';
	}
}


function ga_customers_area_sub_menu_start() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	if (!(strpos($viewuri, 'google_analytic/google_analytic_client') === false)) {
	    $contact_id = get_contact_user_id();
		if(!is_admin()){
        	$CI->db->where('(' . db_prefix() . 'ga_workspaces.id in (SELECT ' . db_prefix() . 'ga_workspace_members.workspace_id FROM ' . db_prefix() . 'ga_workspace_members WHERE ' . db_prefix() . 'ga_workspace_members.workspace_id = ' . db_prefix() . 'ga_workspaces.id AND type = "contact" AND member_id = "'.$contact_id.'"))');
		}

        $CI->db->order_by('name', 'asc');
        $workspaces = $CI->db->get(db_prefix() . 'ga_workspaces')->result_array();
		$workspace_id = ga_get_contact_base_workspace_id();

	    echo '<li class="">';
	    echo '<select name="ga_base_workspace" id="ga_base_workspace" class="selectpicker" data-width="100%">';
	    foreach ($workspaces as $key => $value) {
	    	$selected = '';
	    	if ($value['id'] == $workspace_id) {
	    		$selected = 'selected';
	    	}
    		echo '<option value="'.$value['id'].'" '.$selected .'>'.$value['name'].'</option>';
	    }
	    echo '</select>';
	    echo '</li>';
	}
}
function google_analytic_appint(){
  
}

function google_analytic_preactivate($module_name){
    if ($module_name['system_name'] == GOOGLE_ANALYTIC_MODULE_NAME) {

    }
}

function google_analytic_predeactivate($module_name){
    if ($module_name['system_name'] == GOOGLE_ANALYTIC_MODULE_NAME) {

    }
}
