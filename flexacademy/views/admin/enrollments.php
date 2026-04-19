<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo _flexacademy_lang('enrollments'); ?>
                    </h4>
                    <div>
                        <button type="button"
                                class="btn btn-primary mright5"
                                data-toggle="modal"
                                data-target="#flexacademyEnrollStudentModal">
                            <i class="fa fa-graduation-cap tw-mr-1"></i>
                            <?php echo _flexacademy_lang('enroll-student'); ?>
                        </button>
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
                                <h3 class="tw-mb-2 tw-text-blue-600"><?php echo $stats['total_students']; ?></h3>
                                <p class="tw-text-sm tw-text-neutral-600 tw-mb-0">
                                    <i class="fa fa-users tw-mr-1"></i>
                                    <?php echo _flexacademy_lang('total-students'); ?>
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
                                <h3 class="tw-mb-2 tw-text-yellow-600"><?php echo $stats['in_progress']; ?></h3>
                                <p class="tw-text-sm tw-text-neutral-600 tw-mb-0">
                                    <i class="fa fa-spinner tw-mr-1"></i>
                                    <?php echo _flexacademy_lang('in-progress'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enrollments Table -->
                <div class="panel_s">
                    <div class="panel-body">
                        <?php if (empty($enrollments)) { ?>
                            <div class="tw-text-center tw-py-12">
                                <i class="fa fa-book-open tw-text-6xl tw-text-neutral-300 tw-mb-4"></i>
                                <h4 class="tw-text-neutral-500"><?php echo _flexacademy_lang('no-enrollments-yet'); ?></h4>
                                <p class="tw-text-neutral-400 tw-mb-4"><?php echo _flexacademy_lang('no-enrollments-found'); ?></p>
                            </div>
                        <?php } else { ?>
                            <table class="table dt-table" data-order-col="7" data-order-type="desc">
                                <thead>
                                    <tr>
                                        <th><?php echo _flexacademy_lang('student'); ?></th>
                                        <th><?php echo _flexacademy_lang('student-type'); ?></th>
                                        <th><?php echo _flexacademy_lang('course'); ?></th>
                                        <th><?php echo _flexacademy_lang('progress'); ?></th>
                                        <th><?php echo _flexacademy_lang('lessons-completed'); ?></th>
                                        <!-- <th><?php //echo _flexacademy_lang('time-spent'); ?></th> -->
                                        <th><?php echo _flexacademy_lang('enrolled-date'); ?></th>
                                        <th><?php echo _flexacademy_lang('enrollment-expiry-date'); ?></th>
                                        <th><?php echo _flexacademy_lang('status'); ?></th>
                                        <th><?php echo _flexacademy_lang('actions'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrollments as $enrollment) { ?>
                                        <tr>
                                            <td>
                                                <div>
                                                    <?php if (!empty($enrollment['student_profile_url'])) { ?>
                                                        <a href="<?php echo $enrollment['student_profile_url']; ?>"
                                                           class="tw-font-medium hover:tw-text-primary-600"
                                                           target="_blank" rel="noopener">
                                                            <?php echo htmlspecialchars($enrollment['student_name']); ?>
                                                        </a>
                                                    <?php } else { ?>
                                                        <span class="tw-font-medium">
                                                            <?php echo htmlspecialchars($enrollment['student_name']); ?>
                                                        </span>
                                                    <?php } ?>
                                                    <?php if (!empty($enrollment['student_meta'])) { ?>
                                                        <div class="tw-text-xs tw-text-neutral-500 tw-mt-1">
                                                            <i class="fa fa-id-badge"></i>
                                                            <?php echo htmlspecialchars($enrollment['student_meta']); ?>
                                                        </div>
                                                    <?php } elseif (!empty($enrollment['client_company'])) { ?>
                                                        <div class="tw-text-xs tw-text-neutral-500 tw-mt-1">
                                                            <i class="fa fa-building"></i>
                                                            <?php echo htmlspecialchars($enrollment['client_company']); ?>
                                                        </div>
                                                    <?php } ?>
                                                    <?php if (!empty($enrollment['student_email'])) { ?>
                                                        <div class="tw-text-xs tw-text-neutral-500">
                                                            <i class="fa fa-envelope"></i>
                                                            <?php echo htmlspecialchars($enrollment['student_email']); ?>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="label label-default">
                                                    <?php echo htmlspecialchars($enrollment['student_type_label']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="tw-flex tw-items-center tw-gap-3">
                                                    <?php if (!empty($enrollment['course_image'])) { ?>
                                                        <img src="<?php echo flexacademy_media_url($enrollment['course_image']); ?>" 
                                                             alt="<?php echo htmlspecialchars($enrollment['course_title']); ?>" 
                                                             class="tw-w-16 tw-h-12 tw-object-cover tw-rounded">
                                                    <?php } else { ?>
                                                        <div class="tw-w-16 tw-h-12 tw-bg-gradient-to-br tw-from-purple-400 tw-to-blue-500 tw-rounded tw-flex tw-items-center tw-justify-center">
                                                            <i class="fa fa-graduation-cap tw-text-white"></i>
                                                        </div>
                                                    <?php } ?>
                                                    <div>
                                                        <a href="<?php echo admin_url('flexacademy/course_details/' . $enrollment['course_id']); ?>" 
                                                           class="tw-font-medium hover:tw-text-primary-600">
                                                            <?php echo htmlspecialchars($enrollment['course_title']); ?>
                                                        </a>
                                                        <?php if ($enrollment['last_lesson']) { ?>
                                                            <div class="tw-text-xs tw-text-neutral-500 tw-mt-1">
                                                                <i class="fa fa-clock"></i>
                                                                <?php echo _flexacademy_lang('last-accessed'); ?>: 
                                                                <?php echo time_ago($enrollment['last_lesson']['last_accessed']); ?>
                                                            </div>
                                                        <?php } ?>
                                                    </div>
                                                </div>
                                            </td> 
                                            <td>
                                                <div class="tw-w-32">
                                                    <div class="progress no-margin progress-bar-mini tw-mb-1">
                                                        <div class="progress-bar no-percent-text not-dynamic
                                                            <?php 
                                                            if ($enrollment['progress'] >= 100) echo 'progress-bar-success';
                                                            elseif ($enrollment['progress'] >= 50) echo 'progress-bar-info';
                                                            else echo 'progress-bar-warning';
                                                            ?>" 
                                                             role="progressbar"
                                                             aria-valuenow="<?php echo round((float)$enrollment['progress']); ?>"
                                                             aria-valuemin="0"
                                                             aria-valuemax="100"
                                                             style="width: 0%"
                                                             data-percent="<?php echo round((float)$enrollment['progress']); ?>">
                                                        </div>
                                                    </div>
                                                    <span class="tw-text-xs tw-font-semibold"><?php echo round((float)$enrollment['progress']); ?>%</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="tw-text-sm">
                                                    <?php echo $enrollment['completed_lessons']; ?> / <?php echo $enrollment['total_lessons']; ?>
                                                </span>
                                            </td>
                                            <!-- <td>
                                                <span class="tw-text-sm">
                                                    <?php 
                                                    /*$hours = floor($enrollment['total_time_spent'] / 60);
                                                    $minutes = $enrollment['total_time_spent'] % 60;
                                                    if ($hours > 0) {
                                                        echo $hours . 'h ' . $minutes . 'm';
                                                    } else {
                                                        echo $minutes . 'm';
                                                    }*/
                                                    ?>
                                                </span>
                                            </td> -->
                                            <td>
                                                <span class="tw-text-sm">
                                                    <?php echo _dt($enrollment['enrolled_at']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <span class="tw-text-sm">
                                                    <?php echo !empty($enrollment['expires_at']) ? _dt($enrollment['expires_at']) : '—'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($enrollment['progress'] >= 100) { ?>
                                                    <span class="label label-success">
                                                        <i class="fa fa-check-circle"></i> <?php echo _flexacademy_lang('completed'); ?>
                                                    </span>
                                                <?php } elseif ($enrollment['status'] === 'active') { ?>
                                                    <span class="label label-info">
                                                        <i class="fa fa-play-circle"></i> <?php echo _flexacademy_lang('active'); ?>
                                                    </span>
                                                <?php } else { ?>
                                                    <span class="label label-default">
                                                        <?php 
                                                        $status_key = 'status-' . str_replace('_', '-', $enrollment['status']);
                                                        echo _flexacademy_lang($status_key);
                                                        ?>
                                                    </span>
                                                <?php } ?>
                                            </td>
                                            <td>
                                                <div class="tw-flex tw-gap-2">
                                                    <a href="<?php echo admin_url('flexacademy/course_details/' . $enrollment['course_id']); ?>" 
                                                       class="btn btn-default btn-sm" title="<?php echo _flexacademy_lang('view-course'); ?>">
                                                        <i class="fa fa-eye"></i>
                                                    </a>
                                                    <!--check if certificate is issued-->
                                                    <?php if (!empty($enrollment['certificate_id'])) { ?>
                                                        <a href="<?php echo flexacademy_get_certificate_url($enrollment['certificate_id'], true); ?>" 
                                                           class="btn text-info btn-sm" title="<?php echo _flexacademy_lang('view-certificate'); ?>">
                                                            <i class="fa fa-certificate"></i>
                                                        </a>
                                                    <?php } ?>
                                                    <a href="<?php echo admin_url('flexacademy/delete_enrollment/' . $enrollment['id']); ?>" 
                                                       class="btn text-danger btn-sm _delete" title="<?php echo _flexacademy_lang('delete-enrollment'); ?>">
                                                        <i class="fa-regular fa-trash-can"></i>
                                                    </a>
                                                    <?php if (!empty($enrollment['invoice_id'])) { ?>
                                                        <a href="<?php echo admin_url('invoices/list_invoices/' . $enrollment['invoice_id']); ?>" 
                                                           class="btn btn-info btn-sm" title="<?php echo _flexacademy_lang('view-invoice'); ?>">
                                                            <i class="fa fa-file-invoice"></i>
                                                        </a>
                                                    <?php } ?>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->load->view('partials/admin/enrollments/enroll-student-modal'); ?>

<?php init_tail(); ?>
</body>
</html>

