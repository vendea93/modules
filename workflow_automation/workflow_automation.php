<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: Workflow Automation
Description: This is a powerful tool designed to streamline business processes across departments by automating actions, notifications, and updates based on user-defined triggers and conditions.
Version: 1.0.0
Requires at least: 2.3.*
Author: GreenTech Solutions
Author URI: https://codecanyon.net/user/greentech_solutions
*/

define('WORKFLOW_AUTOMATION_MODULE_NAME', 'workflow_automation');
define('WORKFLOW_AUTOMATION_MODULE_UPLOAD_FOLDER', module_dir_path(WORKFLOW_AUTOMATION_MODULE_NAME, 'uploads'));
define('WORKFLOW_AUTOMATION_REVISION', 100);

// Init Menu
hooks()->add_action('admin_init', 'wa_module_init_menu_items');
hooks()->add_action('admin_init', 'wa_permissions');

hooks()->add_action('app_admin_head', 'wa_add_head_components');
hooks()->add_action('app_admin_footer', 'wa_add_footer_components');


// task trigger hook
hooks()->add_action('after_add_task', 'add_task_trigger');
hooks()->add_action('after_update_task', 'update_task_trigger');
hooks()->add_action('task_deleted', 'delete_task_trigger');
hooks()->add_action('task_status_changed', 'status_task_change_trigger');

// project trigger hook
hooks()->add_action('after_add_project', 'wa_add_project_trigger');
hooks()->add_action('after_update_project', 'wa_update_project_trigger');
hooks()->add_action('project_status_changed', 'wa_project_status_change_trigger');
hooks()->add_action('after_project_deleted', 'wa_delete_project_trigger');

// Contract trigger hook
hooks()->add_action('after_contract_added', 'wa_add_contract_trigger');
hooks()->add_action('after_contract_updated', 'wa_update_contract_trigger');
hooks()->add_action('after_contract_deleted', 'wa_delete_contract_trigger');

// Lead trigger hook
hooks()->add_action('lead_converted_to_customer', 'wa_lead_converted_to_customer_trigger');
hooks()->add_action('lead_created', 'wa_add_lead_trigger');
hooks()->add_action('after_lead_updated', 'wa_update_lead_trigger');
hooks()->add_action('after_lead_deleted', 'wa_delete_lead_trigger');

// Customer trigger hook
hooks()->add_action('after_client_created', 'wa_add_client_trigger');
hooks()->add_action('client_updated', 'wa_update_client_trigger');
hooks()->add_action('after_client_deleted', 'wa_delete_client_trigger');
hooks()->add_action('contact_created', 'wa_add_contact_trigger');

// Proposals trigger hook
hooks()->add_action('proposal_converted_to_estimate', 'wa_proposal_converted_to_estimate_trigger');
hooks()->add_action('after_proposal_converted_to_invoice', 'wa_proposal_converted_to_invoice_trigger');
hooks()->add_action('proposal_created', 'wa_add_proposal_trigger');
hooks()->add_action('after_proposal_updated', 'wa_update_proposal_trigger');
hooks()->add_action('after_proposal_deleted', 'wa_delete_proposal_trigger');

// Estimates trigger hook
hooks()->add_action('after_estimate_added', 'wa_add_estimate_trigger');
hooks()->add_action('after_estimate_updated', 'wa_update_estimate_trigger');
hooks()->add_action('after_estimate_deleted', 'wa_delete_estimate_trigger');
hooks()->add_action('estimate_converted_to_invoice', 'wa_estimate_converted_to_invoice_trigger');

// Invoices trigger hook
hooks()->add_action('after_invoice_added', 'wa_add_invoice_trigger');
hooks()->add_action('invoice_updated', 'wa_update_invoice_trigger');
hooks()->add_action('after_invoice_deleted', 'wa_delete_invoice_trigger');
hooks()->add_action('invoice_marked_as_cancelled', 'wa_cancelled_invoice_trigger');
hooks()->add_action('invoice_unmarked_as_cancelled', 'wa_unmark_cancelled_invoice_trigger');

// Invoice Payment trigger hook
hooks()->add_action('after_payment_added', 'wa_add_invoice_payment_trigger');
hooks()->add_action('after_payment_updated', 'wa_update_invoice_payment_trigger');
hooks()->add_action('after_payment_deleted', 'wa_delete_invoice_payment_trigger');

// Credit note trigger hook
hooks()->add_action('after_create_credit_note', 'wa_add_credit_note_trigger');
hooks()->add_action('after_update_credit_note', 'wa_update_credit_note_trigger');
hooks()->add_action('after_credit_note_deleted', 'wa_delete_credit_note_trigger');

//Purchase order trigger hook
hooks()->add_action('after_purchase_order_add', 'wa_add_purchase_order_trigger');
hooks()->add_action('after_pur_order_updated', 'wa_update_purchase_order_trigger');
hooks()->add_action('after_pur_order_delete', 'wa_delete_purchase_order_trigger');
hooks()->add_action('after_purchase_order_approve', 'wa_change_approval_status_purchase_order_trigger');
hooks()->add_action('after_purchase_order_change_order_status', 'wa_change_delivery_status_purchase_order_trigger');

// Purchase Request trigger hook
hooks()->add_action('after_add_purchase_request', 'wa_add_purchase_request_trigger');
hooks()->add_action('after_update_purchase_request', 'wa_update_purchase_request_trigger');
hooks()->add_action('after_delete_purchase_request', 'wa_delete_purchase_request_trigger');
hooks()->add_action('after_change_status_purchase_request', 'wa_change_status_purchase_request_trigger');

// Purchase quotation trigger hook
hooks()->add_action('after_create_purchase_quotation', 'wa_create_purchase_quotation_trigger');
hooks()->add_action('after_update_purchase_quotation', 'wa_update_purchase_quotation_trigger');
hooks()->add_action('after_delete_purchase_quotation', 'wa_delete_purchase_quotation_trigger');
hooks()->add_action('after_change_status_purchase_estimate', 'wa_change_status_purchase_quotation_trigger');

// Purchase invoice trigger hook
hooks()->add_action('after_pur_invoice_added', 'wa_create_purchase_invoice_trigger');
hooks()->add_action('after_pur_invoice_updated', 'wa_update_purchase_invoice_trigger');
hooks()->add_action('after_pur_invoice_deleted', 'wa_delete_purchase_invoice_trigger');
hooks()->add_action('after_purchase_invoice_approve', 'wa_change_status_purchase_invoice_trigger');

// Purchase contract trigger hook
hooks()->add_action('after_purchase_contract_added', 'wa_create_purchase_contract_trigger');
hooks()->add_action('after_purchase_contract_updated', 'wa_update_purchase_contract_trigger');
hooks()->add_action('after_purchase_contract_deleted', 'wa_delete_purchase_contract_trigger');
hooks()->add_action('after_purchase_contract_signed', 'wa_signed_purchase_contract_trigger');

//Vendor trigger hook
hooks()->add_action('after_pur_vendor_created', 'wa_create_vendor_trigger');
hooks()->add_action('after_pur_vendor_updated', 'wa_update_vendor_trigger');
hooks()->add_action('after_pur_vendor_deleted', 'wa_delete_vendor_trigger');
hooks()->add_action('after_vendor_contact_added', 'wa_vendor_contact_create_trigger');

