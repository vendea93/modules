<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Reputation Management
Description: This module gives organizations a single, operational hub to listen, analyze, act, and report on everything the market is saying about their brands, products, competitors, and campaigns.
Version: 1.0.0
Requires at least: 2.3.*
Author: GreenTech Solutions
Author URI: https://codecanyon.net/user/greentech_solutions
*/

define('REPUTATION_MODULE_NAME', 'reputation');
define('REPUTATION_REVISION', 100);
define('REPUTATION_IMPORT_ITEM_ERROR', 'modules/reputation/uploads/import_item_error/');

hooks()->add_action('admin_init', 'reputation_module_init_menu_items');
hooks()->add_action('admin_init', 'reputation_permissions');
hooks()->add_action('app_admin_head', 'reputation_head_components');
hooks()->add_action('app_admin_footer', 'reputation_add_footer_components');
hooks()->add_action('admin_navbar_start', 'reputation_navbar_components');
hooks()->add_action('after_cron_run', 'cron_reputation');
hooks()->add_action('task_related_to_select', 'rep_vendor_related_to_select'); // old
hooks()->add_filter('before_return_relation_data', 'rep_vendor_relation_data', 10, 4); // old
hooks()->add_action('task_modal_rel_type_select', 'rep_vendor_task_modal_rel_type_select'); // new
hooks()->add_filter('tasks_table_row_data', 'rep_vendor_add_table_row', 10, 3);
hooks()->add_filter('relation_values', 'rep_vendors_get_relation_values', 10, 2); // new
hooks()->add_filter('get_relation_data', 'reo_vendors_relation_data', 10, 4); // new


$CI = &get_instance();

$CI->load->helper(REPUTATION_MODULE_NAME . '/Reputation');

/**
 * Register activation module hook
 */
register_activation_hook(REPUTATION_MODULE_NAME, 'reputation_module_activation_hook');

function reputation_module_activation_hook() {
	$CI = &get_instance();
	require_once __DIR__ . '/install.php';
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(REPUTATION_MODULE_NAME, [REPUTATION_MODULE_NAME]);

/**
 * Init reputation module menu items in setup in admin_init hook
 * @return null
 */
function reputation_module_init_menu_items() {
	if (has_permission('reputation_topic', '', 'view') || has_permission('reputation_project', '', 'view') || has_permission('reputation_mentions', '', 'view') || has_permission('reputation_summary', '', 'view') || has_permission('reputation_vendor', '', 'view') || has_permission('reputation_social_accounts', '', 'view') || has_permission('reputation_case', '', 'view') || has_permission('reputation_pdf_reports', '', 'view') || has_permission('reputation_setting', '', 'view')) {
		$CI = &get_instance();
		$CI->app_menu->add_sidebar_menu_item('reputation', [
			'name' => _l('reputation'),
			'icon' => 'fa fa-calendar',
			'position' => 30,
		]);

		if (has_permission('reputation_topic', '', 'view')) {
			$CI->app_menu->add_sidebar_children_item('reputation', [
				'slug' => 'reputation_topic',
				'name' => _l('topic_management'),
				'icon' => 'fa fa-list',
				'href' => admin_url('reputation/topic_management'),
				'position' => 1,
			]);
        }

		if (has_permission('reputation_project', '', 'view')) {
			$CI->app_menu->add_sidebar_children_item('reputation', [
				'slug' => 'reputation_project',
				'name' => _l('projects'),
				'icon' => 'fa fa-tasks',
				'href' => admin_url('reputation/projects'),
				'position' => 1,
			]);
        }

        if (has_permission('reputation_mentions', '', 'view')) {
			$CI->app_menu->add_sidebar_children_item('reputation', [
				'slug' => 'reputation_mentions',
				'name' => _l('mentions'),
				'icon' => 'fa fa-tags',
				'href' => admin_url('reputation/mentions'),
				'position' => 1,
			]);
        }

        if (has_permission('reputation_summary', '', 'view')) {
			$CI->app_menu->add_sidebar_children_item('reputation', [
				'slug' => 'reputation_summary',
				'name' => _l('summary'),
				'icon' => 'fa fa-bar-chart',
				'href' => admin_url('reputation/summary'),
				'position' => 1,
			]);
        }

		if (has_permission('reputation_vendor', '', 'view')) {
			$CI->app_menu->add_sidebar_children_item('reputation', [
				'slug' => 'reputation_vendor',
				'name' => _l('vendors'),
				'icon' => 'fa fa-users',
				'href' => admin_url('reputation/vendors'),
				'position' => 1,
			]);
		}

        if (has_permission('reputation_social_accounts', '', 'view')) {
			$CI->app_menu->add_sidebar_children_item('reputation', [
				'slug' => 'reputation_social_accounts',
				'name' => _l('social_accounts'),
				'icon' => 'fa fa-pager',
				'href' => admin_url('reputation/social_accounts'),
				'position' => 1,
			]);
        }

        if (has_permission('reputation_case', '', 'view')) {
			$CI->app_menu->add_sidebar_children_item('reputation', [
				'slug' => 'reputation_case',
				'name' => _l('case_management'),
				'icon' => 'fa fa-random',
				'href' => admin_url('reputation/case_management'),
				'position' => 1,
			]);
        }

        if (has_permission('reputation_pdf_reports', '', 'view')) {
			$CI->app_menu->add_sidebar_children_item('reputation', [
				'slug' => 'reputation_pdf_report',
				'name' => _l('pdf_reports'),
				'icon' => 'fa fa-file-pdf',
				'href' => admin_url('reputation/pdf_reports'),
				'position' => 1,
			]);
        }

        if (has_permission('reputation_setting', '', 'view')) {
			$CI->app_menu->add_sidebar_children_item('reputation', [
				'slug' => 'reputation_setting',
				'name' => _l('settings'),
				'icon' => 'fa fa-cogs',
				'href' => admin_url('reputation/settings'),
				'position' => 10,
			]);
        }

	}

}


/**
 * add head components
 */
function reputation_head_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	if (!(strpos($viewuri, 'admin/reputation') === false)) {
		echo '<link href="' . module_dir_url(REPUTATION_MODULE_NAME, 'assets/css/rep_custom_style.css') . '?v=' . REPUTATION_REVISION . '"  rel="stylesheet" type="text/css" />';
	}
}

