<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div id="flexacademy-certificate-<?php echo htmlspecialchars($certificate_number); ?>" class="flexacademy-certificate-page">
    <div class="flexacademy-certificate-frame">
        <div class="flexacademy-certificate-border">
            <div class="flexacademy-certificate-body">
                <div class="flexacademy-certificate-title"><?php echo strtoupper(_flexacademy_lang('certificate')); ?></div>
                <div class="flexacademy-certificate-subtitle"><?php echo strtoupper(_flexacademy_lang('of-completion')); ?></div>

                <div class="flexacademy-certificate-meta">
                    <div>
                        <span><?php echo _flexacademy_lang('language'); ?></span>
                        <strong><?php echo htmlspecialchars($course_language ?: '-'); ?></strong>
                    </div>
                    <div class="flexacademy-certificate-qr">
                        <?php if ($qr_code_url): ?>
                            <img src="<?php echo htmlspecialchars($qr_code_url); ?>" alt="QR Code">
                        <?php endif; ?>
                        <div class="flexacademy-certificate-qr-number">
                            <?php echo htmlspecialchars($certificate_number); ?>
                        </div>
                    </div>
                    <div class="flexacademy-certificate-date">
                        <span><?php echo _flexacademy_lang('date'); ?></span>
                        <strong><?php echo htmlspecialchars($issue_date_formatted); ?></strong>
                    </div>
                </div>

                <div class="flexacademy-certificate-recipient">
                    <?php echo _flexacademy_lang('certificate-intro'); ?><br>
                    <strong><?php echo htmlspecialchars($student_name); ?></strong>
                    <?php echo _flexacademy_lang('certificate-body'); ?>
                </div>

                <div class="flexacademy-certificate-course">
                    <?php echo _flexacademy_lang('for-course'); ?> <strong><?php echo htmlspecialchars($course['title']); ?></strong>
                </div>

                <div class="flexacademy-certificate-stats">
                    <div>
                        <span><?php echo _flexacademy_lang('total-lessons'); ?></span>
                        <strong><?php echo (int) $total_lessons; ?></strong>
                    </div>
                    <div>
                        <span><?php echo _flexacademy_lang('total-duration'); ?></span>
                        <strong><?php echo htmlspecialchars($total_duration_formatted); ?></strong>
                    </div>
                    <div>
                        <span><?php echo _flexacademy_lang('course-level'); ?></span>
                        <strong><?php echo htmlspecialchars($course_level ?: '-'); ?></strong>
                    </div>
                </div>

                <div class="flexacademy-certificate-seal">
                    <img src="<?php echo base_url('modules/flexacademy/assets/images/certificate-seal.png'); ?>" alt="Seal"/>
                </div>

                <div class="flexacademy-certificate-signatures">
                    <div class="flexacademy-certificate-signature <?php echo !empty($primary_instructor_signature_url) ? 'has-image' : ''; ?>">
                        <?php if (!empty($primary_instructor_signature_url)) : ?>
                            <img src="<?php echo htmlspecialchars($primary_instructor_signature_url); ?>" alt="<?php echo htmlspecialchars($primary_instructor ?: _flexacademy_lang('instructor')); ?>">
                        <?php endif; ?>
                        <div class="line"></div>
                        <div><?php echo htmlspecialchars($primary_instructor ?: _flexacademy_lang('instructor')); ?></div>
                        <small><?php echo _flexacademy_lang('lead-instructor'); ?></small>
                    </div>
                    <div class="flexacademy-certificate-signature <?php echo !empty($issuer_signature_url) ? 'has-image' : ''; ?>">
                        <?php if (!empty($issuer_signature_url)) : ?>
                            <img src="<?php echo htmlspecialchars($issuer_signature_url); ?>" alt="<?php echo htmlspecialchars($company_name); ?>">
                        <?php endif; ?>
                        <div class="line"></div>
                        <div><?php echo htmlspecialchars($company_name); ?></div>
                        <small><?php echo _flexacademy_lang('certificate-issuer'); ?></small>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <button
            class="btn flexacademy-certificate-download-button btn-primary btn-lg mtop30"
            data-success-message="<?php echo _flexacademy_lang('certificate-downloaded-successfully'); ?>"
            data-error-message="<?php echo _flexacademy_lang('error-downloading-certificate'); ?>"
            data-certificate-target="#flexacademy-certificate-<?php echo htmlspecialchars($certificate_number); ?>"
            data-certificate-prefix="<?php echo htmlspecialchars(($certificate_prefix ?? 'certificate') . '-' . $certificate_number); ?>"
            >
            <?php echo _flexacademy_lang('download'); ?>
        </button>
</div>