// Customer Service ticket
hooks()->add_action('cs_after_ticket_added', 'wa_create_cs_ticket_trigger');
hooks()->add_action('cs_after_ticket_updated', 'wa_update_cs_ticket_trigger');
hooks()->add_action('cs_after_ticket_deleted', 'wa_delete_cs_ticket_trigger');

// Staff trigger hook
hooks()->add_action('staff_member_created', 'wa_create_staff_trigger');
hooks()->add_action('staff_member_updated', 'wa_update_staff_trigger');
hooks()->add_action('staff_member_deleted', 'wa_delete_staff_trigger');

// Expense trigger hook
hooks()->add_action('after_expense_added', 'wa_create_expense_trigger');
hooks()->add_action('expense_updated', 'wa_update_expense_trigger');
hooks()->add_action('after_expense_deleted', 'wa_delete_expense_trigger');
hooks()->add_action('expense_converted_to_invoice', 'wa_expense_converted_to_invoice');

// Recruitment trigger hook

// Recruitment plan
hooks()->add_action('rec_after_plan_added', 'wa_create_rec_plan_trigger');
hooks()->add_action('rec_after_plan_updated', 'wa_update_rec_plan_trigger');
hooks()->add_action('rec_after_plan_deleted', 'wa_delete_rec_plan_trigger');

// Recruitment campaign
hooks()->add_action('rec_after_campaign_added', 'wa_create_rec_campaign_trigger');
hooks()->add_action('rec_after_campaign_updated', 'wa_update_rec_campaign_trigger');
hooks()->add_action('rec_after_campaign_deleted', 'wa_delete_rec_campaign_trigger');

// Recruitment form
hooks()->add_action('rec_after_form_added', 'wa_create_rec_form_trigger');
hooks()->add_action('rec_after_form_updated', 'wa_update_rec_form_trigger');
hooks()->add_action('rec_after_form_deleted', 'wa_delete_rec_form_trigger');

// Recruitmemnt candidate
hooks()->add_action('rec_after_candidate_added', 'wa_create_rec_candidate_trigger');
hooks()->add_action('rec_after_candidate_updated', 'wa_update_rec_candidate_trigger');
hooks()->add_action('rec_after_candidate_deleted', 'wa_delete_rec_candidate_trigger');
hooks()->add_action('rec_after_rated_candidate', 'wa_rated_rec_candidate_trigger');

// Recruitment Interview Schedule
hooks()->add_action('rec_after_interview_schedule_added', 'wa_create_rec_interview_schedule_trigger');
hooks()->add_action('rec_after_interview_schedule_updated', 'wa_update_rec_interview_schedule_trigger');
hooks()->add_action('rec_after_interview_schedule_deleted', 'wa_delete_rec_interview_schedule_trigger');

// Timesheet leave request
hooks()->add_action('ts_after_add_leave_request', 'wa_create_leave_request_trigger');
hooks()->add_action('ts_after_delete_leave_request', 'wa_delete_leave_request_trigger');

// Timesheet leave request additional work hours
hooks()->add_action('ts_after_add_additional_work_hours', 'wa_create_additional_work_hours_trigger');
hooks()->add_action('ts_after_delete_additional_work_hours', 'wa_delete_additional_work_hours_trigger');

// Fleet vehicle
hooks()->add_action('fleet_after_add_vehicle', 'wa_create_vehicle_trigger');
hooks()->add_action('fleet_after_update_vehicle', 'wa_update_vehicle_trigger');
hooks()->add_action('fleet_after_delete_vehicle', 'wa_delete_vehicle_trigger');
hooks()->add_action('fleet_after_add_vehicle_assignment', 'wa_create_vehicle_assignment_trigger');

// Fleet work performance
hooks()->add_action('fleet_after_add_work_performance', 'wa_create_work_performance_trigger');
hooks()->add_action('fleet_after_update_work_performance', 'wa_update_work_performance_trigger');
hooks()->add_action('fleet_after_delete_work_performance', 'wa_delete_work_performance_trigger');

// Fleet event
hooks()->add_action('fleet_after_add_event', 'wa_create_fleet_event_trigger');
hooks()->add_action('fleet_after_update_event', 'wa_update_fleet_event_trigger');
hooks()->add_action('fleet_after_delete_event', 'wa_delete_fleet_event_trigger');

// Fleet work order
hooks()->add_action('fleet_after_add_work_order', 'wa_create_fleet_work_order');
hooks()->add_action('fleet_after_update_work_order', 'wa_update_fleet_work_order');
hooks()->add_action('fleet_after_delete_work_order', 'wa_delete_fleet_work_order');

// Fleet booking
hooks()->add_action('fleet_after_add_booking', 'wa_create_fleet_booking'); 
hooks()->add_action('fleet_after_update_booking', 'wa_update_fleet_booking'); 
hooks()->add_action('fleet_after_delete_booking', 'wa_delete_fleet_booking'); 

// Fleet fuel
hooks()->add_action('fleet_after_add_fuel', 'wa_create_fleet_fuel');
hooks()->add_action('fleet_after_update_fuel', 'wa_update_fleet_fuel');
hooks()->add_action('fleet_after_delete_fuel', 'wa_delete_fleet_fuel');

// Manufacturing work center
hooks()->add_action('manufacturing_after_add_work_center', 'wa_create_work_center_trigger');
hooks()->add_action('manufacturing_after_update_work_center', 'wa_update_work_center_trigger');
hooks()->add_action('manufacturing_after_delete_work_center', 'wa_delete_work_center_trigger');

// Manufacturing routing
hooks()->add_action('manufacturing_after_add_routing', 'wa_create_routing_trigger');
hooks()->add_action('manufacturing_after_update_routing', 'wa_update_routing_trigger');
hooks()->add_action('manufacturing_after_delete_routing', 'wa_delete_routing_trigger');

// Manufacturing operation
hooks()->add_action('manufacturing_after_add_operation', 'wa_create_operation_trigger');
hooks()->add_action('manufacturing_after_update_operation', 'wa_update_operation_trigger');
hooks()->add_action('manufacturing_after_delete_operation', 'wa_delete_operation_trigger');

// Manufacturing operation
hooks()->add_action('manufacturing_after_add_bill_of_material', 'wa_create_bill_of_material_trigger');
hooks()->add_action('manufacturing_after_update_bill_of_material', 'wa_update_bill_of_material_trigger');
hooks()->add_action('manufacturing_after_delete_bill_of_material', 'wa_delete_bill_of_material_trigger');

// Manufacturing bom component
hooks()->add_action('manufacturing_after_add_bom_component', 'wa_create_bom_component_trigger');
hooks()->add_action('manufacturing_after_update_bom_component', 'wa_update_bom_component_trigger');
hooks()->add_action('manufacturing_after_delete_bom_component', 'wa_delete_bom_component_trigger');

// Manufacturing bom component
hooks()->add_action('after_manufacturing_order_added', 'wa_create_manufacturing_order_trigger');
hooks()->add_action('after_manufacturing_order_updated', 'wa_update_manufacturing_order_trigger');
hooks()->add_action('after_manufacturing_order_deleted', 'wa_delete_manufacturing_order_trigger');

