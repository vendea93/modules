<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo _flexacademy_lang('my-enrollments'); ?>
                    </h4>
                    <div>
                        <a href="<?php echo admin_url('flexacademy/staff_courses'); ?>" class="btn btn-primary mright5">
                            <i class="fa fa-graduation-cap tw-mr-1"></i>
                            <?php echo _flexacademy_lang('browse-courses'); ?>
                        </a>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="row tw-mb-4">
                    <div class="col-md-3 col-sm-6">
                        <div class="panel_s">
                            <div class="panel-body tw-text-center">
                                <h3 class="tw-mb-2 tw-text-primary-600"><?php echo $stats['total_enrollments']; ?></h3>
                                <p class="tw-text-sm tw-text-neutral-600 tw-mb-0">
                                    <i class="fa fa-book tw-mr-1"></i>
                                    <?php echo _flexacademy_lang('total-enrollments'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="panel_s">
                            <div class="panel-body tw-text-center">
                                <h3 class="tw-mb-2 tw-text-blue-600"><?php echo $stats['in_progress']; ?></h3>
                                <p class="tw-text-sm tw-text-neutral-600 tw-mb-0">
                                    <i class="fa fa-spinner tw-mr-1"></i>
                                    <?php echo _flexacademy_lang('in-progress'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="panel_s">
                            <div class="panel-body tw-text-center">
                                <h3 class="tw-mb-2 tw-text-green-600"><?php echo $stats['completed_enrollments']; ?></h3>
                                <p class="tw-text-sm tw-text-neutral-600 tw-mb-0">
                                    <i class="fa fa-check-circle tw-mr-1"></i>
                                    <?php echo _flexacademy_lang('completed'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="panel_s">
                            <div class="panel-body tw-text-center">
                                <h3 class="tw-mb-2 tw-text-yellow-600"><?php echo $stats['active_enrollments']; ?></h3>
                                <p class="tw-text-sm tw-text-neutral-600 tw-mb-0">
                                    <i class="fa fa-play-circle tw-mr-1"></i>
                                    <?php echo _flexacademy_lang('active'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enrollments Table -->
                <div class="panel_s">
                    <div class="panel-body panel-table-full">
                        <?php if (empty($enrollments)): ?>
                            <div class="tw-text-center tw-py-12">
                                <i class="fa fa-book-open tw-text-6xl tw-text-neutral-300 tw-mb-4"></i>
                                <h4 class="tw-text-neutral-500"><?php echo _flexacademy_lang('no-enrollments-yet'); ?></h4>
                                <p class="tw-text-neutral-400 tw-mb-4"><?php echo _flexacademy_lang('start-learning-enroll-course'); ?></p>
                                <a href="<?php echo admin_url('flexacademy/staff_courses'); ?>" class="btn btn-primary">
                                    <i class="fa fa-graduation-cap tw-mr-1"></i>
                                    <?php echo _flexacademy_lang('browse-courses'); ?>
                                </a>
                            </div>
                        <?php else: ?>
                            <table class="table dt-table" data-order-col="4" data-order-type="desc">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th><?php echo _flexacademy_lang('course'); ?></th>
                                        <th><?php echo _flexacademy_lang('progress'); ?></th>
                                        <th><?php echo _flexacademy_lang('lessons-completed'); ?></th>
                                        <th><?php echo _flexacademy_lang('enrolled-date'); ?></th>
                                        <th><?php echo _flexacademy_lang('last-accessed'); ?></th>
                                        <th><?php echo _flexacademy_lang('status'); ?></th>
                                        <th><?php echo _flexacademy_lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i = 1; foreach ($enrollments as $enrollment): ?>
                                        <tr>
                                            <td><?php echo $i++; ?></td>
                                            <td>
                                                <div class="tw-flex tw-items-center tw-gap-3">
                                                    <?php if (!empty($enrollment['course_image'])): ?>
                                                        <img src="<?php echo flexacademy_media_url($enrollment['course_image']); ?>" 
                                                             alt="<?php echo htmlspecialchars($enrollment['course_title']); ?>" 
                                                             style="width: 48px; height: 48px; object-fit: cover; border-radius: 4px; flex-shrink: 0;">
                                                    <?php else: ?>
                                                        <div style="width: 48px; height: 48px; background: linear-gradient(135deg, #a78bfa 0%, #3b82f6 100%); border-radius: 4px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                            <i class="fa fa-graduation-cap" style="color: white;"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <a href="<?php echo admin_url('flexacademy/course_details/' . $enrollment['course_id']); ?>" 
                                                           class="tw-font-medium hover:tw-text-primary-600">
                                                            <?php echo htmlspecialchars($enrollment['course_title']); ?>
                                                        </a>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div style="width: 120px;">
                                                    <?php 
                                                    // Ensure progress is always numeric
                                                    $progress_value = 0;
                                                    if (isset($enrollment['progress'])) {
                                                        if (is_numeric($enrollment['progress'])) {
                                                            $progress_value = (float)$enrollment['progress'];
                                                        }
                                                    }
                                                    ?>
                                                    <div class="progress" style="height: 8px; margin-bottom: 4px;">
                                                        <div class="progress-bar no-percent-text
                                                            <?php 
                                                            if ($progress_value >= 100) echo 'progress-bar-success';
                                                            elseif ($progress_value >= 50) echo 'progress-bar-info';
                                                            else echo 'progress-bar-warning';
                                                            ?>" 
                                                             role="progressbar" 
                                                                aria-valuenow="<?php echo $progress_value; ?>"
                                                                aria-valuemin="0" aria-valuemax="100" 
                                                                 data-percent="<?php echo $progress_value; ?>">
                                                        </div>
                                                    </div>
                                                    <span style="font-size: 11px; font-weight: 600;"><?php echo $progress_value; ?>%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="tw-text-sm">
                                                    <?php echo $enrollment['completed_lessons']; ?> / <?php echo $enrollment['total_lessons']; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="tw-text-sm">
                                                    <?php echo _dt($enrollment['enrolled_at']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="tw-text-sm">
                                                    <?php 
                                                    if ($enrollment['last_lesson']) {
                                                        echo time_ago($enrollment['last_lesson']['last_accessed']);
                                                    } else {
                                                        echo '-';
                                                    }
                                                    ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($enrollment['progress'] >= 100): ?>
                                                    <span class="label label-success">
                                                        <i class="fa fa-check-circle"></i> <?php echo _flexacademy_lang('completed'); ?>
                                                    </span>
                                                <?php elseif ($enrollment['status'] === 'active'): ?>
                                                    <span class="label label-info">
                                                        <i class="fa fa-play-circle"></i> <?php echo _flexacademy_lang('active'); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="label label-default">
                                                        <?php 
                                                        $status_key = 'status-' . str_replace('_', '-', $enrollment['status']);
                                                        echo _flexacademy_lang($status_key);
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="tw-flex tw-gap-1">
                                                    <a href="<?php echo admin_url('flexacademy/course_details/' . $enrollment['course_id']); ?>" 
                                                       class="text text-default btn-sm" 
                                                       title="<?php echo _flexacademy_lang('view-details'); ?>">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <?php if ($enrollment['progress'] >= 100): ?>
                                                        <a href="<?php echo admin_url('flexacademy/staff_course_player/' . $enrollment['course_slug']); ?>" 
                                                           class="text text-success btn-sm"
                                                           title="<?php echo _flexacademy_lang('review'); ?>">
                                                            <i class="fa fa-eye"></i>
                                                        </a>
                                                    <?php elseif ($enrollment['progress'] > 0): ?>
                                                        <a href="<?php echo admin_url('flexacademy/staff_course_player/' . $enrollment['course_slug']); ?>" 
                                                           class="text text-primary btn-sm"
                                                           title="<?php echo _flexacademy_lang('continue'); ?>">
                                                            <i class="fa fa-play-circle"></i>
                                                        </a>
                                                    <?php else: ?>
                                                        <a href="<?php echo admin_url('flexacademy/staff_course_player/' . $enrollment['course_slug']); ?>" 
                                                           class="text text-primary btn-sm"
                                                           title="<?php echo _flexacademy_lang('start'); ?>">
                                                            <i class="fa fa-play-circle"></i>
                                                        </a>
                                                    <?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
</body>
</html>
