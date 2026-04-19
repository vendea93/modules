<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700 section-heading section-heading-purchase_orders">
    <?php echo _l('als_tasks'); ?>
</h4>
<div class="panel_s">
    <div class="panel-body">

        <table class="table dt-table table-tasks" data-order-col="0" data-order-type="desc">

            <thead>

                <tr>

                    <th class="th-invoice-id">#</th>

                    <th class="th-invoice-name"><?php echo _l('task'); ?></th>

                    <th class="th-invoice-status"><?php echo _l('task_status'); ?></th>

                    <th class="th-invoice-startdate"><?php echo _l('task_single_start_date'); ?></th>

                    <th class="th-invoice-duedate"><?php echo _l('task_single_due_date'); ?></th>

                    <th class="th-invoice-signed"><?php echo _l('signature'); ?></th>

                </tr>

            </thead>

            <tbody>

            <?php foreach ($tasks as $task ) { ?>

                <tr>

                    <td data-order="<?php echo $task->id; ?>"> <?php echo $task->id; ?> </td>
                    <td data-order="<?php echo $task->name; ?>">
                        <a href="<?php echo site_url('task_signing/client_side/detail/'.$task->id)?>"><?php echo $task->name; ?></a>
                    </td>
                    <td data-order="<?php echo $task->status; ?>"> <?php echo format_task_status( $task->status ); ?> </td>
                    <td data-order="<?php echo $task->startdate; ?>"> <?php echo _dt( $task->startdate ); ?> </td>
                    <td data-order="<?php echo $task->duedate; ?>"> <?php echo _dt( $task->duedate ); ?> </td>
                    <td data-order="<?php echo $task->signed; ?>">
                        <?php
                        if ( $task->signed == 1 )
                        {
                            echo _l('is_signed');
                        }
                        else
                        {
                            echo _l('is_not_signed');
                        }
                        ?>
                    </td>

                </tr>

            <?php } ?>

            </tbody>

        </table>


    </div>
</div>