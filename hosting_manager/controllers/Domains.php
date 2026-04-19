<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Domains extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['hosting_manager_model', 'staff_model',  'settings_model','domain_model']);
    }

    /**
     * List all domains for a given hosting ID
     */
    public function index()
    {
        
        if (!has_permission('hosting_manager', get_staff_user_id(), 'domain_view')) {
            access_denied('hosting_manager');
        }
        $hosting_id = $this->input->get('hosting_id');
        if (!$hosting_id) {
            show_404();
        }

        $data['title'] = _l('Domains');
        $data['hosting_id'] = $hosting_id;
        $data['active_tab'] = 'domain';
        $data['hosting'] = $this->hosting_manager_model->get($hosting_id);
        $this->load->view('domains/index', $data);
    }

    public function list(){
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('hosting_manager', 'tables/domains'));
        }
    }


    /**
     * Create a new domain under a specific hosting ID
     */
    public function create()
    {

        $data = $this->input->post();
        $hosting_id = $this->input->post('hosting_id');
        if (!$hosting_id) {
            show_404();
        }
        if (empty($data['name'])) {
            set_alert('warning', _l('The domain name field is required'));
            redirect($_SERVER['HTTP_REFERER']);
        }




        if ($this->input->post()) {
            if (!has_permission('hosting_manager', get_staff_user_id(), 'domain_create')) {
                access_denied('hosting_manager');
            }
            $data = [
                'hosting_id' => $this->input->post('hosting_id'),
                'title'   => $this->input->post('name'),
                'ssl_active'   => $this->input->post('ssl_status'),
                'price'   => $this->input->post('price'),
                'description'   => $this->input->post('description'),
                'status'      => $this->input->post('status'),
                'created_by'          => get_staff_user_id(),
                'created_at'  => date('Y-m-d H:i:s')
            ];
            
            $insert_id = $this->domain_model->add($data);
            if ($insert_id) {
                set_alert('success', 'Domain added successfully');
            }

            redirect(admin_url('hosting_manager/domains?hosting_id=' . $hosting_id),'refresh');
        }
    }

    /**
     * Edit a domain under a specific hosting ID
     */
    public function edit( $id = null)
    {
        
        if ( !$id) {
            show_404();
        }

        if ($this->input->post()) {
      

            if (!has_permission('hosting_manager', get_staff_user_id(), 'domain_edit')) {
                access_denied('hosting_manager');
            }

            $data = [
                'title'   => $this->input->post('name'),
                'ssl_active'   => $this->input->post('ssl_status'),
                'price'   => $this->input->post('price'),
                'description'   => $this->input->post('description'),
                'status'      => $this->input->post('status'),
            ];

            $hosting_id = $this->input->post('hosting_id');
            $updated = $this->domain_model->update($id, $data);
            if ($updated) {
                set_alert('success', 'Domain updated successfully');
            }

             redirect(admin_url('hosting_manager/domains?hosting_id=' . $hosting_id),'refresh');
        }

        $data['domain'] = $this->domain_model->get($id);
        $data['title'] = _l('Edit');
        $data['hosting_id'] = $data['domain']->hosting_id;
        $this->load->view('domains/edit_modal', $data);
    }

    /**
     * Delete a domain under a specific hosting ID
     */
    public function delete($id = null)
    {
        $hosting_id = $this->input->get('hosting_id');
        if (!has_permission('hosting_manager', get_staff_user_id(), 'domain_delete')) {
            access_denied('hosting_manager');
        }

    
        if ($this->domain_model->delete($id)) {
            set_alert('success', 'Domain deleted successfully');
        }

         redirect(admin_url('hosting_manager/domains?hosting_id=' . $hosting_id),'refresh');
    }
}
