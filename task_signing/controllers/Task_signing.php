<?php

/**
 * @property Tasks_model $tasks_model
 */

class Task_signing extends AdminController
{

    public function __construct()

    {
        parent::__construct();

        if (!class_exists('tasks_model') )
            $this->load->model('tasks_model');

    }


    public function sign_task( $task_id = 0 )
    {


        if( $this->input->post() && $task_id > 0 )
        {

            $task_assigned_id = get_staff_user_id();

            $sign_image_path = "uploads/task_sign/task_$task_id/staff_$task_assigned_id";

            unlink(FCPATH."$sign_image_path/signature.png");

            process_digital_signature_image( $this->input->post('signature', false) , $sign_image_path ) ;

            if( file_exists( FCPATH."$sign_image_path/signature.png" ) )
            {

                // task sign successs


                if ( $this->db->table_exists(db_prefix().'task_signature_info') )
                {

                    $this->db->where('task_id',$task_id)
                            ->where('staff_id',$task_assigned_id)
                            ->delete(db_prefix().'task_signature_info');

                    $this->db->set('task_id',$task_id)
                            ->set('staff_id',$task_assigned_id)
                            ->set('ip_address', $this->input->ip_address())
                            ->set('datetime', date('Y-m-d H:i:s'))
                            ->insert(db_prefix().'task_signature_info');

                }


                $this->set_task_completed( $task_id );

                log_activity( 'The task has been signed [ Task ID : '.$task_id.' ]  ' );

                set_alert("success" , _l('ts_task_signed'));

            }

        }


        redirect( admin_url( 'tasks/list_tasks/'.$task_id ) );

    }


    public function set_task_completed( $task_id )
    {

        ts_task_set_completed( $task_id );

    }



    public function remove_sign( $task_id = 0 , $assigned_id = 0 )
    {

        $sign_image_path =  "uploads/task_sign/task_$task_id/staff_$assigned_id";

        unlink(FCPATH."$sign_image_path/signature.png");

        log_activity( 'The task sign removed [ Task ID : '.$task_id.' ]  ' );

        redirect( admin_url( 'tasks/list_tasks/'.$task_id ) );

    }


    public function member_detail()
    {

        if ( $this->input->is_ajax_request() )
        {

            $memberid = $this->input->post('memberid');


            $value = 1;

            if ( !empty( $memberid ) )
            {

                $data = $this->db->select('task_sign_index')
                                ->from(db_prefix().'staff')
                                ->where('staffid',$memberid)
                                ->get()
                                ->row();

                if ( !empty( $data->task_sign_index ) && $data->task_sign_index > 0 )
                    $value = $data->task_sign_index ;

            }

            $content = render_input( 'task_sign_index' , 'ts_sign_priority' , $value , 'number' , [ 'min' => 1 , 'max' => 100 , 'step' => 1 ] );

            echo json_encode( [ 'content' => $content ] );

        }

    }


    public function request_customer_signature()
    {

        if ( $this->input->is_ajax_request() )
        {

            $task_id = $this->input->post('task_id');

            $table = db_prefix()."task_client_signature_info";

            $client_id = ts_get_task_client_id( $task_id );


            if ( !empty( $client_id ) )
            {

                $info = $this->db->select('*')->from($table)->where('task_id',$task_id)->get()->row();

                if ( empty( $info ) )
                {

                    $this->db->insert($table,[
                        'task_id' => $task_id,
                        'client_id' => $client_id,
                        'request_date' => date('Y-m-d H:i:s'),
                    ]);

                    $record_id = $this->db->insert_id();

                    if ( $record_id )
                    {

                        log_activity( 'Customer signature request sent [ Task ID : '.$task_id.' Client ID : '.$client_id.' ]  ' );

                        // Send mail to client
                        $this->send_mail_to_client( $record_id , $task_id , $client_id );

                    }

                }

            }
            
        }

        echo json_encode( [ 'success' => true , 'message' => _l('ts_send_successful') ] );

    }


    public function send_mail_to_client( $record_id , $task_id = 0 , $client_id = 0 )
    {


        $contact_id = get_primary_contact_user_id( $client_id );

        if ( empty( $contact_id ) )
            return true;

        $contact    = $this->db->select('firstname, lastname, email')->from(db_prefix().'contacts')->where('id',$contact_id)->get()->row();

        $subject    = get_option('companyname').' | '._l('ts_task_signature_email_subject');

        $email      = $contact->email;

        $message_content    = ts_task_client_signature_email_content();


        $task_info = $this->db->select('name')->from(db_prefix().'tasks')->where('id',$task_id)->get()->row();


        $task_merge_field = [
            '{signature_link}'      => site_url('task_signing/client_side/detail/'.$record_id) ,
            '{task_name}'           => !empty( $task_info->name ) ? $task_info->name : '' ,
            '{client}'              => get_company_name( $client_id ) ,
            '{contact_full_name}'   => $contact->firstname.' '.$contact->lastname ,
        ];

        foreach ( $task_merge_field as $key => $val )
        {

            $message_content = str_ireplace($key, $val, $message_content);

        }


        $message_content = nl2br( $message_content );


        $cnf = [

            'from_email' => get_option('smtp_email'),

            'from_name'  => get_option('companyname'),

            'email'      => $email,

        ];


        $this->load->config('email');

        $this->email->from($cnf['from_email'], $cnf['from_name']);

        $this->email->to($cnf['email']);

        $this->email->subject($subject);

        $this->email->message($message_content);


        $systemBCC = get_option('bcc_emails');

        if ($systemBCC != '') {

            $this->email->bcc($systemBCC);

        }

        $result = $this->email->send();

        $this->email->send_queue();

        return $result;

    }


}
