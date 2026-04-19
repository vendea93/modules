<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy_enrollment_confirmed extends App_mail_template
{
    protected $for = 'client';

    protected $customer_email;

    protected $enrollment_data;
    
    public $slug = 'flexacademy-enrollment-confirmed';

    public $rel_type = 'flexacademy';

    public function __construct($customer_email, $enrollment_data)
    {
        parent::__construct();

        $this->customer_email = $customer_email;
        $this->enrollment_data = $enrollment_data;
    }

    public function build()
    {
        $this->set_merge_fields('flexacademy_merge_fields', $this->enrollment_data);
        $this->to($this->customer_email)
            ->set_rel_id($this->enrollment_data['enrollment_id'] ?? 0);
    }
}

