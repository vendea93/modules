<aside class="tw-w-full">
        <div class="panel_s tw-p-4">
            <div>
                <div class="tw-aspect-square tw-overflow-hidden tw-relative tw-w-full tw-rounded-md tw-bg-white tw-mb-3 sm:tw-mb-4">
                    <img class="tw-w-full tw-h-full tw-object-cover" src="<?php echo flexacademy_media_url($course['image']); ?>" />
                </div>
                <h2 class="tw-text-lg tw-mb-3">
                    <span class="tw-font-semibold"><?php echo $currency->symbol . number_format($course['discount_price'], 2) ?></span>
                    <span class="tw-text-gray-500 tw-text-sm tw-line-through tw-opacity-80"><?php echo $currency->symbol . number_format($course['price'], 2) ?></span>
            </h2>
            <?php 
            // Enrollment data is passed from controller
            $is_enrolled = isset($enrollment) && $enrollment;
            $enrollment_progress = $is_enrolled ? (isset($enrollment->progress) ? $enrollment->progress : 0) : 0;
            ?>
            
            <div class="tw-flex mbot10 tw-flex-col sm:tw-flex-row tw-items-stretch tw-gap-2 sm:tw-gap-3">
                <?php if($is_enrolled): ?>
                    <a href="<?php echo site_url('flexacademy/course/player/' . $course['slug']); ?>" 
                       class="btn btn-primary flexacademy-btn-primary tw-w-full">
                        <i class="fa fa-play"></i> 
                        <?php echo $enrollment_progress > 0 ? _flexacademy_lang('continue-lesson') : _flexacademy_lang('start-now'); ?>
                    </a>
                <?php elseif($course['pricing_type'] === 'free'): ?>
                    <button class="btn flexacademy-btn-primary tw-w-full" 
                            data-course-id="<?php echo $course['id']; ?>"
                            data-enrolling-text="<?php echo _flexacademy_lang('enrolling'); ?>"
                            data-enroll-text="<?php echo _flexacademy_lang('enroll'); ?>">
                        <i class="fa fa-graduation-cap"></i> <?php echo _flexacademy_lang('enroll'); ?>
                    </button>
                <?php else: ?>
                    <button id="flexacademy-add-to-cart-btn" 
                            class="btn btn-primary tw-w-full"
                            data-course-id="<?php echo $course['id']; ?>"
                            data-text-add="<?php echo _flexacademy_lang('add-to-cart'); ?>"
                            data-text-added="<?php echo _flexacademy_lang('added-to-cart'); ?>"
                            <?php echo $is_in_cart ? 'disabled' : ''; ?>>
                        <span class="add-to-cart-text <?php echo $is_in_cart ? 'tw-hidden' : ''; ?>"><?php echo _flexacademy_lang('buy-now'); ?></span>
                        <span class="added-to-cart-text <?php echo $is_in_cart ? '' : 'tw-hidden'; ?>"><?php echo _flexacademy_lang('added-to-cart'); ?></span>
                    </button>
                <?php endif; ?>
            </div>
            </div>
            <div class="tw-flex tw-mt-4 tw-flex-col tw-gap-2 sm:tw-gap-4">
                <div class="tw-flex tw-justify-between tw-items-center ">
                    <p class="tw-m-0 tw-text-gray-600"><i class="fa fa-users tw-mr-2"></i>
                        <?php echo _flexacademy_lang('students'); ?></p>
                    <p class="tw-m-0 tw-font-medium"><?php echo $enrollment_count; ?></p>
                </div>
                <div class="tw-flex tw-justify-between tw-items-center ">
                    <p class="tw-m-0 tw-text-gray-600"><i class="fa fa-language tw-mr-2"></i> <?php echo _flexacademy_lang('language') ?></p>
                    <p class="tw-m-0 tw-font-medium"><?php echo flexacademy_get_course_languages($course['language']); ?></p>
                </div>
                <div class="tw-flex tw-justify-between tw-items-center ">
                    <p class="tw-m-0 tw-text-gray-600"><i class="fa fa-clock tw-mr-2"></i>
                        <?php echo _flexacademy_lang('duration'); ?></p>
                    <p class="tw-m-0 tw-font-medium"> <?php 
                        echo flexacademy_convert_duration_from_minutes($totalDuration) ?></p>
                </div>
                <div class="tw-flex tw-justify-between tw-items-center  tw-capitalize">
                    <p class="tw-m-0 tw-text-gray-600"><i class="fa fa-chart-simple tw-mr-2"></i> <?php echo _flexacademy_lang('level') ?></p>
                    <p class="tw-m-0 tw-font-medium"><?php echo flexacademy_get_course_levels($course["difficulty_level"])?></p>
                </div>
                <div class="tw-flex tw-justify-between tw-items-center  tw-gap-2">
                    <p class="tw-m-0 tw-text-gray-600 tw-flex-shrink-0"><i class="fa fa-clock tw-mr-2"></i>
                        <?php echo _flexacademy_lang('expiry_period') ?></p>
                    <p class="tw-m-0 tw-text-right tw-font-medium"><?php echo $course["expiry_period"] . " " . flexacademy_expiry_types($course["expiry_type"]); ?>
                    </p>
                </div>
                <div class="tw-flex tw-justify-between tw-items-center ">
                    <p class="tw-m-0 tw-text-gray-600"><i class="fa fa-envelope tw-mr-2"></i> <?php echo _flexacademy_lang('certificate-included'); ?></p>
                    <p class="tw-m-0 tw-font-medium"><?php echo _flexacademy_lang('yes'); ?></p>
                </div>
            </div>
        </div>
    </aside>