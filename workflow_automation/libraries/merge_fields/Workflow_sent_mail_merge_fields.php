<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Workflow_sent_mail_merge_fields extends App_merge_fields
{
    public function build()
    {
        return [

            [
                'name'      => 'Staff first name',
                'key'       => '{first_name}',
                'available' => [
                   
                ],
                'templates' => [
                    'workflow-automation-task-sent-mail-action',
                ],
            ],
            [
                'name'      => 'Staff last name',
                'key'       => '{last_name}',
                'available' => [
                   
                ],
                'templates' => [
                    'workflow-automation-task-sent-mail-action',
                ],
            ],
            [
                'name'      => 'Content',
                'key'       => '{content}',
                'available' => [
                   
                ],
                'templates' => [
                    'workflow-automation-task-sent-mail-action',
                ],
            ],
            
        ];
    }

    /**
     * Merge field for appointments
     * @param  mixed $teampassword 
     * @return array
     */
    public function format($data)
    {


        $this->ci->db->where('staffid', $data->staff_id);
        $staff = $this->ci->db->get(db_prefix().'staff')->row();

        if ($staff) {
            $fields['{first_name}'] = $staff->firstname;
            $fields['{last_name}'] = $staff->lastname;
        }
        $fields['{content}'] = $data->content;


        return $fields;
    }

}
