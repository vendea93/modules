<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/** @var array $parent_categories */

?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
							<?php echo isset($category) ? _l('edit_category') : _l('add_new_category'); ?>
                        </h4>
                        <hr class="hr-panel-heading"/>

						<?php echo form_open(admin_url('catering_management_module/categories/category'.(isset($category) ? '/'.$category->id : '')), ['id' => 'category_form']); ?>

						<?php echo render_input('name', 'category_name', isset($category) ? $category->name : '', 'text', ['required' => TRUE]); ?>

                        <div class="row">
                            <div class="col-md-6">
								<?php
								$selected_parent = isset($category) ? $category->parent_id : '';
								$parent_options = [['id' => '', 'name' => _l('none')]];
								foreach ($parent_categories as $parent)
								{
									if ( ! isset($category) || $parent['id'] != $category->id)
									{
										$parent_options[] = ['id' => $parent['id'], 'name' => $parent['name']];
									}
								}
								echo render_select('parent_id', $parent_options, ['id', 'name'], 'parent_category', $selected_parent);
								?>
                            </div>
                            <div class="col-md-6">
								<?php echo render_input('display_order', 'display_order', isset($category) ? $category->display_order : '0', 'number', ['min' => '0']); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
								<?php echo render_input('icon', 'icon_class', isset($category) ? $category->icon : '', 'text', ['placeholder' => 'fa fa-cutlery']); ?>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="color"><?php echo _l('color'); ?></label>
                                    <input type="color" name="color" id="color" class="form-control" value="<?php echo isset($category) ? $category->color : '#3498db'; ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="active"><?php echo _l('status'); ?></label>
                            <select name="active" id="active" class="selectpicker" data-width="100%">
                                <option value="1" <?php echo (isset($category) && $category->active == 1) || ! isset($category) ? 'selected' : ''; ?>>
									<?php echo _l('active'); ?>
                                </option>
                                <option value="0" <?php echo (isset($category) && $category->active == 0) ? 'selected' : ''; ?>>
									<?php echo _l('inactive'); ?>
                                </option>
                            </select>
                        </div>

                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary">
                                <i class="fa fa-check"></i>
								<?php echo _l('submit'); ?>
                            </button>
                            <a href="<?php echo admin_url('catering/categories'); ?>" class="btn btn-default">
                                <i class="fa fa-times"></i>
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
<script>
    $(function () {
        appValidateForm($('#category_form'), {
            name: 'required'
        });
    });
</script>
</body>
</html>