// Omni sales order
hooks()->add_action('after_omni_sale_cart_added', 'wa_create_omni_sale_order_trigger');
hooks()->add_action('after_omni_sale_cart_updated', 'wa_update_omni_sale_order_trigger');
hooks()->add_action('after_omni_sales_order_deleted', 'wa_delete_omni_sale_order_trigger');

// Omni sales refund
hooks()->add_action('after_omni_sales_refund_added', 'wa_create_omni_sales_refund_trigger');
hooks()->add_action('after_omni_sales_refund_updated', 'wa_update_omni_sales_refund_trigger');
hooks()->add_action('after_omni_sales_refund_deleted', 'wa_delete_omni_sales_refund_trigger');

// Omni sales trade discount
hooks()->add_action('after_omni_sale_trade_discount_added', 'wa_create_omni_sale_trade_discount_trigger');
hooks()->add_action('after_omni_sale_trade_discount_updated', 'wa_update_omni_sale_trade_discount_trigger');
hooks()->add_action('after_omni_sale_trade_discount_deleted', 'wa_delete_omni_sale_trade_discount_trigger');

// Fixed euquipment asset
hooks()->add_action('after_fe_asset_added', 'wa_create_fe_asset_trigger');
hooks()->add_action('after_fe_asset_updated_wfl', 'wa_update_fe_asset_trigger');
hooks()->add_action('after_fe_asset_deleted', 'wa_delete_fe_asset_trigger');

// Fixed euquipment license
hooks()->add_action('after_fe_license_added', 'wa_create_fe_license_trigger');
hooks()->add_action('after_fe_license_updated', 'wa_update_fe_license_trigger');
hooks()->add_action('after_fe_license_deleted', 'wa_delete_fe_license_trigger');

// Fixed equipment accessories
hooks()->add_action('after_fe_accessories_added', 'wa_create_fe_accessories_trigger');
hooks()->add_action('after_fe_accessories_updated', 'wa_update_fe_accessories_trigger');
hooks()->add_action('after_fe_accessories_deleted', 'wa_delete_fe_accessories_trigger');

// Fixed equipment consumable
hooks()->add_action('after_fe_consumable_added', 'wa_create_fe_consumable_trigger');
hooks()->add_action('after_fe_consumable_updated', 'wa_update_fe_consumable_trigger');
hooks()->add_action('after_fe_consumable_deleted', 'wa_delete_fe_consumable_trigger');

// Fixed equipment component
hooks()->add_action('after_fe_component_added', 'wa_create_fe_component_trigger');
hooks()->add_action('after_fe_component_updated', 'wa_update_fe_component_trigger');
hooks()->add_action('after_fe_component_deleted', 'wa_delete_fe_component_trigger');

// Fixed hr payroll payslip
hooks()->add_action('hr_payroll_after_payslip_added', 'wa_create_payslip_trigger');
hooks()->add_action('hr_payroll_after_payslip_updated', 'wa_update_payslip_trigger');
hooks()->add_action('hr_payroll_after_payslip_deleted', 'wa_delete_payslip_trigger');

// Fixed hr payroll payslip template
hooks()->add_action('hr_payroll_after_payslip_template_added', 'wa_create_payslip_template_trigger');
hooks()->add_action('hr_payroll_after_payslip_template_updated', 'wa_update_payslip_template_trigger');
hooks()->add_action('hr_payroll_after_payslip_template_deleted', 'wa_delete_payslip_template_trigger');

// Warehouse items
hooks()->add_action('item_created', 'wa_create_wh_item_trigger');
hooks()->add_action('wh_item_updated', 'wa_update_wh_item_trigger');
hooks()->add_action('wh_delete_item', 'wa_delete_wh_item_trigger');

// Warehouse goods receipt
hooks()->add_action('after_wh_goods_receipt_added', 'wa_create_wh_goods_receipt_trigger');
hooks()->add_action('after_wh_goods_receipt_updated', 'wa_update_wh_goods_receipt_trigger');

// Warehouse goods delivery
hooks()->add_action('after_wh_goods_delivery_added', 'wa_create_wh_goods_delivery_trigger');
hooks()->add_action('after_wh_goods_delivery_updated', 'wa_update_wh_goods_delivery_trigger');
hooks()->add_action('after_wh_goods_delivery_approval_updated', 'wa_update_wh_goods_delivery_trigger');

// Warehouse packing list
hooks()->add_action('after_wh_packing_list_added', 'wa_create_wh_packing_list_trigger');
hooks()->add_action('after_wh_packing_list_change_delivery_status', 'wa_change_delivery_status_wh_packing_list_trigger');


// Timer cron run
hooks()->add_action('before_cron_run', 'timer_run_workflow');

// Merge fields
register_merge_fields('workflow_automation/merge_fields/workflow_sent_mail_merge_fields');

hooks()->add_filter('other_merge_fields_available_for', 'workflow_register_other_merge_fields');


/**
 * Register activation module hook
 */
register_activation_hook(WORKFLOW_AUTOMATION_MODULE_NAME, 'wa_module_activation_hook');
/**
 * Load the module helper
 */
$CI = &get_instance();
$CI->load->helper(WORKFLOW_AUTOMATION_MODULE_NAME . '/workflow_automation');

function wa_module_activation_hook() {
	$CI = &get_instance();
	require_once __DIR__ . '/install.php';
}

/**
 * Register language files, must be registered if the module is using languages
 */
register_language_files(WORKFLOW_AUTOMATION_MODULE_NAME, [WORKFLOW_AUTOMATION_MODULE_NAME]);



/**
 * Init goals module menu items in setup in admin_init hook
 * @return null
 */
function wa_module_init_menu_items() {

	$CI = &get_instance();

    if(has_permission('workflow_automation', '', 'view') || has_permission('workflow_automation_setting', '', 'edit') || has_permission('workflow_automation_history', '', 'view') || has_permission('workflow_automation', '', 'view_own')){
    	$CI->app_menu->add_sidebar_menu_item('wa-workflow-automation', [
    		'name' => _l('workflow_automation'),
    		'icon' => 'fa fa-tasks menu-icon',
    		'position' => 3,
    	]);

        if(has_permission('workflow_automation', '', 'view') || has_permission('workflow_automation', '', 'view_own') ){

        	$CI->app_menu->add_sidebar_children_item('wa-workflow-automation', [
                'slug'     => 'wa-workflow',
                'name'     => _l('wa_workflow'),
                'href'     => admin_url('workflow_automation/workflow'),
                'position' => 1,
            ]);
        }

        if(has_permission('workflow_automation_history', '', 'view')){
            $CI->app_menu->add_sidebar_children_item('wa-workflow-automation', [
                'slug'     => 'wa-workflow-history',
                'name'     => _l('wa_histories'),
                'href'     => admin_url('workflow_automation/history'),
                'position' => 2,
            ]);
        }

        if(has_permission('workflow_automation_setting', '', 'edit')){
            $CI->app_menu->add_sidebar_children_item('wa-workflow-automation', [
                'slug'     => 'wa-settings',
                'name'     => _l('wa_settings'),
                'href'     => admin_url('workflow_automation/settings'),
                'position' => 3,
            ]);
        }
    }
}

/**
 * [wa_permissions description]
 * @return [type] [description]
 */
