<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>


<div data-sticky-class="preview-sticky-header">

    <div class="container preview-sticky-container">

        <div class="sm:tw-flex sm:tw-justify-between -tw-mx-4">

            <div class="sm:tw-self-end tw-inline-flex">

                <h4 class="tw-my-0 tw-font-semibold contract-html-subject">

                    <?php echo $title ?>

                </h4>

            </div>

            <div class="tw-flex tw-items-end tw-space-x-2 tw-mt-3 sm:tw-mt-0">


                <a href="<?php echo site_url('task_signing/client_side'); ?>"

                   class="btn btn-default action-button go-to-portal">

                    <?php echo _l('client_go_to_dashboard'); ?>

                </a>

            </div>

        </div>

    </div>

</div>

<br />

<div class="panel_s">

    <div class="panel-body">

        <div class="col-md-7">

            <h4>
                <span class="bold"><?php echo _l('task_single_start_date'); ?>:</span>
                <?php echo _dt( $task->startdate ); ?>
            </h4>

            <hr />

            <h4>
                <span class="bold"><?php echo _l('task_single_due_date'); ?>:</span>
                <?php echo _dt( $task->duedate ); ?>
            </h4>

            <hr />

            <h4>
                <span class="bold"><?php echo _l('task_status'); ?>:</span>
                <?php echo format_task_status( $task->status , true ); ?>
            </h4>

            <hr />

            <h4 class="bold"><?php echo _l('task_view_description'); ?></h4>

            <div class="tc-content">

                <?php echo $task->description; ?>

            </div>

        </div>


        <!-- Task sign -->
        <div class="col-md-5">


            <?php if ( $sign_info->signed == 0 ) {

                require_once(__DIR__ . '/task_signature_inc.php');

            } else {

                echo "<h3 class='text text-success'> "._l('ts_task_signed')." </h3>";
                echo "<img style='width: 400px' src='".site_url( $sign_info->signature )."' />";


                echo "<br/>";
                echo "<br/>";
                echo "<span> "._l('ts_task_signed_by')." : ".get_contact_full_name( $sign_info->contact_id )." </span>";

                echo "<br/>  ";
                echo "<span> "._l('ts_signature_date').' : '._dt($sign_info->signature_date)." </span>";

                echo "<br/>";
                echo "<span> "._l('ts_ip_address')." : ".$sign_info->ip_address." </span>";

            } ?>

        </div>

    </div>

</div>