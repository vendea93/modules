<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="tw-flex tw-items-center tw-justify-between tw-mb-4">
                    <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-text-neutral-700">
                        <i class="fa fa-folder tw-mr-2"></i><?php echo $title; ?>
                    </h4>
                    <a href="<?php echo admin_url('poly_utilities/task_templates'); ?>" class="btn btn-default">
                        <i class="fa fa-arrow-left tw-mr-1"></i><?php echo _l('back'); ?>
                    </a>
                </div>

                <div class="alert alert-info tw-mb-4">
                    <i class="fa fa-info-circle tw-mr-2"></i>
                    <strong><?php echo _l('poly_utilities_task_template_category_info_title'); ?></strong>
                    <p class="tw-mb-0 tw-mt-2"><?php echo _l('poly_utilities_task_template_category_info_description'); ?></p>
                </div>

                <div class="panel_s">
                    <div class="panel-body">
                        <?php echo form_open(admin_url('poly_utilities/task_templates/category' . (!empty($category->id) ? '/' . $category->id : '')), ['id' => 'category-form']); ?>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <?php 
                                $name_value = isset($category->name) ? $category->name : '';
                                echo render_input('name', 'name', $name_value, 'text', ['required' => true]); 
                                ?>
                            </div>
                            <div class="col-md-6">
                                <?php 
                                $color_value = isset($category->color) ? $category->color : '#3498db';
                                echo render_input('color', _l('poly_utilities_color'), $color_value, 'color'); 
                                ?>
                            </div>
                        </div>

                        <div class="form-group">
                            <?php 
                            $description_value = isset($category->description) ? $category->description : '';
                            echo render_textarea('description', _l('poly_utilities_description'), $description_value, ['rows' => 3]); 
                            ?>
                        </div>

                        <div class="form-group">
                            <label class="control-label"><?php echo _l('poly_utilities_active'); ?></label>
                            <div class="onoffswitch">
                                <input type="checkbox" 
                                       name="active" 
                                       id="active" 
                                       class="onoffswitch-checkbox" 
                                       value="1"
                                       <?php echo (!isset($category) || (isset($category->active) && $category->active == 1)) ? 'checked' : ''; ?>>
                                <label class="onoffswitch-label" for="active"></label>
                            </div>
                        </div>

                        <div class="tw-mt-4">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-save tw-mr-1"></i><?php echo _l('submit'); ?>
                            </button>
                            <a href="<?php echo admin_url('poly_utilities/task_templates'); ?>" class="btn btn-default">
                                <?php echo _l('cancel'); ?>
                            </a>
                        </div>

                        <?php echo form_close(); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>

