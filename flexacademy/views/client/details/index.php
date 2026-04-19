<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="tw-bg-gray-50 tw-min-h-screen">
<section class="container">
    <div class="row">
    <div>
        <a href="<?php echo site_url('flexacademy/courses'); ?>" class="btn btn-link tw-text-base">
            <i class="fa fa-arrow-left"></i>
            <?php echo _l('back'); ?>
        </a>
    </div>
    <section class="col-lg-8 col-md-7 tw-space-y-6 sm:tw-space-y-8 tw-rounded-lg tw-border tw-border-gray-100 tw-shadow-md tw-p-4 sm:tw-p-6">
        <div class="tw-mb-4">
            <h1 class="tw-mb-3 tw-font-semibold"> <?php echo $title; ?>
            </h1>
            <p class="tw-text-gray-700 tw-mb-4"> <?php 
            echo $course["description"];
            ?></p>
            <p class="tw-text-gray-700 tw-mb-4">
             </p>
            <?php $this->load->view('flexacademy/partials/clients/course-details/sub-header', ['course' => $course, 'instructors' => $instructors, 'enrollment_count' => $enrollment_count, 'totalDuration' => $totalDuration]); ?>
        </div>
        <div class="panel_s tw-p-4 mtop30">
            <div>
                <div id="flexacademy-tabs" class="nav nav-tabs customer-profile-tabs nav-tabs-horizontal">
                    <?php 
                    $menu = flexacademy_client_course_details_menu();
                    foreach($menu as $key => $item) {
                    ?>
                    <button
                        class="<?php 
                            if($key == 0) {
                                echo "flexacademy-tabs-active";
                            }
                        ?> btn tw-mr-3 btn-lg tw-bg-transparent"
                        data-tab="<?php echo $item["key"] ?>">
                        <?php echo $item["name"] ?></button>
                    <?php } ?>
                </div>
            </div>

            <div class="card-body tw-p-2 sm:tw-p-4 tw-px-3">
                <?php $this->load->view('flexacademy/partials/clients/course-details/section-lessons', ['sections' => $sections]); ?>
                    
                <?php $this->load->view('flexacademy/partials/clients/course-details/instructors', ['instructors' => $instructors]); ?>

                <?php $this->load->view('flexacademy/partials/clients/course-details/faq', ['faqs' => $faqs]); ?>

                <?php $this->load->view('flexacademy/partials/clients/course-details/info', ['requirements' => $requirements, 'outcomes' => $outcomes]); ?>
            </div>
        </div>

    </section>
    <div class="col-lg-4 col-md-5">
    <?php $this->load->view('flexacademy/partials/clients/course-details/right-hand-side', ['course' => $course, 'instructors' => $instructors, 'enrollment_count' => $enrollment_count, 'totalDuration' => $totalDuration]); ?>
    </div>
    </div>
</section>
</div>

<!-- Modals -->

<!-- Fixed Cart Icon -->
<?php $this->load->view('flexacademy/partials/cart-icon'); ?>
