<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Workflow automation model
 */
class Workflow_automation_model extends App_Model {

	/**
	 * [__construct description]
	 */
	public function __construct()
    {
        parent::__construct();
        
    }

    /**
     * [add_workflow description]
     */
    public function add_workflow($data){
    	$data['created_at'] = date('Y-m-d H:i:s');
    	$data['created_by'] = get_staff_user_id();

    	if(isset($data['start_email'])){
    		$data['start_email'] = 1;
    	}else{
    		$data['start_email'] = 0;
    	}

    	if(isset($data['private'])){
    		$data['private'] = 1;
    	}else{
    		$data['private'] = 0;
    	}

    	$data['enabled'] = 1;

    	$this->db->insert(db_prefix().'wa_workflows', $data);
    	$insert_id = $this->db->insert_id();
    	if($insert_id){
    		return $insert_id;
    	}

    	return false;
    }

    /**
     * [update_workflow description]
     * @return [type] [description]
     */
    public function update_workflow($data, $id){

    	if(isset($data['start_email'])){
    		$data['start_email'] = 1;
    	}else{
    		$data['start_email'] = 0;
    	}

    	if(isset($data['private'])){
    		$data['private'] = 1;
    	}else{
    		$data['private'] = 0;
    	}


    	$this->db->where('id', $id);
    	$this->db->update(db_prefix().'wa_workflows', $data);
    	if($this->db->affected_rows() > 0){
    		return true;
    	}
    	return false;
    }

    /**
     * [delete_workflow description]
     * @return [type] [description]
     */
    public function delete_workflow($id){
        if(!has_permission('wa_workflows', '', 'delete')){
            access_denied('workflow');
        }

        $this->db->where('flow_id', $id);
        $this->db->delete(db_prefix().'wa_flows_logs');

        $this->db->where('flow_id', $id);
        $this->db->delete(db_prefix().'wa_action_logs');

        $this->db->where('flow_id', $id);
        $this->db->delete(db_prefix().'wa_automatic_log');
        
        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'wa_workflows');
        if($this->db->affected_rows() > 0){
            return true;
        }
        return false;
    }

    /**
     * [get_workflow description]
     * @return [type] [description]
     */
    public function get_workflow($id){

        $this->db->where('id', $id);
        return $this->db->get(db_prefix().'wa_workflows')->row();
    }


    /**
     * @param  array
     * @return boolean
     */
    public function workflow_builder_save($data){
        if(isset($data['workflow_id']) && $data['workflow_id'] != ''){
            $this->db->where('id', $data['workflow_id']);
            $this->db->update(db_prefix() . 'wa_workflows', ['workflow' => json_encode($data['workflow'])]);

            if ($this->db->affected_rows() > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * [add_task_template description]
     */
    public function add_task_template($data){
        if(is_array($data['assignees']) && count($data['assignees']) > 0){
            $data['assignees'] = implode(',', $data['assignees']);
        }

        if(is_array($data['followers']) && count($data['followers']) > 0){
            $data['followers'] = implode(',', $data['followers']);
        }

        $data['start_date'] = to_sql_date($data['start_date']);
        $data['due_date'] = to_sql_date($data['due_date']);

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'wa_task_templates', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){
            return $insert_id;
        }

        return false;
    }

    /**
     * [update_task_template description]
     * @param  [type] $data [description]
     * @param  [type] $id   [description]
     * @return [type]       [description]
     */
    public function update_task_template($data, $id){

        if(is_array($data['assignees']) && count($data['assignees']) > 0){
            $data['assignees'] = implode(',', $data['assignees']);
        }

        if(is_array($data['followers']) && count($data['followers']) > 0){
            $data['followers'] = implode(',', $data['followers']);
        }

        $data['start_date'] = to_sql_date($data['start_date']);
        $data['due_date'] = to_sql_date($data['due_date']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix().'wa_task_templates', $data);
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [delete_task_template description]
     * @param  [type] $id [description]
     * @return [type]     [description]
     */
    public function delete_task_template($id){

        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'wa_task_templates');
        if($this->db->affected_rows() > 0){
            return true;
        }

        return false;
    }

    /**
     * [get_task_templates description]
     * @return [type] [description]
     */
    public function get_task_templates(){
        return $this->db->get(db_prefix().'wa_task_templates')->result_array();
    }

    /**
     * [get_task_templates description]
     * @return [type] [description]
     */
    public function get_task_template($id){
        $this->db->where('id', $id);
        return $this->db->get(db_prefix().'wa_task_templates')->row();
    }
    
    /**
     * [run_work_flow description]
     * @return [type] [description]
     */
    public function run_work_flows($data){

        $user_id = get_staff_user_id();


        $flows = $this->db->query('SELECT * FROM '.db_prefix().'wa_workflows WHERE enabled = 1 AND (private = 0 OR (private = 1 AND created_by = '.$user_id.'))')->result_array();

        foreach ($flows as $key => $flow) {
            $this->run_work_flow($flow, $data);
        }

        return true;

    }

    /**
     * [run_work_flow description]
     * @param  [type] $flow_id [description]
     * @param  [type] $data    [description]
     * @return [type]          [description]
     */
    public function run_work_flow($flow, $data){
        if(!is_array($flow)){
            return false;
        }

        $workflow = json_decode(json_decode($flow['workflow'] ?? '') ?? '', true);

        if(is_null($workflow)){
            return false;
        }

        $workflow = $workflow['drawflow']['Home']['data'];

        $data['workflow_id'] = $flow['id'];



        $this->run_work_flow_object($workflow, $data);

        return true;
    }

    /**
     * [run_work_flow_task description]
     * @return [type] [description]
     */
    public function run_work_flow_object($workflow, $run_data){

        $data = [];

        $data['workflow_id'] = $run_data['workflow_id'];
        $data['rel_type'] = $run_data['rel_type'];
        $data['rel_id'] = $run_data['rel_id'];
        $data['start_case'] = $run_data['start_case'];

        $data['invoice_id'] = isset($run_data['invoice_id']) ? $run_data['invoice_id'] : 0;
        $data['estimate_id'] = isset($run_data['estimate_id']) ? $run_data['estimate_id'] : 0;

        $data['workflow'] = $workflow;

        foreach($workflow as $data_workflow){
            $data['node'] = $data_workflow;

            if($data_workflow['class'] == 'flow_start'){
                if( ((isset($data_workflow['data']['trigger_type']) && $data_workflow['data']['trigger_type'] == 'user_action') || !isset($data_workflow['data']['trigger_type'])) && $run_data['start_case'] != 'automatic' ){
                    if(isset($data_workflow['data']['start_when']) && isset($data_workflow['data']['data_type'])){
                        if($data_workflow['data']['start_when'] == $run_data['start_case'] && $data_workflow['data']['data_type'] == $run_data['rel_type']){
                            if(!$this->check_workflow_node_log($data)){
                                $data['node_type'] = 'flow_start';
                                $data['result'] = 'success';
                                $this->save_workflow_node_log($data);
                            }
                            foreach ($data_workflow['outputs']['output_1']['connections'] as $connection) {
                                $data['node'] = $workflow[$connection['node']];
                                
                                $this->run_workflow_node($data);
                                    
                                
                            }
                        }

                    }
                }elseif(isset($data_workflow['data']['trigger_type']) && $data_workflow['data']['trigger_type'] == 'automatic' && $run_data['start_case'] == 'automatic'){
                    if(isset($data_workflow['data']['repeat_every'])){
                        if($data_workflow['data']['repeat_every'] == 'day'){

                            if((int)$data_workflow['data']['hour_of_day'] <= (int)date('H')){

                                $this->db->where('flow_id', $data['workflow_id']);
                                $this->db->where('date(created_at) = "'.date('Y-m-d').'"');
                                $logs = $this->db->get(db_prefix().'wa_automatic_log')->result_array();

                                if(count($logs) == 0){
                                    if(!$this->check_workflow_node_log($data)){
                                        $data['node_type'] = 'flow_start';
                                        $data['result'] = 'success';
                                        $this->save_workflow_node_log($data);
                                    }
                                    foreach ($data_workflow['outputs']['output_1']['connections'] as $connection) {
                                        $data['node'] = $workflow[$connection['node']];
                                        
                                        $this->run_workflow_node($data);
                                    }

                                    $automatic_log = [];
                                    $automatic_log['flow_id'] = $data['workflow_id'];
                                    $automatic_log['created_at'] = date('Y-m-d H:i:s');
                                    $automatic_log['repeat_every'] = $data_workflow['data']['repeat_every'];
                                    $automatic_log['hour_of_day'] = $data_workflow['data']['hour_of_day'];

                                    $this->save_automatic_log($automatic_log);
                                }

                            }
                        }else if($data_workflow['data']['repeat_every'] == 'week'){

                            if(isset($data_workflow['data']['day_of_week']) && $data_workflow['data']['day_of_week'] == date('N')){
                                if((int)$data_workflow['data']['hour_of_day'] <= (int)date('H')){
                                    $this->db->where('flow_id', $data['workflow_id']);
                                    $this->db->where('date(created_at) = "'.date('Y-m-d').'"');
                                    $logs = $this->db->get(db_prefix().'wa_automatic_log')->result_array();

                                    if(count($logs) == 0){
                                        if(!$this->check_workflow_node_log($data)){
                                            $data['node_type'] = 'flow_start';
                                            $data['result'] = 'success';
                                            $this->save_workflow_node_log($data);
                                        }
                                        foreach ($data_workflow['outputs']['output_1']['connections'] as $connection) {
                                            $data['node'] = $workflow[$connection['node']];
                                            
                                            $this->run_workflow_node($data);
                                                
                                        }

                                        $automatic_log = [];
                                        $automatic_log['flow_id'] = $data['workflow_id'];
                                        $automatic_log['created_at'] = date('Y-m-d H:i:s');
                                        $automatic_log['repeat_every'] = $data_workflow['data']['repeat_every'];
                                        $automatic_log['hour_of_day'] = $data_workflow['data']['hour_of_day'];
                                        $automatic_log['day_of_week'] = $data_workflow['data']['day_of_week'];

                                        $this->save_automatic_log($automatic_log);

                                    }
                                }
                            }

                        }else if($data_workflow['data']['repeat_every'] == 'no_repeat'){
                            $current_time = strtotime(date('Y-m-d H:i:s'));

                            if(isset($data_workflow['data']['time']) && $data_workflow['data']['time']){
                                $time = strtotime($data_workflow['data']['time']);
                                if($current_time >= $time){

                                    $this->db->where('flow_id', $data['workflow_id']);
                                    $logs = $this->db->get(db_prefix().'wa_automatic_log')->result_array();
                                    if(count($logs) == 0){
                                        if(!$this->check_workflow_node_log($data)){
                                            $data['node_type'] = 'flow_start';
                                            $data['result'] = 'success';
                                            $this->save_workflow_node_log($data);
                                        }
                                        foreach ($data_workflow['outputs']['output_1']['connections'] as $connection) {
                                            $data['node'] = $workflow[$connection['node']];
                                            $this->run_workflow_node($data);
                                                
                                        }

                                        $automatic_log = [];
                                        $automatic_log['flow_id'] = $data['workflow_id'];
                                        $automatic_log['created_at'] = date('Y-m-d H:i:s');
                                        $automatic_log['repeat_every'] = $data_workflow['data']['repeat_every'];
   
                                        $this->save_automatic_log($automatic_log);

                                    }
                                }
                            }
                        }else if($data_workflow['data']['repeat_every'] == 'month'){

                            if(isset($data_workflow['data']['day_of_month']) && $data_workflow['data']['day_of_month'] == date('d')){
                                if((int)$data_workflow['data']['hour_of_day'] <= (int)date('H')){

                                    $this->db->where('flow_id', $data['workflow_id']);
                                    $this->db->where('date(created_at) = "'.date('Y-m-d').'"');
                                    $logs = $this->db->get(db_prefix().'wa_automatic_log')->result_array();

                                    if(count($logs) == 0){
                                        if(!$this->check_workflow_node_log($data)){
                                            $data['node_type'] = 'flow_start';
                                            $data['result'] = 'success';
                                            $this->save_workflow_node_log($data);
                                        }
                                        foreach ($data_workflow['outputs']['output_1']['connections'] as $connection) {
                                            $data['node'] = $workflow[$connection['node']];
                                            
                                            $this->run_workflow_node($data);
                                                
                                        }

                                        $automatic_log = [];
                                        $automatic_log['flow_id'] = $data['workflow_id'];
                                        $automatic_log['created_at'] = date('Y-m-d H:i:s');
                                        $automatic_log['repeat_every'] = $data_workflow['data']['repeat_every'];
                                        $automatic_log['hour_of_day'] = $data_workflow['data']['hour_of_day'];
                                        $automatic_log['day_of_month'] = $data_workflow['data']['day_of_month'];

                                        $this->save_automatic_log($automatic_log);
                                    }
                                }
                            }

                        }
                    }
                }
            }
        }


        return true;
    }

    /**
     * [save_automatic_log description]
     * @return [type] [description]
     */
    public function save_automatic_log($data){
        $this->db->insert(db_prefix().'wa_automatic_log', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){
            return true;
        }
        return false;
    }

    /**
     * @param  array
     * @return boolean
     */
    public function check_workflow_node_log($data){
        $this->db->where('flow_id', $data['workflow_id']);
        $this->db->where('rel_type', $data['rel_type']);
        $this->db->where('rel_id', $data['rel_id']);
        $this->db->where('node_id', $data['node']['id']);
        if($data['start_case'] == 'updated' || $data['start_case'] == 'automatic' || $data['start_case'] == 'change_delivery_status' || $data['start_case'] == 'create_assignment'){
            $this->db->where('created_at', date('Y-m-d H:i:s'));
        }

        $logs = $this->db->get(db_prefix().'wa_flows_logs')->row();

        if($logs){
            return $logs->output;
        }

        return false;
    }

    /**
     * @param  array
     * @param  string
     * @return boolean
     */
    public function save_workflow_node_log($data, $output = 'output_1'){
        $this->db->where('flow_id', $data['workflow_id']);
        $this->db->where('rel_type', $data['rel_type']);
        $this->db->where('rel_id', $data['rel_id']);
        $this->db->where('node_id', $data['node']['id']);
        if($data['start_case'] == 'updated' || $data['start_case'] == 'automatic' || $data['start_case'] == 'change_delivery_status' || $data['start_case'] == 'create_assignment'){
            $this->db->where('created_at', date('Y-m-d H:i:s'));
        }
        $logs = $this->db->get(db_prefix().'wa_flows_logs')->row();

        if(!$logs){

            $this->db->insert(db_prefix().'wa_flows_logs', [
                'flow_id' => $data['workflow_id'], 
                'rel_type' => $data['rel_type'],
                'rel_id' => $data['rel_id'],
                'node_id' => $data['node']['id'], 
                'output' => $output, 
                'node_type' => $data['node_type'],
                'action' => isset($data['action']) ? $data['action'] : '',
                'condition' => isset($data['condition']) ? $data['condition'] : '',
                'condition_field' => isset($data['condition_field']) ? $data['condition_field'] : '',
                'condition_variable' => isset($data['condition_variable']) ? $data['condition_variable'] : '',
                'result' => $data['result'],
                'created_at' => date('Y-m-d H:i:s'), 
            ]);
        }

        return true;
    }

    /**
     * @param  array
     * @return boolean
     */
    public function run_workflow_node($data){
        $output = $this->check_workflow_node_log($data);
        if(!$output){
            switch ($data['node']['class']) {

                case 'condition':

                    $success = $this->handle_condition_node($data);
                    if($success == 'output_1'){
                        $data['node_type'] = 'condition';
                        $data['result'] = 'success';
                        $data['condition'] = $data['node']['data']['condition'];
                        $data['condition_field'] = $data['node']['data']['check'];
                        $data['condition_variable'] = isset($data['node']['data']['condition_variable']) ? $data['node']['data']['condition_variable'] : '';
                        $this->save_workflow_node_log($data);

                        foreach ($data['node']['outputs']['output_1']['connections'] as $connection) {
                            $data['node'] = $data['workflow'][$connection['node']];
                            $this->run_workflow_node($data);
                        }

                    }elseif($success == 'output_2'){
                        $data['node_type'] = 'condition';
                        $data['result'] = 'success';
                        $data['condition'] = $data['node']['data']['condition'];
                        $data['condition_field'] = $data['node']['data']['check'];
                        $data['condition_variable'] = isset($data['node']['data']['condition_variable']) ? $data['node']['data']['condition_variable'] : '';
                        $this->save_workflow_node_log($data, 'output_2');

                        foreach ($data['node']['outputs']['output_2']['connections'] as $connection) {
                            $data['node'] = $data['workflow'][$connection['node']];
                            $this->run_workflow_node($data);
                        }
                    }else{
                        $data['node_type'] = 'condition';
                        $data['result'] = 'fail';
                        $this->save_workflow_node_log($data, 'output_2');
                    }

                    break;

                case 'action':
                    $success = $this->handle_action_node($data);

                    if($success){
                        $data['result'] = 'success';
                        $data['node_type'] = 'action';
                        $data['action'] = $data['node']['data']['action'];
                        $this->save_workflow_node_log($data);

                        foreach ($data['node']['outputs']['output_1']['connections'] as $connection) {
                            $data['node'] = $data['workflow'][$connection['node']];
                            $this->run_workflow_node($data);
                        }
                    }else{
                        $data['result'] = 'fail';
                        $data['node_type'] = 'action';
                        $data['action'] = $data['node']['data']['action'];
                        $this->save_workflow_node_log($data);
                    }

                    break;



                default:
                    // code...
                    break;
            }
        }else{
            foreach ($data['node']['outputs'][$output]['connections'] as $connection) {
                $data['node'] = $data['workflow'][$connection['node']];
                $this->run_workflow_node($data);
            }
        }

        return true;
    }


    /**
     * @param  array
     * @return boolean
     */
    public function handle_action_node($data){

        if(!(strpos($data['node']['data']['action'], '_default') === false) ){

            return $this->handle_default_action_node($data);

        }else{
        
            switch ($data['rel_type']) {

                case 'tasks':

                    return $this->handle_task_action_node($data);

                    break;

                case 'projects':
                    return $this->handle_project_action_node($data);
                    break;

                case 'contracts':
                    return $this->handle_contract_action_node($data);
                    break;

                case 'leads':
                    return $this->handle_lead_action_node($data);
                    break;    

                case 'customers':
                    return $this->handle_customer_action_node($data);
                    break;    

                case 'proposals':
                    return $this->handle_proposal_action_node($data);
                    break;    

                case 'estimates':
                    return $this->handle_estimate_action_node($data);
                    break;  

                case 'invoices':
                    return $this->handle_invoice_action_node($data);
                    break; 

                case 'payment':
                    return $this->handle_payment_action_node($data);
                    break; 

                case 'credit_notes':
                    return $this->handle_credit_note_action_node($data);
                    break;     

                case 'purchase_order':
                    return $this->handle_purchase_order_action_node($data);
                    break;

                case 'purchase_request':
                    return $this->handle_purchase_request_action_node($data);
                    break;

                case 'purchase_quotation':
                    return $this->handle_purchase_quotation_action_node($data);
                    break;

                case 'purchase_invoice':
                    return $this->handle_purchase_invoice_action_node($data);
                    break;

                case 'purchase_contract':
                    return $this->handle_purchase_contract_action_node($data);
                    break;

                case 'vendor':
                    return $this->handle_purchase_vendor_action_node($data);
                    break;

                case 'ticket':
                    return $this->handle_cs_ticket_action_node($data);
                    break;
               
                case 'staff':
                    return $this->handle_staff_action_node($data);
                    break;
         

                case 'expense':
                    return $this->handle_expense_action_node($data);
                    break;
           
                case 'recruitment_plan':
                    return $this->handle_recruitment_plan_action_node($data);
                    break;

                case 'recruitment_campaign':
                    return $this->handle_recruitment_campaign_action_node($data);
                    break;

                case 'recruitment_form':
                    return $this->handle_recruitment_form_action_node($data);
                    break;

                case 'candidate':
                    return $this->handle_candidate_action_node($data);
                    break;

                case 'interview_schedule':
                    return $this->handle_interview_schedule_action_node($data);
                    break;

                case 'leave_request':
                    return $this->handle_leave_request_action_node($data);
                    break;

                case 'additional_work_hours':
                    return $this->handle_additional_work_hours_action_node($data);
                    break;

                case 'vehicle':
                    return $this->handle_vehicle_action_node($data);
                    break;
             
                case 'workperformance':
                    return $this->handle_workperformance_action_node($data);
                    break;

                case 'event':
                    return $this->handle_event_action_node($data);
                    break;

                case 'work_order':
                    return $this->handle_work_order_action_node($data);
                    break;

                case 'booking':
                    return $this->handle_booking_action_node($data);
                    break;

                case 'fuel':
                    return $this->handle_fuel_action_node($data);
                    break;

                case 'work_center':
                    return $this->handle_work_center_action_node($data);
                    break;

                case 'routing':
                    return $this->handle_routing_action_node($data);
                    break;

                case 'operation':
                    return $this->handle_operation_action_node($data);
                    break;    

                case 'bill_of_material':
                    return $this->handle_bill_of_material_action_node($data);
                    break;

                case 'bom_component':
                    return $this->handle_bom_component_action_node($data);
                    break;

                case 'manufacturing_order':
                    return $this->handle_manufacturing_order_action_node($data);
                    break;

                case 'omni_sales_order':
                    return $this->handle_omni_sales_order_action_node($data);
                    break;

                case 'omni_sales_refund':
                    return $this->handle_omni_sales_refund_action_node($data);
                    break;    

                case 'trade_discount':
                    return $this->handle_omni_sales_trade_discount_action_node($data);
                    break;    

                case 'asset':
                    return $this->handle_asset_action_node($data);
                    break;

                case 'license':
                    return $this->handle_license_action_node($data);
                    break;      

                case 'accessories':
                    return $this->handle_accessories_action_node($data);
                    break;   

                case 'consumable':
                    return $this->handle_consumable_action_node($data);
                    break; 

                case 'component':
                    return $this->handle_component_action_node($data);
                    break; 

                case 'payslip':
                    return $this->handle_payslip_action_node($data);
                    break;
                
                case 'payslip_template':
                    return $this->handle_payslip_template_action_node($data);
                    break;

                case 'items':
                    return $this->handle_items_action_node($data);
                    break;

                case 'inventory_receiving_voucher':
                    return $this->handle_inventory_receiving_voucher_action_node($data);
                    break;

                case 'inventory_delivery_voucher':
                    return $this->handle_inventory_delivery_voucher_action_node($data);
                    break;

                case 'packing_list':
                    return $this->handle_packing_list_action_node($data);
                    break;
        
                default:
                    break;

            }
        }

    }

    /**
     * [handle_customer_action_node description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function handle_customer_action_node($data){

        $this->load->model('clients_model');

        $client = $this->clients_model->get($data['rel_id']);


        switch ($data['node']['data']['action']) {
               
            case 'send_email':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                $content = $data['node']['data']['send_email_content'];
                $send_to = $data['node']['data']['send_email_to'];

                $sent = $this->sent_email_action($content, $send_to);

                if($sent){
                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'send_email';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'send_email';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);
                return false;
            break;

            case 'delete_customer':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($client->userid)){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_customer';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return false;
                }

                $success = $this->clients_model->delete($data['rel_id']);
                
                if($success){
                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'delete_customer';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return true;
                }
                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'delete_customer';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'update_customer_field':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($client->userid)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_customer_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return false;
                }

                $this->db->where('userid', $data['rel_id']);

                if($data['node']['data']['customer_field'] == 'company'){
                    if(!isset($data['node']['data']['company'])){
                        $data['node']['data']['task_name'] = $client->company;
                    }

                    $this->db->update(db_prefix().'clients', ['company' => $data['node']['data']['company']]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_customer_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);
                        return true;
                    }
                }else if($data['node']['data']['customer_field'] == 'vat'){
                    if(!isset($data['node']['data']['vat'])){
                        $data['node']['data']['vat'] = $client->vat;
                    }

                    $this->db->update(db_prefix().'clients', ['vat' => $data['node']['data']['vat'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_customer_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);
                        return true;
                    }
                }else if($data['node']['data']['customer_field'] == 'phonenumber'){
                    if(!isset($data['node']['data']['phonenumber'])){
                        $data['node']['data']['phonenumber'] = $client->phonenumber;
                    }

                    $this->db->update(db_prefix().'clients', ['phonenumber' => $data['node']['data']['phonenumber'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_customer_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);
                        return true;
                    }
                }else if($data['node']['data']['customer_field'] == 'website'){
                    if(!isset($data['node']['data']['website'])){
                        $data['node']['data']['website'] = $client->website;
                    }

                    $this->db->update(db_prefix().'clients', ['website' => $data['node']['data']['website'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_customer_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);
                        return true;
                    }
                }
                
                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'update_customer_field';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'change_group_customer':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($client->userid)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_group_customer';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return false;
                }
                if(isset($data['node']['data']['client_group']) && is_numeric($data['node']['data']['client_group']) && $data['node']['data']['client_group'] > 0){

                    $this->db->where('customer_id', $data['rel_id']);
                    $this->db->delete(db_prefix().'customer_groups');

                    $this->db->insert(db_prefix().'customer_groups', [
                        'customer_id' => $data['rel_id'],
                        'groupid'     => $data['node']['data']['client_group'],
                    ]);

                    $insert_id = $this->db->insert_id();
                    if($insert_id){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_group_customer';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_group_customer';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return false;

                }
            break;

            case 'add_note':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($client->userid)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'add_note';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return false;
                }
                if(!isset($data['node']['data']['note'])){
                    $data['node']['data']['note'] = '';
                }

                $this->load->model('misc_model');
                $note_data = [];
                $note_data['description'] = $data['node']['data']['note'];

                $note_id = $this->misc_model->add_note($note_data, 'customer', $client->userid);
                if($note_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'note';
                    $log_data['action_relsult_id'] = $note_id;

                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'add_note';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'create_task':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($client->userid)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $this->load->model('tasks_model');

                if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return false;
                }
                
                $task_template = $this->get_task_template($data['node']['data']['task_template']);
                if(!isset($task_template->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return false;
                }

                $task_data = [];

                $task_data['name'] = $task_template->task_subject;
                $task_data['hourly_rate'] = '';
                $task_data['startdate'] = $task_template->start_date;
                $task_data['duedate'] = $task_template->due_date;
                $task_data['priority'] = $task_template->priority;
                $task_data['rel_type'] = 'customer';
                $task_data['rel_id'] = $client->userid;
                $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                $task_data['created_by_workflow'] = 1;

                $task_id = $this->tasks_model->add($task_data);
                if($task_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = $task_id;

                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_task';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'create_reminder_for_customer':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($client->userid)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['reminder_time']) || $data['node']['data']['reminder_time'] == '' || !isset($data['node']['data']['reminder_time_type']) || $data['node']['data']['reminder_time_type'] == ''){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                if(!isset($data['node']['data']['reminder_to'])){
                    $data['node']['data']['reminder_to'] = $client->addedfrom;
                }

                if(is_numeric($data['node']['data']['reminder_to']) && $data['node']['data']['reminder_to'] > 0){
                    $this->load->model('misc_model');

                    $rmd_data = [];
                    $rmd_data['notify_by_email'] = 1;

                    $date = strtotime(date('Y-m-d H:i:s'));
                    if($data['node']['data']['reminder_time_type'] == 'min'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' minutes', $date);
                    }else if($data['node']['data']['reminder_time_type'] == 'hours'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' hours', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'days') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' day', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'months') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' months', $date);
                    }


                    $rmd_data['date'] = date('Y-m-d H:i:s', $date);

                    $rmd_data['staff'] = $data['node']['data']['reminder_to'];
                    $rmd_data['creator'] = get_staff_user_id();
                    $rmd_data['description'] = $data['node']['data']['reminder_description'];
                    $rmd_data['rel_id'] = $data['rel_id'];
                    $rmd_data['rel_type'] = 'customer';

                    $success = $this->misc_model->add_reminder($rmd_data, $data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'reminder';
                        $log_data['action_relsult_id'] = $success;

                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                }

                return false;
            break;

            case 'assign_to_staff':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($client->userid)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'assign_to_staff';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return false;
                }
                if(isset($data['node']['data']['staff']) && is_numeric($data['node']['data']['staff']) && $data['node']['data']['staff'] > 0){
                    $admin_data = [];

                    $admin_data['customer_admins'][] = $data['node']['data']['staff'];

                    $success = $this->clients_model->assign_admins($admin_data, $data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'assign_to_staff';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);
                        return true;
                    }
                }
                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'assign_to_staff';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);
                return false;
            break;

            default:
                return false;
            break;
        }

    }

    /**
     * [handle_task_action_node description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function handle_task_action_node($data){
        $this->load->model('tasks_model');

        $task = $this->tasks_model->get($data['rel_id']);

        

        switch ($data['node']['data']['action']) {
               
            case 'assign_to':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($task->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'assign_to';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return false;
                }

                $task_assigness = $this->tasks_model->get_task_assignees($data['rel_id']);
                foreach($task_assigness as $assign) {
                    if($data['node']['data']['action_variable'] == $assign['assigneeid']){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'assign_to';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);
                        return true;
                    }
                }
                
                $success = $this->tasks_model->add_task_assignees([
                            'taskid'   => $data['rel_id'],
                            'assignee' => $data['node']['data']['action_variable'],
                        ]);

                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'assign_to';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'assign_to';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
                
                break;
            case 'add_a_comment':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($task->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'add_a_comment';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $success = $this->tasks_model->add_task_comment([
                    'taskid'   => $data['rel_id'],
                    'content' => $data['node']['data']['comment_content'],
                    'staffid' => $task->addedfrom,
                    'contact_id' => 0,
                ]);

                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'task_comment';
                    $log_data['action_relsult_id'] = $success;

                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'add_a_comment';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

                break;

            case 'delete_comment':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($task->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_comment';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(count($task->comments) > 0){
                    $rs = 0;
                    foreach($task->comments as $comment){
                        $success = $this->tasks_model->remove_comment($comment['id']);
                        if($success){
                            $rs++;
                        }
                    }
                }

                if($rs > 0){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'delete_comment';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'delete_comment';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

                break;
            
            case 'delete_task':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($task->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $success = $this->tasks_model->delete_task($data['rel_id']);
                
                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'delete_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'delete_task';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

                break;

            case 'update_task_field':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($task->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_task_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $this->db->where('id', $data['rel_id']);

                if($data['node']['data']['task_field'] == 'name'){
                    if(!isset($data['node']['data']['task_name'])){
                        $data['node']['data']['task_name'] = $task->name;
                    }

                    $this->db->update(db_prefix().'tasks', ['name' => $data['node']['data']['task_name']]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_task_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['task_field'] == 'hourly_rate'){
                    if(!isset($data['node']['data']['task_hourly_rate'])){
                        $data['node']['data']['task_hourly_rate'] = 0;
                    }

                    $this->db->update(db_prefix().'tasks', ['hourly_rate' => $data['node']['data']['task_hourly_rate'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_task_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['task_field'] == 'description'){
                    if(!isset($data['node']['data']['task_description'])){
                        $data['node']['data']['task_description'] = '';
                    }

                    $this->db->update(db_prefix().'tasks', ['description' => $data['node']['data']['task_description'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_task_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }
                
                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'update_task_field';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

                break;
            case 'change_priority':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($task->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_priority';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['task_priority'])){
                    $data['node']['data']['task_priority'] = 2;
                }

                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'tasks', ['priority' => $data['node']['data']['task_priority'] ]);

                if($this->db->affected_rows() > 0){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'change_priority';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'change_priority';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

                break;


            case 'create_reminder_for_task':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($task->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                if(!isset($data['node']['data']['reminder_time']) || $data['node']['data']['reminder_time'] == '' || !isset($data['node']['data']['reminder_time_type']) || $data['node']['data']['reminder_time_type'] == ''){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                if(!isset($data['node']['data']['reminder_to'])){
                    $data['node']['data']['reminder_to'] = $task->addedfrom;
                }

                if(is_numeric($data['node']['data']['reminder_to']) && $data['node']['data']['reminder_to'] > 0){
                    $this->load->model('misc_model');

                    $rmd_data = [];
                    $rmd_data['notify_by_email'] = 1;

                    $date = strtotime(date('Y-m-d H:i:s'));
                    if($data['node']['data']['reminder_time_type'] == 'min'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' minutes', $date);
                    }else if($data['node']['data']['reminder_time_type'] == 'hours'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' hours', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'days') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' day', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'months') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' months', $date);
                    }


                    $rmd_data['date'] = date('Y-m-d H:i:s', $date);

                    $rmd_data['staff'] = $data['node']['data']['reminder_to'];
                    $rmd_data['creator'] = get_staff_user_id();
                    $rmd_data['description'] = $data['node']['data']['reminder_description'];
                    $rmd_data['rel_id'] = $data['rel_id'];
                    $rmd_data['rel_type'] = 'task';

                    $success = $this->misc_model->add_reminder($rmd_data, $data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'create_reminder';
                        $log_data['action_relsult_id'] = $success;

                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_reminder';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

                break;
            case 'create_task':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                
                $task_template = $this->get_task_template($data['node']['data']['task_template']);
                if(!isset($task_template->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    
                    return false;
                }

                $task_data = [];

                $task_data['name'] = $task_template->task_subject;
                $task_data['hourly_rate'] = '';
                $task_data['startdate'] = $task_template->start_date;
                $task_data['duedate'] = $task_template->due_date;
                $task_data['priority'] = $task_template->priority;
                $task_data['rel_type'] = $task_template->rel_type;
                $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                $task_data['created_by_workflow'] = 1;

                $task_id = $this->tasks_model->add($task_data);
                if($task_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = $task_id;

                    $this->save_action_log($log_data);
                    

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_task';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);
                    

                return false;


                break;

            case 'send_email';

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                $content = $data['node']['data']['send_email_content'];
                $send_to = $data['node']['data']['send_email_to'];

                $sent = $this->sent_email_action($content, $send_to);

                if($sent){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'send_email';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return true;
                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'send_email';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);
                return false;

                break;

            
            default:
                // code...
                break;
        }
    }


        /**
     * [handle_project_action_node description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function handle_project_action_node($data){
        $this->load->model('projects_model');

        $project = $this->projects_model->get($data['rel_id']);

        switch ($data['node']['data']['action']) {

            case 'delete_project':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($project->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_project_fields';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $success = $this->projects_model->delete($data['rel_id']);
                
                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'update_project_fields';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'update_project_fields';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'update_project_fields':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($project->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_project_fields';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }


                $this->db->where('id', $data['rel_id']);
                if($data['node']['data']['project_field'] == 'name'){
                    if(!isset($data['node']['data']['project_name'])){
                        $data['node']['data']['project_name'] = $project->name;
                    }

                    $this->db->update(db_prefix().'projects', ['name' => $data['node']['data']['project_name']]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_project_fields';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['project_field'] == 'hourly_rate'){
                    if(!isset($data['node']['data']['project_hourly_rate'])){
                        $data['node']['data']['project_hourly_rate'] = 0;
                    }

                    $this->db->update(db_prefix().'projects', ['project_rate_per_hour' => $data['node']['data']['project_hourly_rate'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_project_fields';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['project_field'] == 'description'){
                    if(!isset($data['node']['data']['project_description'])){
                        $data['node']['data']['project_description'] = '';
                    }

                    $this->db->update(db_prefix().'projects', ['description' => $data['node']['data']['project_description'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_project_fields';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'update_project_fields';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'change_status':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($project->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return false;
                }
                if(!isset($data['node']['data']['project_status'])){
                    $data['node']['data']['project_status'] = $project->status;
                }

                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'projects', ['status' => $data['node']['data']['project_status'] ]);

                if($this->db->affected_rows() > 0){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'change_status';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'change_status';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'create_task':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($project->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $this->load->model('tasks_model');

                if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                
                $task_template = $this->get_task_template($data['node']['data']['task_template']);
                if(!isset($task_template->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $task_data = [];

                $task_data['name'] = $task_template->task_subject;
                $task_data['hourly_rate'] = '';
                $task_data['startdate'] = $task_template->start_date;
                $task_data['duedate'] = $task_template->due_date;
                $task_data['priority'] = $task_template->priority;
                $task_data['rel_type'] = 'project';
                $task_data['rel_id'] = $project->id;
                $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                $task_data['created_by_workflow'] = 1;

                $task_id = $this->tasks_model->add($task_data);
                if($task_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = $task_id;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_task';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'assign_to_customer':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($project->id)){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'assign_to_customer';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['assign_to_client'])){
                    $data['node']['data']['assign_to_client'] = $project->clientid;
                }

                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'projects', ['clientid' => $data['node']['data']['assign_to_client'] ]);

                if($this->db->affected_rows() > 0){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'assign_to_customer';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'assign_to_customer';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'send_email';

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                $content = $data['node']['data']['send_email_content'];
                $send_to = $data['node']['data']['send_email_to'];

                $sent = $this->sent_email_action($content, $send_to);

                if($sent){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'send_email';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'send_email';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            default:
                return false;
            break;
        }

    }

    /**
     * [handle_contract_action_node description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function handle_contract_action_node($data){
        $this->load->model('contracts_model');

        $contract = $this->contracts_model->get($data['rel_id']);

        switch ($data['node']['data']['action']) {

            case 'send_email':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                $content = $data['node']['data']['send_email_content'];
                $send_to = $data['node']['data']['send_email_to'];

                $sent = $this->sent_email_action($content, $send_to);

                if($sent){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'send_email';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'send_email';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);


                return false;
            break;

            case 'delete_contract':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($contract->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_contract';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $success = $this->contracts_model->delete($data['rel_id']);
                
                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'delete_contract';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'delete_contract';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'change_status_project':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($contract->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_project';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                if(is_numeric($contract->project_id) && $contract->project_id > 0){
                    $project = $this->projects_model->get($contract->project_id);
                    if(isset($project->id)){

                        if(!isset($data['node']['data']['project_status'])){
                            $data['node']['data']['project_status'] = $project->status;
                        }


                        $this->db->where('id', $contract->project_id);
                        $this->db->update(db_prefix().'projects', ['status' => $data['node']['data']['project_status'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'change_status_project';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }
                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'change_status_project';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'create_task':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($contract->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                   return false;
                } 

                $this->load->model('tasks_model');

                if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                
                $task_template = $this->get_task_template($data['node']['data']['task_template']);
                if(!isset($task_template->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $task_data = [];

                $task_data['name'] = $task_template->task_subject;
                $task_data['hourly_rate'] = '';
                $task_data['startdate'] = $task_template->start_date;
                $task_data['duedate'] = $task_template->due_date;
                $task_data['priority'] = $task_template->priority;
                $task_data['rel_type'] = 'contract';
                $task_data['rel_id'] = $contract->id;
                $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                $task_data['created_by_workflow'] = 1;

                $task_id = $this->tasks_model->add($task_data);
                if($task_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = $task_id;

                    $this->save_action_log($log_data);

                    return true;
                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_task';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'assign_to_customer':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($contract->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'assign_to_customer';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                   return false;
                } 
                if(!isset($data['node']['data']['assign_to_client'])){
                    $data['node']['data']['assign_to_client'] = $contract->client;
                }

                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'contracts', ['client' => $data['node']['data']['assign_to_client'] ]);

                if($this->db->affected_rows() > 0){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'assign_to_customer';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'assign_to_customer';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'add_note':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($contract->id)){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'add_note';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                   return false;
                } 
                if(!isset($data['node']['data']['note'])){
                    $data['node']['data']['note'] = '';
                }

                $this->load->model('misc_model');
                $note_data = [];
                $note_data['description'] = $data['node']['data']['note'];

                $note_id = $this->misc_model->add_note($note_data, 'contract', $contract->id);
                if($note_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'note';
                    $log_data['action_relsult_id'] = $note_id;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'add_note';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'mark_as_signed':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($contract->id)){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'mark_as_signed';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;
                } 
               $marked = $this->contracts_model->mark_as_signed($contract->id);
                if($marked){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'mark_as_signed';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'mark_as_signed';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'unmark_as_signed':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($contract->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'unmark_as_signed';
                    $log_data['action_relsult_id'] = 0;

                     $this->save_action_log($log_data);

                    return false;
                } 
               $marked = $this->contracts_model->unmark_as_signed($contract->id);
                if($marked){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'unmark_as_signed';
                    $log_data['action_relsult_id'] = 0;

                     $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'unmark_as_signed';
                $log_data['action_relsult_id'] = 0;

                 $this->save_action_log($log_data);

                return false;
            break;

            default:
                return false;
            break;

        }


    }

    /**
     * [handle_lead_action_node description]
     * @return [type] [description]
     */
    public function handle_lead_action_node($data){
        $this->load->model('contracts_model');

        $lead = $this->leads_model->get($data['rel_id']);

        switch ($data['node']['data']['action']) {

            case 'send_email':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                $content = $data['node']['data']['send_email_content'];
                $send_to = $data['node']['data']['send_email_to'];

                $sent = $this->sent_email_action($content, $send_to);

                if($sent){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'send_email';
                    $log_data['action_relsult_id'] = 0;

                     $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'send_email';
                $log_data['action_relsult_id'] = 0;

                 $this->save_action_log($log_data);

                return false;
            break;

            case 'delete_lead':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($lead->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_lead';
                    $log_data['action_relsult_id'] = 0;

                     $this->save_action_log($log_data);

                    return false;
                }
                $success = $this->leads_model->delete($data['rel_id']);
                
                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'delete_lead';
                    $log_data['action_relsult_id'] = 0;

                     $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'delete_lead';
                $log_data['action_relsult_id'] = 0;

                 $this->save_action_log($log_data);

                return false;
            break;

            case 'update_lead_field':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($lead->id)){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_lead_field';
                    $log_data['action_relsult_id'] = 0;

                     $this->save_action_log($log_data);

                    return false;
                }

                $this->db->where('id', $data['rel_id']);
                if($data['node']['data']['lead_field'] == 'name'){
                    if(!isset($data['node']['data']['name'])){
                        $data['node']['data']['name'] = $lead->name;
                    }

                    $this->db->update(db_prefix().'leads', ['name' => $data['node']['data']['name']]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_lead_field';
                        $log_data['action_relsult_id'] = 0;

                         $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['lead_field'] == 'address'){
                    if(!isset($data['node']['data']['address'])){
                        $data['node']['data']['address'] = '';
                    }

                    $this->db->update(db_prefix().'leads', ['address' => $data['node']['data']['address'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_lead_field';
                        $log_data['action_relsult_id'] = 0;

                         $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['lead_field'] == 'description'){
                    if(!isset($data['node']['data']['description'])){
                        $data['node']['data']['description'] = '';
                    }

                    $this->db->update(db_prefix().'leads', ['description' => $data['node']['data']['description'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_lead_field';
                        $log_data['action_relsult_id'] = 0;

                         $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['lead_field'] == 'position'){
                    if(!isset($data['node']['data']['position'])){
                        $data['node']['data']['position'] = '';
                    }

                    $this->db->update(db_prefix().'leads', ['title' => $data['node']['data']['position'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_lead_field';
                        $log_data['action_relsult_id'] = 0;

                         $this->save_action_log($log_data);


                        return true;
                    }
                }
                

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'update_lead_field';
                $log_data['action_relsult_id'] = 0;

                 $this->save_action_log($log_data);

                return false;

            break;

            case 'change_status_lead':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($lead->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_lead';
                    $log_data['action_relsult_id'] = 0;

                     $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['lead_status'])){
                    $data['node']['data']['lead_status'] = $lead->status;
                }

                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'leads', ['status' => $data['node']['data']['lead_status'] ]);

                if($this->db->affected_rows() > 0){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'change_status_lead';
                    $log_data['action_relsult_id'] = 0;
                     $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'change_status_lead';
                $log_data['action_relsult_id'] = 0;

                 $this->save_action_log($log_data);

                return false;
            break;

            case 'change_source_lead':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($lead->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_source_lead';
                    $log_data['action_relsult_id'] = 0;

                     $this->save_action_log($log_data);


                    return false;
                }
                if(!isset($data['node']['data']['lead_source'])){
                    $data['node']['data']['lead_source'] = $lead->source;
                }

                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'leads', ['source' => $data['node']['data']['lead_source'] ]);

                if($this->db->affected_rows() > 0){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'change_source_lead';
                    $log_data['action_relsult_id'] = 0;

                     $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'change_source_lead';
                $log_data['action_relsult_id'] = 0;

                 $this->save_action_log($log_data);


                return false;
            break;

            case 'assign_to_staff':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($lead->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'assign_to_staff';
                    $log_data['action_relsult_id'] = 0;

                     $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['staff'])){
                    $data['node']['data']['staff'] = $lead->assigned;
                }

                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'leads', ['assigned' => $data['node']['data']['staff'] ]);

                if($this->db->affected_rows() > 0){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'assign_to_staff';
                    $log_data['action_relsult_id'] = 0;

                     $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'assign_to_staff';
                $log_data['action_relsult_id'] = 0;

                 $this->save_action_log($log_data);

                return false;
            break;

            case 'convert_to_customer':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($lead->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'convert_to_customer';
                    $log_data['action_relsult_id'] = 0;

                     $this->save_action_log($log_data);

                    return false;
                }
                $client_id = $this->convert_lead_to_customer($lead);
                if($client_id){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'client';
                    $log_data['action_relsult_id'] = $client_id;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'convert_to_customer';
                $log_data['action_relsult_id'] = 0;

                 $this->save_action_log($log_data);

                return false;
            break;

            case 'create_task':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($lead->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $this->load->model('tasks_model');

                if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                
                $task_template = $this->get_task_template($data['node']['data']['task_template']);
                if(!isset($task_template->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $task_data = [];

                $task_data['name'] = $task_template->task_subject;
                $task_data['hourly_rate'] = '';
                $task_data['startdate'] = $task_template->start_date;
                $task_data['duedate'] = $task_template->due_date;
                $task_data['priority'] = $task_template->priority;
                $task_data['rel_type'] = 'lead';
                $task_data['rel_id'] = $lead->id;
                $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                $task_data['created_by_workflow'] = 1;

                $task_id = $this->tasks_model->add($task_data);
                if($task_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = $task_id;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_task';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'add_note':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($lead->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'add_note';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;
                }
                if(!isset($data['node']['data']['note'])){
                    $data['node']['data']['note'] = '';
                }

                $this->load->model('misc_model');
                $note_data = [];
                $note_data['description'] = $data['node']['data']['note'];

                $note_id = $this->misc_model->add_note($note_data, 'lead', $lead->id);
                if($note_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'note';
                    $log_data['action_relsult_id'] = $note_id;

                    $this->save_action_log($log_data);


                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'add_note';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'create_reminder_for_lead':

                 $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($lead->id)){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['reminder_time']) || $data['node']['data']['reminder_time'] == '' || !isset($data['node']['data']['reminder_time_type']) || $data['node']['data']['reminder_time_type'] == ''){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                if(!isset($data['node']['data']['reminder_to'])){
                    $data['node']['data']['reminder_to'] = $lead->assigned;
                }

                if(is_numeric($data['node']['data']['reminder_to']) && $data['node']['data']['reminder_to'] > 0){
                    $this->load->model('misc_model');

                    $rmd_data = [];
                    $rmd_data['notify_by_email'] = 1;

                    $date = strtotime(date('Y-m-d H:i:s'));
                    if($data['node']['data']['reminder_time_type'] == 'min'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' minutes', $date);
                    }else if($data['node']['data']['reminder_time_type'] == 'hours'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' hours', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'days') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' day', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'months') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' months', $date);
                    }


                    $rmd_data['date'] = date('Y-m-d H:i:s', $date);

                    $rmd_data['staff'] = $data['node']['data']['reminder_to'];
                    $rmd_data['creator'] = get_staff_user_id();
                    $rmd_data['description'] = $data['node']['data']['reminder_description'];
                    $rmd_data['rel_id'] = $data['rel_id'];
                    $rmd_data['rel_type'] = 'lead';

                    $success = $this->misc_model->add_reminder($rmd_data, $data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'reminder';
                        $log_data['action_relsult_id'] = $success;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_reminder';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'mark_as_lost':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($lead->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'mark_as_lost';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $marked = $this->leads_model->mark_as_lost($lead->id);

                if($marked){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'mark_as_lost';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'mark_as_lost';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'unmark_as_lost':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($lead->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'unmark_as_lost';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $marked = $this->leads_model->unmark_as_lost($lead->id);

                if($marked){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'unmark_as_lost';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'unmark_as_lost';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'mark_as_junk':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($lead->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'mark_as_junk';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $marked = $this->leads_model->mark_as_junk($lead->id);

                if($marked){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'mark_as_junk';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'mark_as_junk';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'unmark_as_junk':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($lead->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'unmark_as_junk';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $marked = $this->leads_model->unmark_as_junk($lead->id);

                if($marked){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'unmark_as_junk';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'unmark_as_junk';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            default:
                return false;
            break;
        }
    }

    /**
     * [handle_proposal_action_node description]
     * @return [type] [description]
     */
    public function handle_proposal_action_node($data){
        $this->load->model('proposals_model');

        $proposal = $this->proposals_model->get($data['rel_id']);


        switch ($data['node']['data']['action']) {

            case 'update_proposal_field':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($proposal->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_proposal_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $this->db->where('id', $data['rel_id']);

                if($data['node']['data']['proposal_field'] == 'subject'){
                    if(!isset($data['node']['data']['subject'])){
                        $data['node']['data']['subject'] = $proposal->subject;
                    }

                    $this->db->update(db_prefix().'proposals', ['subject' => $data['node']['data']['subject']]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_proposal_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['proposal_field'] == 'assigned'){
                    if(!isset($data['node']['data']['assigned'])){
                        $data['node']['data']['assigned'] = $proposal->assigned;
                    }

                    $this->db->update(db_prefix().'proposals', ['assigned' => $data['node']['data']['assigned'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_proposal_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['proposal_field'] == 'proposal_to'){
                    if(!isset($data['node']['data']['proposal_to'])){
                        $data['node']['data']['proposal_to'] = '';
                    }

                    $this->db->update(db_prefix().'proposals', ['proposal_to' => $data['node']['data']['proposal_to'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_proposal_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['proposal_field'] == 'email'){
                    if(!isset($data['node']['data']['email'])){
                        $data['node']['data']['email'] = '';
                    }

                    $this->db->update(db_prefix().'proposals', ['email' => $data['node']['data']['email'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_proposal_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }
                
                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'update_proposal_field';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'delete_proposal':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($proposal->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_proposal';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $this->load->model('proposals_model');
                $success = $this->proposals_model->delete($data['rel_id']);
                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'delete_proposal';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'delete_proposal';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'change_status_proposal':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($proposal->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_proposal';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['proposal_status'])){
                    $data['node']['data']['proposal_status'] = $proposal->status;
                }

                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'proposals', ['status' => $data['node']['data']['proposal_status'] ]);

                if($this->db->affected_rows() > 0){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'change_status_proposal';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }
                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'change_status_proposal';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);


                return false;
            break;

            case 'change_project':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($proposal->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_project';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if($proposal->rel_type == 'customer' ){
                    if(!isset($data['node']['data']['project'])){
                        $data['node']['data']['project'] = $proposal->project_id;
                    }

                    $project_ids = [];
                    $this->load->model('projects_model');
                    $projects = $this->projects_model->get('', 'clientid = '.$proposal->rel_id);
                    foreach($projects as $project){
                        if(!in_array($project['id'], $project_ids)){
                            $project_ids[] = $project['id'];
                        }
                    }

                    if(!in_array($data['node']['data']['project'], $project_ids)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_project';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }


                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'proposals', ['project_id' => $data['node']['data']['project'] ]);

                    if($this->db->affected_rows() > 0){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_project';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return true;
                    }
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'change_project';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'add_comment':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($proposal->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'add_comment';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['comment_content'])){
                    $data['node']['data']['comment_content'] = '';
                }


                $comment_data = [];
                $comment_data['content'] = $data['node']['data']['comment_content'];
                $comment_data['proposalid'] = $data['rel_id'];

                $success = $this->proposals_model->add_comment($comment_data);

                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'comment';
                    $log_data['action_relsult_id'] = $success;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'add_comment';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'create_reminder_for_proposal':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($proposal->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['reminder_time']) || $data['node']['data']['reminder_time'] == '' || !isset($data['node']['data']['reminder_time_type']) || $data['node']['data']['reminder_time_type'] == ''){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                if(!isset($data['node']['data']['reminder_to'])){
                    $data['node']['data']['reminder_to'] = $proposal->assigned;
                }

                if(is_numeric($data['node']['data']['reminder_to']) && $data['node']['data']['reminder_to'] > 0){
                    $this->load->model('misc_model');

                    $rmd_data = [];
                    $rmd_data['notify_by_email'] = 1;

                    $date = strtotime(date('Y-m-d H:i:s'));
                    if($data['node']['data']['reminder_time_type'] == 'min'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' minutes', $date);
                    }else if($data['node']['data']['reminder_time_type'] == 'hours'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' hours', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'days') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' day', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'months') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' months', $date);
                    }


                    $rmd_data['date'] = date('Y-m-d H:i:s', $date);

                    $rmd_data['staff'] = $data['node']['data']['reminder_to'];
                    $rmd_data['creator'] = get_staff_user_id();
                    $rmd_data['description'] = $data['node']['data']['reminder_description'];
                    $rmd_data['rel_id'] = $data['rel_id'];
                    $rmd_data['rel_type'] = 'proposal';

                    $success = $this->misc_model->add_reminder($rmd_data, $data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'reminder';
                        $log_data['action_relsult_id'] = $success;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                }

                return false;
            break;

            case 'create_task':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($proposal->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $this->load->model('tasks_model');

                if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                
                $task_template = $this->get_task_template($data['node']['data']['task_template']);
                if(!isset($task_template->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $task_data = [];

                $task_data['name'] = $task_template->task_subject;
                $task_data['hourly_rate'] = '';
                $task_data['startdate'] = $task_template->start_date;
                $task_data['duedate'] = $task_template->due_date;
                $task_data['priority'] = $task_template->priority;
                $task_data['rel_type'] = 'proposal';
                $task_data['rel_id'] = $proposal->id;
                $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                $task_data['created_by_workflow'] = 1;

                $task_id = $this->tasks_model->add($task_data);
                if($task_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = $task_id;

                    $this->save_action_log($log_data);

                    return true;
                }

                return false;
            break;

            case 'create_note':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($proposal->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_note';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['note'])){
                    $data['node']['data']['note'] = '';
                }

                $this->load->model('misc_model');
                $note_data = [];
                $note_data['description'] = $data['node']['data']['note'];

                $note_id = $this->misc_model->add_note($note_data, 'proposal', $proposal->id);
                if($note_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'note';
                    $log_data['action_relsult_id'] = $note_id;

                    $this->save_action_log($log_data);

                    return true;
                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_note';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            default:
                return false;
            break;
        }
    }

    /**
     * [handle_estimate_action_node description]
     * @return [type] [description]
     */
    public function handle_estimate_action_node($data){
        $this->load->model('estimates_model');

        $estimate = $this->estimates_model->get($data['rel_id']);

        if(!isset($estimate->id)){
            return false;
        }

        switch ($data['node']['data']['action']) {


            case 'update_estimate_field':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($estimate->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_estimate_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $this->db->where('id', $data['rel_id']);

                if($data['node']['data']['estimate_field'] == 'reference_no'){
                    if(!isset($data['node']['data']['reference_no'])){
                        $data['node']['data']['reference_no'] = $estimate->reference_no;
                    }

                    $this->db->update(db_prefix().'estimates', ['reference_no' => $data['node']['data']['reference_no']]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_estimate_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['estimate_field'] == 'sale_agent'){
                    if(!isset($data['node']['data']['sale_agent'])){
                        $data['node']['data']['sale_agent'] = $estimate->sale_agent;
                    }

                    $this->db->update(db_prefix().'estimates', ['sale_agent' => $data['node']['data']['sale_agent'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_estimate_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['estimate_field'] == 'adminnote'){
                    if(!isset($data['node']['data']['adminnote'])){
                        $data['node']['data']['adminnote'] = '';
                    }

                    $this->db->update(db_prefix().'estimates', ['adminnote' => $data['node']['data']['adminnote'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_estimate_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['estimate_field'] == 'clientnote'){
                    if(!isset($data['node']['data']['clientnote'])){
                        $data['node']['data']['clientnote'] = '';
                    }

                    $this->db->update(db_prefix().'estimates', ['clientnote' => $data['node']['data']['clientnote'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_estimate_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['estimate_field'] == 'terms'){
                    if(!isset($data['node']['data']['terms'])){
                        $data['node']['data']['terms'] = '';
                    }

                    $this->db->update(db_prefix().'estimates', ['terms' => $data['node']['data']['terms'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_estimate_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'update_estimate_field';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'delete_estimate':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($estimate->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_estimate';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $success = $this->estimates_model->delete($data['rel_id'], true);
                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'delete_estimate';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'delete_estimate';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'change_status_estimate':

                 $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($estimate->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_estimate';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['estimate_status'])){
                    $data['node']['data']['estimate_status'] = $estimate->status;
                }

                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'estimates', ['status' => $data['node']['data']['estimate_status'] ]);

                if($this->db->affected_rows() > 0){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'change_status_estimate';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'change_status_estimate';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'change_project':

                 $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($estimate->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_project';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;
                }
                if(!isset($data['node']['data']['project'])){
                    $data['node']['data']['project'] = $estimate->project_id;
                }

                $project_ids = [];
                $this->load->model('projects_model');
                $projects = $this->projects_model->get('', 'clientid = '.$estimate->clientid);
                foreach($projects as $project){
                    if(!in_array($project['id'], $project_ids)){
                        $project_ids[] = $project['id'];
                    }
                }

                if(!in_array($data['node']['data']['project'], $project_ids)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_project';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }


                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'estimates', ['project_id' => $data['node']['data']['project'] ]);

                if($this->db->affected_rows() > 0){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'change_project';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'change_project';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);
                
                return false;

            break;

            case 'convert_to_invoice':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($estimate->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'convert_to_invoice';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $invoiceid = $this->estimates_model->convert_to_invoice($data['rel_id'], false, true);
                if($invoiceid){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'invoice';
                    $log_data['action_relsult_id'] = $invoiceid;

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'convert_to_invoice';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'create_reminder_for_estimate':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($estimate->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['reminder_time']) || $data['node']['data']['reminder_time'] == '' || !isset($data['node']['data']['reminder_time_type']) || $data['node']['data']['reminder_time_type'] == ''){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;
                }

                if(!isset($data['node']['data']['reminder_to'])){
                    $data['node']['data']['reminder_to'] = $estimate->sale_agent;
                }

                if(is_numeric($data['node']['data']['reminder_to']) && $data['node']['data']['reminder_to'] > 0){
                    $this->load->model('misc_model');

                    $rmd_data = [];
                    $rmd_data['notify_by_email'] = 1;

                    $date = strtotime(date('Y-m-d H:i:s'));
                    if($data['node']['data']['reminder_time_type'] == 'min'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' minutes', $date);
                    }else if($data['node']['data']['reminder_time_type'] == 'hours'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' hours', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'days') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' day', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'months') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' months', $date);
                    }


                    $rmd_data['date'] = date('Y-m-d H:i:s', $date);

                    $rmd_data['staff'] = $data['node']['data']['reminder_to'];
                    $rmd_data['creator'] = get_staff_user_id();
                    $rmd_data['description'] = $data['node']['data']['reminder_description'];
                    $rmd_data['rel_id'] = $data['rel_id'];
                    $rmd_data['rel_type'] = 'estimate';

                    $success = $this->misc_model->add_reminder($rmd_data, $data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'reminder';
                        $log_data['action_relsult_id'] = $success;

                        $this->save_action_log($log_data);

                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;

                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_reminder';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);


                return false;
            break;

            case 'create_task':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($estimate->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $this->load->model('tasks_model');

                if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                
                $task_template = $this->get_task_template($data['node']['data']['task_template']);
                if(!isset($task_template->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $task_data = [];

                $task_data['name'] = $task_template->task_subject;
                $task_data['hourly_rate'] = '';
                $task_data['startdate'] = $task_template->start_date;
                $task_data['duedate'] = $task_template->due_date;
                $task_data['priority'] = $task_template->priority;
                $task_data['rel_type'] = 'estimate';
                $task_data['rel_id'] = $estimate->id;
                $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                $task_data['created_by_workflow'] = 1;

                $task_id = $this->tasks_model->add($task_data);
                if($task_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = $task_id;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_task';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'create_note':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($estimate->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_note';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;
                }
                if(!isset($data['node']['data']['note'])){
                    $data['node']['data']['note'] = '';
                }

                $this->load->model('misc_model');
                $note_data = [];
                $note_data['description'] = $data['node']['data']['note'];

                $note_id = $this->misc_model->add_note($note_data, 'estimate', $estimate->id);
                if($note_id){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'note';
                    $log_data['action_relsult_id'] = $note_id;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_note';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            default:
                return false;
            break;

        }
    }

    /**
     * [handle_invoice_action_node description]
     * @return [type] [description]
     */
    public function handle_invoice_action_node($data){
        $this->load->model('invoices_model');

        $invoice = $this->invoices_model->get($data['rel_id']);

       

        switch ($data['node']['data']['action']) {

            case 'update_invoice_field':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($invoice->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_invoice_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $this->db->where('id', $data['rel_id']);

                if($data['node']['data']['invoice_field'] == 'sale_agent'){
                    if(!isset($data['node']['data']['sale_agent'])){
                        $data['node']['data']['sale_agent'] = $invoice->sale_agent;
                    }

                    $this->db->update(db_prefix().'invoices', ['sale_agent' => $data['node']['data']['sale_agent'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_invoice_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['invoice_field'] == 'adminnote'){
                    if(!isset($data['node']['data']['adminnote'])){
                        $data['node']['data']['adminnote'] = '';
                    }

                    $this->db->update(db_prefix().'invoices', ['adminnote' => $data['node']['data']['adminnote'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_invoice_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['invoice_field'] == 'clientnote'){
                    if(!isset($data['node']['data']['clientnote'])){
                        $data['node']['data']['clientnote'] = '';
                    }

                    $this->db->update(db_prefix().'invoices', ['clientnote' => $data['node']['data']['clientnote'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_invoice_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['invoice_field'] == 'terms'){
                    if(!isset($data['node']['data']['terms'])){
                        $data['node']['data']['terms'] = '';
                    }

                    $this->db->update(db_prefix().'invoices', ['terms' => $data['node']['data']['terms'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_invoice_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'update_invoice_field';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);
                
                return false;
            break;

            case 'delete_invoice':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($invoice->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_invoice';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;
                }
                $success = $this->invoices_model->delete($data['rel_id'], true);
                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'delete_invoice';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'delete_invoice';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'change_status_invoice':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($invoice->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_project';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['invoice_status'])){
                    $data['node']['data']['invoice_status'] = $invoice->status;
                }

                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'invoices', ['status' => $data['node']['data']['invoice_status'] ]);

                if($this->db->affected_rows() > 0){
                    return true;
                }
                return false;
            break;

            
            case 'change_project':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($invoice->id)){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_project';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['project'])){
                    $data['node']['data']['project'] = $invoice->project_id;
                }

                $project_ids = [];
                $this->load->model('projects_model');
                $projects = $this->projects_model->get('', 'clientid = '.$invoice->clientid);
                foreach($projects as $project){
                    if(!in_array($project['id'], $project_ids)){
                        $project_ids[] = $project['id'];
                    }
                }

                if(!in_array($data['node']['data']['project'], $project_ids)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_project';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }


                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'invoices', ['project_id' => $data['node']['data']['project'] ]);

                if($this->db->affected_rows() > 0){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'change_project';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }
                
                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'change_project';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'create_reminder_for_invoice':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($invoice->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['reminder_time']) || $data['node']['data']['reminder_time'] == '' || !isset($data['node']['data']['reminder_time_type']) || $data['node']['data']['reminder_time_type'] == ''){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                if(!isset($data['node']['data']['reminder_to'])){
                    $data['node']['data']['reminder_to'] = $invoice->sale_agent;
                }

                if(is_numeric($data['node']['data']['reminder_to']) && $data['node']['data']['reminder_to'] > 0){
                    $this->load->model('misc_model');

                    $rmd_data = [];
                    $rmd_data['notify_by_email'] = 1;

                    $date = strtotime(date('Y-m-d H:i:s'));
                    if($data['node']['data']['reminder_time_type'] == 'min'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' minutes', $date);
                    }else if($data['node']['data']['reminder_time_type'] == 'hours'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' hours', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'days') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' day', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'months') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' months', $date);
                    }


                    $rmd_data['date'] = date('Y-m-d H:i:s', $date);

                    $rmd_data['staff'] = $data['node']['data']['reminder_to'];
                    $rmd_data['creator'] = get_staff_user_id();
                    $rmd_data['description'] = $data['node']['data']['reminder_description'];
                    $rmd_data['rel_id'] = $data['rel_id'];
                    $rmd_data['rel_type'] = 'invoice';

                    $success = $this->misc_model->add_reminder($rmd_data, $data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'reminder';
                        $log_data['action_relsult_id'] = $success;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                }

                return false;
            break;

            case 'create_task':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($invoice->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $this->load->model('tasks_model');

                if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                     $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                
                $task_template = $this->get_task_template($data['node']['data']['task_template']);
                if(!isset($task_template->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $task_data = [];

                $task_data['name'] = $task_template->task_subject;
                $task_data['hourly_rate'] = '';
                $task_data['startdate'] = $task_template->start_date;
                $task_data['duedate'] = $task_template->due_date;
                $task_data['priority'] = $task_template->priority;
                $task_data['rel_type'] = 'invoice';
                $task_data['rel_id'] = $invoice->id;
                $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                $task_data['created_by_workflow'] = 1;

                $task_id = $this->tasks_model->add($task_data);
                if($task_id){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = $task_id;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_task';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'create_note':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($invoice->id)){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_note';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['note'])){
                    $data['node']['data']['note'] = '';
                }

                $this->load->model('misc_model');
                $note_data = [];
                $note_data['description'] = $data['node']['data']['note'];

                $note_id = $this->misc_model->add_note($note_data, 'invoice', $invoice->id);
                if($note_id){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'note';
                    $log_data['action_relsult_id'] = $note_id;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_note';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'create_payment':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($invoice->id)){
                    return false;
                }
                $payment_data = [];
                $payment_data['invoiceid'] = $invoice->id;
                $payment_data['amount'] = get_invoice_total_left_to_pay($invoice->id);
                if($payment_data['amount'] == 0){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_payment';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $invoice->allowed_payment_modes = unserialize($invoice->allowed_payment_modes);
                foreach ($invoice->allowed_payment_modes as $key => $allowed_mode) {
                    if($key == 0){
                        $payment_data['paymentmode'] = $allowed_mode;
                    }
                }

                $payment_data['date'] = date('Y-m-d');

                $this->load->model('payments_model');
                $payment_id = $this->payments_model->add($payment_data);
                if($payment_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'payment';
                    $log_data['action_relsult_id'] = $payment_id;

                    $this->save_action_log($log_data);

                    return true;
                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_payment';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);
                return false;

                
            break;

            default:
                return false;
            break;
        }
    }

    /**
     * [handle_payment_action_node description]
     * @return [type] [description]
     */
    public function handle_payment_action_node($data){

        $this->load->model('invoices_model');
        $this->load->model('payments_model');

        $invoice = $this->invoices_model->get($data['invoice_id']);

        $payment = $this->payments_model->get($data['rel_id']);

        

        $invoice->allowed_payment_modes = unserialize($invoice->allowed_payment_modes);

        switch ($data['node']['data']['action']) {

            
            case 'delete_payment':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($payment->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_payment';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                if(!isset($invoice->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_payment';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $success = $this->payments_model->delete($data['rel_id']);
                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'delete_payment';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'delete_payment';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'update_payment_field':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($payment->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_payment_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                if(!isset($invoice->id)){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_payment_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;
                }
                if($data['node']['data']['payment_field'] == 'paymentmethod'){
                    if(!isset($data['node']['data']['paymentmethod'])){
                        $data['node']['data']['paymentmethod'] = '';
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'invoicepaymentrecords', ['paymentmethod' => $data['node']['data']['paymentmethod'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_payment_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);
                        return true;
                    }
                }else if($data['node']['data']['payment_field'] == 'paymentmode'){
                    if(!isset($data['node']['data']['paymentmode']) || !in_array($data['node']['data']['paymentmode'], $invoice->allowed_payment_modes)){
                        $data['node']['data']['paymentmode'] = $payment->paymentmode;
                    }

                     $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'invoicepaymentrecords', ['paymentmode' => $data['node']['data']['paymentmode'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_payment_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['payment_field'] == 'transactionid'){
                    if(!isset($data['node']['data']['transactionid'])){
                        $data['node']['data']['transactionid'] = '';
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'invoicepaymentrecords', ['transactionid' => $data['node']['data']['transactionid'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_payment_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['payment_field'] == 'note'){
                    if(!isset($data['node']['data']['note'])){
                        $data['node']['data']['note'] = '';
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'invoicepaymentrecords', ['note' => $data['node']['data']['note'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_payment_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'update_payment_field';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);
                return false;
            break;

            default:
                return false;
            break;
        }
    }

    /**
     * [handle_credit_note_action_node description]
     * @return [type] [description]
     */
    public function handle_credit_note_action_node($data){
        $this->load->model('credit_notes_model');

        $credit_note = $this->credit_notes_model->get($data['rel_id']);

        switch ($data['node']['data']['action']) {
            case 'delete_credit_note':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($credit_note->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_credit_note';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $success = $this->credit_notes_model->delete($data['rel_id']);
                if($success){
                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'delete_credit_note';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'delete_credit_note';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'update_credit_note_field':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($credit_note->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_credit_note_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if($data['node']['data']['credit_note_field'] == 'reference_no'){
                    if(!isset($data['node']['data']['reference_no'])){
                        $data['node']['data']['reference_no'] = '';
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'creditnotes', ['reference_no' => $data['node']['data']['reference_no'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_credit_note_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['credit_note_field'] == 'adminnote'){
                    if(!isset($data['node']['data']['adminnote'])){
                        $data['node']['data']['adminnote'] = '';
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'creditnotes', ['adminnote' => $data['node']['data']['adminnote'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_credit_note_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['credit_note_field'] == 'clientnote'){
                    if(!isset($data['node']['data']['clientnote'])){
                        $data['node']['data']['clientnote'] = '';
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'creditnotes', ['clientnote' => $data['node']['data']['clientnote'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_credit_note_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['credit_note_field'] == 'terms'){
                    if(!isset($data['node']['data']['terms'])){
                        $data['node']['data']['terms'] = '';
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'creditnotes', ['terms' => $data['node']['data']['terms'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_credit_note_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'update_credit_note_field';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'change_status_credit_note':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($credit_note->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_credit_note';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return false;
                }

                if(!isset($data['node']['data']['credit_note_status'])){
                    $data['node']['data']['credit_note_status'] = $credit_note->status;
                }

                $this->db->where('id', $data['rel_id']);
                $this->db->update(db_prefix().'creditnotes', ['status' => $data['node']['data']['credit_note_status'] ]);
                if($this->db->affected_rows() > 0){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'change_status_credit_note';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'change_status_credit_note';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'create_reminder_for_credit_note':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($credit_note->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if(!isset($data['node']['data']['reminder_time']) || $data['node']['data']['reminder_time'] == '' || !isset($data['node']['data']['reminder_time_type']) || $data['node']['data']['reminder_time_type'] == ''){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                if(!isset($data['node']['data']['reminder_to'])){
                    $data['node']['data']['reminder_to'] = $credit_note->addedfrom;
                }

                if(is_numeric($data['node']['data']['reminder_to']) && $data['node']['data']['reminder_to'] > 0){
                    $this->load->model('misc_model');

                    $rmd_data = [];
                    $rmd_data['notify_by_email'] = 1;

                    $date = strtotime(date('Y-m-d H:i:s'));
                    if($data['node']['data']['reminder_time_type'] == 'min'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' minutes', $date);
                    }else if($data['node']['data']['reminder_time_type'] == 'hours'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' hours', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'days') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' day', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'months') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' months', $date);
                    }


                    $rmd_data['date'] = date('Y-m-d H:i:s', $date);

                    $rmd_data['staff'] = $data['node']['data']['reminder_to'];
                    $rmd_data['creator'] = get_staff_user_id();
                    $rmd_data['description'] = $data['node']['data']['reminder_description'];
                    $rmd_data['rel_id'] = $data['rel_id'];
                    $rmd_data['rel_type'] = 'credit_note';

                    $success = $this->misc_model->add_reminder($rmd_data, $data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'reminder';
                        $log_data['action_relsult_id'] = $success;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_reminder';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'refund':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($credit_note->id)){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'refund';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $refund_data = [];
                $refund_data['refunded_on'] = date('Y-m-d');
                $refund_data['staff_id'] = get_staff_user_id();
                $refund_data['amount'] = $credit_note->remaining_credits;

                $this->load->model('payment_modes_model');
                $payment_modes = $this->payment_modes_model->get('', [
                    'expenses_only !=' => 1,
                ]);

                $refund_data['payment_mode'] = $payment_modes[0]['id'];
                $refund_data['note'] = '';
                $refund_id = $this->credit_notes_model->create_refund($data['rel_id'], $refund_data); 
                if($refund_id){
                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'refund';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'refund';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'void':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($credit_note->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'void';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                if ($credit_note->status != 2 && $credit_note->status != 3 && !$credit_note->credits_used){
                    $success = $this->credit_notes_model->mark($data['rel_id'], 3);

                    if($success){
                         $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'void';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'void';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);
                
                return false;
            break;

            case 'mark_as_open':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($credit_note->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'mark_as_open';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                if (total_rows(db_prefix() . 'creditnotes', ['status' => 3, 'id' => $id]) ) {
                    $success = $this->credit_notes_model->mark($id, 1);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'mark_as_open';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'mark_as_open';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            default:
                return false;
            break;
        }
    }

    /**
     * [handle_purchase_order_action_node description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function handle_purchase_order_action_node($data){
        if(wa_get_status_modules('purchase')){

            $this->load->model('purchase/purchase_model');

            $purchase_order = $this->purchase_model->get_pur_order($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'update_purchase_order_field':

                     $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_order->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_purchase_order_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return false;
                    }
                    if($data['node']['data']['purchase_order_field'] == 'purchase_order_description'){
                        if(!isset($data['node']['data']['pur_order_name'])){
                            $data['node']['data']['pur_order_name'] = $purchase_order->pur_order_name;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_orders', ['pur_order_name' => $data['node']['data']['pur_order_name'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_order_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_order_field'] == 'vendor'){
                        if(!isset($data['node']['data']['vendor'])){
                            $data['node']['data']['vendor'] = $purchase_order->vendor;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_orders', ['vendor' => $data['node']['data']['vendor'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_order_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_order_field'] == 'buyer'){
                        if(!isset($data['node']['data']['buyer'])){
                            $data['node']['data']['buyer'] = $purchase_order->buyer;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_orders', ['buyer' => $data['node']['data']['buyer'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_order_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_order_field'] == 'type'){
                        if(!isset($data['node']['data']['type'])){
                            $data['node']['data']['type'] = '';
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_orders', ['type' => $data['node']['data']['type'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_order_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_order_field'] == 'project'){
                        if(!isset($data['node']['data']['project'])){
                            $data['node']['data']['project'] = $purchase_order->project;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_orders', ['project' => $data['node']['data']['project'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_order_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_order_field'] == 'vendornote'){
                        if(!isset($data['node']['data']['vendornote'])){
                            $data['node']['data']['vendornote'] = $purchase_order->vendornote;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_orders', ['vendornote' => $data['node']['data']['vendornote'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_order_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_order_field'] == 'terms'){
                        if(!isset($data['node']['data']['terms'])){
                            $data['node']['data']['terms'] = $purchase_order->terms;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_orders', ['terms' => $data['node']['data']['terms'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_order_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_purchase_order_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;

                break;

                case 'delete_purchase_order':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_order->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_purchase_order';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->purchase_model->delete_pur_order($data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_purchase_order';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_purchase_order';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'change_approval_status':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($purchase_order->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_approval_status_purchase_order';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['approval_status'])){
                        $data['node']['data']['approval_status'] = $purchase_order->approval_status;
                    }

                    $success = $this->purchase_model->change_status_pur_order($data['node']['data']['approval_status'], $data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_approval_status_purchase_order';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_approval_status_purchase_order';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;

                break;

                case 'change_delivery_status':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($purchase_order->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_delivery_status';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['delivery_status'])){
                        $data['node']['data']['delivery_status'] = $purchase_order->delivery_status;
                    }

                    $success = $this->purchase_model->change_delivery_status($data['node']['data']['delivery_status'], $data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_delivery_status';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_delivery_status';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'create_reminder_for_purchase_order':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($purchase_order->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_reminder';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    if(!isset($data['node']['data']['reminder_time']) || $data['node']['data']['reminder_time'] == '' || !isset($data['node']['data']['reminder_time_type']) || $data['node']['data']['reminder_time_type'] == ''){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_reminder';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['reminder_to'])){
                        $data['node']['data']['reminder_to'] = $purchase_order->addedfrom;
                    }

                    if(is_numeric($data['node']['data']['reminder_to']) && $data['node']['data']['reminder_to'] > 0){
                        $this->load->model('misc_model');

                        $rmd_data = [];
                        $rmd_data['notify_by_email'] = 1;

                        $date = strtotime(date('Y-m-d H:i:s'));
                        if($data['node']['data']['reminder_time_type'] == 'min'){
                            $date = strtotime('+'.$data['node']['data']['reminder_time'].' minutes', $date);
                        }else if($data['node']['data']['reminder_time_type'] == 'hours'){
                            $date = strtotime('+'.$data['node']['data']['reminder_time'].' hours', $date);
                        }else if ($data['node']['data']['reminder_time_type'] == 'days') {
                            $date = strtotime('+'.$data['node']['data']['reminder_time'].' day', $date);
                        }else if ($data['node']['data']['reminder_time_type'] == 'months') {
                            $date = strtotime('+'.$data['node']['data']['reminder_time'].' months', $date);
                        }


                        $rmd_data['date'] = date('Y-m-d H:i:s', $date);

                        $rmd_data['staff'] = $data['node']['data']['reminder_to'];
                        $rmd_data['creator'] = get_staff_user_id();
                        $rmd_data['description'] = $data['node']['data']['reminder_description'];
                        $rmd_data['rel_id'] = $data['rel_id'];
                        $rmd_data['rel_type'] = 'purchase_order';

                        $success = $this->misc_model->add_reminder($rmd_data, $data['rel_id']);
                        if($success){

                            $log_data['action_relsult'] = 'fail';
                            $log_data['action_relsult_type'] = 'reminder';
                            $log_data['action_relsult_id'] = $success;

                            $this->save_action_log($log_data);

                            return true;
                        }

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_reminder';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;

                    }

                    return false;
                break;

                case 'create_task':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_order->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    $this->load->model('tasks_model');

                    if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    
                    $task_template = $this->get_task_template($data['node']['data']['task_template']);
                    if(!isset($task_template->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $task_data = [];

                    $task_data['name'] = $task_template->task_subject;
                    $task_data['hourly_rate'] = '';
                    $task_data['startdate'] = $task_template->start_date;
                    $task_data['duedate'] = $task_template->due_date;
                    $task_data['priority'] = $task_template->priority;
                    $task_data['rel_type'] = 'pur_order';
                    $task_data['rel_id'] = $purchase_order->id;
                    $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                    $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                    $task_data['created_by_workflow'] = 1;

                    $task_id = $this->tasks_model->add($task_data);
                    if($task_id){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'task';
                        $log_data['action_relsult_id'] = $task_id;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'send_email':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    $content = $data['node']['data']['send_email_content'];
                    $send_to = $data['node']['data']['send_email_to'];

                    $sent = $this->sent_email_action($content, $send_to);

                    if($sent){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'send_email';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'send_email';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_purchase_request_action_node description]
     * @return [type] [description]
     */
    public function handle_purchase_request_action_node($data){
        if(wa_get_status_modules('purchase')){

            $this->load->model('purchase/purchase_model');

            $purchase_request = $this->purchase_model->get_purchase_request($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'delete_purchase_request':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_request->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_purchase_request';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return false;
                    }

                    $success = $this->purchase_model->delete_pur_request($data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_purchase_request';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_purchase_request';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;
                break;

                case 'update_purchase_request_field':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_request->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_purchase_request_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    if($data['node']['data']['purchase_request_field'] == 'requester'){
                        if(!isset($data['node']['data']['requester'])){
                            $data['node']['data']['requester'] = $purchase_request->requester;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_request', ['requester' => $data['node']['data']['requester'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_request_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_request_field'] == 'description'){
                        if(!isset($data['node']['data']['description'])){
                            $data['node']['data']['description'] = $purchase_request->rq_description;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_request', ['rq_description' => $data['node']['data']['description'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_request_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_request_field'] == 'pur_rq_name'){
                        if(!isset($data['node']['data']['pur_rq_name'])){
                            $data['node']['data']['pur_rq_name'] = $purchase_request->pur_rq_name;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_request', ['pur_rq_name' => $data['node']['data']['pur_rq_name'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_request_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_request_field'] == 'type'){
                        if(!isset($data['node']['data']['type'])){
                            $data['node']['data']['type'] = '';
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_request', ['type' => $data['node']['data']['type'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_request_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_request_field'] == 'project'){
                        if(!isset($data['node']['data']['project'])){
                            $data['node']['data']['project'] = $purchase_request->project;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_request', ['project' => $data['node']['data']['project'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_request_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_purchase_request_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_approval_status':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_request->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_approval_status_purchase_request';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['approval_status'])){
                        $data['node']['data']['approval_status'] = $purchase_request->status;
                    }
                    $success = $this->purchase_model->change_pr_approve_status($data['node']['data']['approval_status'], $data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_approval_status_purchase_request';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_approval_status_purchase_request';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'send_email':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    $content = $data['node']['data']['send_email_content'];
                    $send_to = $data['node']['data']['send_email_to'];

                    $sent = $this->sent_email_action($content, $send_to);

                    if($sent){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'send_email';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'send_email';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;
                break;


                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_purchase_quotation_action_node description]
     * @return [type] [description]
     */
    public function handle_purchase_quotation_action_node($data){
        if(wa_get_status_modules('purchase')){

            $this->load->model('purchase/purchase_model');

            $purchase_quotation = $this->purchase_model->get_estimate($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_purchase_quotation':
                    if(!isset($purchase_quotation->id)){
                        return false;
                    }

                    $success = $this->purchase_model->delete_estimate($data['rel_id']);
                    if($success){
                        return true;
                    }
                    return false;
                break;

                case 'update_purchase_quotation_field':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_quotation->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_purchase_quotation_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return false;
                    }
                    if($data['node']['data']['purchase_quotation_field'] == 'buyer'){
                        if(!isset($data['node']['data']['buyer'])){
                            $data['node']['data']['buyer'] = $purchase_quotation->buyer;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_estimates', ['buyer' => $data['node']['data']['buyer'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_quotation_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_quotation_field'] == 'vendor'){
                        if(!isset($data['node']['data']['vendor'])){
                            $data['node']['data']['vendor'] = $purchase_quotation->vendor;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_estimates', ['vendor' => $data['node']['data']['vendor'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_quotation_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_purchase_quotation_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_approval_status':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_quotation->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_approval_status_purchase_quotation';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['approval_status'])){
                        $data['node']['data']['approval_status'] = $purchase_quotation->status;
                    }

                    $success = $this->purchase_model->change_status_pur_estimate($data['node']['data']['approval_status'], $data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_approval_status_purchase_quotation';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_approval_status_purchase_quotation';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'send_email':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    $content = $data['node']['data']['send_email_content'];
                    $send_to = $data['node']['data']['send_email_to'];

                    $sent = $this->sent_email_action($content, $send_to);

                    if($sent){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'send_email';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'send_email';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_purchase_invoice_action_node description]
     * @return [type] [description]
     */
    public function handle_purchase_invoice_action_node($data){
        if(wa_get_status_modules('purchase')){

            $this->load->model('purchase/purchase_model');

            $purchase_invoice = $this->purchase_model->get_pur_invoice($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'delete_purchase_invoice':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_invoice->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_purchase_invoice';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->purchase_model->delete_pur_invoice($data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_purchase_invoice';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_purchase_invoice';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return false;

                break;

                case 'update_purchase_invoice_field':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_invoice->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_purchase_invoice_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    if($data['node']['data']['purchase_invoice_field'] == 'vendor_invoice_number'){
                        if(!isset($data['node']['data']['vendor_invoice_number'])){
                            $data['node']['data']['vendor_invoice_number'] = $purchase_invoice->vendor_invoice_number;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_invoices', ['vendor_invoice_number' => $data['node']['data']['vendor_invoice_number'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_invoice_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_invoice_field'] == 'vendor'){
                        if(!isset($data['node']['data']['vendor'])){
                            $data['node']['data']['vendor'] = $purchase_invoice->vendor;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_invoices', ['vendor' => $data['node']['data']['vendor'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_invoice_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_invoice_field'] == 'transactionid'){
                        if(!isset($data['node']['data']['transactionid'])){
                            $data['node']['data']['transactionid'] = $purchase_invoice->transactionid;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_invoices', ['transactionid' => $data['node']['data']['transactionid'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_invoice_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_invoice_field'] == 'vendornote'){
                        if(!isset($data['node']['data']['vendornote'])){
                            $data['node']['data']['vendornote'] = $purchase_invoice->vendor_note;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_invoices', ['vendor_note' => $data['node']['data']['vendornote'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_invoice_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_invoice_field'] == 'adminnote'){

                        if(!isset($data['node']['data']['adminnote'])){
                            $data['node']['data']['adminnote'] = $purchase_invoice->adminnote;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_invoices', ['adminnote' => $data['node']['data']['adminnote'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_invoice_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_invoice_field'] == 'terms'){
                        if(!isset($data['node']['data']['terms'])){
                            $data['node']['data']['terms'] = $purchase_invoice->terms;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_invoices', ['terms' => $data['node']['data']['terms'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_invoice_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_purchase_invoice_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                        

                    return false;
                break;

                case 'change_approval_status':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($purchase_invoice->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_approval_status_purchase_invoice';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['approval_status'])){
                        $data['node']['data']['approval_status'] = $purchase_invoice->status;
                    }

                    $success = $this->purchase_model->change_status_pur_invoice($data['node']['data']['approval_status'], $data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_approval_status_purchase_invoice';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_approval_status_purchase_invoice';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'create_reminder_for_purchase_invoice':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_invoice->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_reminder';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    if(!isset($data['node']['data']['reminder_time']) || $data['node']['data']['reminder_time'] == '' || !isset($data['node']['data']['reminder_time_type']) || $data['node']['data']['reminder_time_type'] == ''){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_reminder';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['reminder_to'])){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_reminder';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(is_numeric($data['node']['data']['reminder_to']) && $data['node']['data']['reminder_to'] > 0){
                        $this->load->model('misc_model');

                        $rmd_data = [];
                        $rmd_data['notify_by_email'] = 1;

                        $date = strtotime(date('Y-m-d H:i:s'));
                        if($data['node']['data']['reminder_time_type'] == 'min'){
                            $date = strtotime('+'.$data['node']['data']['reminder_time'].' minutes', $date);
                        }else if($data['node']['data']['reminder_time_type'] == 'hours'){
                            $date = strtotime('+'.$data['node']['data']['reminder_time'].' hours', $date);
                        }else if ($data['node']['data']['reminder_time_type'] == 'days') {
                            $date = strtotime('+'.$data['node']['data']['reminder_time'].' day', $date);
                        }else if ($data['node']['data']['reminder_time_type'] == 'months') {
                            $date = strtotime('+'.$data['node']['data']['reminder_time'].' months', $date);
                        }


                        $rmd_data['date'] = date('Y-m-d H:i:s', $date);

                        $rmd_data['staff'] = $data['node']['data']['reminder_to'];
                        $rmd_data['creator'] = get_staff_user_id();
                        $rmd_data['description'] = $data['node']['data']['reminder_description'];
                        $rmd_data['rel_id'] = $data['rel_id'];
                        $rmd_data['rel_type'] = 'pur_invoice';

                        $success = $this->misc_model->add_reminder($rmd_data, $data['rel_id']);
                        if($success){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'reminder';
                            $log_data['action_relsult_id'] = $success;

                            $this->save_action_log($log_data);

                            return true;
                        }

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_reminder';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;

                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'create_task':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($purchase_invoice->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    $this->load->model('tasks_model');

                    if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    
                    $task_template = $this->get_task_template($data['node']['data']['task_template']);
                    if(!isset($task_template->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $task_data = [];

                    $task_data['name'] = $task_template->task_subject;
                    $task_data['hourly_rate'] = '';
                    $task_data['startdate'] = $task_template->start_date;
                    $task_data['duedate'] = $task_template->due_date;
                    $task_data['priority'] = $task_template->priority;
                    $task_data['rel_type'] = 'pur_invoice';
                    $task_data['rel_id'] = $purchase_invoice->id;
                    $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                    $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                    $task_data['created_by_workflow'] = 1;

                    $task_id = $this->tasks_model->add($task_data);
                    if($task_id){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'task';
                        $log_data['action_relsult_id'] = $task_id;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'add_note':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_invoice->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'add_note';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    if(!isset($data['node']['data']['note'])){
                        $data['node']['data']['note'] = '';
                    }

                    $this->load->model('misc_model');
                    $note_data = [];
                    $note_data['description'] = $data['node']['data']['note'];

                    $note_id = $this->misc_model->add_note($note_data, 'pur_invoice', $purchase_invoice->id);
                    if($note_id){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'note';
                        $log_data['action_relsult_id'] = $note_id;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'add_note';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_purchase_contract_action_node description]
     * @return [type] [description]
     */
    public function handle_purchase_contract_action_node($data){
        if(wa_get_status_modules('purchase')){

            $this->load->model('purchase/purchase_model');

            $purchase_contract = $this->purchase_model->get_contract($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'update_purchase_contract_field':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_contract->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_purchase_contract_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return false;
                    }
                    if($data['node']['data']['purchase_contract_field'] == 'service_category'){
                        if(!isset($data['node']['data']['service_category'])){
                            $data['node']['data']['service_category'] = $purchase_contract->service_category;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_contracts', ['service_category' => $data['node']['data']['service_category'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_contract_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_contract_field'] == 'vendor'){
                        if(!isset($data['node']['data']['vendor'])){
                            $data['node']['data']['vendor'] = $purchase_contract->vendor;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_contracts', ['vendor' => $data['node']['data']['vendor'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_contract_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_contract_field'] == 'contract_value'){
                        if(!isset($data['node']['data']['contract_value'])){
                            $data['node']['data']['contract_value'] = $purchase_contract->contract_value;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_contracts', ['contract_value' => $data['node']['data']['contract_value'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_contract_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['purchase_contract_field'] == 'department'){
                        if(!isset($data['node']['data']['department'])){
                            $data['node']['data']['department'] = $purchase_contract->department;
                        }

                        $this->db->where('id', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_contracts', ['department' => $data['node']['data']['department'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_purchase_contract_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_purchase_contract_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'delete_purchase_contract';

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_contract->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_purchase_contract';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->purchase_model->delete_contract($data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_purchase_contract';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_purchase_contract';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'create_reminder_for_purchase_contract':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_contract->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_reminder';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    if(!isset($data['node']['data']['reminder_time']) || $data['node']['data']['reminder_time'] == '' || !isset($data['node']['data']['reminder_time_type']) || $data['node']['data']['reminder_time_type'] == ''){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_reminder';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['reminder_to'])){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_reminder';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(is_numeric($data['node']['data']['reminder_to']) && $data['node']['data']['reminder_to'] > 0){
                        $this->load->model('misc_model');

                        $rmd_data = [];
                        $rmd_data['notify_by_email'] = 1;

                        $date = strtotime(date('Y-m-d H:i:s'));
                        if($data['node']['data']['reminder_time_type'] == 'min'){
                            $date = strtotime('+'.$data['node']['data']['reminder_time'].' minutes', $date);
                        }else if($data['node']['data']['reminder_time_type'] == 'hours'){
                            $date = strtotime('+'.$data['node']['data']['reminder_time'].' hours', $date);
                        }else if ($data['node']['data']['reminder_time_type'] == 'days') {
                            $date = strtotime('+'.$data['node']['data']['reminder_time'].' day', $date);
                        }else if ($data['node']['data']['reminder_time_type'] == 'months') {
                            $date = strtotime('+'.$data['node']['data']['reminder_time'].' months', $date);
                        }


                        $rmd_data['date'] = date('Y-m-d H:i:s', $date);

                        $rmd_data['staff'] = $data['node']['data']['reminder_to'];
                        $rmd_data['creator'] = get_staff_user_id();
                        $rmd_data['description'] = $data['node']['data']['reminder_description'];
                        $rmd_data['rel_id'] = $data['rel_id'];
                        $rmd_data['rel_type'] = 'pur_contract';

                        $success = $this->misc_model->add_reminder($rmd_data, $data['rel_id']);
                        if($success){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'reminder';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_reminder';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;

                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'create_task':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_contract->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    $this->load->model('tasks_model');

                    if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    
                    $task_template = $this->get_task_template($data['node']['data']['task_template']);
                    if(!isset($task_template->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $task_data = [];

                    $task_data['name'] = $task_template->task_subject;
                    $task_data['hourly_rate'] = '';
                    $task_data['startdate'] = $task_template->start_date;
                    $task_data['duedate'] = $task_template->due_date;
                    $task_data['priority'] = $task_template->priority;
                    $task_data['rel_type'] = 'pur_contract';
                    $task_data['rel_id'] = $purchase_contract->id;
                    $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                    $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                    $task_data['created_by_workflow'] = 1;

                    $task_id = $this->tasks_model->add($task_data);
                    if($task_id){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'create_task';
                        $log_data['action_relsult_id'] = $task_id;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'send_email':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    $content = $data['node']['data']['send_email_content'];
                    $send_to = $data['node']['data']['send_email_to'];

                    $sent = $this->sent_email_action($content, $send_to);

                    if($sent){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'send_email';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'send_email';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'add_note':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($purchase_contract->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'add_note';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return false;
                    }
                    if(!isset($data['node']['data']['note'])){
                        $data['node']['data']['note'] = '';
                    }

                    $this->load->model('misc_model');
                    $note_data = [];
                    $note_data['description'] = $data['node']['data']['note'];

                    $note_id = $this->misc_model->add_note($note_data, 'pur_contract', $purchase_contract->id);
                    if($note_id){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'note';
                        $log_data['action_relsult_id'] = $note_id;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'add_note';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_purchase_vendor_action_node description]
     * @return [type] [description]
     */
    public function handle_purchase_vendor_action_node($data){
        if(wa_get_status_modules('purchase')){

            $this->load->model('purchase/purchase_model');

            $vendor = $this->purchase_model->get_vendor($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'update_vendor_field':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($vendor->userid)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_vendor_field';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    if($data['node']['data']['vendor_field'] == 'company'){
                        if(!isset($data['node']['data']['company'])){
                            $data['node']['data']['company'] = $vendor->company;
                        }

                        $this->db->where('userid', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_vendor', ['company' => $data['node']['data']['company'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_vendor_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['vendor_field'] == 'vat'){
                        if(!isset($data['node']['data']['vat'])){
                            $data['node']['data']['vat'] = $vendor->vat;
                        }

                        $this->db->where('userid', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_vendor', ['vat' => $data['node']['data']['vat'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_vendor_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['vendor_field'] == 'phone'){
                        if(!isset($data['node']['data']['phone'])){
                            $data['node']['data']['phone'] = $vendor->phonenumber;
                        }

                        $this->db->where('userid', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_vendor', ['phonenumber' => $data['node']['data']['phone'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_vendor_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['vendor_field'] == 'website'){
                        if(!isset($data['node']['data']['website'])){
                            $data['node']['data']['website'] = $vendor->website;
                        }

                        $this->db->where('userid', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_vendor', ['website' => $data['node']['data']['website'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_vendor_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['vendor_field'] == 'address'){
                        if(!isset($data['node']['data']['address'])){
                            $data['node']['data']['address'] = $vendor->address;
                        }

                        $this->db->where('userid', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_vendor', ['address' => $data['node']['data']['address'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_vendor_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['vendor_field'] == 'city'){
                        if(!isset($data['node']['data']['city'])){
                            $data['node']['data']['city'] = $vendor->city;
                        }

                        $this->db->where('userid', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_vendor', ['city' => $data['node']['data']['city'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_vendor_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['vendor_field'] == 'state'){
                        if(!isset($data['node']['data']['state'])){
                            $data['node']['data']['state'] = $vendor->state;
                        }

                        $this->db->where('userid', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_vendor', ['state' => $data['node']['data']['state'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_vendor_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['vendor_field'] == 'country'){
                        if(!isset($data['node']['data']['country'])){
                            $data['node']['data']['country'] = $vendor->country;
                        }

                        $this->db->where('userid', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_vendor', ['country' => $data['node']['data']['country'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_vendor_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }else if($data['node']['data']['vendor_field'] == 'zip'){
                        if(!isset($data['node']['data']['zip'])){
                            $data['node']['data']['zip'] = $vendor->zip;
                        }

                        $this->db->where('userid', $data['rel_id']);
                        $this->db->update(db_prefix().'pur_vendor', ['zip' => $data['node']['data']['zip'] ]);
                        if($this->db->affected_rows() > 0){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'update_vendor_field';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }



                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_vendor_field';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'delete_vendor':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($vendor->userid)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_vendor';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return false;
                    }

                    $success = $this->purchase_model->delete_vendor($data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_vendor';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_vendor';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'send_email':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    $content = $data['node']['data']['send_email_content'];
                    $send_to = $data['node']['data']['send_email_to'];

                    $sent = $this->sent_email_action($content, $send_to);

                    if($sent){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'send_email';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'send_email';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'add_note':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($vendor->userid)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'add_note';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    if(!isset($data['node']['data']['note'])){
                        $data['node']['data']['note'] = '';
                    }

                    $this->load->model('misc_model');
                    $note_data = [];
                    $note_data['description'] = $data['node']['data']['note'];

                    $note_id = $this->misc_model->add_note($note_data, 'pur_vendor', $vendor->userid);
                    if($note_id){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'note';
                        $log_data['action_relsult_id'] = $note_id;

                        $this->save_action_log($log_data);


                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'add_note';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'assign_to_staff':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($vendor->userid)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'assign_to_staff';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return false;
                    }
                    if(isset($data['node']['data']['staff']) && is_numeric($data['node']['data']['staff']) && $data['node']['data']['staff'] > 0){
                        $admin_data = [];

                        $admin_data['customer_admins'][] = $data['node']['data']['staff'];

                        $success = $this->purchase_model->assign_vendor_admins($admin_data, $data['rel_id']);
                        if($success){

                            $log_data['action_relsult'] = 'success';
                            $log_data['action_relsult_type'] = 'assign_to_staff';
                            $log_data['action_relsult_id'] = 0;

                            $this->save_action_log($log_data);

                            return true;
                        }
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'assign_to_staff';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                return false;
                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_cs_ticket_action_node description]
     * @return [type] [description]
     */
    public function handle_cs_ticket_action_node($data){
        if(wa_get_status_modules('customer_service')){

            $this->load->model('customer_service/customer_service_model');

            $ticket = $this->customer_service_model->get_ticket($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'change_priority':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($ticket->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_priority_ticket';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['priority'])){
                        $data['node']['data']['priority'] = $ticket->priority_level;
                    }

                    $success = $this->customer_service_model->customer_service_status_mark_as($data['node']['data']['priority'], $data['rel_id'], 'priority');

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_priority_ticket';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_priority_ticket';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'change_status':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($ticket->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_status_ticket';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['status'])){
                        $data['node']['data']['status'] = $ticket->status;
                    }

                    $success = $this->customer_service_model->customer_service_status_mark_as($data['node']['data']['status'], $data['rel_id'], 'ticket_status');

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_status_ticket';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_ticket';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_type':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($ticket->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_type_ticket';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['type'])){
                        $data['node']['data']['type'] = $ticket->ticket_type;
                    }

                    $success = $this->customer_service_model->customer_service_status_mark_as($data['node']['data']['type'], $data['rel_id'], 'ticket_type');

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_type_ticket';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_type_ticket';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'assign_to_staff':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($ticket->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'assign_to_staff';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['staff'])){
                        $data['node']['data']['staff'] = $ticket->assigned_id;
                    }

                    $this->db->where('id', $ticket->id);
                    $this->db->update(db_prefix().'cs_tickets', [
                        'assigned_id' => $data['node']['data']['staff'],
                    ]);

                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'assign_to_staff';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;

                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'assign_to_staff';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;


                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_staff_action_node description]
     * @return [type] [description]
     */
    public function handle_staff_action_node($data){
        if(wa_get_status_modules('hr_profile')){

            $this->load->model('hr_profile/hr_profile_model');

            $staff = $this->hr_profile_model->get_staff($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($staff->staffid)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_staff';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return false;
                    }

                    $success = $this->hr_profile_model->delete_staff($data['rel_id'], get_staff_user_id());
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_staff';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_staff';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'delete_contract':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($staff->staffid)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_contract';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);
                        return false;
                    }

                    $this->db->where('staff', $data['rel_id']);
                    $this->db->delete(db_prefix().'hr_staff_contract');
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_contract';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_contract';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;


                break;

                case 'change_status_contract':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($staff->staffid)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_status_contract';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return false;
                    }

                    if(!isset($data['node']['data']['contract_status'])){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_status_contract';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return false;
                    }
                    $this->db->where('staff', $data['rel_id']);
                    $this->db->update(db_prefix().'hr_staff_contract', ['contract_status' => $data['node']['data']['contract_status'] ]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_status_contract';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_contract';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;

                break;

                case 'change_status_staff':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($staff->staffid)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_status_staff';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['staff_status'])){
                        $data['node']['data']['staff_status'] = $staff->status_work;
                    }

                    $this->db->where('staffid', $data['rel_id']);
                    $this->db->update(db_prefix().'staff', ['status_work' => $data['node']['data']['staff_status']]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'true';
                        $log_data['action_relsult_type'] = 'change_status_staff';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_staff';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'approve_depandant':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($staff->staffid)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'approve_depandant';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $this->db->where('staffid', $data['rel_id']);
                    $this->db->update(db_prefix().'hr_dependent_person', ['status' => 1]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'approve_depandant';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'approve_depandant';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'reject_depandant':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($staff->staffid)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'reject_depandant';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $this->db->where('staffid', $data['rel_id']);
                    $this->db->update(db_prefix().'hr_dependent_person', ['status' => 2]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'reject_depandant';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'reject_depandant';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'delete_depandant':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($staff->staffid)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_depandant';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $this->db->where('staffid', $data['rel_id']);
                    $this->db->delete(db_prefix().'hr_dependent_person');
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_depandant';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_depandant';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;

                break;

                case 'delete_layoff_checklist':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($staff->staffid)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_layoff_checklist';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->hr_profile_model->delete_procedures_for_quitting_work($data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_layoff_checklist';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_layoff_checklist';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'approve_layoff_checklist':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($staff->staffid)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'approve_layoff_checklist';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $this->db->where('staffid', $data['rel_id']);
                    $this->db->update(db_prefix().'hr_list_staff_quitting_work', ['approval' => 'approved']);
                    if($this->db->affected_rows()){

                        $this->db->where('staffid',$data['rel_id']);
                        $this->db->update(db_prefix().'staff', [
                            'active' => 0,
                            'status_work' => 'inactivity'
                        ]);

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'approve_layoff_checklist';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'approve_layoff_checklist';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);


                    return false;


                break;

                case 'unactive_staff':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');
       

                    if(!isset($staff->staffid)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'unactive_staff';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $this->db->where('staffid',$data['rel_id']);
                    $this->db->update(db_prefix().'staff', [
                        'active' => 0,
                        'status_work' => 'inactivity'
                    ]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'unactive_staff';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'unactive_staff';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_expense_action_node description]
     * @return [type] [description]
     */
    public function handle_expense_action_node($data){
        $this->load->model('expenses_model');

        $expense = $this->expenses_model->get($data['rel_id']);

        switch ($data['node']['data']['action']) {

            case 'copy_expense':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');


                if(!isset($expense->id)){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'expense';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $success = $this->expenses_model->copy($data['rel_id']);

                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'expense';
                    $log_data['action_relsult_id'] = $success;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'expense';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'create_task':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');
                

                if(!isset($expense->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                $this->load->model('tasks_model');

                if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                
                $task_template = $this->get_task_template($data['node']['data']['task_template']);
                if(!isset($task_template->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $task_data = [];

                $task_data['name'] = $task_template->task_subject;
                $task_data['hourly_rate'] = '';
                $task_data['startdate'] = $task_template->start_date;
                $task_data['duedate'] = $task_template->due_date;
                $task_data['priority'] = $task_template->priority;
                $task_data['rel_type'] = 'expense';
                $task_data['rel_id'] = $expense->id;
                $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                $task_data['created_by_workflow'] = 1;

                $task_id = $this->tasks_model->add($task_data);
                if($task_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = $task_id;

                    $this->save_action_log($log_data);

                    return true;
                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'task';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'create_reminder_for_expense':
                if(!isset($expense->id)){
                    return false;
                }
                if(!isset($data['node']['data']['reminder_time']) || $data['node']['data']['reminder_time'] == '' || !isset($data['node']['data']['reminder_time_type']) || $data['node']['data']['reminder_time_type'] == ''){

                    return false;
                }

                if(!isset($data['node']['data']['reminder_to'])){
                    return false;
                }

                if(is_numeric($data['node']['data']['reminder_to']) && $data['node']['data']['reminder_to'] > 0){
                    $this->load->model('misc_model');

                    $rmd_data = [];
                    $rmd_data['notify_by_email'] = 1;

                    $date = strtotime(date('Y-m-d H:i:s'));
                    if($data['node']['data']['reminder_time_type'] == 'min'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' minutes', $date);
                    }else if($data['node']['data']['reminder_time_type'] == 'hours'){
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' hours', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'days') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' day', $date);
                    }else if ($data['node']['data']['reminder_time_type'] == 'months') {
                        $date = strtotime('+'.$data['node']['data']['reminder_time'].' months', $date);
                    }


                    $rmd_data['date'] = date('Y-m-d H:i:s', $date);

                    $rmd_data['staff'] = $data['node']['data']['reminder_to'];
                    $rmd_data['creator'] = get_staff_user_id();
                    $rmd_data['description'] = $data['node']['data']['reminder_description'];
                    $rmd_data['rel_id'] = $data['rel_id'];
                    $rmd_data['rel_type'] = 'expense';

                    $success = $this->misc_model->add_reminder($rmd_data, $data['rel_id']);

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'create_reminder_for_expense';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_reminder_for_expense';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_reminder_for_expense';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'update_expense_fields':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($expense->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_expense_fields';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                if($data['node']['data']['expense_field'] == 'name'){
                    if(!isset($data['node']['data']['name'])){
                        $data['node']['data']['name'] = $expense->name;
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'expenses', ['expense_name' => $data['node']['data']['name'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_expense_fields';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['expense_field'] == 'amount'){
                    if(!isset($data['node']['data']['amount'])){
                        $data['node']['data']['amount'] = $expense->amount;
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'expenses', ['amount' => $data['node']['data']['amount'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_expense_fields';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }
                }else if($data['node']['data']['expense_field'] == 'clientid'){
                    if(!isset($data['node']['data']['clientid'])){
                        $data['node']['data']['clientid'] = $expense->clientid;
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'expenses', ['clientid' => $data['node']['data']['clientid'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_expense_fields';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return true;
                    }
                }else if($data['node']['data']['expense_field'] == 'note'){
                    if(!isset($data['node']['data']['note'])){
                        $data['node']['data']['note'] = $expense->note;
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'expenses', ['note' => $data['node']['data']['note'] ]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_expense_fields';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return true;
                    }
                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'update_expense_fields';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'delete_expense':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($expense->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_expense';
                    $log_data['action_relsult_id'] = 0;
                    return false;
                }

                $success = $this->expenses_model->delete($data['rel_id'], true);

                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'delete_expense';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'delete_expense';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;
            break;

            case 'convert_to_invoice':
                if(!isset($expense->id)){
                    return false;
                }

                if($expense->billable != 1){

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'expenses', ['billable' => 1]);
                }

                $success = $this->expenses_model->convert_to_invoice($data['rel_id'], true);

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'invoice';
                    $log_data['action_relsult_id'] = $success;

                    $this->save_action_log($log_data);

                    return true;
                }


                $log_data['action_relsult'] = 'success';
                $log_data['action_relsult_type'] = 'invoice';
                $log_data['action_relsult_id'] = '';

                $this->save_action_log($log_data);

                return false;
            break;

            default:
                return false;
            break;
        }
    }

    /**
     * [handle_recruitment_plan_action_node description]
     * @return [type] [description]
     */
    public function handle_recruitment_plan_action_node($data){
        if(wa_get_status_modules('recruitment')){

            $this->load->model('recruitment/recruitment_model');

            $plan = $this->recruitment_model->get_rec_proposal($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete':
                    if(!isset($plan->id)){
                        return false;
                    }

                    $success = $this->recruitment_model->delete_recruitment_proposal($data['rel_id']);

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_plan';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_plan';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'approve_plan':

                    if(!isset($plan->id)){
                        return false;
                    }

                    $success = $this->recruitment_model->approve_reject_proposal('approved', $data['rel_id']);

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'approve_plan';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'approve_plan';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            }

        }
    }

    /**
     * [handle_recruitment_campaign_action_node description]
     * @return [type] [description]
     */
    public function handle_recruitment_campaign_action_node($data){
        if(wa_get_status_modules('recruitment')){
            $this->load->model('recruitment/recruitment_model');

            $campaign = $this->recruitment_model->get_rec_campaign($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'change_campaign_status':
                    if(!isset($campaign->cp_id)){
                        return false;
                    }

                    $success = $this->recruitment_model->change_status_campaign($data['node']['data']['status'], $data['rel_id']);

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_campaign_status';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_campaign_status';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
 
                    return false;


                break;

                case 'delete':
                    if(!isset($campaign->cp_id)){
                        return false;
                    }

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    $success = $this->recruitment_model->delete_recruitment_campaign($data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            }

        }
    }

    /**
     * [handle_recruitment_form_action_node description]
     * @return [type] [description]
     */
    public function handle_recruitment_form_action_node($data){
        if(wa_get_status_modules('recruitment')){
            $this->load->model('recruitment/recruitment_model');

            $form = $this->recruitment_model->get_recruitment_channel($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'duplicate_form':

                    if(!isset($form->id)){
                        return false;
                    }

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    $success = $this->recruitment_model->duplicate_recruitment_channel($data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'recruitment_form';
                        $log_data['action_relsult_id'] = $success;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'recruitment_form';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_candidate_action_node description]
     * @return [type] [description]
     */
    public function handle_candidate_action_node($data){
        if(wa_get_status_modules('recruitment')){
            $this->load->model('recruitment/recruitment_model');

            $candidate = $this->recruitment_model->get_candidates($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete':
                    if(!isset($candidate->id)){
                        return false;
                    }

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    $success = $this->recruitment_model->delete_candidate($data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_candidate';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_candidate';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'send_email':
                    $content = $data['node']['data']['send_email_content'];
                    $send_to = $data['node']['data']['send_email_to'];

                    $sent = $this->sent_email_action($content, $send_to);

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if($sent){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'send_mail';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'send_mail';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_candidate_status':
                    if(!isset($candidate->id)){
                        return false;
                    }

                    if(!isset($data['node']['data']['candidate_status'])){
                        $data['node']['data']['candidate_status'] = $candidate->status;
                    }

                    $success = $this->recruitment_model->change_status_candidate($data['node']['data']['candidate_status'], $data['rel_id']);

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_candidate_status';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_candidate_status';
                    $log_data['action_relsult_id'] = 0;


                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'transfer_hr_records':
                    if(!isset($candidate->id)){
                        return false;
                    }

                    $data_staff = [];

                    $prefix_str = 'EC';
                    $next_number = (int)$this->recruitment_model->get_last_staff_id();
                    $staff_code = $prefix_str.str_pad($next_number,5,'0',STR_PAD_LEFT);

                    $data_staff['staff_identifi'] = $staff_code;
                    $data_staff['firstname'] = $candidate->candidate_name;
                    $data_staff['lastname'] = $candidate->last_name;
                    $data_staff['email'] = $candidate->email;
                    $data_staff['phonenumber'] = $candidate->phonenumber;
                 
                    $position_id = '';
                    if(is_numeric($candidate->rec_campaign)){
                        $position_id = $this->recruitment_model->check_job_position_exist_hr_records($candidate->rec_campaign);
                    }
                    
                    $data_staff['job_position'] = $position_id;
                    $data_staff['birthday'] = $candidate->birthday;
                    $data_staff['facebook'] = $candidate->facebook;
                    $data_staff['skype'] = $candidate->skype;
                    $data_staff['birthplace'] = $candidate->birthplace;
                    $data_staff['home_town'] = $candidate->home_town;
                    $data_staff['marital_status'] = $candidate->marital_status;
                    $data_staff['nation'] = $candidate->nation;
                    $data_staff['religion'] = $candidate->religion;
                    $data_staff['identification'] = $candidate->identification;
                    $data_staff['days_for_identity'] = $candidate->days_for_identity;
                    $data_staff['place_of_issue'] = $candidate->place_of_issue;
                    $data_staff['resident'] = $candidate->resident;
                    $data_staff['current_address'] = $candidate->current_accommodation;
                    $data_staff['literacy'] = '';
                    $data_staff['password'] = '123456a@';
                    $data_staff['permissions'] = [];

                    $staff_id = $this->recruitment_model->rec_add_staff($data_staff);

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if($staff_id){
                        
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'staff';
                        $log_data['action_relsult_id'] = $staff_id;

                        $this->save_action_log($log_data);

                        $change = $this->recruitment_model->change_status_candidate(9, $candidate->id);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'staff';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;


                break;

                default:
                    return false;
                break;
            }
        }
    }

        /**
     * [handle_interview_schedule_action_node description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function handle_interview_schedule_action_node($data){
        if(wa_get_status_modules('recruitment')){
            $this->load->model('recruitment/recruitment_model');

            $interview_schedule = $this->recruitment_model->get_interview_schedule($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'delete':
                    if(!isset($interview_schedule->id)){
                        return false;
                    }

                    $success = $this->recruitment_model->delete_interview_schedule($data['rel_id']);

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if($success){

                        
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);


                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;


                break;

                case 'send_email':
                    $content = $data['node']['data']['send_email_content'];
                    $send_to = $data['node']['data']['send_email_to'];

                    $sent = $this->sent_email_action($content, $send_to);

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if($sent){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'send_mail';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'send_mail';
                    $log_data['action_relsult_id'] = 0;
                    this->save_action_log($log_data);
                    return false;
                    
                break;

                default:
                    return false;
                break;
            }
        }
    }


      /**
     * [handle_leave_request_action_node description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function handle_leave_request_action_node($data){
        if(wa_get_status_modules('timesheets')){
            $this->load->model('timesheets/timesheets_model');

            $leave_request = $this->timesheets_model->get_request_leave($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'change_approval_status':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    $status = 0;

                    if(!isset($leave_request->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_approval_status_leave_request';
                        $log_data['action_relsult_id'] = 0;
                        this->save_action_log($log_data);
                        return false;
                    }

                    $status = $leave_request->status;
                    if(isset($data['node']['data']['approval_status']) && $data['node']['data']['approval_status'] != ''){
                        $status = $data['node']['data']['approval_status'];
                    }


                    $rel_type = '';
                    if ($leave_request->rel_type == 1) {
                        switch ($leave_request->type_of_leave) {
                        case 8:
                            $rel_type = 'leave';
                            break;
                        case 2:
                            $rel_type = 'maternity_leave';
                            break;
                        case 4:
                            $rel_type = 'private_work_without_pay';
                            break;
                        case 1:
                            $rel_type = 'sick_leave';
                            break;
                        }
                    } else if ($leave_request->rel_type == 2) {
                        $rel_type = 'late';
                    } else if ($leave_request->rel_type == 3) {
                        $rel_type = 'go_out';
                    } else if ($leave_request->rel_type == 4) {
                        $rel_type = 'go_on_bussiness';
                    } else if ($leave_request->rel_type == 6) {
                        $rel_type = 'early';
                    } else {
                        $rel_type = 'quit_job';
                    }

                    $success = $this->timesheets_model->update_approve_request($data['rel_id'], $rel_type, $status);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_approval_status_leave_request';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_approval_status_leave_request';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'delete_leave_request':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($leave_request->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_leave_request';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $success = $this->timesheets_model->delete_requisition($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_leave_request';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_leave_request';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            }

        }
    }

    /**
     * [handle_additional_work_hours_action_node description]
     * @return [type] [description]
     */
    public function handle_additional_work_hours_action_node($data){
        if(wa_get_status_modules('timesheets')){
            $this->load->model('timesheets/timesheets_model');

            $additional_work_hours = $this->timesheets_model->get_additional_timesheets($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete':  
                     $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($additional_work_hours->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_additional_work_hours';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->timesheets_model->delete_additional_timesheets($data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_additional_work_hours';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_additional_work_hours';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);


                    return false;

                break;

                case 'change_approval_status':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($additional_work_hours->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_additional_work_hours';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $status = $additional_work_hours->status;    
                    if(isset($data['node']['data']['approval_status']) && $data['node']['data']['approval_status'] != ''){
                        $status = $data['node']['data']['approval_status'];
                    }

                    $rel_type = 'additional_timesheets';

                    $success = $this->timesheets_model->update_approve_request($data['rel_id'], $rel_type, $status);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_approval_status_additional_work_hours';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_approval_status_additional_work_hours';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            }

        }
    }

    /**
     * [handle_vehicle_action_node description]
     * @return [type] [description]
     */
    public function handle_vehicle_action_node($data){
        if(wa_get_status_modules('fleet')){
            $this->load->model('fleet/fleet_model');

            $vehicle = $this->fleet_model->get_vehicle($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_vehicle':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($vehicle->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_vehicle';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->fleet_model->delete_vehicle($data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_vehicle';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_vehicle';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                case 'change_status':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($vehicle->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_status_vehicle';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $status = $vehicle->status;
                    if(isset($data['node']['data']['status']) && $data['node']['data']['status'] > 0){
                        $status = $data['node']['data']['status'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fleet_vehicles', ['status' => $status]);
                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_status_vehicle';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_vehicle';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'create_assignment':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($vehicle->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_assignment';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!isset($data['node']['data']['driver']) || !is_numeric($data['node']['data']['driver']) || $data['node']['data']['driver'] == 0){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_assignment';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $driver = $data['node']['data']['driver'];

                    $asm_data = [];
                    $asm_data['vehicle_id'] = $data['rel_id'];
                    $asm_data['driver_id'] = $driver;
                    $asm_data['starting_odometer'] = '';
                    $asm_data['ending_odometer'] = '';

                    $assignment_id = $this->fleet_model->add_vehicle_assignment($asm_data);

                    if($assignment_id){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'vehicle_assignment';
                        $log_data['action_relsult_id'] = $assignment_id;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_assignment';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_workperformance_action_node description]
     * @return [type] [description]
     */
    public function handle_workperformance_action_node($data){
        if(wa_get_status_modules('fleet')){
            $this->load->model('fleet/fleet_model');

            $workperformance = $this->fleet_model->get_logbook($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_workperformance':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($workperformance->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_workperformance';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->fleet_model->delete_logbook($data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_workperformance';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_workperformance';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'change_status':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($workperformance->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_status_workperformance';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }
                    if(!isset($data['node']['data']['status'])){
                        $data['node']['data']['status'] = $workperformance->status;
                    }

                    $success = $this->fleet_model->logbook_change_status($data['node']['data']['status'] ,$data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_status_workperformance';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_workperformance';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                default:
                    return false;
                break;
            }

        }
    }

    /**
     * [handle_event_action_node description]
     * @return [type] [description]
     */
    public function handle_event_action_node($data){
        if(wa_get_status_modules('fleet')){
            $this->load->model('fleet/fleet_model');

            $event = $this->fleet_model->get_event($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'delete_event':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($event->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_event';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->fleet_model->delete_event($data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_event';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_event';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_type':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($event->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_type_event';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $type = $event->event_type;
                    if(isset($data['node']['data']['type'])){
                        $type = $data['node']['data']['type'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fleet_events', ['event_type' => $type]);

                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_type_event';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_type_event';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_driver':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($event->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_driver';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }
                    
                    $driver = $event->driver_id;
                    if(isset($data['node']['data']['driver'])){
                        $driver = $data['node']['data']['driver'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fleet_events', ['driver_id' => $driver]);

                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_driver';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_driver';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                default:
                    return false;
                break;
            }
        }

    }

    /**
     * [handle_work_order_action_node description]
     * @return [type] [description]
     */
    public function handle_work_order_action_node($data){
        if(wa_get_status_modules('fleet')){
            $this->load->model('fleet/fleet_model');

            $work_order = $this->fleet_model->get_work_order($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_work_order':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($work_order->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_work_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->fleet_model->delete_work_order($data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_work_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_work_order';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'change_status':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($work_order->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_status_work_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $status = $work_order->status;
                    if(isset($data['node']['data']['status'])){
                        $status = $data['node']['data']['status'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fleet_work_orders', ['status' => $status]);

                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_status_work_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_work_order';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_vehiche':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($work_order->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_vehiche_work_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $vehicle_id = $work_order->vehicle_id;
                    if(isset($data['node']['data']['vehiche'])){
                        $vehicle_id = $data['node']['data']['vehiche'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fleet_work_orders', ['vehicle_id' => $vehicle_id]);

                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_status_work_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_work_order';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break; 

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_booking_action_node description]
     * @return [type] [description]
     */
    public function handle_booking_action_node($data){
        if(wa_get_status_modules('fleet')){
            $this->load->model('fleet/fleet_model');

            $booking = $this->fleet_model->get_booking($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_booking':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($booking->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_booking';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->fleet_model->delete_booking($data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_booking';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_booking';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                case 'change_status':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($booking->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_status_booking';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $status = $booking->status;
                    if(isset($data['node']['data']['status'])){
                        $status = $data['node']['data']['status'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fleet_bookings', ['status' => $status]);

                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_status_booking';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_booking';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'create_invoice':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($booking->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_invoice';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $invoice_id = $this->fleet_model-> create_invoice_by_booking($data['rel_id']);

                    if($invoice_id){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'invoice';
                        $log_data['action_relsult_id'] = $invoice_id;
                        $this->save_action_log($log_data);


                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_invoice';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_fuel_action_node description]
     * @return [type] [description]
     */
    public function handle_fuel_action_node($data){
        if(wa_get_status_modules('fleet')){
            $this->load->model('fleet/fleet_model');

            $fuel = $this->fleet_model->get_fuel_history($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'delete_fuel':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($fuel->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_fuel';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->fleet_model->delete_fuel_history($data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_fuel';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_fuel';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_type':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($fuel->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_type_fuel';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $fuel_type = $fuel->fuel_type;
                    if(isset($data['node']['data']['type'])){
                        $fuel_type = $data['node']['data']['type'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fleet_fuel_history', ['fuel_type' => $fuel_type]);

                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_type_fuel';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_type_fuel';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_vehiche':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($fuel->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_vehiche';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $vehiche = $fuel->vehicle_id;
                    if(isset($data['node']['data']['vehiche'])){
                        $vehiche = $data['node']['data']['vehiche'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fleet_fuel_history', ['vehicle_id' => $vehiche]);

                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_vehiche';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_vehiche';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'change_vendor':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($fuel->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_vendor';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $vendor = $fuel->vendor_id;
                    if(isset($data['node']['data']['vendor'])){
                        $vendor = $data['node']['data']['vendor'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fleet_fuel_history', ['vendor_id' => $vendor]);

                    if($this->db->affected_rows() > 0){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_vendor';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_vendor';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;


                break;

                default:
                    return false;
                break;
            }

        }
    }

    /**
     * [handle_work_center_action_node description]
     * @return [type] [description]
     */
    public function handle_work_center_action_node($data){
        if(wa_get_status_modules('manufacturing')){
            $this->load->model('manufacturing/manufacturing_model');

            $work_center = $this->manufacturing_model->get_work_centers($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_work_center':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');


                    if(!isset($work_center->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_work_center';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->manufacturing_model->delete_work_center($data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_work_center';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_work_center';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_working_hours':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($work_center->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_working_hours';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $working_hours = $work_center->working_hours;
                    if(isset($data['node']['data']['working_hour'])){
                        $working_hours = $data['node']['data']['working_hour'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'mrp_work_centers', ['working_hours' => $working_hours]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_working_hours';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_working_hours';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'update_cost':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($work_center->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_work_center_cost';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $costs_hour = $work_center->costs_hour;
                    if(isset($data['node']['data']['costs_hour'])){
                        $costs_hour = $data['node']['data']['costs_hour'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'mrp_work_centers', ['costs_hour' => $costs_hour]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_work_center_cost';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_work_center_cost';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;


                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_routing_action_node description]
     * @return [type] [description]
     */
    public function handle_routing_action_node($data){
        if(wa_get_status_modules('manufacturing')){
            $this->load->model('manufacturing/manufacturing_model');

            $routing = $this->manufacturing_model->get_routings($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'delete_routing':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($routing->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_routing';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->manufacturing_model->delete_routing($data['rel_id']);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_routing';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_routing';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                case 'update_name':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($routing->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_routing_name';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }


                    $routing_name = $routing->routing_name;
                    if(isset($data['node']['data']['routing_name'])){
                        $routing_name = $data['node']['data']['routing_name'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'mrp_routings', ['routing_name' => $routing_name]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_routing_name';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_routing_name';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'update_note':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($routing->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_routing_note';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }


                    $note = $routing->description;
                    if(isset($data['node']['data']['note'])){
                        $note = $data['node']['data']['note'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'mrp_routings', ['description' => $note]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_routing_note';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_routing_note';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_operation_action_node description]
     * @return [type] [description]
     */
    public function handle_operation_action_node($data){
        if(wa_get_status_modules('manufacturing')){
            $this->load->model('manufacturing/manufacturing_model');

            $operation = $this->manufacturing_model->get_operation($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_operation':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($operation->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_operation';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->manufacturing_model->delete_operation($data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_operation';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_operation';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'change_work_center':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($operation->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_work_center_operation';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $work_center = $operation->work_center_id;
                    if(isset($data['node']['data']['work_center'])){
                        $work_center = $data['node']['data']['work_center'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'mrp_routing_details', ['work_center_id' => $work_center]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_work_center_operation';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_work_center_operation';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'update_description':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($operation->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_work_center_operation';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $description = $operation->description;
                    if(isset($data['node']['data']['description'])){
                        $description = $data['node']['data']['description'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'mrp_routing_details', ['description' => $description]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_work_center_operation';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_work_center_operation';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_bill_of_material_action_node description]
     * @return [type] [description]
     */
    public function handle_bill_of_material_action_node($data){
        if(wa_get_status_modules('manufacturing')){
            $this->load->model('manufacturing/manufacturing_model');

            $bill_of_material = $this->manufacturing_model->get_bill_of_materials($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_bom':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($bill_of_material->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_bom';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->manufacturing_model->delete_bill_of_material($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_bom';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_bom';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_bom_type':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($bill_of_material->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_bom_type';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $bom_type = $bill_of_material->bom_type;
                    if(isset($data['node']['data']['bom_type'])){
                        $bom_type = $data['node']['data']['bom_type'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'mrp_bill_of_materials', ['bom_type' => $bom_type]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_bom_type';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_bom_type';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_ready_to_produce':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($bill_of_material->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_ready_to_produce';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $ready_to_produce = $bill_of_material->ready_to_produce;
                    if(isset($data['node']['data']['ready_to_produce'])){
                        $ready_to_produce = $data['node']['data']['ready_to_produce'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'mrp_bill_of_materials', ['ready_to_produce' => $ready_to_produce]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_ready_to_produce';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_ready_to_produce';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_consumption':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($bill_of_material->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_consumption';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $consumption = $bill_of_material->consumption;
                    if(isset($data['node']['data']['consumption'])){
                        $consumption = $data['node']['data']['consumption'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'mrp_bill_of_materials', ['consumption' => $consumption]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_consumption';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;    
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_consumption';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_bom_component_action_node description]
     * @return [type] [description]
     */
    public function handle_bom_component_action_node($data){
        if(wa_get_status_modules('manufacturing')){
            $this->load->model('manufacturing/manufacturing_model');

            $bom_component = $this->manufacturing_model->get_bill_of_material_details($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_bom_component':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($bom_component->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_bom_component';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->manufacturing_model->delete_bill_of_material_detail($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_bom_component';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_bom_component';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_unit_of_measure':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($bom_component->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_unit_of_measure_bom_component';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }


                    $unit_id = $bom_component->unit_id;
                    if(isset($data['node']['data']['unit_id'])){
                        $unit_id = $data['node']['data']['unit_id'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'mrp_bill_of_material_details', ['unit_id' => $unit_id]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_unit_of_measure_bom_component';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;    
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_unit_of_measure_bom_component';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_manufacturing_order_action_node description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function handle_manufacturing_order_action_node($data){
         if(wa_get_status_modules('manufacturing')){
            $this->load->model('manufacturing/manufacturing_model');

            $_manufacturing_order = $this->manufacturing_model->get_manufacturing_order($data['rel_id']);

            $manufacturing_order = $_manufacturing_order['manufacturing_order'];

            switch ($data['node']['data']['action']) {

                case 'delete_manufacturing_order':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($manufacturing_order->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_manufacturing_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->manufacturing_model->delete_manufacturing_order($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_manufacturing_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_manufacturing_order';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'mark_as_done':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($manufacturing_order->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'mark_as_done';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->manufacturing_model->mo_mark_as_done($data['rel_id'], $manufacturing_order->product_qty);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'mark_as_done';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'mark_as_done';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'change_status':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($manufacturing_order->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_status_manufacturing_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $status = $manufacturing_order->status;
                    if(isset($data['node']['data']['status'])){
                        $status = $data['node']['data']['status'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'mrp_manufacturing_orders', ['status' => $status]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_status_manufacturing_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;    
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_manufacturing_order';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'create_purchase_request':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($manufacturing_order->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_purchase_request_for_manufacturing_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->manufacturing_model->mo_create_purchase_request($manufacturing_order->id);

                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'create_purchase_request_for_manufacturing_order';
                        $log_data['action_relsult_id'] = $success;
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_purchase_request_for_manufacturing_order';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            } 
        }
    }

    /**
     * [handle_omni_sales_order_action_node description]
     * @return [type] [description]
     */
    public function handle_omni_sales_order_action_node($data){
        if(wa_get_status_modules('omni_sales')){
            $this->load->model('omni_sales/omni_sales_model');

            $order = $this->omni_sales_model->get_cart($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_order':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($order->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_omni_sale_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->omni_sales_model->delete_order($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_omni_sale_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_omni_sale_order';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_status':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($order->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_status_omni_sale_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $status = $order->status;
                    if(isset($data['node']['data']['status'])){
                        $status = $data['node']['data']['status'];
                    }

                    $change_status_data = [];
                    $change_status_data['cancelReason'] = '';
                    $change_status_data['status'] = $status;
                    $success = $this->omni_sales_model->change_status_order($change_status_data, $order->order_number, 1);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_status_omni_sale_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_omni_sale_order';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'create_invoice':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($order->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_invoice_for_omni_sale_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->omni_sales_model->create_invoice_detail_order($data['rel_id']);

                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'invoice';
                        $log_data['action_relsult_id'] = $success;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_invoice_for_omni_sale_order';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'add_shipment_activity_log':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($order->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'add_shipment_activity_log';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!wa_get_status_modules('warehouse')){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'add_shipment_activity_log';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $this->load->model('warehouse/warehouse_model');
                    $shipment = $this->warehouse_model->get_shipment_by_order($data['rel_id']);
                    if (!isset($shipment->id)) {
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'add_shipment_activity_log';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $activity_log_data = [];
                    $activity_log_data['rel_id'] = $shipment->id;
                    $activity_log_data['cart_id'] = $shipment->id;
                    $activity_log_data['date'] = date('Y-m-d H:i:s');
                    $activity_log_data['description'] = isset($data['node']['data']['description']) ? $data['node']['data']['description'] : '';

                    $success = $this->warehouse_model->log_wh_activity($activity_log_data['rel_id'], 'shipment', $activity_log_data['description'], $activity_log_data['date']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'add_shipment_activity_log';
                        $log_data['action_relsult_id'] = $success;
                        $this->save_action_log($log_data);
                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'add_shipment_activity_log';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'create_export_stocck':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($order->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_export_stocck_for_omni_sale_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    if(!wa_get_status_modules('warehouse')){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_export_stocck_for_omni_sale_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $success = $this->omni_sales_model->create_export_stock($data['rel_id'], 2);

                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'create_export_stocck_for_omni_sale_order';
                        $log_data['action_relsult_id'] = $success;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_export_stocck_for_omni_sale_order';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_sale_agent':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($order->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_sale_agent_omni_sale_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $seller = $order->seller;
                    if(isset($data['node']['data']['staff'])){
                        $seller = $data['node']['data']['staff'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'cart', ['seller' => $seller]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_sale_agent_omni_sale_order';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_sale_agent_omni_sale_order';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_omni_sales_refund_action_node description]
     * @return [type] [description]
     */
    public function handle_omni_sales_refund_action_node($data){
        if(wa_get_status_modules('omni_sales')){
            $this->load->model('omni_sales/omni_sales_model');

            $refund = $this->omni_sales_model->get_refund($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_refund':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($refund->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_omni_sales_refund';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);

                        return false;
                    }

                    $success = $this->omni_sales_model->delete_refund($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_omni_sales_refund';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_omni_sales_refund';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'update_amount':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($refund->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_amount_omni_sales_refund';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $amount = $refund->amount;
                    if(isset($data['node']['data']['amount'])){
                        $amount = $data['node']['data']['amount'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'omni_refunds', ['amount' => $amount]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_amount_omni_sales_refund';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_amount_omni_sales_refund';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'change_payment_mode';
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($refund->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_payment_mode_omni_sales_refund';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $payment_mode = $refund->payment_mode;
                    if(isset($data['node']['data']['payment_mode'])){
                        $payment_mode = $data['node']['data']['payment_mode'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'omni_refunds', ['payment_mode' => $payment_mode]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_payment_mode_omni_sales_refund';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_payment_mode_omni_sales_refund';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_omni_sales_trade_discount_action_node description]
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function handle_omni_sales_trade_discount_action_node($data){
        if(wa_get_status_modules('omni_sales')){
            $this->load->model('omni_sales/omni_sales_model');

            $trade_discount = $this->omni_sales_model->get_discount($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_trade_discount':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($trade_discount->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_trade_discount';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $success = $this->omni_sales_model->delete_trade_discount($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_trade_discount';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_trade_discount';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'update_discount':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($trade_discount->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_discount';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $discount = $trade_discount->discount;
                    if(isset($data['node']['data']['discount'])){
                        $discount = $data['node']['data']['discount'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'omni_trade_discount', ['discount' => $discount]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_discount';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_discount';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'update_formal':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($trade_discount->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_formal';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $formal = $trade_discount->formal;
                    if(isset($data['node']['data']['formal'])){
                        $formal = $data['node']['data']['formal'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'omni_trade_discount', ['formal' => $formal]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_formal';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_formal';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'add_client_to_trade_discount':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($trade_discount->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'add_client_to_trade_discount';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $clients = $trade_discount->clients;
                    if(isset($data['node']['data']['client'])){
                        if($trade_discount->clients != ''){
                            $clients = $trade_discount->clients.','.$data['node']['data']['client'];
                        }else{
                            $clients = $data['node']['data']['client'];
                        }
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'omni_trade_discount', ['clients' => $clients]);
                    if($this->db->affected_rows()){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'add_client_to_trade_discount';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'add_client_to_trade_discount';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'update_end_date':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($trade_discount->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_formal';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $end_time = $trade_discount->end_time;
                    if(isset($data['node']['data']['end_time']) && isset($data['node']['data']['end_time_type'])){
                        $current_date = strtotime(date('Y-m-d'));

                        $end_time = strtotime('+ '.$data['node']['data']['end_time'].' '.$data['node']['data']['end_time_type'], $current_date);

                        $end_time = date('Y-m-d', $end_time);
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'omni_trade_discount', ['end_time' => $end_time]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_formal';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_formal';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_asset_action_node description]
     * @return [type] [description]
     */
    public function handle_asset_action_node($data){
        if(wa_get_status_modules('fixed_equipment')){
            $this->load->model('fixed_equipment/fixed_equipment_model');

            $asset = $this->fixed_equipment_model->get_assets($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'delete_asset':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($asset->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_asset';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $success = $this->fixed_equipment_model->delete_assets($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_asset';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_asset';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_status':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($asset->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_status_asset';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $status = $asset->status;
                    if(isset($data['node']['data']['status'])){
                        $status = $data['node']['data']['status'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['status' => $status]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_status_asset';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_status_asset';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                case 'change_location':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($asset->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_location_asset';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $location = $asset->asset_location;
                    if(isset($data['node']['data']['location'])){
                        $location = $data['node']['data']['location'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['asset_location' => $location]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_location_asset';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_location_asset';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'change_model':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($asset->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_model_asset';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $model = $asset->model_id;
                    if(isset($data['node']['data']['model'])){
                        $model = $data['node']['data']['model'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['model_id' => $model]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_model_asset';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_model_asset';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                case 'change_supplier':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($asset->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_supplier_asset';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $supplier = $asset->supplier_id;
                    if(isset($data['node']['data']['supplier'])){
                        $supplier = $data['node']['data']['supplier'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['supplier_id' => $supplier]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_supplier_asset';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_supplier_asset';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_license_action_node description]
     * @return [type] [description]
     */
    public function handle_license_action_node($data){
        if(wa_get_status_modules('fixed_equipment')){
            $this->load->model('fixed_equipment/fixed_equipment_model');

            $license = $this->fixed_equipment_model->get_assets($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'delete_license':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($license->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_license';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $success = $this->fixed_equipment_model->delete_licenses($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_license';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_license';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_category':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($license->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_category_license';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $category = $license->category_id;
                    if(isset($data['node']['data']['category'])){
                        $category = $data['node']['data']['category'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['category_id' => $category]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_category_license';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_category_license';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                case 'change_manufacturer':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($license->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_manufacturer_license';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $manufacturer = $license->manufacturer_id;
                    if(isset($data['node']['data']['manufacturer'])){
                        $manufacturer = $data['node']['data']['manufacturer'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['manufacturer_id' => $manufacturer]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_manufacturer_license';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_manufacturer_license';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'change_depreciation':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($license->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_depreciation_license';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $depreciation = $license->depreciation;
                    if(isset($data['node']['data']['depreciation'])){
                        $depreciation = $data['node']['data']['depreciation'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['depreciation' => $depreciation]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_depreciation_license';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_depreciation_license';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_accessories_action_node description]
     * @return [type] [description]
     */
    public function handle_accessories_action_node($data){
        if(wa_get_status_modules('fixed_equipment')){
            $this->load->model('fixed_equipment/fixed_equipment_model');

            $accessories = $this->fixed_equipment_model->get_assets($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'delete_accessory':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($accessories->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_accessory';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $success = $this->fixed_equipment_model->delete_accessories($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_accessory';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_accessory';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_category':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($accessories->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_category_accessories';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $category = $accessories->category_id;
                    if(isset($data['node']['data']['category'])){
                        $category = $data['node']['data']['category'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['category_id' => $category]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_category_accessories';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_category_accessories';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                case 'change_manufacturer':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($accessories->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_manufacturer_accessories';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $manufacturer = $accessories->manufacturer_id;
                    if(isset($data['node']['data']['manufacturer'])){
                        $manufacturer = $data['node']['data']['manufacturer'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['manufacturer_id' => $manufacturer]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_manufacturer_accessories';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_manufacturer_accessories';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'change_location':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($accessories->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_location_accessories';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $location = $accessories->asset_location;
                    if(isset($data['node']['data']['location'])){
                        $location = $data['node']['data']['location'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['asset_location' => $location]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_location_accessories';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_location_accessories';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'change_supplier':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($accessories->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_supplier_accessories';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $supplier = $accessories->supplier_id;
                    if(isset($data['node']['data']['supplier'])){
                        $supplier = $data['node']['data']['supplier'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['supplier_id' => $supplier]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_supplier_accessories';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_supplier_accessories';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_consumable_action_node description]
     * @return [type] [description]
     */
    public function handle_consumable_action_node($data){
        if(wa_get_status_modules('fixed_equipment')){
            $this->load->model('fixed_equipment/fixed_equipment_model');

            $consumable = $this->fixed_equipment_model->get_assets($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_consumable':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($consumable->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_consumable';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $success = $this->fixed_equipment_model->delete_consumables($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_consumable';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_consumable';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_category':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($consumable->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_category_consumable';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $category = $consumable->category_id;
                    if(isset($data['node']['data']['category'])){
                        $category = $data['node']['data']['category'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['category_id' => $category]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_category_consumable';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_category_consumable';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                case 'change_manufacturer':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($consumable->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_manufacturer_consumable';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $manufacturer = $consumable->manufacturer_id;
                    if(isset($data['node']['data']['manufacturer'])){
                        $manufacturer = $data['node']['data']['manufacturer'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['manufacturer_id' => $manufacturer]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_manufacturer_consumable';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_manufacturer_consumable';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'change_location':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($consumable->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_location_consumable';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $location = $consumable->asset_location;
                    if(isset($data['node']['data']['location'])){
                        $location = $data['node']['data']['location'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['asset_location' => $location]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_location_consumable';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_location_consumable';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                default:
                    return false;
                break;
            }

        }
    }

    /**
     * [handle_component_action_node description]
     * @return [type] [description]
     */
    public function handle_component_action_node($data){
        if(wa_get_status_modules('fixed_equipment')){
            $this->load->model('fixed_equipment/fixed_equipment_model');

            $component = $this->fixed_equipment_model->get_assets($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_component':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($component->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_component';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $success = $this->fixed_equipment_model->delete_components($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_component';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_component';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'change_category':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($component->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_category_component';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $category = $component->category_id;
                    if(isset($data['node']['data']['category'])){
                        $category = $data['node']['data']['category'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['category_id' => $category]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_category_component';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_category_component';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'change_location':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($component->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_location_component';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $location = $component->asset_location;
                    if(isset($data['node']['data']['location'])){
                        $location = $data['node']['data']['location'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'fe_assets', ['asset_location' => $location]);
                    if($this->db->affected_rows()){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_location_component';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_location_component';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                default:
                    return false;
                break;
            }

        }
    }

    /**
     * [handle_payslip_action_node description]
     * @return [type] [description]
     */
    public function handle_payslip_action_node($data){
        if(wa_get_status_modules('hr_payroll')){
            $this->load->model('hr_payroll/hr_payroll_model');

            $payslip = $this->hr_payroll_model->get_hrp_payslip($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'closing_payroll':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($payslip->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'closing_payroll';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    if($payslip->file_name == ''){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'closing_payroll';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $payslip_data = [];

                    $path = HR_PAYROLL_PAYSLIP_FILE . $payslip->file_name;
                    if(!file_exists($path)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'closing_payroll';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }


                    $mystring = file_get_contents($path, true);

                    $payslip_data['payslip_data'] = $mystring;
                    $payslip_data['name'] = $payslip->payslip_name;
                    $payslip_data['id'] = $payslip->id;
                    $payslip_data['image_flag'] = false;

                    $success = $this->hr_payroll_model->payslip_close($payslip_data);

                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'closing_payroll';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'closing_payroll';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'delete_payslip':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($payslip->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_payslip';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $success = $this->hr_payroll_model->delete_payslip($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_payslip';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_payslip';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                case 'payslipng_opening':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($payslip->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'payslipng_opening';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $success = $this->hr_payroll_model->update_payslip_status($data['rel_id'], 'payslip_opening');
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'payslipng_opening';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'payslipng_opening';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_payslip_template_action_node description]
     * @return [type] [description]
     */
    public function handle_payslip_template_action_node($data){
        if(wa_get_status_modules('hr_payroll')){
            $this->load->model('hr_payroll/hr_payroll_model');

            $payslip_template = $this->hr_payroll_model->get_hrp_payslip_templates($data['rel_id']);

            switch ($data['node']['data']['action']) {
                case 'delete_payslip_template':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($payslip_template->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_payslip_template';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }   

                    $success = $this->hr_payroll_model->delete_payslip_template($data['rel_id']);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_payslip_template';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_payslip_template';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'payslip_template_apply_to_staff':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($payslip_template->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'payslip_template_apply_to_staff';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $staff = $payslip_template->staff_employees;
                    if(isset($data['node']['data']['staff']) && $data['node']['data']['staff'] != ''){
                        if($staff != ''){
                            $staff_arr = explode(',', $staff);

                            if(!in_array($data['node']['data']['staff'], $staff_arr)){
                                $staff_arr[] = $data['node']['data']['staff'];

                                $staff = implode(',', $staff_arr);
                            }else{
                                $staff = $data['node']['data']['staff'];
                            }
                        }
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'hrp_payslip_templates', ['staff_employees' => $staff]);
                    if($this->db->affected_rows()){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'payslip_template_apply_to_staff';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'payslip_template_apply_to_staff';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                case 'payslip_template_except_for_staff':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($payslip_template->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'payslip_template_except_for_staff';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $staff = $payslip_template->except_staff;
                    if(isset($data['node']['data']['except_staff']) && $data['node']['data']['except_staff'] != ''){
                        if($staff != ''){
                            $staff_arr = explode(',', $staff);

                            if(!in_array($data['node']['data']['except_staff'], $staff_arr)){
                                $staff_arr[] = $data['node']['data']['except_staff'];

                                $staff = implode(',', $staff_arr);
                            }else{
                                $staff = $data['node']['data']['except_staff'];
                            }
                        }
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'hrp_payslip_templates', ['except_staff' => $staff]);
                    if($this->db->affected_rows()){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'payslip_template_except_for_staff';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'payslip_template_except_for_staff';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            }

        }
    }

    /**
     * [handle_items_action_node description]
     * @return [type] [description]
     */
    public function handle_items_action_node($data){
        if(wa_get_status_modules('warehouse')){
            $this->load->model('warehouse/warehouse_model');

            $item = $this->warehouse_model->get_commodity($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'delete_item':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($item->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'delete_item';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $success = $this->warehouse_model->delete_commodity($data['rel_id']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'delete_item';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'delete_item';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'create_inventory_receiving_voucher':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($item->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_inventory_receiving_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $item_ids = [];
                    $item_ids[] = $data['rel_id'];
                    $success = $this->auto_create_goods_receipt($item_ids);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'inventory_receiving_voucher';
                        $log_data['action_relsult_id'] = $success;
                        $this->save_action_log($log_data);
                        return true;

                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_inventory_receiving_voucher';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                        
                break;

                case 'create_inventory_delivery_voucher':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($item->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'create_inventory_delivery_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $item_ids = [];
                    $item_ids[] = $data['rel_id'];
                    $success = $this->auto_create_goods_delivery_with_auto($item_ids);
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'inventory_delivery_voucher';
                        $log_data['action_relsult_id'] = $success;
                        $this->save_action_log($log_data);
                        return true;

                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_inventory_delivery_voucher';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                case 'change_warehouse':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($item->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_item_warehouse';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $warehouse_id = $item->warehouse_id;
                    if(isset($data['node']['data']['warehouse'])){
                        $warehouse_id = $data['node']['data']['warehouse'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'items', ['warehouse_id' => $warehouse_id]);
                    if($this->db->affected_rows() > 0){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_item_warehouse';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_item_warehouse';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;

                break;

                case 'change_commodity_type':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($item->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_item_warehouse';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $commodity_type = $item->commodity_type;
                    if(isset($data['node']['data']['commodity_type'])){
                        $commodity_type = $data['node']['data']['commodity_type'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'items', ['commodity_type' => $commodity_type]);
                    if($this->db->affected_rows() > 0){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_item_warehouse';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_item_warehouse';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'change_unit':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($item->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_item_unit';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $unit_id = $item->unit_id;
                    if(isset($data['node']['data']['unit'])){
                        $unit_id = $data['node']['data']['unit'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'items', ['unit_id' => $unit_id]);
                    if($this->db->affected_rows() > 0){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_item_unit';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_item_unit';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'change_commodity_group':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($item->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_commodity_group';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }

                    $commodity_group = $item->commodity_group;
                    if(isset($data['node']['data']['commodity_group'])){
                        $commodity_group = $data['node']['data']['commodity_group'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'items', ['group_id' => $commodity_group]);
                    if($this->db->affected_rows() > 0){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_commodity_group';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_commodity_group';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_inventory_receiving_voucher_action_node description]
     * @return [type] [description]
     */
    public function handle_inventory_receiving_voucher_action_node($data){
        if(wa_get_status_modules('warehouse')){
            $this->load->model('warehouse/warehouse_model');

            $receiving_voucher = $this->warehouse_model->get_goods_receipt($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'change_project':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($receiving_voucher->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_project_inventory_receiving_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }


                    $project = $receiving_voucher->project;
                    if(isset($data['node']['data']['project'])){
                        $project = $data['node']['data']['project'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'goods_receipt', ['project' => $project]);
                    if($this->db->affected_rows() > 0){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_project_inventory_receiving_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_project_inventory_receiving_voucher';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;


                break;
                case 'change_department':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($receiving_voucher->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_department_inventory_receiving_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }


                    $department = $receiving_voucher->department;
                    if(isset($data['node']['data']['department'])){
                        $department = $data['node']['data']['department'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'goods_receipt', ['department' => $department]);
                    if($this->db->affected_rows() > 0){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_department_inventory_receiving_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_department_inventory_receiving_voucher';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'update_note':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($receiving_voucher->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_note_inventory_receiving_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }


                    $note = $receiving_voucher->description;
                    if(isset($data['node']['data']['note'])){
                        $note = $data['node']['data']['note'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'goods_receipt', ['description' => $note]);
                    if($this->db->affected_rows() > 0){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_note_inventory_receiving_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_note_inventory_receiving_voucher';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'create_task':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');
                    

                    if(!isset($receiving_voucher->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    $this->load->model('tasks_model');

                    if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    
                    $task_template = $this->get_task_template($data['node']['data']['task_template']);
                    if(!isset($task_template->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $task_data = [];

                    $task_data['name'] = $task_template->task_subject;
                    $task_data['hourly_rate'] = '';
                    $task_data['startdate'] = $task_template->start_date;
                    $task_data['duedate'] = $task_template->due_date;
                    $task_data['priority'] = $task_template->priority;
                    $task_data['rel_type'] = 'stock_import';
                    $task_data['rel_id'] = $data['rel_id'];
                    $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                    $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                    $task_data['created_by_workflow'] = 1;

                    $task_id = $this->tasks_model->add($task_data);
                    if($task_id){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'task';
                        $log_data['action_relsult_id'] = $task_id;

                        $this->save_action_log($log_data);

                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                default:
                    return false;
                break;

            }
        }
    }

    /**
     *
     * @param  [type] $data [description]
     * @return [type]       [description]
     */
    public function handle_inventory_delivery_voucher_action_node($data){
         if(wa_get_status_modules('warehouse')){
            $this->load->model('warehouse/warehouse_model');

            $delivery_voucher = $this->warehouse_model->get_goods_delivery($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'change_project':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($delivery_voucher->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_project_inventory_delivery_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }


                    $project = $delivery_voucher->project;
                    if(isset($data['node']['data']['project'])){
                        $project = $data['node']['data']['project'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'goods_delivery', ['project' => $project]);
                    if($this->db->affected_rows() > 0){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_project_inventory_delivery_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_project_inventory_delivery_voucher';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'change_department':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($delivery_voucher->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_department_inventory_delivery_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }


                    $department = $delivery_voucher->department;
                    if(isset($data['node']['data']['department'])){
                        $department = $data['node']['data']['department'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'goods_delivery', ['department' => $department]);
                    if($this->db->affected_rows() > 0){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_department_inventory_delivery_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_department_inventory_delivery_voucher';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'update_note':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');

                    if(!isset($delivery_voucher->id)){
                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'update_note_inventory_delivery_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return false;
                    }


                    $note = $delivery_voucher->description;
                    if(isset($data['node']['data']['note'])){
                        $note = $data['node']['data']['note'];
                    }

                    $this->db->where('id', $data['rel_id']);
                    $this->db->update(db_prefix().'goods_delivery', ['description' => $note]);
                    if($this->db->affected_rows() > 0){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'update_note_inventory_delivery_voucher';
                        $log_data['action_relsult_id'] = 0;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'update_note_inventory_delivery_voucher';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                break;

                case 'create_task':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');
                    

                    if(!isset($delivery_voucher->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    $this->load->model('tasks_model');

                    if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }
                    
                    $task_template = $this->get_task_template($data['node']['data']['task_template']);
                    if(!isset($task_template->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'task';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $task_data = [];

                    $task_data['name'] = $task_template->task_subject;
                    $task_data['hourly_rate'] = '';
                    $task_data['startdate'] = $task_template->start_date;
                    $task_data['duedate'] = $task_template->due_date;
                    $task_data['priority'] = $task_template->priority;
                    $task_data['rel_type'] = 'stock_export';
                    $task_data['rel_id'] = $data['rel_id'];
                    $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                    $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                    $task_data['created_by_workflow'] = 1;

                    $task_id = $this->tasks_model->add($task_data);
                    if($task_id){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'task';
                        $log_data['action_relsult_id'] = $task_id;

                        $this->save_action_log($log_data);

                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                default:
                    return false;
                break;
            }
        }
    }

    /**
     * [handle_packing_list_action_node description]
     * @return [type] [description]
     */
    public function handle_packing_list_action_node($data){
        if(wa_get_status_modules('warehouse')){
            $this->load->model('warehouse/warehouse_model');

            $packing_list = $this->warehouse_model->get_packing_list($data['rel_id']);

            switch ($data['node']['data']['action']) {

                case 'change_delivery_status':
                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');
                    

                    if(!isset($packing_list->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'change_packing_list_delivery_status';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $delivery_status = $packing_list->delivery_status;
                    if(isset($data['node']['data']['delivery_status'])){
                        $delivery_status = $data['node']['data']['delivery_status'];
                    }


                    $success = $this->warehouse_model->delivery_status_mark_as($delivery_status, $data['rel_id'], 'packing_list');
                    if($success){

                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'change_packing_list_delivery_status';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);
                        return true;
                    }


                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'change_packing_list_delivery_status';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                break;

                case 'add_shipping_log':

                    $log_data = [];
                    $log_data['flow_id'] = $data['workflow_id'];
                    $log_data['node_id'] = $data['node']['id'];
                    $log_data['action'] = $data['node']['data']['action'];
                    $log_data['rel_type'] = $data['rel_type'];
                    $log_data['rel_id'] = $data['rel_id'];
                    $log_data['created_at'] = date('Y-m-d H:i:s');
                    

                    if(!isset($packing_list->id)){

                        $log_data['action_relsult'] = 'fail';
                        $log_data['action_relsult_type'] = 'add_packing_list_shipping_log';
                        $log_data['action_relsult_id'] = 0;

                        $this->save_action_log($log_data);

                        return false;
                    }

                    $activity_log_data = [];
                    $activity_log_data['rel_id'] = $data['rel_id'];
                    $activity_log_data['date'] = date('Y-m-d H:i:s');
                    $activity_log_data['description'] = isset($data['node']['data']['wh_activity_textarea']) ? $data['node']['data']['wh_activity_textarea'] : '';

                    $success = $this->warehouse_model->log_wh_activity($activity_log_data['rel_id'], 'packing_list', $activity_log_data['description'], $activity_log_data['date']);
                    if($success){
                        $log_data['action_relsult'] = 'success';
                        $log_data['action_relsult_type'] = 'add_packing_list_shipping_log';
                        $log_data['action_relsult_id'] = $success;
                        $this->save_action_log($log_data);
                        return true;
                    }

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'add_packing_list_shipping_log';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;

                break;

                default:
                    return false;
                break;
            }

        }
    }


    /**
     * [save_action_log description]
     * @return [type] [description]
     */
    public function save_action_log($data){

        $insert_id = $this->db->insert(db_prefix().'wa_action_logs', $data);
        if($insert_id){
            return $insert_id;
        }

        return false;

    }

    /**
     * @param  array
     * @return boolean
     */
    public function handle_condition_node($data){
        if($this->check_filter_test($data, $data['node'])){
            return 'output_1';
        }else{
            return 'output_2';
        }
             
        return false;
    }

    /**
     * [check_filter_test description]
     * @param  [type] $data [description]
     * @param  [type] $node [description]
     * @return [type]       [description]
     */
    public function check_filter_test($data, $node){
        


        if(!isset($node['data']['check'])){
            $node['data']['check'] = 'name';
        }
        
        if(!isset($node['data']['condition'])){
            $node['data']['condition'] = 'equal';
        }

        if(!isset($node['data']['condition_variable'])){
            $node['data']['value_of_variable'] = '';
        }

        if($node['data']['check'] == 'tag'){
            return false;
        }

        switch ($data['rel_type']) {
            case 'tasks':
                switch ($node['data']['check']) {
                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'addedfrom':
                        $name_of_variable = 'addedfrom';
                    break;

                    case 'client':
                        $name_of_variable = 'client';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }

            break;

            case 'projects':

                switch ($node['data']['check']) {
                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'addedfrom':
                        $name_of_variable = 'addedfrom';
                    break;

                    case 'client':
                        $name_of_variable = 'clientid';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'contracts':

                switch ($node['data']['check']) {
               
                    case 'addedfrom':
                        $name_of_variable = 'addedfrom';
                    break;

                    case 'client':
                        $name_of_variable = 'client';
                    break;

                    case 'project':
                        $name_of_variable = 'project_id';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }

            break;

            case 'leads':
                switch ($node['data']['check']) {
                    case 'addedfrom':
                        $name_of_variable = 'addedfrom';
                    break;

                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'source':
                        $name_of_variable = 'source';
                    break;

                    case 'assign_to_staff':
                        $name_of_variable = 'assigned';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'customers':
                switch ($node['data']['check']) {
                    case 'addedfrom':
                        $name_of_variable = 'addedfrom';
                    break;

                    case 'assign_to_staff':
                        $name_of_variable = 'assign_to_staff';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;

                }   
            break;

            case 'proposals':
                switch ($node['data']['check']) {
                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'project':
                        $name_of_variable = 'project_id';
                    break;

                    case 'customer':
                        $name_of_variable = 'customer';
                    break;

                    case 'lead':
                        $name_of_variable = 'lead';
                    break;

                    case 'total_value':
                        $name_of_variable = 'total';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'estimates':
                switch ($node['data']['check']) {
                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'project':
                        $name_of_variable = 'project_id';
                    break;

                    case 'customer':
                        $name_of_variable = 'clientid';
                    break;

                    case 'total_value':
                        $name_of_variable = 'total';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'invoices':
                switch ($node['data']['check']) {

                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'project':
                        $name_of_variable = 'project_id';
                    break;

                    case 'customer':
                        $name_of_variable = 'clientid';
                    break;

                    case 'total_value':
                        $name_of_variable = 'total';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'payment':
                switch ($node['data']['check']) {

                    case 'customer':
                        $name_of_variable = 'clientid';
                    break;

                    case 'paymentmode':
                        $name_of_variable = 'paymentmode';
                    break;

                    case 'invoice_status':
                        $name_of_variable = 'status';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'credit_notes':
                switch ($node['data']['check']) {

                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'customer':
                        $name_of_variable = 'clientid';
                    break;

                    case 'total_value':
                        $name_of_variable = 'total';
                    break;

                    case 'remaining_amount':
                        $name_of_variable = 'remaining_credits';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'purchase_order':
                switch ($node['data']['check']) {
                    case 'approval_status':
                        $name_of_variable = 'approve_status';
                    break;

                    case 'order_status':
                        $name_of_variable = 'order_status';
                    break;

                    case 'vendor':
                        $name_of_variable = 'vendor';
                    break;

                    case 'department':
                        $name_of_variable = 'department';
                    break;

                    case 'type':
                        $name_of_variable = 'type';
                    break;

                    case 'person_in_charge':
                        $name_of_variable = 'buyer';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'purchase_request':
                switch ($node['data']['check']) {

                    case 'approval_status':
                        $name_of_variable = 'status';
                    break;

                    case 'project':
                        $name_of_variable = 'project';
                    break;

                    case 'department':
                        $name_of_variable = 'department';
                    break;

                    case 'type':
                        $name_of_variable = 'type';
                    break;

                    case 'requestor':
                        $name_of_variable = 'requester';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;

                }
            break;

            case 'purchase_quotation':
                switch ($node['data']['check']) {
                    case 'approval_status':
                        $name_of_variable = 'status';
                    break;

                    case 'vendor':
                        $name_of_variable = 'vendor';
                    break;

                    case 'buyer':
                        $name_of_variable = 'buyer';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'purchase_invoice':
                switch ($node['data']['check']) {
                    case 'approval_status':
                        $name_of_variable = 'approval_status';
                    break;

                    case 'vendor':
                        $name_of_variable = 'vendor';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }

            break;

            case 'purchase_contract':
                switch ($node['data']['check']) {
                    case 'service_category':
                        $name_of_variable = 'service_category';
                    break;

                    case 'contract_value':
                        $name_of_variable = 'contract_value';
                    break;

                    case 'vendor':
                        $name_of_variable = 'vendor';
                    break;

                    case 'addedfrom':
                        $name_of_variable = 'add_from';
                    break;

                    case 'department':
                        $name_of_variable = 'department';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }

            break;

            case 'vendor':
                switch ($node['data']['check']) {

                    case 'country':
                        $name_of_variable = 'country';
                    break;

                    case 'category':
                        $name_of_variable = 'category';
                    break;

                    case 'city':
                        $name_of_variable = 'city';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'ticket':
                switch ($node['data']['check']) {
                    case 'summary':
                        $name_of_variable = 'issue_summary';
                    break;

                    case 'priority':
                        $name_of_variable = 'priority_level';
                    break;

                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'type':
                        $name_of_variable = 'ticket_type';
                    break;

                    case 'customer':
                        $name_of_variable = 'client_id';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }

            break;

            case 'staff':
                switch ($node['data']['check']) {
                    case 'status':
                        $name_of_variable = 'status_work';
                    break;

                    case 'role':
                        $name_of_variable = 'role';
                    break;

                    case 'department':
                        $name_of_variable = 'department';
                    break;

                    case 'job_position':
                        $name_of_variable = 'job_position';
                    break;

                    case 'gender':
                        $name_of_variable = 'sex';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'expense':
                switch ($node['data']['check']) {
                    case 'amount':
                        $name_of_variable = 'amount';
                    break;

                    case 'category':
                        $name_of_variable = 'category';
                    break;

                    case 'customer':
                        $name_of_variable = 'clientid';
                    break;

                    case 'convert_to_invoice_status':
                        $name_of_variable = 'convert_to_invoice_status';
                    break;

                    case 'payment_mode':
                        $name_of_variable = 'paymentmode';
                    break;

                    case 'is_billable':
                        $name_of_variable = 'billable';
                    break;

                    case 'vendor':
                        $name_of_variable = 'vendor';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'recruitment_plan':
                switch ($node['data']['check']) {

                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'department':
                        $name_of_variable = 'department';
                    break;

                    case 'position':
                        $name_of_variable = 'position';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'recruitment_campaign':
                switch ($node['data']['check']) {

                    case 'status':
                        $name_of_variable = 'cp_status';
                    break;   

                    case 'department':
                        $name_of_variable = 'cp_department';
                    break;

                    case 'position':
                        $name_of_variable = 'cp_position';
                    break;

                    case 'company':
                        $name_of_variable = 'company_id';
                    break;

                    case 'manager':
                        $name_of_variable = 'cp_manager';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'recruitment_form':
                switch ($node['data']['check']) {

                    case 'status':
                        $name_of_variable = 'lead_status';
                    break;

                    case 'responsible':
                        $name_of_variable = 'responsible';
                    break;

                    case 'language':
                        $name_of_variable = 'language';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'candidate':
                switch ($node['data']['check']) {

                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'desired_salary':
                        $name_of_variable = 'desired_salary';
                    break;

                    case 'marital_status':
                        $name_of_variable = 'marital_status';
                    break;

                    case 'campaign':
                        $name_of_variable = 'rec_campaign';
                    break;

                    case 'seniority':
                        $name_of_variable = 'year_experience';
                    break;

                    case 'gender':
                        $name_of_variable = 'gender';
                    break;

                    case 'skill':
                        $name_of_variable = 'skill';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'interview_schedule':
                switch ($node['data']['check']) {

                    case 'position':
                        $name_of_variable = 'position';    
                    break;

                    case 'interviewer':
                        $name_of_variable = 'interviewer';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'leave_request':
                switch ($node['data']['check']) {

                    case 'status':
                        $name_of_variable = 'status';    
                    break;

                    case 'type':
                        $name_of_variable = 'rel_type';
                    break;

                    case 'handover_recipients':
                        $name_of_variable = 'handover_recipients';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'additional_work_hours':

                switch ($node['data']['check']) {

                    case 'status':
                        $name_of_variable = 'status';    
                    break;

                    case 'timekeeping_type':
                        $name_of_variable = 'timekeeping_type';
                    break;

                    case 'creator':
                        $name_of_variable = 'creator';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'vehicle':
                switch ($node['data']['check']) {

                    case 'status':
                        $name_of_variable = 'status';    
                    break;

                    case 'vehicle_type':
                        $name_of_variable = 'vehicle_type_id';    
                    break;

                    case 'ownership':
                        $name_of_variable = 'ownership';    
                    break;

                    case 'vehicle_group_id':
                        $name_of_variable = 'vehicle_group_id';    
                    break;

                    case 'body_type':
                        $name_of_variable = 'body_type';    
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }

            break;

            case 'workperformance':
                switch ($node['data']['check']) {
                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'vehicle':
                        $name_of_variable = 'vehicle_id';
                    break;

                    case 'driver':
                        $name_of_variable = 'driver_id';
                    break;


                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'event':
                switch ($node['data']['check']) {
                    case 'event_type':
                        $name_of_variable = 'event_type';
                    break;

                    case 'vehicle':
                        $name_of_variable = 'vehicle_id';
                    break;

                    case 'driver':
                        $name_of_variable = 'driver_id';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'work_order':
                switch ($node['data']['check']) {
                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'vehicle':
                        $name_of_variable = 'vehicle_id';
                    break;

                    case 'price':
                        $name_of_variable = 'total';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'booking':
                switch ($node['data']['check']) {
                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'fuel':
                switch ($node['data']['check']) {
                    case 'vehicle':
                        $name_of_variable = 'vehicle_id';
                    break;

                    case 'price':
                        $name_of_variable = 'price';
                    break;

                    case 'type':
                        $name_of_variable = 'fuel_type';
                    break;

                    case 'vendor':
                        $name_of_variable = 'vendor_id';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'work_center':
                switch ($node['data']['check']) {

                    case 'working_hours':
                        $name_of_variable = 'working_hours';
                    break;

                    case 'oee_target':
                        $name_of_variable = 'oee_target';
                    break;

                    case 'time_efficiency':
                        $name_of_variable = 'time_efficiency';
                    break;

                    case 'costs_hour':
                        $name_of_variable = 'costs_hour';
                    break;

                    case 'capacity':
                        $name_of_variable = 'capacity';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'routing':
                switch ($node['data']['check']) {
                    case 'routing_name':
                        $name_of_variable = 'routing_name';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'operation':
                switch ($node['data']['check']) {
                    case 'routing':
                        $name_of_variable = 'routing_id';
                    break;

                    case 'work_center':
                        $name_of_variable = 'work_center_id';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'bill_of_material':
                switch ($node['data']['check']) {   
                    case 'routing':
                        $name_of_variable = 'routing_id';
                    break;

                    case 'bom_type':
                        $name_of_variable = 'bom_type';
                    break;

                    case 'ready_to_produce':
                        $name_of_variable = 'ready_to_produce';
                    break;

                    case 'consumption':
                        $name_of_variable = 'consumption';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'bom_component':
                switch ($node['data']['check']) {   
                    case 'component':
                       $name_of_variable = 'product_id';
                    break;

                    case 'product_qty':
                       $name_of_variable = 'product_qty';
                    break;

                    case 'unit_id':
                       $name_of_variable = 'unit_id';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
                
            break;

            case 'manufacturing_order':
                switch ($node['data']['check']) {
                    case 'bom':
                        $name_of_variable = 'bom_id';
                    break;

                    case 'unit_id':
                        $name_of_variable = 'unit_id';
                    break;

                    case 'staff_id':
                        $name_of_variable = 'staff_id';
                    break;

                    case 'product_qty':
                        $name_of_variable = 'product_qty';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;   
                }
            break;

            case 'omni_sales_order':
                switch ($node['data']['check']) {
                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'channel':
                        $name_of_variable = 'channel_id';
                    break;

                    case 'customer':
                        $name_of_variable = 'userid';
                    break;

                    case 'sale_agent':
                        $name_of_variable = 'seller';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break; 
                }
            break;

            case 'omni_sales_refund':
                switch ($node['data']['check']) {
                    case 'amount':
                        $name_of_variable = 'amount';
                    break;

                    case 'paymentmode':
                        $name_of_variable = 'payment_mode';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'trade_discount':
                switch ($node['data']['check']) {
                    case 'channel':
                        $name_of_variable = 'channel';
                    break;

                    case 'client_groups':
                        $name_of_variable = 'group_clients';
                    break;

                    case 'customer':
                        $name_of_variable = 'clients';
                    break;

                    case 'expired':
                        $name_of_variable = 'end_time';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'asset':
                switch ($node['data']['check']) {

                    case 'model_id':
                        $name_of_variable = 'model_id';
                    break;

                    case 'status':
                        $name_of_variable = 'status';
                    break;

                    case 'supplier':
                        $name_of_variable = 'supplier_id';
                    break;

                    case 'location':
                        $name_of_variable = 'asset_location';
                    break;

                    case 'checkout_to':
                        $name_of_variable = 'checkout_to';
                    break;


                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'license':
                switch ($node['data']['check']) {

                    case 'category_id':
                        $name_of_variable = 'category_id';
                    break;

                    case 'manufacturer':
                        $name_of_variable = 'manufacturer_id';
                    break;

                    case 'supplier':
                        $name_of_variable = 'supplier_id';
                    break;

                    case 'depreciation':
                        $name_of_variable = 'depreciation';
                    break;

                    case 'checkout_to':
                        $name_of_variable = 'checkout_to';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'accessories':
                switch ($node['data']['check']) {

                    case 'category_id':
                        $name_of_variable = 'category_id';
                    break;

                    case 'manufacturer':
                        $name_of_variable = 'manufacturer_id';
                    break;

                    case 'supplier':
                        $name_of_variable = 'supplier_id';
                    break;

                    case 'checkout_to':
                        $name_of_variable = 'checkout_to';
                    break;

                    case 'location':
                        $name_of_variable = 'asset_location';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'consumable':
                switch ($node['data']['check']) {

                    case 'category_id':
                        $name_of_variable = 'category_id';
                    break;

                    case 'manufacturer':
                        $name_of_variable = 'manufacturer_id';
                    break;

                    case 'location':
                        $name_of_variable = 'asset_location';
                    break;

                    case 'checkout_to':
                        $name_of_variable = 'checkout_to';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'component':
                switch ($node['data']['check']) {

                    case 'category_id':
                        $name_of_variable = 'category_id';
                    break;

                    case 'location':
                        $name_of_variable = 'asset_location';
                    break;

                    case 'checkout_to':
                        $name_of_variable = 'checkout_to';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'payslip':
                switch ($node['data']['check']) {

                    case 'timesheet_integration':
                        $name_of_variable = 'timesheet_integration';
                    break;

                    case 'hr_profile_integration':
                        $name_of_variable = 'hr_profile_integration';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'payslip_template':
                switch ($node['data']['check']) {

                    case 'timesheet_integration':
                        $name_of_variable = 'timesheet_integration';
                    break;

                    case 'hr_profile_integration':
                        $name_of_variable = 'hr_profile_integration';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'items':
                switch ($node['data']['check']) {
                    case 'warehouse_id':
                        $name_of_variable = 'warehouse_id';
                    break;

                    case 'commodity_type':
                        $name_of_variable = 'commodity_type';
                    break;

                    case 'commodity_group':
                        $name_of_variable = 'group_id';
                    break;

                    case 'unit':
                        $name_of_variable = 'unit_id';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'inventory_receiving_voucher':
                switch ($node['data']['check']) {
                    case 'buyer_id':
                        $name_of_variable = 'buyer_id';
                    break;

                    case 'project':
                        $name_of_variable = 'project';
                    break;

                    case 'requester':
                        $name_of_variable = 'requester';
                    break;

                    case 'type':
                        $name_of_variable = 'type';
                    break;

                    case 'department':
                        $name_of_variable = 'department';
                    break;

                    case 'warehouse_id':
                        $name_of_variable = 'warehouse_id';
                    break;

                    case 'number_of_items':
                        $name_of_variable = 'number_of_items';
                    break;

                    case 'approval_status':
                        $name_of_variable = 'approval';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'inventory_delivery_voucher':
                switch ($node['data']['check']) {

                    case 'department':
                        $name_of_variable = 'department';
                    break;

                    case 'requester':
                        $name_of_variable = 'requester';
                    break;

                    case 'staff_id':
                        $name_of_variable = 'staff_id';
                    break;

                    case 'type':
                        $name_of_variable = 'type';
                    break;

                    case 'project':
                        $name_of_variable = 'project';
                    break;

                    case 'customer':
                        $name_of_variable = 'customer_code';
                    break;

                    case 'number_of_items':
                        $name_of_variable = 'number_of_items';
                    break;

                    case 'approval_status':
                        $name_of_variable = 'approval';
                    break;

                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            case 'packing_list':
                switch ($node['data']['check']) {

                    case 'customer':
                        $name_of_variable = 'clientid';
                    break;

                    case 'approval_status':
                        $name_of_variable = 'approval';
                    break;

                    case 'delivery_status':
                        $name_of_variable = 'delivery_status';
                    break;

                    case 'number_of_items':
                        $name_of_variable = 'number_of_items';
                    break;
                        
                    default:
                        $name_of_variable = $node['data']['check'];
                    break;
                }
            break;

            default:
               
            break;

        }

        if(isset($node['data']['condition_variable']) && $node['data']['condition_variable'] == ' 0'){
            $node['data']['condition_variable'] = 0;
        }

        switch ($node['data']['condition']) {
            case 'enable':
                switch ($data['rel_type']) {
                    case 'payslip':

                        if($name_of_variable == 'timesheet_integration'){
                            if(!wa_get_status_modules('timesheets')){
                                return false;
                            }

                            if(get_hr_payroll_option('integrated_timesheets') == 1){
                                return true;
                            }

                            return false;
                        }elseif($name_of_variable == 'hr_profile_integration'){
                            if(!wa_get_status_modules('hr_profile')){
                                return false;
                            }

                            if(get_hr_payroll_option('integrated_hrprofile') == 1){
                                return true;
                            }

                            return false;
                        }
                    break;

                    case 'payslip_template':

                        if($name_of_variable == 'timesheet_integration'){
                            if(!wa_get_status_modules('timesheets')){
                                return false;
                            }

                            if(get_hr_payroll_option('integrated_timesheets') == 1){
                                return true;
                            }

                            return false;
                        }elseif($name_of_variable == 'hr_profile_integration'){
                            if(!wa_get_status_modules('hr_profile')){
                                return false;
                            }

                            if(get_hr_payroll_option('integrated_hrprofile') == 1){
                                return true;
                            }

                            return false;
                        }
                    break;


                    default:
                    break;
                }

            break;

            case 'disable':
                switch ($data['rel_type']) {
                    case 'payslip':

                        if($name_of_variable == 'timesheet_integration'){
                            if(!wa_get_status_modules('timesheets')){
                                return false;
                            }

                            if(get_hr_payroll_option('integrated_timesheets') == 0){
                                return true;
                            }

                            return false;
                        }elseif($name_of_variable == 'hr_profile_integration'){
                            if(!wa_get_status_modules('hr_profile')){
                                return false;
                            }

                            if(get_hr_payroll_option('integrated_hrprofile') == 0){
                                return true;
                            }

                            return false;
                        }
                    break;

                    case 'payslip_template':

                        if($name_of_variable == 'timesheet_integration'){
                            if(!wa_get_status_modules('timesheets')){
                                return false;
                            }

                            if(get_hr_payroll_option('integrated_timesheets') == 0){
                                return true;
                            }

                            return false;
                        }elseif($name_of_variable == 'hr_profile_integration'){
                            if(!wa_get_status_modules('hr_profile')){
                                return false;
                            }

                            if(get_hr_payroll_option('integrated_hrprofile') == 0){
                                return true;
                            }

                            return false;
                        }
                    break;

                    default:
                    break;
                }    
            break;


            case 'equal':
                switch ($data['rel_type']) {

                    case 'packing_list':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $packing_list = $this->warehouse_model->get_packing_list($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_packing_list_detail($data['rel_id']);
                            if(count($details) == $node['data']['condition_variable'] ){
                                return true;
                            }

                        }else{

                            if($name_of_variable == 'delivery_status'){
                                if($packing_list->$name_of_variable == NULL || $packing_list->$name_of_variable== '' || $packing_list->$name_of_variable== 'wh_ready_to_deliver'){
                                    $packing_list->$name_of_variable= 'ready_to_deliver';
                                }
                            }


                            if(!isset($packing_list->$name_of_variable)){
                                return false;
                            }   


                            if($packing_list->$name_of_variable == $node['data']['condition_variable'] ){
                                return true;
                            }
                        }

                        return false;
                    break;

                    case 'inventory_delivery_voucher':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $delivery_voucher = $this->warehouse_model->get_goods_delivery($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_goods_delivery_detail($data['rel_id']);
                            if(count($details) == $node['data']['condition_variable'] ){
                                return true;
                            }

                        }else{

                            if(!isset($delivery_voucher->$name_of_variable)){
                                return false;
                            }

                            if($delivery_voucher->$name_of_variable == $node['data']['condition_variable'] ){
                                return true;
                            }
                        }

                        return false;
                    break;

                    case 'inventory_receiving_voucher':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $receiving_voucher = $this->warehouse_model->get_goods_receipt($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_goods_receipt_detail($data['rel_id']);
                            if(count($details) == $node['data']['condition_variable'] ){
                                return true;
                            }

                        }else{

                            if(!isset($receiving_voucher->$name_of_variable)){
                                return false;
                            }

                            if($receiving_voucher->$name_of_variable == $node['data']['condition_variable'] ){
                                return true;
                            }
                        }

                        return false;
                    break;

                    case 'items':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');
                        $item = $this->warehouse_model->get_commodity($data['rel_id']);

                        if(!isset($item->$name_of_variable)){
                            return false;
                        }

                        if($item->$name_of_variable == $node['data']['condition_variable'] ){
                            return true;
                        }

                        return false;

                    break;

                    case 'component':
                        if(!wa_get_status_modules('fixed_equipment')){
                            return false;
                        }

                        $this->load->model('fixed_equipment/fixed_equipment_model');

                        $component = $this->fixed_equipment_model->get_assets($data['rel_id']);

                        if($name_of_variable == 'checkout_to'){
                            $data_location_info = $this->fixed_equipment_model->get_asset_location_info($data['rel_id']);
                            if (isset($data_location_info->checkout_to) && $data_location_info->checkout_to != '') {
                                if($data_location_info->checkout_type == 'user'){
                                    $data_location_info->checkout_type = 'staff';
                                }
                                if ($data_location_info->checkout_type == $node['data']['condition_variable']) {
                                    return true;
                                }
                            }

                        }else{
                            if(!isset($component->$name_of_variable)){
                                return false;
                            }

                            if($component->$name_of_variable == $node['data']['condition_variable'] ){
                                return true;
                            }
                        }

                        return false;
                    break;

                    case 'consumable':
                        if(!wa_get_status_modules('fixed_equipment')){
                            return false;
                        }

                        $this->load->model('fixed_equipment/fixed_equipment_model');

                        $consumable = $this->fixed_equipment_model->get_assets($data['rel_id']);

                        if($name_of_variable == 'checkout_to'){
                            $data_location_info = $this->fixed_equipment_model->get_asset_location_info($data['rel_id']);
                            if (isset($data_location_info->checkout_to) && $data_location_info->checkout_to != '') {
                                if($data_location_info->checkout_type == 'user'){
                                    $data_location_info->checkout_type = 'staff';
                                }
                                if ($data_location_info->checkout_type == $node['data']['condition_variable']) {
                                    return true;
                                }
                            }

                        }else{
                            if(!isset($consumable->$name_of_variable)){
                                return false;
                            }

                            if($consumable->$name_of_variable == $node['data']['condition_variable'] ){
                                return true;
                            }
                        }

                        return false;
                    break;

                    case 'accessories':
                        if(!wa_get_status_modules('fixed_equipment')){
                            return false;
                        }

                        $this->load->model('fixed_equipment/fixed_equipment_model');

                        $accessories = $this->fixed_equipment_model->get_assets($data['rel_id']);

                        if($name_of_variable == 'checkout_to'){
                            $data_location_info = $this->fixed_equipment_model->get_asset_location_info($data['rel_id']);
                            if (isset($data_location_info->checkout_to) && $data_location_info->checkout_to != '') {
                                if($data_location_info->checkout_type == 'user'){
                                    $data_location_info->checkout_type = 'staff';
                                }
                                if ($data_location_info->checkout_type == $node['data']['condition_variable']) {
                                    return true;
                                }
                            }

                        }else{
                            if(!isset($accessories->$name_of_variable)){
                                return false;
                            }

                            if($accessories->$name_of_variable == $node['data']['condition_variable'] ){
                                return true;
                            }
                        }

                        return false;
                    break;

                    case 'license':
                        if(!wa_get_status_modules('fixed_equipment')){
                            return false;
                        }

                        $this->load->model('fixed_equipment/fixed_equipment_model');

                        $license = $this->fixed_equipment_model->get_assets($data['rel_id']);

                        if($name_of_variable == 'checkout_to'){
                            $data_location_info = $this->fixed_equipment_model->get_asset_location_info($data['rel_id']);
                            if (isset($data_location_info->checkout_to) && $data_location_info->checkout_to != '') {
                                if($data_location_info->checkout_type == 'user'){
                                    $data_location_info->checkout_type = 'staff';
                                }
                                if ($data_location_info->checkout_type == $node['data']['condition_variable']) {
                                    return true;
                                }
                            }

                        }else{
                            if(!isset($license->$name_of_variable)){
                                return false;
                            }

                            if($license->$name_of_variable == $node['data']['condition_variable'] ){
                                return true;
                            }
                        }

                        return false;
                    break;

                    case 'asset':
                        if(!wa_get_status_modules('fixed_equipment')){
                            return false;
                        }

                        $this->load->model('fixed_equipment/fixed_equipment_model');

                        $asset = $this->fixed_equipment_model->get_assets($data['rel_id']);

                        if($name_of_variable == 'checkout_to'){
                            $data_location_info = $this->fixed_equipment_model->get_asset_location_info($data['rel_id']);

                            if (isset($data_location_info->checkout_to) && $data_location_info->checkout_to != '') {

                                if($data_location_info->checkout_type == 'user'){
                                    $data_location_info->checkout_type = 'staff';
                                }
                                if ($data_location_info->checkout_type == $node['data']['condition_variable']) {
                                    return true;
                                }
                            }

                        }else{
                            if(!isset($asset->$name_of_variable)){
                                return false;
                            }

                            if($asset->$name_of_variable == $node['data']['condition_variable'] ){
                                return true;
                            }
                        }

                        return false;
                    break;

                    case 'trade_discount':
                        if(!wa_get_status_modules('omni_sales')){
                            return false;
                        }
                        $this->load->model('omni_sales/omni_sales_model');

                        $trade_discount = $this->omni_sales_model->get_discount($data['rel_id']);

                        if(!isset($trade_discount->$name_of_variable)){
                            return false;
                        }
                        if($name_of_variable == 'group_clients'){
                            $group_clients_arr = [];
                            if($trade_discount->group_clients != ''){
                                $group_clients_arr = explode(',', $trade_discount->group_clients);
                            }

                            if(count($group_clients_arr) > 0 && in_array($node['data']['condition_variable'], $group_clients_arr)){
                                return true;
                            }
                        }elseif($name_of_variable == 'clients'){
                            $clients_arr = [];
                            if($trade_discount->clients != ''){
                                $clients_arr = explode(',', $trade_discount->clients);
                            }

                            if(count($clients_arr) > 0 && in_array($node['data']['condition_variable'], $clients_arr)){
                                return true;
                            }
                        }else{
                            if($name_of_variable == 'end_time'){

                                if($node['data']['condition_variable'] == 'expired'){
                                    if(date('Y-m-d') < $trade_discount->end_time){
                                        return false;
                                    }else{
                                        return true;
                                    }
                                }else if($node['data']['condition_variable'] == 'not_expired'){
                                    if(date('Y-m-d') < $trade_discount->end_time){
                                        return true;
                                    }else{
                                        return false;
                                    }
                                }

                            }else{
                                if($trade_discount->$name_of_variable == $node['data']['condition_variable'] ){
                                    return true;
                                }
                            }
                        }


                        return false;
                    break;

                    case 'omni_sales_refund':
                        if(!wa_get_status_modules('omni_sales')){
                            return false;
                        }   
                        $this->load->model('omni_sales/omni_sales_model');

                        $refund = $this->omni_sales_model->get_refund($data['rel_id']);

                        if(!isset($refund->$name_of_variable)){
                            return false;
                        }

                        if($refund->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'omni_sales_order':
                        if(!wa_get_status_modules('omni_sales')){
                            return false;
                        }   
                        $this->load->model('omni_sales/omni_sales_model');

                        $order = $this->omni_sales_model->get_cart($data['rel_id']);

                        if(!isset($order->$name_of_variable)){
                            return false;
                        }

                        if($order->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'manufacturing_order':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $manufacturing_order = $this->manufacturing_model->get_manufacturing_order($data['rel_id']);

                        if(!isset($manufacturing_order['manufacturing_order']->$name_of_variable)){
                            return false;
                        }

                        if($manufacturing_order['manufacturing_order']->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'bom_component':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $bom_component = $this->manufacturing_model->get_bill_of_material_details($data['rel_id']);

                        if(!isset($bom_component->$name_of_variable)){
                            return false;
                        }

                        if($bom_component->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'bill_of_material':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $bill_of_material = $this->manufacturing_model->get_bill_of_materials($data['rel_id']);

                        if(!isset($bill_of_material->$name_of_variable)){
                            return false;
                        }

                        if($bill_of_material->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'operation':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $operation = $this->manufacturing_model->get_operation($data['rel_id']);

                        if(!isset($operation->$name_of_variable)){
                            return false;
                        }

                        if($operation->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'routing':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $routing = $this->manufacturing_model->get_routings($data['rel_id']);

                        if(!isset($routing->$name_of_variable)){
                            return false;
                        }

                        if($routing->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'work_center':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $work_center = $this->manufacturing_model->get_work_centers($data['rel_id']);

                        if(!isset($work_center->$name_of_variable)){
                            return false;
                        }

                        if($work_center->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'fuel':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $fuel = $this->fleet_model->get_fuel_history($data['rel_id']);

                        if(!isset($fuel->$name_of_variable)){
                            return false;
                        }

                        if($fuel->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'booking':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $booking = $this->fleet_model->get_booking($data['rel_id']);

                        if(!isset($booking->$name_of_variable)){
                            return false;
                        }

                        if($booking->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'work_order':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $work_order = $this->fleet_model->get_work_order($data['rel_id']);

                        if(!isset($work_order->$name_of_variable)){
                            return false;
                        }

                        if($work_order->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'event':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $event = $this->fleet_model->get_event($data['rel_id']);

                        if(!isset($event->$name_of_variable)){
                            return false;
                        }

                        if($event->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'workperformance':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $workperformance = $this->fleet_model->get_logbook($data['rel_id']);

                        if(!isset($workperformance->$name_of_variable)){
                            return false;
                        }

                        if($workperformance->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'vehicle':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $vehicle = $this->fleet_model->get_vehicle($data['rel_id']);

                        if(!isset($vehicle->$name_of_variable)){
                            return false;
                        }

                        if($vehicle->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'additional_work_hours':
                        if(!wa_get_status_modules('timesheets')){
                            return false;
                        }
                        $this->load->model('timesheets/timesheets_model');

                        $additional_work_hours = $this->timesheets_model->get_additional_timesheets($data['rel_id']);

                        if(!isset($additional_work_hours->$name_of_variable)){
                            return false;
                        }


                        if($additional_work_hours->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'leave_request':
                        if(!wa_get_status_modules('timesheets')){
                            return false;
                        }
                        $this->load->model('timesheets/timesheets_model');

                        $leave_request = $this->timesheets_model->get_request_leave($data['rel_id']);

                        if(!isset($leave_request->$name_of_variable)){
                            return false;
                        }

                        if($name_of_variable == 'handover_recipients'){
                            if($leave_request->$name_of_variable != ''){
                                $handover_ids = explode(',', $leave_request->$name_of_variable);

                                if(in_array( $node['data']['condition_variable'] , $handover_ids)){
                                    return true;
                                }
                            }
                        }



                        if($leave_request->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'interview_schedule':
                        if(!wa_get_status_modules('recruitment')){
                            return false;
                        }
                        $this->load->model('recruitment/recruitment_model');

                        $interview_schedule = $this->recruitment_model->get_interview_schedule($data['rel_id']);

                        if(!isset($interview_schedule->$name_of_variable)){
                            return false;
                        }

                        if($name_of_variable == 'interviewer'){
                            if($interview_schedule->$name_of_variable != ''){
                                $interviewer_ids = explode(',', $interview_schedule->$name_of_variable);

                                if(in_array( $node['data']['condition_variable'] , $interviewer_ids)){
                                    return true;
                                }
                            }
                        }


                        if($interview_schedule->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'candidate':
                        if(!wa_get_status_modules('recruitment')){
                            return false;
                        }
                        $this->load->model('recruitment/recruitment_model');
                        $candidate = $this->recruitment_model->get_candidates($data['rel_id']);

                        if(!isset($candidate->$name_of_variable)){
                            return false;
                        }

                        if($name_of_variable == 'skill'){
                            if($candidate->$name_of_variable != ''){
                                $skill_ids = explode(',', $candidate->$name_of_variable);

                                if(in_array( $node['data']['condition_variable'] , $skill_ids)){
                                    return true;
                                }
                            }
                        }else{

                            if($candidate->$name_of_variable == $node['data']['condition_variable']){
                                return true;
                            }
                        }

                        return false;


                    break;

                    case 'recruitment_form':
                        if(!wa_get_status_modules('recruitment')){
                            return false;
                        }
                        $this->load->model('recruitment/recruitment_model');

                        $form = $this->recruitment_model->get_recruitment_channel($data['rel_id']);

                        if(!isset($form->$name_of_variable)){
                            return false;
                        }

                        if($form->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'recruitment_campaign':
                         if(!wa_get_status_modules('recruitment')){
                            return false;
                        }

                        $this->load->model('recruitment/recruitment_model');
                        $campaign = $this->recruitment_model->get_rec_campaign($data['rel_id']);

                        if(!isset($campaign->$name_of_variable)){
                            return false;
                        }

                        if($name_of_variable != 'cp_manager'){
                            if($campaign->$name_of_variable == $node['data']['condition_variable']){
                                return true;
                            }
                        }else{
                            if($campaign->cp_manager != ''){
                                $manager_arr = explode(',', $campaign->cp_manager);
                                if(in_array($node['data']['condition_variable'], $manager_arr)){
                                    return true;
                                }
                            }
                        }

                        return false;

                    break;

                    case 'recruitment_plan':
                        if(!wa_get_status_modules('recruitment')){
                            return false;
                        }

                        $this->load->model('recruitment/recruitment_model');
                        $plan = $this->recruitment_model->get_rec_proposal($data['rel_id']);

                        if(!isset($plan->$name_of_variable)){
                            return false;
                        }

                        if($plan->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'expense':
                        $this->load->model('expenses_model');
                        $expense = $this->expenses_model->get($data['rel_id']);

                        if($name_of_variable == 'convert_to_invoice_status'){
                            if($node['data']['condition_variable'] == 'converted'){
                                if(is_numeric($expense->invoiceid) && $expense->invoiceid > 0){
                                    return true;
                                }

                                return false;
                            }elseif($node['data']['condition_variable'] == 'not_converted'){
                                if(is_numeric($expense->invoiceid) && $expense->invoiceid > 0){
                                    return false;
                                }

                                return true;
                            }
                        }else if($name_of_variable == 'billable'){
                            if($node['data']['condition_variable'] == 'yes'){
                                if($expense->billable == 1){
                                    return true;
                                }else if($expense->billable == 0){
                                    return false;
                                }

                                return false;
                            }

                        }else{
                            if(!isset($expense->$name_of_variable)){
                                return false;
                            }

                            if($expense->$name_of_variable == $node['data']['condition_variable']){
                                return true;
                            }
                        }


                        return false;

                    break;

                    case 'staff':
                        if(!wa_get_status_modules('hr_profile')){
                            return false;
                        }

                        $this->load->model('hr_profile/hr_profile_model');

                        $staff = $this->hr_profile_model->get_staff($data['rel_id']);

                        if($name_of_variable == 'department'){
                            $this->load->model('departments_model');

                            $department_ids = $this->departments_model->get_staff_departments($data['rel_id'], true);

                            if(in_array($node['data']['condition_variable'], $department_ids)){
                                return true;
                            }

                        }else{
                            if(!isset($staff->$name_of_variable)){
                                return false;
                            }

                            if($staff->$name_of_variable == $node['data']['condition_variable']){
                                return true;
                            }

                        }

                        return false;
                    break;

                    case 'ticket':
                        if(!wa_get_status_modules('customer_service')){
                            return false;
                        }

                        $this->load->model('customer_service/customer_service_model');

                        $ticket = $this->customer_service_model->get_ticket($data['rel_id']);

                        if(!isset($ticket->$name_of_variable)){
                            return false;
                        }

                        if($ticket->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'vendor':
                        if(!wa_get_status_modules('purchase')){
                            return false;
                        }

                        $this->load->model('purchase/purchase_model');
                        $vendor = $this->purchase_model->get_vendor($data['rel_id']);

                        if(!isset($vendor->$name_of_variable)){
                            return false;
                        }

                        if($name_of_variable == 'category'){
                            if($vendor->category != ''){
                                $categories_ids = explode(',', $vendor->category);
                                if(in_array($node['data']['condition_variable'], $categories_ids)){
                                    return true;
                                }
                            }
                        }else{
                            if($vendor->$name_of_variable == $node['data']['condition_variable']){
                                return true;
                            }
                        }

                        return false;


                    break;

                    case 'purchase_contract':
                        if(!wa_get_status_modules('purchase')){
                            return false;
                        }

                        $this->load->model('purchase/purchase_model');
                        $purchase_contract = $this->purchase_model->get_contract($data['rel_id']);

                        if(!isset($purchase_contract->$name_of_variable)){
                            return false;
                        }

                        if($purchase_contract->$name_of_variable == $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'purchase_invoice':
                        if(!wa_get_status_modules('purchase')){
                            return false;
                        }

                        $this->load->model('purchase/purchase_model');
                        $purchase_invoice = $this->purchase_model->get_pur_invoice($data['rel_id']);

                        if(!isset($purchase_invoice->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] == $purchase_invoice->$name_of_variable ){
                            return true;
                        }

                        return false;

                    break;

                    case 'purchase_quotation':

                        if(!wa_get_status_modules('purchase')){
                            return false;
                        }

                        $this->load->model('purchase/purchase_model');
                        $purchase_quotation = $this->purchase_model->get_estimate($data['rel_id']);

                        if(!isset($purchase_quotation->$name_of_variable)){
                            return false;
                        }

                        if($name_of_variable == 'vendor'){
                            if($node['data']['condition_variable'] == $purchase_quotation->vendor->userid ){
                                return true;
                            }

                        }else{
                            if($node['data']['condition_variable'] == $purchase_quotation->$name_of_variable ){
                                return true;
                            }
                        }
                        return false;

                    break;

                    case 'purchase_request':

                        if(!wa_get_status_modules('purchase')){
                            return false;
                        }

                        $this->load->model('purchase/purchase_model');
                        $purchase_request = $this->purchase_model->get_purchase_request($data['rel_id']);

                        if(!isset($purchase_request->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] == $purchase_request->$name_of_variable ){
                            return true;
                        }
                        return false;

                    break;

                    case 'purchase_order':
                        if(!wa_get_status_modules('purchase')){
                            return false;
                        }

                        $this->load->model('purchase/purchase_model');
                        $purchase_order = $this->purchase_model->get_pur_order($data['rel_id']);

                        if(!isset($purchase_order->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] == $purchase_order->$name_of_variable ){
                            return true;
                        }
                        return false;
                    break;

                    case 'credit_notes':
                        $this->load->model('credit_notes_model');
                        $credit_note = $this->credit_notes_model->get($data['rel_id']);

                        if(!isset($credit_note->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] == $credit_note->$name_of_variable ){
                            return true;
                        }
                        return false;
                    break;

                    case 'payment':
                        if($name_of_variable == 'clientid' || $name_of_variable == 'status'){
                            $this->load->model('invoices_model');
                            $invoice = $this->invoices_model->get($data['invoice_id']);

                            if(!isset($invoice->$name_of_variable)){
                                return false;
                            }

                            if($node['data']['condition_variable'] == $invoice->$name_of_variable ){
                                return true;
                            }
                            return false;
                        }elseif($name_of_variable == 'paymentmode'){
                            $this->load->model('payments_model');
                            $payment = $this->payments_model->get($data['rel_id']);

                            if(!isset($payment->$name_of_variable)){
                                return false;
                            }

                            if($node['data']['condition_variable'] == $payment->$name_of_variable ){
                                return true;
                            }
                            return false;
                        }

                        return false;
                    break;

                    case 'invoices':
                        $this->load->model('invoices_model');
                        $invoice = $this->invoices_model->get($data['rel_id']);

                        if(!isset($invoice->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] == $invoice->$name_of_variable ){
                            return true;
                        }
                        return false;

                    break;

                    case 'tasks':
                        $this->load->model('tasks_model');
                        $task = $this->tasks_model->get($data['rel_id']);

                        if(!isset($task->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] == $task->$name_of_variable ){
                            return true;
                        }
                        return false;

                    break;

                    case 'projects':
                        $this->load->model('projects_model');
                        $projects = $this->projects_model->get($data['rel_id']);

                        if(!isset($projects->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] == $projects->$name_of_variable ){
                            return true;
                        }
                        return false;

                    break;

                    case 'contracts':
                        $this->load->model('contracts_model');
                        $contracts = $this->contracts_model->get($data['rel_id']);

                        if(!isset($contracts->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] == $contracts->$name_of_variable ){
                            return true;
                        }
                        return false;

                    break;

                    case 'leads':
                        $this->load->model('leads_model');
                        $leads = $this->leads_model->get($data['rel_id']);

                        if(!isset($leads->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] == $leads->$name_of_variable ){
                            return true;
                        }
                        return false;

                    break;

                    case 'customers':
                        if($name_of_variable == 'addedfrom'){
                            $this->load->model('clients_model');
                            $client = $this->clients_model->get($data['rel_id']);

                            if(!isset($client->$name_of_variable)){
                                return false;
                            }

                            if($node['data']['condition_variable'] == $client->$name_of_variable ){
                                return true;
                            }
                        }elseif($name_of_variable == 'assign_to_staff'){
                            $this->load->model('clients_model');
                            $client_admins = $this->clients_model->get_admins($data['rel_id']);

                            foreach($client_admins as $admin){
                                if($node['data']['condition_variable'] == $admin['staff_id']){
                                    return true;
                                }
                            }
                        }
                        return false;
                    break;

                    case 'proposals':

                        $this->load->model('proposals_model');
                        $proposal =  $this->proposals_model->get($data['rel_id']);
                        if($name_of_variable == 'customer'){
                            

                            if(!isset($proposal->rel_type) || !isset($proposal->rel_id)){
                                return false;
                            }

                            if($proposal->rel_type == 'customer' && $proposal->rel_id == $node['data']['condition_variable']){
                                return true;
                            }

                        }elseif($name_of_variable == 'lead'){
                            if(!isset($proposal->rel_type) || !isset($proposal->rel_id)){
                                return false;
                            }

                            if($proposal->rel_type == 'lead' && $proposal->rel_id == $node['data']['condition_variable']){
                                return true;
                            }
                        }else{

                            if(!isset($proposal->$name_of_variable)){
                                return false;
                            }

                            if($node['data']['condition_variable'] == $proposal->$name_of_variable ){
                                return true;
                            }
                        }

                        return false;
                    break;

                    case 'estimates':
                        $this->load->model('estimates_model');
                        $estimate = $this->estimates_model->get($data['rel_id']);

                        if(!isset($estimate->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] == $estimate->$name_of_variable ){
                            return true;
                        }

                    break;

                    default:
                        return false;
                    break;

                }
                
                break;

            case 'not_equal':
                switch ($data['rel_type']) {

                case 'packing_list':
                    if(!wa_get_status_modules('warehouse')){
                        return false;
                    }
                    $this->load->model('warehouse/warehouse_model');

                    $packing_list = $this->warehouse_model->get_packing_list($data['rel_id']);

                    if($name_of_variable == 'number_of_items'){ 
                        $details = $this->warehouse_model->get_packing_list_detail($data['rel_id']);
                        if(count($details) != $node['data']['condition_variable'] ){
                            return true;
                        }

                    }else{

                        if($name_of_variable == 'delivery_status'){
                            if($packing_list->$name_of_variable == NULL || $packing_list->$name_of_variable== '' || $packing_list->$name_of_variable== 'wh_ready_to_deliver'){
                                $packing_list->$name_of_variable= 'ready_to_deliver';
                            }
                        }

                        if(!isset($packing_list->$name_of_variable)){
                            return false;
                        }


                        if($packing_list->$name_of_variable != $node['data']['condition_variable'] ){
                            return true;
                        }
                    }

                    return false;
                break;    

                case 'inventory_delivery_voucher':
                    if(!wa_get_status_modules('warehouse')){
                        return false;
                    }
                    $this->load->model('warehouse/warehouse_model');

                    $delivery_voucher = $this->warehouse_model->get_goods_delivery($data['rel_id']);

                    if($name_of_variable == 'number_of_items'){ 
                        $details = $this->warehouse_model->get_goods_delivery_detail($data['rel_id']);
                        if(count($details) != $node['data']['condition_variable'] ){
                            return true;
                        }

                    }else{

                        if(!isset($delivery_voucher->$name_of_variable)){
                            return false;
                        }

                        if($delivery_voucher->$name_of_variable != $node['data']['condition_variable'] ){
                            return true;
                        }
                    }

                    return false;
                break;

                case 'inventory_receiving_voucher':
                    if(!wa_get_status_modules('warehouse')){
                        return false;
                    }
                    $this->load->model('warehouse/warehouse_model');

                    $receiving_voucher = $this->warehouse_model->get_goods_receipt($data['rel_id']);

                    if($name_of_variable == 'number_of_items'){ 
                        $details = $this->warehouse_model->get_goods_receipt_detail($data['rel_id']);
                        if(count($details) != $node['data']['condition_variable'] ){
                            return true;
                        }

                    }else{

                        if(!isset($receiving_voucher->$name_of_variable)){
                            return false;
                        }

                        if($receiving_voucher->$name_of_variable != $node['data']['condition_variable'] ){
                            return true;
                        }
                    }

                    return false;
                break;

                case 'items':
                    if(!wa_get_status_modules('warehouse')){
                        return false;
                    }
                    $this->load->model('warehouse/warehouse_model');
                    $item = $this->warehouse_model->get_commodity($data['rel_id']);

                    if(!isset($item->$name_of_variable)){
                        return false;
                    }

                    if($item->$name_of_variable != $node['data']['condition_variable'] ){
                        return true;
                    }

                    return false;

                break;    

                case 'component':
                    if(!wa_get_status_modules('fixed_equipment')){
                        return false;
                    }

                    $this->load->model('fixed_equipment/fixed_equipment_model');

                    $component = $this->fixed_equipment_model->get_assets($data['rel_id']);

                    if($name_of_variable == 'checkout_to'){
                        $data_location_info = $this->fixed_equipment_model->get_asset_location_info($data['rel_id']);
                        if (isset($data_location_info->checkout_to) && $data_location_info->checkout_to != '') {
                            if($data_location_info->checkout_type == 'user'){
                                $data_location_info->checkout_type = 'staff';
                            }
                            if ($data_location_info->checkout_type == $node['data']['condition_variable']) {
                                return true;
                            }
                        }

                    }else{
                        if(!isset($component->$name_of_variable)){
                            return false;
                        }

                        if($component->$name_of_variable == $node['data']['condition_variable'] ){
                            return true;
                        }
                    }

                    return false;
                break;

                case 'consumable':
                    if(!wa_get_status_modules('fixed_equipment')){
                        return false;
                    }

                    $this->load->model('fixed_equipment/fixed_equipment_model');

                    $consumable = $this->fixed_equipment_model->get_assets($data['rel_id']);

                    if($name_of_variable == 'checkout_to'){
                        $data_location_info = $this->fixed_equipment_model->get_asset_location_info($data['rel_id']);
                        if (isset($data_location_info->checkout_to) && $data_location_info->checkout_to != '') {
                            if($data_location_info->checkout_type == 'user'){
                                $data_location_info->checkout_type = 'staff';
                            }
                            if ($data_location_info->checkout_type != $node['data']['condition_variable']) {
                                return true;
                            }
                        }

                    }else{
                        if(!isset($consumable->$name_of_variable)){
                            return false;
                        }

                        if($consumable->$name_of_variable != $node['data']['condition_variable'] ){
                            return true;
                        }
                    }

                    return false;
                break;    

                case 'accessories':
                    if(!wa_get_status_modules('fixed_equipment')){
                        return false;
                    }

                    $this->load->model('fixed_equipment/fixed_equipment_model');

                    $accessories = $this->fixed_equipment_model->get_assets($data['rel_id']);

                    if($name_of_variable == 'checkout_to'){
                        $data_location_info = $this->fixed_equipment_model->get_asset_location_info($data['rel_id']);
                        if (isset($data_location_info->checkout_to) && $data_location_info->checkout_to != '') {
                            if($data_location_info->checkout_type == 'user'){
                                $data_location_info->checkout_type = 'staff';
                            }
                            if ($data_location_info->checkout_type != $node['data']['condition_variable']) {
                                return true;
                            }
                        }

                    }else{
                        if(!isset($accessories->$name_of_variable)){
                            return false;
                        }

                        if($accessories->$name_of_variable != $node['data']['condition_variable'] ){
                            return true;
                        }
                    }

                    return false;
                break;

                case 'license':
                    if(!wa_get_status_modules('fixed_equipment')){
                        return false;
                    }

                    $this->load->model('fixed_equipment/fixed_equipment_model');

                    $license = $this->fixed_equipment_model->get_assets($data['rel_id']);

                    if($name_of_variable == 'checkout_to'){
                        $data_location_info = $this->fixed_equipment_model->get_asset_location_info($data['rel_id']);
                        if (isset($data_location_info->checkout_to) && $data_location_info->checkout_to != '') {
                            if($data_location_info->checkout_type == 'user'){
                                $data_location_info->checkout_type = 'staff';
                            }
                            if ($data_location_info->checkout_type != $node['data']['condition_variable']) {
                                return true;
                            }
                        }

                    }else{
                        if(!isset($license->$name_of_variable)){
                            return false;
                        }

                        if($license->$name_of_variable != $node['data']['condition_variable'] ){
                            return true;
                        }
                    }

                    return false;
                break;

                case 'asset':
                    if(!wa_get_status_modules('fixed_equipment')){
                        return false;
                    }

                    $this->load->model('fixed_equipment/fixed_equipment_model');

                    $asset = $this->fixed_equipment_model->get_assets($data['rel_id']);

                    if($name_of_variable == 'checkout_to'){
                        $data_location_info = $this->fixed_equipment_model->get_asset_location_info($data['rel_id']);
                        if (isset($data_location_info->checkout_to) && $data_location_info->checkout_to != '') {

                            if($data_location_info->checkout_type == 'user'){
                                $data_location_info->checkout_type = 'staff';
                            }
                            if ($data_location_info->checkout_type != $node['data']['condition_variable']) {
                                return true;
                            }
                        }

                    }else{
                        if(!isset($asset->$name_of_variable)){
                            return false;
                        }

                        if($asset->$name_of_variable != $node['data']['condition_variable'] ){
                            return true;
                        }
                    }

                    return false;
                break;

                case 'trade_discount':
                    if(!wa_get_status_modules('omni_sales')){
                        return false;
                    }
                    $this->load->model('omni_sales/omni_sales_model');

                    $trade_discount = $this->omni_sales_model->get_discount($data['rel_id']);

                    if(!isset($trade_discount->$name_of_variable)){
                        return false;
                    }

                    if($name_of_variable == 'group_clients'){
                        $group_clients_arr = [];
                        if($trade_discount->group_clients != ''){
                            $group_clients_arr = explode(',', $trade_discount->group_clients);
                        }

                        if(count($group_clients_arr) > 0 && !in_array($node['data']['condition_variable'], $group_clients_arr)){
                            return true;
                        }
                    }elseif($name_of_variable == 'clients'){
                        $clients_arr = [];
                        if($trade_discount->clients != ''){
                            $clients_arr = explode(',', $trade_discount->clients);
                        }

                        if(count($clients_arr) > 0 && !in_array($node['data']['condition_variable'], $clients_arr)){
                            return true;
                        }
                    }else{
                        if($name_of_variable == 'end_time'){

                            if($node['data']['condition_variable'] == 'expired'){
                                if(date('Y-m-d') < $trade_discount->end_time){
                                    return true;
                                }else{
                                    return false;
                                }
                            }else if($node['data']['condition_variable'] == 'not_expired'){
                                if(date('Y-m-d') < $trade_discount->end_time){
                                    return false;
                                }else{
                                    return true;
                                }
                            }

                        }else{
                            if($trade_discount->$name_of_variable == $node['data']['condition_variable'] ){
                                return true;
                            }
                        }
                    }


                    return false;
                break;    

                case 'omni_sales_refund':
                    if(!wa_get_status_modules('omni_sales')){
                        return false;
                    }   
                    $this->load->model('omni_sales/omni_sales_model');

                    $refund = $this->omni_sales_model->get_refund($data['rel_id']);

                    if(!isset($refund->$name_of_variable)){
                        return false;
                    }

                    if($refund->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;
                break;    

                case 'omni_sales_order':
                    if(!wa_get_status_modules('omni_sales')){
                        return false;
                    }   
                    $this->load->model('omni_sales/omni_sales_model');

                    $order = $this->omni_sales_model->get_cart($data['rel_id']);

                    if(!isset($order->$name_of_variable)){
                        return false;
                    }

                    if($order->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;
                break;    

                case 'manufacturing_order':
                    if(!wa_get_status_modules('manufacturing')){
                        return false;
                    }   
                    $this->load->model('manufacturing/manufacturing_model');

                    $manufacturing_order = $this->manufacturing_model->get_manufacturing_order($data['rel_id']);

                    if(!isset($manufacturing_order['manufacturing_order']->$name_of_variable)){
                        return false;
                    }

                    if($manufacturing_order['manufacturing_order']->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;
                break;

                case 'bom_component':
                    if(!wa_get_status_modules('manufacturing')){
                        return false;
                    }   
                    $this->load->model('manufacturing/manufacturing_model');

                    $bom_component = $this->manufacturing_model->get_bill_of_material_details($data['rel_id']);

                    if(!isset($bom_component->$name_of_variable)){
                        return false;
                    }

                    if($bom_component->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;
                break;

                case 'bill_of_material':
                    if(!wa_get_status_modules('manufacturing')){
                        return false;
                    }   
                    $this->load->model('manufacturing/manufacturing_model');

                    $bill_of_material = $this->manufacturing_model->get_bill_of_material($data['rel_id']);

                    if(!isset($bill_of_material->$name_of_variable)){
                        return false;
                    }

                    if($bill_of_material->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;
                break;

                case 'operation':
                    if(!wa_get_status_modules('manufacturing')){
                        return false;
                    }   
                    $this->load->model('manufacturing/manufacturing_model');

                    $operation = $this->manufacturing_model->get_operation($data['rel_id']);

                    if(!isset($operation->$name_of_variable)){
                        return false;
                    }

                    if($operation->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;
                break;

                case 'routing':
                    if(!wa_get_status_modules('manufacturing')){
                        return false;
                    }    
                    $this->load->model('manufacturing/manufacturing_model');

                    $routing = $this->manufacturing_model->get_routings($data['rel_id']);

                    if(!isset($routing->$name_of_variable)){
                        return false;
                    }

                    if($routing->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                case 'work_center':
                    if(!wa_get_status_modules('manufacturing')){
                        return false;
                    }   
                    $this->load->model('manufacturing/manufacturing_model');

                    $work_center = $this->manufacturing_model->get_work_centers($data['rel_id']);

                    if(!isset($work_center->$name_of_variable)){
                        return false;
                    }

                    if($work_center->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                case 'fuel':
                    if(!wa_get_status_modules('fleet')){
                        return false;
                    }   
                    $this->load->model('fleet/fleet_model');

                    $fuel = $this->fleet_model->get_fuel_history($data['rel_id']);

                    if(!isset($fuel->$name_of_variable)){
                        return false;
                    }

                    if($fuel->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                case 'booking':
                    if(!wa_get_status_modules('fleet')){
                        return false;
                    }   
                    $this->load->model('fleet/fleet_model');

                    $booking = $this->fleet_model->get_booking($data['rel_id']);

                    if(!isset($booking->$name_of_variable)){
                        return false;
                    }

                    if($booking->$name_of_variable == $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                case 'work_order':
                    if(!wa_get_status_modules('fleet')){
                        return false;
                    }   
                    $this->load->model('fleet/fleet_model');

                    $work_order = $this->fleet_model->get_work_order($data['rel_id']);

                    if(!isset($work_order->$name_of_variable)){
                        return false;
                    }

                    if($work_order->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                case 'event':
                    if(!wa_get_status_modules('fleet')){
                        return false;
                    }   
                    $this->load->model('fleet/fleet_model');

                    $event = $this->fleet_model->get_event($data['rel_id']);

                    if(!isset($event->$name_of_variable)){
                        return false;
                    }

                    if($event->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                
                case 'workperformance':
                    if(!wa_get_status_modules('fleet')){
                        return false;
                    }   
                    $this->load->model('fleet/fleet_model');

                    $workperformance = $this->fleet_model->get_logbook($data['rel_id']);

                    if(!isset($workperformance->$name_of_variable)){
                        return false;
                    }

                    if($workperformance->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                case 'vehicle':
                    if(!wa_get_status_modules('fleet')){
                        return false;
                    }   
                    $this->load->model('fleet/fleet_model');

                    $vehicle = $this->fleet_model->get_vehicle($data['rel_id']);

                    if(!isset($vehicle->$name_of_variable)){
                        return false;
                    }

                    if($vehicle->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                case 'additional_work_hours':
                    if(!wa_get_status_modules('timesheets')){
                        return false;
                    }
                    $this->load->model('timesheets/timesheets_model');

                    $additional_work_hours = $this->timesheets_model->get_additional_timesheets($data['rel_id']);

                    if(!isset($additional_work_hours->$name_of_variable)){
                        return false;
                    }


                    if($additional_work_hours->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                case 'leave_request':
                    if(!wa_get_status_modules('timesheets')){
                        return false;
                    }
                    $this->load->model('timesheets/timesheets_model');

                    $leave_request = $this->timesheets_model->get_request_leave($data['rel_id']);

                    if(!isset($leave_request->$name_of_variable)){
                        return false;
                    }

                    if($name_of_variable == 'handover_recipients'){
                        if($leave_request->$name_of_variable != ''){
                            $handover_ids = explode(',', $leave_request->$name_of_variable);

                            if(!in_array( $node['data']['condition_variable'] , $handover_ids)){
                                return true;
                            }
                        }
                    }


                    if($leave_request->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;


                case 'interview_schedule':
                    if(!wa_get_status_modules('recruitment')){
                        return false;
                    }
                    $this->load->model('recruitment/recruitment_model');

                    $interview_schedule = $this->recruitment_model->get_interview_schedule($data['rel_id']);

                    if(!isset($interview_schedule->$name_of_variable)){
                        return false;
                    }

                    if($name_of_variable == 'interviewer'){
                        if($interview_schedule->$name_of_variable != ''){
                            $interviewer_ids = explode(',', $interview_schedule->$name_of_variable);

                            if(!in_array( $node['data']['condition_variable'] , $interviewer_ids)){
                                return true;
                            }
                        }
                    }


                    if($interview_schedule->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                case 'candidate':
                    if(!wa_get_status_modules('recruitment')){
                        return false;
                    }
                    $this->load->model('recruitment/recruitment_model');
                    $candidate = $this->recruitment_model->get_candidates($data['rel_id']);

                    if(!isset($candidate->$name_of_variable)){
                        return false;
                    }

                    if($name_of_variable == 'skill'){
                        if($candidate->$name_of_variable != ''){
                            $skill_ids = explode(',', $candidate->$name_of_variable);

                            if(!in_array( $node['data']['condition_variable'] , $skill_ids)){
                                return true;
                            }
                        }
                    }else{

                        if($candidate->$name_of_variable != $node['data']['condition_variable']){
                            return true;
                        }
                    }

                    return false;


                break;

                case 'recruitment_form':
                    if(!wa_get_status_modules('recruitment')){
                        return false;
                    }
                    $this->load->model('recruitment/recruitment_model');

                    $form = $this->recruitment_model->get_recruitment_channel($data['rel_id']);

                    if(!isset($form->$name_of_variable)){
                        return false;
                    }

                    if($form->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                case 'recruitment_campaign':
                    if(!wa_get_status_modules('recruitment')){
                        return false;
                    }

                    $this->load->model('recruitment/recruitment_model');
                    $campaign = $this->recruitment_model->get_rec_campaign($data['rel_id']);

                    if(!isset($campaign->$name_of_variable)){
                        return false;
                    }

                    if($name_of_variable != 'cp_manager'){
                        if($campaign->$name_of_variable != $node['data']['condition_variable']){
                            return true;
                        }
                    }else{
                        if($campaign->cp_manager != ''){
                            $manager_arr = explode(',', $campaign->cp_manager);
                            if(!in_array($node['data']['condition_variable'], $manager_arr)){
                                return true;
                            }
                        }
                    }

                    return false;

                break;

                case 'recruitment_plan':
                    if(!wa_get_status_modules('recruitment')){
                        return false;
                    }

                    $this->load->model('recruitment/recruitment_model');
                    $plan = $this->recruitment_model->get_rec_proposal($data['rel_id']);

                    if(!isset($plan->$name_of_variable)){
                        return false;
                    }

                    if($plan->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                case 'expense':
                    $this->load->model('expenses_model');
                    $expense = $this->expenses_model->get($data['rel_id']);

                    if($name_of_variable == 'convert_to_invoice_status'){
                        if($node['data']['condition_variable'] == 'converted'){
                            if(is_numeric($expense->invoiceid) && $expense->invoiceid > 0){
                                return false;
                            }

                            return true;
                        }elseif($node['data']['condition_variable'] == 'not_converted'){
                            if(is_numeric($expense->invoiceid) && $expense->invoiceid > 0){
                                return true;
                            }

                            return false;
                        }
                    }else if($name_of_variable == 'billable'){
                        if($node['data']['condition_variable'] == 'yes'){
                            if($expense->billable == 1){
                                return false;
                            }else if($expense->billable == 0){
                                return true;
                            }

                        }

                    }else{
                        if(!isset($expense->$name_of_variable)){
                            return false;
                        }

                        if($expense->$name_of_variable != $node['data']['condition_variable']){
                            return true;
                        }
                    }


                    return false;

                break;

                case 'staff':
                    if(!wa_get_status_modules('hr_profile')){
                        return false;
                    }

                    $this->load->model('hr_profile/hr_profile_model');

                    $staff = $this->hr_profile_model->get_staff($data['rel_id']);

                    if($name_of_variable == 'department'){
                        $this->load->model('departments_model');

                        $department_ids = $this->departments_model->get_staff_departments($data['rel_id'], true);

                        if(!in_array($node['data']['condition_variable'], $department_ids)){
                            return true;
                        }

                    }else{
                        if(!isset($staff->$name_of_variable)){
                            return false;
                        }

                        if($staff->$name_of_variable != $node['data']['condition_variable']){
                            return true;
                        }

                    }

                    return false;
                break;

                case 'ticket':
                    if(!wa_get_status_modules('customer_service')){
                        return false;
                    }

                    $this->load->model('customer_service/customer_service_model');

                    $ticket = $this->customer_service_model->get_ticket($data['rel_id']);

                    if(!isset($ticket->$name_of_variable)){
                        return false;
                    }

                    if($ticket->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                case 'vendor':
                    if(!wa_get_status_modules('purchase')){
                        return false;
                    }

                    $this->load->model('purchase/purchase_model');
                    $vendor = $this->purchase_model->get_vendor($data['rel_id']);

                    if(!isset($vendor->$name_of_variable)){
                        return false;
                    }

                    if($name_of_variable == 'category'){
                        if($vendor->category != ''){
                            $categories_ids = explode(',', $vendor->category);
                            if(!in_array($node['data']['condition_variable'], $categories_ids)){
                                return true;
                            }
                        }
                    }else{
                        if($vendor->$name_of_variable != $node['data']['condition_variable']){
                            return true;
                        }
                    }

                    return false;

                break;

                case 'purchase_contract':
                    if(!wa_get_status_modules('purchase')){
                        return false;
                    }

                    $this->load->model('purchase/purchase_model');
                    $purchase_contract = $this->purchase_model->get_contract($data['rel_id']);

                    if(!isset($purchase_contract->$name_of_variable)){
                        return false;
                    }

                    if($purchase_contract->$name_of_variable != $node['data']['condition_variable']){
                        return true;
                    }

                    return false;

                break;

                case 'purchase_invoice':
                    if(!wa_get_status_modules('purchase')){
                        return false;
                    }

                    $this->load->model('purchase/purchase_model');
                    $purchase_invoice = $this->purchase_model->get_pur_invoice($data['rel_id']);

                    if(!isset($purchase_invoice->$name_of_variable)){
                        return false;
                    }

                    if($node['data']['condition_variable'] != $purchase_invoice->$name_of_variable ){
                        return true;
                    }

                    return false;

                break;

                case 'purchase_quotation':

                    if(!wa_get_status_modules('purchase')){
                        return false;
                    }

                    $this->load->model('purchase/purchase_model');
                    $purchase_quotation = $this->purchase_model->get_estimate($data['rel_id']);

                    if(!isset($purchase_quotation->$name_of_variable)){
                        return false;
                    }

                    if($name_of_variable == 'vendor'){
                        if($node['data']['condition_variable'] != $purchase_quotation->vendor->userid ){
                            return true;
                        }

                    }else{
                        if($node['data']['condition_variable'] != $purchase_quotation->$name_of_variable ){
                            return true;
                        }
                    }
                    return false;

                break;

                case 'purchase_request':

                    if(!wa_get_status_modules('purchase')){
                        return false;
                    }

                    $this->load->model('purchase/purchase_model');
                    $purchase_request = $this->purchase_model->get_purchase_request($data['rel_id']);

                    if(!isset($purchase_request->$name_of_variable)){
                        return false;
                    }

                    if($node['data']['condition_variable'] != $purchase_request->$name_of_variable ){
                        return true;
                    }
                    return false;

                break;

                case 'purchase_order':
                    if(!wa_get_status_modules('purchase')){
                        return false;
                    }

                    $this->load->model('purchase/purchase_model');
                    $purchase_order = $this->purchase_model->get_pur_order($data['rel_id']);

                    if(!isset($purchase_order->$name_of_variable)){
                        return false;
                    }

                    if($node['data']['condition_variable'] != $purchase_order->$name_of_variable ){
                        return true;
                    }
                    return false;
                break;

                case 'credit_notes':
                    $this->load->model('credit_notes_model');
                    $credit_note = $this->credit_notes_model->get($data['rel_id']);

                    if(!isset($credit_note->$name_of_variable)){
                        return false;
                    }

                    if($node['data']['condition_variable'] != $credit_note->$name_of_variable ){
                        return true;
                    }
                    return false;
                break;

                case 'payment':
                    if($name_of_variable == 'clientid' || $name_of_variable == 'status'){
                        $this->load->model('invoices_model');
                        $invoice = $this->invoices_model->get($data['invoice_id']);

                        if(!isset($invoice->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] != $invoice->$name_of_variable ){
                            return true;
                        }
                        return false;
                    }elseif($name_of_variable == 'paymentmode'){
                        $this->load->model('payments_model');
                        $payment = $this->payments_model->get($data['rel_id']);

                        if(!isset($payment->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] != $payment->$name_of_variable ){
                            return true;
                        }
                        return false;
                    }

                    return false;
                break;

                case 'invoices':
                    $this->load->model('invoices_model');
                    $invoice = $this->invoices_model->get($data['rel_id']);

                    if(!isset($invoice->$name_of_variable)){
                        return false;
                    }

                    if($node['data']['condition_variable'] != $invoice->$name_of_variable ){
                        return true;
                    }
                    return false;

                break;

                case 'tasks':
                    $this->load->model('tasks_model');
                    $task = $this->tasks_model->get($data['rel_id']);

                    if($node['data']['condition_variable'] != $task->$name_of_variable ){
                        return true;
                    }
                    return false;

                break;

                case 'projects':
                    $this->load->model('projects_model');
                    $projects = $this->projects_model->get($data['rel_id']);

                    if(!isset($projects->$name_of_variable)){
                        return false;
                    }

                    if($node['data']['condition_variable'] != $projects->$name_of_variable ){
                        return true;
                    }

                    return false;

                break;

                case 'contracts':
                    $this->load->model('contracts_model');
                    $contracts = $this->contracts_model->get($data['rel_id']);

                    if(!isset($contracts->$name_of_variable)){
                        return false;
                    }

                    if($node['data']['condition_variable'] != $contracts->$name_of_variable ){
                        return true;
                    }
                    return false;

                break;

                case 'leads':
                    $this->load->model('leads_model');
                    $leads = $this->leads_model->get($data['rel_id']);

                    if(!isset($leads->$name_of_variable)){
                        return false;
                    }

                    if($node['data']['condition_variable'] != $leads->$name_of_variable ){
                        return true;
                    }
                    return false;

                break;

                case 'customers':
                    if($name_of_variable == 'addedfrom'){
                        $this->load->model('clients_model');
                        $client = $this->clients_model->get($data['rel_id']);

                        if(!isset($client->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] != $client->$name_of_variable ){
                            return true;
                        }

                    }elseif($name_of_variable == 'assign_to_staff'){
                        $this->load->model('clients_model');
                        $client_admins = $this->clients_model->get_admins($data['rel_id']);

                        $count_condition = 0;
                        foreach($client_admins as $admin){
                            if($node['data']['condition_variable'] != $admin['staff_id']){
                                $count_condition++;
                            }
                        }

                        if($count_condition == count($client_admins)){
                            return true;
                        }
                        
                    }
                    return false;
                break;

                case 'proposals':

                    $this->load->model('proposals_model');
                    $proposal =  $this->proposals_model->get($data['rel_id']);
                    if($name_of_variable == 'customer'){
                        

                        if(!isset($proposal->rel_type) || !isset($proposal->rel_id)){
                            return false;
                        }

                        if($proposal->rel_type != 'customer' || $proposal->rel_id != $node['data']['condition_variable']){
                            return true;
                        }

                    }elseif($name_of_variable == 'lead'){
                        if(!isset($proposal->rel_type) || !isset($proposal->rel_id)){
                            return false;
                        }

                        if($proposal->rel_type != 'lead' || $proposal->rel_id != $node['data']['condition_variable']){
                            return true;
                        }
                    }else{
                        if(!isset($proposal->$name_of_variable)){
                            return false;
                        }

                        if($node['data']['condition_variable'] != $proposal->$name_of_variable ){
                            return true;
                        }
                    }

                    return false;
                break;

                case 'estimates':
                    $this->load->model('estimates_model');
                    $estimate = $this->estimates_model->get($data['rel_id']);

                    if(!isset($estimate->$name_of_variable)){
                        return false;
                    }

                    if($node['data']['condition_variable'] != $estimate->$name_of_variable ){
                        return true;
                    }

                break;

                default:
                    return false;

                break;

                }
            break;

            case 'greater_than':
                switch ($data['rel_type']) {

                    case 'packing_list':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $packing_list = $this->warehouse_model->get_packing_list($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_packing_list_detail($data['rel_id']);
                            if(count($details) > $node['data']['condition_variable'] ){
                                return true;
                            }

                        }

                        return false;
                    break; 

                    case 'inventory_delivery_voucher':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $delivery_voucher = $this->warehouse_model->get_goods_delivery($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_goods_delivery_detail($data['rel_id']);
                            if(count($details) > $node['data']['condition_variable'] ){
                                return true;
                            }

                        }

                        return false;
                    break;

                    case 'inventory_receiving_voucher':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $receiving_voucher = $this->warehouse_model->get_goods_receipt($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_goods_receipt_detail($data['rel_id']);
                            if(count($details) > $node['data']['condition_variable'] ){
                                return true;
                            }

                        }

                        return false;
                    break;


                    case 'omni_sales_refund':
                        if(!wa_get_status_modules('omni_sales')){
                            return false;
                        }   
                        $this->load->model('omni_sales/omni_sales_model');

                        $refund = $this->omni_sales_model->get_refund($data['rel_id']);

                        if(!isset($refund->$name_of_variable)){
                            return false;
                        }

                        if($refund->$name_of_variable > $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break; 

                    case 'manufacturing_order':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $manufacturing_order = $this->manufacturing_model->get_manufacturing_order($data['rel_id']);

                        if(!isset($manufacturing_order['manufacturing_order']->$name_of_variable)){
                            return false;
                        }

                        if($manufacturing_order['manufacturing_order']->$name_of_variable > $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'bom_component':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $bom_component = $this->manufacturing_model->get_bill_of_material_details($data['rel_id']);

                        if(!isset($bom_component->$name_of_variable)){
                            return false;
                        }

                        if($bom_component->$name_of_variable > $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'work_center':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $work_center = $this->manufacturing_model->get_work_centers($data['rel_id']);

                        if(!isset($work_center->$name_of_variable)){
                            return false;
                        }

                        if($work_center->$name_of_variable > $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'fuel':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $fuel = $this->fleet_model->get_fuel_history($data['rel_id']);

                        if(!isset($fuel->$name_of_variable)){
                            return false;
                        }

                        if($fuel->$name_of_variable > $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'work_order':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $work_order = $this->fleet_model->get_work_order($data['rel_id']);

                        if(!isset($work_order->$name_of_variable)){
                            return false;
                        }

                        if($work_order->$name_of_variable > $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'expense':
                        $this->load->model('expenses_model');
                        $expense = $this->expenses_model->get($data['rel_id']);

                       
                        if(!isset($expense->$name_of_variable)){
                            return false;
                        }

                        if( (float) $expense->$name_of_variable > (float) $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'purchase_contract':
                        if(!wa_get_status_modules('purchase')){
                            return false;
                        }

                        $this->load->model('purchase/purchase_model');
                        $purchase_contract = $this->purchase_model->get_contract($data['rel_id']);

                        if(!isset($purchase_contract->$name_of_variable)){
                            return false;
                        }

                        if( (float) $purchase_contract->$name_of_variable > (float) $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;


                    case 'credit_notes':
                        $this->load->model('credit_notes_model');
                        $credit_note = $this->credit_notes_model->get($data['rel_id']);

                        if(!isset($credit_note->$name_of_variable)){
                            return false;
                        }

                        if( (float) $credit_note->$name_of_variable > (float) $node['data']['condition_variable']){
                            return true;
                        }
                        return false;
                    break;

                    case 'proposals':

                        $this->load->model('proposals_model');
                        $proposal =  $this->proposals_model->get($data['rel_id']);
                        if(!isset($proposal->$name_of_variable)){
                                return false;
                            }

                        if( (float) $proposal->$name_of_variable > (float) $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'estimates':
                        $this->load->model('estimates_model');
                        $estimate = $this->estimates_model->get($data['rel_id']);

                        if(!isset($estimate->$name_of_variable)){
                            return false;
                        }

                        if( (float) $estimate->$name_of_variable > (float) $node['data']['condition_variable'] ){
                            return true;
                        }

                    break;

                    case 'invoices':
                        $this->load->model('invoices_model');
                        $invoice = $this->invoices_model->get($data['rel_id']);

                        if(!isset($invoice->$name_of_variable)){
                            return false;
                        }

                        if( (float) $invoice->$name_of_variable > (float) $node['data']['condition_variable']){
                            return true;
                        }
                        return false;

                    break;

                    default:
                        return false;

                    break;
                }
            break;

            case 'greater_than_or_equal':
                switch ($data['rel_type']) {

                    case 'packing_list':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $packing_list = $this->warehouse_model->get_packing_list($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_packing_list_detail($data['rel_id']);
                            if(count($details) >= $node['data']['condition_variable'] ){
                                return true;
                            }

                        }

                        return false;
                    break; 

                     case 'inventory_delivery_voucher':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $delivery_voucher = $this->warehouse_model->get_goods_delivery($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_goods_delivery_detail($data['rel_id']);
                            if(count($details) >= $node['data']['condition_variable'] ){
                                return true;
                            }

                        }

                        return false;
                    break;

                    case 'inventory_receiving_voucher':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $receiving_voucher = $this->warehouse_model->get_goods_receipt($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_goods_receipt_detail($data['rel_id']);
                            if(count($details) >= $node['data']['condition_variable'] ){
                                return true;
                            }

                        }

                        return false;
                    break;

                    case 'omni_sales_refund':
                        if(!wa_get_status_modules('omni_sales')){
                            return false;
                        }   
                        $this->load->model('omni_sales/omni_sales_model');

                        $refund = $this->omni_sales_model->get_refund($data['rel_id']);

                        if(!isset($refund->$name_of_variable)){
                            return false;
                        }

                        if($refund->$name_of_variable >= $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break; 

                    case 'manufacturing_order':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $manufacturing_order = $this->manufacturing_model->get_manufacturing_order($data['rel_id']);

                        if(!isset($manufacturing_order['manufacturing_order']->$name_of_variable)){
                            return false;
                        }

                        if($manufacturing_order['manufacturing_order']->$name_of_variable >= $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'bom_component':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $bom_component = $this->manufacturing_model->get_bill_of_material_details($data['rel_id']);

                        if(!isset($bom_component->$name_of_variable)){
                            return false;
                        }

                        if($bom_component->$name_of_variable >= $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'work_center':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $work_center = $this->manufacturing_model->get_work_centers($data['rel_id']);

                        if(!isset($work_center->$name_of_variable)){
                            return false;
                        }

                        if($work_center->$name_of_variable >= $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'fuel':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $fuel = $this->fleet_model->get_fuel_history($data['rel_id']);

                        if(!isset($fuel->$name_of_variable)){
                            return false;
                        }

                        if($fuel->$name_of_variable >= $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'work_order':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $work_order = $this->fleet_model->get_work_order($data['rel_id']);

                        if(!isset($work_order->$name_of_variable)){
                            return false;
                        }

                        if($work_order->$name_of_variable >= $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'expense':
                        $this->load->model('expenses_model');
                        $expense = $this->expenses_model->get($data['rel_id']);

                       
                        if(!isset($expense->$name_of_variable)){
                            return false;
                        }

                        if( (float) $expense->$name_of_variable >= (float) $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'purchase_contract':
                        if(!wa_get_status_modules('purchase')){
                            return false;
                        }

                        $this->load->model('purchase/purchase_model');
                        $purchase_contract = $this->purchase_model->get_contract($data['rel_id']);

                        if(!isset($purchase_contract->$name_of_variable)){
                            return false;
                        }

                        if( (float) $purchase_contract->$name_of_variable >= (float) $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;


                    case 'credit_notes':
                        $this->load->model('credit_notes_model');
                        $credit_note = $this->credit_notes_model->get($data['rel_id']);

                        if(!isset($credit_note->$name_of_variable)){
                            return false;
                        }

                        if( (float) $credit_note->$name_of_variable >= (float) $node['data']['condition_variable']){
                            return true;
                        }
                        return false;
                    break;

                    case 'proposals':

                        $this->load->model('proposals_model');
                        $proposal =  $this->proposals_model->get($data['rel_id']);
                        if(!isset($proposal->$name_of_variable)){
                                return false;
                            }

                        if( (float) $proposal->$name_of_variable >= (float) $node['data']['condition_variable']){
                            return true;
                        }


                        return false;
                    break;

                    case 'estimates':
                        $this->load->model('estimates_model');
                        $estimate = $this->estimates_model->get($data['rel_id']);

                        if(!isset($estimate->$name_of_variable)){
                            return false;
                        }

                        if( (float) $estimate->$name_of_variable >= (float) $node['data']['condition_variable'] ){
                            return true;
                        }

                    break;

                    case 'invoices':
                        $this->load->model('invoices_model');
                        $invoice = $this->invoices_model->get($data['rel_id']);

                        if(!isset($invoice->$name_of_variable)){
                            return false;
                        }

                        if((float) $invoice->$name_of_variable >= (float) $node['data']['condition_variable']){
                            return true;
                        }
                        return false;

                    break;

                    default:
                        return false;

                    break;
                }
            break;

            case 'less_than':
                switch ($data['rel_type']) {
                    case 'packing_list':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $packing_list = $this->warehouse_model->get_packing_list($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_packing_list_detail($data['rel_id']);
                            if(count($details) < $node['data']['condition_variable'] ){
                                return true;
                            }

                        }

                        return false;
                    break; 

                     case 'inventory_delivery_voucher':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $delivery_voucher = $this->warehouse_model->get_goods_delivery($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_goods_delivery_detail($data['rel_id']);
                            if(count($details) < $node['data']['condition_variable'] ){
                                return true;
                            }

                        }

                        return false;
                    break;


                    case 'inventory_receiving_voucher':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $receiving_voucher = $this->warehouse_model->get_goods_receipt($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_goods_receipt_detail($data['rel_id']);
                            if(count($details) < $node['data']['condition_variable'] ){
                                return true;
                            }

                        }

                        return false;
                    break;

                    case 'omni_sales_refund':
                        if(!wa_get_status_modules('omni_sales')){
                            return false;
                        }   
                        $this->load->model('omni_sales/omni_sales_model');

                        $refund = $this->omni_sales_model->get_refund($data['rel_id']);

                        if(!isset($refund->$name_of_variable)){
                            return false;
                        }

                        if($refund->$name_of_variable < $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break; 

                    case 'manufacturing_order':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $manufacturing_order = $this->manufacturing_model->get_manufacturing_order($data['rel_id']);

                        if(!isset($manufacturing_order['manufacturing_order']->$name_of_variable)){
                            return false;
                        }

                        if($manufacturing_order['manufacturing_order']->$name_of_variable < $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'bom_component':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $bom_component = $this->manufacturing_model->get_bill_of_material_details($data['rel_id']);

                        if(!isset($bom_component->$name_of_variable)){
                            return false;
                        }

                        if($bom_component->$name_of_variable < $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'work_center':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $work_center = $this->manufacturing_model->get_work_centers($data['rel_id']);

                        if(!isset($work_center->$name_of_variable)){
                            return false;
                        }

                        if($work_center->$name_of_variable < $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'fuel':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $fuel = $this->fleet_model->get_fuel_history($data['rel_id']);

                        if(!isset($fuel->$name_of_variable)){
                            return false;
                        }

                        if($fuel->$name_of_variable < $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'work_order':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $work_order = $this->fleet_model->get_work_order($data['rel_id']);

                        if(!isset($work_order->$name_of_variable)){
                            return false;
                        }

                        if($work_order->$name_of_variable < $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'expense':
                        $this->load->model('expenses_model');
                        $expense = $this->expenses_model->get($data['rel_id']);

                       
                        if(!isset($expense->$name_of_variable)){
                            return false;
                        }

                        if( (float) $expense->$name_of_variable < (float) $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'purchase_contract':
                        if(!wa_get_status_modules('purchase')){
                            return false;
                        }

                        $this->load->model('purchase/purchase_model');
                        $purchase_contract = $this->purchase_model->get_contract($data['rel_id']);

                        if(!isset($purchase_contract->$name_of_variable)){
                            return false;
                        }

                        if( (float) $purchase_contract->$name_of_variable < (float) $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;


                    case 'credit_notes':
                        $this->load->model('credit_notes_model');
                        $credit_note = $this->credit_notes_model->get($data['rel_id']);

                        if(!isset($credit_note->$name_of_variable)){
                            return false;
                        }

                        if( (float) $credit_note->$name_of_variable <  (float) $node['data']['condition_variable']){
                            return true;
                        }
                        return false;
                    break;


                    case 'proposals':

                        $this->load->model('proposals_model');
                        $proposal =  $this->proposals_model->get($data['rel_id']);
                        if(!isset($proposal->$name_of_variable)){
                                return false;
                            }

                        if( (float) $proposal->$name_of_variable < (float) $node['data']['condition_variable']){
                            return true;
                        }


                        return false;
                    break;

                    case 'estimates':
                        $this->load->model('estimates_model');
                        $estimate = $this->estimates_model->get($data['rel_id']);

                        if(!isset($estimate->$name_of_variable)){
                            return false;
                        }

                        if( (float) $estimate->$name_of_variable < (float) $node['data']['condition_variable'] ){
                            return true;
                        }
                        return false;

                    break;

                    case 'invoices':
                        $this->load->model('invoices_model');
                        $invoice = $this->invoices_model->get($data['rel_id']);

                        if(!isset($invoice->$name_of_variable)){
                            return false;
                        }

                        if( (float) $invoice->$name_of_variable < (float) $node['data']['condition_variable']){
                            return true;
                        }
                        return false;

                    break;

                    default:
                        return false;

                    break;
                }
            break;

            case 'less_than_or_equal':
                switch ($data['rel_type']) {

                    case 'packing_list':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $packing_list = $this->warehouse_model->get_packing_list($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_packing_list_detail($data['rel_id']);
                            if(count($details) <= $node['data']['condition_variable'] ){
                                return true;
                            }

                        }

                        return false;
                    break; 

                    case 'inventory_delivery_voucher':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $delivery_voucher = $this->warehouse_model->get_goods_delivery($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_goods_delivery_detail($data['rel_id']);
                            if(count($details) <= $node['data']['condition_variable'] ){
                                return true;
                            }

                        }

                        return false;
                    break;

                    case 'inventory_receiving_voucher':
                        if(!wa_get_status_modules('warehouse')){
                            return false;
                        }
                        $this->load->model('warehouse/warehouse_model');

                        $receiving_voucher = $this->warehouse_model->get_goods_receipt($data['rel_id']);

                        if($name_of_variable == 'number_of_items'){ 
                            $details = $this->warehouse_model->get_goods_receipt_detail($data['rel_id']);
                            if(count($details) <= $node['data']['condition_variable'] ){
                                return true;
                            }

                        }

                        return false;
                    break;

                    case 'omni_sales_refund':
                        if(!wa_get_status_modules('omni_sales')){
                            return false;
                        }   
                        $this->load->model('omni_sales/omni_sales_model');

                        $refund = $this->omni_sales_model->get_refund($data['rel_id']);

                        if(!isset($refund->$name_of_variable)){
                            return false;
                        }

                        if($refund->$name_of_variable <= $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break; 

                    case 'manufacturing_order':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $manufacturing_order = $this->manufacturing_model->get_manufacturing_order($data['rel_id']);

                        if(!isset($manufacturing_order['manufacturing_order']->$name_of_variable)){
                            return false;
                        }

                        if($manufacturing_order['manufacturing_order']->$name_of_variable <= $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'bom_component':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $bom_component = $this->manufacturing_model->get_bill_of_material_details($data['rel_id']);

                        if(!isset($bom_component->$name_of_variable)){
                            return false;
                        }

                        if($bom_component->$name_of_variable <= $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'work_center':
                        if(!wa_get_status_modules('manufacturing')){
                            return false;
                        }   
                        $this->load->model('manufacturing/manufacturing_model');

                        $work_center = $this->manufacturing_model->get_work_centers($data['rel_id']);

                        if(!isset($work_center->$name_of_variable)){
                            return false;
                        }

                        if($work_center->$name_of_variable <= $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'fuel':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $fuel = $this->fleet_model->get_fuel_history($data['rel_id']);

                        if(!isset($fuel->$name_of_variable)){
                            return false;
                        }

                        if($fuel->$name_of_variable <= $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'work_order':
                        if(!wa_get_status_modules('fleet')){
                            return false;
                        }   
                        $this->load->model('fleet/fleet_model');

                        $work_order = $this->fleet_model->get_work_order($data['rel_id']);

                        if(!isset($work_order->$name_of_variable)){
                            return false;
                        }

                        if($work_order->$name_of_variable <= $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;


                    case 'expense':
                        $this->load->model('expenses_model');
                        $expense = $this->expenses_model->get($data['rel_id']);

                       
                        if(!isset($expense->$name_of_variable)){
                            return false;
                        }

                        if( (float) $expense->$name_of_variable <= (float) $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'purchase_contract':
                        if(!wa_get_status_modules('purchase')){
                            return false;
                        }

                        $this->load->model('purchase/purchase_model');
                        $purchase_contract = $this->purchase_model->get_contract($data['rel_id']);

                        if(!isset($purchase_contract->$name_of_variable)){
                            return false;
                        }

                        if( (float) $purchase_contract->$name_of_variable <= (float) $node['data']['condition_variable']){
                            return true;
                        }

                        return false;

                    break;

                    case 'credit_notes':
                        $this->load->model('credit_notes_model');
                        $credit_note = $this->credit_notes_model->get($data['rel_id']);

                        if(!isset($credit_note->$name_of_variable)){
                            return false;
                        }

                        if( (float) $credit_note->$name_of_variable <= (float) $node['data']['condition_variable']){
                            return true;
                        }
                        return false;
                    break;


                    case 'proposals':

                        $this->load->model('proposals_model');
                        $proposal =  $this->proposals_model->get($data['rel_id']);
                        if(!isset($proposal->$name_of_variable)){
                                return false;
                            }

                        if( (float) $proposal->$name_of_variable <= (float) $node['data']['condition_variable']){
                            return true;
                        }

                        return false;
                    break;

                    case 'estimates':
                        $this->load->model('estimates_model');
                        $estimate = $this->estimates_model->get($data['rel_id']);

                        if(!isset($estimate->$name_of_variable)){
                            return false;
                        }

                        if( (float) $estimate->$name_of_variable <= (float) $node['data']['condition_variable'] ){
                            return true;
                        }

                    break;

                    case 'invoices':
                        $this->load->model('invoices_model');
                        $invoice = $this->invoices_model->get($data['rel_id']);

                        if(!isset($invoice->$name_of_variable)){
                            return false;
                        }

                        if( (float) $invoice->$name_of_variable <= (float) $node['data']['condition_variable']){
                            return true;
                        }
                        return false;

                    break;

                    default:
                        return false;

                    break;
                }
            break;

            default:
                return false;

            break;

        }

        return false;
    }

    /**
     * [add_category description]
     */
    public function add_category($data){

        $data['created_at'] = date('Y-m-d H:i:s');
        $data['created_by'] = get_staff_user_id();

        $this->db->insert(db_prefix().'wa_categories', $data);
        $insert_id = $this->db->insert_id();
        if($insert_id){
            return $insert_id;
        }
        return false;
    }


    /**
     * [update_category description]
     */
    public function update_category($data, $id){

        $this->db->where('id', $id);
        $this->db->update(db_prefix().'wa_categories', $data);
        if( $this->db->affected_rows() > 0){
       
            return true;
        }
        return false;
    }

    /**
     * [get_categories description]
     * @return [type] [description]
     */
    public function get_categories($id = ''){
        if($id != ''){
            $this->db->where('id', $id);
            return $this->db->get(db_prefix().'wa_categories')->row();
        }
        return $this->db->get(db_prefix().'wa_categories')->result_array();
    }

    /**
     * [delete_category description]
     * @return [type] [description]
     */
    public function delete_category($id){

        $this->db->where('id', $id);
        $this->db->delete(db_prefix().'wa_categories');
        if($this->db->affected_rows() > 0){
            return true;
        }
        return false;
    }

    /**
     * [get_history_logs description]
     * @return [type] [description]
     */
    public function get_history_logs($flow_id){

        $this->db->where('flow_id', $flow_id);
        $logs = $this->db->get(db_prefix().'wa_flows_logs')->result_array();
        
        return $logs;

    }

    /**
     * [check_task_create_or_update_by_workflow description]
     * @return [type] [description]
     */
    public function check_task_create_or_update_by_workflow($task_id){
        $this->db->select('id, created_by_workflow, updated_by_workflow');
        $this->db->where('id', $task_id);
        return $this->db->get(db_prefix().'tasks')->row();
    }

    /**
     * [sent_email_action description]
     * @return [type] [description]
     */
    public function sent_email_action($content, $staff_id){

        $data = [];
        $data['content'] = $content;
        $data['staff_id'] = $staff_id;

        $this->load->model('staff_model');
        $staff = $this->staff_model->get($staff_id);
        if(isset($staff->email)){
            $data['mail_to'] = $staff->email;


            $template = mail_template('workflow_send_mail_action', 'workflow_automation', array_to_object($data) );

            if ($template->send()) {
                return true;
            }

        }

        
        return false;
    }

    /**
     * Convert lead to client
     * @since  version 1.0.1
     * @return mixed
     */
    public function convert_lead_to_customer($lead)
    {
        $default_country  = get_option('customer_default_country');
        $lead = (array) $lead;

        if(mb_strpos($lead['name'],' ') !== false){
           $_temp = explode(' ',$lead['name']);
           $firstname = $_temp[0];
           if(isset($_temp[2])){
             $lastname = $_temp[1] . ' ' . $_temp[2];
          } else {
             $lastname = $_temp[1];
          }
       } else {
          $lastname = '';
          $firstname = $lead->name;
       }

        $data             = [
            'leadid' => $lead['id'],
            'password' => '1',
            'firstname' => $firstname,
            'lastname' => $lastname,
            'title' => $lead['title'],
            'email' => $lead['email'],
            'company' => $lead['company'],
            'phonenumber' => $lead['phonenumber'],
            'website' => $lead['website'],
            'address' => $lead['address'],
            'city' => $lead['city'],
            'state' => $lead['state'],
            'country' => $lead['country'],
            'zip' => $lead['zip'],
            'fakeusernameremembered' => '',
            'fakepasswordremembered' => '',
        ];

        if ($data['country'] == '' && $default_country != '') {
            $data['country'] = $default_country;
        }

        $data['billing_street']  = $data['address'];
        $data['billing_city']    = $data['city'];
        $data['billing_state']   = $data['state'];
        $data['billing_zip']     = $data['zip'];
        $data['billing_country'] = $data['country'];

        $data['is_primary'] = 1;
        $id                 = $this->clients_model->add($data, true);
        if ($id) {
            $primary_contact_id = get_primary_contact_user_id($id);

            if (!has_permission('customers', '', 'view') && get_option('auto_assign_customer_admin_after_lead_convert') == 1) {
                $this->db->insert(db_prefix() . 'customer_admins', [
                    'date_assigned' => date('Y-m-d H:i:s'),
                    'customer_id'   => $id,
                    'staff_id'      => get_staff_user_id(),
                ]);
            }
            $this->load->model('leads_model');
            
            $this->leads_model->log_lead_activity($data['leadid'], 'not_lead_activity_converted', false, serialize([
                get_staff_full_name(),
            ]));
            $default_status = $this->leads_model->get_status('', [
                'isdefault' => 1,
            ]);
            $this->db->where('id', $data['leadid']);
            $this->db->update(db_prefix() . 'leads', [
                'date_converted' => date('Y-m-d H:i:s'),
                'status'         => $default_status[0]['id'],
                'junk'           => 0,
                'lost'           => 0,
            ]);
            // Check if lead email is different then client email
            $contact = $this->clients_model->get_contact(get_primary_contact_user_id($id));
          
            // set the lead to status client in case is not status client
            $this->db->where('isdefault', 1);
            $status_client_id = $this->db->get(db_prefix() . 'leads_status')->row()->id;
            $this->db->where('id', $data['leadid']);
            $this->db->update(db_prefix() . 'leads', [
                'status' => $status_client_id,
            ]);

            set_alert('success', _l('lead_to_client_base_converted_success'));

            if (is_gdpr() && get_option('gdpr_after_lead_converted_delete') == '1') {
                // When lead is deleted
                // move all proposals to the actual customer record
                $this->db->where('rel_id', $data['leadid']);
                $this->db->where('rel_type', 'lead');
                $this->db->update('proposals', [
                    'rel_id'   => $id,
                    'rel_type' => 'customer',
                ]);

                $this->leads_model->delete($data['leadid']);

                $this->db->where('userid', $id);
                $this->db->update(db_prefix() . 'clients', ['leadid' => null]);
            }

            log_activity('Created Lead Client Profile [LeadID: ' . $data['leadid'] . ', ClientID: ' . $id . ']');
            hooks()->do_action('lead_converted_to_customer', ['lead_id' => $data['leadid'], 'customer_id' => $id]);

            return $id;
        }

        return false;
    }

    /**
     * delete hr profile permission
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_hr_profile_permission($id)
    {
        $str_permissions ='';
        foreach (list_workflow_automation_permisstion() as $per_key =>  $per_value) {
            if(strlen($str_permissions) > 0){
                $str_permissions .= ",'".$per_value."'";
            }else{
                $str_permissions .= "'".$per_value."'";
            }
        }

        $sql_where = " feature IN (".$str_permissions.") ";

        $this->db->where('staff_id', $id);
        $this->db->where($sql_where);
        $this->db->delete(db_prefix() . 'staff_permissions');

        if ($this->db->affected_rows() > 0) {
            return true;
        }

        return false;
    }

    /**
     * [change_workflow_status description]
     * @param  [type] $id     [description]
     * @param  [type] $status [description]
     * @return [type]         [description]
     */
    public function change_workflow_status($id, $status)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'wa_workflows', [
            'enabled' => $status,
        ]);
    }

    /**
     * [get_workflow description]
     * @return [type] [description]
     */
    public function get_workflows(){

        return $this->db->get(db_prefix().'wa_workflows')->result_array();
    }


    /**
     * auto create goods receipt with purchase order
     * @param  array $data 
     *      
     */
    public function auto_create_goods_receipt($item_ids)
    {
        $this->load->model('clients_model');
        $get_base_currency =  get_base_currency();

        $arr_pur_resquest = [];
        $total_goods_money = 0;
        $total_money = 0;
        $total_tax_money = 0;
        $value_of_inventory = 0;
        $currency = 0;
        $currency_exchange_rate = 1;
        if($get_base_currency){
            $currency = $get_base_currency->id;
        }

        $sql = 'SELECT id as commodity_code, ' . db_prefix() . 'items.description, ' . db_prefix() . 'items.unit_id, purchase_price, tax, tax2  FROM '.db_prefix().'items WHERE id IN ('.implode(',',$item_ids).')';
        $results = $this->db->query($sql)->result_array();

        $currency_rate = 1;
        

        $wh_products_by_serial = get_option('wh_products_by_serial');
        $wh_serial_number_as_mandatory = get_option('wh_serial_number_as_mandatory');
        $next_serial_number = (int)get_option('next_serial_number');
        $next_lot_number = (int) get_option('next_lot_number');

        foreach ($results as $key => $value) {
            $value['unit_price'] = round((float)$value['purchase_price']/$currency_rate, 5);
            $value['quantities'] = 1;

            $value['into_money'] = $value['unit_price'] * 1;
            $results[$key]['unit_price'] = $value['unit_price'];
            $results[$key]['into_money'] = $value['into_money'];
            $results[$key]['quantities'] = 1;

            //get tax value
            $tax_rate = 0 ;
            $arr_tax = [];
            if($value['tax'] != null && $value['tax'] != '') {
                $arr_tax[] = $value['tax'];
            }

            if($value['tax2'] != null && $value['tax2'] != '') {
                $arr_tax[] = $value['tax2'];
                
            }

            foreach ($arr_tax as $tax_id) {
                $tax = $this->warehouse_model->get_taxe_value($tax_id);
                if($tax){
                    $tax_rate += (float)$tax->taxrate;              
                }
            }

            $value['tax_money'] = $value['into_money']*(float)$tax_rate/100;
            $results[$key]['tax_money'] = $value['tax_money'];
            $results[$key]['goods_money'] = $value['into_money'];

            $total_goods_money += $value['into_money'];
            $total_tax_money += $value['tax_money'];

            $sub_total = (float)$value['unit_price'] * (float)$value['quantities'];
            $results[$key]['sub_total'] = $sub_total;
            if(get_option('auto_generate_lotnumber') == 1){
                $results[$key]['lot_number'] = $this->warehouse_model->create_lot_number(false, false, $next_lot_number);
                $next_lot_number++;
            }

            if($wh_products_by_serial == 1 && $wh_serial_number_as_mandatory == 1){
                $serial_number = $this->warehouse_model->create_serial_numbers($value['quantities'], false, false, false, $next_serial_number);
                $serial_number = implode(',', $serial_number);
                $results[$key]['serial_number'] = $serial_number;
                $next_serial_number += (int)$value['quantities'];

            }
        }
        $wh_update_option_value = $this->warehouse_model->wh_update_option_value('next_serial_number', $next_serial_number);
        $this->warehouse_model->update_inventory_setting(['next_lot_number' =>  $next_lot_number]);

        $total_money = $total_goods_money + $total_tax_money;
        $value_of_inventory = $total_goods_money;



        $arr_pur_resquest['date_c']         = '';
        $arr_pur_resquest['date_add']       = '';
        $arr_pur_resquest['supplier_name']  = '';
        $arr_pur_resquest['buyer_id']       = '';
        $arr_pur_resquest['pr_order_id']    = 0;
        $arr_pur_resquest['description']    = '';
        $arr_pur_resquest['addedfrom']  = get_staff_user_id();

      

        $arr_pur_resquest['goods_receipt_detail'] = $results;
        $arr_pur_resquest['total_tax_money'] = $total_tax_money;
        $arr_pur_resquest['total_goods_money'] = $total_goods_money;
        $arr_pur_resquest['value_of_inventory'] = $value_of_inventory;
        $arr_pur_resquest['total_money'] = $total_money;
        $arr_pur_resquest['total_results'] = count($results);
        $arr_pur_resquest['currency'] = $currency;
        $arr_pur_resquest['currency_exchange_rate'] = $currency_exchange_rate;

        $status = $this->add_goods_receipt_from_auto($arr_pur_resquest);

        return $status;

        
    }

    /**
     * [add_goods_receipt_from_auto description]
     * @param [type] $data_insert [description]
     */
    public function add_goods_receipt_from_auto($data_insert)
    {
        
        $warehouse_id =  get_warehouse_option('goods_receipt_warehouse');

        $data = [];
        $data['approval'] = 1;

        if (isset($data['hot_purchase'])) {
            $hot_purchase = $data['hot_purchase'];
            unset($data['hot_purchase']);
        }

        $data['goods_receipt_code'] = $this->warehouse_model->create_goods_code();

        if(!is_null($data_insert['date_c'])){

            if(!$this->warehouse_model->check_format_date($data_insert['date_c'])){
                $data['date_c'] = to_sql_date($data_insert['date_c']);
            }else{
                $data['date_c'] = $data_insert['date_c'];
            }
        }else{
            $data['date_c'] = date("Y-m-d");
        }

        if(!is_null($data_insert['date_add']) && new_strlen($data_insert['date_add']) > 0 ){

            if(!$this->warehouse_model->check_format_date($data_insert['date_add'])){
                $data['date_add'] = to_sql_date($data_insert['date_add']);
            }else{
                $data['date_add'] = $data_insert['date_add'];
            }
        }else{
            $data['date_add'] = date("Y-m-d");
        }

        $data['addedfrom'] =  $data_insert['addedfrom'];

        $data['total_tax_money'] = reformat_currency_j($data_insert['total_tax_money']);

        $data['total_goods_money'] = reformat_currency_j($data_insert['total_goods_money']);
        $data['value_of_inventory'] = reformat_currency_j($data_insert['value_of_inventory']);

        $data['total_money'] = reformat_currency_j($data_insert['total_money']);
        $data['supplier_name'] = $data_insert['supplier_name'];
        $data['buyer_id'] = $data_insert['buyer_id'];
        $data['pr_order_id'] = $data_insert['pr_order_id'];
        $data['description'] = $data_insert['description'];
        $data['currency'] = $data_insert['currency'];
        $data['currency_exchange_rate'] = $data_insert['currency_exchange_rate'];


        $this->db->insert(db_prefix() . 'goods_receipt', $data);
        $insert_id = $this->db->insert_id();

        $results=0;

        if (isset($insert_id) && (count($data_insert['goods_receipt_detail']) > 0) ) {

            foreach ($data_insert['goods_receipt_detail'] as $purchase_key => $purchase_value) {
                if(isset($purchase_value['description'])){
                    unset($purchase_value['description']);
                }
                if(isset($purchase_value['into_money'])){
                    unset($purchase_value['into_money']);
                }

                if(isset($purchase_value['purchase_price'])){
                    unset($purchase_value['purchase_price']);
                }

                unset($purchase_value['tax2']);

                $purchase_value['warehouse_id'] = $warehouse_id;
                $purchase_value['goods_receipt_id'] = $insert_id;

                $this->db->insert(db_prefix() . 'goods_receipt_detail', $purchase_value);
                $insert_detail = $this->db->insert_id();

                $results++;

            }

            $data_log = [];
            $data_log['rel_id'] = $insert_id;
            $data_log['rel_type'] = 'stock_import';
            $data_log['staffid'] = get_staff_user_id();
            $data_log['date'] = date('Y-m-d H:i:s');
            $data_log['note'] = "stock_import";

            $this->warehouse_model->add_activity_log($data_log);

        }

        if(isset($insert_id)){
            /*update next number setting*/
            $this->warehouse_model->update_inventory_setting(['next_inventory_received_mumber' =>  get_warehouse_option('next_inventory_received_mumber')+1]);
        }

        //approval if not approval setting
        if (isset($insert_id)) {
            if ($data['approval'] == 1) {
                $this->warehouse_model->update_approve_request($insert_id, 1, 1);
            }

            return $insert_id;
        }

        return  false;


    }


    /**
     * auto_create_goods_delivery_with_auto
     * @param  integer $invoice_id 
     *              
     */
    public function auto_create_goods_delivery_with_auto($item_ids)
    {
        $get_base_currency =  get_base_currency();

        $currency = 0;
        $currency_exchange_rate = 1;
        $currency_exchange_rate_temp = 1;
        if($get_base_currency){
            $currency = $get_base_currency->id;
        }
        $currency_rate = 1;

        if(count($item_ids) > 0){

            /*get value for goods delivery*/

            $base_currency_name = get_currency_name($currency);


            $data['goods_delivery_code'] = $this->warehouse_model->create_goods_delivery_code();
            
            $data['date_c'] = '';
            $data['date_add'] = '';
            $data['customer_code']  = '';
            $data['invoice_id']     = '';
            $data['addedfrom']  = '';
            $data['description']    = '';
            $data['address']    = '';
            $data['staff_id']   = '';
            $data['invoice_no']     = '';
            $data['currency']   = $currency;
            $data['currency_exchange_rate']     = $currency_exchange_rate;

            $data['sub_total']  = 0;

            $data['total_money']    = 0;
            
            $data['after_discount']     = 0;

      
            $this->db->where('id IN ('.implode(',', $item_ids).')');
            $arr_itemable = $this->db->get(db_prefix().'items')->result_array();

            $arr_item_insert=[];
            $arr_new_item_insert=[];
            $index=0;

            if(count($arr_itemable) > 0){
                foreach ($arr_itemable as $key => $value) {
                    $commodity_code = $value['id'];
                    //get_unit_id
                    $unit_id = $value['unit_id'];
                    //get warranty
                    $warranty = $this->warehouse_model->get_warranty_from_commodity_name($value['description']);
                    $unit_price = ((float)$value['rate'] * $currency_rate) + 0;

                    if($commodity_code != 0){

                        $tax_rate = '';
                        $tax_name = '';
                        $str_tax_id = '';
                        $total_tax_rate = 0;
                        $commodity_name = wh_get_item_variatiom($commodity_code);

                        $value['qty'] = 1;
                        // TODO
                        if((float)$value['qty'] > 0){

                            $temporaty_quantity = $value['qty'];
                            $inventory_warehouse_by_commodity = $this->warehouse_model->get_inventory_warehouse_by_commodity($commodity_code);

                            if(count($inventory_warehouse_by_commodity) > 0){
                            //have serial number
                                foreach ($inventory_warehouse_by_commodity as $key => $inventory_warehouse) {
                                    if($temporaty_quantity > 0){
                                        $available_quantity = (float)$inventory_warehouse['inventory_number'];
                                        $warehouse_id = $inventory_warehouse['warehouse_id'];

                                        $temporaty_available_quantity = $available_quantity;
                                        $list_temporaty_serial_numbers = $this->warehouse_model->get_list_temporaty_serial_numbers($commodity_code, $inventory_warehouse['warehouse_id'], $value['qty']);
                                        foreach ($list_temporaty_serial_numbers as $serial_value) {

                                            if($temporaty_available_quantity > 0){
                                                $temporaty_commodity_name = $commodity_name.' SN: '.$serial_value['serial_number'];
                                                $quantities = 1;

                                                $arr_new_item_insert[$index]['commodity_name'] = $temporaty_commodity_name;
                                                $arr_new_item_insert[$index]['commodity_code'] = $commodity_code;
                                                $arr_new_item_insert[$index]['quantities'] = $quantities + 0;
                                                $arr_new_item_insert[$index]['unit_price'] = $unit_price;
                                                $arr_new_item_insert[$index]['tax_rate'] = $tax_rate;
                                                $arr_new_item_insert[$index]['tax_name'] = $tax_name;
                                                $arr_new_item_insert[$index]['tax_id'] = $str_tax_id;
                                                $arr_new_item_insert[$index]['unit_id'] = $unit_id;
                                                $arr_new_item_insert[$index]['guarantee_period'] = $warranty;
                                                $arr_new_item_insert[$index]['serial_number'] = $serial_value['serial_number'];
                                                $arr_new_item_insert[$index]['warehouse_id'] = $warehouse_id;
                                                $arr_new_item_insert[$index]['available_quantity'] = $temporaty_available_quantity;

                                                $arr_new_item_insert[$index]['total_money'] = (float)$quantities*(float)$unit_price + ((float)$total_tax_rate/100 * (float)$quantities*(float)$unit_price);
                                                $arr_new_item_insert[$index]['total_after_discount'] = (float)$quantities*(float)$unit_price + ((float)$total_tax_rate/100 * (float)$quantities*(float)$unit_price);


                                                $temporaty_quantity--;
                                                $temporaty_available_quantity--;
                                                $index ++;
                                                $inventory_warehouse_by_commodity[$key]['inventory_number'] = $temporaty_available_quantity;
                                            }
                                        }
                                    }
                                }
                                
                                
                                // don't have serial number
                                if($temporaty_quantity > 0){
                                    $quantities = $temporaty_quantity;
                                    $available_quantity = 0;

                                    foreach ($inventory_warehouse_by_commodity as $key => $inventory_warehouse) {
                                        if((float)$inventory_warehouse['inventory_number'] > 0 && $temporaty_quantity > 0){

                                            $available_quantity = (float)$inventory_warehouse['inventory_number'];
                                            $warehouse_id = $inventory_warehouse['warehouse_id'];
                                            
                                            if ($temporaty_quantity >= $inventory_warehouse['inventory_number']) {
                                                $temporaty_quantity = (float) $temporaty_quantity - (float) $inventory_warehouse['inventory_number'];
                                                $quantities = (float)$inventory_warehouse['inventory_number'];
                                            } else {
                                                $quantities = (float)$temporaty_quantity;
                                                $temporaty_quantity = 0;
                                            }

                                            $arr_new_item_insert[$index]['commodity_name'] = $commodity_name;
                                            $arr_new_item_insert[$index]['commodity_code'] = $commodity_code;
                                            $arr_new_item_insert[$index]['quantities'] = $quantities + 0;
                                            $arr_new_item_insert[$index]['unit_price'] = $unit_price;
                                            $arr_new_item_insert[$index]['tax_rate'] = $tax_rate;
                                            $arr_new_item_insert[$index]['tax_name'] = $tax_name;
                                            $arr_new_item_insert[$index]['tax_id'] = $str_tax_id;
                                            $arr_new_item_insert[$index]['unit_id'] = $unit_id;
                                            $arr_new_item_insert[$index]['guarantee_period'] = $warranty;
                                            $arr_new_item_insert[$index]['serial_number'] = '';
                                            $arr_new_item_insert[$index]['warehouse_id'] = $warehouse_id;
                                            $arr_new_item_insert[$index]['available_quantity'] = $available_quantity;

                                            $arr_new_item_insert[$index]['total_money'] = (float)$quantities*(float)$unit_price + ((float)$total_tax_rate/100 * (float)$quantities*(float)$unit_price);
                                            $arr_new_item_insert[$index]['total_after_discount'] = (float)$quantities*(float)$unit_price + ((float)$total_tax_rate/100 * (float)$quantities*(float)$unit_price);

                                            $index ++;
                                        }
                                    }
                                }

                            }else{
                                // check is product is service
                                $get_commodity = $this->warehouse_model->get_commodity($commodity_code);
                                if($get_commodity && $get_commodity->without_checking_warehouse == 1){

                                    $quantities = $temporaty_quantity;
                                // invoice with service item

                                    $available_quantity = 0;
                                    $warehouse_id = '';



                                    $arr_new_item_insert[$index]['commodity_name'] = $commodity_name;
                                    $arr_new_item_insert[$index]['commodity_code'] = $commodity_code;
                                    $arr_new_item_insert[$index]['quantities'] = $quantities + 0;
                                    $arr_new_item_insert[$index]['unit_price'] = $unit_price;
                                    $arr_new_item_insert[$index]['tax_rate'] = $tax_rate;
                                    $arr_new_item_insert[$index]['tax_name'] = $tax_name;
                                    $arr_new_item_insert[$index]['tax_id'] = $str_tax_id;
                                    $arr_new_item_insert[$index]['unit_id'] = $unit_id;
                                    $arr_new_item_insert[$index]['guarantee_period'] = $warranty;
                                    $arr_new_item_insert[$index]['serial_number'] = '';
                                    $arr_new_item_insert[$index]['warehouse_id'] = $warehouse_id;
                                    $arr_new_item_insert[$index]['available_quantity'] = $available_quantity;

                                    $arr_new_item_insert[$index]['total_money'] = (float)$quantities*(float)$unit_price + ((float)$total_tax_rate/100 * (float)$quantities*(float)$unit_price);
                                    $arr_new_item_insert[$index]['total_after_discount'] = (float)$quantities*(float)$unit_price + ((float)$total_tax_rate/100 * (float)$quantities*(float)$unit_price);

                                    $index ++;  
                                }

                            }

                        }

                    }


                }
            }

            $data_insert=[];

            $data_insert['goods_delivery'] = $data;
            $data_insert['goods_delivery_detail'] = $arr_new_item_insert;

            if(count($arr_new_item_insert) == 0){
                return false;
            }
            
           
            $status = $this->add_goods_delivery_from_auto($data_insert);

            if($status){
                return true;
            }else{
                return false;
            }

        }

        return false;

    }


     /**
     * add goods delivery from invoice
     * @param array $data_insert 
     */
    public function add_goods_delivery_from_auto($data_insert)
    {
        $results=0;
        $flag_export_warehouse = 1;


        $this->db->insert(db_prefix() . 'goods_delivery', $data_insert['goods_delivery']);
        $insert_id = $this->db->insert_id();


        if (isset($insert_id)) {

            foreach ($data_insert['goods_delivery_detail'] as $delivery_detail_key => $delivery_detail_value) {
                /*check export warehouse*/

                //checking Do not save the quantity of inventory with item
                if($this->warehouse_model->check_item_without_checking_warehouse($delivery_detail_value['commodity_code']) == true){

                    $inventory = $this->warehouse_model->get_inventory_by_commodity($delivery_detail_value['commodity_code']);

                    if($inventory){
                        $inventory_number =  $inventory->inventory_number;

                        if((float)$inventory_number < (float)$delivery_detail_value['quantities'] ){
                            $flag_export_warehouse = 0;
                        }

                    }else{
                        $flag_export_warehouse = 0;
                    }

                }


                $delivery_detail_value['goods_delivery_id'] = $insert_id;
                $this->db->insert(db_prefix() . 'goods_delivery_detail', $delivery_detail_value);
                $insert_detail = $this->db->insert_id();

                $results++;

            }
            $data_log = [];
            $data_log['rel_id'] = $insert_id;
            $data_log['rel_type'] = 'stock_export';
            $data_log['staffid'] = get_staff_user_id();
            $data_log['date'] = date('Y-m-d H:i:s');
            $data_log['note'] = "stock_export";

            $this->warehouse_model->add_activity_log($data_log);

            /*update next number setting*/
            $this->warehouse_model->update_inventory_setting(['next_inventory_delivery_mumber' =>  get_warehouse_option('next_inventory_delivery_mumber')+1]);
            
        }


        //check inventory warehouse => export warehouse
        if($flag_export_warehouse == 1){
            //update approval
            $data_update['approval'] = 1;
            $this->db->where('id', $insert_id);
            $this->db->update(db_prefix() . 'goods_delivery', $data_update);

            if(isset($data_insert['goods_delivery']['customer_code']) && is_numeric($data_insert['goods_delivery']['customer_code'])){
                // create_shipment_from_delivery_note
                $this->warehouse_model->create_shipment_from_delivery_note($insert_id);
            }


            if(is_numeric($data_insert['goods_delivery']['customer_code'])){
                $this->warehouse_model->warehouse_check_update_shipment_when_delivery_note_approval($insert_id);
            }

            //update shipment when delivery note approval
            $this->warehouse_model->check_update_shipment_when_delivery_note_approval($insert_id);

            //update history stock, inventoty manage after staff approved
            $goods_delivery_detail = $this->warehouse_model->get_goods_delivery_detail($insert_id);

            foreach ($goods_delivery_detail as $goods_delivery_detail_value) {
                // add goods transaction detail (log) after update invetory number
                // 
                // check Without checking warehouse

                if($this->warehouse_model->check_item_without_checking_warehouse($goods_delivery_detail_value['commodity_code']) == true){
                    // $this->add_inventory_manage($goods_delivery_detail_value, 2);
                    $this->warehouse_model->add_inventory_from_invoices($goods_delivery_detail_value);
                }

            }
        }

        if(isset($insert_id)){
            return $insert_id;
        }

        return false;


    }

    /**
     * [get_proposal_templates description]
     * @return [type] [description]
     */
    public function get_proposal_templates(){
        $this->load->model('proposals_model');

        $proposals = $this->proposals_model->get('', ['status' => 6]);

        $rs = [];

        foreach($proposals as $proposal){
            $proposal['proposal_number'] = format_proposal_number($proposal['id']);
            $rs[] = $proposal;
        }

        return $rs;
    }

    /**
     * [get_estimate_templates description]
     * @return [type] [description]
     */
    public function get_estimate_templates(){
        $this->load->model('estimates_model');

        $estimates = $this->estimates_model->get('', ['status' => 1]);

        $rs = [];

        foreach($estimates as $estimate){
            $estimate['estimate_number'] = format_estimate_number($estimate['id']);
            $rs[] = $estimate;
        }

        return $rs;
    }

    /**
     * [get_invoice_templates description]
     * @return [type] [description]
     */
    public function get_invoice_templates(){
        $this->load->model('invoices_model');

        $invoices = $this->invoices_model->get('', ['status' => Invoices_model::STATUS_DRAFT]);

        $rs = [];

        foreach($invoices as $invoice){
            $invoice['invoice_number'] = format_invoice_number($invoice['id']);
            $rs[] = $invoice;
        }

        return $rs;
    }

    /**
     * [get_manufacturing_order_templates description]
     * @return [type] [description]
     */
    public function get_manufacturing_order_templates(){


        $this->db->where('status', 'draft');
        $manufacturing_orders = $this->db->get(db_prefix().'mrp_manufacturing_orders')->result_array();

        return $manufacturing_orders;
    }

    /**
     * [get_purchase_request_templates description]
     * @return [type] [description]
     */
    public function get_purchase_request_templates(){
        $this->db->where('status', 1);
        $purchase_request = $this->db->get(db_prefix().'pur_request')->result_array();

        return $purchase_request;
    }

       /**
     * [get_purchase_order_templates description]
     * @return [type] [description]
     */
    public function get_purchase_order_templates(){
        $this->db->where('approve_status', 1);
        $purchase_orders = $this->db->get(db_prefix().'pur_orders')->result_array();

        return $purchase_orders;
    }

    /**
     * [get_manual_order_templates description]
     * @return [type] [description]
     */
    public function get_manual_order_templates(){
        $this->db->where('channel', 'manual');
        $this->db->where('status', 0);

        $cart = $this->db->get(db_prefix().'cart')->result_array();

        return $cart;
    }


       /**
     * [get_inventory_receiving_voucher_templates description]
     * @return [type] [description]
     */
    public function get_inventory_receiving_voucher_templates(){
        $this->db->where('approval', 0);
        $receiving_voucher = $this->db->get(db_prefix().'goods_receipt')->result_array();

        return $receiving_voucher;
    }

      /**
     * [get_inventory_delivery_voucher_templates description]
     * @return [type] [description]
     */
    public function get_inventory_delivery_voucher_templates(){
        $this->db->where('approval', 0);
        $delivery_voucher = $this->db->get(db_prefix().'goods_delivery')->result_array();

        return $delivery_voucher;
    }

    /**
     * 
     */
    public function handle_default_action_node($data){

        switch ($data['node']['data']['action']) {
            case 'create_task_default':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');
                
                $this->load->model('tasks_model');

                if(!isset($data['node']['data']['task_template']) || !is_numeric($data['node']['data']['task_template'])){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }
                
                $task_template = $this->get_task_template($data['node']['data']['task_template']);
                if(!isset($task_template->id)){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);

                    return false;
                }

                $task_data = [];

                $task_data['name'] = $task_template->task_subject;
                $task_data['hourly_rate'] = '';
                $task_data['startdate'] = $task_template->start_date;
                $task_data['duedate'] = $task_template->due_date;
                $task_data['priority'] = $task_template->priority;
                $task_data['rel_type'] = $task_template->rel_type;
                $task_data['rel_id'] = null;
                $task_data['assignees'] = ($task_template->assignees != '' ? explode(',', $task_template->assignees) : []); 
                $task_data['followers'] = ($task_template->followers != '' ? explode(',', $task_template->followers) : []); 
                $task_data['created_by_workflow'] = 1;

                $task_id = $this->tasks_model->add($task_data);
                if($task_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'task';
                    $log_data['action_relsult_id'] = $task_id;

                    $this->save_action_log($log_data);

                    return true;
                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'task';
                $log_data['action_relsult_id'] = 0;

                $this->save_action_log($log_data);

                return false;

            break;

            case 'send_email_default':
                $content = $data['node']['data']['send_email_content'];
                $send_to = $data['node']['data']['send_email_to'];

                $sent = $this->sent_email_action($content, $send_to);

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if($sent){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'send_mail';
                    $log_data['action_relsult_id'] = 0;

                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'send_mail';
                $log_data['action_relsult_id'] = 0;
                $this->save_action_log($log_data);
                return false;

            break;

            case 'create_proposal_default':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($data['node']['data']['proposal_template']) || $data['node']['data']['proposal_template'] == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_proposal_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $this->load->model('proposals_model');
                $proposal_id = $this->proposals_model->copy($data['node']['data']['proposal_template']);
                if($proposal_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'proposal';
                    $log_data['action_relsult_id'] = $proposal_id;
                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_proposal_default';
                $log_data['action_relsult_id'] = 0;
                $this->save_action_log($log_data);
                return false;

            break;

            case 'create_estimate_default':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($data['node']['data']['estimate_template']) || $data['node']['data']['estimate_template'] == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_estimate_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $this->load->model('estimates_model');
                $estimate_id = $this->estimates_model->copy($data['node']['data']['estimate_template']);
                if($estimate_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'estimate';
                    $log_data['action_relsult_id'] = $estimate_id;
                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_estimate_default';
                $log_data['action_relsult_id'] = 0;
                $this->save_action_log($log_data);
                return false;
            break;

            case 'create_invoice_default':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!isset($data['node']['data']['invoice_template']) || $data['node']['data']['invoice_template'] == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_invoice_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $this->load->model('invoices_model');
                $invoice_id = $this->invoices_model->copy($data['node']['data']['invoice_template']);
                if($invoice_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'invoice';
                    $log_data['action_relsult_id'] = $invoice_id;
                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_invoice_default';
                $log_data['action_relsult_id'] = 0;
                $this->save_action_log($log_data);
                return false;
            break;

            case 'create_purchase_request_default':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!wa_get_status_modules('purchase')){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_purchase_request_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                if(!isset($data['node']['data']['purchase_request_template']) || $data['node']['data']['purchase_request_template'] == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_purchase_request_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $pr_id = $this->create_purchase_request_default($data['node']['data']['purchase_request_template'], $data['workflow_id']);
                if($pr_id){
                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'purchase_request';
                    $log_data['action_relsult_id'] = $pr_id;
                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_purchase_request_default';
                $log_data['action_relsult_id'] = 0;
                $this->save_action_log($log_data);
                return false;
            break;

            case 'create_purchase_order_default':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!wa_get_status_modules('purchase')){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_purchase_order_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                if(!isset($data['node']['data']['purchase_order_template']) || $data['node']['data']['purchase_order_template'] == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_purchase_order_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $po_id = $this->create_purchase_order_default($data['node']['data']['purchase_order_template'], $data['workflow_id']);
                if($po_id){
                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'purchase_order';
                    $log_data['action_relsult_id'] = $po_id;
                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_purchase_order_default';
                $log_data['action_relsult_id'] = 0;
                $this->save_action_log($log_data);
                return false;
            break;

            case 'create_manufacturing_order_default':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!wa_get_status_modules('manufacturing')){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_manufacturing_order_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                if(!isset($data['node']['data']['manufacturing_order_template']) || $data['node']['data']['manufacturing_order_template'] == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_manufacturing_order_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $order_id = $this->create_manufacturing_order_default($data['node']['data']['manufacturing_order_template'], $data['workflow_id']);
                if($order_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'manufacturing_order';
                    $log_data['action_relsult_id'] = $order_id;
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_manufacturing_order_default';
                $log_data['action_relsult_id'] = 0;
                $this->save_action_log($log_data);
                return false;

            break;

            case 'create_manual_order_default':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!wa_get_status_modules('omni_sales')){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_manual_order_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                if(!isset($data['node']['data']['manual_order_template']) || $data['node']['data']['manual_order_template'] == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_manufacturing_order_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $order_id = $this->create_manual_order_default($data['node']['data']['manual_order_template'], $data['workflow_id']);
                if($order_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'manual_order';
                    $log_data['action_relsult_id'] = $order_id;
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_manual_order_default';
                $log_data['action_relsult_id'] = 0;
                $this->save_action_log($log_data);
                return false;


            break;

            case 'create_inventory_receiving_voucher_default':

                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!wa_get_status_modules('warehouse')){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_inventory_receiving_voucher_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                if(!isset($data['node']['data']['inventory_receiving_voucher_template']) || $data['node']['data']['inventory_receiving_voucher_template'] == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_inventory_receiving_voucher_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $item_ids = [];

                $success = $this->create_inventory_receiving_voucher_default($data['node']['data']['inventory_receiving_voucher_template'], $data['workflow_id']);
                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'inventory_receiving_voucher';
                    $log_data['action_relsult_id'] = $success;
                    $this->save_action_log($log_data);
                    return true;

                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_inventory_receiving_voucher_default';
                $log_data['action_relsult_id'] = 0;
                $this->save_action_log($log_data);
                return false;

            break;

            case 'create_inventory_delivery_voucher_default':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!wa_get_status_modules('warehouse')){

                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_inventory_delivery_voucher_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                if(!isset($data['node']['data']['inventory_delivery_voucher_template']) || $data['node']['data']['inventory_delivery_voucher_template'] == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_inventory_delivery_voucher_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $item_ids = [];

                $success = $this->create_inventory_delivery_voucher_default($data['node']['data']['inventory_delivery_voucher_template'], $data['workflow_id']);
                if($success){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'inventory_delivery_voucher';
                    $log_data['action_relsult_id'] = $success;
                    $this->save_action_log($log_data);
                    return true;

                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_inventory_delivery_voucher_default';
                $log_data['action_relsult_id'] = 0;
                $this->save_action_log($log_data);
                return false;
            break;

            case 'assign_manager_default':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!wa_get_status_modules('hr_profile')){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'assign_manager_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                if(!isset($data['node']['data']['team_manage']) || $data['node']['data']['team_manage'] == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'assign_manager_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }


                $this->db->where('flow_id', $data['workflow_id']);
                $this->db->where('rel_type',  $data['rel_type']);
                $this->db->where('action_relsult_type',  'staff');
                $this->db->where('action_relsult',  'success');

                $log = $this->db->get(db_prefix().'wa_action_logs')->row();

                $staffid = 0;
                if(isset($log->action_relsult_id)){
                    $staffid = $log->action_relsult_id;
                }

                if($data['rel_type'] == 'staff'){
                    $staffid = $data['rel_id'];
                }

                if($staffid == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'assign_manager_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $this->db->where('staffid', $staffid);
                $this->db->update(db_prefix().'staff', ['team_manage' => $data['node']['data']['team_manage']]);
                if($this->db->affected_rows() > 0){
                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'assign_manager_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'assign_manager_default';
                $log_data['action_relsult_id'] = 0;
                $this->save_action_log($log_data);
                return false;
            break;

            case 'create_hr_contract_default':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!wa_get_status_modules('hr_profile')){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_hr_contract_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $this->db->where('flow_id', $data['workflow_id']);
                $this->db->where('rel_type',  $data['rel_type']);
                $this->db->where('action_relsult_type',  'staff');
                $this->db->where('action_relsult',  'success');

                $log = $this->db->get(db_prefix().'wa_action_logs')->row();

                $staffid = 0;
                if(isset($log->action_relsult_id)){
                    $staffid = $log->action_relsult_id;
                }

                if($data['rel_type'] == 'staff'){
                    $staffid = $data['rel_id'];
                }

                if($staffid == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_hr_contract_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $insert_id = $this->create_hr_contract_default($staffid, $data['workflow_id']);
                if($insert_id){

                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'staff_contract';
                    $log_data['action_relsult_id'] = $insert_id;
                    $this->save_action_log($log_data);
                    return true;
                }

                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_hr_contract_default';
                $log_data['action_relsult_id'] = 0;
                $this->save_action_log($log_data);
                return false;

            break;

            case 'create_training_default':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!wa_get_status_modules('hr_profile')){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_training_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $this->db->where('flow_id', $data['workflow_id']);
                $this->db->where('rel_type',  $data['rel_type']);
                $this->db->where('action_relsult_type',  'staff');
                $this->db->where('action_relsult',  'success');

                $log = $this->db->get(db_prefix().'wa_action_logs')->row();

                $staffid = 0;
                if(isset($log->action_relsult_id)){
                    $staffid = $log->action_relsult_id;
                }

                if($data['rel_type'] == 'staff'){
                    $staffid = $data['rel_id'];
                }


                if($staffid == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_training_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }
                $training_data = [];
                $training_data['staff_id'] = $staffid;
                $training_data['training_programs_name'] = 'Training programs created by workflow automation module';
                $training_data['training_places'] = get_option('invoice_company_name');
                $training_data['training_time_from'] = date('Y-m-d H:i:s');

                $this->load->model('hr_profile/hr_profile_model');

                $insert_id = $this->hr_profile_model->add_education($training_data);

                if($insert_id){
                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'staff_training';
                    $log_data['action_relsult_id'] = $insert_id;
                    $this->save_action_log($log_data);

                    return true;
                }

                return false;

            break;

            case 'create_onboarding_default':
                $log_data = [];
                $log_data['flow_id'] = $data['workflow_id'];
                $log_data['node_id'] = $data['node']['id'];
                $log_data['action'] = $data['node']['data']['action'];
                $log_data['rel_type'] = $data['rel_type'];
                $log_data['rel_id'] = $data['rel_id'];
                $log_data['created_at'] = date('Y-m-d H:i:s');

                if(!wa_get_status_modules('hr_profile')){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_onboarding_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $this->db->where('flow_id', $data['workflow_id']);
                $this->db->where('rel_type',  $data['rel_type']);
                $this->db->where('action_relsult_type',  'staff');
                $this->db->where('action_relsult',  'success');

                $log = $this->db->get(db_prefix().'wa_action_logs')->row();

                $staffid = 0;
                if(isset($log->action_relsult_id)){
                    $staffid = $log->action_relsult_id;
                }

                if($data['rel_type'] == 'staff'){
                    $staffid = $data['rel_id'];
                }

                $this->load->model('hr_profile/hr_profile_model');

                if($staffid == 0){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_onboarding_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $list_staff = $this->hr_profile_model->get_staff_info_id($staffid);
                if(!isset($list_staff->staffid)){
                    $log_data['action_relsult'] = 'fail';
                    $log_data['action_relsult_type'] = 'create_onboarding_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);
                    return false;
                }

                $data_rec_tranfer['staffid'] = $list_staff->staffid;
                $data_rec_tranfer['firstname'] = isset($list_staff->firstname) ? $list_staff->firstname : '';
                $data_rec_tranfer['birthday'] = isset($list_staff->birthday) ? $list_staff->birthday : '';
                $data_rec_tranfer['staffidentifi'] = isset($list_staff->staffidentifi) ? $list_staff->staffidentifi : '';

                $insert_id = $this->hr_profile_model->add_rec_transfer_records($data_rec_tranfer);


                if($insert_id){

                    $list_reception_staff_asset = $this->hr_profile_model->get_setting_asset_allocation();
                    $group_checklist = $this->hr_profile_model->group_checklist();
                    $setting_training = $this->hr_profile_model->get_setting_training();


                    $info_data = [];
                    foreach($group_checklist as $key => $group){
                        $info_data['title_name'][$key] = $group['group_name'];

                        $checklists = $this->hr_profile_model->checklist_by_group($group['id']);
                        foreach($checklists as $_key => $checklist){
                            $info_data['sub_title_name'][$key][$_key] = $checklist['name'];
                        }

                    }

                    $this->hr_profile_model->add_manage_info_reception_for_staff($staffid, $info_data);


                    $asset_data = [];
                    foreach ($list_reception_staff_asset as $p_key => $p_value) {     
                        $asset_data[] = ['name' => $p_value['name']];
                    }

                    $this->hr_profile_model->add_asset_staff($staffid, $asset_data);


                    if ($list_staff->job_position != '') {

                        $get_list_training_program = $this->hr_profile_model->get_list_training_program($list_staff->job_position, $setting_training->training_type);

                        if(isset($get_list_training_program[0])){
                            $jp_interview_training = $this->hr_profile_model->get_job_position_training_de($get_list_training_program[0]['training_process_id']);
                 
                            if ($jp_interview_training) {
                                $this->hr_profile_model->add_training_staff($jp_interview_training, $list_staff->staffid);
                                if (isset($list_staff->email)) {
                                    if ($list_staff->email != '') {
                                        $this->send_training_staff($list_staff->email, $list_staff->job_position, $setting_training->training_type, $jp_interview_training->position_training_id, $list_staff->staffid);
                                    }
                                }
                            }
                        }
                    }


                    $log_data['action_relsult'] = 'success';
                    $log_data['action_relsult_type'] = 'create_onboarding_default';
                    $log_data['action_relsult_id'] = 0;
                    $this->save_action_log($log_data);

                    return true;
                }


                $log_data['action_relsult'] = 'fail';
                $log_data['action_relsult_type'] = 'create_onboarding_default';
                $log_data['action_relsult_id'] = 0;
                $this->save_action_log($log_data);

                return false;
            break;

            default:
                return false;
            break;
        }
    }

    /**
     * [create_hr_contract_default description]
     * @return [type] [description]
     */
    public function create_hr_contract_default($staffid, $workflow_id){
        if(!wa_get_status_modules('hr_profile')){
            return false;
        }

        $this->load->model('hr_profile/hr_profile_model');

        $workflow = $this->get_workflow($workflow_id);

        if(!isset($workflow->id)){
            return false;
        }

        $contract_data = [];

        $contract_data['contract_code'] = $this->hr_profile_model->create_code('staff_contract_code');
        $contract_data['staff'] = $staffid;

        $contract_type = $this->hr_profile_model->get_contracttype();

        if(!is_array($contract_type) || count($contract_type) == 0){
            return false;
        }

        $contract_data['name_contract'] = $contract_type[0]['name_contracttype'];
        $contract_data['start_valid'] = date('Y-m-d');
        $contract_data['end_valid'] = '';
        $contract_data['hourly_or_month'] = 'month';
        $contract_data['staff_contract_hs'] = '[[null,null,null,null,null]]';
        $contract_data['sign_day'] = date('Y-m-d');
        $contract_data['staff_delegate'] = '';

        $insert_id = $this->hr_profile_model->add_contract($contract_data);


        return $insert_id;

    }

    /**
     * [create_inventory_delivery_voucher_default description]
     * @return [type] [description]
     */
    public function create_inventory_delivery_voucher_default($delivery_id, $workflow_id){
        if(!wa_get_status_modules('warehouse')){
            return false;
        }

        $this->load->model('warehouse/warehouse_model');

        $delivery_voucher = $this->warehouse_model->get_goods_delivery($delivery_id);
        $delivery_voucher_detail = $this->warehouse_model->get_goods_delivery_detail($delivery_id);
        $workflow = $this->get_workflow($workflow_id);

        if(!isset($delivery_voucher->id) || !isset($workflow->id)){
            return false;
        }

        $voucher_data = [];
        $voucher_data['id'] = '';
        $voucher_data['edit_approval'] = '';
        $voucher_data['save_and_send_request'] = 'false';
        $voucher_data['additional_discount'] = $delivery_voucher->additional_discount;
        $voucher_data['currency'] = '0';
        $voucher_data['currency_exchange_rate'] = '0';
        $voucher_data['date_c'] = $delivery_voucher->date_c;
        $voucher_data['date_add'] = $delivery_voucher->date_add;
        $voucher_data['pr_order_id'] = $delivery_voucher->pr_order_id;
        $voucher_data['invoice_id'] = $delivery_voucher->invoice_id;
        $voucher_data['customer_code'] = $delivery_voucher->customer_code;
        $voucher_data['to_'] = $delivery_voucher->to_;
        $voucher_data['address'] = $delivery_voucher->address;
        $voucher_data['project'] = $delivery_voucher->project;
        $voucher_data['type'] = $delivery_voucher->type;
        $voucher_data['department'] = $delivery_voucher->department;
        $voucher_data['requester'] = $delivery_voucher->requester;
        $voucher_data['warehouse_id'] = $delivery_voucher->warehouse_id;
        $voucher_data['staff_id'] = $delivery_voucher->staff_id;
        $voucher_data['invoice_no'] = $delivery_voucher->invoice_no;
        $voucher_data['item_select'] = '';
        $voucher_data['commodity_name'] = '';
        $voucher_data['note'] = '';
        $voucher_data['available_quantity'] = '';
        $voucher_data['unit_name'] = '';
        $voucher_data['quantities'] = '';
        $voucher_data['guarantee_period'] = '';
        $voucher_data['unit_price'] = '';
        $voucher_data['discount'] = '';
        $voucher_data['commodity_code'] = '';
        $voucher_data['unit_id'] = '';
        $voucher_data['discount_money'] = '';
        $voucher_data['total_after_discount'] = '';
        $voucher_data['serial_number'] = '';
        $voucher_data['without_checking_warehouse'] = '';

        foreach($delivery_voucher_detail as $key => $detail){

            $voucher_data['newitems'][$key]['order'] = $key;
            $voucher_data['newitems'][$key]['id'] = '';
            $voucher_data['newitems'][$key]['commodity_name'] = $detail['commodity_name'];
            $voucher_data['newitems'][$key]['warehouse_id'] = $detail['warehouse_id'];
            $voucher_data['newitems'][$key]['note'] = $detail['note'];
            $voucher_data['newitems'][$key]['available_quantity'] = $detail['available_quantity'];
            $voucher_data['newitems'][$key]['quantities'] = $detail['quantities'];
            $voucher_data['newitems'][$key]['guarantee_period'] = $detail['guarantee_period'];
            $voucher_data['newitems'][$key]['unit_price'] = $detail['unit_price'];
            $voucher_data['newitems'][$key]['discount'] = $detail['discount'];
            $voucher_data['newitems'][$key]['commodity_code'] = $detail['commodity_code'];
            $voucher_data['newitems'][$key]['discount_money'] = $detail['discount_money'];
            $voucher_data['newitems'][$key]['total_after_discount'] = $detail['total_after_discount'];
            $voucher_data['newitems'][$key]['unit_id'] = $detail['unit_id'];
            $voucher_data['newitems'][$key]['serial_number'] = $detail['serial_number'];
            $voucher_data['newitems'][$key]['without_checking_warehouse'] = 0;
        }

        $voucher_data['after_discount'] = $delivery_voucher->after_discount;
        $voucher_data['total_discount'] = $delivery_voucher->total_discount;
        $voucher_data['total_money'] = $delivery_voucher->total_money;
       

        $insert_id = $this->warehouse_model->add_goods_delivery($voucher_data);

        return $insert_id;

    }

    /**
     * [create_inventory_receiving_voucher_default description]
     * @param  [type] $receiving_id [description]
     * @param  [type] $workflow_id  [description]
     * @return [type]               [description]
     */
    public function create_inventory_receiving_voucher_default($receiving_id, $workflow_id){
        if(!wa_get_status_modules('warehouse')){
            return false;
        }

        $this->load->model('warehouse/warehouse_model');

        $receiving_voucher = $this->warehouse_model->get_goods_receipt($receiving_id);
        $receiving_voucher_detail = $this->warehouse_model->get_goods_receipt_detail($receiving_id);
        $workflow = $this->get_workflow($workflow_id);

        if(!isset($receiving_voucher->id) || !isset($workflow->id)){
            return false;
        }

        $voucher_data = [];
        $voucher_data['id'] = '';
        $voucher_data['save_and_send_request'] = 'false';
        $voucher_data['currency'] = $receiving_voucher->currency;
        $voucher_data['currency_exchange_rate'] = $receiving_voucher->currency_exchange_rate;
        $voucher_data['date_c'] = $receiving_voucher->date_c;
        $voucher_data['date_add'] = $receiving_voucher->date_add;
        $voucher_data['pr_order_id'] = $receiving_voucher->pr_order_id;
        $voucher_data['supplier_code'] = $receiving_voucher->supplier_code;
        $voucher_data['supplier_name'] = $receiving_voucher->supplier_name;
        $voucher_data['buyer_id'] = $receiving_voucher->buyer_id;
        $voucher_data['project'] = $receiving_voucher->project;
        $voucher_data['type'] = $receiving_voucher->type;
        $voucher_data['department'] = $receiving_voucher->department;
        $voucher_data['requester'] = $receiving_voucher->requester;
        $voucher_data['deliver_name'] = $receiving_voucher->deliver_name;
        $voucher_data['warehouse_id_m'] = $receiving_voucher->warehouse_id;
        $voucher_data['expiry_date_m'] = $receiving_voucher->expiry_date;
        $voucher_data['invoice_no'] = $receiving_voucher->invoice_no;
        $voucher_data['item_select'] = '';
        $voucher_data['commodity_name'] = '';
        $voucher_data['warehouse_id'] = '';
        $voucher_data['note'] = '';
        $voucher_data['quantities'] = '';
        $voucher_data['unit_name'] = '';
        $voucher_data['unit_price'] = '';
        $voucher_data['lot_number'] = '';
        $voucher_data['date_manufacture'] = '';
        $voucher_data['expiry_date'] = '';
        $voucher_data['commodity_code'] = '';
        $voucher_data['unit_id'] = '';
        $voucher_data['serial_number'] = '';
        $voucher_data['newitems'] = [];

        foreach($receiving_voucher_detail as $key => $detail){
            $voucher_data['newitems'][$key]['order'] = $key;
            $voucher_data['newitems'][$key]['id'] = '';
            $voucher_data['newitems'][$key]['commodity_name'] = $detail['commodity_name'];
            $voucher_data['newitems'][$key]['warehouse_id'] = $detail['warehouse_id'];
            $voucher_data['newitems'][$key]['note'] = $detail['note'];
            $voucher_data['newitems'][$key]['quantities'] = $detail['quantities'];
            $voucher_data['newitems'][$key]['unit_price'] = $detail['unit_price'];
            $voucher_data['newitems'][$key]['lot_number'] = $detail['lot_number'];
            $voucher_data['newitems'][$key]['date_manufacture'] = $detail['date_manufacture'];
            $voucher_data['newitems'][$key]['expiry_date'] = $detail['expiry_date'];
            $voucher_data['newitems'][$key]['commodity_code'] = $detail['commodity_code'];
            $voucher_data['newitems'][$key]['unit_id'] = $detail['unit_id'];
            $voucher_data['newitems'][$key]['serial_number'] = $detail['serial_number'];

        }

        $voucher_data['total_goods_money'] = $receiving_voucher->total_goods_money;
        $voucher_data['value_of_inventory'] = $receiving_voucher->value_of_inventory;
        $voucher_data['total_tax_money'] = $receiving_voucher->total_tax_money;
        $voucher_data['total_money'] = $receiving_voucher->total_money;
        $voucher_data['description'] = $receiving_voucher->description;

        $insert_id = $this->warehouse_model->add_goods_receipt($voucher_data);
        
        return $insert_id;
        

    }

    /**
     * [create_manual_order_default description]
     * @return [type] [description]
     */
    public function create_manual_order_default($order_id, $workflow_id){
        if(!wa_get_status_modules('omni_sales')){
            return false;
        }


        $this->load->model('omni_sales/omni_sales_model');
        $cart = $this->omni_sales_model->get_cart($order_id);
        $cart_detail = $this->omni_sales_model->get_cart_detailt_by_master($order_id);
        $workflow = $this->get_workflow($workflow_id);

        if(!isset($cart->id) || !isset($workflow->id)){
            return false;
        }

        $order_data = [];
        $order_data['estimate_id'] = '';
        $order_data['customer'] = $cart->userid;
        $order_data['payment_methods'] = $cart->allowed_payment_modes;
        $order_data['discount_type'] = $cart->discount_type_str;
        $order_data['currency'] = $cart->currency;
        $order_data['sale_agent'] = $cart->seller;
        $order_data['note'] = $cart->staff_note;
        $order_data['product_id'] = '';
        $order_data['product_id'] = 0;
        $order_data['description'] = '';
        $order_data['long_description'] = '';
        $order_data['quantity'] = 1;
        $order_data['rate'] = 0;
        $order_data['taxid'] = 0;
        $order_data['taxrate'] = '';
        $order_data['tax'] = '';
        $order_data['subtotal'] = $cart->sub_total;
        $order_data['total'] = $cart->total;
        $order_data['discount'] = $cart->discount;
        $order_data['add_discount'] = $cart->add_discount;
        $order_data['add_discount_type'] = $cart->discount_type;
        $order_data['adjustment'] = $cart->adjustment;
        $order_data['shipping_value'] = $cart->shipping_value;
        $order_data['shipping_form'] = $cart->shipping_form;
        $order_data['client_note'] = $cart->notes;
        $order_data['terms'] = $cart->terms;

        $order_data['newitems'] = [];
        foreach($cart_detail as $key => $item){
            $order_data['newitems'][$key]['order'] = $key;
            $order_data['newitems'][$key]['id'] = '';
            $order_data['newitems'][$key]['product_id'] = $item['product_id'];
            $order_data['newitems'][$key]['description'] = $item['product_name'];
            $order_data['newitems'][$key]['long_description'] = $item['long_description'];
            $order_data['newitems'][$key]['qty'] = $item['quantity'];
            $order_data['newitems'][$key]['rate'] = $item['prices'];
            $order_data['newitems'][$key]['taxname'] = $item['tax_name'];
            $order_data['newitems'][$key]['tax_rate'] = $item['tax_rate'];
            if($cart->discount_type == 2){
                $order_data['newitems'][$key]['discount'] = $item['prices_discount'];    
            }else{
                $order_data['newitems'][$key]['discount'] = $item['percent_discount'];
            }
        }

        $insert_id = $this->omni_sales_model->create_new_order($order_data);


        return $insert_id;

    }

    /**
     * [create_manufacturing_order_default description]
     * @return [type] [description]
     */
    public function create_manufacturing_order_default($order_id, $workflow_id){
        if(!wa_get_status_modules('manufacturing')){
            return false;
        }
        $this->load->model('manufacturing/manufacturing_model');
        $manufacturing_order = $this->manufacturing_model->get_manufacturing_order($order_id);

        $workflow = $this->get_workflow($workflow_id);

        if(!isset($manufacturing_order['manufacturing_order']->id) || !isset($workflow->id)){
            return false;
        }

        $order_data = [];
        $order_data['product_id'] = $manufacturing_order['manufacturing_order']->product_id;
        $order_data['date_deadline'] = $manufacturing_order['manufacturing_order']->date_deadline;
        $order_data['product_qty'] = $manufacturing_order['manufacturing_order']->product_qty;
        $order_data['date_plan_from'] = $manufacturing_order['manufacturing_order']->date_plan_from;
        $order_data['unit_id'] = $manufacturing_order['manufacturing_order']->unit_id;
        $order_data['staff_id'] = $manufacturing_order['manufacturing_order']->staff_id;
        $order_data['bom_id'] = $manufacturing_order['manufacturing_order']->bom_id;
        $order_data['manufacturing_order_code'] = $this->manufacturing_model->create_code('mo_code');
        $order_data['routing_id'] = $manufacturing_order['manufacturing_order']->routing_id;

        $order_data['product_tab_hs'] = '[';
        if(isset($manufacturing_order['manufacturing_order_detail']) && is_array($manufacturing_order['manufacturing_order_detail'])){
            foreach($manufacturing_order['manufacturing_order_detail'] as $key => $detail){
                $order_data['product_tab_hs'] .= '[0';
                $order_data['product_tab_hs'] .= ','.$detail['product_id'];
                $order_data['product_tab_hs'] .= ','.$detail['unit_id'];
                $order_data['product_tab_hs'] .= ','.$detail['qty_to_consume'];
                $order_data['product_tab_hs'] .= ','.$detail['qty_reserved'];
                if(($key+1) < count($manufacturing_order['manufacturing_order_detail'])){
                    $order_data['product_tab_hs'] .= ','.$detail['qty_done'].'],';
                }else{
                    $order_data['product_tab_hs'] .= ','.$detail['qty_done'].']';
                }
            }
        }

        $order_data['product_tab_hs'] .= ']';

        $order_data['components_warehouse_id'] = '';
        $order_data['finished_products_warehouse_id'] = $manufacturing_order['manufacturing_order']->finished_products_warehouse_id;

        $insert_id = $this->manufacturing_model->add_manufacturing_order($order_data);

        return $insert_id;

    }

    /**
     * [create_purchase_order_default description]
     * @return [type] [description]
     */
    public function create_purchase_order_default($purchase_order_id, $workflow_id){

        if(!wa_get_status_modules('purchase')){
            return false;
        }

        $this->load->model('purchase/purchase_model');
        $pur_order = $this->purchase_model->get_pur_order($purchase_order_id, $workflow_id);
        $purchase_order_detail = $this->purchase_model->get_pur_order_detail($purchase_order_id, $workflow_id);

        $workflow = $this->get_workflow($workflow_id);

        if(!isset($pur_order->id) || !isset($workflow->id)){
            return false;
        }

        if(isset($pur_order->id)){
            
            $po_data = [];

            $prefix = get_purchase_option('pur_order_prefix');
            $next_number = get_purchase_option('next_po_number');

            $pur_order_number = (isset($pur_order) ? $pur_order->pur_order_number : $prefix.'-'.str_pad($next_number,5,'0',STR_PAD_LEFT).'-'.date('M-Y'));      
            if(get_option('po_only_prefix_and_number') == 1){
                $pur_order_number = (isset($pur_order) ? $pur_order->pur_order_number : $prefix.'-'.str_pad($next_number,5,'0',STR_PAD_LEFT));
            }


            $po_data['pur_order_name'] = 'PO Created by Workflow Automation - '.$workflow->name;
            $po_data['number'] = $next_number;
            $po_data['pur_order_number'] = $pur_order_number;
            $po_data['vendor'] = $pur_order->vendor;
           
            $po_data['department'] = '';
            $po_data['project'] = '';
            $po_data['type'] = '';
            $po_data['currency'] = $pur_order->currency;
            $po_data['tags'] = '';
            $po_data['order_date'] = date('Y-m-d');
            $po_data['buyer'] = $pur_order->buyer;
            $po_data['sale_invoice'] = '';
            $po_data['days_owed'] = '';
            $po_data['delivery_date'] = '';
            $po_data['discount_type'] = $pur_order->discount_type;
            $po_data['shipping_address'] = get_option('pur_company_address');
            $po_data['shipping_zip'] = get_option('pur_company_zipcode');
            $po_data['shipping_city'] = get_option('pur_company_city');
            $po_data['shipping_state'] = get_option('pur_company_state');
            $po_data['shipping_country_text'] = get_option('pur_company_country_text');
            $po_data['shipping_country'] = get_option('pur_company_country_code');
            $po_data['from_currency'] = $pur_order->from_currency;
            $po_data['currency_rate'] = $pur_order->currency_rate;
            $po_data['total_mn'] = $pur_order->subtotal;
            $po_data['add_discount_type'] = 'amount';
            $po_data['order_discount'] = 0;
            $po_data['dc_total'] = 0;
            $po_data['shipping_fee'] = $pur_order->shipping_fee;
            $po_data['grand_total'] = $pur_order->total;
            $po_data['vendornote'] = get_purchase_option('vendor_note');
            $po_data['terms'] = get_purchase_option('terms_and_conditions');

            $po_data['newitems'] = [];

            if(is_array($purchase_order_detail) && count($purchase_order_detail) > 0){
                foreach($purchase_order_detail as $key => $item){

                    $_item                     = $this->purchase_model->get_item_v2($item['item_code']);
                    $long_description = '';
                    if(isset($_item)){
                        $long_description   = nl2br($_item->long_description ?? '');
                    }

                    $item_dt = [];
                    $item_dt['order'] =  ($key+1);
                    $item_dt['item_name'] = $item['item_name'];
                    $item_dt['item_description'] = $long_description;
                    $item_dt['unit_price'] = $item['unit_price'];
                    $item_dt['quantity'] = $item['quantity'];
                    $item_dt['tax_value'] = $item['tax_value'];
                    $item_dt['discount'] = $item['discount_%'];
                    $item_dt['discount_money'] = $item['discount_money'];
                    $item_dt['item_code'] = $item['item_code'];
                    $item_dt['unit_id'] = $item['unit_id'];
                    $item_dt['total'] = $item['total'];
                    $item_dt['total_money'] = $item['total_money'];
                    $item_dt['into_money'] = $item['into_money'];

                    $po_data['newitems'][] = $item_dt;

                }
            }

            return $this->purchase_model->add_pur_order($po_data);

        }
        return false;
    }

    /**
     * [create_purchase_request_default description]
     * @return [type] [description]
     */
    public function create_purchase_request_default($purchase_request_id, $workflow_id){
        if(!wa_get_status_modules('purchase')){
            return false;
        }

        $this->load->model('purchase/purchase_model');

        $purchase_request = $this->purchase_model->get_purchase_request($purchase_request_id);
        $workflow = $this->get_workflow($workflow_id);

        if(!isset($purchase_request->id) || !isset($workflow->id)){
            return false;
        }

        $get_base_currency = get_base_currency();
        $currency = 0;
        if($get_base_currency){
            $currency = $get_base_currency->id;
        }

        $purchase_request_detail = $this->purchase_model->get_pur_request_detail($purchase_request_id);
        

        $mo_detail=[];

        $arr_product_id=[];
        foreach ($purchase_request_detail as $key => $mo_value) {
            $arr_product_id[] = $mo_value['item_code'];
        }

        $this->db->where('id IN ('.implode(",",$arr_product_id) .')');
        $products = $this->db->get(db_prefix() . 'items')->result_array();

        $arr_products=[];
        foreach ($products as $product) {
            $arr_products[$product['id']] = $product;
        }

        $pu_subtotal=0;
        $pu_total_tax=0;
        $pu_total=0;

        foreach ($purchase_request_detail as $key => $mo_value) {

            $tax_select = [];

            $pu_qty = $mo_value['quantity'];

            $unit_price = isset($arr_products[$mo_value['item_code']]) ? (float)$arr_products[$mo_value['item_code']]['purchase_price'] : 0;
            $list_taxrate = isset($arr_products[$mo_value['item_code']]) ? $arr_products[$mo_value['item_code']]['supplier_taxes_id'] : '';

            $taxrate= 0 ;
            $tax_id='';
            if(new_strlen($list_taxrate) > 0){
                $array_taxrate = new_explode(',', $list_taxrate);
                $this->load->model('taxes_model');
                foreach ($array_taxrate as $taxrate_id) {
                    $tax = $this->taxes_model->get($taxrate_id);
                    if($tax){
                        $taxrate += (float)$tax->taxrate;
                        $tax_select[] = $tax->name.'|'.$tax->taxrate;
                    }
                }

            }

            $tax_value = (float)$unit_price*$pu_qty*$taxrate/100;
            $into_money = (float)$unit_price*$pu_qty;
            $total = (float)$unit_price*$pu_qty+$tax_value;

            $pu_total_tax += $tax_value;
            $pu_subtotal += $into_money;
            $pu_total += $total;
            $item_text = isset(pur_get_commodity_name($mo_value['item_code'])->description) ? pur_get_commodity_name($mo_value['item_code'])->description : '';

            array_push($mo_detail, [
                'item_code' => $mo_value['item_code'],
                'unit_id' => $mo_value['unit_id'],
                'unit_price' => $unit_price,
                'quantity' => $pu_qty,
                'into_money' => $into_money,
                'tax' => $tax_id,
                'tax_value' => $tax_value, 
                'total' => $total,
                'inventory_quantity' => 0,
                'item_text' => $item_text,
                'tax_select' => $tax_select,
            ]);
            
        }

        $prefix = get_purchase_option('pur_request_prefix');

        $staff_departments = $this->departments_model->get_staff_departments(get_staff_user_id(), true);
        if(count($staff_departments) > 0){
            $data['department'] = $staff_departments[0];
        }else{
            $staff_departments = $this->departments_model->get();
            if(count($staff_departments) > 0){
                $data['department'] = $staff_departments[0]['departmentid'];
            }else{
                $data['department'] = 0;
            }
        }

        $dpm_name = department_pur_request_name($data['department']);

        $purchase_data=[];
        $purchase_data['number'] = get_purchase_option('next_pr_number');
        $purchase_data['pur_rq_code'] =  $prefix.'-'.str_pad($purchase_data['number'],5,'0',STR_PAD_LEFT).'-'.date('M-Y').'-'.$dpm_name;
        $purchase_data['pur_rq_name'] =  'PUR create from Workflow Automation '.$workflow->name;
        $purchase_data['project'] =  '';
        $purchase_data['type'] =  '';
        $purchase_data['sale_invoice'] =  '';
        $purchase_data['requester'] =  get_staff_user_id();
        $purchase_data['from_items'] =  '1';
        $purchase_data['rq_description'] =  _l('this_puchase_request_create_from_workflow_automation_module').$workflow->name;
        $purchase_data['subtotal'] =  $pu_subtotal;
        $purchase_data['total_mn'] =  $pu_total;
        $purchase_data['department'] =  $data['department'];
        $purchase_data['currency'] =  $currency;
        $purchase_data['from_currency'] =  $currency;
        $purchase_data['currency_rate'] =  1;

        $request_detail_temp=[];
        foreach ($mo_detail as $mo_detail_value) {
            $request_detail_temp[] = [
                'item_code' => $mo_detail_value['item_code'],
                'unit_id' => $mo_detail_value['unit_id'],
                'unit_price' => $mo_detail_value['unit_price'],
                'into_money' => $mo_detail_value['into_money'],
                'total' => $mo_detail_value['total'],
                'tax_value' => $mo_detail_value['tax_value'],
                'item_text' => $mo_detail_value['item_text'] ?? '',
                'quantity' => $mo_detail_value['quantity'],
                'tax_select' => $mo_detail_value['tax_select'],
            ];
        }

        $purchase_data['newitems'] = $request_detail_temp;

        //add purchase request
        $pur_request_id = $this->purchase_model->add_pur_request($purchase_data);
        if($pur_request_id){
            return $pur_request_id;
        }

        return false;
    }
}