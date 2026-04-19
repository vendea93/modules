<div class="modal fade" id="flexacademy-order-section-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php echo _flexacademy_lang('order-section'); ?></h4>
            </div>
            <div class="modal-body">
                <ul id="flexacademy-order-list" class="flexacademy-order-list" data-type="section" data-success="<?php echo _flexacademy_lang('order-success'); ?>">
                    <?php foreach ($course['sections'] as $section): ?>
                        <li class="flexacademy-order-item" data-id="<?php echo $section['id']; ?>"><span><i class="fa-solid fa-grip-vertical tw-mr-1"></i></span> <?php echo $section['title']; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <div class="modal-footer">
                <!--save changes button-->
                <a href="<?php echo admin_url('flexacademy/course_details/'.$course['id'].'?key=curriculum'); ?>" class="btn btn-primary" id="flexacademy-order-section-save"><?php echo _flexacademy_lang('save-changes'); ?></a>
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _flexacademy_lang('close'); ?></button>
            </div>
        </div>
    </div>
</div>