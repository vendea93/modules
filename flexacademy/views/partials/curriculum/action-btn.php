<div class="curriculum-actions tw-mb-6">
            <button type="button" class="btn btn-outline-secondary flexacademy-section-cta"
            data-course-id="<?php echo $course['id']; ?>"
            data-section-id="0"
            data-section-title=""
            data-section-label="<?php echo _flexacademy_lang('add-section'); ?>"
            >
                <i class="fa-solid fa-plus tw-mr-1"></i>
                <?php echo _flexacademy_lang('add-section'); ?>
            </button>
            <button
            data-course-id="<?php echo $course['id']; ?>"
            data-lesson-id="0"
            data-lesson-title=""
            data-section-id="0"
            data-duration="0"
            data-file-path=""
            data-lesson-type="file"
            data-text-lesson=""
            data-summary=""
            data-file-url=""
            data-file-source="upload-file"
            data-lesson-label="<?php echo _flexacademy_lang('add-lesson'); ?>"
            type="button" class="btn btn-outline-secondary flexacademy-lesson-cta" data-toggle="modal" data-target="#addLessonModal">
                <i class="fa-solid fa-plus tw-mr-1"></i>
                <?php echo _flexacademy_lang('add-lesson'); ?>
            </button>
            <button type="button" class="btn btn-outline-secondary flexacademy-quiz-cta"
            data-course-id="<?php echo $course['id']; ?>"
            data-quiz-id="0"
            data-quiz-title=""
            data-section-id="0"
            data-quiz-total-marks="100"
            data-quiz-pass-marks="50"
            data-quiz-retake-limit="1"
            data-quiz-time-limit="10"
            data-quiz-description=""
            data-quiz-label="<?php echo _flexacademy_lang('add-quiz'); ?>"
            >
                <i class="fa-solid fa-plus tw-mr-1"></i>
                <?php echo _flexacademy_lang('add-quiz'); ?>
            </button>
            <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#flexacademy-order-section-modal">
                <i class="fa-solid fa-sort tw-mr-1"></i>
                <?php echo _flexacademy_lang('sort-section'); ?>
            </button>
            <!--sort lesson-->
            <button type="button" class="btn btn-outline-secondary" data-toggle="modal" data-target="#flexacademy-order-lesson-modal">
                <i class="fa-solid fa-sort tw-mr-1"></i>
                <?php echo _flexacademy_lang('sort-lesson'); ?>
            </button>
        </div>