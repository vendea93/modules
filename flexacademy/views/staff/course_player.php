<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php if (!empty($show_selector)) { ?>
            <div class="row">
                <div class="col-md-12">
                    <div class="panel_s">
                        <div class="panel-body">
                            <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                                <h4 class="tw-my-0 tw-font-semibold">
                                    <?php echo _flexacademy_lang('course-player'); ?>
                                </h4>
                            </div>
                            <p class="tw-text-sm tw-text-neutral-600 tw-mb-4">
                                <?php echo _flexacademy_lang('staff-course-player-select'); ?>
                            </p>
                            <?php if (!empty($enrollments)) { ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                            <tr>
                                                <th><?php echo _flexacademy_lang('course-title'); ?></th>
                                                <th><?php echo _flexacademy_lang('progress'); ?></th>
                                                <th><?php echo _flexacademy_lang('status'); ?></th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($enrollments as $enrollment) { ?>
                                                <?php
                                                $progress = round((float) $enrollment['progress'], 2);
                                                $status_key = 'status-' . strtolower(str_replace('_', '-', $enrollment['status']));
                                                $status_label = _flexacademy_lang($status_key);
                                                if ($status_label === $status_key) {
                                                    $status_label = ucwords(str_replace(['_', '-'], ' ', $enrollment['status']));
                                                }

                                                $status_class = 'label-default';
                                                if (in_array($enrollment['status'], ['active', 'in_progress', 'in-progress'], true)) {
                                                    $status_class = 'label-info';
                                                } elseif ($enrollment['status'] === 'completed') {
                                                    $status_class = 'label-success';
                                                }

                                                if ($progress >= 100) {
                                                    $progress_bar_class = 'progress-bar-success';
                                                } elseif ($progress >= 50) {
                                                    $progress_bar_class = 'progress-bar-info';
                                                } else {
                                                    $progress_bar_class = 'progress-bar-warning';
                                                }
                                                ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($enrollment['course_title']); ?></td>
                                                    <td style="width:220px;">
                                                        <div class="progress no-margin progress-bar-mini">
                                                            <div class="progress-bar <?php echo $progress_bar_class; ?> no-percent-text not-dynamic" 
                                                                 role="progressbar"
                                                                 aria-valuenow="<?php echo $progress; ?>"
                                                                 aria-valuemin="0"
                                                                 aria-valuemax="100"
                                                                 style="width:0%"
                                                                 data-percent="<?php echo $progress; ?>">
                                                            </div>
                                                        </div>
                                                        <span class="tw-text-xs tw-font-semibold">
                                                            <?php echo $progress; ?>%
                                                        </span>
                                                    </td>
                                                    <td>
                                                        <span class="label <?php echo $status_class; ?>">
                                                            <?php echo $status_label; ?>
                                                        </span>
                                                    </td>
                                                    <td class="text-right">
                                                        <a href="<?php echo admin_url('flexacademy/staff_course_player/' . $enrollment['course_slug']); ?>" 
                                                           class="btn btn-primary btn-sm">
                                                            <i class="fa fa-play"></i>
                                                            <?php echo $progress > 0 ? _flexacademy_lang('continue') : _flexacademy_lang('start'); ?>
                                                        </a>
                                                    </td>
                                                </tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php } else { ?>
                                <div class="tw-text-center tw-py-12 tw-text-neutral-500">
                                    <i class="fa fa-info-circle fa-2x tw-mb-3"></i>
                                    <p class="tw-font-semibold tw-mb-0">
                                        <?php echo _flexacademy_lang('staff-course-player-empty'); ?>
                                    </p>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php } elseif (!empty($lesson)) { ?>
            <?php $this->load->view('flexacademy/partials/course-player', [
                'lesson' => $lesson,
                'course' => $course,
                'sections' => $sections,
                'lesson_progress' => $lesson_progress,
                'enrollment' => $enrollment,
                'back_url' => $back_url,
                'player_base_url' => $player_base_url,
            ]); ?>
        <?php } else { ?>
            <div class="panel_s">
                <div class="panel-body tw-text-center tw-py-12">
                    <i class="fa fa-info-circle fa-2x tw-text-neutral-500 tw-mb-3"></i>
                    <h4 class="tw-font-semibold tw-mb-2">
                        <?php echo _flexacademy_lang('no-lessons-yet'); ?>
                    </h4>
                    <p class="tw-text-neutral-500">
                        <?php echo _flexacademy_lang('lesson-content-coming-soon'); ?>
                    </p>
                </div>
            </div>
        <?php } ?>
    </div>
</div>
<?php init_tail(); ?>
