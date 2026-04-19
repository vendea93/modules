<?php


function ts_get_task_client_id( $task_id = 0 )
{

    $client_id = 0;

    $CI = &get_instance();

    $task_info = $CI->db->select('rel_id, rel_type')->from(db_prefix().'tasks')->where('id', $task_id)->get()->row();

    if ( !empty( $task_info->rel_id ) )
    {

        if ( $task_info->rel_type == 'customer' )
        {
            $client_id = $task_info->rel_id;
        }
        elseif ( $task_info->rel_type == 'project' )
        {

            $project = $CI->db->select('clientid')->where('id',$task_info->rel_id)->get(db_prefix() . 'projects')->row();
            if ( !empty( $project->clientid ) )
                $client_id = $project->clientid;

        }
        elseif ( $task_info->rel_type == 'contract' )
        {

            $contract = $CI->db->select('client')->where('id',$task_info->rel_id)->get(db_prefix() . 'contracts')->row();
            if ( !empty( $contract->client ) )
                $client_id = $contract->client;

        }
        elseif ( $task_info->rel_type == 'proposal' )
        {

            $proposal = $CI->db->select('rel_type, rel_id')->where('id',$task_info->rel_id)->get(db_prefix() . 'proposals')->row();
            if ( !empty( $proposal->rel_type ) && $proposal->rel_type == 'customer' )
                $client_id = $proposal->rel_id;

        }

    }



    return $client_id;

}


function ts_get_task_client_signature( $task_id = 0 , $client_id = 0 )
{

    return get_instance()->db->select('*')->from(db_prefix().'task_client_signature_info')->where('task_id', $task_id)->where('client_id', $client_id)->get()->row();

}


function ts_task_set_completed( $task_id )
{

    $CI = &get_instance();

    if( !class_exists('tasks_model' ) )
        $CI->load->model('tasks_model');


    $task_assignees = $CI->tasks_model->get_task_assignees( $task_id );

    if ( !empty( $task_assignees ) )
    {

        $has_unsigned = false;

        foreach ( $task_assignees as $task_assignee )
        {

            $task_assigned_id = $task_assignee['assigneeid'];

            $sign_image_path = "uploads/task_sign/task_$task_id/staff_$task_assigned_id/signature.png";

            if( !file_exists( FCPATH."$sign_image_path" ) )
                $has_unsigned = true;

        }


        $ts_followers_will_sign = get_option('ts_followers_will_sign');
        $task_followers = $CI->tasks_model->get_task_followers( $task_id );

        if ( !empty( $task_followers ) && !empty( $ts_followers_will_sign ) )
        {

            foreach ( $task_followers as $follower )
            {

                $task_follower_id = $follower["followerid"];

                $sign_image_path = "uploads/task_sign/task_$task_id/staff_$task_follower_id/signature.png";

                if( !file_exists( FCPATH."$sign_image_path" ) )
                    $has_unsigned = true;

            }

        }

        $ts_not_complete_without_customer_sign = get_option('ts_not_complete_without_customer_sign');

        $task_client_id     = ts_get_task_client_id( $task_id );
        $client_sign_info   = ts_get_task_client_signature( $task_id , $task_client_id );

        if ( !empty( $ts_not_complete_without_customer_sign ) )
        {

            if ( !empty( $client_sign_info->id ) && $client_sign_info->signed == 0 )
                $has_unsigned = true;

        }


        if (!$has_unsigned)
        {

            // complete the task
            $CI->tasks_model->mark_as( 5 , $task_id );


            // closing all timers
            $CI->db->where('end_time IS NULL');

            $CI->db->where('task_id', $task_id);

            $CI->db->update(db_prefix() . 'taskstimers', [ 'end_time' => time() ] );

        }

    }

}


function ts_task_client_signature_email_content()
{

    $ts_signature_email_content = get_option('ts_signature_email_content');

    if ( empty( $ts_signature_email_content ) )
    {
        $ts_signature_email_content = 'Hello {client},

Your signature is required for the task titled "{task_name}".

Please use the link below to view the task details and add your signature:

Go to Task : {signature_link}';
    }

    return $ts_signature_email_content;

}
