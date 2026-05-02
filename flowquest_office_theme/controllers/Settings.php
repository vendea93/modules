<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Settings extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('flowquest_office_theme/Settings_model');
    }

    public function index()
    {
        $data['title'] = _l('flowquest_office_theme');
        $data['settings'] = $this->Settings_model->get_settings();
        $this->load->view('flowquest_office_theme/settings', $data);
    }

    public function save()
    {
        // Save theme settings
        if ($this->input->post()) {
            // Here you can add logic for saving settings
            set_alert('success', 'Ustawienia zostały zapisane');
            redirect(admin_url('flowquest_office_theme/settings'));
        }
    }
}