<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="col-md-6">
    <div class="panel_s">
        <div class="panel-body">
            <h5 class="no-margin">
                <?php echo _flexacademy_lang('recent-courses'); ?>
                <a href="<?php echo admin_url('flexacademy/courses'); ?>" class="pull-right">
                    <?php echo _flexacademy_lang('view-all'); ?>
                </a>
            </h5>
            <hr class="hr-panel-separator" />
            <?php if (!empty($recent_courses)) { ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?php echo _flexacademy_lang('course-title'); ?></th>
                                <th><?php echo _flexacademy_lang('course-instructor'); ?></th>
                                <th><?php echo _flexacademy_lang('course-status'); ?></th>
                                <th><?php echo _flexacademy_lang('date-created'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_courses as $course) { ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo admin_url('flexacademy/course_details/' . $course['id']); ?>">
                                            <?php echo $course['title']; ?>
                                        </a>
                                    </td>
                                    <td><?php echo $course['instructor_name']; ?></td>
                                    <td>
                                        <span class="label label-<?php echo $course['status'] == 'published' ? 'success' : ($course['status'] == 'draft' ? 'default' : 'warning'); ?>">
                                            <?php echo _flexacademy_lang('status-' . $course['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo _dt($course['date_created']); ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <p class="text-muted"><?php echo _flexacademy_lang('no-courses-found'); ?></p>
            <?php } ?>
        </div>
    </div>
</div>