function wa_permissions(){
    $capabilities = [];
    $capabilities_setting = [];
    $capabilities_view = [];

    $capabilities['capabilities'] = [
        'view' => _l('permission_view') . '(' . _l('permission_global') . ')',
        'view_own' => _l('permission_view') . '(' . _l('permission_own') . ')',
        'create' => _l('permission_create'),
        'edit' => _l('permission_edit'),
        'delete' => _l('permission_delete'),
    ];

    $capabilities_view['capabilities'] = [
        'view' => _l('permission_view'),
    ];

    $capabilities_setting['capabilities'] = [
        'edit' => _l('permission_edit'),
    ];


    register_staff_capabilities('workflow_automation', $capabilities, _l('workflow_automation'));
    register_staff_capabilities('workflow_automation_history', $capabilities_view, _l('workflow_automation_history'));
    register_staff_capabilities('workflow_automation_setting', $capabilities_setting, _l('workflow_automation_setting'));
}

/**
* Functions of the module
*/
function wa_add_head_components(){
    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];

    if (!(strpos($viewuri, 'admin/workflow_automation/workflow_detail') === false)) {
        echo '<link href="' . module_dir_url(WORKFLOW_AUTOMATION_MODULE_NAME, 'assets/plugins/Drawflow-master/docs/drawflow.min.css') . '?v=' . WORKFLOW_AUTOMATION_REVISION . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(WORKFLOW_AUTOMATION_MODULE_NAME, 'assets/plugins/Drawflow-master/docs/beautiful.css') . '?v=' . WORKFLOW_AUTOMATION_REVISION . '"  rel="stylesheet" type="text/css" />';
    }

    if (!(strpos($viewuri, 'admin/workflow_automation/workflow_builder') === false)) {
        echo '<link href="' . module_dir_url(WORKFLOW_AUTOMATION_MODULE_NAME, 'assets/plugins/Drawflow-master/docs/drawflow.min.css') . '?v=' . WORKFLOW_AUTOMATION_REVISION . '"  rel="stylesheet" type="text/css" />';
        echo '<link href="' . module_dir_url(WORKFLOW_AUTOMATION_MODULE_NAME, 'assets/plugins/Drawflow-master/docs/beautiful.css') . '?v=' . WORKFLOW_AUTOMATION_REVISION . '"  rel="stylesheet" type="text/css" />';
    }

    if (!(strpos($viewuri, 'admin/workflow_automation/') === false)) {
        echo '<link href="' . module_dir_url(WORKFLOW_AUTOMATION_MODULE_NAME, 'assets/css/wa_style.css') . '?v=' . WORKFLOW_AUTOMATION_REVISION . '"  rel="stylesheet" type="text/css" />';
       
    }

}

/**
 * [wa_add_footer_components description]
 * @return [type] [description]
 */
function wa_add_footer_components(){

    $CI = &get_instance();
    $viewuri = $_SERVER['REQUEST_URI'];

    if (!(strpos($viewuri, 'admin/workflow_automation/workflow_builder') === false)) {
        echo '<script src="' . module_dir_url(WORKFLOW_AUTOMATION_MODULE_NAME, 'assets/plugins/Drawflow-master/src/drawflow.js') . '?v=' . WORKFLOW_AUTOMATION_REVISION . '"></script>';
    }

    if (!(strpos($viewuri, 'admin/workflow_automation/workflow_detail') === false)) {
        echo '<script src="' . module_dir_url(WORKFLOW_AUTOMATION_MODULE_NAME, 'assets/plugins/Drawflow-master/src/drawflow.js') . '?v=' . WORKFLOW_AUTOMATION_REVISION . '"></script>';
    }


}

/**
* workflow_automation_appint
*/
function workflow_automation_appint(){

}

/**
* workflow_automation_preactivate
*/
function workflow_automation_preactivate($module_name){

}

/**
* workflow_automation_predeactivate
*/
function workflow_automation_predeactivate($module_name){

}

/**
 * [add_task_trigger description]
 */
function add_task_trigger($task_id){

    $data = [];
    $data['rel_id'] = $task_id;
    $data['rel_type'] = 'tasks';
    $data['start_case'] = 'created';


    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    $task = $CI->Workflow_automation_model->check_task_create_or_update_by_workflow($task_id);

    if(is_null($task->created_by_workflow)){
        $task->created_by_workflow = 0;
    }
    
    if($task->created_by_workflow != 1){
       $CI->Workflow_automation_model->run_work_flows($data);
    }


        
}


/**
 * [update_task_trigger description]
 */
function update_task_trigger($task_id){

    $data = [];
    $data['rel_id'] = $task_id;
    $data['rel_type'] = 'tasks';
    $data['start_case'] = 'updated';


    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');

    
    $CI->Workflow_automation_model->run_work_flows($data);
    

}

/**
 * [delete_task_trigger description]
 * @return [type] [description]
 */
