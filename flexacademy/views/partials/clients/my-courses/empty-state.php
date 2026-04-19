<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="flexacademy-empty-state">
    <div class="tw-text-primary-600 tw-text-2xl">
        <i class="fa fa-book"></i>
    </div>
    <p class="tw-text-lg mbot30"><?php echo _flexacademy_lang('no-enrollments'); ?></p>
    <a href="<?php echo site_url('flexacademy/courses'); ?>" class="btn btn-primary flexacademy-btn-primary">
        <?php echo _flexacademy_lang('browse-courses'); ?>
    </a>
</div>

