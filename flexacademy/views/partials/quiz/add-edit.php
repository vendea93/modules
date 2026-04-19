<div class="modal fade" id="addQuizModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title"><?php echo _flexacademy_lang('add-quiz'); ?></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php echo form_open_multipart(admin_url('flexacademy/add_edit_quiz'), ['id' => 'addQuizForm']); ?>
                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                <input type="hidden" name="quiz_id" value="0">
                <div class="modal-body">
                    <?php echo render_input('title', _flexacademy_lang('quiz-title'), '', 'text', ['required' => true]); ?>
                    <?php echo render_select('section_id', $course['sections'], ['id', 'title'], _flexacademy_lang('section'), '', ['required' => true]); ?>
                    <?php echo render_input('total_marks', _flexacademy_lang('total-marks'), '', 'number', ['min' => 1, 'value' => 100]); ?>
                    <?php echo render_input('pass_marks', _flexacademy_lang('pass-marks'), '', 'number', ['min' => 1, 'value' => 50]); ?>
                    <?php echo render_input('retake_limit', _flexacademy_lang('retake-limit'), '', 'number', ['min' => 1, 'value' => 1]); ?>
                    <?php echo render_input('time_limit', _flexacademy_lang('time-limit-minutes'), '', 'number', ['min' => 1, 'value' => 10]); ?>
                    <?php echo render_textarea('description', _flexacademy_lang('description'), '', [], [], '', 'tinymce'); ?>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo _flexacademy_lang('close'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo _flexacademy_lang('save'); ?></button>
                </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>