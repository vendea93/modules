<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Workflow_automation Controller
 */
class Workflow_automation extends AdminController {

	/**
	 * Constructs a new instance.
	 */
	public function __construct() {
		parent::__construct();
		$this->load->model('Workflow_automation_model');
        hooks()->do_action('workflow_automation_init');
	}

	/**
	 * [workflow description]
	 * @return [type] [description]
	 */
	public function workflow(){
        wa_token();
		$data['title'] = _l('wa_workflow');

		if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('workflow_automation', 'workflow/table_workflows'));
        }

        $data['categories'] = $this->Workflow_automation_model->get_categories();

		$this->load->view('workflow/manage', $data);

	}

	/**
	 * [workflow_form description]
	 * @return [type] [description]
	 */
	public function workflow_form(){
		if($this->input->post()){
			$data = $this->input->post();

			if($data['workflow_id'] == ''){
				unset($data['workflow_id']);
				$workflow_id = $this->Workflow_automation_model->add_workflow($data);

				if($workflow_id){
					set_alert('success', _l('added_successfully'));
				}
			}else{

				$workflow_id = $data['workflow_id'];
				unset($data['workflow_id']);
				$success = $this->Workflow_automation_model->update_workflow($data, $workflow_id);
				if($success){
					set_alert('success', _l('updated_successfully'));
				}
			}

		}

		redirect(admin_url('workflow_automation/workflow'));
	}

	/**
	 * [delete_workflow description]
	 * @return [type] [description]
	 */
	public function delete_workflow($id){
		if(!has_permission('wa_workflows', '', 'delete')){
			access_denied('workflow');
		}

		$success = $this->Workflow_automation_model->delete_workflow($id);

		if($success){
			set_alert('success', _l('deleted_successfully'));
		}

		redirect(admin_url('workflow_automation/workflow'));
	}


    /**
     * [workflow_detail description]
     * @return [type] [description]
     */
    public function workflow_detail($id){
        wa_token();
        $data['title'] = _l('wa_workflow_detail');

        $data['workflow'] = $this->Workflow_automation_model->get_workflow($id);

        if(!isset($data['workflow']->id)){
            show_404();
        }

        if(!has_permission('workflow_automation', '', 'view') && !has_permission('workflow_automation', '', 'view_own') ){
            access_denied('workflow_detail');
        }

        if(!has_permission('workflow_automation', '', 'view')){
            if($data['workflow']->private == 1 && $data['workflow']->created_by != get_staff_user_id()){
                access_denied('workflow_detail');
            }
        }

        $data['history_logs'] = $this->Workflow_automation_model->get_history_logs($id);
        
        $this->load->view('workflow/workflow_detail', $data);
    }

    /**
     * [workflow_builder description]
     * @return [type] [description]
     */
    public function workflow_builder($id){
        wa_token();
        $data['workflow'] = $this->Workflow_automation_model->get_workflow($id);

        if(!isset($data['workflow']->id)){
            show_404();
        }

        if(!has_permission('workflow_automation', '', 'view') && !has_permission('workflow_automation', '', 'view_own') ){
            access_denied('workflow_builder');
        }

        if(!has_permission('workflow_automation', '', 'view')){
            if($data['workflow']->private == 1 && $data['workflow']->created_by != get_staff_user_id()){
                access_denied('workflow_builder');
            }
        }

        $data['title'] = _l('wa_workflow_builder');

        $data['is_edit'] = true;
        
        $this->load->view('workflow/workflow_builder', $data);
    }


    /**
     * get workflow node html
     * @return view
     */
    public function get_workflow_node_html($workflow_id = ''){
        $data = $this->input->post();

        $data['workflow'] = $this->Workflow_automation_model->get_workflow($workflow_id);

        switch ($data['type']) {
            case 'flow_start':
                break;
            case 'action':

                break;

            case 'condition':

                break;
            case 'break':

                break;
            default:

                break;
        }


        $this->load->view('workflow/workflow_node/'.$data['type'], $data);
    }


    /**
     * [get_condition_for_check description]
     * @return [type] [description]
     */
    public function get_condition_for_check(){

    	$html = '<option value=""></option>';
    	if($this->input->post()){
    		$data = $this->input->post();

    		if($data['check'] != '' && $data['workflow_type'] == 'tasks'){
    			if($data['check'] == 'addedfrom' || $data['check'] == 'status' || $data['check'] == 'priority'){
    				$html .= '<option value="equal">'._l('wa_equal').'</option>';
    				$html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
    			}else if($data['check'] == 'startdate' || $data['check'] == 'duedate' || $data['check'] == 'datefinished'){
    				$html .= '<option value="equal">'._l('wa_equal').'</option>';
    				$html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
    				$html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
    				$html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
    				$html .= '<option value="less_than">'._l('wa_less_than').'</option>';
    				$html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
    			}
    		}elseif($data['check'] != '' && $data['workflow_type'] == 'projects'){
                if($data['check'] == 'addedfrom' || $data['check'] == 'status' || $data['check'] == 'client'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }else if($data['check'] == 'startdate' || $data['check'] == 'duedate' || $data['check'] == 'datefinished'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'contracts'){
                if($data['check'] == 'addedfrom' || $data['check'] == 'project' || $data['check'] == 'client'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }else if($data['check'] == 'startdate' || $data['check'] == 'duedate'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'leads'){
               
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                
            }elseif($data['check'] != '' && $data['workflow_type'] == 'customers'){
               
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                
            }elseif($data['check'] != '' && $data['workflow_type'] == 'proposals'){
                if($data['check'] == 'status' || $data['check'] == 'customer' || $data['check'] == 'project' || $data['check'] == 'lead'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }else if($data['check'] == 'total_value'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }
                
            }elseif($data['check'] != '' && $data['workflow_type'] == 'estimates'){
                if($data['check'] == 'status' || $data['check'] == 'customer' || $data['check'] == 'project'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }else if($data['check'] == 'total_value'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }
                
            }elseif($data['check'] != '' && $data['workflow_type'] == 'invoices'){
                if($data['check'] == 'status' || $data['check'] == 'customer' || $data['check'] == 'project'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }else if($data['check'] == 'total_value'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }
                
            }elseif($data['check'] != '' && $data['workflow_type'] == 'credit_notes'){
                if($data['check'] == 'status' || $data['check'] == 'customer' || $data['check'] == 'project'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }else if($data['check'] == 'total_value' || $data['check'] == 'remaining_amount'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }
                
            }elseif($data['check'] != '' && $data['workflow_type'] == 'payment'){
                if( $data['check'] == 'customer' || $data['check'] == 'invoice_status' || $data['check'] = 'payment_method'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }
                
            }elseif($data['check'] != '' && $data['workflow_type'] == 'purchase_order'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'purchase_request'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'purchase_quotation'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'purchase_invoice'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'purchase_contract'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'vendor'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'ticket'){
                if($data['check'] == 'customer' || $data['check'] == 'summary' || $data['check'] == 'priority' || $data['check'] == 'status' || $data['check'] == 'type'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }else if($data['check'] == 'customer_satisfaction' || $data['check'] == 'tickets_violating_kpi' || $data['check'] == 'tickets_violating_sla' || $data['check'] == 'tickets_resolved' || $data['check'] == 'tickets_unresolved'){

                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';

                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'staff'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'expense'){
                if($data['check'] != 'amount'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }else{

                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';

                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'subscription'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';

            }elseif($data['check'] != '' && ($data['workflow_type'] == 'recruitment_plan' || $data['workflow_type'] == 'recruitment_campaign' || $data['workflow_type'] == 'recruitment_form')){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'candidate'){
                if($data['check'] != 'rating_score'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }else{
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'interview_schedule'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'leave_request'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'additional_work_hours'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'attendance'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'vehicle'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'workperformance'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'event'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'work_order'){
                if($data['check'] != 'price'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }else{
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'booking'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'fuel'){
                if($data['check'] != 'price'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }else{
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'work_center'){
                if($data['check'] == 'working_hours'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }else{
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'routing'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'operation'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'bill_of_material'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'bom_component'){
                if($data['check'] == 'product_qty'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }else{

                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'manufacturing_order'){
                if($data['check'] == 'product_qty'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }else{

                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'omni_sales_order'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'omni_sales_refund'){
                if($data['check'] == 'amount'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }else{
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }
            }elseif($data['check'] != '' && ($data['workflow_type'] == 'trade_discount' || $data['workflow_type'] == 'voucher')){
               
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                
            }elseif($data['check'] != '' && ($data['workflow_type'] == 'asset' || $data['workflow_type'] == 'license' || $data['workflow_type'] == 'accessories' || $data['workflow_type'] == 'consumable' || $data['workflow_type'] == 'component')){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && ($data['workflow_type'] == 'payslip' || $data['workflow_type'] == 'payslip_template')){
                $html .= '<option value="enable">'._l('wa_enable').'</option>';
                $html .= '<option value="disable">'._l('wa_disable').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'items'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
            }elseif($data['check'] != '' && $data['workflow_type'] == 'opening_stock'){
                $html .= '<option value="equal">'._l('wa_equal').'</option>';
                $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
            }elseif($data['check'] != '' && ($data['workflow_type'] == 'inventory_receiving_voucher' || $data['workflow_type'] == 'inventory_delivery_voucher' || $data['workflow_type'] == 'packing_list' || $data['workflow_type'] == 'inventory_delivery_note') ){
                if($data['check'] != 'number_of_items'){
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                }else{
                    $html .= '<option value="equal">'._l('wa_equal').'</option>';
                    $html .= '<option value="not_equal">'._l('wa_not_equal').'</option>';
                    $html .= '<option value="greater_than">'._l('wa_greater_than').'</option>';
                    $html .= '<option value="greater_than_or_equal">'._l('wa_greater_than_or_equal').'</option>';
                    $html .= '<option value="less_than">'._l('wa_less_than').'</option>';
                    $html .= '<option value="less_than_or_equal">'._l('wa_less_than_or_equal').'</option>';
                }
            }

    	}


    	echo json_encode([
    		'condition_option' => $html,
    	]);

    }

    /**
     * [get_variable_condition_for_check description]
     * @return [type] [description]
     */
    public function get_variable_condition_for_check(){
    	$html = '';
    	if($this->input->post()){
    		$data = $this->input->post();
    		if($data['check'] != '' && $data['workflow_type'] == 'tasks'){
    			if($data['check'] == 'addedfrom' ){

    				$this->load->model('staff_model');
    				$staff = $this->staff_model->get('', ['active' => 1]);

    				$html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
    			}elseif ($data['check'] == 'status') {
    				$this->load->model('tasks_model');
    				$task_statuses = $this->tasks_model->get_statuses();

    				$html .= render_select('condition_variable['.$data['nodeid'].']', $task_statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
    			}elseif ($data['check'] == 'priority'){

    				$priorities = get_tasks_priorities();
    				$html .= render_select('condition_variable['.$data['nodeid'].']', $priorities, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
    			}elseif($data['check'] == 'startdate' || $data['check'] == 'duedate' || $data['check'] == 'datefinished'){

    				$html .= render_date_input('condition_variable['.$data['nodeid'].']', '', '', ['df-condition_variable'=> '', 'placeholder' => _l('default_is_the_workflow_start_date'), 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => _l('wa_default_date_note')]);

    			}

    		}elseif($data['check'] != '' && $data['workflow_type'] == 'projects'){
                if($data['check'] == 'addedfrom' ){

                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif ($data['check'] == 'status') {
                    $this->load->model('projects_model');
                    $project_statuses = $this->projects_model->get_project_statuses();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $project_statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'startdate' || $data['check'] == 'duedate'){

                    $html .= render_date_input('condition_variable['.$data['nodeid'].']', '', '', ['df-condition_variable'=> '', 'placeholder' => _l('default_is_the_workflow_start_date'), 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => _l('wa_default_date_note')]);

                }elseif ($data['check'] == 'client') {
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'contracts'){
                if($data['check'] == 'addedfrom' ){

                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'startdate' || $data['check'] == 'duedate'){

                    $html .= render_date_input('condition_variable['.$data['nodeid'].']', '', '', ['df-condition_variable'=> '', 'placeholder' => _l('default_is_the_workflow_start_date'), 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => _l('wa_default_date_note')]);

                }elseif ($data['check'] == 'client') {
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'project'){
                    $this->load->model('projects_model');
                    $projects = $this->projects_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $projects, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'leads'){
                if($data['check'] == 'addedfrom' ){

                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'assign_to_staff' ){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'status' ){
                    $this->load->model('leads_model');
                    $status = $this->leads_model->get_status();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'source' ){
                    $this->load->model('leads_model');
                    $source = $this->leads_model->get_source();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $source, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'customers'){
                if($data['check'] == 'addedfrom' ){

                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'assign_to_staff' ){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'proposals'){

                if($data['check'] == 'status' ){
                    $this->load->model('proposals_model');
                    $statuses = $this->proposals_model->get_statuses();

                    $status = [];

                    foreach($statuses as $st_id){
                        $status[] = ['id' => $st_id, 'name' => format_proposal_status($st_id)];
                    }

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }else if($data['check'] == 'project'){
                    $this->load->model('projects_model');
                    $projects = $this->projects_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $projects, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'customer'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'lead'){
                    $this->load->model('leads_model');
                    $leads = $this->leads_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $leads, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'total_value'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                } 

            }else if($data['check'] != '' && $data['workflow_type'] == 'estimates'){

                if($data['check'] == 'status' ){
                    $this->load->model('estimates_model');
                    $statuses = $this->estimates_model->get_statuses();

                    $status = [];

                    foreach($statuses as $st_id){
                        $status[] = ['id' => $st_id, 'name' => format_estimate_status($st_id, '', false)];
                    }

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }else if($data['check'] == 'project'){
                    $this->load->model('projects_model');
                    $projects = $this->projects_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $projects, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'customer'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'total_value'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                } 

            }else if($data['check'] != '' && $data['workflow_type'] == 'invoices'){

                if($data['check'] == 'status' ){
                    $this->load->model('invoices_model');
                    $statuses = $this->invoices_model->get_statuses();

                    $status = [];

                    foreach($statuses as $st_id){
                        $status[] = ['id' => $st_id, 'name' => format_invoice_status($st_id, '', false)];
                    }

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }else if($data['check'] == 'project'){
                    $this->load->model('projects_model');
                    $projects = $this->projects_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $projects, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'customer'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'total_value'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '', '','number', ['df-condition_variable'=> '']);
                } 

            }else if($data['check'] != '' && $data['workflow_type'] == 'credit_notes'){

                if($data['check'] == 'status' ){
                    $this->load->model('credit_notes_model');
                    $statuses = $this->credit_notes_model->get_statuses();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }else if($data['check'] == 'customer'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'total_value'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }else if($data['check'] == 'remaining_amount'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }

            }else if($data['check'] != '' && $data['workflow_type'] == 'payment'){

                if($data['check'] == 'invoice_status' ){
                    $this->load->model('invoices_model');
                    $statuses = $this->invoices_model->get_statuses();

                    $status = [];

                    foreach($statuses as $st_id){
                        $status[] = ['id' => $st_id, 'name' => format_invoice_status($st_id, '', false)];
                    }

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }else if($data['check'] == 'customer'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'paymentmode'){
                    $this->load->model('payment_modes_model');
                    $payment_methods = $this->payment_modes_model->get();
                    $html .= render_select('condition_variable['.$data['nodeid'].']', $payment_methods, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                } 

            }else if($data['check'] != '' && $data['workflow_type'] == 'purchase_order'){
                if($data['check'] == 'approval_status'){
                    $status = [];

                    $status[] = ['id' => 1, 'name' => _l('purchase_draft')];
                    $status[] = ['id' => 2, 'name' => _l('purchase_approved')];
                    $status[] = ['id' => 3, 'name' => _l('pur_rejected')];
                    $status[] = ['id' => 4, 'name' => _l('pur_canceled')];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'order_status'){

                    $status = [];

                    $status[] = ['id' => 'new', 'name' => _l('new_order')];
                    $status[] = ['id' => 'delivered', 'name' => _l('wa_delivered')];
                    $status[] = ['id' => 'confirmed', 'name' => _l('wa_confirmed')];
                    $status[] = ['id' => 'cancelled', 'name' => _l('wa_cancelled')];
                    $status[] = ['id' => 'return', 'name' => _l('wa_return')];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }else if($data['check'] == 'vendor'){
                    if(wa_get_status_modules('purchase')){
                        $this->load->model('purchase/purchase_model');

                        $vendors = $this->purchase_model->get_vendor();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $vendors, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['check'] == 'department'){
                    $this->load->model('departments_model');
                    $departments = $this->departments_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $departments, ['departmentid', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                 
                }else if($data['check'] == 'type'){
                    $types = [
                        ['id' => 'capex', 'name' => 'CAPEX'],
                        ['id' => 'opex', 'name' => 'OPEX'],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                 
                }else if($data['check'] == 'person_in_charge'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'purchase_request'){
                if($data['check'] == 'approval_status'){
                    $status = [];

                    $status[] = ['id' => 1, 'name' => _l('purchase_draft')];
                    $status[] = ['id' => 2, 'name' => _l('purchase_approved')];
                    $status[] = ['id' => 3, 'name' => _l('pur_rejected')];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'department'){
                    $this->load->model('departments_model');
                    $departments = $this->departments_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $departments, ['departmentid', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                 
                }else if($data['check'] == 'project'){
                    $this->load->model('projects_model');
                    $projects = $this->projects_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $projects, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                 
                }else if($data['check'] == 'type'){
                    $types = [
                        ['id' => 'capex', 'name' => 'CAPEX'],
                        ['id' => 'opex', 'name' => 'OPEX'],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                 
                }else if($data['check'] == 'requestor'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'purchase_quotation'){
                if($data['check'] == 'approval_status'){
                    $status = [];

                    $status[] = ['id' => 1, 'name' => _l('purchase_draft')];
                    $status[] = ['id' => 2, 'name' => _l('purchase_approved')];
                    $status[] = ['id' => 3, 'name' => _l('pur_rejected')];


                    $html .= render_select('condition_variable['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'vendor'){
                    if(wa_get_status_modules('purchase')){
                        $this->load->model('purchase/purchase_model');

                        $vendors = $this->purchase_model->get_vendor();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $vendors, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['check'] == 'buyer'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'purchase_invoice'){
                if($data['check'] == 'approval_status'){
                    $status = [];

                    $status[] = ['id' => 1, 'name' => _l('purchase_draft')];
                    $status[] = ['id' => 2, 'name' => _l('purchase_approved')];
                    $status[] = ['id' => 3, 'name' => _l('pur_rejected')];
                    $status[] = ['id' => 4, 'name' => _l('pur_canceled')];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'vendor'){
                    if(wa_get_status_modules('purchase')){
                        $this->load->model('purchase/purchase_model');

                        $vendors = $this->purchase_model->get_vendor();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $vendors, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['check'] == 'buyer'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'purchase_contract'){
                if($data['check'] == 'service_category'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'text', ['df-condition_variable'=> '']);
                }else if($data['check'] == 'contract_value'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }else if($data['check'] == 'vendor'){
                    if(wa_get_status_modules('purchase')){
                        $this->load->model('purchase/purchase_model');

                        $vendors = $this->purchase_model->get_vendor();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $vendors, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['check'] == 'addedfrom'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'department'){
                    $this->load->model('departments_model');
                    $departments = $this->departments_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $departments, ['departmentid', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                 
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'vendor'){
                if($data['check'] == 'country'){
                    $countries = get_all_countries();
                    $html .= render_select('condition_variable['.$data['nodeid'].']', $countries, [ 'country_id', [ 'short_name']], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }else if($data['check'] == 'category'){
                    if(wa_get_status_modules('purchase')){
                        $this->load->model('purchase/purchase_model');

                        $vendor_categoriess = $this->purchase_model->get_vendor_category();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $vendor_categoriess, ['id', 'category_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['check'] == 'city'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'text', ['df-condition_variable'=> '']);
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'ticket'){
                if($data['check'] == 'summary'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'text', ['df-condition_variable'=> '']);
                }else if($data['check'] == 'priority'){

                    if(wa_get_status_modules('customer_service')){
                        $priorities = cs_priority();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $priorities, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }

                }else if($data['check'] == 'status'){
                    if(wa_get_status_modules('customer_service')){
                        $statuses = cs_ticket_status();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['check'] == 'type'){
                    if(wa_get_status_modules('customer_service')){
                        $types = cs_ticket_type();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['check'] == 'customer'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'customer_satisfaction'){
                     $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }else if($data['check'] == 'tickets_violating_kpi'){
                     $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }else if($data['check'] == 'tickets_violating_sla'){
                     $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }else if($data['check'] == 'tickets_resolved'){
                     $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }else if($data['check'] == 'tickets_unresolved'){
                     $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'staff'){
                if($data['check'] == 'contract_status'){
                    $statuses = [
                        ['id' => 'draft', 'name' => _l('draft')],
                        ['id' => 'valid', 'name' => _l('valid')],
                        ['id' => 'inactivity', 'name' => _l('invalid')],
                        ['id' => 'finish', 'name' => _l('finish')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'contract_sign_status'){
                    $statuses = [
                        ['id' => 'signed', 'name' => _l('signed')],
                        ['id' => 'not_sign', 'name' => _l('not_sign')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'status'){
                    $statuses = [
                        ['id' => 'working', 'name' => _l('wa_working')],
                        ['id' => 'maternity_leave', 'name' => _l('maternity_leave')],
                        ['id' => 'inactivity', 'name' => _l('inactivity')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'role'){

                    $this->load->model('roles_model');
                    $roles = $this->roles_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $roles, ['roleid', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'department'){
                    $this->load->model('departments_model');
                    $departments = $this->departments_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $departments, ['departmentid', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'job_position'){
                    if(wa_get_status_modules('hr_profile')){

                        $this->load->model('hr_profile/hr_profile_model');
                        $positions = $this->hr_profile_model->get_job_position();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $positions, ['position_id', 'position_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }

                }elseif($data['check'] == 'gender'){
                   $genders = [
                        ['id' => 'male', 'name' => _l('male')],
                        ['id' => 'female', 'name' => _l('female')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $genders, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);     
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'expense'){
                if($data['check'] == 'amount'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }elseif($data['check'] == 'category'){
                    $this->load->model('expenses_model');
                    $categories = $this->expenses_model->get_category();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $categories, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'customer'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'convert_to_invoice_status'){
                    $statuses = [
                        ['id' => 'converted', 'name' => _l('converted')],
                        ['id' => 'not_converted', 'name' => _l('not_converted')],
                    ];


                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'payment_mode'){
                    $this->load->model('payment_modes_model');
                    $modes = $this->payment_modes_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $modes, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'is_billable'){
                    $statuses = [
                        ['id' => 'yes', 'name' => _l('wa_yes')],
                        ['id' => 'no', 'name' => _l('wa_no')],
                    ];


                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'vendor'){
                    if(wa_get_status_modules('purchase')){
                        $this->load->model('purchase/purchase_model');

                        $vendors = $this->purchase_model->get_vendor();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $vendors, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'subscription'){
                if($data['check'] == 'status'){

                    $statuses            = get_subscriptions_statuses();
                    $list_st = [];

                    $list_st[] = ['id' => 'not_subscribed', 'name' => _l('not_subscribed')];
                    foreach ($statuses as $status) {
                        $list_st[] = ['id' => $status['id'], 'name' => _l($status['id'])];
                    }

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $list_st, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'clientid'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'recruitment_plan'){

                if($data['check'] == 'status'){

                    $statuses = [
                        ['id' => 1, 'name' => _l('_proposal')],
                        ['id' => 2, 'name' => _l('approved')],
                        ['id' => 4, 'name' => _l('reject')],
                    ];


                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'department'){
                    $this->load->model('departments_model');
                    $departments = $this->departments_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $departments, ['departmentid', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'position'){
                    if(wa_get_status_modules('recruitment')){
                        $this->load->model('recruitment/recruitment_model');
                        $positions = $this->recruitment_model->get_job_position();

                         $html .= render_select('condition_variable['.$data['nodeid'].']', $positions, ['position_id', 'position_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }

            }else if($data['check'] != '' && $data['workflow_type'] == 'recruitment_campaign'){
                if($data['check'] == 'status'){
                    $statuses = [
                        ['id' => 1, 'name' => _l('planning')],
                        ['id' => 2, 'name' => _l('overdue')],
                        ['id' => 3, 'name' => _l('in_progress')],
                        ['id' => 4, 'name' => _l('finish')],
                        ['id' => 5, 'name' => _l('cancel')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }elseif($data['check'] == 'department'){
                    $this->load->model('departments_model');
                    $departments = $this->departments_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $departments, ['departmentid', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'position'){
                    if(wa_get_status_modules('recruitment')){
                        $this->load->model('recruitment/recruitment_model');
                        $positions = $this->recruitment_model->get_job_position();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $positions, ['position_id', 'position_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'company'){
                    if(wa_get_status_modules('recruitment')){
                        $this->load->model('recruitment/recruitment_model');
                        $companies = $this->recruitment_model->get_company();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $companies, ['id', 'company_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }

                }elseif($data['check'] == 'manager'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'recruitment_form'){
                if($data['check'] == 'status'){
                    $statuses = [
                        '1' => ['id' => '1', 'name' => _l('application')],
                        '2' => ['id' => '2', 'name' => _l('potential')],
                        '3' => ['id' => '3', 'name' => _l('interview')],
                        '4' => ['id' => '4', 'name' => _l('won_interview')],
                        '5' => ['id' => '5', 'name' => _l('send_offer')],
                        '6' => ['id' => '6', 'name' => _l('elect')],
                        '7' => ['id' => '7', 'name' => _l('non_elect')],
                        '8' => ['id' => '8', 'name' => _l('unanswer')],
                        '9' => ['id' => '9', 'name' => _l('transferred')],
                        '11' => ['id' => '10', 'name' => _l('preliminary_selection')],
                    ];
                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }elseif($data['check'] == 'responsible'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'language'){
                    $languages = $this->app->get_available_languages();

                    $langs = [];
                    foreach($languages as $language){
                        $langs[] = ['id' => $language, 'name' => ucfirst($language)];
                    }

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $langs, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }
            }else if($data['check'] != '' && $data['workflow_type'] == 'candidate'){
                if($data['check'] == 'status'){

                    $statuses = [
                        '1' => ['id' => '1', 'name' => _l('application')],
                        '2' => ['id' => '2', 'name' => _l('potential')],
                        '3' => ['id' => '3', 'name' => _l('interview')],
                        '4' => ['id' => '4', 'name' => _l('won_interview')],
                        '5' => ['id' => '5', 'name' => _l('send_offer')],
                        '6' => ['id' => '6', 'name' => _l('elect')],
                        '7' => ['id' => '7', 'name' => _l('non_elect')],
                        '8' => ['id' => '8', 'name' => _l('unanswer')],
                        '9' => ['id' => '9', 'name' => _l('transferred')],
                        '10' => ['id' => '10', 'name' => _l('freedom')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }elseif($data['check'] == 'desired_salary'){

                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);

                }elseif($data['check'] == 'marital_status'){
                    $statuses = [
                        '1' => ['id' => 'single', 'name' => _l('single')],
                        '2' => ['id' => 'married', 'name' => _l('married')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'campaign'){
                    if(wa_get_status_modules('recruitment')){

                        $this->load->model('recruitment/recruitment_model');
                        $campaigns = $this->recruitment_model->get_rec_campaign();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $campaigns, ['cp_id', 'campaign_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }
                }elseif($data['check'] == 'seniority'){
                    if(wa_get_status_modules('recruitment')){
                        $seniorities = rec_year_experience();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $seniorities, ['value', 'label'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }
                }elseif($data['check'] == 'age_group'){
                 

                    $groups = [
                        '1' => ['id' => '11/16', 'name' => _l('11 - 16')],
                        '2' => ['id' => '17/20', 'name' => _l('17 - 20')],
                        '3' => ['id' => '21/24', 'name' => _l('21 - 24')],
                        '4' => ['id' => '25/34', 'name' => _l('25 - 34')],
                        '5' => ['id' => '35/44', 'name' => _l('35 - 44')],
                        '6' => ['id' => '45/54', 'name' => _l('45 - 54')],
                        '7' => ['id' => '55/64', 'name' => _l('55 - 64')],
                        '8' => ['id' => '65', 'name' => _l('65+')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $groups, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }elseif($data['check'] == 'gender'){
                    $genders = [
                        ['id' => 'male', 'name' => _l('male')],
                        ['id' => 'female', 'name' => _l('female')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $genders, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }elseif($data['check'] == 'skill'){
                    if(wa_get_status_modules('recruitment')){
                        $this->load->model('recruitment/recruitment_model');
                        $skills  = $this->recruitment_model->get_skill();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $skills, ['id', 'skill_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }
                }elseif($data['check'] == 'company'){
                    if(wa_get_status_modules('recruitment')){
                        $this->load->model('recruitment/recruitment_model');
                        $companies = $this->recruitment_model->get_company();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $companies, ['id', 'company_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'rating_score'){


                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);


                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'interview_schedule'){
                if($data['check'] == 'position'){
                    if(wa_get_status_modules('recruitment')){
                        $this->load->model('recruitment/recruitment_model');
                        $positions = $this->recruitment_model->get_job_position();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $positions, ['position_id', 'position_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'interviewer'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'leave_request'){
                if($data['check'] == 'status'){

                    $statuses = [
                        ['id' => ' '.'0', 'name' => _l('Create')],
                        ['id' => '1', 'name' => _l('approved')],
                        ['id' => '3', 'name' => _l('Reject')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
 
                }else if($data['check'] == 'type'){

                    $types = [
                        ['id' => '1', 'name' => _l('Leave')],
                        ['id' => '2', 'name' => _l('late')],
                        ['id' => '6', 'name' => _l('early')],
                        ['id' => '3', 'name' => _l('Go_out')],
                        ['id' => '4', 'name' => _l('Go_on_bussiness')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);


                }else if($data['check'] == 'department'){
                    $this->load->model('departments_model');
                    $departments = $this->departments_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $departments, ['departmentid', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'handover_recipients'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'additional_work_hours'){
                if($data['check'] == 'status'){

                    $statuses = [
                        ['id' => ' '.'0', 'name' => _l('status_0')],
                        ['id' => '1', 'name' => _l('status_1')],
                        ['id' => '2', 'name' => _l('status_-1')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
 
                }else if($data['check'] == 'timekeeping_type'){

                    $types = [
                        ['id' => 'W', 'name' => _l('W')],
                        ['id' => 'OT', 'name' => _l('OT')],
                       
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);


                }else if($data['check'] == 'department'){
                    $this->load->model('departments_model');
                    $departments = $this->departments_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $departments, ['departmentid', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'creator'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'attendance'){
                if($data['check'] == 'staff'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'role'){
                    $this->load->model('roles_model');
                    $roles = $this->roles_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $roles, ['roleid', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'vehicle'){
                if($data['check'] == 'status'){
                    $statuses = [
                        ['id' => 'active', 'name' => _l('active')],
                        ['id' => 'inactive', 'name' => _l('inactive')],
                        ['id' => 'in_shop', 'name' => _l('in_shop')],
                        ['id' => 'out_of_service', 'name' => _l('out_of_service')],
                        ['id' => 'sold', 'name' => _l('sold')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'vehicle_type'){
                    if(wa_get_status_modules('fleet')){
                        $this->load->model('fleet/fleet_model');
                        $types = $this->fleet_model->get_data_vehicle_types();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'ownership'){
                    $ownerships = [
                       ['id' => 'owner', 'name' => _l('owner')],
                       ['id' => 'leased', 'name' => _l('leased')],
                       ['id' => 'rented', 'name' => _l('rented')],
                       ['id' => 'customer', 'name' => _l('customer')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $ownerships, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'vehicle_group_id'){
                    if(wa_get_status_modules('fleet')){
                        $this->load->model('fleet/fleet_model');
                        $groups = $this->fleet_model->get_data_vehicle_groups();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $groups, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'body_type'){


                    $body_type = [
                       ['id' => 'conventional', 'name' => _l('conventional')],
                       ['id' => 'full_size', 'name' => _l('full_size')],
                       ['id' => 'hatchback', 'name' => _l('hatchback')],
                       ['id' => 'pickup', 'name' => _l('pickup')],
                       ['id' => 'sedan', 'name' => _l('sedan')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $body_type, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'workperformance'){
                if($data['check'] == 'status'){
                    if(wa_get_status_modules('fleet')){
                        $statuses = fleet_logbook_status();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'vehicle'){
                    if(wa_get_status_modules('fleet')){

                        $this->load->model('fleet/fleet_model');
                        $vehicles = $this->fleet_model->get_vehicle();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $vehicles, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'driver'){
                    if(wa_get_status_modules('fleet')){

                        $this->load->model('fleet/fleet_model');
                        $drivers = $this->fleet_model->get_driver();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $drivers, ['staffid', ['firstname','lastname']], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }    
            }elseif($data['check'] != '' && $data['workflow_type'] == 'event'){
                if($data['check'] == 'event_type'){
                    $event_types = [
                      ['id' => 'accident', 'name' => _l('accident')],
                      ['id' => 'parts_damage', 'name' => _l('parts_damage')],
                      ['id' => 'other', 'name' => _l('other')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $event_types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'vehicle'){
                    if(wa_get_status_modules('fleet')){

                        $this->load->model('fleet/fleet_model');
                        $vehicles = $this->fleet_model->get_vehicle();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $vehicles, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'driver'){
                    if(wa_get_status_modules('fleet')){

                        $this->load->model('fleet/fleet_model');
                        $drivers = $this->fleet_model->get_driver();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $drivers, ['staffid', ['firstname', 'lastname' ]], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                } 
            }elseif($data['check'] != '' && $data['workflow_type'] == 'work_order'){
                if($data['check'] == 'status'){
                    if (wa_get_status_modules('fleet')) {
                        $statuses = [
                             ['id' => 'open', 'name' => _l('open')],
                             ['id' => 'in_progress', 'name' => _l('in_progress')],
                             ['id' => 'parts_ordered', 'name' => _l('parts_ordered')],
                             ['id' => 'complete', 'name' => _l('complete')],
                          ];
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }
                }elseif($data['check'] == 'vehicle'){
                    if(wa_get_status_modules('fleet')){

                        $this->load->model('fleet/fleet_model');
                        $vehicles = $this->fleet_model->get_vehicle();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $vehicles, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'price'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'booking'){
                if($data['check'] == 'status'){
                    if (wa_get_status_modules('fleet')) {
                        $statuses = fleet_booking_status();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }
                }elseif($data['check'] == 'create_invoice_status'){

                    $statuses = [
                        ['id' => 'created', 'name' => _l('wa_created') ],
                        ['id' => 'not_created_yet', 'name' => _l('wa_not_created_yet') ],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'fuel'){

                if($data['check'] == 'vehicle'){
                    if(wa_get_status_modules('fleet')){

                        $this->load->model('fleet/fleet_model');
                        $vehicles = $this->fleet_model->get_vehicle();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $vehicles, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'price'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }elseif($data['check'] == 'vendor'){
                    if(wa_get_status_modules('purchase')){
                        $this->load->model('purchase/purchase_model');
                        $vendors = $this->purchase_model->get_vendor();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $vendors, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'type'){
                    $fuel_types = [
                      ['id' => 'compressed_natural_gas', 'name' => _l('compressed_natural_gas')],
                      ['id' => 'diesel', 'name' => _l('diesel')],
                      ['id' => 'gasoline', 'name' => _l('gasoline')],
                      ['id' => 'propane', 'name' => _l('propane')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $fuel_types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'work_center'){
                if($data['check'] == 'working_hours'){

                    if(wa_get_status_modules('manufacturing')){
                        $this->load->model('manufacturing/manufacturing_model');
                        $working_hours = $this->manufacturing_model->get_working_hours();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $working_hours, ['id', 'working_hour_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'oee_target' || $data['check'] == 'time_efficiency' || $data['check'] == 'costs_hour' || $data['check'] == 'capacity'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'routing'){
                if($data['check'] == 'routing_name'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'text', ['df-condition_variable'=> '']);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'operation'){
                if($data['check'] == 'routing'){
                    if(wa_get_status_modules('manufacturing')){
                        $this->load->model('manufacturing/manufacturing_model');
                        $routings = $this->manufacturing_model->get_routings();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $routings, ['id', array('routing_code','routing_name')], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'work_center'){
                    if(wa_get_status_modules('manufacturing')){
                        $this->load->model('manufacturing/manufacturing_model');
                        $work_centers = $this->manufacturing_model->get_work_centers();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $work_centers, ['id', array('work_center_code','work_center_name')], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'bill_of_material'){
                if($data['check'] == 'routing'){
                    if(wa_get_status_modules('manufacturing')){
                        $this->load->model('manufacturing/manufacturing_model');
                        $routings = $this->manufacturing_model->get_routings();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $routings, ['id', array('routing_code','routing_name')], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['check'] == 'bom_type'){
                    if(wa_get_status_modules('manufacturing')){
                        $types = [
                            ['id' => 'manufacture_this_product' , 'name' => _l('manufacture_this_product')],
                            ['id' => 'kit' , 'name' => _l('kit')],
                        ];
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['check'] == 'ready_to_produce'){
                    $ready_to_produce_type=[];
                    $ready_to_produce_type[] = [
                        'name' => 'all_available',
                        'label' => _l('when_all_components_are_available'),
                    ];
                    $ready_to_produce_type[] = [
                        'name' => 'components_for_1st',
                        'label' => _l('when_components_for_1st_operation_are_available'),
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $ready_to_produce_type, ['name', 'label'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }else if($data['check'] == 'consumption'){
                    $consumption_type=[];
                    $consumption_type[] = [
                        'name' => 'strict',
                        'label' => _l('strict'),
                    ];

                    $consumption_type[] = [
                        'name' => 'flexible',
                        'label' => _l('flexible'),
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $consumption_type, ['name', 'label'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'bom_component'){

                if($data['check'] == 'component'){
                    if(wa_get_status_modules('manufacturing')){
                        $this->load->model('manufacturing/manufacturing_model');
                        $products = $this->manufacturing_model->get_product();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $products, ['id','description'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'bom_type'){
                    if(wa_get_status_modules('manufacturing')){
                        $types = [
                            ['id' => 'manufacture_this_product' , 'name' => _l('manufacture_this_product')],
                            ['id' => 'kit' , 'name' => _l('kit')],
                        ];
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'product_qty'){
                     $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }elseif($data['check'] == 'unit_id'){
                    if(wa_get_status_modules('manufacturing')){
                        $this->load->model('manufacturing/manufacturing_model');
                        $units = $this->manufacturing_model->mrp_get_unit();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $units, ['unit_type_id','unit_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'manufacturing_order'){
                if($data['check'] == 'bom'){
                    if(wa_get_status_modules('manufacturing')){
                        $this->load->model('manufacturing/manufacturing_model');
                        $boms = $this->manufacturing_model->get_bill_of_materials();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $boms, ['id', 'bom_code'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'unit_id'){
                    if(wa_get_status_modules('manufacturing')){
                        $this->load->model('manufacturing/manufacturing_model');
                        $units = $this->manufacturing_model->mrp_get_unit();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $units, ['unit_type_id','unit_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'staff_id'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);                
                }elseif($data['check'] == 'product_qty'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'omni_sales_order'){
                if($data['check'] == 'status'){
                    if(wa_get_status_modules('omni_sales')){
                        $statuses = omni_status_list();

                        foreach($statuses as $key => $status){
                            if($status['id'] == 0){
                                $statuses[$key]['id'] = ' 0';
                            }
                        }

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'label'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);    
                    }
                }elseif($data['check'] == 'create_invoice_status'){
                    $statuses = [
                        ['id' => 'created', 'name' => _l('wa_created') ],
                        ['id' => 'not_created_yet', 'name' => _l('wa_not_created_yet') ],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'order_type'){
                    $types = [
                        ['id' => 'sale_order', 'name' => _l('omni_sale_order')],
                        ['id' => 'return_order', 'name' => _l('omni_return_order')],
                    ];
                    $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'channel'){
                    $types = [
                        ['id' => '1', 'name' => _l('Pos')],
                        ['id' => '2', 'name' => _l('Portal')],
                        ['id' => '3', 'name' => _l('omni_woocommerce')],
                        ['id' => '4', 'name' => _l('omni_manual')],
                        ['id' => '6', 'name' => _l('omni_pre_order')],
                    ];
                    $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }elseif($data['check'] == 'customer'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'sale_agent'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);    
                }

            }elseif($data['check'] != '' && $data['workflow_type'] == 'omni_sales_refund'){
                if($data['check'] == 'amount'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }elseif($data['check'] == 'paymentmode'){
                    $this->load->model('payment_modes_model');
                    $payment_methods = $this->payment_modes_model->get();
                    $html .= render_select('condition_variable['.$data['nodeid'].']', $payment_methods, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'trade_discount'){
                if($data['check'] == 'channel'){
                    $types = [
                        ['id' => '1', 'name' => _l('Pos')],
                        ['id' => '2', 'name' => _l('Portal')],
                        ['id' => '6', 'name' => _l('omni_pre_order')],
                    ];
                    $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'client_groups'){
                    $this->load->model('client_groups_model');
                    $client_groups = $this->client_groups_model->get_groups();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $client_groups, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'customer'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'expired'){
                    $expireds = [
                        ['id' => 'expired', 'name' => _l('expired')],
                        ['id' => 'not_expired', 'name' => _l('not_expired')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $expireds, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'voucher'){
                if($data['check'] == 'channel'){
                    $types = [
                        ['id' => '1', 'name' => _l('Pos')],
                        ['id' => '2', 'name' => _l('Portal')],
                        ['id' => '6', 'name' => _l('omni_pre_order')],
                    ];
                    $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'client_groups'){
                    $this->load->model('client_groups_model');
                    $client_groups = $this->client_groups_model->get_groups();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $client_groups, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['check'] == 'customer'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'expired'){
                    $expireds = [
                        ['id' => 'expired', 'name' => _l('expired')],
                        ['id' => 'not_expired', 'name' => _l('not_expired')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $expireds, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'license'){
                if($data['check'] == 'category_id'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $categories = $this->fixed_equipment_model->get_categories('', 'license');
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $categories, ['id', 'category_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'manufacturer'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $manufacturers = $this->fixed_equipment_model->get_asset_manufacturers();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $manufacturers, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'supplier'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $suppliers = $this->fixed_equipment_model->get_suppliers();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $suppliers, ['id', 'supplier_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'depreciation'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $depreciations = $this->fixed_equipment_model->get_depreciations();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $depreciations, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'checkout_to'){
                    if(wa_get_status_modules('fixed_equipment')){
                        
                        $checkout_types = [
                            ['id' => 'user', 'name' => _l('staff')],
                            ['id' => 'customer', 'name' => _l('customer')],
                            ['id' => 'asset', 'name' => _l('asset')],
                            ['id' => 'project', 'name' => _l('project')],
                        ];

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $checkout_types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'asset'){
                if($data['check'] == 'model_id'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $models = $this->fixed_equipment_model->get_models();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $models, ['id', 'model_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'status'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $statuses = $this->fixed_equipment_model->get_status_labels();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'supplier'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $suppliers = $this->fixed_equipment_model->get_suppliers();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $suppliers, ['id', 'supplier_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'location'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $locations = $this->fixed_equipment_model->get_locations();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $locations, ['id', 'location_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'checkout_to'){
                    if(wa_get_status_modules('fixed_equipment')){
                        
                        $checkout_types = [
                            ['id' => 'staff', 'name' => _l('staff')],
                            ['id' => 'customer', 'name' => _l('customer')],
                            ['id' => 'asset', 'name' => _l('asset')],
                            ['id' => 'location', 'name' => _l('location')],
                            ['id' => 'project', 'name' => _l('project')],
                        ];

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $checkout_types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'checkin_status'){
                    if(wa_get_status_modules('fixed_equipment')){
                        
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $statuses = $this->fixed_equipment_model->get_status_labels();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'checkin_location'){
                    if(wa_get_status_modules('fixed_equipment')){
                        
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $locations = $this->fixed_equipment_model->get_locations();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $locations, ['id', 'location_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'accessories'){
                if($data['check'] == 'category_id'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $categories = $this->fixed_equipment_model->get_categories('', 'accessory');
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $categories, ['id', 'category_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'manufacturer'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $manufacturers = $this->fixed_equipment_model->get_asset_manufacturers();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $manufacturers, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'supplier'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $suppliers = $this->fixed_equipment_model->get_suppliers();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $suppliers, ['id', 'supplier_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'location'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $locations = $this->fixed_equipment_model->get_locations();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $locations, ['id', 'location_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'checkout_to'){
                    if(wa_get_status_modules('fixed_equipment')){
                        
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'consumable'){
                if($data['check'] == 'category_id'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $categories = $this->fixed_equipment_model->get_categories('', 'consumable');
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $categories, ['id', 'category_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'manufacturer'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $manufacturers = $this->fixed_equipment_model->get_asset_manufacturers();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $manufacturers, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'location'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $locations = $this->fixed_equipment_model->get_locations();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $locations, ['id', 'location_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'checkout_to'){
                    if(wa_get_status_modules('fixed_equipment')){
                        
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'component'){
                if($data['check'] == 'category_id'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $categories = $this->fixed_equipment_model->get_categories('', 'component');
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $categories, ['id', 'category_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'location'){
                    if(wa_get_status_modules('fixed_equipment')){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $locations = $this->fixed_equipment_model->get_locations();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $locations, ['id', 'location_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'checkout_to'){
                    if(wa_get_status_modules('fixed_equipment')){
                        
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $assets = $this->fixed_equipment_model->get_assets('', 'asset');

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $assets, array('id',array('series', 'assets_name')), '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'items'){
                if($data['check'] == 'warehouse_id'){
                    if(wa_get_status_modules('warehouse')){
                        $this->load->model('warehouse/warehouse_model');
                        $warehouses = $this->warehouse_model->get_warehouse();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $warehouses, array('warehouse_id',array('warehouse_code', 'warehouse_name')), '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'commodity_type'){
                    if(wa_get_status_modules('warehouse')){
                        $this->load->model('warehouse/warehouse_model');
                        $types = $this->warehouse_model->get_commodity_type();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $types, array('commodity_type_id',array('commondity_code', 'commondity_name')), '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'commodity_group'){
                    if(wa_get_status_modules('warehouse')){
                        $this->load->model('warehouse/warehouse_model');
                        $groups = $this->warehouse_model->get_commodity_group_type();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $groups, array('id',array('commodity_group_code', 'name')), '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'unit'){
                    if(wa_get_status_modules('warehouse')){
                        $this->load->model('warehouse/warehouse_model');
                        $units = $this->warehouse_model->get_unit_type();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $units, array('unit_type_id',array('unit_code', 'unit_name')), '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'opening_stock'){
                if($data['check'] == 'number_of_items'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'inventory_receiving_voucher'){
                if($data['check'] == 'number_of_items'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }elseif($data['check'] == 'buyer_id'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'project'){
                    $this->load->model('projects_model');
                    $projects = $this->projects_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $projects, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'requester'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'supplier_code'){
                    if(wa_get_status_modules('purchase')){
                        $this->load->model('purchase/purchase_model');
                        $vendors = $this->purchase_model->get_vendor();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $vendors, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'type'){
                    
                    $types = [
                        ['id' => 'capex', 'name' => 'CAPEX'],
                        ['id' => 'opex', 'name' => 'OPEX'],
                    ];
                    $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'department'){
                    $this->load->model('departments_model');
                    $departments = $this->departments_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $departments, ['departmentid', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'warehouse_id'){
                    if(wa_get_status_modules('warehouse')){
                        $this->load->model('warehouse/warehouse_model');
                        $warehouses = $this->warehouse_model->get_warehouse();

                        $html .= render_select('condition_variable['.$data['nodeid'].']', $warehouses, array('warehouse_id',array('warehouse_code', 'warehouse_name')), '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['check'] == 'approval_status'){
                    $statuses = [
                        ['id' => 1, 'name' => _l('approved')],
                        ['id' => ' '.'0', 'name' => _l('not_yet_approve')],                        
                        ['id' => -1, 'name' => _l('reject')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'inventory_delivery_voucher'){
                if($data['check'] == 'number_of_items'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }elseif($data['check'] == 'project'){
                    $this->load->model('projects_model');
                    $projects = $this->projects_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $projects, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'requester'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'type'){
                    
                    $types = [
                        ['id' => 'capex', 'name' => 'CAPEX'],
                        ['id' => 'opex', 'name' => 'OPEX'],
                    ];
                    $html .= render_select('condition_variable['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'department'){
                    $this->load->model('departments_model');
                    $departments = $this->departments_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $departments, ['departmentid', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'staff_id'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'customer'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'approval_status'){
                    $statuses = [
                        ['id' => 1, 'name' => 'approved'],
                        ['id' => ' '.'0', 'name' => 'not_yet_approve'],
                        ['id' => -1, 'name' => 'reject'],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'packing_list'){
                if($data['check'] == 'number_of_items'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }elseif($data['check'] == 'approval_status'){
                    $statuses = [
                        ['id' => 1, 'name' => _l('approved') ],
                        ['id' => ' '.'0', 'name' => _l('not_yet_approve')],
                        ['id' => -1, 'name' => _l('reject')],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'customer'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'delivery_status'){
                    if(wa_get_status_modules('warehouse')){
                        $statuses = packing_list_status();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'inventory_delivery_note'){
                if($data['check'] == 'number_of_items'){
                    $html .= render_input('condition_variable['.$data['nodeid'].']', '','', 'number', ['df-condition_variable'=> '']);
                }elseif($data['check'] == 'deliverer'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['check'] == 'approval_status'){
                    $statuses = [
                        ['id' => 1, 'name' => 'approved'],
                        ['id' => ' '.'0', 'name' => 'not_yet_approve'],
                        ['id' => -1, 'name' => 'reject'],
                    ];

                    $html .= render_select('condition_variable['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }elseif($data['check'] != '' && $data['workflow_type'] == 'omni_sales_refund'){
                    if($data['check'] == 'amount'){
                        $html .= render_input('condition_variable['.$data['nodeid'].']', '', '', 'number' , ['df-condition_variable' => '', 'data-nodeid' => $data['nodeid']]);
                    }elseif($data['check'] == 'paymentmode'){
                        $this->load->model('payment_modes_model');
                        $payment_methods = $this->payment_modes_model->get();
                        $html .= render_select('condition_variable['.$data['nodeid'].']', $payment_methods, ['id', 'name'], '', '', ['df-condition_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }
                }

    	}

    	echo json_encode([
    		'html' => $html,
    	]);

    }

    /**
     * workflow builder save
     * @return redirect
     */
    public function workflow_builder_save(){
        $data = $this->input->post();
        $workflow = '';
        if (isset($_FILES['workflow'])) {
            $file = $_FILES['workflow'];
        
            $workflow = file_get_contents($_FILES['workflow']['tmp_name']);
        }

        $data['workflow'] = $workflow;
        $success = $this->Workflow_automation_model->workflow_builder_save($data);
        $message = _l('updated_successfully', _l('workflow'));

        echo json_encode(['success' => true, 'message' => $message, 'url' => admin_url('workflow_automation/workflow_builder/' . $data['workflow_id'])]);
        die();
    }

    /**
     * [get_variable_for_action description]
     * @return [type] [description]
     */
    public function get_variable_for_action(){
    	$html = '';
    	if($this->input->post()){
    		$data = $this->input->post();

            if(!(strpos($data['action'], '_default') === false)){
                if($data['action'] != '' && $data['action'] == 'create_task_default'){
                    $task_templates = $this->Workflow_automation_model->get_task_templates();

                    $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['action'] != '' && $data['action'] == 'create_proposal_default' && ($data['workflow_type'] != '' || $data['workflow_type'] == '') ) {
                    $proposal_templates = $this->Workflow_automation_model->get_proposal_templates();

                    $html .= render_select('proposal_template['.$data['nodeid'].']', $proposal_templates, ['id', 'proposal_number'], 'wa_proposal_template', '', ['df-proposal_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['action'] != '' && $data['action'] == 'create_estimate_default' && ($data['workflow_type'] != '' || $data['workflow_type'] == '') ) {
                    $estimate_templates = $this->Workflow_automation_model->get_estimate_templates();

                    $html .= render_select('estimate_template['.$data['nodeid'].']', $estimate_templates, ['id', 'estimate_number'], 'wa_estimate_template', '', ['df-estimate_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['action'] != '' && $data['action'] == 'create_invoice_default' && ($data['workflow_type'] != '' || $data['workflow_type'] == '') ) {
                    $invoice_templates = $this->Workflow_automation_model->get_invoice_templates();

                    $html .= render_select('invoice_template['.$data['nodeid'].']', $invoice_templates, ['id', 'invoice_number'], 'wa_invoice_template', '', ['df-invoice_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['action'] != '' && $data['action'] == 'send_email_default'){

                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                    $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['action'] != '' && $data['action'] == 'create_manufacturing_order_default' && ($data['workflow_type'] != '' || $data['workflow_type'] == '') ){
                    if(wa_get_status_modules('manufacturing')){
                        $manufacturing_order_templates = $this->Workflow_automation_model->get_manufacturing_order_templates();

                        $html .= render_select('manufacturing_order_template['.$data['nodeid'].']', $manufacturing_order_templates, ['id', 'manufacturing_order_code'], 'wa_manufacturing_order_template', '', ['df-manufacturing_order_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['action'] != '' && $data['action'] == 'create_purchase_request_default' && ($data['workflow_type'] != '' || $data['workflow_type'] == '') ){
                    if(wa_get_status_modules('purchase')){
                        $purchase_request_templates = $this->Workflow_automation_model->get_purchase_request_templates();

                        $html .= render_select('purchase_request_template['.$data['nodeid'].']', $purchase_request_templates, ['id', 'pur_rq_code'], 'wa_purchase_request_template', '', ['df-purchase_request_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['action'] != '' && $data['action'] == 'create_purchase_order_default' && ($data['workflow_type'] != '' || $data['workflow_type'] == '') ){
                    if(wa_get_status_modules('purchase')){
                        $purchase_order_templates = $this->Workflow_automation_model->get_purchase_order_templates();

                        $html .= render_select('purchase_order_template['.$data['nodeid'].']', $purchase_order_templates, ['id', 'pur_order_number'], 'wa_purchase_order_template', '', ['df-purchase_order_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['action'] != '' && $data['action'] == 'create_manual_order_default' && ($data['workflow_type'] != '' || $data['workflow_type'] == '')){
                    if(wa_get_status_modules('omni_sales')){
                        $manual_order_templates = $this->Workflow_automation_model->get_manual_order_templates();

                        $html .= render_select('manual_order_template['.$data['nodeid'].']', $manual_order_templates, ['id', 'order_number'], 'wa_manual_order_template', '', ['df-manual_order_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['action'] != '' && $data['action'] == 'assign_manager_default' && ($data['workflow_type'] != '' || $data['workflow_type'] == '')){
                    if(wa_get_status_modules('hr_profile')){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        $html .= render_select('team_manage['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'team_manage', '', ['df-team_manage'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }
                }elseif($data['action'] != '' && $data['action'] == 'create_inventory_receiving_voucher_default' && ($data['workflow_type'] != '' || $data['workflow_type'] == '')){
                    if(wa_get_status_modules('warehouse')){
                        $inventory_receiving_voucher_templates = $this->Workflow_automation_model->get_inventory_receiving_voucher_templates();

                        $html .= render_select('inventory_receiving_voucher_template['.$data['nodeid'].']', $inventory_receiving_voucher_templates, ['id', 'goods_receipt_code'], 'inventory_receiving_voucher_template', '', ['df-inventory_receiving_voucher_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }
                }elseif($data['action'] != '' && $data['action'] == 'create_inventory_delivery_voucher_default' && ($data['workflow_type'] != '' || $data['workflow_type'] == '')){
                    if(wa_get_status_modules('warehouse')){
                        $inventory_delivery_voucher_templates = $this->Workflow_automation_model->get_inventory_delivery_voucher_templates();

                        $html .= render_select('inventory_delivery_voucher_template['.$data['nodeid'].']', $inventory_delivery_voucher_templates, ['id', 'goods_delivery_code'], 'inventory_delivery_voucher_template', '', ['df-inventory_delivery_voucher_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }
                }

            }else{
        		if($data['action'] != '' && $data['workflow_type'] == 'tasks'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);
        			if($data['action'] == 'assign_to' ){

        				$html .= render_select('action_variable['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-action_variable'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
        			}elseif($data['action'] == 'send_email'){


                        $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'add_a_comment'){

                        $html .= render_textarea('comment_content['.$data['nodeid'].']', 'content', '', ['df-comment_content'=> '', 'data-nodeid' => $data['nodeid']]);
                    }elseif($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'add_tag'){

                        $html .= '<div class="form-group">
                                <div id="inputTagsWrapper">
                                    <label for="tags['.$data['nodeid'].'" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>'._l('tags').'</label>
                                    <input type="text" class="tagsinput" id="tags" name="tags"
                                        value=""
                                        data-role="tagsinput">
                                </div>
                            </div>';
                    }elseif($data['action'] == 'change_status'){

                        $this->load->model('tasks_model');
                        $task_statuses = $this->tasks_model->get_statuses();

                        $html .= render_select('task_status['.$data['nodeid'].']', $task_statuses, ['id', 'name'], 'wa_status', '', ['df-task_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }elseif($data['action'] == 'change_priority'){

                        $priorities = get_tasks_priorities();
                        $html .= render_select('task_priority['.$data['nodeid'].']', $priorities, ['id', 'name'], '', '', ['df-task_priority'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }elseif($data['action'] == 'update_task_field'){

                        $fields = [
                            ['id' => 'name', 'name' => _l('wa_subject')],
                            ['id' => 'hourly_rate', 'name' => _l('wa_hourly_rate')],
                            ['id' => 'description', 'name' => _l('wa_description')],
                        ];

                        $html .= render_select('task_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_task_field', '', ['df-task_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'task_field_change(this); return false;'], [], '', '', true);

                    }elseif($data['action'] == 'create_reminder_for_task'){


                            $html .= '<div class="row"><div class="col-md-6">';

                            $html .= render_input('reminder_time['.$data['nodeid'].']', 'wa_send_notification_after', '', 'number' , ['df-reminder_time' => '', 'data-nodeid' => $data['nodeid']]);

                            $time_types = [];
                            $time_types = [
                                ['id' => 'min', 'name' => _l('wa_min')],
                                ['id' => 'hours', 'name' => _l('wa_hours')],
                                ['id' => 'days', 'name' => _l('wa_days')],
                                ['id' => 'months', 'name' => _l('wa_months')],
                            ];

                            $html .= '</div>';
                            $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                            $html .= render_select('reminder_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-reminder_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                            $html .= '</div></div>';


                        $html .= render_select('reminder_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_set_reminder_to', get_staff_user_id(),  ['df-reminder_to'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_textarea('reminder_description['.$data['nodeid'].']', 'wa_reminder_description', '', ['df-reminder_description'=> '', 'data-nodeid' => $data['nodeid']]);


                    }
        		}else if($data['action'] != '' && $data['workflow_type'] == 'projects'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);
                    if($data['action'] == 'send_email'){

                        $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'assign_to_customer'){
                        $this->load->model('clients_model');

                        $clients = $this->clients_model->get();

                         $html .= render_select('assign_to_client['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-assign_to_client'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }elseif($data['action'] == 'add_tag'){

                        $html .= '<div class="form-group">
                                <div id="inputTagsWrapper">
                                    <label for="tags['.$data['nodeid'].']" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i>'._l('tags').'</label>
                                    <input type="text" class="tagsinput" id="tags" name="tags['.$data['nodeid'].']" df-tags data-nodeid="'.$data['nodeid'].'"
                                        value=""
                                        data-role="tagsinput">
                                </div>
                            </div>';
                    }elseif($data['action'] == 'change_status'){

                        $this->load->model('projects_model');
                        $project_statuses = $this->projects_model->get_project_statuses();

                        $html .= render_select('project_status['.$data['nodeid'].']', $project_statuses, ['id', 'name'], 'wa_status', '', ['df-project_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }elseif($data['action'] == 'update_project_fields'){

                        $fields = [
                            ['id' => 'name', 'name' => _l('wa_project_name')],
                            ['id' => 'hourly_rate', 'name' => _l('wa_hourly_rate')],
                            ['id' => 'description', 'name' => _l('wa_description')],
                        ];

                        $html .= render_select('project_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_project_field', '', ['df-project_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'project_field_change(this); return false;'], [], '', '', true);

                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'contracts'){
                    if($data['action'] == 'send_email'){

                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'change_status_project'){

                        $this->load->model('projects_model');
                        $project_statuses = $this->projects_model->get_project_statuses();

                        $html .= render_select('project_status['.$data['nodeid'].']', $project_statuses, ['id', 'name'], 'wa_status', '', ['df-project_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }elseif($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'assign_to_customer'){
                        $this->load->model('clients_model');

                        $clients = $this->clients_model->get();

                        $html .= render_select('assign_to_client['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-assign_to_client'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'add_note'){
                        $html .= render_textarea('note['.$data['nodeid'].']', 'wa_note', '', ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);
                    }elseif($data['action'] == 'update_contract_field'){
                        $fields = [
                            ['id' => 'subject', 'name' => _l('wa_subject')],
                            ['id' => 'value', 'name' => _l('wa_value')],
                            ['id' => 'description', 'name' => _l('wa_description')],
                        ];

                        $html .= render_select('contract_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_contract_field', '', ['df-contract_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'contract_field_change(this); return false;'], [], '', '', true);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'leads'){
                    if($data['action'] == 'send_email'){

                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'change_status_lead'){
                        $this->load->model('leads_model');
                        $status = $this->leads_model->get_status();

                        $html .= render_select('lead_status['.$data['nodeid'].']', $status, ['id', 'name'], 'wa_status', '', ['df-lead_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'change_source_lead'){
                        $this->load->model('leads_model');
                        $source = $this->leads_model->get_source();

                        $html .= render_select('lead_source['.$data['nodeid'].']', $source, ['id', 'name'], 'wa_status', '', ['df-lead_source'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'assign_to_staff'){

                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_select('staff['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_staff', '', ['df-staff'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'add_note'){
                        $html .= render_textarea('note['.$data['nodeid'].']', 'wa_note', '', ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);
                    }else if($data['action'] == 'create_reminder_for_lead'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= '<div class="row"><div class="col-md-6">';

                            $html .= render_input('reminder_time['.$data['nodeid'].']', 'wa_send_notification_after', '', 'number' , ['df-reminder_time' => '', 'data-nodeid' => $data['nodeid']]);

                            $time_types = [];
                            $time_types = [
                                ['id' => 'min', 'name' => _l('wa_min')],
                                ['id' => 'hours', 'name' => _l('wa_hours')],
                                ['id' => 'days', 'name' => _l('wa_days')],
                                ['id' => 'months', 'name' => _l('wa_months')],
                            ];

                            $html .= '</div>';
                            $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                            $html .= render_select('reminder_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-reminder_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                            $html .= '</div></div>';


                        $html .= render_select('reminder_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_set_reminder_to', get_staff_user_id(),  ['df-reminder_to'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_textarea('reminder_description['.$data['nodeid'].']', 'wa_reminder_description', '', ['df-reminder_description'=> '', 'data-nodeid' => $data['nodeid']]);

                    }else if($data['action'] == 'update_lead_field'){
                        $fields = [
                            ['id' => 'name', 'name' => _l('wa_name')],
                            ['id' => 'address', 'name' => _l('wa_address')],
                            ['id' => 'position', 'name' => _l('wa_position')],
                            ['id' => 'description', 'name' => _l('wa_description')],
                        ];

                        $html .= render_select('lead_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_lead_field', '', ['df-lead_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'lead_field_change(this); return false;'], [], '', '', true);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'customers'){
                    if($data['action'] == 'send_email'){

                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'change_group_customer'){

                        $this->load->model('clients_model');
                        $groups = $this->clients_model->get_groups();

                        $html .= render_select('client_group['.$data['nodeid'].']', $groups, ['id', 'name'], 'wa_client_group', '', ['df-client_group'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'assign_to_staff'){

                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_select('staff['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_staff', '', ['df-staff'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'add_note'){
                        $html .= render_textarea('note['.$data['nodeid'].']', 'wa_note', '', ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);
                    }else if($data['action'] == 'create_reminder_for_customer'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        $html .= '<div class="row"><div class="col-md-6">';

                            $html .= render_input('reminder_time['.$data['nodeid'].']', 'wa_send_notification_after', '', 'number' , ['df-reminder_time' => '', 'data-nodeid' => $data['nodeid']]);

                            $time_types = [];
                            $time_types = [
                                ['id' => 'min', 'name' => _l('wa_min')],
                                ['id' => 'hours', 'name' => _l('wa_hours')],
                                ['id' => 'days', 'name' => _l('wa_days')],
                                ['id' => 'months', 'name' => _l('wa_months')],
                            ];

                            $html .= '</div>';
                            $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                            $html .= render_select('reminder_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-reminder_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                            $html .= '</div></div>';


                        $html .= render_select('reminder_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_set_reminder_to', get_staff_user_id(),  ['df-reminder_to'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_textarea('reminder_description['.$data['nodeid'].']', 'wa_reminder_description', '', ['df-reminder_description'=> '', 'data-nodeid' => $data['nodeid']]);

                    }else if($data['action'] == 'update_customer_field'){
                        $fields = [
                            ['id' => 'company', 'name' => _l('wa_company')],
                            ['id' => 'vat', 'name' => _l('wa_vat')],
                            ['id' => 'phonenumber', 'name' => _l('wa_phonenumber')],
                            ['id' => 'website', 'name' => _l('wa_website')],
                        ];

                        $html .= render_select('customer_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_lead_field', '', ['df-customer_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'customer_field_change(this); return false;'], [], '', '', true);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'proposals'){
                    if($data['action'] == 'update_proposal_field'){
                        $fields = [
                            ['id' => 'subject', 'name' => _l('wa_subject')],
                            ['id' => 'assigned', 'name' => _l('wa_assigned')],
                            ['id' => 'proposal_to', 'name' => _l('wa_proposal_to')],
                            ['id' => 'email', 'name' => _l('wa_email')],
                        ];

                        $html .= render_select('proposal_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_lead_field', '', ['df-proposal_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'proposal_field_change(this); return false;'], [], '', '', true);
                    }else if($data['action'] == 'change_status_proposal'){
                        $this->load->model('proposals_model');
                        $statuses = $this->proposals_model->get_statuses();

                        $status = [];

                        foreach($statuses as $st_id){
                            $status[] = ['id' => $st_id, 'name' => format_proposal_status($st_id)];
                        }

                        $html .= render_select('proposal_status['.$data['nodeid'].']', $status, ['id', 'name'], 'wa_status', '', ['df-proposal_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'change_project'){
                        $projects = $this->projects_model->get();

                        $html .= render_select('project['.$data['nodeid'].']', $projects, ['id', 'name'], 'wa_project', '', ['df-project'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'add_comment'){
                        $html .= render_textarea('comment_content['.$data['nodeid'].']', 'content', '', ['df-comment_content'=> '', 'data-nodeid' => $data['nodeid']]);
                    }else if($data['action'] == 'create_reminder_for_proposal'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        $html .= '<div class="row"><div class="col-md-6">';

                            $html .= render_input('reminder_time['.$data['nodeid'].']', 'wa_send_notification_after', '', 'number' , ['df-reminder_time' => '', 'data-nodeid' => $data['nodeid']]);

                            $time_types = [];
                            $time_types = [
                                ['id' => 'min', 'name' => _l('wa_min')],
                                ['id' => 'hours', 'name' => _l('wa_hours')],
                                ['id' => 'days', 'name' => _l('wa_days')],
                                ['id' => 'months', 'name' => _l('wa_months')],
                            ];

                            $html .= '</div>';
                            $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                            $html .= render_select('reminder_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-reminder_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                            $html .= '</div></div>';


                        $html .= render_select('reminder_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_set_reminder_to', get_staff_user_id(),  ['df-reminder_to'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_textarea('reminder_description['.$data['nodeid'].']', 'wa_reminder_description', '', ['df-reminder_description'=> '', 'data-nodeid' => $data['nodeid']]);

                    }else if($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'create_note'){
                        $html .= render_textarea('note['.$data['nodeid'].']', 'wa_note', '', ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'estimates'){

                    if($data['action'] == 'update_estimate_field'){
                        $fields = [
                            ['id' => 'reference_no', 'name' => _l('wa_reference_no')],
                            ['id' => 'sale_agent', 'name' => _l('wa_sale_agent')],
                            ['id' => 'adminnote', 'name' => _l('wa_adminnote')],
                            ['id' => 'clientnote', 'name' => _l('wa_clientnote')],
                            ['id' => 'terms', 'name' => _l('wa_terms')],
                        ];

                        $html .= render_select('estimate_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_lead_field', '', ['df-estimate_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'estimate_field_change(this); return false;'], [], '', '', true);

                    }else if($data['action'] == 'change_status_estimate'){
                        $this->load->model('estimates_model');
                        $statuses = $this->estimates_model->get_statuses();

                        $status = [];

                        foreach($statuses as $st_id){
                            $status[] = ['id' => $st_id, 'name' => format_estimate_status($st_id)];
                        }

                        $html .= render_select('estimate_status['.$data['nodeid'].']', $status, ['id', 'name'], 'wa_status', '', ['df-estimate_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'change_project'){
                        $projects = $this->projects_model->get();

                        $html .= render_select('project['.$data['nodeid'].']', $projects, ['id', 'name'], 'wa_project', '', ['df-project'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'create_reminder_for_estimate'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        $html .= '<div class="row"><div class="col-md-6">';

                            $html .= render_input('reminder_time['.$data['nodeid'].']', 'wa_send_notification_after', '', 'number' , ['df-reminder_time' => '', 'data-nodeid' => $data['nodeid']]);

                            $time_types = [];
                            $time_types = [
                                ['id' => 'min', 'name' => _l('wa_min')],
                                ['id' => 'hours', 'name' => _l('wa_hours')],
                                ['id' => 'days', 'name' => _l('wa_days')],
                                ['id' => 'months', 'name' => _l('wa_months')],
                            ];

                            $html .= '</div>';
                            $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                            $html .= render_select('reminder_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-reminder_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                            $html .= '</div></div>';


                        $html .= render_select('reminder_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_set_reminder_to', get_staff_user_id(),  ['df-reminder_to'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_textarea('reminder_description['.$data['nodeid'].']', 'wa_reminder_description', '', ['df-reminder_description'=> '', 'data-nodeid' => $data['nodeid']]);

                    }else if($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'create_note'){
                        $html .= render_textarea('note['.$data['nodeid'].']', 'wa_note', '', ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);
                    }


                }else if($data['action'] != '' && $data['workflow_type'] == 'invoices'){
                    if($data['action'] == 'update_invoice_field'){
                        $fields = [
                            ['id' => 'sale_agent', 'name' => _l('wa_sale_agent')],
                            ['id' => 'adminnote', 'name' => _l('wa_adminnote')],
                            ['id' => 'clientnote', 'name' => _l('wa_clientnote')],
                            ['id' => 'terms', 'name' => _l('wa_terms')],
                        ];

                        $html .= render_select('invoice_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_lead_field', '', ['df-invoice_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'invoice_field_change(this); return false;'], [], '', '', true);

                    }else if($data['action'] == 'change_status_invoice'){
                        $this->load->model('invoices_model');
                        $statuses = $this->invoices_model->get_statuses();

                        $status = [];

                        foreach($statuses as $st_id){
                            $status[] = ['id' => $st_id, 'name' => format_invoice_status($st_id)];
                        }

                        $html .= render_select('invoice_status['.$data['nodeid'].']', $status, ['id', 'name'], 'wa_status', '', ['df-invoice_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }else if($data['action'] == 'change_project'){
                        $projects = $this->projects_model->get();

                        $html .= render_select('project['.$data['nodeid'].']', $projects, ['id', 'name'], 'wa_project', '', ['df-project'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'create_reminder_for_invoice'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        $html .= '<div class="row"><div class="col-md-6">';

                            $html .= render_input('reminder_time['.$data['nodeid'].']', 'wa_send_notification_after', '', 'number' , ['df-reminder_time' => '', 'data-nodeid' => $data['nodeid']]);

                            $time_types = [];
                            $time_types = [
                                ['id' => 'min', 'name' => _l('wa_min')],
                                ['id' => 'hours', 'name' => _l('wa_hours')],
                                ['id' => 'days', 'name' => _l('wa_days')],
                                ['id' => 'months', 'name' => _l('wa_months')],
                            ];

                            $html .= '</div>';
                            $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                            $html .= render_select('reminder_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-reminder_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                            $html .= '</div></div>';


                        $html .= render_select('reminder_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_set_reminder_to', get_staff_user_id(),  ['df-reminder_to'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_textarea('reminder_description['.$data['nodeid'].']', 'wa_reminder_description', '', ['df-reminder_description'=> '', 'data-nodeid' => $data['nodeid']]);

                    }else if($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'create_note'){
                        
                        $html .= render_textarea('note['.$data['nodeid'].']', 'wa_note', '', ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);
                        
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'credit_notes'){
                    if($data['action'] == 'update_credit_note_field'){
                        $fields = [
                            ['id' => 'reference_no', 'name' => _l('wa_reference_no')],
                            ['id' => 'adminnote', 'name' => _l('wa_adminnote')],
                            ['id' => 'clientnote', 'name' => _l('wa_clientnote')],
                            ['id' => 'terms', 'name' => _l('wa_terms')],
                        ];

                        $html .= render_select('credit_note_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_lead_field', '', ['df-credit_note_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'credit_note_field_change(this); return false;'], [], '', '', true);

                    }else if($data['action'] == 'change_status_credit_note'){
                        $this->load->model('credit_notes_model');
                        $statuses = $this->credit_notes_model->get_statuses();

                        $html .= render_select('credit_note_status['.$data['nodeid'].']', $statuses, ['id', 'name'], 'wa_status', '', ['df-credit_note_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }else if($data['action'] == 'create_reminder_for_credit_note'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        $html .= '<div class="row"><div class="col-md-6">';

                            $html .= render_input('reminder_time['.$data['nodeid'].']', 'wa_send_notification_after', '', 'number' , ['df-reminder_time' => '', 'data-nodeid' => $data['nodeid']]);

                            $time_types = [];
                            $time_types = [
                                ['id' => 'min', 'name' => _l('wa_min')],
                                ['id' => 'hours', 'name' => _l('wa_hours')],
                                ['id' => 'days', 'name' => _l('wa_days')],
                                ['id' => 'months', 'name' => _l('wa_months')],
                            ];

                            $html .= '</div>';
                            $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                            $html .= render_select('reminder_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-reminder_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                            $html .= '</div></div>';


                        $html .= render_select('reminder_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_set_reminder_to', get_staff_user_id(),  ['df-reminder_to'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_textarea('reminder_description['.$data['nodeid'].']', 'wa_reminder_description', '', ['df-reminder_description'=> '', 'data-nodeid' => $data['nodeid']]);

                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'payment'){
                    if($data['action'] == 'update_payment_field'){
                        $fields = [
                          
                            ['id' => 'paymentmode', 'name' => _l('wa_paymentmode')],
                            ['id' => 'transactionid', 'name' => _l('wa_transactionid')],
                            ['id' => 'note', 'name' => _l('wa_note')],
                        ];

                        $html .= render_select('payment_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_lead_field', '', ['df-payment_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'payment_field_change(this); return false;'], [], '', '', true);

                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'purchase_order'){
                    if($data['action'] == 'update_purchase_order_field'){
                        $fields = [
                            ['id' => 'purchase_order_description', 'name' => _l('wa_purchase_order_description')],
                            ['id' => 'vendor', 'name' => _l('wa_vendor')],
                            ['id' => 'buyer', 'name' => _l('wa_person_in_charge')],
                            ['id' => 'type', 'name' => _l('wa_type')],
                            ['id' => 'vendornote', 'name' => _l('wa_vendornote')],
                            ['id' => 'terms', 'name' => _l('wa_terms')],
                            ['id' => 'project', 'name' => _l('wa_project')],
                        ];

                        $html .= render_select('purchase_order_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_lead_field', '', ['df-purchase_order_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'purchase_order_field_change(this); return false;'], [], '', '', true);
                    }else if($data['action'] == 'change_approval_status'){
                        $status = [];

                        $status[] = ['id' => 1, 'name' => _l('purchase_draft')];
                        $status[] = ['id' => 2, 'name' => _l('purchase_approved')];
                        $status[] = ['id' => 3, 'name' => _l('pur_rejected')];
                        $status[] = ['id' => 4, 'name' => _l('pur_canceled')];

                        $html .= render_select('approval_status['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-approval_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }else if($data['action'] == 'change_delivery_status'){
                        $status = [];

                        $status[] = ['id' => ' 0', 'name' => _l('undelivered')];
                        $status[] = ['id' => 1, 'name' => _l('completely_delivered')];
                        $status[] = ['id' => 2, 'name' => _l('pending_delivered')];
                        $status[] = ['id' => 3, 'name' => _l('partially_delivered')];


                        $html .= render_select('delivery_status['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-delivery_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'create_reminder_for_purchase_order'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        $html .= '<div class="row"><div class="col-md-6">';

                            $html .= render_input('reminder_time['.$data['nodeid'].']', 'wa_send_notification_after', '', 'number' , ['df-reminder_time' => '', 'data-nodeid' => $data['nodeid']]);

                            $time_types = [];
                            $time_types = [
                                ['id' => 'min', 'name' => _l('wa_min')],
                                ['id' => 'hours', 'name' => _l('wa_hours')],
                                ['id' => 'days', 'name' => _l('wa_days')],
                                ['id' => 'months', 'name' => _l('wa_months')],
                            ];

                            $html .= '</div>';
                            $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                            $html .= render_select('reminder_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-reminder_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                            $html .= '</div></div>';


                        $html .= render_select('reminder_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_set_reminder_to', get_staff_user_id(),  ['df-reminder_to'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_textarea('reminder_description['.$data['nodeid'].']', 'wa_reminder_description', '', ['df-reminder_description'=> '', 'data-nodeid' => $data['nodeid']]);
                    }else if($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'send_email'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'purchase_request'){
                    if($data['action'] == 'update_purchase_request_field'){

                        $fields = [
                            ['id' => 'pur_rq_name', 'name' => _l('wa_purchase_request_name')],
                            ['id' => 'project', 'name' => _l('wa_project')],
                            ['id' => 'requester', 'name' => _l('wa_requester')],
                            ['id' => 'type', 'name' => _l('wa_type')],
                            ['id' => 'description', 'name' => _l('wa_description')],

                        ];

                        $html .= render_select('purchase_request_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_lead_field', '', ['df-purchase_request_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'purchase_request_field_change(this); return false;'], [], '', '', true);
                    }else if($data['action'] == 'change_approval_status'){
                        $status = [];

                        $status[] = ['id' => 1, 'name' => _l('purchase_draft')];
                        $status[] = ['id' => 2, 'name' => _l('purchase_approved')];
                        $status[] = ['id' => 3, 'name' => _l('pur_rejected')];
                        $status[] = ['id' => 4, 'name' => _l('pur_canceled')];

                        $html .= render_select('approval_status['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-approval_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'send_email'){
                         $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'purchase_quotation'){
                    if($data['action'] == 'update_purchase_quotation_field'){

                        $fields = [
                            ['id' => 'vendor', 'name' => _l('wa_vendor')],
                            ['id' => 'buyer', 'name' => _l('wa_person_in_charge')],
                            
                        ];

                        $html .= render_select('purchase_quotation_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_lead_field', '', ['df-purchase_quotation_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'purchase_quotation_field_change(this); return false;'], [], '', '', true);

                    }else if($data['action'] == 'change_approval_status'){
                        $status = [];

                        $status[] = ['id' => 1, 'name' => _l('purchase_draft')];
                        $status[] = ['id' => 2, 'name' => _l('purchase_approved')];
                        $status[] = ['id' => 3, 'name' => _l('pur_rejected')];


                        $html .= render_select('approval_status['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-approval_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'send_email'){
                         $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'purchase_invoice'){
                    if($data['action'] == 'update_purchase_invoice_field'){
                        $fields = [
                            ['id' => 'vendor_invoice_number', 'name' => _l('wa_purchase_invoice_number')],
                            ['id' => 'transactionid', 'name' => _l('wa_transactionid')],
                            ['id' => 'vendor', 'name' => _l('wa_vendor')],
                            ['id' => 'vendornote', 'name' => _l('wa_vendornote')],
                            ['id' => 'terms', 'name' => _l('wa_terms')],
                            ['id' => 'adminnote', 'name' => _l('wa_adminnote')],
                        ];

                        $html .= render_select('purchase_invoice_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_lead_field', '', ['df-purchase_invoice_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'purchase_invoice_field_change(this); return false;'], [], '', '', true);
                    }else if($data['action'] == 'change_approval_status'){
                        $status = [];

                        $status[] = ['id' => 1, 'name' => _l('purchase_draft')];
                        $status[] = ['id' => 2, 'name' => _l('purchase_approved')];
                        $status[] = ['id' => 3, 'name' => _l('pur_rejected')];
                        $status[] = ['id' => 4, 'name' => _l('pur_canceled')];

                        $html .= render_select('approval_status['.$data['nodeid'].']', $status, ['id', 'name'], '', '', ['df-approval_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }else if($data['action'] == 'create_reminder_for_purchase_invoice'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        $html .= '<div class="row"><div class="col-md-6">';

                            $html .= render_input('reminder_time['.$data['nodeid'].']', 'wa_send_notification_after', '', 'number' , ['df-reminder_time' => '', 'data-nodeid' => $data['nodeid']]);

                            $time_types = [];
                            $time_types = [
                                ['id' => 'min', 'name' => _l('wa_min')],
                                ['id' => 'hours', 'name' => _l('wa_hours')],
                                ['id' => 'days', 'name' => _l('wa_days')],
                                ['id' => 'months', 'name' => _l('wa_months')],
                            ];

                            $html .= '</div>';
                            $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                            $html .= render_select('reminder_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-reminder_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                            $html .= '</div></div>';


                        $html .= render_select('reminder_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_set_reminder_to', get_staff_user_id(),  ['df-reminder_to'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_textarea('reminder_description['.$data['nodeid'].']', 'wa_reminder_description', '', ['df-reminder_description'=> '', 'data-nodeid' => $data['nodeid']]);
                    }else if($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'send_email'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'add_note'){
                        $html .= render_textarea('note['.$data['nodeid'].']', 'wa_note', '', ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'purchase_contract'){
                    if($data['action'] == 'update_purchase_contract_field'){
                        $fields = [
                            ['id' => 'service_category', 'name' => _l('wa_service_category')],
                            ['id' => 'contract_value', 'name' => _l('wa_contract_value')],
                            ['id' => 'vendor', 'name' => _l('wa_vendor')],
                            ['id' => 'department', 'name' => _l('wa_department')],
                        ];

                        $html .= render_select('purchase_contract_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_lead_field', '', ['df-purchase_contract_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'purchase_contract_field_change(this); return false;'], [], '', '', true);
                    }else if($data['action'] == 'create_reminder_for_purchase_contract'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        $html .= '<div class="row"><div class="col-md-6">';

                            $html .= render_input('reminder_time['.$data['nodeid'].']', 'wa_send_notification_after', '', 'number' , ['df-reminder_time' => '', 'data-nodeid' => $data['nodeid']]);

                            $time_types = [];
                            $time_types = [
                                ['id' => 'min', 'name' => _l('wa_min')],
                                ['id' => 'hours', 'name' => _l('wa_hours')],
                                ['id' => 'days', 'name' => _l('wa_days')],
                                ['id' => 'months', 'name' => _l('wa_months')],
                            ];

                            $html .= '</div>';
                            $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                            $html .= render_select('reminder_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-reminder_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                            $html .= '</div></div>';


                        $html .= render_select('reminder_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_set_reminder_to', get_staff_user_id(),  ['df-reminder_to'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_textarea('reminder_description['.$data['nodeid'].']', 'wa_reminder_description', '', ['df-reminder_description'=> '', 'data-nodeid' => $data['nodeid']]);
                    }else if($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'send_email'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'add_note'){
                        $html .= render_textarea('note['.$data['nodeid'].']', 'wa_note', '', ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'vendor'){
                    if($data['action'] == 'update_vendor_field'){
                        $fields = [
                            ['id' => 'company', 'name' => _l('company')],
                            ['id' => 'vat', 'name' => _l('wa_vat')],
                            ['id' => 'phone', 'name' => _l('wa_phone')],
                            ['id' => 'website', 'name' => _l('wa_website')],
                            ['id' => 'address', 'name' => _l('wa_address')],
                            ['id' => 'city', 'name' => _l('wa_city')],
                            ['id' => 'state', 'name' => _l('wa_state')],
                            ['id' => 'zip', 'name' => _l('wa_zip')],
                            ['id' => 'country', 'name' => _l('wa_country')],
                        ];

                        $html .= render_select('vendor_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_lead_field', '', ['df-vendor_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'vendor_field_change(this); return false;'], [], '', '', true);
                    }else if($data['action'] == 'send_email'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }else if($data['action'] == 'add_note'){
                        $html .= render_textarea('note['.$data['nodeid'].']', 'wa_note', '', ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);
                    }else if($data['action'] == 'assign_to_staff'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_select('staff['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_staff', '', ['df-staff'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }

                }else if($data['action'] != '' && $data['workflow_type'] == 'ticket'){
                    if($data['action'] == 'change_priority'){
                        if(wa_get_status_modules('customer_service')){
                            $priorities = cs_priority();

                            $html .= render_select('priority['.$data['nodeid'].']', $priorities, ['id', 'name'], '', '', ['df-priority'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }else if($data['action'] == 'change_status'){
                        if(wa_get_status_modules('customer_service')){
                            $statuses = cs_ticket_status();

                            $html .= render_select('status['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }else if($data['action'] == 'change_type'){
                        if(wa_get_status_modules('customer_service')){
                            $types = cs_ticket_type();

                            $html .= render_select('type['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-type'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }else if($data['action'] == 'assign_to_staff'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_select('staff['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_staff', '', ['df-staff'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'staff'){
                    if($data['action'] == 'create_onboarding'){
                        if(wa_get_status_modules('hr_profile')){


                            $this->load->model('hr_profile/hr_profile_model');
                            $type_of_trainings = $this->hr_profile_model->get_type_of_training();

                            $html .= render_select('type_of_training['.$data['nodeid'].']', $type_of_trainings, ['id', 'name'], 'wa_type_of_training', '', ['df-type_of_training'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                            $training_program = [];

                            $html .= render_select('training_program['.$data['nodeid'].']', $training_program, ['id', 'name'], 'wa_training_program', '', ['df-training_program'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'layoff_checkist'){

                        $html .= '<div class="row"><div class="col-md-6">';

                        $html .= render_input('layoff_time['.$data['nodeid'].']', 'wa_layoff_after', '', 'number' , ['df-layoff_time' => '', 'data-nodeid' => $data['nodeid']]);

                        $time_types = [];
                        $time_types = [
                            ['id' => 'min', 'name' => _l('wa_min')],
                            ['id' => 'hours', 'name' => _l('wa_hours')],
                            ['id' => 'days', 'name' => _l('wa_days')],
                            ['id' => 'months', 'name' => _l('wa_months')],
                        ];

                        $html .= '</div>';
                        $html .= '<div class="col-md-6"><label for="layoff_time_type">&nbsp;</label>';
                        $html .= render_select('layoff_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-layoff_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                        $html .= '</div></div>';

                    }elseif($data['action'] == 'assign_training_program'){

                        $html .= render_input('training_programs_name['.$data['nodeid'].']', 'wa_training_programs_name', '', 'text' , ['df-training_programs_name' => '', 'data-nodeid' => $data['nodeid']]);


                        $html .= render_input('training_places['.$data['nodeid'].']', 'wa_training_places', '', 'text' , ['df-training_places' => '', 'data-nodeid' => $data['nodeid']]);


                        $html .= render_textarea('training_results['.$data['nodeid'].']', 'wa_training_results', '', ['df-training_results'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= '<div class="row"><div class="col-md-6">';

                        $html .= render_input('start_time['.$data['nodeid'].']', 'wa_start_after', '', 'number' , ['df-start_time' => '', 'data-nodeid' => $data['nodeid']]);

                            $time_types = [];
                            $time_types = [
                                ['id' => 'min', 'name' => _l('wa_min')],
                                ['id' => 'hours', 'name' => _l('wa_hours')],
                                ['id' => 'days', 'name' => _l('wa_days')],
                                ['id' => 'months', 'name' => _l('wa_months')],
                            ];

                            $html .= '</div>';
                            $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                            $html .= render_select('start_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-start_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                            $html .= '</div></div>';


                        $html .= '<div class="row"><div class="col-md-6">';

                            $html .= render_input('finish_time['.$data['nodeid'].']', 'wa_finish_after', '', 'number' , ['df-finish_time' => '', 'data-nodeid' => $data['nodeid']]);

                            $time_types = [];
                            $time_types = [
                                ['id' => 'min', 'name' => _l('wa_min')],
                                ['id' => 'hours', 'name' => _l('wa_hours')],
                                ['id' => 'days', 'name' => _l('wa_days')],
                                ['id' => 'months', 'name' => _l('wa_months')],
                            ];

                            $html .= '</div>';
                            $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                            $html .= render_select('finish_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-finish_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                            $html .= '</div></div>';


                    }elseif($data['action'] == 'change_status_contract'){
                         $statuses = [
                            ['id' => 'draft', 'name' => _l('draft')],
                            ['id' => 'valid', 'name' => _l('valid')],
                            ['id' => 'invalid', 'name' => _l('exprired')],
                            ['id' => 'finish', 'name' => _l('finish')],
                        ];

                        $html .= render_select('contract_status['.$data['nodeid'].']', $statuses, ['id', 'name'], 'wa_status', '', ['df-contract_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'change_status_staff'){
                        $statuses = [
                            ['id' => 'working', 'name' => _l('wa_working')],
                            ['id' => 'maternity_leave', 'name' => _l('maternity_leave')],
                            ['id' => 'inactivity', 'name' => _l('inactivity')],
                        ];

                        $html .= render_select('staff_status['.$data['nodeid'].']', $statuses, ['id', 'name'], 'wa_status', '', ['df-staff_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'expense'){
                    if($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'create_reminder_for_expense'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        $html .= '<div class="row"><div class="col-md-6">';

                            $html .= render_input('reminder_time['.$data['nodeid'].']', 'wa_send_notification_after', '', 'number' , ['df-reminder_time' => '', 'data-nodeid' => $data['nodeid']]);

                            $time_types = [];
                            $time_types = [
                                ['id' => 'min', 'name' => _l('wa_min')],
                                ['id' => 'hours', 'name' => _l('wa_hours')],
                                ['id' => 'days', 'name' => _l('wa_days')],
                                ['id' => 'months', 'name' => _l('wa_months')],
                            ];

                            $html .= '</div>';
                            $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                            $html .= render_select('reminder_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-reminder_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                            $html .= '</div></div>';


                        $html .= render_select('reminder_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_set_reminder_to', get_staff_user_id(),  ['df-reminder_to'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_textarea('reminder_description['.$data['nodeid'].']', 'wa_reminder_description', '', ['df-reminder_description'=> '', 'data-nodeid' => $data['nodeid']]);

                    }elseif($data['action'] == 'update_expense_fields'){

                        $fields = [
                            ['id' => 'name', 'name' => _l('wa_name')],
                            ['id' => 'amount', 'name' => _l('wa_amount')],
                            ['id' => 'clientid', 'name' => _l('wa_customer')],
                            ['id' => 'note', 'name' => _l('wa_note')],
                        ];

                        $html .= render_select('expense_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_expense_field', '', ['df-expense_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'expense_field_change(this); return false;'], [], '', '', true);
                    }

                }else if($data['action'] != '' && $data['workflow_type'] == 'subscription'){
                    if($data['action'] == 'update_subscription_field'){
                        $fields = [
                            ['id' => 'name', 'name' => _l('wa_name')],
                            ['id' => 'clientid', 'name' => _l('wa_customer')],
                            ['id' => 'description', 'name' => _l('wa_description')],
                        ];

                        $html .= render_select('subscription_field['.$data['nodeid'].']', $fields, ['id', 'name'], 'wa_subscription_field', '', ['df-subscription_field'=> '', 'data-nodeid' => $data['nodeid'], 'onchange' => 'subscription_field_change(this); return false;'], [], '', '', true);
                    }elseif($data['action'] == 'change_status'){
                        $statuses            = get_subscriptions_statuses();
                        $list_st = [];

                        $list_st[] = ['id' => 'not_subscribed', 'name' => _l('not_subscribed')];
                        foreach ($statuses as $status) {
                            $list_st[] = ['id' => $status['id'], 'name' => _l($status['id'])];
                        }

                        $html .= render_select('status['.$data['nodeid'].']', $list_st, ['id', 'name'], '', '', ['df-status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'recruitment_campaign'){
                    if($data['action'] == 'change_campaign_status'){

                        $statuses = [
                            ['id' => 1, 'name' => _l('planning')],
                            ['id' => 2, 'name' => _l('overdue')],
                            ['id' => 3, 'name' => _l('in_progress')],
                            ['id' => 4, 'name' => _l('finish')],
                            ['id' => 5, 'name' => _l('cancel')],
                        ];

                        $html .= render_select('status['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'candidate'){
                    if($data['action'] == 'send_email_to_candidate'){

                        $html .= render_input('subject['.$data['nodeid'].']', 'wa_subject', '', 'number' , ['df-subject' => '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_textarea('content['.$data['nodeid'].']', 'wa_content', '', ['df-content'=> '', 'data-nodeid' => $data['nodeid']]);
                    }else if($data['action'] == 'change_candidate_status'){
                        $statuses = [
                            '1' => ['id' => '1', 'name' => _l('application')],
                            '2' => ['id' => '2', 'name' => _l('potential')],
                            '3' => ['id' => '3', 'name' => _l('interview')],
                            '4' => ['id' => '4', 'name' => _l('won_interview')],
                            '5' => ['id' => '5', 'name' => _l('send_offer')],
                            '6' => ['id' => '6', 'name' => _l('elect')],
                            '7' => ['id' => '7', 'name' => _l('non_elect')],
                            '8' => ['id' => '8', 'name' => _l('unanswer')],
                            '9' => ['id' => '9', 'name' => _l('transferred')],
                            '10' => ['id' => '10', 'name' => _l('freedom')],
                        ];

                        $html .= render_select('candidate_status['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-candidate_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'send_email'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'interview_schedule'){
                    if($data['action'] == 'send_email_to_contact'){
                        $html .= render_input('subject['.$data['nodeid'].']', 'wa_subject', '', 'number' , ['df-subject' => '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_textarea('content['.$data['nodeid'].']', 'wa_content', '', ['df-content'=> '', 'data-nodeid' => $data['nodeid']]);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'leave_request'){
                    if($data['action'] == 'change_approval_status'){
                        $statuses = [
                            ['id' => ' '.'0', 'name' => _l('Create')],
                            ['id' => '1', 'name' => _l('approved')],
                            ['id' => '3', 'name' => _l('Reject')],
                        ];

                        $html .= render_select('approval_status['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-approval_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }else if($data['action'] != '' && $data['workflow_type'] == 'additional_work_hours'){
                     if($data['action'] == 'change_approval_status'){
                        $statuses = [
                            ['id' => ' '.'0', 'name' => _l('status_0')],
                            ['id' => '1', 'name' => _l('status_1')],
                            ['id' => '2', 'name' => _l('status_-1')],
                        ];

                        $html .= render_select('approval_status['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-approval_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'attendance'){
                    if($data['action'] == 'send_email'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_textarea('send_email_content['.$data['nodeid'].']', 'content', '', ['df-send_email_content'=> '', 'data-nodeid' => $data['nodeid']]);

                        $html .= render_select('send_email_to['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_send_email_to', '', ['df-send_email_to'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'vehicle'){
                    if($data['action'] == 'change_status'){
                        if(wa_get_status_modules('fleet')){
                            $statuses = [
                                 ['id' => 'active', 'name' => _l('active')],
                                 ['id' => 'inactive', 'name' => _l('inactive')],
                                 ['id' => 'in_shop', 'name' => _l('in_shop')],
                                 ['id' => 'out_of_service', 'name' => _l('out_of_service')],
                                 ['id' => 'sold', 'name' => _l('sold')],
                            ];

                            $html .= render_select('status['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'create_assignment'){
                        if(wa_get_status_modules('fleet')){
                            $this->load->model('fleet/fleet_model');
                            $drivers = $this->fleet_model->get_driver();
                            $html .= render_select('driver['.$data['nodeid'].']', $drivers, ['staffid', ['firstname', 'lastname']], 'driver', '', ['df-driver'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                        }
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'workperformance'){
                    if($data['action'] == 'change_status'){
                        if(wa_get_status_modules('fleet')){
                            $statuses = fleet_logbook_status();

                            $html .= render_select('status['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'event'){
                    if($data['action'] == 'change_type'){
                        $event_types = [
                          ['id' => 'accident', 'name' => _l('accident')],
                          ['id' => 'parts_damage', 'name' => _l('parts_damage')],
                          ['id' => 'other', 'name' => _l('other')],
                        ];


                        $html .= render_select('type['.$data['nodeid'].']', $event_types, ['id', 'name'], '', '', ['df-type'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'change_driver'){
                        if(wa_get_status_modules('fleet')){
                            $this->load->model('fleet/fleet_model');
                            $drivers = $this->fleet_model->get_driver();
                            $html .= render_select('driver['.$data['nodeid'].']', $drivers, ['staffid', ['firstname', 'lastname']], '', '', ['df-driver'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                        }
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'work_order'){
                    if($data['action'] == 'change_status'){
                        if(wa_get_status_modules('fleet')){
                            $statuses = [
                             ['id' => 'open', 'name' => _l('open')],
                             ['id' => 'in_progress', 'name' => _l('in_progress')],
                             ['id' => 'parts_ordered', 'name' => _l('parts_ordered')],
                             ['id' => 'complete', 'name' => _l('complete')],
                          ];

                            $html .= render_select('status['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'change_vehiche'){

                        if(wa_get_status_modules('fleet')){
                            $this->load->model('fleet/fleet_model');
                            $vehicles = $this->fleet_model->get_vehicle();

                            $html .= render_select('vehiche['.$data['nodeid'].']', $vehicles, ['id', 'name'], '', '', ['df-vehiche'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }

                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'booking'){
                    if($data['action'] == 'change_status'){
                        if(wa_get_status_modules('fleet')){
                            $statuses = fleet_booking_status();

                            $html .= render_select('status['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'fuel'){
                    if($data['action'] == 'change_type'){
                        $fuel_types = [
                          ['id' => 'compressed_natural_gas', 'name' => _l('compressed_natural_gas')],
                          ['id' => 'diesel', 'name' => _l('diesel')],
                          ['id' => 'gasoline', 'name' => _l('gasoline')],
                          ['id' => 'propane', 'name' => _l('propane')],
                        ];

                        $html .= render_select('type['.$data['nodeid'].']', $fuel_types, ['id', 'name'], '', '', ['df-type'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'change_vehiche'){

                        if(wa_get_status_modules('fleet')){
                            $this->load->model('fleet/fleet_model');
                            $vehicles = $this->fleet_model->get_vehicle();

                            $html .= render_select('vehiche['.$data['nodeid'].']', $vehicles, ['id', 'name'], '', '', ['df-vehiche'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'change_vendor'){
                        if(wa_get_status_modules('purchase')){
                            $this->load->model('purchase/purchase_model');
                            $vendors = $this->purchase_model->get_vendor();

                            $html .= render_select('vendor['.$data['nodeid'].']', $vendors, ['userid', 'company'], '', '', ['df-vendor'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'work_center'){
                    if($data['action'] == 'change_working_hours'){
                        if(wa_get_status_modules('manufacturing')){
                            $this->load->model('manufacturing/manufacturing_model');
                            $working_hours = $this->manufacturing_model->get_working_hours();

                            $html .= render_select('working_hour['.$data['nodeid'].']', $working_hours, ['id', 'working_hour_name'], '', '', ['df-working_hour'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'update_cost'){
                        $html .= render_input('costs_hour['.$data['nodeid'].']', 'costs_hour', '', 'number' , ['df-costs_hour' => '', 'data-nodeid' => $data['nodeid']]);
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'routing'){
                    if($data['action'] == 'update_name'){
                        $html .= render_input('routing_name['.$data['nodeid'].']', 'routing_name', '', 'text' , ['df-routing_name' => '', 'data-nodeid' => $data['nodeid']]);
                    }elseif($data['action'] == 'update_note'){
                        $html .= render_textarea('note['.$data['nodeid'].']', 'note', '', ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'operation'){
                    if($data['action'] == 'change_work_center'){
                        if(wa_get_status_modules('manufacturing')){
                            $this->load->model('manufacturing/manufacturing_model');
                            $work_centers = $this->manufacturing_model->get_work_centers();
                            $html .= render_select('work_center['.$data['nodeid'].']', $work_centers, ['id', array('work_center_code', 'work_center_name')], '', '', ['df-work_center'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                        }
                    }elseif($data['action'] == 'update_description'){
                        $html .= render_textarea('description['.$data['nodeid'].']', 'description', '', ['df-description'=> '', 'data-nodeid' => $data['nodeid']]);
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'bill_of_material'){
                    if($data['action'] == 'change_bom_type'){
                        if(wa_get_status_modules('manufacturing')){
                            $types = [
                                ['id' => 'manufacture_this_product' , 'name' => _l('manufacture_this_product')],
                                ['id' => 'kit' , 'name' => _l('kit')],
                            ];
                            $html .= render_select('bom_type['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-bom_type'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'change_ready_to_produce'){
                        $ready_to_produce_type=[];
                        $ready_to_produce_type[] = [
                            'name' => 'all_available',
                            'label' => _l('when_all_components_are_available'),
                        ];
                        $ready_to_produce_type[] = [
                            'name' => 'components_for_1st',
                            'label' => _l('when_components_for_1st_operation_are_available'),
                        ];

                        $html .= render_select('ready_to_produce['.$data['nodeid'].']', $ready_to_produce_type, ['name', 'label'], '', '', ['df-ready_to_produce'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'change_consumption'){
                        $consumption_type=[];
                        $consumption_type[] = [
                            'name' => 'strict',
                            'label' => _l('strict'),
                        ];

                        $consumption_type[] = [
                            'name' => 'flexible',
                            'label' => _l('flexible'),
                        ];

                        $html .= render_select('consumption['.$data['nodeid'].']', $consumption_type, ['name', 'label'], '', '', ['df-consumption'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'bom_component'){
                    if($data['action'] == 'change_unit_of_measure'){

                        if(wa_get_status_modules('manufacturing')){
                            $this->load->model('manufacturing/manufacturing_model');
                            $units = $this->manufacturing_model->mrp_get_unit();

                            $html .= render_select('unit_id['.$data['nodeid'].']', $units, ['unit_type_id','unit_name'], '', '', ['df-unit_id'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }

                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'manufacturing_order'){
                    if($data['action'] == 'change_status'){
                        $status_data=[];
                        $status_data[]=[
                            'name' => 'draft',
                            'label' => _l('mrp_draft'),
                        ];
                        $status_data[]=[
                            'name' => 'planned',
                            'label' => _l('mrp_planned'),
                        ];
                        $status_data[]=[
                            'name' => 'cancelled',
                            'label' => _l('mrp_cancelled'),
                        ];
                        $status_data[]=[
                            'name' => 'confirmed',
                            'label' => _l('mrp_confirmed'),
                        ];
                        $status_data[]=[
                            'name' => 'done',
                            'label' => _l('mrp_done'),
                        ];
                        $status_data[]=[
                            'name' => 'in_progress',
                            'label' => _l('mrp_in_progress'),
                        ];


                        $html .= render_select('status['.$data['nodeid'].']', $status_data, ['name','label'], '', '', ['df-status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }

                }elseif($data['action'] != '' && $data['workflow_type'] == 'omni_sales_order'){
                    if($data['action'] == 'change_status'){
                        if(wa_get_status_modules('omni_sales')){
                            $statuses = omni_status_list();

                            $html .= render_select('status['.$data['nodeid'].']', $statuses, ['id','label'], '', '', ['df-status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'add_shipment_activity_log'){
                         $html .= render_textarea('description['.$data['nodeid'].']', 'shipment_log', '', ['df-description'=> '', 'data-nodeid' => $data['nodeid']]);
                    }elseif($data['action'] == 'change_sale_agent'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);

                        $html .= render_select('staff['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_staff', '', ['df-staff'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'omni_sales_refund'){
                    if($data['action'] == 'update_amount'){
                        $html .= render_input('amount['.$data['nodeid'].']', 'amount', '', 'number' , ['df-amount' => '', 'data-nodeid' => $data['nodeid']]);
                    }elseif($data['action'] == 'change_payment_mode'){
                        $this->load->model('payment_modes_model');
                        $payment_methods = $this->payment_modes_model->get();
                        $html .= render_select('payment_mode['.$data['nodeid'].']', $payment_methods, ['id', 'name'], '', '', ['df-payment_mode'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'trade_discount'){
                    if($data['action'] == 'update_discount'){
                        $html .= render_input('discount['.$data['nodeid'].']', 'amount', '', 'number' , ['df-discount' => '', 'data-nodeid' => $data['nodeid']]);

                    }elseif($data['action'] == 'update_formal'){

                        $formals = [
                            ['id' => '1', 'name' => _l('percent_of_product')],
                            ['id' => '2', 'name' => _l('price')],
                        ];
                        $html .= render_select('formal['.$data['nodeid'].']', $formals, ['id', 'name'], '', '', ['df-formal'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }elseif($data['action'] == 'add_client_to_trade_discount'){
                        $this->load->model('clients_model');

                        $clients = $this->clients_model->get();

                        $html .= render_select('client['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-client'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'update_end_date'){

                        $html .= '<div class="row"><div class="col-md-6">';
                        $html .= render_input('end_time['.$data['nodeid'].']', 'wa_end_after', '', 'number' , ['df-end_time' => '', 'data-nodeid' => $data['nodeid']]);

                        $time_types = [];
                        $time_types = [
                            ['id' => 'days', 'name' => _l('wa_days')],
                            ['id' => 'months', 'name' => _l('wa_months')],
                        ];

                        $html .= '</div>';
                        $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                        $html .= render_select('end_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-end_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                        $html .= '</div></div>';
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'voucher'){
                    if($data['action'] == 'update_discount'){
                        $html .= render_input('discount['.$data['nodeid'].']', 'amount', '', 'text' , ['df-discount' => '', 'data-nodeid' => $data['nodeid']]);

                    }elseif($data['action'] == 'update_type'){

                        $formals = [
                            ['id' => '1', 'name' => _l('coupon_reduce_by_percent')],
                            ['id' => '2', 'name' => _l('voucher_reduce_by_amount')],
                        ];
                        $html .= render_select('type['.$data['nodeid'].']', $formals, ['id', 'name'], '', '', ['df-type'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    }elseif($data['action'] == 'add_client_to_voucher'){
                        $this->load->model('clients_model');

                        $clients = $this->clients_model->get();

                        $html .= render_select('client['.$data['nodeid'].']', $clients, ['userid', 'company'], '', '', ['df-client'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'update_end_date'){
                        $html .= render_input('end_time['.$data['nodeid'].']', 'wa_end_after', '', 'number' , ['df-end_time' => '', 'data-nodeid' => $data['nodeid']]);

                        $time_types = [];
                        $time_types = [
                            ['id' => 'days', 'name' => _l('wa_days')],
                            ['id' => 'months', 'name' => _l('wa_months')],
                        ];

                        $html .= '</div>';
                        $html .= '<div class="col-md-6"><label for="reminder_time_type">&nbsp;</label>';
                        $html .= render_select('end_time_type['.$data['nodeid'].']', $time_types, ['id', 'name'], '', 'days',  ['df-end_time_type'=> '', 'data-nodeid' => $data['nodeid']]);
                        $html .= '</div></div>';
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'asset'){
                    if($data['action'] == 'change_status'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $statuses = $this->fixed_equipment_model->get_status_labels(); 
                            $html .= render_select('status['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }else if($data['action'] == 'change_location'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $locations = $this->fixed_equipment_model->get_locations(); 
                            $html .= render_select('location['.$data['nodeid'].']', $locations, ['id', 'location_name'], '', '', ['df-location'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }else if($data['action'] == 'change_model'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $models = $this->fixed_equipment_model->get_models();
                            $html .= render_select('model['.$data['nodeid'].']', $models, ['id', 'model_name'], '', '', ['df-model'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }else if($data['action'] == 'change_supplier'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $suppliers = $this->fixed_equipment_model->get_suppliers();
                            $html .= render_select('supplier['.$data['nodeid'].']', $suppliers, ['id', 'supplier_name'], '', '', ['df-supplier'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }   
                }elseif($data['action'] != '' && $data['workflow_type'] == 'license'){
                    if($data['action'] == 'change_category'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $categories = $this->fixed_equipment_model->get_categories('', 'license');
                            $html .= render_select('category['.$data['nodeid'].']', $categories, ['id', 'category_name'], '', '', ['df-category'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'change_manufacturer'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $manufacturers = $this->fixed_equipment_model->get_asset_manufacturers();
                            $html .= render_select('manufacturer['.$data['nodeid'].']', $manufacturers, ['id', 'name'], '', '', ['df-manufacturer'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'change_depreciation'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $depreciations = $this->fixed_equipment_model->get_depreciations();
                            $html .= render_select('depreciation['.$data['nodeid'].']', $depreciations, ['id', 'name'], '', '', ['df-depreciation'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'accessories'){
                    if($data['action'] == 'change_category'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $categories = $this->fixed_equipment_model->get_categories('', 'accessory');
                            $html .= render_select('category['.$data['nodeid'].']', $categories, ['id', 'category_name'], '', '', ['df-category'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'change_manufacturer'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $manufacturers = $this->fixed_equipment_model->get_asset_manufacturers();
                            $html .= render_select('manufacturer['.$data['nodeid'].']', $manufacturers, ['id', 'name'], '', '', ['df-manufacturer'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'change_location'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $locations = $this->fixed_equipment_model->get_locations(); 
                            $html .= render_select('location['.$data['nodeid'].']', $locations, ['id', 'location_name'], '', '', ['df-location'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'change_supplier'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $suppliers = $this->fixed_equipment_model->get_suppliers();
                            $html .= render_select('supplier['.$data['nodeid'].']', $suppliers, ['id', 'supplier_name'], '', '', ['df-supplier'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'consumable'){
                    if($data['action'] == 'change_category'){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $categories = $this->fixed_equipment_model->get_categories('', 'consumable');
                        $html .= render_select('category['.$data['nodeid'].']', $categories, ['id', 'category_name'], '', '', ['df-category'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'change_manufacturer'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $manufacturers = $this->fixed_equipment_model->get_asset_manufacturers();
                            $html .= render_select('manufacturer['.$data['nodeid'].']', $manufacturers, ['id', 'name'], '', '', ['df-manufacturer'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'change_location'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $locations = $this->fixed_equipment_model->get_locations(); 
                            $html .= render_select('location['.$data['nodeid'].']', $locations, ['id', 'location_name'], '', '', ['df-location'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'component'){
                    if($data['action'] == 'change_category'){
                        $this->load->model('fixed_equipment/fixed_equipment_model');
                        $categories = $this->fixed_equipment_model->get_categories('', 'component');
                        $html .= render_select('category['.$data['nodeid'].']', $categories, ['id', 'category_name'], '', '', ['df-category'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'change_location'){
                        if(wa_get_status_modules('fixed_equipment')){
                            $this->load->model('fixed_equipment/fixed_equipment_model');
                            $locations = $this->fixed_equipment_model->get_locations(); 
                            $html .= render_select('location['.$data['nodeid'].']', $locations, ['id', 'location_name'], '', '', ['df-location'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'payslip_template'){
                    if($data['action'] == 'payslip_template_apply_to_staff'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        $html .= render_select('staff['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-staff'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'payslip_template_except_for_staff'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        $html .= render_select('except_staff['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-except_staff'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'items'){
                    if($data['action'] == 'change_warehouse'){
                        if(wa_get_status_modules('warehouse')){
                            $this->load->model('warehouse/warehouse_model');
                            $warehouses = $this->warehouse_model->get_warehouse();
                            $html .= render_select('warehouse['.$data['nodeid'].']', $warehouses, array('warehouse_id',array('warehouse_code', 'warehouse_name')), '', '', ['df-warehouse'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'change_commodity_type'){
                        if(wa_get_status_modules('warehouse')){
                            $this->load->model('warehouse/warehouse_model');
                            $types = $this->warehouse_model->get_commodity_type_add_commodity();
                            $html .= render_select('commodity_type['.$data['nodeid'].']', $types, array('commodity_type_id',array('commondity_code', 'commondity_name')), '', '', ['df-commodity_type'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'change_unit'){
                        if(wa_get_status_modules('warehouse')){
                            $this->load->model('warehouse/warehouse_model');
                            $units = $this->warehouse_model->get_unit_type();
                            $html .= render_select('unit['.$data['nodeid'].']', $units, array('unit_type_id',array('unit_code', 'unit_name')), '', '', ['df-unit'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }elseif($data['action'] == 'change_commodity_group'){
                        if(wa_get_status_modules('warehouse')){
                            $this->load->model('warehouse/warehouse_model');
                            $groups = $this->warehouse_model->get_commodity_group_add_commodity();

                            $html .= render_select('commodity_group['.$data['nodeid'].']', $groups, array('id',array('commodity_group_code', 'name')), '', '', ['df-commodity_group'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                        }
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'inventory_receiving_voucher'){
                    if($data['action'] == 'change_project'){
                        $projects = $this->projects_model->get();

                        $html .= render_select('project['.$data['nodeid'].']', $projects, ['id', 'name'], 'wa_project', '', ['df-project'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'change_department'){
                        $this->load->model('departments_model');
                        $departments = $this->departments_model->get();

                        $html .= render_select('department['.$data['nodeid'].']', $departments, ['departmentid', 'name'], '', '', ['df-department'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'update_note'){
                        $html .= render_textarea('note['.$data['nodeid'].']', 'shipment_log', '', ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);

                    }elseif($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'inventory_delivery_voucher'){
                    if($data['action'] == 'change_project'){
                        $projects = $this->projects_model->get();

                        $html .= render_select('project['.$data['nodeid'].']', $projects, ['id', 'name'], 'wa_project', '', ['df-project'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'change_department'){
                        $this->load->model('departments_model');
                        $departments = $this->departments_model->get();

                        $html .= render_select('department['.$data['nodeid'].']', $departments, ['departmentid', 'name'], '', '', ['df-department'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }elseif($data['action'] == 'update_note'){
                        $html .= render_textarea('note['.$data['nodeid'].']', 'shipment_log', '', ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);

                    }elseif($data['action'] == 'create_task'){
                        $task_templates = $this->Workflow_automation_model->get_task_templates();

                        $html .= render_select('task_template['.$data['nodeid'].']', $task_templates, ['id', 'template_name'], 'wa_task_template', '', ['df-task_template'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'packing_list'){
                    if($data['action'] == 'change_delivery_status'){
                        if(wa_get_status_modules('warehouse')){
                            $statuses = packing_list_status();
                            $html .= render_select('delivery_status['.$data['nodeid'].']', $statuses, ['id', 'name'], '', '', ['df-delivery_status'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                        }
                    }elseif($data['action'] == 'add_shipping_log'){
                        $html .= render_textarea('wh_activity_textarea['.$data['nodeid'].']', 'shipment_log', '', ['df-wh_activity_textarea'=> '', 'data-nodeid' => $data['nodeid']]);
                    }
                }elseif($data['action'] != '' && $data['workflow_type'] == 'inventory_delivery_note'){
                    if($data['action'] == 'change_deliverer'){
                        $this->load->model('staff_model');
                        $staff = $this->staff_model->get('', ['active' => 1]);
                        $html .= render_select('staff_id['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-staff_id'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }
            }
    	}  

    	echo json_encode([
    		'html' => $html,
    	]);
    }

    /**
     * [settings description]
     * @return [type] [description]
     */
    public function settings(){
        

        $data['group'] = $this->input->get('group');

        $data['title']                 = _l('wa_settings');

        $data['tab'][] = 'task_template';
        $data['tab'][] = 'categories';
        $data['tab'][] = 'permissions';

        if($data['group'] == ''){
            $data['group'] = 'task_template';
        }

        if($data['group'] == 'task_template'){
            $data['task_templates'] = $this->Workflow_automation_model->get_task_templates();
        }else if($data['group'] == 'categories'){

            $data['categories'] = $this->Workflow_automation_model->get_categories();
        }

        $data['members'] = $this->staff_model->get('', ['active' => 1]);

        $data['tabs']['view'] = 'settings/includes/'.$data['group'];


        $this->load->view('settings/manage_setting', $data);

    }

    /**
     * [task_template_form description]
     * @return [type] [description]
     */
    public function task_template_form(){
        if($this->input->post()){
            $data = $this->input->post();
            if($data['task_template_id'] == ''){

                unset($data['task_template_id']);
                $id = $this->Workflow_automation_model->add_task_template($data);

                if($id){
                    set_alert('success', _l('added_successfully'));
                }

            }else{
                $template_id = $data['task_template_id'];
                unset($data['task_template_id']);

                $success = $this->Workflow_automation_model->update_task_template($data, $template_id);

                if($success){
                    set_alert('success', _l('updated_successfully'));
                }
            }

            redirect(admin_url('workflow_automation/settings?group=task_template'));
        }
    }

    /**
     * [delete_task_template description]
     * @return [type] [description]
     */
    public function delete_task_template($id){
        if(!$id){
            redirect(admin_url('workflow_automation/settings?group=task_template'));
        }

        $success = $this->Workflow_automation_model->delete_task_template($id);
        if($success){
            set_alert('success', _l('deleted_successfully'));
        }

        redirect(admin_url('workflow_automation/settings?group=task_template'));
    }

    /**
     * [category_form description]
     * @return [type] [description]
     */
    public function category_form(){
        if($this->input->post()){
            $data = $this->input->post();
            if($data['category_id'] == ''){

                unset($data['category_id']);
                $id = $this->Workflow_automation_model->add_category($data);

                if($id){
                    set_alert('success', _l('added_successfully'));
                }

            }else{
                $category_id = $data['category_id'];
                unset($data['category_id']);

                $success = $this->Workflow_automation_model->update_category($data, $category_id);

                if($success){
                    set_alert('success', _l('updated_successfully'));
                }
            }

            redirect(admin_url('workflow_automation/settings?group=categories'));
        }
    }

    /**
     * [delete_category description]
     * @return [type] [description]
     */
    public function delete_category($id){
        if(!$id){
            redirect(admin_url('workflow_automation/settings?group=categories'));
        }

        $success = $this->Workflow_automation_model->delete_category($id);
        if($success){
            set_alert('success', _l('deleted_successfully'));
        }
        redirect(admin_url('workflow_automation/settings?group=categories'));
    }

    /**
     * [get_start_case_for_data_type description]
     * @return [type] [description]
     */
    public function get_start_case_for_data_type(){

        $html = '<option value=""></option>';
        if($this->input->post()){
            $data = $this->input->post();
            $list = [];
            if(isset($data['workflow_type'])){
                $list = wa_get_start_case_by_type($data['workflow_type']);
            }

            foreach ($list as $key => $case) {
                $html .= '<option value="'.$case['id'].'">'.$case['name'].'</option>';
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [get_task_field description]
     * @return [type] [description]
     */
    public function get_task_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'name'){
                    $html .= render_input('task_name['.$data['nodeid'].']', 'wa_subject', '', 'text',  ['df-task_name'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'hourly_rate'){
                    $html .= render_input('task_hourly_rate['.$data['nodeid'].']', 'wa_hourly_rate', '', 'number',  ['df-task_hourly_rate'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'startdate'){
                    $html .= render_date_input('task_startdate['.$data['nodeid'].']', 'wa_startdate', '',  ['df-task_startdate'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'duedate'){
                    $html .= render_date_input('task_duedate['.$data['nodeid'].']', 'wa_duedate', '',  ['df-task_duedate'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'description'){
                    $html .= render_textarea('task_description['.$data['nodeid'].']', 'wa_description', '',  ['df-task_description'=> '', 'data-nodeid' => $data['nodeid']]);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }


    /**
     * [get_project_field description]
     * @return [type] [description]
     */
    public function get_project_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'name'){
                    $html .= render_input('project_name['.$data['nodeid'].']', 'wa_project_name', '', 'text',  ['df-project_name'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'hourly_rate'){
                    $html .= render_input('project_hourly_rate['.$data['nodeid'].']', 'wa_hourly_rate', '', 'number',  ['df-project_hourly_rate'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'description'){
                    $html .= render_textarea('project_description['.$data['nodeid'].']', 'wa_description', '',  ['df-project_description'=> '', 'data-nodeid' => $data['nodeid']]);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [get_lead_field description]
     * @return [type] [description]
     */
    public function get_lead_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'name'){
                    $html .= render_input('name['.$data['nodeid'].']', 'wa_name', '', 'text',  ['df-name'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'address'){
                    $html .= render_input('address['.$data['nodeid'].']', 'wa_address', '', 'text',  ['df-address'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'position'){
                    $html .= render_input('position['.$data['nodeid'].']', 'wa_position', '', 'text',  ['df-position'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'description'){
                    $html .= render_textarea('description['.$data['nodeid'].']', 'wa_description', '',  ['df-description'=> '', 'data-nodeid' => $data['nodeid']]);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [get_customer_field description]
     * @return [type] [description]
     */
    public function get_customer_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'company'){
                    $html .= render_input('company['.$data['nodeid'].']', 'wa_company', '', 'text',  ['df-company'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'vat'){
                    $html .= render_input('vat['.$data['nodeid'].']', 'wa_vat', '', 'text',  ['df-vat'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'phonenumber'){
                    $html .= render_input('phonenumber['.$data['nodeid'].']', 'wa_phonenumber', '', 'number',  ['df-phonenumber'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'website'){
                    $html .= render_input('website['.$data['nodeid'].']', 'wa_website', '', 'text',  ['df-website'=> '', 'data-nodeid' => $data['nodeid']]);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [get_proposal_field description]
     * @return [type] [description]
     */
    public function get_proposal_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'subject'){
                    $html .= render_input('subject['.$data['nodeid'].']', 'wa_subject', '', 'text',  ['df-subject'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'assigned'){
                    
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('assigned['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_staff', '', ['df-assigned'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }elseif($data['field'] == 'proposal_to'){
                    $html .= render_input('proposal_to['.$data['nodeid'].']', 'wa_proposal_to', '', 'text',  ['df-proposal_to'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'email'){
                    $html .= render_input('email['.$data['nodeid'].']', 'wa_email', '', 'text',  ['df-email'=> '', 'data-nodeid' => $data['nodeid']]);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }


    /**
     * [get_estimate_field description]
     * @return [type] [description]
     */
    public function get_estimate_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'reference_no'){
                    $html .= render_input('reference_no['.$data['nodeid'].']', 'wa_reference_no', '', 'text',  ['df-reference_no'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'sale_agent'){
                    
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('sale_agent['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_staff', '', ['df-sale_agent'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }elseif($data['field'] == 'adminnote'){
                    $html .= render_textarea('adminnote['.$data['nodeid'].']', 'wa_adminnote', '',  ['df-adminnote'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'clientnote'){
                    $html .= render_textarea('clientnote['.$data['nodeid'].']', 'wa_clientnote', '',  ['df-clientnote'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'terms'){
                    $html .= render_textarea('terms['.$data['nodeid'].']', 'wa_terms', '',  ['df-terms'=> '', 'data-nodeid' => $data['nodeid']]);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [get_invoice_field description]
     * @return [type] [description]
     */
    public function get_invoice_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'sale_agent'){
                    
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('sale_agent['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], 'wa_staff', '', ['df-sale_agent'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }elseif($data['field'] == 'adminnote'){
                    $html .= render_textarea('adminnote['.$data['nodeid'].']', 'wa_adminnote', '',  ['df-adminnote'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'clientnote'){
                    $html .= render_textarea('clientnote['.$data['nodeid'].']', 'wa_clientnote', '',  ['df-clientnote'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'terms'){
                    $html .= render_textarea('terms['.$data['nodeid'].']', 'wa_terms', '',  ['df-terms'=> '', 'data-nodeid' => $data['nodeid']]);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }


    /**
     * [get_credit_note_field description]
     * @return [type] [description]
     */
    public function get_credit_note_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'reference_no'){

                    $html .= render_input('reference_no['.$data['nodeid'].']', 'wa_reference_no', '', 'text',  ['df-reference_no'=> '', 'data-nodeid' => $data['nodeid']]);

                }elseif($data['field'] == 'adminnote'){
                    $html .= render_textarea('adminnote['.$data['nodeid'].']', 'wa_adminnote', '',  ['df-adminnote'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'clientnote'){
                    $html .= render_textarea('clientnote['.$data['nodeid'].']', 'wa_clientnote', '',  ['df-clientnote'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'terms'){
                    $html .= render_textarea('terms['.$data['nodeid'].']', 'wa_terms', '',  ['df-terms'=> '', 'data-nodeid' => $data['nodeid']]);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [get_payment_field description]
     * @return [type] [description]
     */
    public function get_payment_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'amount'){

                    $html .= render_input('amount['.$data['nodeid'].']', 'wa_amount', '', 'number',  ['df-amount'=> '', 'data-nodeid' => $data['nodeid']]);

                }elseif($data['field'] == 'paymentmethod'){
                    $html .= render_input('paymentmethod['.$data['nodeid'].']', 'wa_paymentmethod', '', 'text',  ['df-paymentmethod'=> '', 'data-nodeid' => $data['nodeid']]);

                }elseif($data['field'] == 'paymentmode'){
                    
                    $this->load->model('payment_modes_model');
                    $payment_methods = $this->payment_modes_model->get();
                    $html .= render_select('paymentmode['.$data['nodeid'].']', $payment_methods, ['id', 'name'], '', '', ['df-paymentmode'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    
                }elseif($data['field'] == 'transactionid'){
                     $html .= render_input('transactionid['.$data['nodeid'].']', 'wa_transactionid', '', 'text',  ['df-transactionid'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'note'){
                     $html .= render_textarea('note['.$data['nodeid'].']', 'wa_note', '',  ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }


    /**
     * [get_purchase_order_field description]
     * @return [type] [description]
     */
    public function get_purchase_order_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'vendor'){
                    if(wa_get_status_modules('purchase')){
                        $this->load->model('purchase/purchase_model');
                        $vendors = $this->purchase_model->get_vendor();
                        $html .= render_select('vendor['.$data['nodeid'].']', $vendors, ['userid', 'company'], '', '', ['df-vendor'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }
                }elseif($data['field'] == 'buyer'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('buyer['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-buyer'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['field'] == 'type'){
                    $types = [
                        ['id' => 'capex', 'name' => 'CAPEX'],
                        ['id' => 'opex', 'name' => 'OPEX'],
                    ];

                    $html .= render_select('type['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-type'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }elseif($data['field'] == 'vendornote'){
                    $html .= render_textarea('vendornote['.$data['nodeid'].']', 'wa_vendornote', '',  ['df-vendornote'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'terms'){
                    $html .= render_textarea('terms['.$data['nodeid'].']', 'wa_terms', '',  ['df-terms'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'project'){
                    $this->load->model('projects_model');
                    $projects = $this->projects_model->get();

                    $html .= render_select('project['.$data['nodeid'].']', $projects, ['id', 'name'], '', '', ['df-project'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['field'] == 'purchase_order_description'){
                    $html .= render_input('pur_order_name['.$data['nodeid'].']', 'pur_order_name', '', 'text',  ['df-pur_order_name'=> '', 'data-nodeid' => $data['nodeid']]);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [get_purchase_request_field description]
     * @return [type] [description]
     */
    public function get_purchase_request_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'requester'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('requester['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-requester'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['field'] == 'type'){
                    $types = [
                        ['id' => 'capex', 'name' => 'CAPEX'],
                        ['id' => 'opex', 'name' => 'OPEX'],
                    ];

                    $html .= render_select('type['.$data['nodeid'].']', $types, ['id', 'name'], '', '', ['df-type'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                }elseif($data['field'] == 'project'){
                    $this->load->model('projects_model');
                    $projects = $this->projects_model->get();

                    $html .= render_select('project['.$data['nodeid'].']', $projects, ['id', 'name'], '', '', ['df-project'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['field'] == 'description'){
                   

                    $html .= render_textarea('description['.$data['nodeid'].']', 'wa_description', '',  ['df-description'=> '', 'data-nodeid' => $data['nodeid']]);

                }elseif($data['field'] == 'pur_rq_name'){
                   

                    $html .= render_input('pur_rq_name['.$data['nodeid'].']', 'wa_pur_rq_name', '', 'text', ['df-pur_rq_name'=> '', 'data-nodeid' => $data['nodeid']]);

                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [get_purchase_quotation_field description]
     * @return [type] [description]
     */
    public function get_purchase_quotation_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'buyer'){
                    $this->load->model('staff_model');
                    $staff = $this->staff_model->get('', ['active' => 1]);

                    $html .= render_select('buyer['.$data['nodeid'].']', $staff, ['staffid', 'full_name'], '', '', ['df-buyer'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }elseif($data['field'] == 'vendor'){
                   if(wa_get_status_modules('purchase')){
                        $this->load->model('purchase/purchase_model');
                        $vendors = $this->purchase_model->get_vendor();
                        $html .= render_select('vendor['.$data['nodeid'].']', $vendors, ['userid', 'company'], '', '', ['df-vendor'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }

                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }


    /**
     * [get_contract_field description]
     * @return [type] [description]
     */
    public function get_purchase_invoice_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'vendor_invoice_number'){
                    $html .= render_input('vendor_invoice_number['.$data['nodeid'].']', 'wa_vendor_invoice_number', '', 'text', ['df-vendor_invoice_number'=> '', 'data-nodeid' => $data['nodeid']]);
                  
                }else if($data['field'] == 'transactionid'){
                    $html .= render_input('transactionid['.$data['nodeid'].']', 'wa_transactionid', '', 'number', ['df-transactionid'=> '', 'data-nodeid' => $data['nodeid']]);
                }elseif($data['field'] == 'vendor'){
                   if(wa_get_status_modules('purchase')){
                        $this->load->model('purchase/purchase_model');
                        $vendors = $this->purchase_model->get_vendor();
                        $html .= render_select('vendor['.$data['nodeid'].']', $vendors, ['userid', 'company'], '', '', ['df-vendor'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                    }

                }elseif($data['field'] == 'vendornote'){
                   

                    $html .= render_textarea('vendornote['.$data['nodeid'].']', 'wa_vendornote', '',  ['df-vendornote'=> '', 'data-nodeid' => $data['nodeid']]);

                }elseif($data['field'] == 'terms'){
                   

                    $html .= render_textarea('terms['.$data['nodeid'].']', 'wa_terms', '',  ['df-terms'=> '', 'data-nodeid' => $data['nodeid']]);

                }elseif($data['field'] == 'adminnote'){
                   

                    $html .= render_textarea('adminnote['.$data['nodeid'].']', 'wa_adminnote', '',  ['df-adminnote'=> '', 'data-nodeid' => $data['nodeid']]);

                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }


    /**
     * [get_vendor_field description]
     * @return [type] [description]
     */
    public function get_vendor_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'company'){
                    $html .= render_input('company['.$data['nodeid'].']', 'wa_company', '', 'text', ['df-company'=> '', 'data-nodeid' => $data['nodeid']]);
                  
                }else if($data['field'] == 'vat'){
                    $html .= render_input('vat['.$data['nodeid'].']', 'wa_vat', '', 'text', ['df-vat'=> '', 'data-nodeid' => $data['nodeid']]);
                }else if($data['field'] == 'phone'){
                    $html .= render_input('phone['.$data['nodeid'].']', 'wa_phone', '', 'text', ['df-phone'=> '', 'data-nodeid' => $data['nodeid']]);
                }else if($data['field'] == 'website'){
                    $html .= render_input('website['.$data['nodeid'].']', 'wa_website', '', 'text', ['df-website'=> '', 'data-nodeid' => $data['nodeid']]);
                }else if($data['field'] == 'address'){
                    $html .= render_textarea('address['.$data['nodeid'].']', 'wa_address', '',  ['df-address'=> '', 'data-nodeid' => $data['nodeid']]);
                }else if($data['field'] == 'city'){
                    $html .= render_input('city['.$data['nodeid'].']', 'wa_city', '', 'text', ['df-city'=> '', 'data-nodeid' => $data['nodeid']]);
                }else if($data['field'] == 'state'){
                    $html .= render_input('state['.$data['nodeid'].']', 'wa_state', '', 'text', ['df-state'=> '', 'data-nodeid' => $data['nodeid']]);
                }else if($data['field'] == 'country'){
                    $countries = get_all_countries();
                    $html .= render_select('country['.$data['nodeid'].']', $countries, [ 'country_id', [ 'short_name']], '', '', ['df-country'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['field'] == 'zip'){
                    $html .= render_input('zip['.$data['nodeid'].']', 'wa_zip', '', 'text', ['df-zip'=> '', 'data-nodeid' => $data['nodeid']]);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }


    /**
     * [get_expense_field description]
     * @return [type] [description]
     */
    public function get_expense_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'name'){
                    $html .= render_input('name['.$data['nodeid'].']', 'wa_name', '', 'text', ['df-name'=> '', 'data-nodeid' => $data['nodeid']]);
                  
                }else if($data['field'] == 'amount'){
                    $html .= render_input('amount['.$data['nodeid'].']', 'wa_amount', '', 'number', ['df-amount'=> '', 'data-nodeid' => $data['nodeid']]);
                }else if($data['field'] == 'note'){
                    $html .= render_textarea('note['.$data['nodeid'].']', 'wa_note', '',  ['df-note'=> '', 'data-nodeid' => $data['nodeid']]);
                }else if($data['field'] == 'clientid'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();
                    
                    $html .= render_select('clientid['.$data['nodeid'].']', $clients, [ 'userid', 'company'], '', '', ['df-clientid'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }


    /**
     * [get_subscription_field description]
     * @return [type] [description]
     */
    public function get_subscription_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'name'){
                    $html .= render_input('name['.$data['nodeid'].']', 'wa_name', '', 'text', ['df-name'=> '', 'data-nodeid' => $data['nodeid']]);
                  
                }else if($data['field'] == 'description'){
                    $html .= render_textarea('description['.$data['nodeid'].']', 'wa_description', '',  ['df-description'=> '', 'data-nodeid' => $data['nodeid']]);
                }else if($data['field'] == 'clientid'){
                    $this->load->model('clients_model');
                    $clients = $this->clients_model->get();
                    
                    $html .= render_select('clientid['.$data['nodeid'].']', $clients, [ 'userid', 'company'], '', '', ['df-clientid'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [get_purchase_contract_field description]
     * @return [type] [description]
     */
    public function get_purchase_contract_field(){
        $html = '';
        if($this->input->post()){
            $data = $this->input->post();

            if(isset($data['field'])){
                if($data['field'] == 'service_category'){
                    $html .= render_input('service_category['.$data['nodeid'].']', 'wa_service_category', '', 'text', ['df-service_category'=> '', 'data-nodeid' => $data['nodeid']]);
                  
                }else if($data['field'] == 'contract_value'){
                    $html .= render_input('contract_value['.$data['nodeid'].']', 'wa_contract_value', '', 'number', ['df-contract_value'=> '', 'data-nodeid' => $data['nodeid']]);
                }else if($data['field'] == 'vendor'){
                    $this->load->model('purchase/purchase_model');
                    $vendors = $this->purchase_model->get_vendor();
                    
                    $html .= render_select('vendor['.$data['nodeid'].']', $vendors, [ 'userid', 'company'], '', '', ['df-vendor'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }else if($data['field'] == 'department'){
                    $this->load->model('departments_model');
                    $departments = $this->departments_model->get();
                    
                    $html .= render_select('department['.$data['nodeid'].']', $departments, [ 'departmentid', 'name'], '', '', ['df-department'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);
                }
            }
        }

        echo json_encode([
            'html' => $html,
        ]);
    }

    /**
     * [workflow_automation_update_permissions description]
     * @return [type] [description]
     */
    public function workflow_automation_update_permissions($id = ''){
        if (!is_admin() && !has_permission('workflow_automation_setting', '', 'edit')) {
            access_denied('workflow_automation');
        }
        $data = $this->input->post();

        if(!isset($id) || $id == ''){
            $id   = $data['staff_id'];
        }


        if(isset($id) && $id != ''){

            $data = hooks()->apply_filters('before_update_staff_member', $data, $id);

            if (is_admin()) {
                if (isset($data['administrator'])) {
                    $data['admin'] = 1;
                    unset($data['administrator']);
                } else {
                    if ($id != get_staff_user_id()) {
                        if ($id == 1) {
                            return [
                                'cant_remove_main_admin' => true,
                            ];
                        }
                    } else {
                        return [
                            'cant_remove_yourself_from_admin' => true,
                        ];
                    }
                    $data['admin'] = 0;
                }
            }

            $this->db->where('staffid', $id);
            $this->db->update(db_prefix() . 'staff', [
                'role'  => $data['role']
            ]);

            $response = $this->staff_model->update_permissions((isset($data['admin']) && $data['admin'] == 1 ? [] : $data['permissions']), $id);
        }else{
            $this->load->model('roles_model');

            $role_id = $data['role'];
            unset($data['role']);
            unset($data['staff_id']);

            $data['update_staff_permissions'] = true;

            $response = $this->roles_model->update($data, $role_id);
        }

        if (is_array($response)) {
            if (isset($response['cant_remove_main_admin'])) {
                set_alert('warning', _l('staff_cant_remove_main_admin'));
            } elseif (isset($response['cant_remove_yourself_from_admin'])) {
                set_alert('warning', _l('staff_cant_remove_yourself_from_admin'));
            }
        } elseif ($response == true) {
            set_alert('success', _l('updated_successfully', _l('staff_member')));
        }
        redirect(admin_url('workflow_automation/settings?group=permissions'));
    }

    /**
     * delete workflow_automation permission
     * @param  [type] $id 
     * @return [type]     
     */
    public function delete_workflow_automation_permission($id)
    {
        if(!is_admin() && !has_permission('workflow_automation_setting', '', 'edit')) {
            access_denied('workflow_automation');
        }

        $response = $this->workflow_automation_model->delete_hr_profile_permission($id);

        if (is_array($response) && isset($response['referenced'])) {
            set_alert('warning', _l('pur_is_referenced', _l('permissions')));
        } elseif ($response == true) {
            set_alert('success', _l('deleted', _l('permissions')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('permissions')));
        }
        redirect(admin_url('workflow_automation/settings?group=permissions'));

    }

    /**
     * staff id changed
     * @param  [type] $staff_id 
     * @return [type]           
     */
    public function staff_id_changed($staff_id)
    {   
        $role_id = '';
        $status = 'false';
        $r_permission=[];

        $staff  = $this->staff_model->get($staff_id);

        if($staff){
            if(count($staff->permissions) > 0){
                foreach ($staff->permissions as $permission) {
                    $r_permission[$permission['feature']][] = $permission['capability'];
                }
            }

            $role_id = $staff->role;
            $status = 'true';

        }

        if(count($r_permission) > 0){
            $data=['role_id'   => $role_id, 'status'    => $status, 'permission' => 'true', 'r_permission' => $r_permission];
        }else{
            $data=['role_id'   => $role_id, 'status'    => $status, 'permission' => 'false', 'r_permission' => $r_permission];
        }

        echo json_encode($data); 
        die;
    }

    /**
     * [permission_table description]
     * @return [type] [description]
     */
    public function permission_table()
    {
        if ($this->input->is_ajax_request()) {

            $select = [
                'staffid',
                'CONCAT(firstname," ",lastname) as full_name',
                'firstname', //for role name
                'email',
                'phonenumber',
            ];
            $where = [];
            $where[] = 'AND '.db_prefix().'staff.admin != 1';

            $arr_staff_id = workflow_automation_get_staff_id_permissions();

            if(count($arr_staff_id) > 0){
                $where[] = 'AND '.db_prefix().'staff.staffid IN (' . implode(', ', $arr_staff_id) . ')';
            }else{
                $where[] = 'AND '.db_prefix().'staff.staffid IN ("")';
            }

            $aColumns     = $select;
            $sIndexColumn = 'staffid';
            $sTable       = db_prefix() . 'staff';
            $join         = ['LEFT JOIN ' . db_prefix() . 'roles ON ' . db_prefix() . 'roles.roleid = ' . db_prefix() . 'staff.role'];

            $result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, [ db_prefix() . 'roles.name as role_name', db_prefix() . 'staff.role']);

            $output  = $result['output'];
            $rResult = $result['rResult'];

            $not_hide = '';

            foreach ($rResult as $aRow) {
                $row = [];

                $row[] = '<a href="' . admin_url('staff/member/' . $aRow['staffid']) . '">' . $aRow['full_name']  . '</a>';

                $row[] = $aRow['role_name'];
                $row[] = $aRow['email'];
                $row[] = $aRow['phonenumber'];

                $options ='';

                if(is_admin() || has_permission('workflow_automation_setting', '', 'edit')){
                    $options = icon_btn('#', 'fa fa-pencil-square', 'btn-default', [
                        'title'   => _l('edit'),
                        'onclick' => 'permissions_update(' . $aRow['staffid'] . ', '.$aRow['role'].', '.$not_hide.'); return false;',
                    ]);
                }

                if(is_admin() || has_permission('workflow_automation_setting', '', 'edit')){
                    $options .= icon_btn('workflow_automation/delete_workflow_automation_permission/' . $aRow['staffid'], 'fa fa-remove', 'btn-danger _delete', ['title' => _l('delete')]);
                }

                $row[] = $options;

                $output['aaData'][] = $row;
            }

            echo json_encode($output);
            die();
        }
    }

    /**
     * permission modal
     * @return [type] 
     */
    public function permission_modal()
    {
        if (!$this->input->is_ajax_request()) {
            show_404();
        }
        $this->load->model('staff_model');

        if ($this->input->post('slug') === 'update') {
            $staff_id = $this->input->post('staff_id');
            $role_id = $this->input->post('role_id');

            $data = [ 'funcData' => ['staff_id'=> isset($staff_id) ? $staff_id : null ] ];

            if(isset($staff_id)) {
                $data['member']  = $this->staff_model->get($staff_id);
            }

            $data['roles_value']         = $this->roles_model->get();
            $data['staffs']  = workflow_automation_get_staff_id_dont_permissions();
            $add_new = $this->input->post('add_new');

            if($add_new == ' hide'){
                $data['add_new']        = ' hide';
                $data['display_staff']  = '';
            }else{
                $data['add_new'] = '';
                $data['display_staff']  = ' hide';
            }


            $this->load->view('settings/includes/permissions_modal', $data);
        }
    }

    /**
     * change settings status
     * @param  [type] $id     
     * @param  [type] $status 
     * @return [type]         
     */
    public function change_workflow_status($id, $status)
    {
        if (has_permission('workflow_automation_setting', '', 'edit')) {
            if ($this->input->is_ajax_request()) {
                $this->Workflow_automation_model->change_workflow_status($id, $status);
            }
        }
    }

    /**
     * [history description]
     * @return [type] [description]
     */
    public function history(){

        if(!has_permission('workflow_automation_history', '', 'view')){
            access_denied('history');
        }
        $data['title'] = _l('wa_histories');

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('workflow_automation', 'histories/table_histories'));
        }

        $data['workflows'] = $this->Workflow_automation_model->get_workflows();

        $this->load->view('histories/manage', $data);
    }

    /**
     * [clear_logs description]
     * @return [type] [description]
     */
    public function clear_logs(){
        if(!is_admin()){
            access_denied('histories');
        }

        $this->db->where('1=1');
        $this->db->delete(db_prefix().'wa_action_logs');

        $this->db->where('1=1');
        $this->db->delete(db_prefix().'wa_automatic_log');
        
        $this->db->where('1=1');
        $this->db->delete(db_prefix().'wa_flows_logs');
        if($this->db->affected_rows() > 0){
            set_alert('success', _l('clear_logs_successfully'));
        }

        redirect(admin_url('workflow_automation/history'));
    }

    /**
     * [get_repeat_every_html description]
     * @return [type] [description]
     */
    public function get_repeat_every_html(){

        $html = '';
        if($this->input->post()){
            $data = $this->input->post();
            if(isset($data['repeat_every'])){

                if($data['repeat_every'] == 'day'){
                    $html .= '<label for="hour_of_day['.$data['nodeid'].']"><i class="fa-regular fa-circle-question" data-toggle="tooltip" data-placement="top" title="'._l('wa_hour_of_day_note').'" ></i> '._l('wa_hour_of_day').'</label>';
                    $html .= render_input('hour_of_day['.$data['nodeid'].']', '', '', 'number', ['df-hour_of_day'=> '', 'data-nodeid' => $data['nodeid'] , 'step' => 1, 'max' => 24, 'min' => 0]);
                }elseif($data['repeat_every'] == 'week'){
                    $days = [
                        ['id' => 1, 'name' => _l('wa_monday')],
                        ['id' => 2, 'name' => _l('wa_tuesday')],
                        ['id' => 3, 'name' => _l('wa_wednesday')],
                        ['id' => 4, 'name' => _l('wa_thursday')],
                        ['id' => 5, 'name' => _l('wa_friday')],
                        ['id' => 6, 'name' => _l('wa_saturday')],
                        ['id' => 7, 'name' => _l('wa_sunday')],
                    ];

                    $html .= render_select('day_of_week['.$data['nodeid'].']', $days, [ 'id', 'name'], 'wa_day_of_week', '', ['df-day_of_week'=> '', 'data-nodeid' => $data['nodeid']], [], '', '', true);

                    $html .= '<label for="hour_of_day['.$data['nodeid'].']"><i class="fa-regular fa-circle-question" data-toggle="tooltip" data-placement="top" title="'._l('wa_hour_of_day_note').'" ></i> '._l('wa_hour_of_day').'</label>';
                    $html .= render_input('hour_of_day['.$data['nodeid'].']', '', '', 'number', ['df-hour_of_day'=> '', 'data-nodeid' => $data['nodeid'] , 'step' => 1, 'max' => 24, 'min' => 0]);
                }elseif($data['repeat_every'] == 'month'){
                    $html .= render_input('day_of_month['.$data['nodeid'].']', 'wa_day_of_month', '', 'number', ['df-day_of_month'=> '', 'data-nodeid' => $data['nodeid'] , 'step' => 1, 'max' => 30, 'min' => 1]);

                    $html .= '<label for="hour_of_day['.$data['nodeid'].']"><i class="fa-regular fa-circle-question" data-toggle="tooltip" data-placement="top" title="'._l('wa_hour_of_day_note').'" ></i> '._l('wa_hour_of_day').'</label>';
                    $html .= render_input('hour_of_day['.$data['nodeid'].']', '', '', 'number', ['df-hour_of_day'=> '', 'data-nodeid' => $data['nodeid'] , 'step' => 1, 'max' => 24, 'min' => 0]);
                }else{
                    $html .= render_datetime_input('time['.$data['nodeid'].']', 'wa_time', '', ['df-time'=> '', 'data-nodeid' => $data['nodeid']]);
                }
            }

        }

        echo json_encode([
            'html' => $html,
        ]);
    }

}