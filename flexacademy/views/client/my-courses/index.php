<?php
defined('BASEPATH') or exit('No direct script access allowed');
?>

<div class="flexacademy-my-courses-container">
    <?php if (empty($enrollments)): ?>
        <?php $this->load->view('flexacademy/partials/clients/my-courses/empty-state'); ?>
    <?php else: ?>
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
            <p class="tw-text-lg tw-font-bold"><?php echo _flexacademy_lang('my-courses'); ?></p>
            <a href="<?php echo site_url('flexacademy/courses'); ?>" class="btn btn-primary flexacademy-btn-primary">
                <?php echo _flexacademy_lang('browse-courses'); ?>
            </a>
        </div>
        <div class="flexacademy-courses-grid">
            <?php foreach ($enrollments as $enrollment): ?>
                <?php $this->load->view('flexacademy/partials/clients/my-courses/enrollment-card', [
                    'enrollment' => $enrollment
                ]); ?>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modals: Unenroll removed by requirement -->

