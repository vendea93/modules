<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Hosting_manager extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['hosting_manager_model', 'staff_model', 'settings_model']);
    }

    /**
     * Display the list of hosting records.
     */
    public function index()
    {
        $data['title'] = _l('hosting_manager_list');

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('hosting_manager', 'tables/hosting_manager'));
        }

        $this->load->view('index', $data);
    }

    /**
     * Display the form to add a new hosting record.
     */
    public function create()
    {
        $data['title'] = _l('hosting_manager_add');
        $data['staff'] = $this->staff_model->get('', ['active' => 1]);
        $data['projects'] = $this->hosting_manager_model->get_projects();
        $data['clients'] = $this->hosting_manager_model->get_clients();

        $this->load->view('create', $data);
    }

    /**
     * Save a new hosting record.
     */
    public function save_hosting_manager()
    {
        $data = $this->input->post();

        if (!has_permission('hosting_manager', get_staff_user_id(), 'create')) {
            access_denied('hosting_manager');
        }

        if (get_option('hosting_manager_purchase_is_valid') != 1) {
            set_alert('warning', _l('Please verify your purchase item token before proceeding.'));
            redirect(admin_url('hosting_manager'));
        }

        if (empty($data['title'])) {
            set_alert('warning', _l('The hosting title field is required'));
            redirect(admin_url('hosting_manager'));
        }

        $insert_data = [
            'title'             => $data['title'],
            'provider'          => $data['provider'] ?? null,
            'provider_url'      => $data['provider_url'] ?? null,
            'provider_user'     => $data['username'] ?? null,
            'provider_password' => $data['password'] ?? null,
            'plan'              => $data['plan'] ?? null,
            'price'             => $data['price'] ?? null,
            'start_date'        => !empty($data['start_date']) ? date('Y-m-d', strtotime($data['start_date'])) : null,
            'expiry_date'       => !empty($data['expiry_date']) ? date('Y-m-d', strtotime($data['expiry_date'])) : null,
            'status'            => $data['status'] ?? 'active',
            'client_id'         => !empty($data['client_id']) ? (int)$data['client_id'] : null,
            'project_id'        => !empty($data['project_id']) ? (int)$data['project_id'] : null,
            'description'       => $data['description'] ?? null,
            'created_by'        => get_staff_user_id(),
            'created_at'        => date('Y-m-d H:i:s'),
            'updated_at'        => date('Y-m-d H:i:s')
        ];

        $insert_id = $this->hosting_manager_model->add($insert_data);

        set_alert($insert_id ? 'success' : 'warning', _l($insert_id ? 'Created successfully' : 'An error occurred while creating the hosting record'));
        redirect(admin_url('hosting_manager'));
    }

    /**
     * View a hosting record.
     */
    public function view($id)
    {
        if (!has_permission('hosting_manager', get_staff_user_id(), 'detail_view')) {
            access_denied('hosting_manager');
        }

        $data['hosting'] = $this->hosting_manager_model->get($id);

        if (empty($data['hosting'])) {
            show_404();
        }
        $data['active_tab'] = 'overview';

        $this->load->view('view', $data);
    }

    /**
     * Display the form to edit a hosting record.
     */
    public function edit($id)
    {
        $data['hosting'] = $this->hosting_manager_model->get($id);
        $data['projects'] = $this->hosting_manager_model->get_projects();
        $data['clients'] = $this->hosting_manager_model->get_clients();

        $this->load->view('edit', $data);
    }

    /**
     * Update an existing hosting record.
     */
    public function update_hosting_manager()
    {
        if (!has_permission('hosting_manager', get_staff_user_id(), 'edit')) {
            access_denied('hosting_manager');
        }

        $data = $this->input->post();

        if (empty($data['id']) || empty($data['title'])) {
            set_alert('warning', _l('The ID and hosting name fields are required'));
            redirect(admin_url('hosting_manager'));
        }

        $update_data = [
            'title'             => $data['title'],
            'provider'          => $data['provider'] ?? null,
            'provider_url'      => $data['provider_url'] ?? null,
            'provider_user'     => $data['username'] ?? null,
            'provider_password' => $data['password'] ?? null,
            'plan'              => $data['plan'] ?? null,
            'price'             => $data['price'] ?? null,
            'start_date'        => !empty($data['start_date']) ? date('Y-m-d', strtotime($data['start_date'])) : null,
            'expiry_date'       => !empty($data['expiry_date']) ? date('Y-m-d', strtotime($data['expiry_date'])) : null,
            'status'            => $data['status'] ?? 'active',
            'client_id'         => !empty($data['client_id']) ? (int)$data['client_id'] : null,
            'project_id'        => !empty($data['project_id']) ? (int)$data['project_id'] : null,
            'description'       => $data['description'] ?? null,
        ];

        $update_result = $this->hosting_manager_model->update($data['id'], $update_data);

        set_alert($update_result ? 'success' : 'warning', _l($update_result ? 'Updated successfully' : 'An error occurred while updating the hosting record'));
        redirect(admin_url('hosting_manager'));
    }

        /**
         * Delete a hosting record.
         *
         * @param int $id
         * @return void
         */
        public function delete($id)
        {
            if (!has_permission('hosting_manager', get_staff_user_id(), 'delete')) {
                access_denied('hosting_manager');
            }

            if (!is_numeric($id)) {
                set_alert('danger', _l('Invalid Domain ID'));
                redirect(admin_url('hosting_manager'));
            }

            $success = $this->hosting_manager_model->delete($id);

            set_alert($success ? 'success' : 'danger', $success ? _l('Deleted successfully') : _l('An error occurred while deleting the hosting record'));
            redirect(admin_url('hosting_manager'));
        }

        /**
         * Manage Domain Manager settings.
         *
         * @return void
         */
        public function setting()
        {
            if ($this->input->post()) {
                $post_data = $this->input->post();
                $purchase_code = 'success';

                if ($purchase_code == 'success') {
                    $post_data['settings']['hosting_manager_purchase_is_valid'] = 1;
                    $success = $this->settings_model->update($post_data);


                }

                redirect(admin_url('hosting_manager/setting'));
            }

            $data['title'] = _l('Hosting Manager Settings');
            $this->load->view('manage', $data);
        }

        /**
         * Render the hosting manager data table.
         *
         * @return void
         */
        public function hosting_manager_table()
        {
            if (!has_permission('hosting_manager', '', 'view')) {
                access_denied('hosting_manager');
            }

            $this->app->get_table_data(module_views_path('hosting_manager', 'tables/hosting_manager'));
        }

    }
