<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy_payment_reminder extends App_mail_template
{
    protected $for = 'client';

    protected $customer_email;

    protected $reminder_data;
    
    public $slug = 'flexacademy-payment-reminder';

    public $rel_type = 'flexacademy';

    public function __construct($customer_email, $reminder_data)
    {
        parent::__construct();

        $this->customer_email = $customer_email;
        $this->reminder_data = $reminder_data;
    }

    public function build()
    {
        $this->set_merge_fields('flexacademy_merge_fields', $this->reminder_data);
        $this->to($this->customer_email)
            ->set_rel_id($this->reminder_data['invoice_id'] ?? 0);
    }
}