/**
 * add footer components
 * @return
 */
function reputation_add_footer_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	if (!(strpos($viewuri, 'admin/reputation') === false)) {
        echo '<script src="' . module_dir_url(REPUTATION_MODULE_NAME, 'assets/js/rep_main.js') .'?v=' . REPUTATION_REVISION.'"></script>';
    }

	if (!(strpos($viewuri, 'admin/reputation/vendors') === false)) {
        echo '<script src="' . module_dir_url(REPUTATION_MODULE_NAME, 'assets/js/vendors/vendor_manage.js') .'?v=' . REPUTATION_REVISION.'"></script>';
    }


    if (!(strpos($viewuri, '/admin/reputation/mentions') === false) || !(strpos($viewuri, '/admin/reputation/summary') === false) || !(strpos($viewuri, '/admin/reputation/analysis') === false) || !(strpos($viewuri, '/admin/reputation/pdf_reports') === false)) {
		echo '<script src="' . module_dir_url(REPUTATION_MODULE_NAME, 'assets/plugins/highcharts/highcharts.js') . '"></script>';
		echo '<script src="' . module_dir_url(REPUTATION_MODULE_NAME, 'assets/plugins/highcharts/modules/heatmap.js') . '"></script>';
		echo '<script src="' . module_dir_url(REPUTATION_MODULE_NAME, 'assets/plugins/highcharts/modules/variable-pie.js') . '"></script>';
		echo '<script src="' . module_dir_url(REPUTATION_MODULE_NAME, 'assets/plugins/highcharts/modules/export-data.js') . '"></script>';
		echo '<script src="' . module_dir_url(REPUTATION_MODULE_NAME, 'assets/plugins/highcharts/modules/accessibility.js') . '"></script>';
		echo '<script src="' . module_dir_url(REPUTATION_MODULE_NAME, 'assets/plugins/highcharts/modules/exporting.js') . '"></script>';
		echo '<script src="' . module_dir_url(REPUTATION_MODULE_NAME, 'assets/plugins/highcharts/highcharts-3d.js') . '"></script>';
		echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>';
		echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>';
	}
}


/**
 * Vendors relation values
 * @param  [type] $values
 * @param  [type] $relation
 * @return [type]
 */
function rep_vendors_get_relation_values($values, $relation = null)
{

    if ($values['type'] == 'vendor') {
        if (is_array($values['relation'])) {
            $values['id']   = $values['relation']['userid'];
            $values['name'] = $values['relation']['company'];
        } else {
            $values['id']   = $values['relation']->userid;
            $values['name'] = $values['relation']->company;
        }
        $values['link'] = admin_url('reputation/vendor/' . $values['id']);
    }

    return $values;
}

/**
 * po get relation data
 * @param  object $data
 * @param  object $obj
 * @return
 */
function reo_vendors_relation_data($data, $obj, $q = '') {
    $type = $obj['type'];
    $rel_id = $obj['rel_id'];
    $CI = &get_instance();
    $CI->load->model('reputation/reputation_model');

    if ($type == 'vendor') {
        if ($rel_id != '') {
            $data = $CI->reputation_model->get_vendor($rel_id);
        } else {
            if($q != ''){
                $data = $CI->reputation_model->get_vendor_search($q);
            }
        }
    }
    return $data;
}


/**
 * task related to select
 * @param  string $value
 * @return string
 */
