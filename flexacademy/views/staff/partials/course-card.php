<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="col-md-4 col-sm-6">
    <div class="panel_s tw-mb-4">
        <!-- Course Image -->
        <div class="tw-relative">
            <?php if (!empty($course['image'])): ?>
                <img src="<?php echo flexacademy_media_url($course['image']); ?>" 
                     alt="<?php echo htmlspecialchars($course['title']); ?>" 
                     class="tw-w-full tw-h-48 tw-object-cover">
            <?php else: ?>
                <div class="tw-w-full tw-h-48 tw-bg-gradient-to-br tw-from-purple-400 tw-to-blue-500 tw-flex tw-items-center tw-justify-center">
                    <i class="fa fa-graduation-cap tw-text-6xl tw-text-white tw-opacity-50"></i>
                </div>
            <?php endif; ?>
            
            <!-- Enrollment Badge -->
            <?php if ($course['is_enrolled']): ?>
                <div class="tw-absolute tw-top-2 tw-right-2">
                    <span class="label label-success">
                        <i class="fa fa-check-circle"></i> <?php echo _flexacademy_lang('enrolled'); ?>
                    </span>
                </div>
            <?php endif; ?>
            
            <!-- Pricing Badge -->
            <div class="tw-absolute tw-bottom-2 tw-left-2">
                <?php if ($course['pricing_type'] === 'free'): ?>
                    <span class="label label-info"><?php echo _flexacademy_lang('free'); ?></span>
                <?php else: ?>
                    <span class="label label-primary">
                        <?php 
                        if ($course['discount_price'] > 0) {
                            echo app_format_money($course['discount_price'], get_base_currency());
                        } else {
                            echo app_format_money($course['price'], get_base_currency());
                        }
                        ?>
                    </span>
                <?php endif; ?>
            </div>
        </div>

        <!-- Course Details -->
        <div class="panel-body">
            <h4 class="tw-mt-0 tw-mb-2">
                <a href="<?php echo admin_url('flexacademy/course_details/' . $course['id']); ?>" 
                   class="tw-text-neutral-800 hover:tw-text-primary-600">
                    <?php echo htmlspecialchars($course['title']); ?>
                </a>
            </h4>
            
            <p class="tw-text-sm tw-text-neutral-600 tw-mb-3">
                <?php echo character_limiter(strip_tags($course['short_description']), 100); ?>
            </p>

            <!-- Course Stats -->
            <div class="tw-flex tw-items-center tw-gap-4 tw-text-xs tw-text-neutral-500 tw-mb-3">
                <div class="tw-flex tw-items-center tw-gap-1">
                    <i class="fa fa-clock"></i>
                    <span><?php echo $course['total_duration']; ?></span>
                </div>
                <div class="tw-flex tw-items-center tw-gap-1">
                    <i class="fa fa-book"></i>
                    <span><?php echo $course['total_lessons']; ?> <?php echo _flexacademy_lang('lessons'); ?></span>
                </div>
                <div class="tw-flex tw-items-center tw-gap-1">
                    <i class="fa fa-users"></i>
                    <span><?php echo $course['total_students']; ?> <?php echo _flexacademy_lang('students'); ?></span>
                </div>
            </div>

            <!-- Progress Bar (if enrolled) -->
            <?php if ($course['is_enrolled']): ?>
                <div class="tw-mb-3">
                    <div class="tw-flex tw-justify-between tw-text-xs tw-mb-1">
                        <span class="tw-text-neutral-600"><?php echo _flexacademy_lang('progress'); ?></span>
                        <span class="tw-font-semibold tw-text-primary-600"><?php echo round($course['enrollment_progress']); ?>%</span>
                    </div>
                    <div class="progress tw-h-2">
                        <div class="progress-bar progress-bar-success" 
                             role="progressbar" 
                             style="width: <?php echo $course['enrollment_progress']; ?>%">
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Action Buttons -->
            <div class="tw-flex tw-gap-2">
                <?php if ($course['is_enrolled']): ?>
                    <?php if ($course['enrollment_progress'] >= 100): ?>
                        <a href="<?php echo admin_url('flexacademy/staff_course_player/' . $course['slug']); ?>" 
                           class="btn btn-success tw-text-sm tw-flex tw-items-center tw-justify-center tw-gap-2">
                            <i class="fa fa-eye"></i>
                            <?php echo _flexacademy_lang('view-course'); ?>
                        </a>
                    <?php elseif ($course['enrollment_progress'] > 0): ?>
                        <a href="<?php echo admin_url('flexacademy/staff_course_player/' . $course['slug']); ?>" 
                           class="btn btn-primary tw-text-sm tw-flex tw-items-center tw-justify-center tw-gap-2">
                            <i class="fa fa-refresh"></i>
                            <?php echo _flexacademy_lang('continue'); ?>
                        </a>
                    <?php else: ?>
                        <a href="<?php echo admin_url('flexacademy/staff_course_player/' . $course['slug']); ?>" 
                           class="btn btn-primary tw-text-sm tw-flex tw-items-center tw-justify-center tw-gap-2">
                            <i class="fa fa-play"></i>
                            <?php echo _flexacademy_lang('start'); ?>
                        </a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo admin_url('flexacademy/course_details/' . $course['id']); ?>" 
                       class="btn btn-default btn-sm tw-flex-1">
                        <i class="fa fa-eye"></i> <?php echo _flexacademy_lang('view-details'); ?>
                    </a>
                    <?php if ($course['pricing_type'] === 'free'): ?>
                        <button class="btn btn-primary btn-sm tw-flex-1 flexacademy-enroll-free" 
                                data-course-id="<?php echo $course['id']; ?>">
                            <i class="fa fa-check"></i> <?php echo _flexacademy_lang('enroll-now'); ?>
                        </button>
                    <?php else: ?>
                        <a href="<?php echo admin_url('flexacademy/course_details/' . $course['id']); ?>" 
                           class="btn btn-primary btn-sm tw-flex-1">
                            <i class="fa fa-shopping-cart"></i> <?php echo _flexacademy_lang('enroll'); ?>
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

