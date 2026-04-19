<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/** @var object $event */
/** @var object $event_menu */
/** @var array $menus */
/** @var array $packages */
/** @var array $menu_items */
/** @var array $menu_sections */
/** @var array $event_staff */
/** @var array $staff_summary */
/** @var array $financials_summary */
/** @var array $notes_stats */
/** @var array $menu_sections */
/** @var array $event_menu_summary */


?>
<div class="row">
    <div class="col-md-8">
        <!-- Menu Selection -->
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="tw-font-semibold tw-mb-4">
                    <i class="fa fa-book tw-mr-2"></i>
					<?php echo _l('menu_selection'); ?>
                </h4>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php echo _l('select_menu_or_package'); ?></label>
                            <select class="selectpicker" data-width="100%" id="menu_selection" data-event-id="<?php echo $event->eventid; ?>">
                                <option value=""><?php echo _l('choose_option'); ?></option>
                                <optgroup label="<?php echo _l('menus'); ?>">
									<?php foreach ($menus as $menu): ?>
                                        <option value="menu_<?php echo $menu['id']; ?>"
                                                data-type="menu"
                                                data-id="<?php echo $menu['id']; ?>"
                                                data-price="<?php echo $menu['base_price_per_person']; ?>"
											<?php echo ($event_menu && $event_menu->menu_id == $menu['id']) ? 'selected' : ''; ?>>
											<?php echo $menu['menu_name']; ?>
                                        </option>
									<?php endforeach; ?>
                                </optgroup>
                                <optgroup label="<?php echo _l('packages'); ?>">
									<?php foreach ($packages as $package): ?>
                                        <option value="package_<?php echo $package['id']; ?>"
                                                data-type="package"
                                                data-id="<?php echo $package['id']; ?>"
                                                data-price="<?php echo $package['price_per_person']; ?>"
											<?php echo ($event_menu && $event_menu->package_id == $package['id']) ? 'selected' : ''; ?>>
											<?php echo $package['package_name']; ?> - $<?php echo number_format($package['price_per_person'], 2); ?>/person
                                        </option>
									<?php endforeach; ?>
                                </optgroup>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php echo _l('pricing_mode'); ?></label>
                            <select class="selectpicker" data-width="100%" id="pricing_mode">
                                <option value="per_person" <?php echo ($event_menu && $event_menu->pricing_mode == 'per_person') ? 'selected' : ''; ?>><?php echo _l('per_person'); ?></option>
                                <option value="fixed" <?php echo ($event_menu && $event_menu->pricing_mode == 'fixed') ? 'selected' : ''; ?>><?php echo _l('fixed_price'); ?></option>
                                <option value="package" <?php echo ($event_menu && $event_menu->pricing_mode == 'package') ? 'selected' : ''; ?>><?php echo _l('package_price'); ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php echo _l('price_per_person'); ?></label>
                            <div class="input-group">
                                <span class="input-group-addon">$</span>
                                <input type="number" class="form-control" id="price_per_person"
                                       value="<?php echo $event_menu ? $event_menu->price_per_person : '45.00'; ?>"
                                       step="0.01">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php echo _l('total_menu_cost'); ?></label>
                            <div class="input-group">
                                <span class="input-group-addon">$</span>
                                <input type="text" class="form-control" id="total_menu_cost"
                                       value="<?php echo $event_menu ? number_format($event_menu->price_per_person * $event->guest_count_expected, 2) : number_format(45 * $event->guest_count_expected, 2); ?>"
                                       readonly>
                            </div>
                            <small class="text-muted">Based on <?php echo $event->guest_count_expected; ?> guests</small>
                        </div>
                    </div>
                </div>

                <hr>

                <!-- Menu Items List -->
                <div class="clearfix mb-3">
                    <h5 class="pull-left tw-font-semibold"><?php echo _l('menu_items'); ?></h5>
                    <button class="btn btn-success btn-sm pull-right" id="add-menu-item">
                        <i class="fa fa-plus"></i> <?php echo _l('add_item'); ?>
                    </button>
                </div>

				<?php
				$event_menu_items = $event_menu->items ?? [];
				?>
				<?php if ( ! empty($event_menu_items)) : ?>
					<?php foreach ($event_menu_items as $section) : ?>
                        <div class="menu-section" data-section-id="<?php echo $section['section_id']; ?>">
                            <h5 class="bold">
                                <i class="fa fa-bars sortable-section-handle"></i>
								<?php echo htmlspecialchars($section['section_name']); ?>
                            </h5>
                            <div class="table-responsive">
                                <table class="table table-bordered section-items-table" data-section="<?php echo $section['section_id']; ?>">
                                    <thead>
                                    <tr>
                                        <th width="30px"><i class="fa fa-sort"></i></th>
                                        <th><?php echo _l('item_name'); ?></th>
                                        <th><?php echo _l('category'); ?></th>
                                        <th><?php echo _l('portion_size'); ?></th>
                                        <th><?php echo _l('unit_cost'); ?></th>
                                        <th><?php echo _l('unit_price'); ?></th>
                                        <th><?php echo _l('allergens_and_dietary_types'); ?></th>
                                        <th width="50px"><?php echo _l('actions'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody class="sortable-items">
									<?php
									$items = $section['items'];
//                                    cmm_debug_var($items);
									foreach ($items as $item)
									{ ?>
                                        <tr data-id="<?php echo $item['id']; ?>">
                                            <td class="handle"><i class="fa fa-bars text-muted"></i></td>
                                            <td>
                                                <strong><?php echo $item['item_name']; ?></strong><br>
                                                <small class="text-muted"><?php echo $item['description']; ?></small>
                                            </td>
                                            <td><span class="label label-info"><?php echo $item['category_name']; ?></span></td>
                                            <td><?php echo $item['portion_per_guest']; ?> per guest</td>
                                            <td><?php echo app_format_money($item['unit_cost'], get_base_currency()); ?></td>
                                            <td><?php echo app_format_money($item['unit_price'], get_base_currency()); ?></td>
                                            <td>
												<?php if ( ! empty($item['dietary_types'])): ?>
													<?php foreach ($item['dietary_types'] as $dietary): ?>
                                                        <span class="label label-success label-xs" title="<?php echo $dietary['label']; ?>">
                                                    <?php echo $dietary['label']; ?>
                                                </span>
													<?php endforeach; ?>
												<?php endif; ?>
												<?php if ( ! empty($item['allergens'])): ?>
													<?php foreach ($item['allergens'] as $allergen): ?>
                                                        <span class="label label-warning label-xs" title="<?php echo $allergen['label']; ?>">
                                                    <?php echo $allergen['label']; ?>
                                                </span>
													<?php endforeach; ?>
												<?php endif; ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-default btn-xs edit-item"
                                                        data-item-data='<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, 'UTF-8'); ?>'
                                                        data-item-id="<?php echo $item['id']; ?>">
                                                    <i class="fa fa-pencil"></i>
                                                </button>
                                                <button class="btn btn-danger btn-xs delete-item" data-item-id="<?php echo $item['id']; ?>">
                                                    <i class="fa fa-remove"></i>
                                                </button>
                                            </td>
                                        </tr>
									<?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
					<?php endforeach; ?>
				<?php endif ?>
            </div>
        </div>
    </div>
    <!-- Right Sidebar -->
    <div class="col-md-4">
        <!-- Allergen Summary -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-exclamation-triangle tw-mr-2"></i>
					<?php echo _l('allergen_summary'); ?>
                </h5>
                <div class="tw-space-y-2">
					<?php if ($event_menu && ! empty($event_menu->allergen_summary)): ?>
						<?php foreach ($event_menu->allergen_summary as $allergen): ?>
                            <div class="alert alert-warning alert-sm">
                                <i class="fa fa-warning"></i>
                                <strong><?php echo $allergen['label']; ?></strong> - <?php echo $allergen['item_count']; ?> items
                            </div>
						<?php endforeach; ?>
					<?php else: ?>
                        <div class="text-muted">
                            <i class="fa fa-info-circle"></i> <?php echo _l('no_allergen_data'); ?>
                        </div>
					<?php endif; ?>
                </div>
                <button class="btn btn-default btn-block btn-sm mt-3">
                    <i class="fa fa-file-pdf-o"></i> <?php echo _l('generate_allergen_report'); ?>
                </button>
            </div>
        </div>

        <!-- Dietary Flags -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-leaf tw-mr-2"></i>
					<?php echo _l('dietary_options'); ?>
                </h5>
                <div class="tw-space-y-2">
					<?php if ($event_menu && ! empty($event_menu->dietary_summary)): ?>
						<?php foreach ($event_menu->dietary_summary as $dietary): ?>
                            <div>
                                <span class="label label-success"><?php echo $dietary['label']; ?></span>
                                <span class="text-muted"><?php echo $dietary['item_count']; ?> items</span>
                            </div>
						<?php endforeach; ?>
					<?php else: ?>
                        <div class="text-muted">
                            <i class="fa fa-info-circle"></i> <?php echo _l('no_dietary_data'); ?>
                        </div>
					<?php endif; ?>
                </div>
            </div>
        </div>

        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-exclamation-triangle tw-mr-2"></i>
					<?php echo _l('quick_stats'); ?>
                </h5>

                <div class="tw-space-y-3">
                    <div class="tw-flex tw-justify-between">
                        <span class="text-muted"><?php echo _l('total_sections'); ?>:</span>
                        <strong><?php echo number_format($event_menu_summary['total_sections']) ?></strong>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="text-muted"><?php echo _l('total_items'); ?>:</span>
                        <strong><?php echo number_format($event_menu_summary['total_items']) ?></strong>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="text-muted"><?php echo _l('total_cost'); ?>:</span>
                        <strong><?php echo app_format_money($event_menu_summary['total_costs'], get_base_currency()) ?></strong>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span class="text-muted"><?php echo _l('total_price'); ?>:</span>
                        <strong><?php echo app_format_money($event_menu_summary['total_prices'], get_base_currency()) ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="panel_s">
            <div class="panel-body">
                <button class="btn btn-primary btn-block" id="save-menu">
                    <i class="fa fa-save"></i> <?php echo _l('save_menu'); ?>
                </button>
            </div>
        </div>

    </div>
</div>

<!-- Add Menu Item Modal -->
<div class="modal fade" id="addMenuItemModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo _l('add_item'); ?></h4>
            </div>
            <div class="modal-body">
                <form id="addMenuItemForm">
                    <input type="hidden" id="event_id" value="<?php echo $event->eventid; ?>">
                    <input type="hidden" id="event_menu_id" value="<?php echo $event_menu->menu_id ?? ''; ?>">
                    <div class="form-group">
                        <label><?php echo _l('select_section'); ?></label>
                        <select class="form-control" id="item_section" required>
                            <option value=""><?php echo _l('please_select_section'); ?></option>
							<?php foreach ($menu_sections as $section): ?>
                                <option value="<?php echo $section['id']; ?>"><?php echo $section['name']; ?></option>
							<?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label><?php echo _l('search_items'); ?></label>
                        <input type="text" class="form-control" id="item_search" placeholder="<?php echo _l('type_to_search'); ?>">
                        <div id="item_results" class="mt-2" style="max-height: 200px; overflow-y: auto;"></div>
                    </div>

                    <div class="form-group" id="selected_item_details" style="display: none; margin: 10px 0;">
                        <input type="hidden" id="selected_item_id">
                        <div class="well">
                            <h5 id="selected_item_name"></h5>
                            <p id="selected_item_description" class="text-muted"></p>
                            <p><strong><?php echo _l('unit_cost'); ?>:</strong> $<span id="selected_unit_cost"></span></p>
                            <p><strong><?php echo _l('unit_price'); ?>:</strong> $<span id="selected_item_price"></span></p>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?php echo _l('portion_size'); ?></label>
                        <input type="number" class="form-control" id="item_portion_per_guest" value="1.0" step="0.1" min="0.1">
                    </div>
                    <div class="form-group">
                        <label><?php echo _l('unit_cost'); ?></label>
                        <div class="input-group">
                            <span class="input-group-addon">$</span>
                            <input type="number" class="form-control" id="item_unit_cost" step="0.1" min="0">
                        </div>
                    </div>

                    <div class="form-group">
                        <label><?php echo _l('unit_price'); ?></label>
                        <div class="input-group">
                            <span class="input-group-addon">$</span>
                            <input type="number" class="form-control" id="item_unit_price" step="0.1" min="0">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('cancel'); ?></button>
                <button type="button" class="btn btn-primary" id="confirm_add_item"><?php echo _l('add_item'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Menu Item Modal -->
<div class="modal fade" id="editMenuItemModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo _l('edit_menu_item'); ?></h4>
            </div>
            <div class="modal-body">
                <form id="editMenuItemForm">
                    <input type="hidden" id="edit_event_item_id">

                    <div class="form-group">
                        <label><?php echo _l('item_name'); ?></label>
                        <input type="text" class="form-control" id="edit_item_name" readonly>
                    </div>

                    <div class="form-group">
                        <label><?php echo _l('description'); ?></label>
                        <textarea class="form-control" id="edit_item_description" rows="2" readonly></textarea>
                    </div>

                    <div class="form-group">
                        <label><?php echo _l('portion_size'); ?></label>
                        <input type="number" class="form-control" id="edit_portion_per_guest" step="0.1" min="0.1" required>
                        <small class="text-muted"><?php echo _l('per_person'); ?></small>
                    </div>

                    <div class="form-group">
                        <label><?php echo _l('unit_cost'); ?></label>
                        <div class="input-group">
                            <span class="input-group-addon">$</span>
                            <input type="number" class="form-control" id="edit_unit_cost" step="0.1" min="0" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label><?php echo _l('unit_price'); ?></label>
                        <div class="input-group">
                            <span class="input-group-addon">$</span>
                            <input type="number" class="form-control" id="edit_unit_price" step="0.1" min="0" required>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('cancel'); ?></button>
                <button type="button" class="btn btn-primary" id="confirm_edit_item"><?php echo _l('save'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        $(function () {
            var eventId = <?php echo $event->eventid; ?>;
            var guestCount = <?php echo $event->guest_count_expected; ?>;

            // Make items sortable
            var el = document.querySelector('.sortable-items');
            if (el) {
                $('.sortable-items').sortable({
                    handle: '.handle',
                    axis: 'y',
                    placeholder: 'ui-sortable-placeholder',
                    update: function (event, ui) {
                        updatePositions($(this));
                    }
                });

                function updatePositions(sortable) {
                    const positions = {};
                    $(sortable).find('tr').each(function (index) {
                        var itemId = $(this).data('id');
                        if (itemId) {
                            positions[itemId] = index + 1;
                        }
                    });

                    $.post(admin_url + 'catering_management_module/events/update_menu_item_positions', {
                        positions: positions
                    }, function (response) {
                        if (response.success) {
                            alert_float('success', response.message);
                        }
                    }, 'json');
                }
            }

            // Menu selection change
            $('#menu_selection').on('change', function () {
                var selectedOption = $(this).find('option:selected');
                var type = selectedOption.data('type');
                var id = selectedOption.data('id');
                var price = selectedOption.data('price');

                if (type && id) {
                    $('#price_per_person').val(price);
                    updateTotalCost();

                    // Load menu/package
                    $.post(admin_url + 'catering_management_module/events/save_event_menu', {
                        event_id: eventId,
                        menu_type: type,
                        menu_id: id,
                        pricing_mode: $('#pricing_mode').val(),
                        price_per_person: price
                    }, function (response) {
                        if (response.success) {
                            setTimeout(function () {
                                location.reload();
                            }, 1500)
                        } else {
                            alert_float('danger', response.message);
                        }
                    }, 'json');
                }
            });

            // Pricing mode change
            $('#pricing_mode').on('change', function () {
                updateTotalCost();
            });

            // Price per person change
            $('#price_per_person').on('change', function () {
                updateTotalCost();
            });

            // Update total cost
            function updateTotalCost() {
                var price = parseFloat($('#price_per_person').val()) || 0;
                var total = guestCount * price;
                $('#total_menu_cost').val(total.toFixed(2));

                // Update subtotal in table
                $('#menu-subtotal').text('$' + price.toFixed(2));
            }

            // Add menu item
            $('#add-menu-item').on('click', function () {
                $('#addMenuItemModal').modal('show');
            });

            // Search items
            $('#item_search').on('input', function () {
                const search = $(this).val();
                const eventId = $('#event_id').val();
                const eventMenuId = $('#event_menu_id').val();

                if (search.length >= 1) {
                    $.get(admin_url + 'catering_management_module/events/get_menu_items', {
                        search: search,
                        event_id: eventId,
                        event_menu_id: eventMenuId,
                    }, function (response) {
                        if (response.success) {
                            var html = '';
                            if (response.items.length > 0) {
                                response.items.forEach(function (item) {
                                    html += '<div class="item-option" data-item-id="' + item.id + '" data-name="' + item.item_name + '" data-description="' + item.description + '" data-cost="' + item.unit_cost + '" data-price="' + item.unit_price + '">';
                                    html += '<strong>' + item.item_name + '</strong>';
                                    html += '<br><small class="text-muted">' + item.category_name + '</small>';
                                    html += '<br><small class="text-success">$' + parseFloat(item.unit_price).toFixed(2) + '</small>';
                                    html += '</div>';
                                });
                            } else {
                                html = '<div class="text-muted"><?php echo _l('no_items_found'); ?></div>';
                            }
                            $('#item_results').html(html);
                        }
                    }, 'json');
                } else {
                    $('#item_results').html('');
                }
            });

            // Select item
            $(document).on('click', '.item-option', function () {
                const itemId = $(this).data('item-id');
                const name = $(this).data('name');
                const description = $(this).data('description');
                const cost = $(this).data('cost');
                const price = $(this).data('price');

                $('#selected_item_id').val(itemId);
                $('#selected_item_name').text(name);
                $('#selected_item_description').text(description);
                $('#selected_unit_cost').text(parseFloat(cost).toFixed(2));
                $('#selected_item_price').text(parseFloat(price).toFixed(2));
                $('#item_unit_price').val(price);
                $('#item_unit_cost').val(cost);
                $('#selected_item_details').show();
                $('#item_results').html('');
                $('#item_search').val('');
            });

            // Confirm add item
            $('#confirm_add_item').on('click', function () {
                var eventId = $('#event_id').val();
                var itemId = $('#selected_item_id').val();
                var sectionId = $('#item_section').val();
                var portionPerGuest = $('#item_portion_per_guest').val();
                var costPrice = $('#item_unit_cost').val();
                var unitPrice = $('#item_unit_price').val();

                if (!itemId || !sectionId) {
                    alert_float('danger', '<?php echo _l('missing_parameters'); ?>');
                    return;
                }

                $.post(admin_url + 'catering_management_module/events/add_menu_item', {
                    event_id: eventId,
                    item_id: itemId,
                    section_id: sectionId,
                    portion_per_guest: portionPerGuest,
                    unit_cost: costPrice,
                    unit_price: unitPrice
                }, function (response) {
                    if (response.success) {
                        alert_float('success', response.message);
                        $('#addMenuItemModal').modal('hide');
                        setTimeout(function () {
                            location.reload();
                        }, 1500) // Reload to show new item
                    } else {
                        alert_float('danger', response.message);
                    }
                }, 'json');
            });

            // Edit item
            $('.edit-item').on('click', function () {
                const itemId = $(this).data('item-id');
                const itemData = $(this).data('item-data');

                // Populate modal
                $('#edit_event_item_id').val(itemId);
                $('#edit_item_name').val(itemData.item_name);
                $('#edit_item_description').val(itemData.description);
                $('#edit_portion_per_guest').val(itemData.portion_per_guest);
                $('#edit_unit_cost').val(itemData.unit_cost);
                $('#edit_unit_price').val(itemData.unit_price);

                // Show modal
                $('#editMenuItemModal').modal('show');
            });

            // Confirm edit item
            $('#confirm_edit_item').on('click', function () {
                var eventItemId = $('#edit_event_item_id').val();
                var portionPerGuest = $('#edit_portion_per_guest').val();
                var costPrice = $('#edit_unit_cost').val();
                var unitPrice = $('#edit_unit_price').val();

                if (!eventItemId || !portionPerGuest || !unitPrice) {
                    alert_float('danger', '<?php echo _l('missing_parameters'); ?>');
                    return;
                }

                $.post(admin_url + 'catering_management_module/events/update_menu_item', {
                    event_item_id: eventItemId,
                    portion_per_guest: portionPerGuest,
                    unit_cost: costPrice,
                    unit_price: unitPrice
                }, function (response) {
                    if (response.success) {
                        alert_float('success', response.message);
                        $('#editMenuItemModal').modal('hide');
                        setTimeout(function () {
                            location.reload();
                        }, 1500) // Reload to show updated item
                    } else {
                        alert_float('danger', response.message);
                    }
                }, 'json');
            });

            // Delete item
            $('.delete-item').on('click', function () {
                var itemId = $(this).data('item-id');
                if (confirm('<?php echo _l('confirm_remove_item'); ?>')) {
                    $.post(admin_url + 'catering_management_module/events/remove_menu_item', {
                        event_item_id: itemId
                    }, function (response) {
                        if (response.success) {
                            alert_float('success', response.message);
                            setTimeout(function () {
                                location.reload();
                            }, 1500) // Reload to remove item
                        } else {
                            alert_float('danger', response.message);
                        }
                    }, 'json');
                }
            });

            // Save menu
            $('#save-menu').on('click', function () {
                var menuType = $('#menu_selection').find('option:selected').data('type');
                var menuId = $('#menu_selection').find('option:selected').data('id');
                var pricingMode = $('#pricing_mode').val();
                var pricePerPerson = $('#price_per_person').val();

                if (!menuType || !menuId) {
                    alert_float('danger', '<?php echo _l('please_select_menu_or_package'); ?>');
                    return;
                }

                $.post(admin_url + 'catering_management_module/events/save_event_menu', {
                    event_id: eventId,
                    menu_type: menuType,
                    menu_id: menuId,
                    pricing_mode: pricingMode,
                    price_per_person: pricePerPerson
                }, function (response) {
                    if (response.success) {
                        alert_float('success', response.message);
                    } else {
                        alert_float('danger', response.message);
                    }
                }, 'json');
            });
        });
    });
</script>