<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php

/** @var array $categories */
/** @var array $dietary_types */
/** @var array $allergens */
?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
							<?php echo isset($item) ? _l('edit_menu_item') : _l('add_new_menu_item'); ?>
                        </h4>
                        <hr class="hr-panel-heading"/>

						<?php echo form_open(admin_url('catering_management_module/menu_items/item'.(isset($item) ? '/'.$item->id : '')), ['id' => 'menu_item_form']); ?>

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Basic Information -->
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <h4 class="bold"><?php echo _l('basic_information'); ?></h4>

										<?php echo render_input('item_name', 'menu_item_name', isset($item) ? $item->item_name : '', 'text', ['required' => TRUE]); ?>

                                        <div class="row">
                                            <div class="col-md-6">
												<?php
												$selected_category = isset($item) ? $item->category_id : '';
												$category_options = [];
												foreach ($categories as $cat)
												{
													$category_options[] = [
														'id' => $cat['id'],
														'name' => $cat['name'],
													];
												}
												echo render_select('category_id', $category_options, ['id', 'name'], 'category', $selected_category, ['required' => TRUE]);
												?>
                                            </div>
                                            <div class="col-md-6">
												<?php echo render_input('default_portion_size', 'portion_size', isset($item) ? $item->default_portion_size : 'per person'); ?>
                                            </div>
                                        </div>

										<?php echo render_textarea('description', 'description', isset($item) ? $item->description : '', ['rows' => 4]); ?>

                                        <div class="row">
                                            <div class="col-md-4">
												<?php echo render_input('unit_cost', 'unit_cost', isset($item) ? $item->unit_cost : '0.00', 'number', ['step' => '0.01', 'min' => '0', 'required' => TRUE]); ?>
                                            </div>
                                            <div class="col-md-4">
												<?php echo render_input('unit_price', 'unit_price', isset($item) ? $item->unit_price : '0.00', 'number', ['step' => '0.01', 'min' => '0', 'required' => TRUE]); ?>
                                            </div>
                                            <div class="col-md-4">
												<?php echo render_input('prep_time_minutes', 'prep_time_minutes', isset($item) ? $item->prep_time_minutes : '', 'number', ['min' => '0']); ?>
                                            </div>
                                        </div>

										<?php if (catering_can_view_costs() && isset($item)) { ?>
                                            <div class="alert alert-info">
                                                <strong><?php echo _l('margin'); ?>:</strong>
												<?php
												$margin = catering_profit_margin($item->unit_cost, $item->unit_price);
												echo catering_margin_badge($margin);
												?>
                                            </div>
										<?php } ?>
                                    </div>
                                </div>

                                <!-- Dietary & Allergen Information -->
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <h4 class="bold"><?php echo _l('dietary_allergen_information'); ?></h4>

                                        <div class="form-group">
                                            <label><?php echo _l('dietary_types'); ?></label>
                                            <div class="checkbox-group">
												<?php
												$selected_dietary = [];
												if (isset($item) && ! empty($item->dietary_types))
												{
													foreach ($item->dietary_types as $dt)
													{
														$selected_dietary[] = $dt['id'];
													}
												}

												foreach ($dietary_types as $dt)
												{
													$checked = in_array($dt['id'], $selected_dietary) ? 'checked' : '';
													?>
                                                    <div class="form-group">
                                                        <div class="checkbox">
                                                            <input type="checkbox"
																<?php echo $checked ?>
                                                                   name="dietary_types[]"
                                                                   value="<?php echo $dt['id']; ?>"
                                                                   id="dietary_types_<?php echo $dt['id']; ?>">
                                                            <label for="dietary_types_<?php echo $dt['id']; ?>">
																<?php echo htmlspecialchars($dt['label']); ?>
                                                            </label>
                                                        </div>
                                                    </div>
												<?php } ?>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label><?php echo _l('allergens'); ?></label>
                                            <div class="checkbox-group">
												<?php
												$selected_allergens = [];
												if (isset($item) && ! empty($item->allergens))
												{
													foreach ($item->allergens as $allergen)
													{
														$selected_allergens[] = $allergen['id'];
													}
												}
												foreach ($allergens as $allergen)
												{
													$checked = in_array($allergen['id'], $selected_allergens) ? 'checked' : '';
													$badge_class = $allergen['severity'] == 'severe' ? 'danger' : ($allergen['severity'] == 'moderate' ? 'warning' : 'info');
													?>
                                                    <div class="form-group">
                                                        <div class="checkbox">
                                                            <input type="checkbox"
																<?php echo $checked ?>
                                                                   name="allergens[]"
                                                                   value="<?php echo $allergen['id']; ?>"
                                                                   id="allergens_<?php echo $allergen['id']; ?>">
                                                            <label for="allergens_<?php echo $allergen['id']; ?>">
                                                                <span class="label label-<?php echo $badge_class; ?>">
                                                                    <i class="<?php echo $allergen['icon']; ?>"></i>
                                                                    <?php echo htmlspecialchars($allergen['label']); ?>
                                                                </span>
                                                            </label>
                                                        </div>
                                                    </div>
												<?php } ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Sidebar -->
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <h4 class="bold"><?php echo _l('settings'); ?></h4>

                                        <div class="form-group">
                                            <label for="active"><?php echo _l('status'); ?></label>
                                            <select name="active" id="active" class="selectpicker" data-width="100%">
                                                <option value="1" <?php echo (isset($item) && $item->active == 1) || ! isset($item) ? 'selected' : ''; ?>>
													<?php echo _l('active'); ?>
                                                </option>
                                                <option value="0" <?php echo (isset($item) && $item->active == 0) ? 'selected' : ''; ?>>
													<?php echo _l('inactive'); ?>
                                                </option>
                                            </select>
                                        </div>

										<?php if (isset($item)) { ?>
                                            <div class="form-group">
                                                <label><?php echo _l('version'); ?></label>
                                                <p class="form-control-static">
													<?php echo catering_version_badge($item->version); ?>
                                                </p>
                                            </div>

                                            <div class="form-group">
                                                <label><?php echo _l('created'); ?></label>
                                                <p class="form-control-static">
													<?php echo _dt($item->created_at); ?>
                                                </p>
                                            </div>

											<?php if ($item->updated_at) { ?>
                                                <div class="form-group">
                                                    <label><?php echo _l('last_updated'); ?></label>
                                                    <p class="form-control-static">
														<?php echo _dt($item->updated_at); ?>
                                                    </p>
                                                </div>
											<?php } ?>
										<?php } ?>

                                        <hr/>

                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fa fa-check"></i>
											<?php echo _l('submit'); ?>
                                        </button>

                                        <a href="<?php echo admin_url('catering/items'); ?>" class="btn btn-default btn-block">
                                            <i class="fa fa-times"></i>
											<?php echo _l('cancel'); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
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
        appValidateForm($('#menu_item_form'), {
            item_name: 'required',
            category_id: 'required',
            unit_cost: {
                required: true,
                number: true,
                min: 0
            },
            unit_price: {
                required: true,
                number: true,
                min: 0
            }
        });

        // Calculate margin on price/cost change
        $('#unit_cost, #unit_price').on('input', function () {
            var cost = parseFloat($('#unit_cost').val()) || 0;
            var price = parseFloat($('#unit_price').val()) || 0;

            if (price > 0) {
                var margin = ((price - cost) / price) * 100;
                margin = margin.toFixed(2);

                var badge_class = 'default';
                if (margin >= 40) badge_class = 'success';
                else if (margin >= 20) badge_class = 'warning';
                else if (margin >= 0) badge_class = 'danger';

                $('#margin_display').html(
                    '<span class="label label-' + badge_class + '">' + margin + '%</span>'
                );
            }
        });
    });
</script>
</body>
</html>