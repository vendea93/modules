<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<input type="hidden" id="flexacademy_ajax_url" value="<?php echo admin_url('flexacademy/ajax'); ?>">
<div class="row">
    <div class="col-md-12">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
            <h4 class="tw-my-0 tw-font-semibold tw-text-lg">
                <?php echo _flexacademy_lang('curriculum'); ?>
            </h4>
        </div>
        
        <!-- Action Buttons -->
        <?php $this->load->view('partials/curriculum/action-btn', ['course' => $course]); ?>
        
        <div class="">
            <div class="">
                <div class="flexacademy-curriculum-container">
                    <?php if (isset($course['sections']) && !empty($course['sections'])): ?>
                        <?php $i = 1; ?>
                        <div class="sections-list" id="sortable-sections">
                            <?php foreach ($course['sections'] as $sectionIndex => $section): ?>
                                <?php $this->load->view('partials/curriculum/section-list', ['course' => $course, 'section' => $section, 'sectionIndex' => $sectionIndex, 'i' => $i]); ?>
                            <?php $i++;endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center tw-py-8">
                            <i class="fa-solid fa-book tw-text-4xl tw-text-gray-400 tw-mb-4"></i>
                            <h5 class="tw-text-gray-500"><?php echo _flexacademy_lang('no-sections-yet'); ?></h5>
                            <p class="tw-text-gray-400"><?php echo _flexacademy_lang('add-your-first-section'); ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $this->load->view('partials/section/add-edit-form', ['course' => $course]); ?>
<?php $this->load->view('partials/lesson/add-edit-form', ['course' => $course]); ?>
<?php $this->load->view('partials/questions/modal', ['course' => $course]); ?>

<?php $this->load->view('partials/section/order', ['course' => $course]); ?>

<?php $this->load->view('partials/lesson/order', ['course' => $course]); ?>
<?php $this->load->view('partials/quiz/add-edit', ['course' => $course]); ?>