function rep_vendor_related_to_select($value)
{

    $selected = '';
    if($value == 'vendor'){
        $selected = 'selected';
    }
    echo "<option value='vendor' ".$selected.">".
                               _l('vendor')."
                           </option>";

}


/**
 * PO relation data
 * @param  array $data
 * @param  string $type
 * @param  id $rel_id
 * @param  array $q
 * @return array
 */
function rep_vendor_relation_data($data, $type, $rel_id, $q = '')
{

    $CI = &get_instance();
    $CI->load->model('reputation/reputation_model');

    if ($type == 'vendor') {
    	if ($q && !$rel_id) {
            $where_vendor .= '(company LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR CONCAT(firstname, " ", lastname) LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\' OR email LIKE "%' . $CI->db->escape_like_str($q) . '%" ESCAPE \'!\') AND ' . db_prefix() . 'pur_vendor.active = 1';
        }

        $data = $CI->reputation_model->get_vendor($rel_id, $where_vendor);
    }
    return $data;
}

/**
 * po task modal rel type select
 * @param  object $value
 * @return
 */
function rep_vendor_task_modal_rel_type_select($value) {
    $selected = '';
    if (isset($value) && isset($value['rel_type']) && $value['rel_type'] == 'vendor') {
        $selected = 'selected';
    }
    echo "<option value='vendor' " . $selected . ">" .
    _l('vendor') . "
                           </option>";

}


/**
 * PO add table row
 * @param  string $row
 * @param  string $aRow
 * @return [type]
 */
function rep_vendor_add_table_row($row ,$aRow)
{

    $CI = &get_instance();
    $CI->load->model('reputation/reputation_model');

    if($aRow['rel_type'] == 'vendor'){
        $vendor = $CI->reputation_model->get_vendor($aRow['rel_id']);

           if ($vendor) {

                 $str = '<span class="hide"> - </span><a class="text-muted task-table-related" data-toggle="tooltip" title="' . _l('task_related_to') . '" href="' . admin_url('reputation/vendor/' . $vendor->userid) . '">' . $vendor->company . '</a><br />';

                $row[2] =   $row[2].$str;
            }

    }

    return $row;
}

function reputation_navbar_components() {
	$CI = &get_instance();
	$viewuri = $_SERVER['REQUEST_URI'];
	if (!(strpos($viewuri, 'admin/reputation') === false)) {
		$CI->load->model('reputation/reputation_model');


        $CI->db->where('active', 1);
        $CI->db->order_by('project_name', 'asc');
        $workspaces = $CI->db->get(db_prefix() . 'rep_projects')->result_array();

		$workspace_id = rep_get_base_workspace_id();

	    echo '<li class="">';
	    echo '<div class="navbar_base_workspace mtop10 min-width-200px">';
	    echo render_select('rep_base_workspace', $workspaces, array('id', 'project_name'), '', $workspace_id,array(),array(),'','',false);
	    echo '</div>';
	    echo '</li>';
	}
}


function cron_reputation() {
	$CI = &get_instance();

	$last_cron_run                  = get_option('rep_reputation_last_cron_run');

    $seconds = 1800;

    if ($last_cron_run == '' || (time() > ($last_cron_run + $seconds))) {
		$CI->load->model('reputation/reputation_model');
		$CI->reputation_model->cron_reputation();
	}
}


/**
 * Init fleet module permissions in setup in admin_init hook
 */
function reputation_permissions() {

    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    register_staff_capabilities('reputation_topic', $capabilities, _l('reputation_topic'));

    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    register_staff_capabilities('reputation_project', $capabilities, _l('reputation_project'));

    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view'),
        'edit' => _l('permission_edit'),
    ];
    register_staff_capabilities('reputation_mentions', $capabilities, _l('reputation_mentions'));

    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view'),
    ];
    register_staff_capabilities('reputation_summary', $capabilities, _l('reputation_summary'));


    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    register_staff_capabilities('reputation_vendor', $capabilities, _l('reputation_vendor'));

    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    register_staff_capabilities('reputation_social_accounts', $capabilities, _l('reputation_social_accounts'));

    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view'),
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];
    register_staff_capabilities('reputation_case', $capabilities, _l('reputation_case'));

    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view'),
    ];

    register_staff_capabilities('reputation_pdf_reports', $capabilities, _l('reputation_pdf_reports'));

    $capabilities = [];
    $capabilities['capabilities'] = [
        'view'   => _l('permission_view'),
    ];
    register_staff_capabilities('reputation_setting', $capabilities, _l('reputation_setting'));
}

function reputation_appint(){
  
}

function reputation_preactivate($module_name){
    if ($module_name['system_name'] == REPUTATION_MODULE_NAME) {

    }
}

function reputation_predeactivate($module_name){
    if ($module_name['system_name'] == REPUTATION_MODULE_NAME) {

    }
}
