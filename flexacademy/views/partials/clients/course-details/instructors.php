<div id="instructors" class="flexacademy-tabs-pane">
    <h4 class="tw-text-lg tw-font-bold tw-mb-4"><?php echo _flexacademy_lang('about-the-instructors'); ?></h4>
    <div class="tw-flex tw-flex-col tw-gap-3">
        <?php foreach ($instructors as $instructor) { ?>
            <div class="tw-flex tw-justify-between tw-items-center tw-gap-3">
                <div class="tw-flex tw-items-center tw-gap-2 tw-flex-1">
                    <div class="tw-rounded-full tw-overflow-hidden tw-bg-black tw-flex-shrink-0 tw-w-8 tw-h-8">
                        <img class="tw-w-full tw-h-full tw-object-cover"
                            src="<?php echo flexacademy_instructor_image($instructor) ?>" />
                    </div>
                    <div>
                        <p class="tw-mb-1 tw-truncate tw-font-medium tw-text-base"><?php echo $instructor["name"] ?></p>
                        <p class="tw-text-gray-600 tw-mb-0 tw-truncate tw-text-sm"><?php echo $instructor["job_title"] ?></p>
                    </div>
                </div>
                <a href="javascript:void(0);"
                    class="btn btn-link tw-flex-shrink-0 tw-whitespace-nowrap"
                    data-toggle="modal"
                    data-target="#flexacademy-instructor-details-<?php echo $instructor['id']; ?>">
                    <?php echo _flexacademy_lang('view-details'); ?>
                </a>
            </div>
            <?php $this->load->view('flexacademy/partials/clients/course-details/instructor-modal', [
                'instructor' => $instructor,
            ]); ?>
        <?php } ?>
    </div>
</div>