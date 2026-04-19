<?php
defined('BASEPATH') or exit('No direct script access allowed');

hooks()->add_action('after_email_templates', 'add_wa_email_templates');
hooks()->add_action('workflow_automation_init',WORKFLOW_AUTOMATION_MODULE_NAME.'_appint');
hooks()->add_action('pre_activate_module', WORKFLOW_AUTOMATION_MODULE_NAME.'_preactivate');
hooks()->add_action('pre_deactivate_module', WORKFLOW_AUTOMATION_MODULE_NAME.'_predeactivate');
/**
 * [wa_html_entity_decode description]
 * @return [type] [description]
 */
function wa_html_entity_decode($str){
	return html_entity_decode($str ?? '');
}

/**
 * Gets the status modules wa.
 *
 * @param      string   $module_name  The module name
 *
 * @return     boolean  The status modules wa.
 */
function wa_get_status_modules($module_name){
    $CI             = &get_instance();
    $sql = 'select * from '.db_prefix().'modules where module_name = "'.$module_name.'" AND active =1 ';
    $module = $CI->db->query($sql)->row();
    if($module){
        return true;
    }else{
        return false;
    }
}

/**
 * [wa_get_start_case_by_type description]
 * @param  [type] $type [description]
 * @return [type]       [description]
 */
function wa_get_start_case_by_type($type){
	$start_case = [];
	switch ($type) {
        case 'tasks':
			$start_case = [
			  ['id' => 'created', 'name' => _l('wa_created')],
			  ['id' => 'updated', 'name' => _l('wa_updated')],
			  ['id' => 'deleted', 'name' => _l('wa_deleted')],
			  ['id' => 'change_status', 'name' => _l('wa_change_status')],

			];
		break; 

		case 'projects':
			$start_case = [
			  ['id' => 'created', 'name' => _l('wa_created')],
			  ['id' => 'updated', 'name' => _l('wa_updated')],
			  ['id' => 'deleted', 'name' => _l('wa_deleted')],
			  ['id' => 'change_status', 'name' => _l('wa_change_status')],
			]; 
		break;

		case 'contracts':
			$start_case = [
			  ['id' => 'created', 'name' => _l('wa_created')],
			  ['id' => 'updated', 'name' => _l('wa_updated')],
			  ['id' => 'deleted', 'name' => _l('wa_deleted')],
			  
			]; 
		break;

		case 'leads':
			$start_case = [
			  ['id' => 'created', 'name' => _l('wa_created')],
			  ['id' => 'updated', 'name' => _l('wa_updated')],
			  ['id' => 'deleted', 'name' => _l('wa_deleted')],
			  ['id' => 'converted', 'name' => _l('wa_converted')],
			]; 
		break;

		case 'customers':
			$start_case = [
			  ['id' => 'created', 'name' => _l('wa_created')],
			  ['id' => 'contact_created', 'name' => _l('wa_contact_created')],
			  ['id' => 'updated', 'name' => _l('wa_updated')],
			  ['id' => 'deleted', 'name' => _l('wa_deleted')],

			]; 
		break;

		case 'proposals':
			$start_case = [
			  ['id' => 'created', 'name' => _l('wa_created')],
			  ['id' => 'updated', 'name' => _l('wa_updated')],
			  ['id' => 'deleted', 'name' => _l('wa_deleted')],
			  ['id' => 'convert_to_estimate', 'name' => _l('wa_convert_to_estimate')],
			  ['id' => 'convert_to_invoice', 'name' => _l('wa_convert_to_invoice')],
	
			]; 
		break;


		case 'estimates':
			$start_case = [
			  ['id' => 'created', 'name' => _l('wa_created')],
			  ['id' => 'updated', 'name' => _l('wa_updated')],
			  ['id' => 'deleted', 'name' => _l('wa_deleted')],
			  ['id' => 'convert_to_invoice', 'name' => _l('wa_convert_to_invoice')],
			]; 
		break;


		case 'invoices':
			$start_case = [
			  ['id' => 'created', 'name' => _l('wa_created')],
			  ['id' => 'updated', 'name' => _l('wa_updated')],
			  ['id' => 'deleted', 'name' => _l('wa_deleted')],
			  ['id' => 'mark_as_cancelled', 'name' => _l('wa_mark_as_cancelled')],
			  ['id' => 'unmark_as_cancelled', 'name' => _l('wa_unmark_as_cancelled')],
			]; 
		break;

		case 'payment':
			$start_case = [
			  ['id' => 'created', 'name' => _l('wa_created')],
			  ['id' => 'updated', 'name' => _l('wa_updated')],
			  ['id' => 'deleted', 'name' => _l('wa_deleted')],

			]; 
		break;


		case 'credit_notes':
			$start_case = [
			  ['id' => 'created', 'name' => _l('wa_created')],
			  ['id' => 'updated', 'name' => _l('wa_updated')],
			  ['id' => 'deleted', 'name' => _l('wa_deleted')],

			]; 
		break;


		case 'service_modules':
			if(wa_get_status_modules('service_management')){
				$start_case = [
				  ['id' => 'service_created', 'name' => _l('wa_service_created')],
				  ['id' => 'service_updated', 'name' => _l('wa_service_updated')],
				  ['id' => 'service_deleted', 'name' => _l('wa_service_deleted')],
				  ['id' => 'subscription_service_created', 'name' => _l('wa_subscription_service_created')],
				  ['id' => 'subscription_service_updated', 'name' => _l('wa_subscription_service_updated')],
				  ['id' => 'subscription_service_deleted', 'name' => _l('wa_subscription_service_deleted')],
				  ['id' => 'order_created', 'name' => _l('wa_order_created')],
				  ['id' => 'order_updated', 'name' => _l('wa_order_updated')],
				  ['id' => 'order_deleted', 'name' => _l('wa_order_deleted')],
				  ['id' => 'subscription_created', 'name' => _l('wa_subscription_created')],
				]; 
			}
		break;

		case 'warranties_modules':
			if(wa_get_status_modules('warranty_management')){
				$start_case = [
				  ['id' => 'warranty_claim_created', 'name' => _l('wa_warranty_claim_created')],
				  ['id' => 'warranty_claim_updated', 'name' => _l('wa_warranty_claim_updated')],
				  ['id' => 'warranty_claim_deleted', 'name' => _l('wa_warranty_claim_deleted')],
				]; 
			}
		break;

		case 'customer_service_modules':
			if(wa_get_status_modules('customer_service')){
				$start_case = [
				  ['id' => 'sla_created', 'name' => _l('wa_sla_created')],
				  ['id' => 'sla_updated', 'name' => _l('wa_sla_updated')],
				  ['id' => 'sla_deleted', 'name' => _l('wa_sla_deleted')],
				  ['id' => 'kpi_created', 'name' => _l('wa_kpi_created')],
				  ['id' => 'kpi_updated', 'name' => _l('wa_kpi_updated')],
				  ['id' => 'kpi_deleted', 'name' => _l('wa_kpi_deleted')],
				  ['id' => 'workflow_created', 'name' => _l('wa_workflow_created')],
				  ['id' => 'workflow_updated', 'name' => _l('wa_workflow_updated')],
				  ['id' => 'workflow_deleted', 'name' => _l('wa_workflow_deleted')],
				  ['id' => 'category_created', 'name' => _l('wa_category_created')],
				  ['id' => 'category_updated', 'name' => _l('wa_category_updated')],
				  ['id' => 'category_deleted', 'name' => _l('wa_category_deleted')],
				]; 
			}
		break;

		case 'purchase_order':
			if(wa_get_status_modules('purchase')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
					['id' => 'change_approval_status', 'name' => _l('wa_change_approval_status')],
					['id' => 'change_delivery_status', 'name' => _l('wa_change_delivery_status')],

				];
			}

		break;

		case 'purchase_request':
			if(wa_get_status_modules('purchase')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
					['id' => 'change_approval_status', 'name' => _l('wa_change_approval_status')],
					
				];
			}

		break;

		case 'purchase_quotation':
			if(wa_get_status_modules('purchase')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
					['id' => 'change_approval_status', 'name' => _l('wa_change_approval_status')],
					
				];
			}

		break;


		case 'purchase_invoice':
			if(wa_get_status_modules('purchase')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
					['id' => 'change_approval_status', 'name' => _l('wa_change_approval_status')],
					
				];
			}

		break;


		case 'purchase_contract':
			if(wa_get_status_modules('purchase')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
					['id' => 'signed', 'name' => _l('wa_signed')],
					
				];
			}

		break;


		case 'vendor':
			if(wa_get_status_modules('purchase')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
					['id' => 'vendor_contact_created', 'name' => _l('wa_vendor_contact_created')],
					
				];
			}

		break;


		case 'ticket':
			if(wa_get_status_modules('customer_service')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],

				];
			}

		break;

		case 'staff':
			if(wa_get_status_modules('hr_profile')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;


		case 'expense':
			
			$start_case = [
				['id' => 'created', 'name' => _l('wa_created')],
				['id' => 'updated', 'name' => _l('wa_updated')],
				['id' => 'deleted', 'name' => _l('wa_deleted')],
				['id' => 'convert_to_invoice', 'name' => _l('wa_convert_to_invoice')],
			];
			
		break;

		case 'subscription':
			$start_case = [
				['id' => 'created', 'name' => _l('wa_created')],
				['id' => 'updated', 'name' => _l('wa_updated')],
				['id' => 'deleted', 'name' => _l('wa_deleted')],
				['id' => 'create_invoice', 'name' => _l('wa_create_invoice')],
			];
			
		break;


		case 'recruitment_plan':
			if(wa_get_status_modules('recruitment')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],

				];
			}
		break;


		case 'recruitment_campaign':
			if(wa_get_status_modules('recruitment')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],

				];
			}
		break;

		case 'recruitment_form':
			if(wa_get_status_modules('recruitment')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],

				];
			}
		break;


		case 'candidate':
			if(wa_get_status_modules('recruitment')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
					['id' => 'rated', 'name' => _l('wa_rated')],
				];
			}
		break;


		case 'interview_schedule':
			if(wa_get_status_modules('recruitment')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],

				];
			}
		break;

		case 'leave_request':
			if(wa_get_status_modules('timesheets')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'additional_work_hours':
			if(wa_get_status_modules('timesheets')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}

		break;

		case 'attendance':
			if(wa_get_status_modules('timesheets')){
				$start_case = [
					['id' => 'close', 'name' => _l('wa_close')],
					['id' => 'reopen', 'name' => _l('wa_reopen')],
					['id' => 'checkin', 'name' => _l('wa_checkin')],
					['id' => 'checkout', 'name' => _l('wa_checkout')],
				];
			}	
		break;

		case 'vehicle':
			if(wa_get_status_modules('fleet')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
					['id' => 'create_assignment', 'name' => _l('wa_create_assignment')],
				];
			}
		break;

		case 'workperformance':
			if(wa_get_status_modules('fleet')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'event':
			if(wa_get_status_modules('fleet')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'work_order':
			if(wa_get_status_modules('fleet')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'booking':
			if(wa_get_status_modules('fleet')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'fuel':
			if(wa_get_status_modules('fleet')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'work_center':
			if(wa_get_status_modules('manufacturing')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'routing':
			if(wa_get_status_modules('manufacturing')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'operation':
			if(wa_get_status_modules('manufacturing')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'bill_of_material':
			if(wa_get_status_modules('manufacturing')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'bom_component':
			if(wa_get_status_modules('manufacturing')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'manufacturing_order':
			if(wa_get_status_modules('manufacturing')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'omni_sales_order':
			if(wa_get_status_modules('omni_sales')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;


		case 'omni_sales_refund':
			if(wa_get_status_modules('omni_sales')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'trade_discount':
			if(wa_get_status_modules('omni_sales')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'voucher':
			if(wa_get_status_modules('omni_sales')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'asset':
			if(wa_get_status_modules('fixed_equipment')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
					
				];
			}
		break;

		case 'license':
			if(wa_get_status_modules('fixed_equipment')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],

				];
			}
		break;

		case 'accessories':
			if(wa_get_status_modules('fixed_equipment')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],

				];
			}
		break;

		case 'consumable':
			if(wa_get_status_modules('fixed_equipment')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],

				];
			}
		break;

		case 'component':
			if(wa_get_status_modules('fixed_equipment')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
					
				];
			}
		break;

		case 'payslip':
			if(wa_get_status_modules('hr_payroll')){
				$start_case = [
					['id' => 'create_payslip', 'name' => _l('wa_create_payslip')],
					['id' => 'delete_payslip', 'name' => _l('wa_delete_payslip')],
					['id' => 'update_payslip', 'name' => _l('wa_update_payslip')],
				];
			}
		break;

		case 'payslip_template':
			if(wa_get_status_modules('hr_payroll')){
				$start_case = [
					['id' => 'create_payslip_template', 'name' => _l('wa_create_payslip_template')],
					['id' => 'delete_payslip_template', 'name' => _l('wa_delete_payslip_template')],

				];
			}
		break;

		case 'items':
			if(wa_get_status_modules('warehouse')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		case 'opening_stock':
			if(wa_get_status_modules('warehouse')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
				];
			}
		break;

		case 'inventory_receiving_voucher':
			if(wa_get_status_modules('warehouse')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],

				];
			}
		break;

		case 'inventory_delivery_voucher':
			if(wa_get_status_modules('warehouse')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],

				];
			}
		break;

		case 'packing_list':
			if(wa_get_status_modules('warehouse')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					
				];
			}
		break;

		case 'inventory_delivery_note':
			if(wa_get_status_modules('warehouse')){
				$start_case = [
					['id' => 'created', 'name' => _l('wa_created')],
					['id' => 'updated', 'name' => _l('wa_updated')],
					['id' => 'deleted', 'name' => _l('wa_deleted')],
				];
			}
		break;

		default:

        break;
    }

	return $start_case;
}

