<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo _flexacademy_lang('staff-training-courses'); ?>
                    </h4>
                    <div>
                        <a href="<?php echo admin_url('flexacademy/staff_enrollments'); ?>" class="btn btn-primary mright5">
                            <i class="fa fa-book tw-mr-1"></i>
                            <?php echo _flexacademy_lang('my-enrollments'); ?>
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
                                <th><?php echo _flexacademy_lang('pricing') ?></th>
                                <th><?php echo _flexacademy_lang('actions') ?></th>
                            </thead>
                            <tbody>
                                <?php $i = 1;
                                foreach ($courses as $course):
                                    $pricing = $course['pricing_type'] == 'free' ? _flexacademy_lang('free') : app_format_money($course['discount_price'] > 0 ? $course['discount_price'] : $course['price'], get_base_currency());
                                ?>
                                    <tr>
                                        <td>
                                            <?php
                                            echo $i;
                                            $i++;
                                            ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo admin_url('flexacademy/course_details/' . $course['id']); ?>" class="tw-font-medium">
                                                <?php echo $course['title']; ?>
                                            </a>
                                        </td>
                                        <td>
                                            <?php echo character_limiter($course['short_description'], 80); ?>
                                        </td>
                                        <td>
                                            <?php echo $pricing; ?>
                                        </td>
                                        <td>
                                            <div class="tw-flex tw-gap-1">
                                                <a href="<?php echo admin_url('flexacademy/course_details/' . $course['id']); ?>" class="text text-default btn-sm" title="<?php echo _flexacademy_lang('view-details'); ?>">
                                                    <i class="fa fa-eye"></i>
                                                </a>
                                                <?php if ($course['is_enrolled']): ?>
                                                    <a href="<?php echo admin_url('flexacademy/staff_course_player/' . $course['slug']); ?>" class="text text-primary btn-sm" title="<?php echo $course['enrollment_progress'] > 0 ? _flexacademy_lang('continue') : _flexacademy_lang('start'); ?>">
                                                        <i class="fa fa-play-circle"></i>
                                                    </a>
                                                <?php else: ?>
                                                    <button class="text text-success btn-sm flexacademy-enroll-staff" 
                                                            data-course-id="<?php echo $course['id']; ?>"
                                                            title="<?php echo _flexacademy_lang('enroll-now'); ?>">
                                                        <i class="fa fa-check"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
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

<?php init_tail(); ?>
</body>
</html>
