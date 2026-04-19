<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Ftp extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['hosting_manager_model', 'staff_model',  'settings_model','ftp_model']);
    }

    /**
     * List all ftp for a given hosting ID
     */
    public function index()
    {
        
        if (!has_permission('hosting_manager', get_staff_user_id(), 'ftp_view')) {
            access_denied('hosting_manager');
        }
        $hosting_id = $this->input->get('hosting_id');
        if (!$hosting_id) {
            show_404();
        }

        $data['title'] = _l('FTP');
        $data['hosting_id'] = $hosting_id;
        $data['active_tab'] = 'ftp';
        $data['hosting'] = $this->hosting_manager_model->get($hosting_id);
        $this->load->view('ftp/index', $data);
    }

    public function list(){
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('hosting_manager', 'tables/ftps'));
        }
    }


    /**
     * Create a new ftp under a specific hosting ID
     */
    public function create()
    {

        $data = $this->input->post();
        $hosting_id = $this->input->post('hosting_id');
        if (!$hosting_id) {
            show_404();
        }
        if (empty($data['account_name'])) {
            set_alert('warning', _l('The ftp account name field is required'));
            redirect($_SERVER['HTTP_REFERER']);
        }



        if ($this->input->post()) {

            if (!has_permission('hosting_manager', get_staff_user_id(), 'ftp_create')) {
                access_denied('hosting_manager');
            }
            $data = [
                'hosting_id' => $this->input->post('hosting_id'),
                'account_name'   => $this->input->post('account_name'),
                'hostname'   => $this->input->post('hostname'),
                'username'   => $this->input->post('username'),
                'password'   => $this->input->post('ftp_password'),
                'port'   => $this->input->post('port'),
                'protocol'   => $this->input->post('protocol'),
                'root_directory'   => $this->input->post('root_directory'),
                'status'      => $this->input->post('status'),
                'description'   => $this->input->post('description'),
                'created_by'          => get_staff_user_id(),
                'created_at'  => date('Y-m-d H:i:s')
            ];
            
            $insert_id = $this->ftp_model->add($data);
            if ($insert_id) {
                set_alert('success', 'FTP added successfully');
            }

            redirect(admin_url('hosting_manager/ftp?hosting_id=' . $hosting_id),'refresh');
        }

    }

    /**
     * Edit a ftp under a specific hosting ID
     */
    public function edit( $id = null)
    {
        
        if ( !$id) {
            show_404();
        }

        if ($this->input->post()) {
      
            if (!has_permission('hosting_manager', get_staff_user_id(), 'ftp_edit')) {
                access_denied('hosting_manager');
            }
            $data = [
               'account_name'   => $this->input->post('account_name'),
                'hostname'   => $this->input->post('hostname'),
                'username'   => $this->input->post('username'),
                'password'   => $this->input->post('ftp_password'),
                'port'   => $this->input->post('port'),
                'protocol'   => $this->input->post('protocol'),
                'root_directory'   => $this->input->post('root_directory'),
                'status'      => $this->input->post('status'),
                'description'   => $this->input->post('description'),
            ];

            $hosting_id = $this->input->post('hosting_id');
            $updated = $this->ftp_model->update($id, $data);
            if ($updated) {
                set_alert('success', 'Domain updated successfully');
            }

             redirect(admin_url('hosting_manager/ftp?hosting_id=' . $hosting_id),'refresh');
        }

        $data['ftp'] = $this->ftp_model->get($id);
        $data['title'] = _l('Edit');
        $data['hosting_id'] = $data['ftp']->hosting_id;
        $this->load->view('ftp/edit_modal', $data);
    }

    /**
     * Delete a ftp under a specific hosting ID
     */
    public function delete($id = null)
    {
        $hosting_id = $this->input->get('hosting_id');
        if (!has_permission('hosting_manager', get_staff_user_id(), 'ftp_delete')) {
            access_denied('hosting_manager');
        }

    
        if ($this->ftp_model->delete($id)) {
            set_alert('success', 'FTP deleted successfully');
        }

         redirect(admin_url('hosting_manager/ftp?hosting_id=' . $hosting_id),'refresh');
    }
}