/**
 * [wa_get_check_by_type description]
 * @param  [type] $type [description]
 * @return [type]       [description]
 */
function wa_get_check_by_type($type){
	$checks = [];
	switch ($type) {
        case 'tasks':
			$checks =  [
	            ['id' => 'addedfrom','name' => _l('wa_addedfrom')],
	            ['id' => 'status','name' => _l('wa_status')],
	            ['id' => 'priority','name' => _l('wa_priority')],
	             
	        ];
		break; 

		case 'projects':
			$checks =  [
	            ['id' => 'addedfrom','name' => _l('wa_addedfrom')],
	            ['id' => 'status','name' => _l('wa_status')],
	            ['id' => 'client','name' => _l('wa_client')],
	        ];
		break;

		case 'contracts':
			$checks =  [
	            ['id' => 'addedfrom','name' => _l('wa_addedfrom')],
	            ['id' => 'client','name' => _l('wa_client')],
	            ['id' => 'project','name' => _l('wa_project')],
	        ];
        break;

        case 'leads':
			$checks =  [
	            ['id' => 'addedfrom','name' => _l('wa_addedfrom')],
	            ['id' => 'status','name' => _l('wa_status')],
	            ['id' => 'source','name' => _l('wa_source')],
	            ['id' => 'assign_to_staff', 'name' => _l('wa_assign_to_staff')], 
	        ];
        break;

        case 'customers':
			$checks =  [
	            ['id' => 'addedfrom','name' => _l('wa_addedfrom')],
	            ['id' => 'assign_to_staff', 'name' => _l('wa_assign_to_staff')], 
	        ];
        break;

        case 'proposals':
			$checks =  [
	            ['id' => 'status','name' => _l('wa_status')],
	            ['id' => 'project', 'name' => _l('wa_project')], 
	            ['id' => 'customer', 'name' => _l('wa_customer')], 
	            ['id' => 'lead', 'name' => _l('wa_lead')],
	            ['id' => 'total_value', 'name' => _l('wa_total_value')], 
	        ];
        break;

        case 'estimates':
			$checks =  [
	            ['id' => 'status','name' => _l('wa_status')],
	            ['id' => 'project', 'name' => _l('wa_project')], 
	            ['id' => 'customer', 'name' => _l('wa_customer')], 
	            ['id' => 'total_value', 'name' => _l('wa_total_value')], 
	        ];
        break;

        case 'invoices':
			$checks =  [
	            ['id' => 'status','name' => _l('wa_status')],
	            ['id' => 'project', 'name' => _l('wa_project')], 
	            ['id' => 'customer', 'name' => _l('wa_customer')], 
	            ['id' => 'total_value', 'name' => _l('wa_total_value')], 
	        ];
        break;

        case 'payment':
			$checks =  [
	
	            ['id' => 'customer', 'name' => _l('wa_customer')], 
	            ['id' => 'paymentmode', 'name' => _l('wa_paymentmode')], 
	            ['id' => 'invoice_status', 'name' => _l('wa_invoice_status')], 
	        ];
        break;

        case 'credit_notes':
			$checks =  [
	            ['id' => 'status','name' => _l('wa_status')],
	 
	            ['id' => 'customer', 'name' => _l('wa_customer')], 
	            ['id' => 'total_value', 'name' => _l('wa_total_value')], 
	            ['id' => 'remaining_amount', 'name' => _l('wa_remaining_amount')], 
	        ];
        break;

        case 'purchase_order':
        	if(wa_get_status_modules('purchase')){
	        	$checks = [
	        		['id' => 'approval_status', 'name' => _l('wa_approval_status')],
	        		['id' => 'order_status', 'name' => _l('wa_order_status')],
	        		['id' => 'vendor', 'name' => _l('wa_vendor')],
	        		['id' => 'department', 'name' => _l('wa_department')],
	        		['id' => 'type', 'name' => _l('wa_type')],
	        		['id' => 'person_in_charge', 'name' => _l('wa_person_in_charge')],
	        	];
	        }
        	
        break;

        case 'purchase_request':
        	if(wa_get_status_modules('purchase')){
	        	$checks = [
	        		['id' => 'approval_status', 'name' => _l('wa_approval_status')],
	        		['id' => 'project', 'name' => _l('wa_project')],
	        		['id' => 'department', 'name' => _l('wa_department')],
	        		['id' => 'type', 'name' => _l('wa_type')],
	        		['id' => 'requestor', 'name' => _l('wa_requestor')],
	        	];
	        }
        	
        break;

        case 'purchase_quotation':
        	if(wa_get_status_modules('purchase')){
	        	$checks = [
	        		['id' => 'approval_status', 'name' => _l('wa_approval_status')],
	        		['id' => 'vendor', 'name' => _l('wa_vendor')],
	        		['id' => 'buyer', 'name' => _l('wa_buyer')],
	        
	        	];
	        }
        	
        break;


        case 'purchase_invoice':
        	if(wa_get_status_modules('purchase')){
	        	$checks = [
	        		['id' => 'approval_status', 'name' => _l('wa_approval_status')],
	        		['id' => 'vendor', 'name' => _l('wa_vendor')],
	        
	        	];
	        }
        	
        break;


        case 'purchase_contract':
        	if(wa_get_status_modules('purchase')){
	        	$checks = [
	        		['id' => 'service_category', 'name' => _l('wa_service_category')],
	        		['id' => 'contract_value', 'name' => _l('wa_contract_value')],
	        		['id' => 'vendor', 'name' => _l('wa_vendor')],
	        		['id' => 'addedfrom', 'name' => _l('wa_added_from')],
	        		['id' => 'department', 'name' => _l('wa_department')],
	        
	        	];
	        }
        	
        break;


        case 'vendor':
        	if(wa_get_status_modules('purchase')){
	        	$checks = [
	        		['id' => 'country', 'name' => _l('wa_country')],
	        		['id' => 'category', 'name' => _l('wa_category')],
	        		['id' => 'city', 'name' => _l('wa_city')],
	        		
	        
	        	];
	        }
        	
        break;

        case 'ticket':
        	if(wa_get_status_modules('customer_service')){
	        	$checks = [
	        		['id' => 'summary', 'name' => _l('wa_summary')],
	        		['id' => 'priority', 'name' => _l('wa_priority')],
	        		['id' => 'status', 'name' => _l('wa_status')],
	        		['id' => 'type', 'name' => _l('wa_type')],
	        		['id' => 'customer', 'name' => _l('wa_customer')],
	        	];
	        }
        	
        break;

        case 'staff':
			if(wa_get_status_modules('hr_profile')){
				$checks = [
					['id' => 'status', 'name' => _l('wa_status')],
					['id' => 'role', 'name' => _l('wa_role')],
					['id' => 'department', 'name' => _l('wa_department')],	
					['id' => 'job_position', 'name' => _l('wa_job_position')],	
					['id' => 'gender', 'name' => _l('wa_gender')],
				];
			}
		break;

		case 'expense':
			
			$checks = [
				['id' => 'amount', 'name' => _l('wa_amount')],
				['id' => 'category', 'name' => _l('wa_category')],
				['id' => 'customer', 'name' => _l('wa_customer')],
				['id' => 'convert_to_invoice_status', 'name' => _l('wa_convert_to_invoice_status')],
				['id' => 'payment_mode', 'name' => _l('wa_payment_mode')],	
					
				['id' => 'is_billable', 'name' => _l('wa_is_billable')],
			];
			if(wa_get_status_modules('purchase')){

				$checks[] = ['id' => 'vendor', 'name' => _l('wa_vendor')];

			}
		
		break;


		case 'subscription':
			$checks = [
				['id' => 'status', 'name' => _l('wa_status')],
				['id' => 'clientid', 'name' => _l('wa_customer')],
				
			];
			
		break;


		case 'recruitment_plan':
			if(wa_get_status_modules('recruitment')){
				$checks = [
					
					['id' => 'status', 'name' => _l('wa_status')],
					['id' => 'department', 'name' => _l('wa_department')],
					['id' => 'position', 'name' => _l('wa_position')],
				];
			}
		break;

		case 'recruitment_campaign':
			if(wa_get_status_modules('recruitment')){
				$checks = [
					
					['id' => 'status', 'name' => _l('wa_status')],
					['id' => 'department', 'name' => _l('wa_department')],
					['id' => 'position', 'name' => _l('wa_position')],
					['id' => 'company', 'name' => _l('wa_company')],
					['id' => 'manager', 'name' => _l('wa_manager')],
				];
			}
		break;


		case 'recruitment_form':
			if(wa_get_status_modules('recruitment')){
				$checks = [
					
					['id' => 'status', 'name' => _l('wa_status')],
					['id' => 'responsible', 'name' => _l('wa_responsible_person')],
					['id' => 'language', 'name' => _l('wa_language')],

				];
			}
		break;

		case 'candidate':
			if(wa_get_status_modules('recruitment')){
				$checks = [
					['id' => 'status', 'name' => _l('wa_status')],
					['id' => 'desired_salary', 'name' => _l('wa_desired_salary')],
					['id' => 'marital_status', 'name' => _l('wa_marial_status')],
					['id' => 'campaign', 'name' => _l('wa_campaign')],
					['id' => 'seniority', 'name' => _l('wa_seniority')],
					['id' => 'gender', 'name' => _l('wa_gender')],
					['id' => 'skill', 'name' => _l('wa_skill')],
				];

			}
		break;

		case 'interview_schedule':
			if(wa_get_status_modules('recruitment')){
				$checks = [
					['id' => 'position', 'name' => _l('wa_position')],
					['id' => 'interviewer', 'name' => _l('wa_interviewer')],
				];
			}
		break;

		case 'leave_request':
			if(wa_get_status_modules('timesheets')){
				$checks = [
					['id' => 'status', 'name' => _l('wa_status')],
					['id' => 'type', 'name' => _l('wa_type')],
					['id' => 'handover_recipients', 'name' => _l('wa_handover_recipients')],
				];
			}
		break;

		case 'additional_work_hours':
			if(wa_get_status_modules('timesheets')){
				$checks = [
					['id' => 'status', 'name' => _l('wa_status')],
					['id' => 'timekeeping_type', 'name' => _l('wa_timekeeping_type')],
					['id' => 'creator', 'name' => _l('wa_creator')],
				];
			}
		break;

		case 'attendance':
			if(wa_get_status_modules('timesheets')){
				$checks = [

					['id' => 'role', 'name' => _l('wa_role')],
					['id' => 'staff', 'name' => _l('wa_staff')],
				];
			}
		break;

		case 'vehicle':
			if(wa_get_status_modules('fleet')){
				$checks = [

					['id' => 'status', 'name' => _l('wa_status')],
					['id' => 'vehicle_type', 'name' => _l('wa_vehicle_type')],
					['id' => 'ownership', 'name' => _l('wa_ownership')],
					['id' => 'vehicle_group_id', 'name' => _l('wa_vehicle_group')],
					['id' => 'body_type', 'name' => _l('model')],
				];
			}
		break;

		case 'workperformance':
			if(wa_get_status_modules('fleet')){
				$checks = [
					['id' => 'status', 'name' => _l('wa_status')],
					['id' => 'vehicle', 'name' => _l('wa_vehicle')],
					['id' => 'driver', 'name' => _l('wa_driver')],
				];
			}
		break;

		case 'event':
			if(wa_get_status_modules('fleet')){
				$checks = [
					['id' => 'event_type', 'name' => _l('wa_event_type')],
					['id' => 'vehicle', 'name' => _l('wa_vehicle')],
					['id' => 'driver', 'name' => _l('wa_driver')],
				];
			}
		break;


		case 'work_order':
			if(wa_get_status_modules('fleet')){
				$checks = [
					['id' => 'status', 'name' => _l('wa_status')],
					['id' => 'vehicle', 'name' => _l('wa_vehicle')],
					['id' => 'price', 'name' => _l('wa_price')],
				];
			}
		break;


		case 'booking':
			if(wa_get_status_modules('fleet')){
				$checks = [
					['id' => 'status', 'name' => _l('wa_status')],

				];
			}
		break;

		case 'fuel':
			if(wa_get_status_modules('fleet')){
				$checks = [
					['id' => 'vehicle', 'name' => _l('wa_vehicle')],
					['id' => 'price', 'name' => _l('wa_price')],
					['id' => 'type', 'name' => _l('wa_type')],
				];
			}

			if(wa_get_status_modules('purchase')){
				$checks[] = ['id' => 'vendor', 'name' => _l('wa_vendor')];

			}
		break;

		case 'work_center':
			if(wa_get_status_modules('manufacturing')){
				$checks = [
					['id' => 'working_hours', 'name' => _l('wa_working_hours')],
					['id' => 'oee_target', 'name' => _l('oee_target')],
					['id' => 'time_efficiency', 'name' => _l('time_efficiency')],
					['id' => 'costs_hour', 'name' => _l('costs_hour')],
					['id' => 'capacity', 'name' => _l('capacity')],	
				];
			}
		break;


		case 'routing':
			if(wa_get_status_modules('manufacturing')){
				$checks = [
					['id' => 'routing_name', 'name' => _l('wa_routing_name')],
					
				];
			}
		break;


		case 'operation':
			if(wa_get_status_modules('manufacturing')){
				$checks = [
					['id' => 'routing', 'name' => _l('wa_routing')],
					['id' => 'work_center', 'name' => _l('wa_work_center')],

				];
			}
		break;

		case 'bill_of_material':
			if(wa_get_status_modules('manufacturing')){
				$checks = [
					['id' => 'routing', 'name' => _l('wa_routing')],
					['id' => 'bom_type', 'name' => _l('wa_bom_type')],
					['id' => 'ready_to_produce', 'name' => _l('ready_to_produce')],
					['id' => 'consumption', 'name' => _l('consumption')],
				];
			}
		break;

		case 'bom_component':
			if(wa_get_status_modules('manufacturing')){
				$checks = [
					['id' => 'component', 'name' => _l('wa_component')],
					['id' => 'product_qty', 'name' => _l('product_qty')],
					['id' => 'unit_id', 'name' => _l('unit_of_measure')],
				];
			}
		break;

		case 'manufacturing_order':
			if(wa_get_status_modules('manufacturing')){
				$checks = [
					['id' => 'bom', 'name' => _l('wa_bom')],
					['id' => 'unit_id', 'name' => _l('unit_of_measure')],
					['id' => 'staff_id', 'name' => _l('responsible')],
					['id' => 'product_qty', 'name' => _l('product_qty')],
				];
			}
		break;

		case 'omni_sales_order':
			if(wa_get_status_modules('omni_sales')){
				$checks = [
					['id' => 'status', 'name' => _l('status')],
					['id' => 'channel', 'name' => _l('wa_channel')],
					['id' => 'customer', 'name' => _l('wa_customer')],
					['id' => 'sale_agent', 'name' => _l('wa_sale_agent')],
				];
			}
		break;

		case 'omni_sales_refund':
			if(wa_get_status_modules('omni_sales')){
				$checks = [
					['id' => 'amount', 'name' => _l('wa_amount')],
					['id' => 'paymentmode', 'name' => _l('payment_mode')],
	
				];
			}
		break;

		case 'trade_discount':
			if(wa_get_status_modules('omni_sales')){
				$checks = [
					['id' => 'channel', 'name' => _l('channel')],
					['id' => 'client_groups', 'name' => _l('client_groups')],
					['id' => 'customer', 'name' => _l('customer')],
					['id' => 'expired', 'name' => _l('expired')],
					
				];
			}
		break;

		case 'voucher':
			if(wa_get_status_modules('omni_sales')){
				$checks = [
					['id' => 'channel', 'name' => _l('channel')],
					['id' => 'client_groups', 'name' => _l('client_groups')],
					['id' => 'customer', 'name' => _l('customer')],
					['id' => 'expired', 'name' => _l('expired')],
					
				];
			}
		break;

		case 'asset':
			if(wa_get_status_modules('fixed_equipment')){
				$checks = [
					['id' => 'model_id', 'name' => _l('model')],
					['id' => 'status', 'name' => _l('wa_status')],
					['id' => 'supplier', 'name' => _l('supplier')],
					['id' => 'location', 'name' => _l('location')],
					['id' => 'checkout_to', 'name' => _l('checkout_to')],

				];
			}
		break;

		case 'license':
			if(wa_get_status_modules('fixed_equipment')){
				$checks = [
					['id' => 'category_id', 'name' => _l('category')],
					['id' => 'manufacturer', 'name' => _l('wa_manufacturer')],
					['id' => 'supplier', 'name' => _l('supplier')],
					['id' => 'depreciation', 'name' => _l('depreciation')],
					
					
				];
			}
		break;

		case 'accessories':
			if(wa_get_status_modules('fixed_equipment')){
				$checks = [
					['id' => 'category_id', 'name' => _l('category')],
					['id' => 'manufacturer', 'name' => _l('wa_manufacturer')],
					['id' => 'supplier', 'name' => _l('supplier')],
					['id' => 'location', 'name' => _l('location')],
					
					
				];
			}
		break;

		case 'consumable':
			if(wa_get_status_modules('fixed_equipment')){
				$checks = [
					['id' => 'category_id', 'name' => _l('category')],
					['id' => 'manufacturer', 'name' => _l('wa_manufacturer')],
					['id' => 'location', 'name' => _l('location')],
					
					
				];
			}
		break;

		case 'component':
			if(wa_get_status_modules('fixed_equipment')){
				$checks = [
					['id' => 'category_id', 'name' => _l('category')],
					['id' => 'location', 'name' => _l('location')],
					
					
				];
			}
		break;


		case 'payslip':
			if(wa_get_status_modules('timesheets')){
				$checks = [
					['id' => 'timesheet_integration', 'name' => _l('wa_timesheet_integration')],

				];
			}

			if(wa_get_status_modules('hr_profile')){
				$checks = [
					['id' => 'hr_profile_integration', 'name' => _l('wa_hr_profile_integration')],

				];
			}

		break;

		case 'payslip_template':
			if(wa_get_status_modules('timesheets')){
				$checks = [
					['id' => 'timesheet_integration', 'name' => _l('wa_timesheet_integration')],

				];
			}

			if(wa_get_status_modules('hr_profile')){
				$checks = [
					['id' => 'hr_profile_integration', 'name' => _l('wa_hr_profile_integration')],

				];
			}

		break;

		case 'items':
			if(wa_get_status_modules('warehouse')){
				$checks = [
					['id' => 'warehouse_id', 'name' => _l('wa_warehouse')],
					['id' => 'commodity_type', 'name' => _l('wa_commodity_type')],
					['id' => 'commodity_group', 'name' => _l('wa_commodity_group')],
					['id' => 'unit', 'name' => _l('wa_unit')],
				];
			}
		break;

		case 'opening_stock':
			if(wa_get_status_modules('warehouse')){
				$checks = [
					['id' => 'number_of_items', 'name' => _l('number_of_items')],
				];
			}
		break;

		case 'inventory_receiving_voucher':
			if(wa_get_status_modules('warehouse')){
				$checks = [
					['id' => 'buyer_id', 'name' => _l('wa_buyer')],
					['id' => 'project', 'name' => _l('wa_project')],
					['id' => 'requester', 'name' => _l('wa_requester')],
					['id' => 'type', 'name' => _l('wa_type')],
					['id' => 'department', 'name' => _l('wa_department')],
					['id' => 'warehouse_id', 'name' => _l('wa_warehouse')],
					['id' => 'number_of_items', 'name' => _l('wa_number_of_items')],
					['id' => 'approval_status', 'name' => _l('wa_approval_status')],
				];
			}

			if(wa_get_status_modules('purchase')){
				$checks[] = ['id' => 'supplier_code', 'name' => _l('supplier')];
			}
		break;

		case 'inventory_delivery_voucher':
			if(wa_get_status_modules('warehouse')){
				$checks = [
					['id' => 'department', 'name' => _l('wa_department')],
					['id' => 'requester', 'name' => _l('wa_requester')],
					['id' => 'staff_id', 'name' => _l('wa_sales_person')],
					['id' => 'type', 'name' => _l('wa_type')],
					['id' => 'project', 'name' => _l('wa_project')],
					['id' => 'customer', 'name' => _l('wa_customer')],
					['id' => 'number_of_items', 'name' => _l('wa_number_of_items')],
					['id' => 'approval_status', 'name' => _l('wa_approval_status')],
				];
			}	
		break;

		case 'packing_list':
			if(wa_get_status_modules('warehouse')){
				$checks = [
					['id' => 'customer', 'name' => _l('wa_customer')],
					['id' => 'approval_status', 'name' => _l('wa_approval_status')],
					['id' => 'delivery_status', 'name' => _l('wa_delivery_status')],
					['id' => 'number_of_items', 'name' => _l('wa_number_of_items')],
				];
			}
		break;

		case 'inventory_delivery_note':
			if(wa_get_status_modules('warehouse')){
				$checks = [
					['id' => 'deliverer', 'name' => _l('wa_deliverer')],
					['id' => 'approval_status', 'name' => _l('wa_approval_status')],
					['id' => 'number_of_items', 'name' => _l('wa_number_of_items')],
				];
			}
		break;


		default:
        break;
	}

	return $checks;
}

