<div class="row tw-items-center tw-mt-6">
    <div class="col-md-4 col-6 mb-2">
        <div class="tw-flex tw-items-center tw-gap-2">
            <div class="rounded-circle tw-overflow-hidden tw-bg-white tw-w-6 tw-h-6 tw-flex-shrink-0">
                <img class="tw-w-full tw-h-full tw-object-fit-cover"
                    src="<?php echo ($instructors) ? flexacademy_instructor_image($instructors[0]) : '' ?>" />
            </div>
            <p class="tw-mb-0 hover:tw-text-primary tw-cursor-pointer tw-truncate tw-font-medium">
                <?php echo ($instructors) ?  $instructors[0]["name"] . ' (+' . count($instructors) - 1 . ')' : '' ?></p>
        </div>
    </div>
    <div class="col-md-4 col-6 mb-2">
        <p class="tw-mb-0">
            <i class="fa fa-chart-simple tw-mr-1"></i>
            <?php echo flexacademy_get_course_levels($course["difficulty_level"]) ?>
        </p>
    </div>
    <div class="col-md-4 col-6 tw-mb-2">
        <p class="tw-mb-0">
            <i class="fa fa-language tw-mr-1"></i>
            <?php echo flexacademy_get_course_languages($course['language']); ?>
        </p>
    </div>
    <div class="col-md-4 col-6 mb-2">
        <p class="tw-mb-0">
            <i class="fa fa-graduation-cap tw-mr-1"></i>
            <?php echo _flexacademy_lang('course_certificate') ?>
        </p>
    </div>
    <div class="col-md-4 col-6 mb-2">
        <p class="tw-mb-0">
            <i class="fa fa-users tw-mr-1"></i>
            <?php echo $enrollment_count; ?> <?php echo _flexacademy_lang('enrolled_students') ?>
        </p>
    </div>
    <div class="col-md-4 col-6 mb-2">
        <p class="tw-mb-0">
            <i class="fa fa-clock tw-mr-1"></i>
            <?php echo flexacademy_convert_duration_from_minutes($totalDuration) ?>
        </p>
    </div>
</div>