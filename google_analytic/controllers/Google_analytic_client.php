<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * Google analytic client Controller
 */
require 'modules/google_analytic/vendor/autoload.php';
class Google_analytic_client extends ClientsController
{
    public function index()
    {
        if(is_client_logged_in()){
            $this->load->model('google_analytic_model');
            $data['title'] = _l('google_analytic');
            $data['base_workspace'] = $this->google_analytic_model->get_workspace(ga_get_contact_base_workspace_id());

            $this->data($data);

            $this->view('clients/manage');
            $this->layout();
        }else{
            redirect(site_url());
        }
    }

    /**
     * [get_data_analytics]
     */
    public function get_data_analytics(){
        $this->load->model('google_analytic_model');

        $data_filter = $this->input->post();
        $data = [];

        if(isset($data_filter['metrics'])){
            $this->google_analytic_model->update_analytic_metrics_client($data_filter);
        }

        switch ($data_filter['type']) {
            case 'top_stats':
                $data_filter['top_stats'] = $this->google_analytic_model->get_google_analytic_top_stats($data_filter);
                $this->load->view('analytics/'.$data_filter['social'].'/top_stats', $data_filter);
                break;
            case 'session_per_day':
                $data['session_per_day'] = $this->google_analytic_model->get_google_analytic_chart($data_filter);
                echo json_encode($data);
                break;
            case 'session_per_channel':
                $data['session_per_channel'] = $this->google_analytic_model->get_google_analytic_column_chart($data_filter);
                echo json_encode($data);
                break;
            case 'session_per_channel_pie':
                $data['session_per_channel'] = $this->google_analytic_model->get_google_analytic_pie_chart($data_filter);
                echo json_encode($data);
                break;
            case 'table_data':
                $data_filter['data'] = $this->google_analytic_model->get_google_analytic_table_data($data_filter);
                $this->load->view('analytics/'.$data_filter['social'].'/table', $data_filter);
                break;
            default:
                // code...
                break;
        }
    }

    /**
     * [get_metrics_list]
     */
    public function get_metrics_list(){
        $data_filter = $this->input->post();
        $view = $data_filter['ga_tab_active'];

        if($data_filter['ga_tab_active'] == 'conversions'){
            $view = $data_filter['ga_sub_tab_active'].'_'.$data_filter['ga_tab_active'];
        }

        $ga_analytic_metrics = ga_get_contact_metrics();
        $data_filter['metrics'] = explode(',', $ga_analytic_metrics);

        $this->load->view('analytics/metrics/'.$view, $data_filter);
    }

    /**
     * [set_contact_default_workspace]
     * @param [integer] $workspace_id workspace id
     */
    public function set_contact_default_workspace($workspace_id){
        $this->load->model('google_analytic_model');
        $message = '';
        $success = $this->google_analytic_model->set_contact_default_workspace($workspace_id);
        if ($success) {
            $message = _l('updated_successfully', _l('workspace'));
        }

        echo json_encode(['success' => $success, 'message' => $message]);
        die();
    }
}
