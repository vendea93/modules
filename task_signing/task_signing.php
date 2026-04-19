<?php

defined("BASEPATH") or exit("No direct script access allowed");

/*
Module Name: Task Completion Signature Module
Description: Secure the process and make your task management more disciplined by collecting signatures from employees who have completed their tasks.
Author: Halil
Author URI: https://codecanyon.net/user/halilaltndg/portfolio
Version: 1.0.3
*/


define("TASK_SIGNING_MODULE_NAME", "task_signing");


$CI = &get_instance();

/**
 * @note Language uploading
 */
register_language_files(TASK_SIGNING_MODULE_NAME, [TASK_SIGNING_MODULE_NAME]);


/**
 * Load the module helper
 */
$CI->load->helper(TASK_SIGNING_MODULE_NAME . '/'.TASK_SIGNING_MODULE_NAME);

register_activation_hook(TASK_SIGNING_MODULE_NAME, "task_signing_module_activation_hook");


/**
 * @note task signature db installing
 */
function task_signing_module_activation_hook()
{

    $CI = &get_instance();

    require_once __DIR__ . "/install.php";

}


/**
 * @note task model view
 */
hooks()->add_action('before_task_description_section',function ( $task ){


    /**
     * @Version 1.0.3
     */
    if ( !empty( $task->is_signature_required ) && $task->is_signature_required == 1 )
    {

        if ( !empty( $task->assignees ) || !empty( $task->followers ) )
        {

            $taskid = $task->id;

            _maybe_create_upload_path(FCPATH."uploads/task_sign/");
            _maybe_create_upload_path(FCPATH."uploads/task_sign/task_$taskid/");

            $sign_image_path = "uploads/task_sign/task_$taskid";

            $task_staff = [];

            if ( !empty( $task->assignees ) )
            {

                foreach ( $task->assignees as $task_assignee_key => $task_assignee )
                {

                    $task_staff[] = $task_assignee["assigneeid"];

                }

            }


            $ts_followers_will_sign = get_option('ts_followers_will_sign');

            if ( !empty( $task->followers ) && !empty( $ts_followers_will_sign ) )
            {

                foreach ( $task->followers as $task_follower_key => $follower )
                {

                    $task_staff[] = $follower["followerid"];

                }

            }


            $task_staff_lists = get_task_signature_staff_orders( $task_staff );


            $has_need_signature = false;
            $all_staff_signed   = true;
            $task_sign_index = 0;

            foreach ( $task_staff_lists as $task_staff_list )
            {

                $task_assigned_id = $task_staff_list->staffid;

                $task_staff_list->can_sign = false;

                $staff_sign_image_path = "uploads/task_sign/task_$taskid/staff_$task_assigned_id";

                _maybe_create_upload_path(FCPATH."$staff_sign_image_path");


                if( file_exists( FCPATH."$staff_sign_image_path/signature.png" ) )
                {
                    $task_staff_list->assignee_sign = site_url("$staff_sign_image_path/signature.png");
                }
                else
                {

                    $all_staff_signed = false;

                    $has_need_signature = true;

                    if ( $task_sign_index == 0 )
                    {

                        $task_staff_list->can_sign = true;

                        $task_sign_index = $task_staff_list->task_sign_index;

                    }
                    elseif ( $task_sign_index == $task_staff_list->task_sign_index )
                    {

                        $task_staff_list->can_sign = true;

                    }

                }


            }

            require_once __DIR__ . '/includes/task_model_signing.php';

        }

    }


});




hooks()->add_action("app_admin_footer", function (){

    echo "
    
    <script> var lang_ts_send_signature_notification = '"._l('ts_send_signature_notification')."'; </script>
    
    <script src='" . base_url("modules/task_signing/assets/task_signing_js.js?v=".time()) ."'></script> 
    ";


});


function get_task_signature_staff_orders( $staff_ids = [] )
{

    if ( empty( $staff_ids ) )
        return [];

    return get_instance()->db->select('staffid, firstname, lastname, task_sign_index')
                            ->from(db_prefix().'staff')
                            ->where_in('staffid',$staff_ids)
                            ->order_by('task_sign_index')
                            ->get()
                            ->result();


}



/**
 * @version 1.0.2
 */
hooks()->add_action('after_settings_group_view',function ( $tab ){



    $tab_slug = '';

    if ( !empty( $tab['slug'] ) )
        $tab_slug = $tab['slug'];
    elseif ( !empty( $tab['id'] ) )
        $tab_slug = $tab['id'];


    if ( $tab_slug == 'tasks' )
    {

        include_once __DIR__ . '/includes/task_signature_setting.php';

    }



});


/**
 * @Version 1.0.3
 * Customer Side
 */


/**
 * Customer navication menu button
 */
hooks()->add_action('customers_navigation_start',function (){


    if( is_client_logged_in() ) {

        $get_client_user_id = get_client_user_id();

        $get_info = get_instance()->db->select('id')
                                        ->from(db_prefix().'task_client_signature_info')
                                        ->where('client_id',$get_client_user_id)
                                        ->where('signed',0)
                                        ->get()
                                        ->row();

        if ( !empty( $get_info ) ) { ?>

            <li class="customers-nav-task-sign">

                <a href="<?php echo site_url('task_signing/client_side')?>">
                    <?php echo _l('ts_client_side_menu')?>
                </a>

            </li>

        <?php } ?>

    <?php }


});
