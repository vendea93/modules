<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="row">
    <div class="col-md-12">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
            <h4 class="tw-my-0 tw-font-semibold tw-text-lg">
                <?php echo  _flexacademy_lang('edit-basic-info'); ?>
            </h4>
        </div>
    </div>
    </div>
    <div class="col-md-12">
    <?php $this->load->view('partials/course-form', [
        'course' => $course,
        'title' => '',
        'action' => admin_url('flexacademy/course/' . $course['id'])
    ]); ?>
</div>