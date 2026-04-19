<?php
/** @var object $section */
?>
<div class="row">
    <div class="col-md-12">
		<?php echo render_input('name', 'section_name', $section->name, 'text', ['required' => TRUE]); ?>
    </div>
    <div class="col-md-12">
		<?php echo render_textarea('description', 'description', $section->description); ?>
    </div>
    <div class="col-md-6">
		<?php echo render_input('display_order', 'display_order', $section->display_order, 'number'); ?>
    </div>
    <div class="col-md-6">
        <label for="active"><?php echo _l('Active') ?></label>
        <select name="active" data-live-search="true" id="active"
                class="form-control "
                data-none-selected-text="<?= _l('dropdown_non_selected_tex'); ?>">
            <option value="0" <?php echo($section->active ? 'selected' : '') ?>>
				<?= _l('active'); ?>
            </option>
            <option value="1"<?php echo($section->active ? '' : 'selected') ?>>
				<?= _l('inactive'); ?>
            </option>
        </select>
    </div>
</div>