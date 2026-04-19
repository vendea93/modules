<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Workflow_send_mail_action extends App_mail_template
{
    protected $for = 'staff';

    protected $data;

    public $slug = 'workflow-automation-task-sent-mail-action';

    public function __construct($data)
    {
        parent::__construct();

        $this->data = $data;
        // For SMS and merge fields for email
        $this->set_merge_fields('workflow_sent_mail_merge_fields', $this->data);
    }
    public function build()
    {
        $this->to($this->data->mail_to);
    }
}
