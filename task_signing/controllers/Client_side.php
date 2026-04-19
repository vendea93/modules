<?php
defined('BASEPATH') OR exit('No direct script access allowed');


class Client_side extends ClientsController {


    public function __construct()
    {

        parent::__construct();

    }


    public function index()
    {

        $data['title']         = _l('als_tasks');

        $client_id = get_client_user_id();

        $data['tasks'] = $this->db->select('ct.id, ct.task_id, ct.signed, ct.signature_date , t.name , t.status , t.startdate, t.duedate')
                                ->from(db_prefix().'task_client_signature_info ct')
                                ->join(db_prefix().'tasks t', 't.id = ct.task_id')
                                ->where('ct.client_id', $client_id)
                                ->get()
                                ->result();

        $this->data($data);

        $this->view('client_side/v_tasks');

        $this->layout();

    }


    public function detail($id)
    {

        $client_id = get_client_user_id();

        $sign_info = $this->db->select('*')
                            ->from(db_prefix().'task_client_signature_info')
                            ->where('id', $id)
                            ->where('client_id', $client_id)
                            ->get()
                            ->row();

        if ( !empty( $sign_info->task_id ) )
        {

            $task = $this->db->select('*')->from(db_prefix().'tasks')->where('id', $sign_info->task_id )->get()->row();

            if ( !empty( $task->name ) )
            {

                $data['title']      = $task->name;
                $data['task']       = $task;
                $data['sign_info']  = $sign_info;

                $this->data($data);

                $this->view('client_side/task_detail');

                $this->layout();

            }

            else
            {

                set_alert('danger', _l('task_not_found'));


                redirect(site_url('task_signing/client_side'));
            }

        }
        else
        {

            set_alert('danger', _l('task_not_found'));

            redirect(site_url('task_signing/client_side'));

        }

    }


    public function task_sign()
    {


        $record_id = $this->input->post('record_id');

        if( $this->input->post() && $record_id > 0 )
        {

            $client_id = get_client_user_id();

            $contact_id = get_contact_user_id();

            $sign_info = $this->db->select('*')
                                ->from(db_prefix().'task_client_signature_info')
                                ->where('id', $record_id)
                                ->where('client_id', $client_id)
                                ->get()
                                ->row();


            if ( !empty( $sign_info->task_id ) && empty( $sign_info->signed ) )
            {

                $task_id = $sign_info->task_id;

                $sign_image_path = "uploads/task_sign/task_$task_id/client_$client_id";

                unlink(FCPATH."$sign_image_path/signature.png");

                process_digital_signature_image( $this->input->post('signature', false) , $sign_image_path ) ;

                if( file_exists( FCPATH."$sign_image_path/signature.png" ) )
                {

                    $this->db->where('id', $record_id)
                            ->update(db_prefix().'task_client_signature_info', [
                                'signed' => 1 ,
                                'contact_id' => $contact_id,
                                'signature_date' => date('Y-m-d H:i:s') ,
                                'signature' => $sign_image_path."/signature.png" ,
                                'ip_address' => $_SERVER['REMOTE_ADDR']
                            ]);


                    ts_task_set_completed( $task_id );


                    log_activity( 'The task has been signed by client [ Task ID : '.$task_id.' Contact ID : '.$contact_id.' ]  ' );

                    set_alert('success', _l('document_signed_successfully'));

                }
                else
                    set_alert('warning', _l('ts_task_already_signed'));


            }
            else
                set_alert('danger', _l('ts_task_already_signed').'-');

            redirect($_SERVER['HTTP_REFERER']);

        }


    }


}
