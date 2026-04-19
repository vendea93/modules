<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php 
$faq = ($course['faq']) ? flexacademyPerfectUnserialize($course['faq']) : [];
$faq_questions = $faq['question'] ?? [];
$faq_answers = $faq['answer'] ?? [];
$requirements = ($course['requirements']) ? flexacademyPerfectUnserialize($course['requirements']) : [];
$outcomes = ($course['outcomes']) ? flexacademyPerfectUnserialize($course['outcomes']) : [];
?>
<input type="hidden" id="flexacademy_ajax_url" value="<?php echo admin_url('flexacademy/ajax'); ?>">
<?php echo form_open(admin_url('flexacademy/course_info'), ['id' => 'update-course-details-form']); ?>
<input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
<div class="row">
    <div class="col-md-12">
        <div class="tw-flex tw-justify-between tw-items-center tw-mb-4">
            <h4 class="tw-my-0 tw-font-semibold tw-text-lg">
                <?php echo _flexacademy_lang('info'); ?>
            </h4>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <h5 class="text-right">
                <?php echo _flexacademy_lang('faq'); ?>
            </h5>
        </div>
        <div class="col-md-6 flexacademy-faq-container">
            <div class="tw-mb-4 flexacademy-faq-container-each">
                <?php echo render_input('question[]', _flexacademy_lang('faq-question'), isset($faq_questions[0]) ? $faq_questions[0] : ""); ?>
                <?php echo render_textarea('answer[]', _flexacademy_lang('faq-answer'), isset($faq_answers[0]) ? $faq_answers[0] : "", [], [], "", ""); ?>
            </div>
            <?php for($i = 1; $i < count($faq_questions); $i++): ?>
                <div class="tw-mb-4 flexacademy-faq-container-each">
                    <?php echo render_input('question[]',"", $faq_questions[$i]); ?>
                    <?php echo render_textarea('answer[]',"", $faq_answers[$i], [], [], "", ""); ?>
                    <a href="javascript:void(0)" class="btn btn-sm btn-secondary flexacademy-delete-field-cta" data-field="faq">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </div>
            <?php endfor; ?>
        </div>
        <div class="col-md-3">

            <a href="javascript:void(0)" class="btn btn-secondary flexacademy-add-field-cta" 
            data-label="<?php echo _flexacademy_lang('faq-question'); ?>"
            data-label2="<?php echo _flexacademy_lang('faq-answer'); ?>"
            data-field="faq">
             <i class="fa-solid fa-plus"></i>
            </a>
        </div>
    </div>
    <!-- requirements -->
    <div class="row">
        <div class="col-md-3">
            <h5 class="text-right">
                <?php echo _flexacademy_lang('requirements'); ?>
            </h5>
        </div>
        <div class="col-md-6 flexacademy-requirements-container">
            <div class="tw-mb-4 flexacademy-requirements-container-each">
                <?php echo render_input('requirements[]',"", isset($requirements[0]) ? $requirements[0] : ""); ?>
            </div>
            <?php for($i = 1; $i < count($requirements); $i++): ?>
                <div class="tw-mb-4 flexacademy-requirements-container-each">
                    <?php echo render_input('requirements[]', "", $requirements[$i]); ?>
                    <a href="javascript:void(0)" class="btn btn-sm btn-secondary flexacademy-delete-field-cta" data-field="requirements">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </div>
            <?php endfor; ?>
        </div>
        <div class="col-md-3">
            <a href="javascript:void(0)" 
            class="btn btn-secondary flexacademy-add-field-cta" data-field="requirements" 
            data-label="<?php echo _flexacademy_lang('requirements'); ?>">
             <i class="fa-solid fa-plus"></i>
            </a>
        </div>
    </div>
    <!-- outcomes -->
    <div class="row">
        <div class="col-md-3">
            <h5 class="text-right">
                <?php echo _flexacademy_lang('outcomes'); ?>
            </h5>
        </div>
        <div class="col-md-6 flexacademy-outcomes-container">
            <div class="tw-mb-4 flexacademy-outcomes-container-each">
               <?php echo render_input('outcomes[]',"", isset($outcomes[0]) ? $outcomes[0] : ""); ?>
            </div>
            <?php for($i = 1; $i < count($outcomes); $i++): ?>
                <div class="tw-mb-4 flexacademy-outcomes-container-each">
                    <?php echo render_input('outcomes[]', "", $outcomes[$i]); ?>
                    <a href="javascript:void(0)" class="btn btn-sm btn-secondary flexacademy-delete-field-cta" data-field="outcomes">
                        <i class="fa-solid fa-trash"></i>
                    </a>
                </div>
            <?php endfor; ?>
        </div>
        <div class="col-md-3">
            <a href="javascript:void(0)" class="btn btn-secondary flexacademy-add-field-cta" 
            data-field="outcomes" 
            data-label="<?php echo _flexacademy_lang('outcomes'); ?>">
             <i class="fa-solid fa-plus"></i>
            </a>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 text-center">
            <button type="submit" class="btn btn-primary"><?php echo _flexacademy_lang('save-changes'); ?></button>
        </div>
    </div>
</div>
<?php echo form_close(); ?>