/**
 * [wa_get_start_case_by_type description]
 * @param  [type] $type [description]
 * @return [type]       [description]
 */
function wa_get_actions_by_type($type = ''){
	$actions = [];


	$crm_type = 'crm';
	$hr_type = 'hr';
	$manufacturing_sale_type = 'manufacturing_sale';
	

	$actions[] = ['id' => 'create_task_default', 'name' => _l('wa_create_task'), 'group' => _l('wa_'.$crm_type)];
	$actions[] = ['id' => 'send_email_default', 'name' => _l('wa_send_email'), 'group' => ''];
	$actions[] = ['id' => 'create_proposal_default', 'name' => _l('wa_create_proposal'), 'group' => _l('wa_'.$crm_type)];
	$actions[] = ['id' => 'create_estimate_default', 'name' => _l('wa_create_estimate'), 'group' => _l('wa_'.$crm_type)];
	$actions[] = ['id' => 'create_invoice_default', 'name' => _l('wa_create_invoice'), 'group' => _l('wa_'.$crm_type)];


	if(wa_get_status_modules('manufacturing')){
		$actions[] = ['id' => 'create_manufacturing_order_default', 'name' => _l('wa_create_manufacturing_order'), 'group' => _l('wa_'.$manufacturing_sale_type)];
	}

	if(wa_get_status_modules('purchase')){
		$actions[] = ['id' => 'create_purchase_request_default', 'name' => _l('wa_create_purchase_request'), 'group' => _l('wa_'.$manufacturing_sale_type)];
		$actions[] = ['id' => 'create_purchase_order_default', 'name' => _l('wa_create_purchase_order'), 'group' => _l('wa_'.$manufacturing_sale_type)];
	}

	if(wa_get_status_modules('warehouse')){
		$actions[] = ['id' => 'create_inventory_receiving_voucher_default', 'name' => _l('create_inventory_receiving_voucher'), 'group' => _l('wa_'.$manufacturing_sale_type)];
		$actions[] = ['id' => 'create_inventory_delivery_voucher_default', 'name' => _l('create_inventory_delivery_voucher'), 'group' => _l('wa_'.$manufacturing_sale_type)];

	}

	if(wa_get_status_modules('omni_sales')){
		$actions[] = ['id' => 'create_manual_order_default', 'name' => _l('create_manual_order'), 'group' => _l('wa_'.$manufacturing_sale_type)];
	}

	if(wa_get_status_modules('hr_profile')){
		$actions[] = ['id' => 'assign_manager_default', 'name' => _l('assign_manager'), 'group' => _l('wa_'.$hr_type)];
		$actions[] = ['id' => 'create_hr_contract_default', 'name' => _l('create_hr_contract'), 'group' => _l('wa_'.$hr_type)];
		$actions[] = ['id' => 'create_training_default', 'name' => _l('create_training'), 'group' => _l('wa_'.$hr_type)];
		$actions[] = ['id' => 'create_onboarding_default', 'name' => _l('create_onboarding'), 'group' => _l('wa_'.$hr_type)];
	}



	switch ($type) {
        case 'tasks':
			
			  $actions[] = ['id' => 'assign_to', 'name' => _l('wa_assign_to'), 'group' => _l($type)];

			  $actions[] = ['id' => 'add_a_comment', 'name' => _l('wa_add_a_comment'), 'group' => _l($type)];
			  $actions[] = ['id' => 'delete_comment', 'name' => _l('wa_delete_comment'), 'group' => _l($type)];
			  $actions[] = ['id' => 'delete_task', 'name' => _l('wa_delete_task'), 'group' => _l($type)];
			  $actions[] = ['id' => 'update_task_field', 'name' => _l('wa_update_task_field'), 'group' => _l($type)];
			  $actions[] = ['id' => 'create_task', 'name' => _l('wa_create_task'), 'group' => _l($type)];
			  $actions[] = ['id' => 'change_priority', 'name' => _l('wa_change_priority'), 'group' => _l($type)];
			  $actions[] = ['id' => 'create_reminder_for_task', 'name' => _l('wa_create_reminder_for_task'), 'group' => _l($type)]; 
		break; 

		case 'projects':
			
			
			   $actions[] = ['id' => 'delete_project', 'name' => _l('wa_delete_project'), 'group' => _l($type)];
			   $actions[] = ['id' => 'update_project_fields', 'name' => _l('wa_update_project_fields'), 'group' => _l($type)];
			   $actions[] = ['id' => 'change_status', 'name' => _l('wa_change_status'), 'group' => _l($type)];
			   $actions[] = ['id' => 'create_task', 'name' => _l('wa_create_task'), 'group' => _l($type)];
			   $actions[] = ['id' => 'assign_to_customer', 'name' => _l('wa_assign_to_customer'), 'group' => _l($type)];

		break;

		case 'contracts':
			
			 
			  $actions[] = ['id' => 'delete_contract', 'name' => _l('wa_delete_contract'), 'group' => _l($type)];
			  $actions[] = ['id' => 'change_status_project', 'name' => _l('wa_change_status_project'), 'group' => _l($type)];
			  $actions[] = ['id' => 'create_task', 'name' => _l('wa_create_task'), 'group' => _l($type)];
			  $actions[] = ['id' => 'assign_to_customer', 'name' => _l('wa_assign_to_customer'), 'group' => _l($type)];
			  $actions[] = ['id' => 'add_note', 'name' => _l('wa_add_note'), 'group' => _l($type)];
			  $actions[] = ['id' => 'mark_as_signed', 'name' => _l('wa_mark_as_signed'), 'group' => _l($type)];
			  $actions[] = ['id' => 'unmark_as_signed', 'name' => _l('wa_unmark_as_signed'), 'group' => _l($type)];
			
		break;

		case 'leads':

	        $actions[] = ['id' => 'delete_lead','name' => _l('wa_delete_lead'), 'group' => _l($type)];
	        $actions[] = ['id' => 'update_lead_field','name' => _l('wa_update_lead_field'), 'group' => _l($type)];
	        $actions[] = ['id' => 'change_status_lead', 'name' => _l('wa_change_status_lead'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'change_source_lead', 'name' => _l('wa_change_source_lead'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'assign_to_staff', 'name' => _l('wa_assign_to_staff'), 'group' => _l($type)]; 
	       	$actions[] = ['id' => 'convert_to_customer', 'name' => _l('wa_convert_to_customer'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'create_task', 'name' => _l('wa_create_task'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'add_note', 'name' => _l('wa_add_note'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'create_reminder_for_lead', 'name' => _l('wa_create_reminder_for_lead'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'mark_as_lost', 'name' => _l('wa_mark_as_lost'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'mark_as_junk', 'name' => _l('wa_mark_as_junk'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'unmark_as_lost', 'name' => _l('wa_unmark_as_lost'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'unmark_as_junk', 'name' => _l('wa_unmark_as_junk'), 'group' => _l($type)]; 
	   break;

        case 'customers':
			

	        $actions[] = ['id' => 'delete_customer','name' => _l('wa_delete_customer'), 'group' => _l($type)];
	        $actions[] = ['id' => 'update_customer_field','name' => _l('wa_update_customer_field'), 'group' => _l($type)];
	        $actions[] = ['id' => 'change_group_customer', 'name' => _l('change_group_customer'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'add_note', 'name' => _l('wa_add_note'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'assign_to_staff', 'name' => _l('wa_assign_to_staff'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'create_task', 'name' => _l('wa_create_task'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'create_reminder_for_customer', 'name' => _l('wa_create_reminder_for_customer'), 'group' => _l($type)]; 
	     
        break;

        case 'proposals':
			
	        $actions[] = ['id' => 'update_proposal_field','name' => _l('wa_update_proposal_field'), 'group' => _l($type)];
	        $actions[] = ['id' => 'delete_proposal','name' => _l('wa_delete_proposal'), 'group' => _l($type)];
	        $actions[] = ['id' => 'change_status_proposal', 'name' => _l('change_status_proposal'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'change_project', 'name' => _l('wa_change_project'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'add_comment', 'name' => _l('wa_add_comment'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'create_reminder_for_proposal', 'name' => _l('wa_create_reminder_for_proposal'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'create_task', 'name' => _l('wa_create_task'), 'group' => _l($type)];
	        $actions[] = ['id' => 'create_note', 'name' => _l('wa_create_note'), 'group' => _l($type)];
	     
        break;


        case 'estimates':
			
	        $actions[] = ['id' => 'update_estimate_field','name' => _l('wa_update_estimate_field'), 'group' => _l($type)];
	        $actions[] = ['id' => 'delete_estimate','name' => _l('wa_delete_estimate'), 'group' => _l($type)];
	        $actions[] = ['id' => 'change_status_estimate', 'name' => _l('change_status_estimate'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'change_project', 'name' => _l('wa_change_project'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'convert_to_invoice', 'name' => _l('wa_convert_to_invoice'), 'group' => _l($type)];  
	        $actions[] = ['id' => 'create_reminder_for_estimate', 'name' => _l('wa_create_reminder_for_estimate'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'create_task', 'name' => _l('wa_create_task'), 'group' => _l($type)];
	        $actions[] = ['id' => 'create_note', 'name' => _l('wa_create_note'), 'group' => _l($type)];

        break;

        case 'invoices':
			
	        $actions[] = ['id' => 'update_invoice_field','name' => _l('wa_update_invoice_field'), 'group' => _l($type)];
	        $actions[] = ['id' => 'delete_invoice','name' => _l('wa_delete_invoice'), 'group' => _l($type)];
	        $actions[] = ['id' => 'change_status_invoice', 'name' => _l('change_status_invoice'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'change_project', 'name' => _l('wa_change_project'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'create_reminder_for_invoice', 'name' => _l('wa_create_reminder_for_invoice'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'create_task', 'name' => _l('wa_create_task'), 'group' => _l($type)];
	        $actions[] = ['id' => 'create_note', 'name' => _l('wa_create_note'), 'group' => _l($type)];
	        $actions[] = ['id' => 'create_payment', 'name' => _l('wa_create_payment'), 'group' => _l($type)];
	        
        break;

        case 'payment':
			
	        $actions[] = ['id' => 'update_payment_field','name' => _l('wa_update_payment_field'), 'group' => _l($type)];
	        $actions[] = ['id' => 'delete_payment','name' => _l('wa_delete_payment'), 'group' => _l($type)];

	      
        break;

        case 'credit_notes':
			
	        $actions[] = ['id' => 'update_credit_note_field','name' => _l('wa_update_credit_note_field'), 'group' => _l($type)];
	        $actions[] = ['id' => 'delete_credit_note','name' => _l('wa_delete_credit_note'), 'group' => _l($type)];
	        $actions[] = ['id' => 'change_status_credit_note', 'name' => _l('change_status_credit_note'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'create_reminder_for_credit_note', 'name' => _l('wa_create_reminder_for_credit_note'), 'group' => _l($type)]; 
	        $actions[] = ['id' => 'refund', 'name' => _l('wa_refund'), 'group' => _l($type)];
	        $actions[] = ['id' => 'void', 'name' => _l('wa_void'), 'group' => _l($type)];
	        $actions[] = ['id' => 'mark_as_open', 'name' => _l('wa_mark_as_open'), 'group' => _l($type)];
	        
        break;

        case 'purchase_order':
        	if(wa_get_status_modules('purchase')){
        		
		     	$actions[] = ['id' => 'update_purchase_order_field','name' => _l('wa_update_purchase_order_field'), 'group' => _l($type)];
		     	$actions[] = ['id' => 'delete_purchase_order','name' => _l('wa_delete_purchase_order'), 'group' => _l($type)];
		     	$actions[] = ['id' => 'change_approval_status', 'name' => _l('change_approval_status'), 'group' => _l($type)]; 
		     	$actions[] = ['id' => 'change_delivery_status', 'name' => _l('change_delivery_status'), 'group' => _l($type)]; 
		     	$actions[] = ['id' => 'create_reminder_for_purchase_order', 'name' => _l('wa_create_reminder_for_purchase_order'), 'group' => _l($type)]; 
		     	$actions[] = ['id' => 'create_task', 'name' => _l('wa_create_task'), 'group' => _l($type)];

		       
        	}
        break;

        case 'purchase_request':
        	if(wa_get_status_modules('purchase')){
        		
		        $actions[] = ['id' => 'update_purchase_request_field','name' => _l('wa_update_purchase_request_field'), 'group' => _l($type)];
		        $actions[] = ['id' => 'delete_purchase_request','name' => _l('wa_delete_purchase_request'), 'group' => _l($type)];
		        $actions[] = ['id' => 'change_approval_status', 'name' => _l('change_approval_status'), 'group' => _l($type)]; 

		        
        	}
        break;

        case 'purchase_quotation':
        	if(wa_get_status_modules('purchase')){
        		
		       	$actions[] = ['id' => 'update_purchase_quotation_field','name' => _l('wa_update_purchase_quotation_field'), 'group' => _l($type)];
		       	$actions[] = ['id' => 'delete_purchase_quotation','name' => _l('wa_delete_purchase_quotation'), 'group' => _l($type)];
		       	$actions[] = ['id' => 'change_approval_status', 'name' => _l('change_approval_status'), 'group' => _l($type)]; 

		        
        	}
        break;


        case 'purchase_invoice':
        	if(wa_get_status_modules('purchase')){
        		
		        $actions[] = ['id' => 'update_purchase_invoice_field','name' => _l('wa_update_purchase_invoice_field'), 'group' => _l($type)];
		        $actions[] = ['id' => 'delete_purchase_invoice','name' => _l('wa_delete_purchase_invoice'), 'group' => _l($type)];
		        $actions[] = ['id' => 'change_approval_status', 'name' => _l('change_approval_status'), 'group' => _l($type)];  
		        $actions[] = ['id' => 'create_reminder_for_purchase_invoice', 'name' => _l('wa_create_reminder_for_purchase_invoice'), 'group' => _l($type)]; 
		        $actions[] = ['id' => 'create_task', 'name' => _l('wa_create_task'), 'group' => _l($type)];

		        $actions[] = ['id' => 'add_note', 'name' => _l('wa_add_note'), 'group' => _l($type)];
		        
        	}
        break;

        case 'purchase_contract':
        	if(wa_get_status_modules('purchase')){
        		
		        $actions[] = ['id' => 'update_purchase_contract_field','name' => _l('wa_update_purchase_contract_field'), 'group' => _l($type)];
		        $actions[] = ['id' => 'delete_purchase_contract','name' => _l('wa_delete_purchase_contract'), 'group' => _l($type)];
		        $actions[] = ['id' => 'create_reminder_for_purchase_contract', 'name' => _l('wa_create_reminder_for_purchase_contract'), 'group' => _l($type)]; 
		        $actions[] = ['id' => 'create_task', 'name' => _l('wa_create_task'), 'group' => _l($type)];

		        $actions[] = ['id' => 'add_note', 'name' => _l('wa_add_note'), 'group' => _l($type)];
		        
        	}
        break;


        case 'vendor':
        	if(wa_get_status_modules('purchase')){
        		
		        $actions[] = ['id' => 'update_vendor_field','name' => _l('wa_update_vendor_field'), 'group' => _l($type)];
		        $actions[] = ['id' => 'delete_vendor','name' => _l('wa_delete_vendor'), 'group' => _l($type)];

		        $actions[] = ['id' => 'add_note', 'name' => _l('wa_add_note'), 'group' => _l($type)];
		        $actions[] = ['id' => 'assign_to_staff', 'name' => _l('wa_assign_to_staff'), 'group' => _l($type)];
		        
        	}
        break;

        case 'ticket':
        	if(wa_get_status_modules('customer_service')){
        		
		        $actions[] = ['id' => 'change_priority','name' => _l('wa_change_priority'), 'group' => _l($type)];
		        $actions[] = ['id' => 'change_status','name' => _l('wa_change_status'), 'group' => _l($type)];
		        $actions[] = ['id' => 'change_type', 'name' => _l('wa_change_type'), 'group' => _l($type)];
		        $actions[] = ['id' => 'assign_to_staff', 'name' => _l('wa_assign_to_staff'), 'group' => _l($type)];
		        
        	}
        break;


        case 'staff':
			if(wa_get_status_modules('hr_profile')){
				
				$actions[] = ['id' => 'delete', 'name' => _l('wa_delete_staff'), 'group' => _l($type)];
				$actions[] = ['id' => 'delete_contract', 'name' => _l('wa_delete_contract'), 'group' => _l($type)];	
				$actions[] = ['id' => 'change_status_contract', 'name' => _l('wa_change_status_contract'), 'group' => _l($type)];	
				$actions[] = ['id' => 'change_status_staff', 'name' => _l('wa_change_status_staff'), 'group' => _l($type)];
				$actions[] = ['id' => 'approve_depandant', 'name' => _l('wa_approve_depandant'), 'group' => _l($type)];
				$actions[] = ['id' => 'reject_depandant', 'name' => _l('wa_reject_depandant'), 'group' => _l($type)];
				$actions[] = ['id' => 'delete_depandant', 'name' => _l('wa_delete_depandant'), 'group' => _l($type)];
				$actions[] = ['id' => 'approve_layoff_checklist', 'name' => _l('wa_approve_layoff_checklist'), 'group' => _l($type)];
				$actions[] = ['id' => 'delete_layoff_checklist', 'name' => _l('wa_delete_layoff_checklist'), 'group' => _l($type)];
				$actions[] = ['id' => 'unactive_staff', 'name' => _l('wa_unactive_staff'), 'group' => _l($type)];
				
			}
		break;

		case 'expense':

			
		        $actions[] = ['id' => 'copy_expense','name' => _l('wa_copy_expense'), 'group' => _l($type)];
		        $actions[] = ['id' => 'create_task','name' => _l('wa_create_task'), 'group' => _l($type)];
		        $actions[] = ['id' => 'create_reminder_for_expense', 'name' => _l('wa_create_reminder_for_expense'), 'group' => _l($type)];
		        $actions[] = ['id' => 'update_expense_fields', 'name' => _l('wa_update_expense_fields'), 'group' => _l($type)];
		        $actions[] = ['id' => 'delete_expense', 'name' => _l('wa_delete_expense'), 'group' => _l($type)];
		        $actions[] = ['id' => 'convert_to_invoice', 'name' => _l('wa_convert_to_invoice'), 'group' => _l($type)];
		    
        	
        break;


        case 'subscription':
			
			$actions[] = ['id' => 'update_subscription_field', 'name' => _l('wa_update_subscription_field'), 'group' => _l($type)];
			$actions[] = ['id' => 'delete', 'name' => _l('wa_delete'), 'group' => _l($type)];
			$actions[] = ['id' => 'create_invoice', 'name' => _l('wa_create_invoice'), 'group' => _l($type)];
			$actions[] = ['id' => 'send_to_customer', 'name' => _l('wa_send_to_customer'), 'group' => _l($type)];
			$actions[] = ['id' => 'change_status', 'name' => _l('wa_change_status'), 'group' => _l($type)];
			
			
		break;


		case 'recruitment_plan':
			if(wa_get_status_modules('recruitment')){
				
					
				$actions[] = ['id' => 'delete', 'name' => _l('wa_delete'), 'group' => _l($type)];
				$actions[] = ['id' => 'approve_plan', 'name' => _l('wa_approve_plan'), 'group' => _l($type)];
				
			}
		break;

		case 'recruitment_campaign':
			if(wa_get_status_modules('recruitment')){
					
				$actions[] = ['id' => 'change_campaign_status', 'name' => _l('wa_change_campaign_status'), 'group' => _l($type)];
				$actions[] = ['id' => 'delete', 'name' => _l('wa_delete'), 'group' => _l($type)];
				
			}
		break;


		case 'recruitment_form':
			if(wa_get_status_modules('recruitment')){
				
				$actions[] = ['id' => 'duplicate_form', 'name' => _l('wa_duplicate_form'), 'group' => _l($type)];
					
			}
		break;


		case 'candidate':
			if(wa_get_status_modules('recruitment')){
				
				$actions[] = ['id' => 'delete', 'name' => _l('wa_delete'), 'group' => _l($type)];

				$actions[] = ['id' => 'change_candidate_status', 'name' => _l('wa_change_candidate_status'), 'group' => _l($type)];
			}

			if(wa_get_status_modules('hr_profile')){
				$actions[] = 	['id' => 'transfer_hr_records', 'name' => _l('watransfer_hr_records'), 'group' => _l($type)];
			}
		break;


		case 'interview_schedule':
			if(wa_get_status_modules('recruitment')){
				$actions[] = ['id' => 'delete', 'name' => _l('wa_delete'), 'group' => _l($type)];

			}
		break;

		case 'leave_request':
			if(wa_get_status_modules('timesheets')){
				$actions[] = ['id' => 'change_approval_status', 'name' => _l('wa_change_approval_status'), 'group' => _l($type)];
				$actions[] = ['id' => 'delete_leave_request', 'name' => _l('wa_delete_leave_request'), 'group' => _l($type)];
			}
		break;

		case 'additional_work_hours':

			if(wa_get_status_modules('timesheets')){
				$actions[] = ['id' => 'delete', 'name' => _l('wa_delete'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_approval_status', 'name' => _l('wa_change_approval_status'), 'group' => _l($type)];
			}

		break;

		case 'vehicle':

			if(wa_get_status_modules('fleet')){
				$actions[] = ['id' => 'delete_vehicle', 'name' => _l('wa_delete_vehicle'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_status', 'name' => _l('wa_change_status'), 'group' => _l($type)];
				$actions[] = ['id' => 'create_assignment', 'name' => _l('wa_create_assignment'), 'group' => _l($type)];
			}

		break;


		case 'workperformance':
			if(wa_get_status_modules('fleet')){
				
				$actions[] = ['id' => 'delete_workperformance', 'name' => _l('delete_workperformance'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_status', 'name' => _l('wa_change_status'), 'group' => _l($type)];
				
			}
		break;

		case 'event':
			if(wa_get_status_modules('fleet')){
				
				$actions[] = ['id' => 'delete_event', 'name' => _l('delete_event'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_type', 'name' => _l('wa_change_type'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_driver', 'name' => _l('wa_change_driver'), 'group' => _l($type)];
				
			}
		break;

		case 'work_order':
			if(wa_get_status_modules('fleet')){
				
				$actions[] = ['id' => 'delete_work_order', 'name' => _l('delete_work_order'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_status', 'name' => _l('wa_change_status'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_vehiche', 'name' => _l('wa_change_vehiche'), 'group' => _l($type)];
				
			}
		break;

		case 'booking':
			if(wa_get_status_modules('fleet')){
				
				$actions[] = ['id' => 'delete_booking', 'name' => _l('delete_booking'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_status', 'name' => _l('wa_change_status'), 'group' => _l($type)];
				$actions[] = ['id' => 'create_invoice', 'name' => _l('wa_create_invoice'), 'group' => _l($type)];
				
			}
		break;

		case 'fuel':
			if(wa_get_status_modules('fleet')){
				
				$actions[] = ['id' => 'delete_fuel', 'name' => _l('delete_fuel'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_type', 'name' => _l('wa_change_type'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_vehiche', 'name' => _l('wa_change_vehiche'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_vendor', 'name' => _l('wa_change_vendor'), 'group' => _l($type)];
				
			}
		break;

		case 'work_center':
			if(wa_get_status_modules('manufacturing')){
			
				$actions[] = ['id' => 'delete_work_center', 'name' => _l('delete_work_center'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_working_hours', 'name' => _l('wa_change_working_hours'), 'group' => _l($type)];
				$actions[] = ['id' => 'update_cost', 'name' => _l('wa_update_cost'), 'group' => _l($type)];
			
			}
		break;

		case 'routing':
			if(wa_get_status_modules('manufacturing')){
				
				$actions[] = ['id' => 'delete_routing', 'name' => _l('delete_routing'), 'group' => _l($type)];
				$actions[] = ['id' => 'update_name', 'name' => _l('wa_update_name'), 'group' => _l($type)];
				$actions[] = ['id' => 'update_note', 'name' => _l('wa_update_note'), 'group' => _l($type)];
				
			}
		break;

		case 'operation':
			if(wa_get_status_modules('manufacturing')){
				
				$actions[] = ['id' => 'delete_operation', 'name' => _l('delete_operation'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_work_center', 'name' => _l('wa_change_work_center'), 'group' => _l($type)];
				$actions[] = ['id' => 'update_description', 'name' => _l('wa_update_description'), 'group' => _l($type)];
				
			}
		break;

		case 'bill_of_material':
			if(wa_get_status_modules('manufacturing')){
				
				$actions[] = ['id' => 'delete_bom', 'name' => _l('delete_bom'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_bom_type', 'name' => _l('wa_change_bom_type'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_ready_to_produce', 'name' => _l('ready_to_produce'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_consumption', 'name' => _l('consumption'), 'group' => _l($type)];
				
			}
		break;

		case 'bom_component':
			if(wa_get_status_modules('manufacturing')){
				
				$actions[] = ['id' => 'delete_bom_component', 'name' => _l('delete_bom_component'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_unit_of_measure', 'name' => _l('wa_change_unit_of_measure'), 'group' => _l($type)];

				
			}
		break;

		case 'manufacturing_order':
			if(wa_get_status_modules('manufacturing')){
				
				$actions[] = ['id' => 'delete_manufacturing_order', 'name' => _l('delete_manufacturing_order'), 'group' => _l($type)];
				$actions[] = ['id' => 'mark_as_done', 'name' => _l('wa_mark_as_done'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_status', 'name' => _l('wa_change_status'), 'group' => _l($type)];
				
			}

		break;

		case 'omni_sales_order':
			if(wa_get_status_modules('omni_sales')){
				
				$actions[] = ['id' => 'delete_order', 'name' => _l('wa_delete_order'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_status', 'name' => _l('wa_change_status'), 'group' => _l($type)];
				$actions[] = ['id' => 'create_invoice', 'name' => _l('wa_create_invoice'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_sale_agent', 'name' => _l('wa_change_sale_agent'), 'group' => _l($type)];
				
			}

			if(wa_get_status_modules('warehouse')){
				$actions[] = ['id' => 'add_shipment_activity_log', 'name' => _l('wa_add_shipment_activity_log'), 'group' => _l($type)];
				$actions[] = ['id' => 'create_export_stocck', 'name' => _l('wa_create_export_stocck'), 'group' => _l($type)];
			}
		break;


		case 'omni_sales_refund':
			if(wa_get_status_modules('omni_sales')){
				
				$actions[] = ['id' => 'delete_refund', 'name' => _l('wa_delete_refund'), 'group' => _l($type)];
				$actions[] = ['id' => 'update_amount', 'name' => _l('wa_update_amount'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_payment_mode', 'name' => _l('wa_change_payment_mode'), 'group' => _l($type)];

				
			}
		break;

		case 'trade_discount':
			if(wa_get_status_modules('omni_sales')){
				
				$actions[] = ['id' => 'delete_trade_discount', 'name' => _l('delete_trade_discount'), 'group' => _l($type)];
				$actions[] = ['id' => 'update_discount', 'name' => _l('update_discount'), 'group' => _l($type)];
				$actions[] = ['id' => 'update_formal', 'name' => _l('update_formal'), 'group' => _l($type)];
				$actions[] = ['id' => 'add_client_to_trade_discount', 'name' => _l('add_client_to_trade_discount'), 'group' => _l($type)];
				$actions[] = ['id' => 'update_end_date', 'name' => _l('update_end_date'), 'group' => _l($type)];
				
			}
		break;


		case 'asset':
			if(wa_get_status_modules('fixed_equipment')){
				
				$actions[] = ['id' => 'delete_asset', 'name' => _l('delete_asset'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_status', 'name' => _l('wa_change_status'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_location', 'name' => _l('wa_change_location'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_model', 'name' => _l('wa_change_model'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_supplier', 'name' => _l('wa_change_supplier'), 'group' => _l($type)];

				
			}
		break;

		case 'license':
			if(wa_get_status_modules('fixed_equipment')){
				
				$actions[] = ['id' => 'delete_license', 'name' => _l('delete_license'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_category', 'name' => _l('wa_change_category'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_manufacturer', 'name' => _l('wa_change_manufacturer'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_depreciation', 'name' => _l('wa_change_depreciation'), 'group' => _l($type)];
				
			}
		break;

		case 'accessories':
			if(wa_get_status_modules('fixed_equipment')){
				
				$actions[] = ['id' => 'delete_accessory', 'name' => _l('delete_accessory'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_category', 'name' => _l('wa_change_category'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_manufacturer', 'name' => _l('wa_change_manufacturer'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_location', 'name' => _l('wa_change_location'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_supplier', 'name' => _l('wa_change_supplier'), 'group' => _l($type)];
				
			}
		break;

		case 'consumable':
			if(wa_get_status_modules('fixed_equipment')){
				
				$actions[] = ['id' => 'delete_consumable', 'name' => _l('delete_consumable'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_category', 'name' => _l('wa_change_category'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_manufacturer', 'name' => _l('wa_change_manufacturer'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_location', 'name' => _l('wa_change_location'), 'group' => _l($type)];
				
			}
		break;

		case 'component':
			if(wa_get_status_modules('fixed_equipment')){
				
				$actions[] = ['id' => 'delete_component', 'name' => _l('delete_component'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_category', 'name' => _l('wa_change_category'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_location', 'name' => _l('wa_change_location'), 'group' => _l($type)];
				
			}
		break;

		case 'payslip':
			if(wa_get_status_modules('hr_payroll')){
				
				$actions[] = ['id' => 'closing_payroll', 'name' => _l('closing_payroll'), 'group' => _l($type)];
				$actions[] = ['id' => 'delete_payslip', 'name' => _l('wa_delete_payslip'), 'group' => _l($type)];
				$actions[] = ['id' => 'payslipng_opening', 'name' => _l('payslipng_opening'), 'group' => _l($type)];
			}
		break;

		case 'payslip_template':
			if(wa_get_status_modules('hr_payroll')){
				
				
				$actions[] = ['id' => 'delete_payslip_template', 'name' => _l('wa_delete_payslip_template'), 'group' => _l($type)];
				$actions[] = ['id' => 'payslip_template_apply_to_staff', 'name' => _l('wa_payslip_template_apply_to_staff'), 'group' => _l($type)];
				$actions[] = ['id' => 'payslip_template_except_for_staff', 'name' => _l('wa_payslip_template_except_for_staff'), 'group' => _l($type)];
				
				
			}
		break;
			

		case 'items':
			if(wa_get_status_modules('warehouse')){
				
				$actions[] = ['id' => 'delete_item', 'name' => _l('delete_item'), 'group' => _l($type)];
				$actions[] = ['id' => 'create_inventory_receiving_voucher', 'name' => _l('create_inventory_receiving_voucher'), 'group' => _l($type)];
				$actions[] = ['id' => 'create_inventory_delivery_voucher', 'name' => _l('create_inventory_delivery_voucher'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_warehouse', 'name' => _l('change_warehouse'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_commodity_type', 'name' => _l('change_commodity_type'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_unit', 'name' => _l('change_unit'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_commodity_group', 'name' => _l('change_commodity_group'), 'group' => _l($type)];
				
			}
		break;

		case 'opening_stock':
			if(wa_get_status_modules('warehouse')){
				
				$actions[] = ['id' => 'create_inventory_receiving_voucher', 'name' => _l('create_inventory_receiving_voucher'), 'group' => _l($type)];
				$actions[] = ['id' => 'create_inventory_delivery_voucher', 'name' => _l('create_inventory_delivery_voucher'), 'group' => _l($type)];
				
			}
		break;

		case 'inventory_receiving_voucher':
			if(wa_get_status_modules('warehouse')){
				

				$actions[] = ['id' => 'change_project', 'name' => _l('wa_change_project'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_department', 'name' => _l('change_department'), 'group' => _l($type)];
				$actions[] = ['id' => 'update_note', 'name' => _l('wa_update_note'), 'group' => _l($type)];
				$actions[] = ['id' => 'create_task', 'name' => _l('wa_create_task'), 'group' => _l($type)];
				
			}
		break;

		case 'inventory_delivery_voucher':
			if(wa_get_status_modules('warehouse')){
				

				$actions[] = ['id' => 'change_project', 'name' => _l('wa_change_project'), 'group' => _l($type)];
				$actions[] = ['id' => 'change_department', 'name' => _l('change_department'), 'group' => _l($type)];
				$actions[] = ['id' => 'update_note', 'name' => _l('wa_update_note'), 'group' => _l($type)];
				$actions[] = ['id' => 'create_task', 'name' => _l('wa_create_task'), 'group' => _l($type)];
				
			}
		break;

		case 'packing_list':
			if(wa_get_status_modules('warehouse')){
				
				$actions[] = ['id' => 'change_delivery_status', 'name' => _l('change_delivery_status'), 'group' => _l($type)];
				$actions[] = ['id' => 'add_shipping_log', 'name' => _l('wa_add_shipping_log'), 'group' => _l($type)];
				
			}
		break;

		case 'inventory_delivery_note':
			if(wa_get_status_modules('warehouse')){
				
				$actions[] = ['id' => 'change_deliverer', 'name' => _l('change_deliverer'), 'group' => _l($type)];
				$actions[] = ['id' => 'approve_inventory_delivery_note', 'name' => _l('approve_inventory_delivery_note'), 'group' => _l($type)];
			
			}
		break;



		default:
        break;
	}

	return $actions;
}

/**
 * [wa_get_category_name_by_id description]
 * @return [type] [description]
 */
function wa_get_category_name_by_id($id){
	$CI             = &get_instance();

	$CI->db->where('id', $id);
	$cate = $CI->db->get(db_prefix().'wa_categories')->row();
	if(isset($cate->name)){
		return $cate->name;
	}
	return '';

}

/**
 * wa_data_type_list
 * @return [type] [description]
 */
function wa_data_type_list(){
	$types = [
            ['id' => 'tasks', 'name' => _l('wa_tasks')],
            ['id' => 'projects', 'name' => _l('wa_projects')],
            ['id' => 'contracts', 'name' => _l('wa_contracts')],
            ['id' => 'leads', 'name' => _l('wa_leads')],
            ['id' => 'customers', 'name' => _l('wa_customers')],
            ['id' => 'proposals', 'name' => _l('wa_proposals')],
            ['id' => 'estimates', 'name' => _l('wa_estimates')],
            ['id' => 'invoices', 'name' => _l('wa_invoices')],
            ['id' => 'payment', 'name' => _l('wa_payments')],
            ['id' => 'credit_notes', 'name' => _l('wa_credit_notes')],
            ['id' => 'expense', 'name' => _l('wa_expense')],
            
        ];

    if(wa_get_status_modules('purchase')){
    	$types[] = ['id' => 'purchase_order', 'name' => _l('wa_purchase_order')];
    	$types[] = ['id' => 'purchase_request', 'name' => _l('wa_purchase_request')];
    	$types[] = ['id' => 'purchase_quotation', 'name' => _l('wa_purchase_quotation')];
    	$types[] = ['id' => 'purchase_invoice', 'name' => _l('wa_purchase_invoice')];
    	$types[] = ['id' => 'vendor', 'name' => _l('wa_vendor')];
    	$types[] = ['id' => 'purchase_contract', 'name' => _l('wa_purchase_contract')];
  
    }

    if(wa_get_status_modules('customer_service')){
    	$types[] = ['id' => 'ticket', 'name' => _l('wa_ticket')];
    }

    if(wa_get_status_modules('hr_profile')){
    	$types[] = ['id' => 'staff', 'name' => _l('wa_staff')];
    }

    if(wa_get_status_modules('recruitment')){
    	$types[] = ['id' => 'recruitment_plan', 'name' => _l('wa_recruitment_plan')];
    	$types[] = ['id' => 'recruitment_campaign', 'name' => _l('wa_recruitment_campaign')];
    	$types[] = ['id' => 'recruitment_form', 'name' => _l('wa_recruitment_form')];
    	$types[] = ['id' => 'candidate', 'name' => _l('wa_candidate')];
    	$types[] = ['id' => 'interview_schedule', 'name' => _l('wa_interview_schedule')];
    }


    if(wa_get_status_modules('timesheets')){
    	$types[] = ['id' => 'leave_request', 'name' => _l('wa_leave_request')];
    	$types[] = ['id' => 'additional_work_hours', 'name' => _l('wa_additional_work_hours')];

    }


    if(wa_get_status_modules('fleet')){
    	$types[] = ['id' => 'vehicle', 'name' => _l('wa_vehicle')];
    	$types[] = ['id' => 'workperformance', 'name' => _l('wa_workperformance')];
    	$types[] = ['id' => 'event', 'name' => _l('wa_event')];
    	$types[] = ['id' => 'work_order', 'name' => _l('wa_work_order')];
    	$types[] = ['id' => 'booking', 'name' => _l('wa_booking')];
    	$types[] = ['id' => 'fuel', 'name' => _l('wa_fuel')];
    }


    if(wa_get_status_modules('manufacturing')){
    	$types[] = ['id' => 'work_center', 'name' => _l('wa_work_center')];
    	$types[] = ['id' => 'routing', 'name' => _l('wa_routing')];
    	$types[] = ['id' => 'operation', 'name' => _l('wa_operation')];
    	$types[] = ['id' => 'bill_of_material', 'name' => _l('wa_bill_of_material')];
    	$types[] = ['id' => 'bom_component', 'name' => _l('wa_bom_component')];
    	$types[] = ['id' => 'manufacturing_order', 'name' => _l('wa_manufacturing_order')];
    }


    if(wa_get_status_modules('omni_sales')){	
    	$types[] = ['id' => 'omni_sales_order', 'name' => _l('wa_omni_sales_order')];
    	$types[] = ['id' => 'omni_sales_refund', 'name' => _l('wa_omni_sales_refund')];
    	$types[] = ['id' => 'trade_discount', 'name' => _l('wa_trade_discount')];
    }

    if(wa_get_status_modules('fixed_equipment')){
    	$types[] = ['id' => 'asset', 'name' => _l('asset')];
    	$types[] = ['id' => 'license', 'name' => _l('license')];
    	$types[] = ['id' => 'accessories', 'name' => _l('accessories')];
    	$types[] = ['id' => 'consumable', 'name' => _l('consumable')];
    	$types[] = ['id' => 'component', 'name' => _l('component')];
    }

    if(wa_get_status_modules('hr_payroll')){
    	$types[] = ['id' => 'payslip', 'name' => _l('payslip')];
    	$types[] = ['id' => 'payslip_template', 'name' => _l('payslip_template')];
    }

    if(wa_get_status_modules('warehouse')){
    	$types[] = ['id' => 'items', 'name' => _l('items')];
    	$types[] = ['id' => 'inventory_receiving_voucher', 'name' => _l('inventory_receiving_voucher')];
    	$types[] = ['id' => 'inventory_delivery_voucher', 'name' => _l('inventory_delivery_voucher')];
    	$types[] = ['id' => 'packing_list', 'name' => _l('packing_list')];
    
    }

    return $types;
}


if (!function_exists('add_wa_email_templates')) {
    /**
     * Init appointly email templates and assign languages
     * @return void
     */
    function add_wa_email_templates() {
        $CI = &get_instance();

        $data['wa_templates'] = $CI->emails_model->get(['type' => 'workflow_automation', 'language' => 'english']);

        $CI->load->view('workflow_automation/email_templates', $data);
    }
}

/**
 * list workflow_automation permisstion
 * @return [type] 
 */
function list_workflow_automation_permisstion()
{
    $hr_profile_permissions=[];
    $hr_profile_permissions[]='workflow_automation';
    $hr_profile_permissions[]='workflow_automation_setting';
    $hr_profile_permissions[]='workflow_automation_history';


    return $hr_profile_permissions;
}


/**
 * workflow_automation get staff id hr permissions
 * @return [type] 
 */
function workflow_automation_get_staff_id_permissions()
{
    $CI = & get_instance();
    $array_staff_id = [];
    $index=0;

    $str_permissions ='';
    foreach (list_workflow_automation_permisstion() as $per_key =>  $per_value) {
        if(strlen($str_permissions) > 0){
            $str_permissions .= ",'".$per_value."'";
        }else{
            $str_permissions .= "'".$per_value."'";
        }

    }


    $sql_where = "SELECT distinct staff_id FROM ".db_prefix()."staff_permissions
    where feature IN (".$str_permissions.")
    ";
    
    $staffs = $CI->db->query($sql_where)->result_array();

    if(count($staffs)>0){
        foreach ($staffs as $key => $value) {
            $array_staff_id[$index] = $value['staff_id'];
            $index++;
        }
    }
    return $array_staff_id;
}
/**
 * workflow_automation get staff id dont permissions
 * @return [type] 
 */
function workflow_automation_get_staff_id_dont_permissions()
{
    $CI = & get_instance();

    $CI->db->where('admin != ', 1);

    if(count(workflow_automation_get_staff_id_permissions()) > 0){
        $CI->db->where_not_in('staffid', workflow_automation_get_staff_id_permissions());
    }
    return $CI->db->get(db_prefix().'staff')->result_array();
    
}

/**
 * [wa_get_contract_subject description]
 * @return [type] [description]
 */
function wa_get_contract_subject($id){

	$CI = & get_instance();

	$CI->db->select('subject');
	$CI->db->where('id', $id);
	$contract = $CI->db->get(db_prefix().'contracts')->row();
	if(isset( $contract->subject )){
		return  $contract->subject;
	}
	return '';

}

/**
 * [wa_get_lead_name description]
 * @return [type] [description]
 */
function wa_get_lead_name($id){
	$CI = & get_instance();

	$CI->db->select('name');
	$CI->db->where('id', $id);
	$lead = $CI->db->get(db_prefix().'leads')->row();
	if(isset( $lead->name )){
		return  $lead->name;
	}
	return '';
}

/**
 * [wa_get_workflow_name description]
 * @return [type] [description]
 */
function wa_get_workflow_name($id){
	$CI = & get_instance();

	$CI->db->select('name');
	$CI->db->where('id', $id);
	$workflow = $CI->db->get(db_prefix().'wa_workflows')->row();
	if(isset( $workflow->name )){
		return  $workflow->name;
	}
	return '';
}

/**
 * [wa_get_related_to_info description]
 * @param  [type] $rel_type [description]
 * @param  [type] $rel_id   [description]
 * @return [type]           [description]
 */
function wa_get_related_to_info($rel_type, $rel_id){

	$str = '';
	$CI = & get_instance();
	switch ($rel_type) {
               
        case 'tasks':
        $task_str = get_task_subject_by_id($rel_id);
	        if($task_str != ''){
	        	$str = '<a href="' . admin_url('tasks/view/' . $rel_id) . '" onclick="init_task_modal(' . $rel_id . '); return false;">' . $task_str . '</a>';
	        }
        break;

        case 'projects':
        	$project_str = get_project_name_by_id($rel_id);
        	if($project_str != ''){
        		$str = '<a href="' . admin_url('projects/view/' . $rel_id) . '" target="_blank">' . $project_str . '</a>';
        	}

        break;

        case 'contracts':
        	$contract_str = wa_get_contract_subject($rel_id);
        	if($contract_str != ''){
       	 		$str = '<a href="' . admin_url('contracts/contract/' . $rel_id) . '" target="_blank">' . wa_get_contract_subject($rel_id) . '</a>';
        	}
        break;

        case 'leads':
        	$str =  wa_get_lead_name($rel_id);
        break;

        case 'customers':
        	$customers_str = get_company_name($rel_id);
        	if($customers_str != ''){
        		$str =  '<a href="' . admin_url('clients/client/' . $rel_id) . '" target="_blank">' . $customers_str .'</a>';
        	}
        break;

        case 'proposals':
        	$proposals_str = format_proposal_number($rel_id);
        	if($proposals_str != ''){
        		$str =  '<a href="' . admin_url('proposals#' . $rel_id) . '" target="_blank">' .$proposals_str .'</a>';
        	}
        break;

        case 'estimates':
        	$estimates_str = format_estimate_number($rel_id);
        	if($estimates_str != ''){
        		$str =  '<a href="' . admin_url('estimates#' . $rel_id) . '" target="_blank">' . $estimates_str.'</a>';
        	}
        break;

        case 'invoices':
        	$invoice_str = format_invoice_number($rel_id);
        	if($invoice_str != ''){
        		$str =  '<a href="' . admin_url('invoices#' . $rel_id) . '" target="_blank">' . $invoice_str.'</a>';
        	}
        break;

        case 'credit_notes':

        	$credit_note_str = format_credit_note_number($rel_id);
        	if($credit_note_str != ''){
        		$str =  '<a href="' . admin_url('credit_notes#' . $rel_id) . '" target="_blank">' . $credit_note_str.'</a>';
        	}
		break;

		case 'purchase_order':
			if(wa_get_status_modules('purchase')){
				$purchase_order_str = get_pur_order_subject($rel_id);
	        	if($purchase_order_str != ''){
	        		$str =  '<a href="' . admin_url('purchase/purchase_order#' . $rel_id) . '" target="_blank">' . $purchase_order_str.'</a>';
	        	}
	        }
		break;

		case 'purchase_request':
			if(wa_get_status_modules('purchase')){
				$purchase_request_str = pur_get_pur_request_code($rel_id);
	        	if($purchase_request_str != _l('no_pr_found')){
	        		$str =  '<a href="' . admin_url('purchase/view_pur_request/' . $rel_id) . '" target="_blank">' . $purchase_request_str.'</a>';
	        	}
	        }
		break;

		case 'purchase_quotation':
			if(wa_get_status_modules('purchase')){
				$purchase_quotation_str = format_pur_estimate_number($rel_id);
	        	if($purchase_quotation_str != _l('pur_estimate_not_found')){
	        		$str =  '<a href="' . admin_url('purchase/quotations#' . $rel_id) . '" target="_blank">' . $purchase_quotation_str.'</a>';
	        	}
	        }
		break;

		case 'purchase_invoice':
			if(wa_get_status_modules('purchase')){
				$purchase_invoice_str = get_pur_invoice_number($rel_id);
	        	if($purchase_invoice_str != ''){
	        		$str =  '<a href="' . admin_url('purchase/purchase_invoice/' . $rel_id) . '" target="_blank">' . $purchase_invoice_str.'</a>';
	        	}
	        }
		break;

		case 'purchase_contract':
			if(wa_get_status_modules('purchase')){
				$purchase_contract_str = get_pur_contract_number($rel_id);
	        	if($purchase_contract_str != ''){
	        		$str =  '<a href="' . admin_url('purchase/contract/' . $rel_id) . '" target="_blank">' . $purchase_contract_str.'</a>';
	        	}
	        }
		break;

		case 'vendor':
			if(wa_get_status_modules('purchase')){
				$purchase_vendor_str = get_vendor_company_name($rel_id);
	        	if($purchase_vendor_str != ''){
	        		$str =  '<a href="' . admin_url('purchase/vendor/' . $rel_id) . '" target="_blank">' . $purchase_vendor_str.'</a>';
	        	}
	        }
		break;

		case 'ticket':
			if(wa_get_status_modules('customer_service')){
				$ticket_str = cs_get_ticket_code($rel_id);
	        	if($ticket_str != ''){
	        		$str =  '<a href="' . admin_url('customer_service/ticket_detail/' . $rel_id) . '" target="_blank">' . $ticket_str.'</a>';
	        	}
	        }
		break;

		case 'staff':
			if(wa_get_status_modules('hr_profile')){
				$staff_full_name = get_staff_full_name($rel_id);
	        	if($staff_full_name != ''){
	        		$str =  '<a href="' . admin_url('hr_profile/member/' . $rel_id) . '" target="_blank">' . $staff_full_name.'</a>';
	        	}
	        }
		break;

		case 'expense':

			$CI->db->select('expense_name');
			$CI->db->where('id', $rel_id);
			$expense = $CI->db->get(db_prefix().'expenses')->row();

			$expense_name = '';
			if(isset($expense->expense_name)){
				$expense_name = $expense->expense_name;
			}

        	if($expense_name != ''){
        		$str =  '<a href="' . admin_url('expenses#' . $rel_id) . '" target="_blank">' . $expense_name.'</a>';
        	}
	        
		break;

		case 'recruitment_plan':
			if(wa_get_status_modules('recruitment')){
				$CI->db->select('proposal_name');
				$CI->db->where('id', $rel_id);
				$plan = $CI->db->get(db_prefix().'rec_proposal')->row();
				$plan_name = '';
				if(isset($plan->plan_name)){
					$plan_name = $plan->plan_name;
				}

	        	if($plan_name != ''){
	        		$str =  '<a href="' . admin_url('recruitment/recruitment_proposal#' . $rel_id) . '" target="_blank">' . $plan_name.'</a>';
	        	}
			}	
		break;

		case 'recruitment_campaign':
			if(wa_get_status_modules('recruitment')){
				$recruitment_campaign_name = get_rec_campaign_name($rel_id);

	        	if($recruitment_campaign_name != ''){
	        		$str =  '<a href="' . admin_url('recruitment/recruitment_campaign#' . $rel_id) . '" target="_blank">' . $recruitment_campaign_name.'</a>';
	        	}
			}
		break;

		case 'recruitment_form':
			if(wa_get_status_modules('recruitment')){
				$CI->db->select('r_form_name');
				$CI->db->where('id', $rel_id);
				$form = $CI->db->get(db_prefix().'rec_campaign_form_web')->row();

				$form_name = '';

				if(isset($form->r_form_name)){
					$form_name = $form->r_form_name;
				}

	        	if($form_name != ''){
	        		$str =  '<a href="' . admin_url('recruitment/recruitment_channel#' . $rel_id) . '" target="_blank">' . $form_name.'</a>';
	        	}
			}
		break;

		case 'candidate':
			if(wa_get_status_modules('recruitment')){
				$candidate_name = get_candidate_name($rel_id);

	        	if($candidate_name != ''){
	        		$str =  '<a href="' . admin_url('recruitment/candidate/' . $rel_id) . '" target="_blank">' . $candidate_name.'</a>';
	        	}
			}
		break;

		case 'interview_schedule':
			if(wa_get_status_modules('recruitment')){
				$interview_schedule_name = get_rec_interview_name($rel_id);

	        	if($interview_schedule_name != ''){
	        		$str =  '<a href="' . admin_url('recruitment/interview_schedule#' . $rel_id) . '" target="_blank">' . $interview_schedule_name.'</a>';
	        	}
			}
		break;

		case 'leave_request':
			if(wa_get_status_modules('timesheets')){
				$CI->db->select('subject');
				$CI->db->where('id', $rel_id);
				$leave_request = $CI->db->get(db_prefix().'timesheets_requisition_leave')->row();

				$leave_request_name = '';

				if(isset($leave_request->subject)){
					$leave_request_name = $leave_request->subject;
				}

	        	if($leave_request_name != ''){
	        		$str =  '<a href="' . admin_url('timesheets/requisition_detail/' . $rel_id) . '" target="_blank">' . $leave_request_name.'</a>';
	        	}
			}
		break;

		case 'additional_work_hours':
			$str = '';
		break;

		case 'vehicle':
			
			if(wa_get_status_modules('fleet')){
				$vehicle_name = fleet_get_vehicle_name_by_id($rel_id);

	        	if($vehicle_name != ''){
	        		$str =  '<a href="' . admin_url('fleet/vehicle/' . $rel_id) . '" target="_blank">' . $vehicle_name.'</a>';
	        	}
			}
		break;

		case 'workperformance':
			if(wa_get_status_modules('fleet')){
				$CI->db->select('name');
				$CI->db->where('id', $rel_id);
				$workperformance = $CI->db->get(db_prefix().'fleet_logbooks')->row();

				$workperformance_name = '';

				if(isset($workperformance->name)){
					$workperformance_name = $workperformance->name;
				}

	        	if($workperformance_name != ''){
	        		$str =  '<a href="' . admin_url('fleet/logbook_detail/' . $rel_id) . '" target="_blank">' . $workperformance_name.'</a>';
	        	}
			}
		break;

		case 'event':
			if(wa_get_status_modules('fleet')){
				$CI->db->select('subject');
				$CI->db->where('id', $rel_id);
				$event = $CI->db->get(db_prefix().'fleet_events')->row();

				$event_name = '';

				if(isset($event->subject)){
					$event_name = $event->subject;
				}

	        	if($event_name != ''){
	        		$str =  $event_name;
	        	}
			}
		break;

		case 'work_order':
			if(wa_get_status_modules('fleet')){
				$CI->db->select('subject');
				$CI->db->where('id', $rel_id);
				$work_order = $CI->db->get(db_prefix().'fleet_work_orders')->row();

				$work_order_name = '';

				if(isset($work_order->subject)){
					$work_order_name = $work_order->subject;
				}

	        	if($work_order_name != ''){
	        		$str =  '<a href="' . admin_url('fleet/work_order_detail/' . $rel_id) . '" target="_blank">' . $work_order_name.'</a>';
	        	}
			}
		break;

		case 'booking':
			if(wa_get_status_modules('fleet')){
				$CI->db->select('subject');
				$CI->db->where('id', $rel_id);
				$booking = $CI->db->get(db_prefix().'fleet_bookings')->row();

				$booking_name = '';

				if(isset($booking->subject)){
					$booking_name = $booking->subject;
				}

	        	if($booking_name != ''){
	        		$str = '<a href="' . admin_url('fleet/booking_detail/' . $rel_id) . '" target="_blank">' . $booking_name.'</a>';
	        	}
			}
		break;

		case 'fuel':
			$str = '';
		break;

		case 'work_center':
			if(wa_get_status_modules('manufacturing')){
				$work_center_name = get_work_center_name($rel_id);

	        	if($work_center_name != ''){
	        		$str =  '<a href="' . admin_url('manufacturing/view_work_center/' . $rel_id) . '" target="_blank">' . $work_center_name.'</a>';
	        	}
			}
		break;

		case 'routing':
		
			if(wa_get_status_modules('manufacturing')){
				$routing_name = mrp_get_routing_name($rel_id);

	        	if($routing_name != ''){
	        		$str =  '<a href="' . admin_url('manufacturing/operation_manage/' . $rel_id) . '" target="_blank">' . $routing_name.'</a>';
	        	}
			}
		break;

		case 'operation':

			if(wa_get_status_modules('manufacturing')){
				$operation_name = mrp_get_routing_detail_name($rel_id);

	        	if($operation_name != ''){
	        		$str = $operation_name;
	        	}
			}
		break;

		case 'bill_of_material':
			if(wa_get_status_modules('manufacturing')){
				$bill_of_material_name = mrp_get_bill_of_material_code($rel_id);

	        	if($bill_of_material_name != ''){
	        		$str =  '<a href="' . admin_url('manufacturing/bill_of_material_detail_manage/' . $rel_id) . '" target="_blank">' . $bill_of_material_name.'</a>';
	        	}
			}
		break;

		case 'bom_component':
			$str = '';
		break;

		case 'manufacturing_order':
		
			if(wa_get_status_modules('manufacturing')){
				$manufacturing_order_name = mrp_get_manufacturing_code($rel_id);

	        	if($manufacturing_order_name != ''){
	        		$str =  '<a href="' . admin_url('manufacturing/view_manufacturing_order/' . $rel_id) . '" target="_blank">' . $manufacturing_order_name.'</a>';
	        	}
			}
		break;

		case 'omni_sales_order':
			
			if(wa_get_status_modules('omni_sales')){
				$omni_sales_order_name = omni_get_sales_order_code($rel_id);

	        	if($omni_sales_order_name != ''){
	        		$str =  '<a href="' . admin_url('omni_sales/view_order_detailt/' . $rel_id) . '" target="_blank">' . $omni_sales_order_name.'</a>';
	        	}
			}
		break;

		case 'trade_discount':

			if(wa_get_status_modules('omni_sales')){
				$CI->db->select('name_trade_discount');
				$CI->db->where('id', $rel_id);
				$trade_discount = $CI->db->get(db_prefix().'omni_trade_discount')->row();

				$trade_discount_name = '';

				if(isset($trade_discount->name_trade_discount)){
					$trade_discount_name = $trade_discount->name_trade_discount;
				}

	        	if($trade_discount_name != ''){
	        		$str = $trade_discount_name;
	        	}
			}
		break;

		case 'asset':
			if(wa_get_status_modules('fixed_equipment')){
				$asset_name = fe_item_name($rel_id);

	        	if($asset_name != ''){
	        		$str =  '<a href="' . admin_url('fixed_equipment/detail_asset/' . $rel_id.'?tab=details') . '" target="_blank">' . $asset_name.'</a>';
	        	}
			}
		break;

		case 'license':
			if(wa_get_status_modules('fixed_equipment')){
				$asset_name = fe_item_name($rel_id);

	        	if($asset_name != ''){
	        		$str =  '<a href="' . admin_url('fixed_equipment/detail_licenses/' . $rel_id.'?tab=details') . '" target="_blank">' . $asset_name.'</a>';
	        	}
			}
		break;

		case 'accessories':
			if(wa_get_status_modules('fixed_equipment')){
				$asset_name = fe_item_name($rel_id);

	        	if($asset_name != ''){
	        		$str =  '<a href="' . admin_url('fixed_equipment/detail_accessories/' . $rel_id) . '" target="_blank">' . $asset_name.'</a>';
	        	}
			}
		break;

		case 'consumable':
			if(wa_get_status_modules('fixed_equipment')){
				$asset_name = fe_item_name($rel_id);

	        	if($asset_name != ''){
	        		$str =  '<a href="' . admin_url('fixed_equipment/detail_consumables/' . $rel_id) . '" target="_blank">' . $asset_name.'</a>';
	        	}
			}
		break;

		case 'component':
			if(wa_get_status_modules('fixed_equipment')){
				$asset_name = fe_item_name($rel_id);

	        	if($asset_name != ''){
	        		$str =  '<a href="' . admin_url('fixed_equipment/detail_components/' . $rel_id) . '" target="_blank">' . $asset_name.'</a>';
	        	}
			}
		break;

		case 'payslip':
			if(wa_get_status_modules('hr_payroll')){
				$CI->db->select('payslip_name');
				$CI->db->where('id', $rel_id);
				$payslip = $CI->db->get(db_prefix().'hrp_payslips')->row();

				$payslip_name = '';

				if(isset($payslip->subject)){
					$payslip_name = $payslip->payslip_name;
				}

	        	if($payslip_name != ''){
	        		$str = '<a href="' . admin_url('hr_payroll/view_payslip_detail/' . $rel_id) . '" target="_blank">' . $payslip_name.'</a>';
	        	}
			}
		break;

		case 'payslip_template':
			if(wa_get_status_modules('hr_payroll')){
				$payslip_template_name = get_payslip_template_name($rel_id);

	        	if($payslip_template_name != ''){
	        		$str =  '<a href="' . admin_url('hr_payroll/view_payslip_templates_detail/' . $rel_id) . '" target="_blank">' . $payslip_template_name.'</a>';
	        	}
			}
		break;

		case 'items':
			if(wa_get_status_modules('warehouse')){
				$itemse_name = get_item_description($rel_id);

	        	if($itemse_name != ''){
	        		$str =  '<a href="' . admin_url('warehouse/view_commodity_detail/' . $rel_id) . '" target="_blank">' . $itemse_name.'</a>';
	        	}
			}
		break;

		case 'opening_stock':
			$str = '';
		break;

		case 'inventory_receiving_voucher':


			if(wa_get_status_modules('warehouse')){
				$goods_receipt = get_goods_receipt_code($rel_id);

				$goods_receipt_name = '';

				if(isset($goods_receipt->goods_receipt_code)){
					$goods_receipt_name = $goods_receipt->goods_receipt_code;
				}

	        	if($goods_receipt_name != ''){
	        		$str =  '<a href="' . admin_url('warehouse/manage_purchase#' . $rel_id) . '" target="_blank">' . $goods_receipt_name.'</a>';
	        	}
			}
		break;

		case 'inventory_delivery_voucher':
			if(wa_get_status_modules('warehouse')){
				$goods_delivery = get_goods_delivery_code($rel_id);

				$goods_delivery_name = '';

				if(isset($goods_delivery->goods_delivery_code)){
					$goods_delivery_name = $goods_delivery->goods_delivery_code;
				}

	        	if($goods_delivery_name != ''){
	        		$str =  '<a href="' . admin_url('warehouse/manage_delivery#' . $rel_id) . '" target="_blank">' . $goods_delivery_name.'</a>';
	        	}
			}
		break;

		case 'packing_list':

			if(wa_get_status_modules('warehouse')){
				$CI->db->select('packing_list_number');
				$CI->db->where('id', $rel_id);
				$packing_lists = $CI->db->get(db_prefix().'wh_packing_lists')->row();

				$packing_lists_name = '';

				if(isset($packing_lists->packing_list_number)){
					$packing_lists_name = $packing_lists->packing_list_number;
				}

	        	if($packing_lists_name != ''){
	        		$str = '<a href="' . admin_url('warehouse/manage_packing_list#' . $rel_id) . '" target="_blank">' . $packing_lists_name.'</a>';
	        	}
			}
		break;

        default:
        $str = '';
        break;

    }

    return $str;
}

/**
 * Check token
 */
function wa_token(){
	$token_path = realpath(realpath(__DIR__).'/..'). base64_decode('L2xpYnJhcmllcy9saWN0b2tlbi8ubGlj');
	if(!is_file($token_path)){
		redirect(admin_url());
	}	
}