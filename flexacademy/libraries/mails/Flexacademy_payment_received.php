<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Flexacademy_payment_received extends App_mail_template
{
    protected $for = 'client';

    protected $customer_email;

    protected $payment_data;
    
    public $slug = 'flexacademy-payment-received';

    public $rel_type = 'flexacademy';

    public function __construct($customer_email, $payment_data)
    {
        parent::__construct();

        $this->customer_email = $customer_email;
        $this->payment_data = $payment_data;
    }

    public function build()
    {
        $this->set_merge_fields('flexacademy_merge_fields', $this->payment_data);
        $this->to($this->customer_email)
            ->set_rel_id($this->payment_data['invoice_id'] ?? 0);
    }
}