function delete_task_trigger($task_id){
    $data = [];
    $data['rel_id'] = $task_id;
    $data['rel_type'] = 'tasks';
    $data['start_case'] = 'deleted';


    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');

    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [status_task_change_trigger description]
 * @return [type] [description]
 */
function status_task_change_trigger($_data){
    $data = [];
    $data['rel_id'] = $_data['task_id'];
    $data['rel_type'] = 'tasks';
    $data['start_case'] = 'change_status';


    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');

    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_add_project_trigger description]
 */
function wa_add_project_trigger($project_id){
    $data = [];
    $data['rel_id'] = $project_id;
    $data['rel_type'] = 'projects';
    $data['start_case'] = 'created';


    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');

    $CI->Workflow_automation_model->run_work_flows($data);
    
}

function wa_update_project_trigger($project_id){
    $data = [];
    $data['rel_id'] = $project_id;
    $data['rel_type'] = 'projects';
    $data['start_case'] = 'updated';

    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}


/**
 * [wa_project_status_change_trigger description]
 * @return [type] [description]
 */
function wa_project_status_change_trigger($_data){
    $data = [];
    $data['rel_id'] = $_data['project_id'];
    $data['rel_type'] = 'projects';
    $data['start_case'] = 'change_status';


    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');

    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_delete_project_trigger description]
 * @param  string $value [description]
 * @return [type]        [description]
 */
function wa_delete_project_trigger($project_id)
{
    $data = [];
    $data['rel_id'] = $project_id;
    $data['rel_type'] = 'projects';
    $data['start_case'] = 'deleted';

    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}


/**
 * [wa_add_contract_trigger description]
 * @param  string $value [description]
 * @return [type]        [description]
 */
function wa_add_contract_trigger($contract_id)
{
    $data = [];
    $data['rel_id'] = $contract_id;
    $data['rel_type'] = 'contracts';
    $data['start_case'] = 'created';

    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}


/**
 * [wa_update_contract_trigger description]
 * @param  string $value [description]
 * @return [type]        [description]
 */
function wa_update_contract_trigger($contract_id)
{
    $data = [];
    $data['rel_id'] = $contract_id;
    $data['rel_type'] = 'contracts';
    $data['start_case'] = 'updated';

    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}


/**
 * [wa_delete_contract_trigger description]
 * @param  string $value [description]
 * @return [type]        [description]
 */
function wa_delete_contract_trigger($contract_id)
{
    $data = [];
    $data['rel_id'] = $contract_id;
    $data['rel_type'] = 'contracts';
    $data['start_case'] = 'deleted';

    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_lead_converted_to_customer_trigger description]
 * @param  [type] $_data [description]
 * @return [type]        [description]
 */
function wa_lead_converted_to_customer_trigger($_data){

    $data = [];
    $data['rel_id'] = $_data['lead_id'];
    $data['rel_type'] = 'leads';
    $data['start_case'] = 'converted';

    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}
/**
 * [wa_add_lead_trigger description]
 * @param  [type] $lead_id [description]
 * @return [type]          [description]
 */
function wa_add_lead_trigger($lead_id){
    $data = [];
    $data['rel_id'] = $lead_id;
    $data['rel_type'] = 'leads';
    $data['start_case'] = 'created';

    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_update_lead_trigger description]
 * @param  [type] $lead_id [description]
 * @return [type]          [description]
 */
function wa_update_lead_trigger($lead_id){
    $data = [];
    $data['rel_id'] = $lead_id;
    $data['rel_type'] = 'leads';
    $data['start_case'] = 'updated';

    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}


/**
 * [wa_delete_lead_trigger description]
 * @param  [type] $lead_id [description]
 * @return [type]          [description]
 */
function wa_delete_lead_trigger($lead_id){
    $data = [];
    $data['rel_id'] = $lead_id;
    $data['rel_type'] = 'leads';
    $data['start_case'] = 'deleted';

    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_add_client_trigger description]
 * @return [type] [description]
 */
function wa_add_client_trigger($_data){
    $data = [];
    $data['rel_id'] = $_data['id'];
    $data['rel_type'] = 'customers';
    $data['start_case'] = 'created';

    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}


/**
 * [wa_update_client_trigger description]
 * @return [type] [description]
 */
function wa_update_client_trigger($_data){
    $data = [];
    $data['rel_id'] = $_data['id'];
    $data['rel_type'] = 'customers';
    $data['start_case'] = 'updated';

    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_delete_client_trigger description]
 * @return [type] [description]
 */
function wa_delete_client_trigger($client_id){
    $data = [];
    $data['rel_id'] = $client_id;
    $data['rel_type'] = 'customers';
    $data['start_case'] = 'deleted';

    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_add_contact_trigger description]
 * @return [type] [description]
 */
function wa_add_contact_trigger($contact_id){
    $data = [];
    $CI = &get_instance();

    $CI->load->model('clients_model');
    $contact = $CI->clients_model->get_contact($contact_id);

    if(isset($contact->userid)){
        $data['rel_id'] = $contact->userid;
        $data['rel_type'] = 'customers';
        $data['start_case'] = 'contact_created';

        

        $CI->load->model('workflow_automation/Workflow_automation_model');
        
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_add_proposal_trigger description]
 * @return [type] [description]
 */
function wa_add_proposal_trigger($proposal_id){
    $data = [];
    $CI = &get_instance();
    
    $data['rel_id'] = $proposal_id;
    $data['rel_type'] = 'proposals';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
    
}

/**
 * [wa_update_proposal_trigger description]
 * @return [type] [description]
 */
function wa_update_proposal_trigger($proposal_id){
    $data = [];
    $CI = &get_instance();
    
    $data['rel_id'] = $proposal_id;
    $data['rel_type'] = 'proposals';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
    
}

/**
 * [wa_delete_proposal_trigger description]
 * @return [type] [description]
 */
function wa_delete_proposal_trigger($proposal_id){
    $data = [];
    $CI = &get_instance();
    
    $data['rel_id'] = $proposal_id;
    $data['rel_type'] = 'proposals';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
    
}

/**
 * [wa_proposal_converted_to_estimate_trigger description]
 * @return [type] [description]
 */
function wa_proposal_converted_to_estimate_trigger($_data){
    $data = [];
    $CI = &get_instance();
    
    $data['rel_id'] = $_data['proposal_id'];
    $data['estimate_id'] = $_data['estimate_id'];
    $data['rel_type'] = 'proposals';
    $data['start_case'] = 'convert_to_estimate';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
    
}

/**
 * [wa_proposal_converted_to_invoice_trigger description]
 * @return [type] [description]
 */
function wa_proposal_converted_to_invoice_trigger($_data){
    $data = [];
    $CI = &get_instance();
    
    $data['rel_id'] = $_data['proposal_id'];
    $data['invoice_id'] = $_data['invoice_id'];
    $data['rel_type'] = 'proposals';
    $data['start_case'] = 'convert_to_invoice';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
    
}

/**
 * [wa_add_estimate_trigger description]
 * @return [type] [description]
 */
function wa_add_estimate_trigger($estimate_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $estimate_id;
    $data['rel_type'] = 'estimates';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_update_estimate_trigger description]
 * @return [type] [description]
 */
function wa_update_estimate_trigger($estimate_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $estimate_id;
    $data['rel_type'] = 'estimates';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}


/**
 * [wa_delete_estimate_trigger description]
 * @return [type] [description]
 */
function wa_delete_estimate_trigger($estimate_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $estimate_id;
    $data['rel_type'] = 'estimates';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_estimate_converted_to_invoice_trigger description]
 * @return [type] [description]
 */
function wa_estimate_converted_to_invoice_trigger($_data){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $_data['estimate_id'];
    $data['invoice_id'] = $_data['invoice_id'];
    $data['rel_type'] = 'estimates';
    $data['start_case'] = 'convert_to_invoice';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_add_invoice_trigger description]
 * @return [type] [description]
 */
function wa_add_invoice_trigger($invoice_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $invoice_id;
    $data['rel_type'] = 'invoices';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_update_invoice_trigger description]
 * @return [type] [description]
 */
function wa_update_invoice_trigger($_data){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $_data['id'];
    $data['rel_type'] = 'invoices';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_delete_invoice_trigger description]
 * @return [type] [description]
 */
function wa_delete_invoice_trigger($invoice_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $invoice_id;
    $data['rel_type'] = 'invoices';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_cancelled_invoice_trigger description]
 * @return [type] [description]
 */
function wa_cancelled_invoice_trigger($invoice_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $invoice_id;
    $data['rel_type'] = 'invoices';
    $data['start_case'] = 'mark_as_cancelled';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_unmark_cancelled_invoice_trigger description]
 * @return [type] [description]
 */
function wa_unmark_cancelled_invoice_trigger($invoice_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $invoice_id;
    $data['rel_type'] = 'invoices';
    $data['start_case'] = 'unmark_as_cancelled';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_add_invoice_payment_trigger description]
 * @param  [type] $payment_id [description]
 * @return [type]             [description]
 */
function wa_add_invoice_payment_trigger($payment_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $payment_id;

    $CI->load->model('payments_model');
    $payment = $CI->payments_model->get($payment_id);


    $data['invoice_id'] = isset($payment->invoiceid) ? $payment->invoiceid : 0;
    $data['rel_type'] = 'payment';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}


/**
 * [wa_update_invoice_payment_trigger description]
 * @param  [type] $payment_id [description]
 * @return [type]             [description]
 */
function wa_update_invoice_payment_trigger($_data){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $_data['id'];
    $CI->load->model('payments_model');
    $payment = $CI->payments_model->get($_data['id']);


    $data['invoice_id'] = isset($payment->invoiceid) ? $payment->invoiceid : 0;
    $data['rel_type'] = 'payment';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_delete_invoice_payment_trigger description]
 * @param  [type] $payment_id [description]
 * @return [type]             [description]
 */
function wa_delete_invoice_payment_trigger($_data){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $_data['paymentid'];
    $data['invoice_id'] = $_data['invoiceid'];
    $data['rel_type'] = 'payment';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_add_invoice_payment_trigger description]
 * @param  [type] $payment_id [description]
 * @return [type]             [description]
 */
function wa_add_credit_note_trigger($credit_note_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $credit_note_id;
    $data['rel_type'] = 'credit_notes';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_add_invoice_payment_trigger description]
 * @param  [type] $payment_id [description]
 * @return [type]             [description]
 */
function wa_update_credit_note_trigger($credit_note_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $credit_note_id;
    $data['rel_type'] = 'credit_notes';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_add_invoice_payment_trigger description]
 * @param  [type] $payment_id [description]
 * @return [type]             [description]
 */
function wa_delete_credit_note_trigger($credit_note_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $credit_note_id;
    $data['rel_type'] = 'credit_notes';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_add_purchase_order_trigger description]
 * @return [type] [description]
 */
function wa_add_purchase_order_trigger($po_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $po_id;
    $data['rel_type'] = 'purchase_order';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [ description]
 * @retwa_update_purchase_order_triggerurn [type] [description]
 */
function wa_update_purchase_order_trigger($po_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $po_id;
    $data['rel_type'] = 'purchase_order';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [ description]
 * @retwa_delete_purchase_order_triggerurn [type] [description]
 */
function wa_delete_purchase_order_trigger($po_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $po_id;
    $data['rel_type'] = 'purchase_order';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_change_approval_status_purchase_order_trigger description]
 * @return [type] [description]
 */
function wa_change_approval_status_purchase_order_trigger($po_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $po_id;
    $data['rel_type'] = 'purchase_order';
    $data['start_case'] = 'change_approval_status';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_change_delivery_status_purchase_order_trigger description]
 * @return [type] [description]
 */
function wa_change_delivery_status_purchase_order_trigger($po_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $po_id;
    $data['rel_type'] = 'purchase_order';
    $data['start_case'] = 'change_delivery_status';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_add_purchase_request_trigger description]
 * @return [type] [description]
 */
function wa_add_purchase_request_trigger($pr_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $pr_id;
    $data['rel_type'] = 'purchase_request';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_purchase_request_trigger description]
 * @return [type] [description]
 */
function wa_update_purchase_request_trigger($pr_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $pr_id;
    $data['rel_type'] = 'purchase_request';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_purchase_request_trigger description]
 * @return [type] [description]
 */
function wa_delete_purchase_request_trigger($pr_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $pr_id;
    $data['rel_type'] = 'purchase_request';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_change_status_purchase_request_trigger description]
 * @return [type] [description]
 */
function wa_change_status_purchase_request_trigger($pr_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $pr_id;
    $data['rel_type'] = 'purchase_request';
    $data['start_case'] = 'change_approval_status';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_purchase_quotation_trigger description]
 * @return [type] [description]
 */
function wa_create_purchase_quotation_trigger($pq_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $pq_id;
    $data['rel_type'] = 'purchase_quotation';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_purchase_quotation_trigger description]
 * @return [type] [description]
 */
function wa_update_purchase_quotation_trigger($pq_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $pq_id;
    $data['rel_type'] = 'purchase_quotation';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_purchase_quotation_trigger description]
 * @return [type] [description]
 */
function wa_delete_purchase_quotation_trigger($pq_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $pq_id;
    $data['rel_type'] = 'purchase_quotation';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_purchase_quotation_trigger description]
 * @return [type] [description]
 */
function wa_change_status_purchase_quotation_trigger($pq_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $pq_id;
    $data['rel_type'] = 'purchase_quotation';
    $data['start_case'] = 'change_approval_status';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_purchase_invoice_trigger description]
 * @return [type] [description]
 */
function wa_create_purchase_invoice_trigger($pi_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $pi_id;
    $data['rel_type'] = 'purchase_invoice';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_purchase_invoice_trigger description]
 * @return [type] [description]
 */
function wa_update_purchase_invoice_trigger($pi_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $pi_id;
    $data['rel_type'] = 'purchase_invoice';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_purchase_invoice_trigger description]
 * @return [type] [description]
 */
function wa_delete_purchase_invoice_trigger($pi_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $pi_id;
    $data['rel_type'] = 'purchase_invoice';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_purchase_invoice_trigger description]
 * @return [type] [description]
 */
function wa_change_status_purchase_invoice_trigger($pi_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $pi_id;
    $data['rel_type'] = 'purchase_invoice';
    $data['start_case'] = 'change_approval_status';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_purchase_contract_trigger description]
 * @return [type] [description]
 */
function wa_create_purchase_contract_trigger($contract_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $contract_id;
    $data['rel_type'] = 'purchase_contract';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_purchase_contract_trigger description]
 * @return [type] [description]
 */
function wa_update_purchase_contract_trigger($contract_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $contract_id;
    $data['rel_type'] = 'purchase_contract';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_purchase_contract_trigger description]
 * @return [type] [description]
 */
function wa_delete_purchase_contract_trigger($contract_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $contract_id;
    $data['rel_type'] = 'purchase_contract';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}


/**
 * [wa_signed_purchase_contract_trigger description]
 * @return [type] [description]
 */
function wa_signed_purchase_contract_trigger($contract_id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $contract_id;
    $data['rel_type'] = 'purchase_contract';
    $data['start_case'] = 'signed';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_vendor_trigger description]
 * @return [type] [description]
 */
function wa_create_vendor_trigger($_data){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $_data['id'];
    $data['rel_type'] = 'vendor';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_vendor_trigger description]
 * @return [type] [description]
 */
function wa_update_vendor_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'vendor';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_vendor_trigger description]
 * @return [type] [description]
 */
function wa_delete_vendor_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'vendor';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_vendor_contact_create_trigger description]
 * @return [type] [description]
 */
function wa_vendor_contact_create_trigger($id){
    $data = [];

    $CI = &get_instance();

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('purchase')){


        $CI->load->model('purchase/purchase_model');
        $contact = $CI->purchase_model->get_contact($id);

        if(isset($contact->userid)){
            $data['rel_id'] = $contact->userid;
            $data['rel_type'] = 'vendor';
            $data['start_case'] = 'vendor_contact_created';
            $CI->Workflow_automation_model->run_work_flows($data);
        }
    }
}

/**
 * [wa_create_cs_ticket_trigger description]
 * @return [type] [description]
 */
function wa_create_cs_ticket_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'ticket';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('customer_service')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_cs_ticket_trigger description]
 * @return [type] [description]
 */
