<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper" class="customer_profile">
    <div class="content">
        <div class="row">
            <div class="col-md-3">
                <h4 class="tw-text-lg tw-font-semibold tw-text-neutral-800 tw-mt-0">
                    <div class="tw-space-x-3 tw-flex tw-items-center">
                        <span class="tw-truncate">
                            #
                            <?php echo $course['id'] ?> -
                            <?php echo $course['title'] ?>
                        </span>
                    </div>
                </h4>
            </div>
            <div class="col-md-9">
                <a href="<?php echo admin_url('flexacademy/courses') ?>" class="btn btn-link">
                    <i class="fa fa-circle-left fa-lg"></i>
                    <?php echo strtoupper(_l('flexacademy_back')); ?>
                </a>
                <div class="pull-right">
                    <!-- course player and course details link -->
                    <a href="<?php echo site_url('flexacademy/course/player/' . $course['slug']) ?>" class="btn btn-sm btn-secondary">
                        <i class="fa fa-play fa-lg"></i> &nbsp;
                        <?php echo _l('flexacademy_course_player'); ?>
                    </a>
                    <a href="<?php echo site_url('flexacademy/course/details/' . $course['slug']) ?>" class="btn btn-sm btn-secondary">
                        <i class="fa fa-book fa-lg"></i> &nbsp;
                        <?php echo _l('flexacademy_course_details'); ?>
                    </a>
                        
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="col-md-3">
                <?php $this->load->view('course/details/tabs', ['key' => $key]); ?>
            </div>
            <div class="tw-mt-12 sm:tw-mt-0 col-md-9">
                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo $content; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
</body>

</html>