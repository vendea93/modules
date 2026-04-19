<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// Expected variables: $course, $currency
$price = $course['discount_price'] > 0 ? $course['discount_price'] : $course['price'];
$has_discount = $course['discount_price'] > 0 && $course['discount_price'] < $course['price'];
$course_url = site_url('flexacademy/course/details/' . $course['slug']);
?>

<div class="flexacademy-course-card">
    <!-- Course Image -->
    <a href="<?php echo $course_url; ?>" class="flexacademy-course-image-wrapper">
        <img src="<?php echo flexacademy_media_url($course['image']); ?>" 
             alt="<?php echo htmlspecialchars($course['title']); ?>"
             class="flexacademy-course-image">
    </a>

    <!-- Course Content -->
    <div class="flexacademy-course-content">
        <!-- Meta Info -->
        <div class="tw-flex tw-items-center tw-gap-3 tw-mb-2">
            <div class="d-flex align-items-center gap-1 text-muted small">
                <i class="fa fa-users"></i>
                <span><?php echo $course['total_students']; ?> <?php echo _flexacademy_lang('student'); ?></span>
            </div>
            <div class="d-flex align-items-center gap-1 text-muted small">
                <i class="fa fa-clock"></i>
                <span><?php echo flexacademy_convert_duration_from_minutes($course['total_duration']); ?></span>
            </div>
        </div>

        <!-- Course Title -->
        <a href="<?php echo $course_url; ?>" class="tw-text-base tw-truncate tw-font-bold tw-mb-2">
            <?php echo htmlspecialchars($course['title']); ?>
        </a>

        <!-- Rating -->
        <!-- <?php if($course['total_reviews'] > 0): ?>
            <div class="flexacademy-course-rating">
                <div class="flexacademy-course-stars small">
                    <?php 
                    /*$rating = $course['average_rating'];
                    $full_stars = floor($rating);
                    $half_star = ($rating - $full_stars) >= 0.5;
                    $empty_stars = 5 - $full_stars - ($half_star ? 1 : 0);
                    
                    // Full stars
                    for($i = 0; $i < $full_stars; $i++) {
                        echo '<i class="fa fa-star"></i>';
                    }
                    // Half star
                    if($half_star) {
                        echo '<i class="fa fa-star-half-alt"></i>';
                    }
                    // Empty stars
                    for($i = 0; $i < $empty_stars; $i++) {
                        echo '<i class="fa fa-star-o"></i>';
                    }*/
                    ?>
                </div>
                <span class="small flexacademy-course-reviews">(<?php //echo $course['total_reviews']; ?> <?php //echo _flexacademy_lang('reviews'); ?>)</span>
            </div>
        <?php else: ?>
            <div class="flexacademy-course-rating">
                <span class="small flexacademy-course-reviews"><?php //echo _flexacademy_lang('no-reviews-yet'); ?></span>
            </div>
        <?php endif; ?> -->
        <div class="flexacademy-course-rating">
            <span class="small flexacademy-course-reviews tw-truncate "><?php echo $course['short_description']; ?></span>
        </div>

        <!-- Price and Button -->
        <div class="tw-flex tw-items-center tw-gap-3 tw-justify-between">
            <div class="tw-flex tw-items-center tw-gap-1">
                <?php if($course['pricing_type'] === 'free'): ?>
                    <span class=" flexacademy-course-price-free"><?php echo _flexacademy_lang('free'); ?></span>
                <?php else: ?>
                    <p class="tw-flex tw-m-0 tw-items-center tw-gap-1">
                    <span class="tw-text-base tw-font-bold">
                        <?php echo $currency->symbol . number_format($price, 2); ?>
                    </span>
                    <?php if($has_discount): ?>
                        <span class="tw-text-sm tw-line-through">
                            <?php echo $currency->symbol . number_format($course['price'], 2); ?>
                        </span>
                    <?php endif; ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <?php if($course['is_enrolled']): ?>
                <?php 
                $enrollment_progress = isset($course['enrollment_progress']) ? $course['enrollment_progress'] : 0;
                $button_text = $enrollment_progress > 0 ? _flexacademy_lang('continue-lesson') : _flexacademy_lang('start-now');
                ?>
                <a href="<?php echo site_url('flexacademy/course/player/' . $course['slug']); ?>" 
                   class="btn flexacademy-btn-primary tw-truncate btn-primary btn-sm">
                    <i class="fa fa-play tw-text-sm"></i> <?php echo $button_text; ?>
                </a>
            <?php elseif($course['pricing_type'] === 'free'): ?>
                <button class="btn btn-primary flexacademy-btn-primary btn-sm flexacademy-enroll-btn" 
                        data-course-id="<?php echo $course['id']; ?>"
                        data-enrolling-text="<?php echo _flexacademy_lang('enrolling'); ?>"
                        data-enroll-text="<?php echo _flexacademy_lang('enroll'); ?>"
                        data-error-course-id-missing="<?php echo _flexacademy_lang('course-id-missing'); ?>"
                        data-error-try-again="<?php echo _flexacademy_lang('error-occurred-try-again'); ?>">
                    <i class="fa fa-graduation-cap"></i> <?php echo _flexacademy_lang('enroll'); ?>
                </button>
            <?php else: ?>
                <a href="<?php echo $course_url; ?>" 
                   class="btn btn-default btn-sm tw-truncate">
                    <?php echo _flexacademy_lang('learn-more'); ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

