<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
// Expected variables: $enrollment
$course_url = site_url('flexacademy/course/details/' . $enrollment['course_slug']);
$player_url = site_url('flexacademy/course/player/' . $enrollment['course_slug']);
$progress_percentage = round($enrollment['progress'], 1);
$payment_status = isset($enrollment['payment_status']) ? $enrollment['payment_status'] : 'paid';
$invoice_id = isset($enrollment['invoice_id']) ? $enrollment['invoice_id'] : null;

?>

<div class="panel_s tw-p-4">
    <!-- Course Image with Status Badge -->
    <div class="flexacademy-my-course-image-wrapper">
        <a class="tw-overflow-hidden" href="<?php echo $course_url; ?>">
            <img src="<?php echo flexacademy_media_url($enrollment['course_image']); ?>" 
                 alt="<?php echo htmlspecialchars($enrollment['course_title']); ?>" 
                 class="tw-w-full tw-h-full tw-overflow-hidden tw-relative tw-border tw-border-solid tw-border-neutral-200 tw-object-cover">
        </a>
        <?php if ($enrollment['status'] === 'completed'): ?>
            <span class="badge badge-success">
                <i class="fa fa-check"></i> <?php echo _flexacademy_lang('completed'); ?>
            </span>
        <?php endif; ?>
    </div>
    
    <!-- Course Content -->
    <div>
        <!-- Instructor Info -->
        <div class="tw-flex tw-items-center tw-gap-2">
            <img src="<?php echo $enrollment['instructor_image']; ?>" 
                 alt="<?php echo htmlspecialchars($enrollment['instructor_name']); ?>" 
                 class="tw-w-6 tw-h-6 tw-object-cover tw-overflow-hidden tw-rounded-full">
            <span class="tw-text-sm"><?php echo htmlspecialchars($enrollment['instructor_name']); ?></span>
        </div>

        <!-- Course Title -->
        <a href="<?php echo $course_url; ?>">
            <p class="tw-text-lg tw-font-bold">
                <?php echo htmlspecialchars($enrollment['course_title']); ?>
            </p>
        </a>

        <!-- Progress Bar -->
        <div class="flexacademy-my-course-progress">
            <div class="flexacademy-progress-header">
                <span class="flexacademy-progress-label"><?php echo _flexacademy_lang('progress'); ?></span>
                <span class="flexacademy-progress-percentage"><?php echo $progress_percentage; ?>%</span>
            </div>
            <div class="flexacademy-progress-track">
                <div class="flexacademy-progress-bar-fill" style="width: <?php echo $progress_percentage; ?>%"></div>
            </div>
        </div>

        <!-- Action Button -->
        <div class="flexacademy-my-course-actions">
            <?php if ($payment_status === 'pending' && $invoice_id): ?>
                <?php 
                $this->load->model('invoices_model');
                $invoice = $this->invoices_model->get($invoice_id);
                $invoice_url = $invoice ? site_url('invoice/' . $invoice_id . '/' . $invoice->hash) : '#';
                ?>
                <a href="<?php echo $invoice_url; ?>" class="flexacademy-my-course-btn flexacademy-btn-warning">
                    <i class="fa fa-credit-card"></i> <?php echo _flexacademy_lang('pay-now'); ?>
                </a>
            <?php else: ?>
                <a href="<?php echo $player_url; ?>" class="btn btn-primary flexacademy-btn-primary btn-block mtop30 btn-lg">
                    <i class="fa fa-play"></i>
                    <?php 
                    if ($enrollment['status'] === 'completed') {
                        echo _flexacademy_lang('review-course');
                    } elseif ($progress_percentage > 0) {
                        echo _flexacademy_lang('continue-lesson');
                    } else {
                        echo _flexacademy_lang('start-now');
                    }
                    ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

