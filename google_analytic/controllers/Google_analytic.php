<?php

defined('BASEPATH') or exit('No direct script access allowed');
/**
 * This class describes a xero integration.
 */
require 'modules/google_analytic/vendor/autoload.php';

class Google_analytic extends AdminController {
	public function __construct() {
		parent::__construct();
        $this->load->model('google_analytic_model');
        hooks()->do_action('google_analytic_init');    
	}

    /**
     * [setting]
     */
	public function setting()
    {
    	$data['title'] = _l('setting');

        $data          = [];
        $data['group'] = $this->input->get('group');

        $data['tab'][] = 'google_analytic';

        $data['tab_2'] = $this->input->get('tab');
        if ($data['group'] == '') {
            $data['group'] = 'google_analytic';
        }

        $data['title']         = _l('setting');
        $data['tabs']['view'] = 'settings/' . $data['group'];

    	$this->load->view('settings/manage', $data);
    }

    /**
     * [update_setting]
     */
    public function update_setting(){
        $data = $this->input->post();
        $type = $data['type'];
        unset($data['type']);
        $success = $this->google_analytic_model->update_setting($data);
        if($success == true){
            $message = _l('updated_successfully', _l('setting'));
            set_alert('success', $message);
        }
        redirect(admin_url('google_analytic/setting?group='.$type));
    }

    /**
     * [analytics]
     */
    public function analytics(){
        $data['title']         = _l('google_analytic');

        $this->load->view('analytics/manage', $data);
    }

    /**
     * [workspaces]
     */
    public function workspaces(){
        
        $data['title']         = _l('workspaces');
        $data['staffs']         = $this->staff_model->get('', ['active' => 1]);
        $this->load->view('workspaces/manage', $data);
    }

    /**
     * [workspace]
     */
    public function workspace(){
        $data = $this->input->post();

        $message = '';
        if($data['id'] == ''){
            $success = $this->google_analytic_model->add_workspace($data);
            if($success){
                ga_handle_workspace_logo($success);
                $message = _l('added_successfully', _l('workspace'));
                set_alert('success', $message);
            }
        }else{
            
            $id = $data['id'];
            unset($data['id']);
            $success = $this->google_analytic_model->update_workspace($data, $id);
            ga_handle_workspace_logo($id);
            if ($success) {
                $message = _l('updated_successfully', _l('workspace'));
                set_alert('success', $message);
            }
        }

        if($this->input->is_ajax_request()){
            echo json_encode(['success' => $success, 'message' => $message]);
            die();
        }

        redirect(admin_url('google_analytic/workspace_detail/'.$id));

    }

    /**
     * [workspaces_table]
     */
    public function workspaces_table(){

        $this->app->get_table_data(module_views_path('google_analytic', 'workspaces/table_workspaces'));
    }

    public function set_default_workspace($workspace_id){
        $message = '';
        $success = $this->google_analytic_model->set_default_workspace($workspace_id);
        if ($success) {
            $message = _l('updated_successfully', _l('workspace'));
        }

        echo json_encode(['success' => $success, 'message' => $message]);
        die();
    }

    /**
     * [workspace_detail]
     * @param  [integer] $id workspace id
     */
    public function workspace_detail($id){
        
        $data['workspace'] = $this->google_analytic_model->get_workspace($id);
        $data['staffs']         = $this->staff_model->get('', ['active' => 1]);
        $data['contacts']         = $this->google_analytic_model->get_contacts();
        $data['title'] = _l('workspace');

        $this->load->view('workspaces/workspace_detail', $data);
    }
    
    /**
     * delete workspace
     * @param  integer $id
     * @return
     */
    public function delete_workspace($id)
    {

        $success = $this->google_analytic_model->delete_workspace($id);
        $message = '';
        if ($success) {
            $message = _l('deleted', _l('workspace'));
            set_alert('success', $message);
        } else {
            $message = _l('can_not_delete');
            set_alert('warning', $message);
        }

        redirect(admin_url('google_analytic/workspaces'));
    }

    /**
     * [remove_workspace_logo]
     * @param  [type] $workspace_id workspace id
     */
    public function remove_workspace_logo($workspace_id)
    {
        $workspaceLogoPath = 'modules/google_analytic/uploads/workspaces/' . $workspace_id;

        if (file_exists($workspaceLogoPath)) {
            delete_dir($workspaceLogoPath);
        }

        $this->db->where('id', $workspace_id);
        $this->db->update(db_prefix() . 'ga_workspaces', [
            'workspace_logo' => null,
        ]);

        redirect(admin_url('google_analytic/workspace_detail/' . $workspace_id));
    }

    /**
     * add workspace_member
     * @return json
     */
    public function add_workspace_member(){
        $data = $this->input->post();
        $success = false;
        $message = _l('add_failure');
        if($data['workspace_id'] != ''){
            $success = $this->google_analytic_model->add_workspace_member($data);
            if($success){
                $message = _l('added_successfully', _l('member'));
            }
        }
        echo json_encode(['success' => $success, 'message' => $message]);
        die();
    }

    /**
     * [workspace_members_table]
     * @param  string $type
     */
    public function workspace_members_table($type = 'staff'){
        $workspace_id = $this->input->post('workspace_id');

        $this->app->get_table_data(module_views_path('google_analytic', 'workspaces/table_workspace_members'), ['type' => $type, 'workspace_id' => $workspace_id]);
    }

    /**
     * delete workspace
     * @param  integer $id
     * @return
     */
    public function delete_workspace_member($workspace_id, $member_id)
    {
        $success = $this->google_analytic_model->delete_workspace_member($member_id);
        $message = '';
        if ($success) {
            $message = _l('deleted', _l('workspace_member'));
            set_alert('success', $message);
        } else {
            $message = _l('can_not_delete');
            set_alert('warning', $message);
        }

        redirect(admin_url('google_analytic/workspace_detail/'.$workspace_id));
    }