function wa_update_cs_ticket_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'ticket';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('customer_service')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_cs_ticket_trigger description]
 * @return [type] [description]
 */
function wa_delete_cs_ticket_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'ticket';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    if(wa_get_status_modules('customer_service')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_staff_trigger description]
 * @return [type] [description]
 */
function wa_create_staff_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'staff';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('hr_profile')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_staff_trigger description]
 * @return [type] [description]
 */
function wa_update_staff_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'staff';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('hr_profile')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_staff_trigger description]
 * @return [type] [description]
 */
function wa_delete_staff_trigger($_data){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $_data['id'];
    $data['rel_type'] = 'staff';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('hr_profile')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_expense_trigger description]
 * @return [type] [description]
 */
function wa_create_expense_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'expense';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    
    $CI->Workflow_automation_model->run_work_flows($data);
    
}

/**
 * [wa_update_expense_trigger description]
 * @return [type] [description]
 */
function wa_update_expense_trigger($_data){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $_data['id'];
    $data['rel_type'] = 'expense';
    $data['start_case'] = 'updated';

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_delete_expense_trigger description]
 * @return [type] [description]
 */
function wa_delete_expense_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'expense';
    $data['start_case'] = 'deleted';

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}


/**
 * [wa_expense_converted_to_invoice description]
 * @return [type] [description]
 */
