<div class="modal fade" id="addInstructorModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title"><?php echo _flexacademy_lang('add-instructor'); ?></span>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
           <?php echo form_open_multipart(admin_url('flexacademy/add_edit_instructor'), ['id' => 'addInstructorForm', 'class' => 'form-horizontal tw-p-3']); ?>
           <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
           <input type="hidden" name="instructor_id" value="0">
                <div class="modal-body">
                    <div class="form-group">
                        <label for="instructor_name"><?php echo _flexacademy_lang('name'); ?> *</label>
                        <input type="text" class="form-control" id="instructor_name" name="name" required>
                    </div>
                    <div class="form-group">
                        <label for="instructor_email"><?php echo _flexacademy_lang('email'); ?> *</label>
                        <input type="email" class="form-control" id="instructor_email" name="email" required>
                    </div>
                    <div class="form-group">
                        <label for="instructor_job_title"><?php echo _flexacademy_lang('job_title'); ?> *</label>
                        <input type="text" class="form-control" id="instructor_job_title" name="job_title" required>
                    </div>
                    <div class="form-group">
                        <label for="instructor_bio"><?php echo _flexacademy_lang('bio'); ?> *</label>
                        <textarea class="form-control" id="instructor_bio" name="bio" required></textarea>
                    </div>
                    <div class="form-group">
                        <label for="instructor_image"><?php echo _flexacademy_lang('image'); ?></label>
                        <input type="file" class="form-control" accept="image/*" id="instructor_image" name="image">
                    </div>
                    <div class="form-group">
                        <label for="instructor_signature"><?php echo _flexacademy_lang('signature'); ?></label>
                        <input type="file" class="form-control" accept="image/*" id="instructor_signature" name="signature">
                        <p class="help-block tw-mt-2"><?php echo _flexacademy_lang('instructor_signature_help'); ?></p>
                        <div class="flexacademy-current-signature tw-mt-2" data-view-label="<?php echo _flexacademy_lang('view_signature'); ?>" data-prefix-label="<?php echo _flexacademy_lang('current_signature'); ?>"></div>
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
