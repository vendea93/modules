<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="col-md-6">
    <div class="panel_s">
        <div class="panel-body">
            <h5 class="no-margin">
                <?php echo _flexacademy_lang('recent-enrollments'); ?>
                <a href="<?php echo admin_url('flexacademy/enrollments'); ?>" class="pull-right">
                    <?php echo _flexacademy_lang('view-all'); ?>
                </a>
            </h5>
            <hr class="hr-panel-separator" />
            <?php if (!empty($recent_enrollments)) { ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?php echo _flexacademy_lang('course-title'); ?></th>
                                <th><?php echo _flexacademy_lang('students'); ?></th>
                                <th><?php echo _flexacademy_lang('average-progress'); ?></th>
                                <th><?php echo _flexacademy_lang('enrollment-enrolled'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_enrollments as $enrollment) { ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo admin_url('flexacademy/course/' . $enrollment['course_id']); ?>">
                                            <?php echo $enrollment['course_title']; ?>
                                        </a>
                                    </td>
                                    <td><?php echo $enrollment['student_name']; ?></td>
                                    <td>
                                        <div class="progress no-margin progress-bar-mini">
                                            <div class="progress-bar progress-bar-success no-percent-text not-dynamic" 
                                                 role="progressbar"
                                                 aria-valuenow="<?php echo $enrollment['progress']; ?>" 
                                                 aria-valuemin="0"
                                                 aria-valuemax="100" 
                                                 style="width: 0%"
                                                 data-percent="<?php echo $enrollment['progress']; ?>">
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo _dt($enrollment['enrollment_date']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <p class="text-muted"><?php echo _flexacademy_lang('no-enrollments-found'); ?></p>
            <?php } ?>
        </div>
    </div>
</div>