function wa_expense_converted_to_invoice($_data){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $_data['expense_id'];
    $data['rel_type'] = 'expense';
    $data['start_case'] = 'convert_to_invoice';

    $CI->load->model('workflow_automation/Workflow_automation_model');
    
    $CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * [wa_create_rec_plan_trigger description]
 * @return [type] [description]
 */
function wa_create_rec_plan_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'recruitment_plan';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_rec_plan_trigger description]
 * @return [type] [description]
 */
function wa_update_rec_plan_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'recruitment_plan';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_rec_plan_trigger description]
 * @return [type] [description]
 */
function wa_delete_rec_plan_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'recruitment_plan';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_rec_campaign_trigger description]
 * @return [type] [description]
 */
function wa_create_rec_campaign_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'recruitment_campaign';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_rec_campaign_trigger description]
 * @return [type] [description]
 */
function wa_update_rec_campaign_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'recruitment_campaign';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_rec_campaign_trigger description]
 * @return [type] [description]
 */
function wa_delete_rec_campaign_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'recruitment_campaign';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_rec_form_trigger description]
 * @return [type] [description]
 */
function wa_create_rec_form_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'recruitment_form';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_rec_form_trigger description]
 * @return [type] [description]
 */
function wa_update_rec_form_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'recruitment_form';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_rec_form_trigger description]
 * @return [type] [description]
 */
function wa_delete_rec_form_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'recruitment_form';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_rec_candidate_trigger description]
 * @return [type] [description]
 */
function wa_create_rec_candidate_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'candidate';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_rec_candidate_trigger description]
 * @return [type] [description]
 */
function wa_update_rec_candidate_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'candidate';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_rec_candidate_trigger description]
 * @return [type] [description]
 */
function wa_delete_rec_candidate_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'candidate';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_rated_rec_candidate_trigger description]
 * @return [type] [description]
 */
function wa_rated_rec_candidate_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'candidate';
    $data['start_case'] = 'rated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_rec_interview_schedule_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_create_rec_interview_schedule_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'interview_schedule';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_rec_interview_schedule_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_update_rec_interview_schedule_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'interview_schedule';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_rec_interview_schedule_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_delete_rec_interview_schedule_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'interview_schedule';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('recruitment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_leave_request_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_create_leave_request_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'leave_request';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('timesheets')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_leave_request_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_delete_leave_request_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'leave_request';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('timesheets')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}


/**
 * [wa_create_additional_work_hours_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_create_additional_work_hours_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'additional_work_hours';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('timesheets')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_additional_work_hours_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_delete_additional_work_hours_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'additional_work_hours';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('timesheets')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_vehicle_trigger description]
 * @return [type] [description]
 */
function wa_create_vehicle_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'vehicle';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_vehicle_trigger description]
 * @return [type] [description]
 */
function wa_update_vehicle_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'vehicle';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_vehicle_trigger description]
 * @return [type] [description]
 */
function wa_delete_vehicle_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'vehicle';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_vehicle_assignment_trigger description]
 * @return [type] [description]
 */
function wa_create_vehicle_assignment_trigger($vehicle_assignment_id){
    $data = [];

    $CI = &get_instance();
    

    $data['vehicle_assignment'] = $vehicle_assignment_id;
    if(wa_get_status_modules('fleet')){

        $CI->load->model('fleet/fleet_model');
        $vehicle_assignment = $CI->fleet_model->get_vehicle_assignment($vehicle_assignment_id);

        if(isset($vehicle_assignment->vehicle_id)){

            $data['rel_id'] = $vehicle_assignment->vehicle_id;
            $data['rel_type'] = 'vehicle';
            $data['start_case'] = 'create_assignment';


            $CI->load->model('workflow_automation/Workflow_automation_model');
            $CI->Workflow_automation_model->run_work_flows($data);

        }
    }
    
}

/**
 * [wa_create_work_performance_trigger description]
 * @return [type] [description]
 */
function wa_create_work_performance_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'workperformance';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_work_performance_trigger description]
 * @return [type] [description]
 */
function wa_update_work_performance_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'workperformance';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_work_performance_trigger description]
 * @return [type] [description]
 */
function wa_delete_work_performance_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'workperformance';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_fleet_event_trigger description]
 * @return [type] [description]
 */
function wa_create_fleet_event_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'event';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_fleet_event_trigger description]
 * @return [type] [description]
 */
function wa_update_fleet_event_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'event';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_fleet_event_trigger description]
 * @return [type] [description]
 */
function wa_delete_fleet_event_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'event';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_fleet_work_order description]
 * @return [type] [description]
 */
function wa_create_fleet_work_order($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'work_order';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_fleet_work_order description]
 * @return [type] [description]
 */
function wa_update_fleet_work_order($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'work_order';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_fleet_work_order description]
 * @return [type] [description]
 */
function wa_delete_fleet_work_order($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'work_order';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_fleet_booking description]
 * @return [type] [description]
 */
function wa_create_fleet_booking($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'booking';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_fleet_booking description]
 * @return [type] [description]
 */
function wa_update_fleet_booking($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'booking';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_fleet_booking description]
 * @return [type] [description]
 */
function wa_delete_fleet_booking($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'booking';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_fleet_fuel description]
 * @return [type] [description]
 */
function wa_create_fleet_fuel($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'fuel';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}


/**
 * [wa_update_fleet_fuel description]
 * @return [type] [description]
 */
function wa_update_fleet_fuel($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'fuel';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_fleet_fuel description]
 * @return [type] [description]
 */
function wa_delete_fleet_fuel($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'fuel';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fleet')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_work_center_trigger description]
 * @return [type] [description]
 */
function wa_create_work_center_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'work_center';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_work_center_trigger description]
 * @return [type] [description]
 */
function wa_update_work_center_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'work_center';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_work_center_trigger description]
 * @return [type] [description]
 */
function wa_delete_work_center_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'work_center';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_routing_trigger description]
 * @return [type] [description]
 */
function wa_create_routing_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'routing';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_routing_trigger description]
 * @return [type] [description]
 */
function wa_update_routing_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'routing';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_routing_trigger description]
 * @return [type] [description]
 */
function wa_delete_routing_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'routing';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_operation_trigger description]
 * @return [type] [description]
 */
function wa_create_operation_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'operation';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_operation_trigger description]
 * @return [type] [description]
 */
function wa_update_operation_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'operation';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_operation_trigger description]
 * @return [type] [description]
 */
function wa_delete_operation_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'operation';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_bill_of_material_trigger description]
 * @return [type] [description]
 */
function wa_create_bill_of_material_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'bill_of_material';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_bill_of_material_trigger description]
 * @return [type] [description]
 */
function wa_update_bill_of_material_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'bill_of_material';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

 /**
 * [wa_delete_bill_of_material_trigger description]
 * @return [type] [description]
 */
