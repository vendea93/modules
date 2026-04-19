<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo _flexacademy_lang('manage-courses'); ?>
                    </h4>
                    <div>
                        <a href="<?php echo admin_url('flexacademy/course'); ?>" class="btn btn-primary mright5">
                            <i class="fa-solid fa-plus tw-mr-1"></i>
                            <?php echo _flexacademy_lang('add-new-course'); ?>
                        </a>
                    </div>
                </div>
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <table class="table dt-table" data-order-col="0" data-order-type="asc">
                            <thead>
                                <th>#</th>
                                <th><?php echo _flexacademy_lang('course-name') ?></th>
                                <th><?php echo _flexacademy_lang('short-description') ?></th>
                                <th><?php echo _flexacademy_lang('status') ?></th>
                                <th><?php echo _flexacademy_lang('date-created') ?></th>
                                <th><?php echo _flexacademy_lang('actions') ?></th>
                            </thead>
                            <tbody>
                                <?php $i = 1;
                                foreach ($courses as $course):
                                    $status = $course['status'] == 'active' ? _flexacademy_lang('active') : _flexacademy_lang('inactive');
                                ?>
                                    <tr>
                                        <td>
                                            <?php
                                            echo $i;
                                            $i++;
                                            ?>
                                        </td>
                                        <td>
                                            <?php echo $course['title']; ?>
                                            <div class="row-options">
                                                <a href="<?php echo admin_url('flexacademy/course_details/' . $course['id']); ?>" class="text-info"> <?php echo _flexacademy_lang('details') ?></a> |
                                                <a href="<?php echo admin_url('flexacademy/delete_course/' . $course['id']); ?>" class="_delete text-danger"><?php echo _flexacademy_lang('delete') ?> </a>
                                            </div>
                                        </td>
                                        <td>
                                            <?php echo $course['short_description']; ?>
                                        </td>
                                        <td>
                                            <?php echo $status; ?>
                                        </td>
                                        <td>
                                            <?php echo _dt($course['created_at']); ?>
                                        </td>
                                        <td>
                                                <a href="<?php echo admin_url('flexacademy/course_details/' . $course['id']); ?>" class="text-info"> <?php echo _flexacademy_lang('manage-course') ?></a> |
                                                <a href="<?php echo admin_url('flexacademy/course/' . $course['id']); ?>" class="text-warning"> <?php echo _flexacademy_lang('edit-course') ?></a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<input type="hidden" id="flexacademy_ajax_url" value="<?php echo admin_url('flexacademy/ajax'); ?>">


<?php init_tail(); ?>
</body>

</html>