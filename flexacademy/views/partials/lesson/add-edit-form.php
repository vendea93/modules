<div class="modal fade" id="addLessonModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title"><?php echo _flexacademy_lang('add-lesson'); ?></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php echo form_open_multipart(admin_url('flexacademy/add_edit_lesson'), ['id' => 'addEditLessonForm']); ?>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>" />
                        <input type="hidden" name="lesson_id" value="0" />
                        <?php echo render_input('title', _flexacademy_lang('lesson-title'), '', 'text', ['required' => true]); ?>
                        <?php echo render_select('section_id', $course['sections'], ['id', 'title'], _flexacademy_lang('section'), '', ['required' => true]); ?>
                        <?php echo render_input('duration', _flexacademy_lang('duration-minutes'), '', 'number', ['min' => 1, 'step' => 1, 'placeholder' => '0']); ?>

                        <?php echo render_select('lesson_type', flexacademy_get_lesson_types(), ['id', 'name'], _flexacademy_lang('lesson-type'), 'file', ['required' => true]); ?>
                        <div class="flexacademy-file-source-container">
                            <?php echo render_select('file_source', flexacademy_get_file_sources(), ['id', 'name'], _flexacademy_lang('file-source'), ''); ?>
                            <?php //echo render_select('file_type', flexacademy_file_types(), ['id', 'name'], _flexacademy_lang('file-type'), '', ['required' => true]); ?>
                            <div class="flexacademy-file-url-container" style="display: none;">
                                <?php echo render_input('file_url', _flexacademy_lang('source-url'), '', 'text', ['placeholder' => '']); ?>
                            </div>
                            <div class="flexacademy-file-upload-container" style="display: none;">
                                <?php echo render_input('file', _flexacademy_lang('file'), '', 'file', []); ?>
                            </div>
                        </div>
                        <div class="flexacademy-text-container" style="display: none;">
                            <?php echo render_textarea('text_lesson', _flexacademy_lang('text-lesson-content'), '', [], [], '', 'tinymce'); ?>
                        </div>
                       
                        <?= render_textarea('summary', _flexacademy_lang('summary'), '', [], [], '', 'tinymce'); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo _flexacademy_lang('close'); ?></button>
                <button type="submit" class="btn btn-primary"><?php echo _flexacademy_lang('save'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>