function wa_delete_bill_of_material_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'bill_of_material';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_bom_component_trigger description]
 * @return [type] [description]
 */
function wa_create_bom_component_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'bom_component';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_bom_component_trigger description]
 * @return [type] [description]
 */
function wa_update_bom_component_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'bom_component';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_bom_component_trigger description]
 * @return [type] [description]
 */
function wa_delete_bom_component_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'bom_component';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_manufacturing_order_trigger description]
 * @return [type] [description]
 */
function wa_create_manufacturing_order_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'manufacturing_order';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_manufacturing_order_trigger description]
 * @return [type] [description]
 */
function wa_update_manufacturing_order_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'manufacturing_order';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_manufacturing_order_trigger description]
 * @return [type] [description]
 */
function wa_delete_manufacturing_order_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'manufacturing_order';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('manufacturing')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_omni_sale_order_trigger description]
 * @return [type] [description]
 */
function wa_create_omni_sale_order_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'omni_sales_order';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('omni_sales')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_omni_sale_order_trigger description]
 * @return [type] [description]
 */
function wa_update_omni_sale_order_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'omni_sales_order';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('omni_sales')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_omni_sale_order_trigger description]
 * @return [type] [description]
 */
function wa_delete_omni_sale_order_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'omni_sales_order';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('omni_sales')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_omni_sales_refund_trigger description]
 * @return [type] [description]
 */
function wa_create_omni_sales_refund_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'omni_sales_refund';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('omni_sales')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_omni_sales_refund_trigger description]
 * @return [type] [description]
 */
function wa_update_omni_sales_refund_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'omni_sales_refund';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('omni_sales')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_omni_sales_refund_trigger description]
 * @return [type] [description]
 */
function wa_delete_omni_sales_refund_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'omni_sales_refund';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('omni_sales')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_omni_sale_trade_discount_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_create_omni_sale_trade_discount_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'trade_discount';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('omni_sales')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_omni_sale_trade_discount_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_update_omni_sale_trade_discount_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'trade_discount';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('omni_sales')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_omni_sale_trade_discount_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_delete_omni_sale_trade_discount_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'trade_discount';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('omni_sales')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_fe_asset_trigger description]
 * @return [type] [description]
 */
function wa_create_fe_asset_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'asset';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_fe_asset_trigger description]
 * @return [type] [description]
 */
function wa_update_fe_asset_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'asset';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_fe_asset_trigger description]
 * @return [type] [description]
 */
function wa_delete_fe_asset_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'asset';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_fe_license_trigger description]
 * @return [type] [description]
 */
function wa_create_fe_license_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'license';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_fe_license_trigger description]
 * @return [type] [description]
 */
function wa_update_fe_license_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'license';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_fe_license_trigger description]
 * @return [type] [description]
 */
function wa_delete_fe_license_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'license';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_fe_accessories_trigger description]
 * @return [type] [description]
 */
function wa_create_fe_accessories_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'accessories';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}


/**
 * [wa_update_fe_accessories_trigger description]
 * @return [type] [description]
 */
function wa_update_fe_accessories_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'accessories';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_fe_accessories_trigger description]
 * @return [type] [description]
 */
function wa_delete_fe_accessories_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'accessories';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_fe_consumable_trigger description]
 * @return [type] [description]
 */
function wa_create_fe_consumable_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'consumable';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_fe_consumable_trigger description]
 * @return [type] [description]
 */
function wa_update_fe_consumable_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'consumable';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_fe_consumable_trigger description]
 * @return [type] [description]
 */
function wa_delete_fe_consumable_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'consumable';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}


/**
 * [wa_create_fe_component_trigger description]
 * @return [type] [description]
 */
function wa_create_fe_component_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'component';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}


/**
 * [wa_update_fe_component_trigger description]
 * @return [type] [description]
 */
function wa_update_fe_component_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'component';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}


/**
 * [wa_delete_fe_component_trigger description]
 * @return [type] [description]
 */
function wa_delete_fe_component_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'component';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('fixed_equipment')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_payslip_trigger description]
 * @return [type] [description]
 */
function wa_create_payslip_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'payslip';
    $data['start_case'] = 'create_payslip';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('hr_payroll')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_payslip_trigger description]
 * @return [type] [description]
 */
function wa_update_payslip_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'payslip';
    $data['start_case'] = 'update_payslip';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('hr_payroll')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_payslip_trigger description]
 * @return [type] [description]
 */
function wa_delete_payslip_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'payslip';
    $data['start_case'] = 'delete_payslip';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('hr_payroll')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_payslip_template_trigger description]
 * @return [type] [description]
 */
function wa_create_payslip_template_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'payslip_template';
    $data['start_case'] = 'create_payslip_template';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('hr_payroll')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_payslip_template_trigger description]
 * @return [type] [description]
 */
function wa_update_payslip_template_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'payslip_template';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('hr_payroll')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_payslip_template_trigger description]
 * @return [type] [description]
 */
function wa_delete_payslip_template_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'payslip_template';
    $data['start_case'] = 'delete_payslip_template';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('hr_payroll')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_wh_item_trigger description]
 * @return [type] [description]
 */
function wa_create_wh_item_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'items';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('warehouse')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_wh_item_trigger description]
 * @return [type] [description]
 */
function wa_update_wh_item_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'items';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('warehouse')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_delete_wh_item_trigger description]
 * @return [type] [description]
 */
function wa_delete_wh_item_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'items';
    $data['start_case'] = 'deleted';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('warehouse')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_wh_goods_receipt_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_create_wh_goods_receipt_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'inventory_receiving_voucher';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('warehouse')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_wh_goods_receipt_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_update_wh_goods_receipt_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'inventory_receiving_voucher';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('warehouse')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_wh_goods_delivery_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_create_wh_goods_delivery_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'inventory_delivery_voucher';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('warehouse')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_update_wh_goods_delivery_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_update_wh_goods_delivery_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'inventory_delivery_voucher';
    $data['start_case'] = 'updated';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('warehouse')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_create_wh_packing_list_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_create_wh_packing_list_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'packing_list';
    $data['start_case'] = 'created';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('warehouse')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [wa_change_delivery_status_wh_packing_list_trigger description]
 * @param  [type] $id [description]
 * @return [type]     [description]
 */
function wa_change_delivery_status_wh_packing_list_trigger($id){
    $data = [];

    $CI = &get_instance();
    
    $data['rel_id'] = $id;
    $data['rel_type'] = 'packing_list';
    $data['start_case'] = 'change_delivery_status';


    $CI->load->model('workflow_automation/Workflow_automation_model');

    if(wa_get_status_modules('warehouse')){
        $CI->Workflow_automation_model->run_work_flows($data);
    }
}

/**
 * [timer_run_workflow description]
 * @return [type] [description]
 */
function timer_run_workflow($manualy){
	$CI = &get_instance();

	$data = [];


    $data['rel_type'] = 'automatic';
    $data['rel_id'] = 0;
    $data['start_case'] = 'automatic';

	$CI->load->model('workflow_automation/Workflow_automation_model');

	$CI->Workflow_automation_model->run_work_flows($data);
}

/**
 * Register other merge fields for purchase
 *
 * @param [array] $for
 * @return void
 */
function workflow_register_other_merge_fields($for) {
    $for[] = 'workflow_automation';
 
    return $for;
}