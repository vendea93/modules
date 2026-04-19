<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Database extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model(['hosting_manager_model', 'staff_model', 'settings_model', 'database_model']);
    }

    /**
     * Display the list of databases for a given hosting ID.
     */
    public function index()
    {
        if (!has_permission('hosting_manager', get_staff_user_id(), 'database_view')) {
            access_denied('hosting_manager');
        }

        $hosting_id = $this->input->get('hosting_id');
        if (!$hosting_id) {
            show_404();
        }

        $data = [
            'title'      => _l('Database'),
            'hosting_id' => $hosting_id,
            'active_tab' => 'database',
            'hosting'    => $this->hosting_manager_model->get($hosting_id),
        ];

        $this->load->view('database/index', $data);
    }

    /**
     * Fetch the list of databases via AJAX.
     */
    public function list()
    {
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('hosting_manager', 'tables/database'));
        }
    }

    /**
     * Create a new database entry under a specific hosting ID.
     */
    public function create()
    {
        if ($this->input->post()) {
            if (!has_permission('hosting_manager', get_staff_user_id(), 'database_create')) {
                access_denied('hosting_manager');
            }

            $hosting_id = $this->input->post('hosting_id');
            if (!$hosting_id) {
                show_404();
            }

            if (empty($this->input->post('title'))) {
                set_alert('warning', _l('The database title field is required'));
                redirect(admin_url('hosting_manager/database?hosting_id=' . $hosting_id), 'refresh');
            }

            $data = [
                'hosting_id'        => $hosting_id,
                'title'             => $this->input->post('title'),
                'access_url'        => $this->input->post('access_url'),
                'database_name'     => $this->input->post('database_name'),
                'database_username' => $this->input->post('database_username'),
                'database_password' => $this->input->post('database_password'),
                'description'       => $this->input->post('description'),
                'status'            => $this->input->post('status'),
                'created_by'        => get_staff_user_id(),
                'created_at'        => date('Y-m-d H:i:s'),
            ];

            if ($this->database_model->add($data)) {
                set_alert('success', 'Database added successfully');
            }

            redirect(admin_url('hosting_manager/database?hosting_id=' . $hosting_id), 'refresh');
        }
    }

    /**
     * Edit an existing database entry.
     *
     * @param int|null $id Database ID
     */
    public function edit($id = null)
    {
        if (!$id) {
            show_404();
        }

        if ($this->input->post()) {
            if (!has_permission('hosting_manager', get_staff_user_id(), 'database_edit')) {
                access_denied('hosting_manager');
            }

            $data = [
                'title'             => $this->input->post('title'),
                'access_url'        => $this->input->post('access_url'),
                'database_name'     => $this->input->post('database_name'),
                'database_username' => $this->input->post('database_username'),
                'database_password' => $this->input->post('database_password'),
                'description'       => $this->input->post('description'),
                'status'            => $this->input->post('status'),
            ];

            $hosting_id = $this->input->post('hosting_id');
            if ($this->database_model->update($id, $data)) {
                set_alert('success', 'Database updated successfully');
            }

            redirect(admin_url('hosting_manager/database?hosting_id=' . $hosting_id), 'refresh');
        }

        $data = [
            'database'   => $this->database_model->get($id),
            'title'      => _l('Edit'),
            'hosting_id' => $this->database_model->get($id)->hosting_id,
        ];

        $this->load->view('database/edit_modal', $data);
    }

    /**
     * Delete a database entry.
     *
     * @param int|null $id Database ID
     */
    public function delete($id = null)
    {
        if (!has_permission('hosting_manager', get_staff_user_id(), 'database_delete')) {
            access_denied('hosting_manager');
        }

        $hosting_id = $this->input->get('hosting_id');
        if ($this->database_model->delete($id)) {
            set_alert('success', 'Database deleted successfully');
        }

        redirect(admin_url('hosting_manager/database?hosting_id=' . $hosting_id), 'refresh');
    }
}
