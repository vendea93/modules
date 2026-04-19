<div class="modal fade" id="addSectionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title"><?php echo _flexacademy_lang('add-section'); ?></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
           <?php echo form_open(admin_url('flexacademy/add_edit_section'), ['id' => 'addSectionForm', 'class' => 'form-horizontal tw-p-3']); ?>
           <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
           <input type="hidden" name="section_id" value="0">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="section_title"><?php echo _flexacademy_lang('section-title'); ?> *</label>
                        <input type="text" class="form-control" id="section_title" name="title" required>
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
