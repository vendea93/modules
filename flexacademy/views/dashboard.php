<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-justify-between tw-items-center tw-mb-2 sm:tw-mb-4">
                    <h4 class="tw-my-0 tw-font-semibold tw-text-lg tw-self-end">
                        <?php echo $title; ?>
                    </h4>
                    <div>
                        <a href="<?php echo admin_url('flexacademy/course'); ?>" class="btn btn-primary mright5">
                            <i class="fa fa-plus"></i> <?php echo _flexacademy_lang('add-new-course'); ?>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <?php $this->load->view('flexacademy/partials/dashboard/top-stats', [
            'total_courses' => $total_courses,
            'total_active_courses' => $total_active_courses,
            'total_enrollments' => $total_enrollments,
            'total_certificates' => $total_certificates,
        ]); ?>

        <div class="row mtop20">
            <?php $this->load->view('flexacademy/partials/dashboard/recent-courses', [
                'recent_courses' => $recent_courses,
            ]); ?>
            <?php $this->load->view('flexacademy/partials/dashboard/recent-enrollments', [
                'recent_enrollments' => $recent_enrollments,
            ]); ?>
        </div>

        <div class="row mtop20">
            <?php $this->load->view('flexacademy/partials/dashboard/top-courses', [
                'top_courses' => $top_courses,
            ]); ?>
            <?php $this->load->view('flexacademy/partials/dashboard/completion-statistics', [
                'completion_stats' => $completion_stats,
            ]); ?>
        </div>
    </div>
</div>

<?php init_tail(); ?>