    /**
     * [accounts ]
     */
    public function accounts(){
        $this->check_base_workspace();
        
        $data['title']         = _l('properties');
        $data['check_config'] = 0;
        $config = ga_get_google_config();
        if($config['client_id'] != '' && $config['client_secret'] != ''){
            $data['check_config'] = 1;
        }

        $this->load->view('accounts/manage', $data);
    }

    /**
     * [check_base_workspace ]
     */
    public function check_base_workspace(){
        if(ga_get_base_workspace_id() == 0){
            set_alert('warning', _l('please_set_default_workspace'));
            redirect(admin_url('google_analytic/workspaces'));
        }
    }

    /**
     * [accounts_table ]
     */
    public function accounts_table(){

        $this->app->get_table_data(module_views_path('google_analytic', 'accounts/table_accounts'), ['type' => $this->input->post('type')]);
    }

    /**
     * [account]
     */
    public function account(){
        $data = $this->input->post();
        $message = '';
        if($data['id'] == ''){
            unset($data['id']);

            $success = $this->google_analytic_model->add_account($data);
            if($success){
                $message = _l('added_successfully', _l('property'));
            }
        }else{
            $id = $data['id'];
            unset($data['id']);
            $success = $this->google_analytic_model->update_account($data, $id);
            if ($success) {
                $message = _l('updated_successfully', _l('property'));
            }
        }

        echo json_encode(['success' => $success, 'message' => $message]);
        die();
    }

    /**
     * [google_analytic_connect]
     * @param  [integer] $account_id account id
     */
    public function google_analytic_connect($account_id){
        $config = ga_get_google_config();
        $client = new Google_Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setRedirectUri(admin_url('google_analytic/google_callback'));
        $client->setState($account_id);
        $client->setAccessType("offline");
        $client->setPrompt("consent");
        $client->addScope("email");
        $client->addScope("profile");
        $client->addScope("https://www.googleapis.com/auth/analytics.readonly");
        $data['connect_url'] = $client->createAuthUrl();

        redirect($data['connect_url']);
    }

    /**
     * [google_callback]
     */
    public function google_callback(){
        $config = ga_get_google_config();
        $client = new Google_Client();
        $client->setClientId($config['client_id']);
        $client->setClientSecret($config['client_secret']);
        $client->setRedirectUri(admin_url('google_analytic/google_callback'));
        $code = $this->input->get("code") ?? '';
        $account_id = $this->input->get("state") ?? '';

        if ($code != '')
        {   
            $token_result = $client->fetchAccessTokenWithAuthCode($_GET['code']);

            if($token_result){

                $data['account_id'] = $account_id;
                $data['refresh_token'] = $token_result['refresh_token'];
                $data['access_token'] = $token_result['access_token'];
                $data['expires_in'] = time() + $token_result['expires_in'];
                $data['accounts'] = [];
                $analytics = new Google_Service_Analytics($client);

                $response = $analytics->management_accounts->listManagementAccounts();
                foreach ($response['modelData']['accounts'] as $row) { 
                    $id = explode('/', $row['name'])[1];
                    $responseProperties = $analytics->management_webproperties->listManagementWebproperties($id, ['filter' => 'parent:accounts/'.$id]);

                    $data['accounts'][] = [
                        'id' => $id,
                        'name' => $row['displayName'],
                        'properties' => $responseProperties['modelData']['properties'],
                    ];
                } 
            }
        } 

        $this->load->view('accounts/connects/google_analytic', $data);
    }

    /**
     * [delete_account]
     * @param  [integer] $id account id
     */
    public function delete_account($id)
    {
        $success = $this->google_analytic_model->delete_account($id);
        $message = '';
        if ($success) {
            $message = _l('deleted', _l('property'));
            set_alert('success', $message);
        } else {
            $message = _l('can_not_delete');
            set_alert('warning', $message);
        }

        redirect(admin_url('google_analytic/accounts'));
    }

    /**
     * get data dashboard
     * @return json
     */
    public function get_data_analytics(){
        $data_filter = $this->input->post();
        $data = [];

        if(isset($data_filter['metrics'])){
            $this->google_analytic_model->update_analytic_metrics($data_filter);
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
            case 'map_chart_init':
                $data['map_chart'] = $this->google_analytic_model->get_google_analytic_map_chart($data_filter);
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
     * [get_metrics_list ]
     */
    public function get_metrics_list(){
        $data_filter = $this->input->post();
        $view = $data_filter['ga_tab_active'];

        if($data_filter['ga_tab_active'] == 'conversions'){
            $view = $data_filter['ga_sub_tab_active'].'_'.$data_filter['ga_tab_active'];
        }

        $ga_analytic_metrics = ga_get_staff_metrics();
        $data_filter['metrics'] = explode(',', $ga_analytic_metrics);

        $this->load->view('analytics/metrics/'.$view, $data_filter);
    }

    /**
     * [google_analytic_connect_save ]
     */
    public function google_analytic_connect_save(){
        $data = $this->input->post();
        $account_id = $data['account_id'];
        $data['status'] = 1;
        unset($data['account_id']);

        $success = $this->google_analytic_model->account_connect_save($data, $account_id);
        if ($success) {
            $message = _l('connected_successfully');
            set_alert('success', $message);
        }
        
        redirect(admin_url('google_analytic/accounts'));
    }

    /**
     * [change_account_active]
     * @param  [integer] $id     account id
     * @param  [string] $status 
     */
    public function change_account_active($id, $status)
    {
        if ($this->input->is_ajax_request()) {
            $this->google_analytic_model->change_account_active($id, $status);
        }
    }
}