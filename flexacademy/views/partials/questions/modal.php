<div class="modal fade" id="flexacademy-quiz-questions-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title"><?php echo _flexacademy_lang('quiz-questions'); ?></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="flexacademy-quiz-questions-add-question-container">
                    <a href="javascript:void(0)"
                    data-question-id="0"
                    data-question-type=""
                    data-question=""
                    data-correct-answer=""
                    data-options=""
                    data-action="add"
                    class="btn btn-primary flexacademy-quiz-questions-cta"><i class="fa-solid fa-plus"></i> <?php echo _flexacademy_lang('add-question'); ?></a>
                </div>
                <div class="flexacademy-quiz-questions-add-question-form">
                    <?php echo form_open(admin_url('flexacademy/add_edit_quiz_question'), ['id' => 'add-edit-question-form', 'class' => 'flexacademy-quiz-questions-form']); ?>
                    <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                    <input type="hidden" name="quiz_id" value="0">
                    <input type="hidden" name="question_id" value="0">
                    <?php echo render_select('question_type', flexacademy_quiz_question_types(), ['id', 'name'], _flexacademy_lang('question-type'), '',); ?>
                    <?php echo render_textarea('question', _flexacademy_lang('question'), '', [], [], '', 'tinymce'); ?>
                    <div class="flexacademy-quiz-true-false-container" style="display: none;">
                        <?php echo render_select('correct_answer_true_false', [['id' => 1, 'name' => _flexacademy_lang('true')], ['id' => 0, 'name' => _flexacademy_lang('false')]], ['id', 'name'], _flexacademy_lang('correct-answer'), ''); ?>
                    </div>
                    <div class="flexacademy-quiz-choice-container" style="display: none;">
                        <?php echo render_input('options[]', _flexacademy_lang('options'), '', 'text', ['placeholder' => _flexacademy_lang('choice')], [], '', 'flexacademy-tagsinput'); ?>
                        <?php echo render_input('correct_answer_choice[]', _flexacademy_lang('correct-answer-choice'), '', 'text', [ 'placeholder' => _flexacademy_lang('correct-answer')], [], '', 'flexacademy-tagsinput'); ?>
                    </div>
                    <div class="flexacademy-quiz-fill-in-the-blank-container" style="display: none;">
                        <?php echo render_input('correct_answer', _flexacademy_lang('correct-answer'), '', 'text', [ 'placeholder' => _flexacademy_lang('correct-answer')], [], '', ''); ?>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo _flexacademy_lang('save'); ?></button>
                    <?php echo form_close(); ?>
                </div>
                <hr>
                
                <!-- questions list -->
                <h4><?php echo _flexacademy_lang('quiz-questions'); ?></h4>
                <div class="flexacademy-quiz-questions-list">
                    <?php echo $this->load->view('partials/questions/question-list', []); ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo _flexacademy_lang('close'); ?></button>
            </div>
        </div>
    </div>
</div>
</div>