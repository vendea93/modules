<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php
$courses              = $enrollment_courses ?? [];
$students             = $enrollment_students ?? [];
$course_none_selected = _flexacademy_lang('select-course');
$student_none_selected = _flexacademy_lang('select-student');
$modal_disabled       = empty($courses) || empty($students);
?>

<div class="modal fade" id="flexacademyEnrollStudentModal" tabindex="-1" role="dialog" aria-labelledby="flexacademyEnrollStudentModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title" id="flexacademyEnrollStudentModalLabel">
                    <?php echo _flexacademy_lang('enroll-student'); ?>
                </h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="<?php echo _flexacademy_lang('close'); ?>">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <?php echo form_open(admin_url('flexacademy/enroll_student'), ['id' => 'flexacademy-enroll-student-form']); ?>
            <div class="modal-body">
                <?php if ($modal_disabled) { ?>
                    <div class="alert alert-info">
                        <i class="fa fa-info-circle"></i>
                        <?php echo _flexacademy_lang('enroll-student-modal-empty'); ?>
                    </div>
                <?php } ?>

                <?php echo render_select(
                    'course_id',
                    $courses,
                    ['id', 'name'],
                    _flexacademy_lang('select-course'),
                    '',
                    [
                        'required' => true,
                        'data-none-selected-text' => $course_none_selected,
                    ]
                ); ?>

                <?php echo render_select(
                    'student_reference',
                    $students,
                    ['id', 'name'],
                    _flexacademy_lang('select-student'),
                    '',
                    [
                        'required' => true,
                        'data-none-selected-text' => $student_none_selected,
                    ]
                ); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">
                    <?php echo _flexacademy_lang('close'); ?>
                </button>
                <button type="submit" class="btn btn-primary" <?php echo $modal_disabled ? 'disabled' : ''; ?>>
                    <i class="fa fa-check"></i>
                    <?php echo _flexacademy_lang('enroll-submit'); ?>
                </button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>

