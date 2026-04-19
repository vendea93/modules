<div id="curriculum" class="flexacademy-tabs-pane-active">
    <h4 class="tw-text-lg tw-font-bold tw-mb-3"><?php echo _flexacademy_lang('course-curriculum'); ?></h4>
    <?php if (!empty($sections)): ?>
    <div class="accordion mt-4 tw-gap-3" id="flexacademy-accordion">
        <?php foreach ($sections as $index => $section): ?>
            <div class="card mbot10">
                <button id="flexacademy-accordion-header" class="btn outline-0 tw-text-lg btn-default btn-block tw-flex tw-justify-between tw-items-center"
                    data-accordion="flexacademy-accordion-<?php echo $index; ?>">
                    <p class="tw-mb-0">
                        <span class="flexacademy-section-number tw-font-bold tw-text-primary tw-mr-2"><?php echo ($index + 1); ?>.</span>
                        <span class="tw-text-base tw-font-semibold tw-text-gray-800 tw-truncate"><?php echo $section['title']; ?></span>
                    </p>
                    <i class="fa fa-chevron-down tw-text-gray-500 flexacademy-accordion-icon tw-flex-shrink-0"></i>
                </button>
                <div class="flexacademy-accordion-content tw-hidden" id="flexacademy-accordion-<?php echo $index; ?>">
                    <?php if (!empty($section['lessons'])): ?>
                        <div class="p-3 pt-0">
                            <div>
                                <?php foreach ($section['lessons'] as $lesson): ?>
                                    <div class="tw-flex mbot10 tw-items-center tw-justify-between tw-py-2 tw-px-3 tw-bg-white tw-gap-2">
                                        <div class="tw-flex tw-items-center tw-gap-2 tw-flex-1">
                                            <i class="fa <?php echo flexacademy_lesson_type_icon($lesson) ?> tw-text-gray-600 tw-text-sm tw-flex-shrink-0"></i>
                                            <span class="tw-text-gray-800 tw-break-words"><?php echo $lesson["title"]; ?></span>
                                        </div>
                                        <div class="tw-flex tw-items-center tw-flex-shrink-0">
                                            <span class="tw-text-sm tw-text-gray-600 tw-font-medium tw-whitespace-nowrap">
                                                <?php echo flexacademy_convert_duration_from_minutes($lesson["duration"]); ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="tw-p-4 tw-text-center tw-text-gray-500">
                            <p class="tw-mb-0 tw-text-sm"><?php echo _flexacademy_lang('no-lessons-in-section'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</div>