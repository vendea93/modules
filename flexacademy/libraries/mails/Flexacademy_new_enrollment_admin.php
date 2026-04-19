<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy_new_enrollment_admin extends App_mail_template
{
    protected $for = 'staff';

    protected $admin_email;

    protected $enrollment_data;
    
    public $slug = 'flexacademy-new-enrollment-admin';

    public $rel_type = 'flexacademy';

    public function __construct($admin_email, $enrollment_data)
    {
        parent::__construct();

        $this->admin_email = $admin_email;
        $this->enrollment_data = $enrollment_data;
    }

    public function build()
    {
        $this->set_merge_fields('flexacademy_merge_fields', $this->enrollment_data);
        $this->to($this->admin_email)
            ->set_rel_id($this->enrollment_data['enrollment_id'] ?? 0);
    }
}

