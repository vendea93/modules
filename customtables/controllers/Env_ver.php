<?php

use modules\customtables\core\Apiinit;

defined('BASEPATH') || exit('No direct script access allowed');

class Env_ver extends AdminController {
    
    public $input;

    public function __construct()
    {
        parent::__construct();
    }

    public function index() {
        show_404();
    }

    public function activate() {
        $res = Apiinit::pre_validate($this->input->post('module_name'), $this->input->post('purchase_key'));
        if ($res['status']) {
            $res['original_url'] = $this->input->post('original_url');
        }

        echo json_encode($res);
    }

    public function upgrade_database() {
        $res = Apiinit::pre_validate($this->input->post('module_name'), $this->input->post('purchase_key'));
        if ($res['status']) {
            $res['original_url'] = $this->input->post('original_url');
        }

        echo json_encode($res);
    }
}
