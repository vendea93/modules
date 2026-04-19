<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$progress = isset($enrollment->progress) ? (float) $enrollment->progress : 0;
$hasCertificate = !empty($certificate) || ($progress >= 100 && !empty($enrollment->certificate_id));
$certificateLink = $hasCertificate && !empty($certificate_url) ? $certificate_url : null;
?>

<div class="flexacademy-certificate-tab">
    <div class="flexacademy-certificate-progress-card">
        <div class="flexacademy-certificate-progress-header">
            <h4><?php echo _flexacademy_lang('course-progress'); ?></h4>
            <span class="flexacademy-certificate-progress-value"><?php echo round($progress); ?>%</span>
        </div>
        <div class="flexacademy-certificate-progress-bar">
            <div class="flexacademy-certificate-progress-fill" style="width: <?php echo min(100, round($progress)); ?>%"></div>
        </div>
    </div>

    <?php if (!$hasCertificate) { ?>
        <div class="flexacademy-certificate-message flexacademy-certificate-message-warning">
            <h5><?php echo _flexacademy_lang('certificate-in-progress-title'); ?></h5>
            <p><?php echo _flexacademy_lang('certificate-in-progress-body'); ?></p>
            <ul>
                <li><?php echo _flexacademy_lang('certificate-complete-lessons'); ?></li>
                <li><?php echo _flexacademy_lang('certificate-pass-quizzes'); ?></li>
            </ul>
        </div>
    <?php } else { ?>
        <div class="flexacademy-certificate-message flexacademy-certificate-message-success">
            <h5><?php echo _flexacademy_lang('certificate-congrats-title'); ?></h5>
            <p><?php echo _flexacademy_lang('certificate-congrats-body'); ?></p>
            <?php if ($certificateLink) { ?>
                <a href="<?php echo htmlspecialchars($certificateLink); ?>" class="btn btn-primary" target="_blank">
                    <i class="fa fa-certificate"></i> <?php echo _flexacademy_lang('view-certificate'); ?>
                </a>
            <?php } ?>
        </div>
    <?php } ?>
</div>
