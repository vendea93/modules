<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Manage extends AdminController
{
    public function __construct()
    {
        parent::__construct();

        // Load necessary models
        $this->load->model('payment_modes_model');
        $this->load->model('settings_model');
    }

    /**
     * Index method to manage Otpless configuration settings.
     */
    public function index()
    {
        if ($this->input->post()) {
            $post_data = $this->input->post();

            $check = 'ok';

             if($check == 'ok'){
                $success = $this->settings_model->update($post_data);

                    set_alert('success', _l('settings_updated'));
                    redirect(admin_url('otpless/manage'));
                }
            }
        

        $data['title'] = _l('otpless_manage');
        $this->load->view('manage', $data);
    }
}
