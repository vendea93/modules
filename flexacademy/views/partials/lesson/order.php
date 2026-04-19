<div class="modal fade" id="flexacademy-order-lesson-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo _flexacademy_lang('order-lesson'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="flexacademy-order-list"><?php echo _flexacademy_lang('choose-section'); ?></label>
                    <select id="flexacademy-lesson-section-order-list" class="form-control" data-type="section" data-success="<?php echo _flexacademy_lang('order-success'); ?>">
                        <option value=""><?php echo _flexacademy_lang('select-section'); ?></option>
                        <?php foreach ($course['sections'] as $section): ?>
                            <option value="<?php echo $section['id']; ?>"><?php echo $section['title']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flexacademy-lesson-order-list-container">

                </div>

            </div>
            <div class="modal-footer">
                <!--save changes button-->
                <a href="<?php echo admin_url('flexacademy/course_details/' . $course['id'] . '?key=curriculum'); ?>" class="btn btn-primary" id="flexacademy-order-section-save"><?php echo _flexacademy_lang('save-changes'); ?></a>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _flexacademy_lang('close'); ?></button>
            </div>
        </div>
    </div>
</div>