<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="col-md-8">
    <div class="panel_s">
        <div class="panel-body">
            <h5 class="no-margin"><?php echo _flexacademy_lang('top-performing-courses'); ?></h5>
            <hr class="hr-panel-separator" />
            <?php if (!empty($top_courses)) { ?>
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th><?php echo _flexacademy_lang('course-title'); ?></th>
                                <th><?php echo _flexacademy_lang('total-enrollments'); ?></th>
                                <th><?php echo _flexacademy_lang('average-progress'); ?></th>
                                <th><?php echo _flexacademy_lang('completion-rates'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_courses as $course) { ?>
                                <tr>
                                    <td>
                                        <a href="<?php echo admin_url('flexacademy/course/' . $course['id']); ?>">
                                            <?php echo $course['title']; ?>
                                        </a>
                                    </td>
                                    <td><?php echo $course['enrollment_count']; ?></td>
                                    <td>
                                        <div class="progress no-margin progress-bar-mini">
                                            <div class="progress-bar progress-bar-success no-percent-text not-dynamic" 
                                                 role="progressbar"
                                                 aria-valuenow="<?php echo round($course['avg_progress'], 1); ?>" 
                                                 aria-valuemin="0"
                                                 aria-valuemax="100" 
                                                 style="width: 0%"
                                                 data-percent="<?php echo round($course['avg_progress'], 1); ?>">
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo round($course['avg_progress'], 1); ?>%</td>
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

