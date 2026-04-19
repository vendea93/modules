<?php
$course_title = (isset($course) && $course) ? $course['title'] : '';
$course_description = (isset($course) && $course) ? $course['description'] : '';
$course_short_description = (isset($course) && $course) ? $course['short_description'] : '';
$course_category_id = (isset($course) && $course) ? $course['category_id'] : '';
$course_language = (isset($course) && $course) ? $course['language'] : '';
$course_level = (isset($course) && $course) ? $course['difficulty_level'] : '';
$course_pricing_type = (isset($course) && $course) ? $course['pricing_type'] : '';
$course_price = (isset($course) && $course) ? $course['price'] : '';
$course_discount_price = (isset($course) && $course) ? $course['discount_price'] : '';
$course_privacy = (isset($course) && $course) ? $course['privacy'] : '';
$course_status = (isset($course) && $course) ? $course['status'] : '';
$course_access = (isset($course) && $course) ? $course['access'] : 'everyone';
$course_expiry_type = (isset($course) && $course) ? $course['expiry_type'] : '';
$course_expiry_value = (isset($course) && $course) ? $course['expiry_period'] : '';
$course_image = (isset($course) && $course) ? $course['image'] : '';
$action = (isset($action) && $action) ? $action : admin_url('flexacademy/course');
?>

<?= form_open_multipart($action, ['id' => 'flexacademy-course-form']); ?>
        <div class="">
            <?php if (isset($title) && $title): ?>
            <h4 class="tw-mt-0 tw-font-bold tw-text-lg tw-text-neutral-700">
                    <?php echo $title; ?>
                </h4>
            <?php endif; ?>
            <div class="panel_s">
                <div class="panel-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?php echo render_input('title', _flexacademy_lang('course-title'), $course_title); ?>
                            <?= render_textarea('short_description', _flexacademy_lang('short-description'), $course_short_description, [], [], '', ''); ?>
                            <?= render_textarea('description', _flexacademy_lang('description'), $course_description, [], [], '', 'tinymce'); ?>
                            <?php if ($course_image): ?>
                                <img src="<?php echo flexacademy_media_url($course_image); ?>" alt="Course Image" class="tw-w-full tw-h-20 flexacademy-course-image-form flexacademy-img-preview">
                            <?php endif; ?>
                            <?php echo render_input('image', _flexacademy_lang('course-image'), '', 'file', [], [], 'tw-mt-4', 'flexacademy-file-input-form'); ?>

                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="category_id"><?php echo _flexacademy_lang('course-category'); ?></label>
                                <select name="category_id" id="category_id" class="form-control">
                                    <?php foreach (flexacademy_get_parent_categories() as $category): ?>
                                        <option value="<?php echo $category['id']; ?>" <?php echo $category['id'] == $course_category_id ? 'selected' : ''; ?>><?php echo $category['title']; ?></option>
                                        <?php foreach (flexacademy_get_sub_categories($category['id']) as $sub_category): ?>
                                            <option value="<?php echo $sub_category['id']; ?>" <?php echo $sub_category['id'] == $course_category_id ? 'selected' : ''; ?>><?php echo " --" . $sub_category['title']; ?></option>
                                        <?php endforeach; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <?php echo render_select('language', flexacademy_get_course_languages(), ['id', 'name'], _flexacademy_lang('language'), $course_language); ?>
                            <?php echo render_select('difficulty_level', flexacademy_get_course_levels(), ['id', 'name'], _flexacademy_lang('course-level'), $course_level); ?>
                            <!-- status -->
                            <?php echo render_select('status', flexacademy_get_course_statuses(), ['id', 'name'], _flexacademy_lang('status'), $course_status); ?>
                            <?php echo render_select('pricing_type', flexacademy_get_course_pricing_types(), ['id', 'name'], _flexacademy_lang('pricing-type'), $course_pricing_type); ?>
                            <div class="form-group flexacademy-pricing-type-paid <?php echo $course_pricing_type == 'paid' ? 'active' : ''; ?>">
                                <?php echo render_input('price', _flexacademy_lang('price'), $course_price, 'number', ['min' => 0, 'placeholder' => '0.00', 'step' => '0.01']); ?>
                                <?php echo render_input('discount_price', _flexacademy_lang('discount-price'), $course_discount_price, 'number', ['min' => 0, 'placeholder' => '0.00', 'step' => '0.01']); ?>
                            </div>

                            <?php echo render_select('privacy', flexacademy_get_course_privacy(), ['id', 'name'], _flexacademy_lang('privacy'), $course_privacy); ?>
                            <?php echo render_select('access', flexacademy_get_course_access_types(), ['id', 'name'], _flexacademy_lang('course-access'), $course_access); ?>
                            <?php echo render_select('expiry_type', flexacademy_expiry_types(), ['id', 'name'], _flexacademy_lang('expiry-type'), $course_expiry_type); ?>
                            <div class="form-group flexacademy-expiry-type-value">
                                <?php echo render_input('expiry_value', _flexacademy_lang('expiry-value'), $course_expiry_value, 'number', ['min' => 0, 'placeholder' => '0', 'step' => '1']); ?>
                            </div>
                        </div>

                    </div>
                </div>
                <div class="panel-footer text-right">
                    <button type="submit" data-form="#flexacademy-course-form" class="btn btn-primary flexacademy-submit-course-button" autocomplete="off"
                        data-loading-text="<?= _flexacademy_lang('wait_text'); ?>">
                        <?= _flexacademy_lang('save'); ?>
                    </button>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>