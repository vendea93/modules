<div class="section-item accordion-item" data-section-id="<?php echo $section['id']; ?>">
    <div class="section-header accordion-header" id="section-<?php echo $section['id']; ?>">
        <div class="section-info d-flex justify-content-between align-items-center w-100" data-toggle="collapse" data-target="#section-content-<?php echo $section['id']; ?>" aria-expanded="false" aria-controls="section-content-<?php echo $section['id']; ?>">
            <div class="section-title tw-m-0 d-flex align-items-center">
                <i class="fa-solid fa-chevron-down tw-mr-2 accordion-icon"></i>
                <?php echo $i; ?>. <?php echo $section['title']; ?>
                <span class="badge badge-secondary tw-ml-2"><?php echo count($section['lessons']); ?> <?php echo _flexacademy_lang('lessons'); ?></span>
            </div>
            <div class="section-actions">
                <a href="javascript:void(0)" class="btn btn-link btn-sm flexacademy-section-cta"
                    data-course-id="<?php echo $course['id']; ?>"
                    data-section-id="<?php echo $section['id']; ?>"
                    data-section-title="<?php echo $section['title']; ?>"
                    data-section-label="<?php echo _flexacademy_lang('edit-section'); ?>">
                    <i class="fa-solid fa-pencil"></i>
                </a>
                <a href="<?php echo admin_url('flexacademy/delete_section/' . $section['id']); ?>" class="btn btn-link btn-sm text-danger flexacademy-delete-section _delete" data-confirm="<?php echo _flexacademy_lang('confirm-delete-section'); ?>">
                    <i class="fa-solid fa-trash-can"></i>
                </a>
            </div>
        </div>
    </div>
    <div id="section-content-<?php echo $section['id']; ?>" class="section-content collapse flexacademy-section-content" aria-labelledby="section-<?php echo $section['id']; ?>">
        <div class="accordion-body">
            <?php if (!empty($section['lessons'])): ?>
                <div class="lessons-list">
                    <table class="table table-striped table-borderless">
                        <thead>
                            <tr>
                                <th><?php echo _flexacademy_lang('lesson'); ?></th>
                                <th><?php echo _flexacademy_lang('duration'); ?></th>
                                <th><?php echo _flexacademy_lang('type'); ?></th>
                                <th><?php echo _flexacademy_lang('actions'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($section['lessons'] as $lessonIndex => $lesson):
                            ?>
                                <tr>
                                    <td><?php echo $lesson['title']; ?></td>
                                    <td><?php echo $lesson['duration']; ?> <?php echo _flexacademy_lang('minutes'); ?></td>
                                    <td><?php if($lesson['lesson_type'] == 'file'){
                                        echo ucfirst($lesson['lesson_type']) . ' (' . ucfirst($lesson['file_source']) . ')';
                                    }else{
                                        echo ucfirst(_flexacademy_lang($lesson['lesson_type']));
                                    }
                                    ?></td>
                                    <td>
                                        <?php if($lesson['lesson_type'] == 'quiz'): 
                                            $quiz = flexacademy_get_quiz_by_id($lesson['quiz_id']);
                                            ?>
                                            <a href="javascript:void(0)" class="btn btn-link btn-sm flexacademy-quiz-results" title="<?php echo _flexacademy_lang('quiz-results'); ?>" data-quiz-id="<?php echo $lesson['quiz_id']; ?>">
                                                <i class="fa-solid fa-chart-line"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-link btn-sm flexacademy-quiz-questions" title="<?php echo _flexacademy_lang('quiz-questions'); ?>" data-quiz-id="<?php echo $lesson['quiz_id']; ?>">
                                                <i class="fa-solid fa-list-check"></i>
                                            </a>
                                            <a href="javascript:void(0)" class="btn btn-link btn-sm flexacademy-quiz-cta"
                                                data-course-id="<?php echo $course['id']; ?>"
                                                data-quiz-id="<?php echo $lesson['quiz_id']; ?>"
                                                data-quiz-title="<?php echo $quiz['title']; ?>"
                                                data-section-id="<?php echo $quiz['section_id']; ?>"
                                                data-quiz-total-marks="<?php echo $quiz['total_marks']; ?>"
                                                data-quiz-pass-marks="<?php echo $quiz['pass_marks']; ?>"
                                                data-quiz-retake-limit="<?php echo $quiz['retake_limit']; ?>"
                                                data-quiz-time-limit="<?php echo $quiz['time_limit']; ?>"
                                                data-quiz-description="<?php echo $quiz['description']; ?>"
                                                data-quiz-label="<?php echo _flexacademy_lang('edit-quiz'); ?>">
                                                <i class="fa-solid fa-pencil"></i>
                                            </a>
                                            <a href="<?php echo admin_url('flexacademy/delete_quiz/' . $lesson['quiz_id']); ?>" class="btn btn-link btn-sm text-danger flexacademy-delete-quiz _delete" data-confirm="<?php echo _flexacademy_lang('confirm-delete-quiz'); ?>">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </a>
                                        <?php else: ?>
                                        <a href="javascript:void(0)" class="btn btn-link btn-sm flexacademy-lesson-cta"
                                            data-course-id="<?php echo $course['id']; ?>"
                                            data-lesson-id="<?php echo $lesson['id']; ?>"
                                            data-lesson-title="<?php echo $lesson['title']; ?>"
                                            data-section-id="<?php echo $section['id']; ?>"
                                            data-duration="<?php echo $lesson['duration']; ?>"
                                            data-file-path="<?php echo $lesson['file_path']; ?>"
                                            data-lesson-type="<?php echo $lesson['lesson_type']; ?>"
                                            data-text-lesson="<?php echo $lesson['text_lesson']; ?>"
                                            data-summary="<?php echo $lesson['summary']; ?>"
                                            data-file-url="<?php echo $lesson['file_url']; ?>"
                                            data-file-source="<?php echo $lesson['file_source']; ?>"
                                            data-lesson-label="<?php echo _flexacademy_lang('edit-lesson'); ?>">
                                            <i class="fa-solid fa-pencil"></i>
                                        </a>
                                        <a href="<?php echo admin_url('flexacademy/delete_lesson/' . $lesson['id']); ?>" class="btn btn-link btn-sm text-danger flexacademy-delete-lesson _delete" data-confirm="<?php echo _flexacademy_lang('confirm-delete-lesson'); ?>">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center tw-py-4">
                    <i class="fa-solid fa-book-open tw-text-2xl tw-text-gray-400 tw-mb-2"></i>
                    <p class="tw-text-gray-500 tw-mb-0"><?php echo _flexacademy_lang('no-lessons-in-section'); ?></p>
                    <button type="button"
                        class="btn btn-outline-primary btn-sm tw-mt-2 flexacademy-add-lesson-to-section flexacademy-lesson-cta"
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
                        data-file-source="upload-file">
                        <i class="fa-solid fa-plus tw-mr-1"></i>
                        <?php echo _flexacademy_lang('add-lesson'); ?>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>