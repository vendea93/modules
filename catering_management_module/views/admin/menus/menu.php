<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin">
							<?php echo isset($menu) ? _l('edit_menu') : _l('add_new_menu'); ?>
                        </h4>
                        <hr class="hr-panel-heading"/>

						<?php echo form_open(admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menus/menu'.(isset($menu) ? '/'.$menu->id : '')), ['id' => 'menu_form']); ?>

                        <div class="row">
                            <div class="col-md-8">
                                <!-- Basic Information -->
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <h4 class="bold"><?php echo _l('basic_information'); ?></h4>

										<?php echo render_input('menu_name', 'menu_name', isset($menu) ? $menu->menu_name : '', 'text', ['required' => TRUE]); ?>

										<?php echo render_textarea('description', 'description', isset($menu) ? $menu->description : '', ['rows' => 3]); ?>

										<?php echo render_input('base_price_per_person', 'base_price_per_person', isset($menu) ? $menu->base_price_per_person : '', 'number', ['step' => '0.01', 'min' => '0']); ?>
                                    </div>
                                </div>

                                <!-- Menu Items by Section -->
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <h4 class="bold">
											<?php echo _l('menu_items'); ?>
                                            <button type="button" class="btn btn-sm btn-success pull-right" id="add_menu_item">
                                                <i class="fa fa-plus"></i> <?php echo _l('add_item'); ?>
                                            </button>
                                        </h4>

                                        <div id="menu_sections">
											<?php if (isset($menu) && ! empty($menu->items)) { ?>												<?php foreach ($menu->items as $section) { ?>
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
                                                                    <th width="100px"><?php echo _l('price'); ?></th>
                                                                    <th width="50px"><?php echo _l('actions'); ?></th>
                                                                </tr>
                                                                </thead>
                                                                <tbody class="sortable-items">
																<?php foreach ($section['items'] as $item) { ?>
                                                                    <tr data-link-id="<?php echo $item['link_id']; ?>">
                                                                        <td><span class="handle"><i class="fa fa-sort"></i></span></td>
                                                                        <td>
                                                                            <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
																			<?php if ($item['description']) { ?>
                                                                                <br><small class="text-muted"><?php echo htmlspecialchars($item['description']); ?></small>
																			<?php } ?>
																			<?php if ( ! empty($item['dietary_types']) || ! empty($item['allergens'])) { ?>
                                                                                <br>
																				<?php foreach (explode(',', $item['dietary_types']) as $dietary) { ?>
																					<?php if (trim($dietary)) { ?>
                                                                                        <span class="badge badge-success"><?php echo htmlspecialchars(trim($dietary)); ?></span>
																					<?php } ?>
																				<?php } ?>
																				<?php foreach (explode(',', $item['allergens']) as $allergen) { ?>
																					<?php if (trim($allergen)) {
                                                                                        ?>
                                                                                        <span class="badge badge-danger"><?php echo htmlspecialchars(trim($allergen)); ?></span>
																					<?php } ?>
																				<?php } ?>
																			<?php } ?>
                                                                        </td>
                                                                        <td><?php echo catering_format_price($item['unit_price']); ?></td>
                                                                        <td>
                                                                            <button type="button" class="btn btn-danger btn-icon remove-item" data-link-id="<?php echo $item['link_id']; ?>">
                                                                                <i class="fa fa-trash"></i>
                                                                            </button>
                                                                            <input type="hidden" name="items[<?php echo $item['link_id']; ?>][item_id]" value="<?php echo $item['id']; ?>">
                                                                            <input type="hidden" name="items[<?php echo $item['link_id']; ?>][section_id]" value="<?php echo $section['section_id']; ?>">
                                                                            <input type="hidden" name="items[<?php echo $item['link_id']; ?>][position]" value="<?php echo $item['position']; ?>" class="position-input">
                                                                        </td>
                                                                    </tr>
																<?php } ?>
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>
												<?php } ?>
											<?php } else { ?>
                                                <p class="text-muted text-center" style="padding: 20px;">
                                                    <i class="fa fa-inbox"></i> <?php echo _l('no_items_added_yet'); ?>
                                                </p>
											<?php } ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <!-- Settings Sidebar -->
                                <div class="panel_s">
                                    <div class="panel-body">
                                        <h4 class="bold"><?php echo _l('settings'); ?></h4>

                                        <div class="form-group">
                                            <label for="active"><?php echo _l('status'); ?></label>
                                            <select name="active" id="active" class="selectpicker" data-width="100%">
                                                <option value="1" <?php echo (isset($menu) && $menu->active == 1) || ! isset($menu) ? 'selected' : ''; ?>>
													<?php echo _l('active'); ?>
                                                </option>
                                                <option value="0" <?php echo (isset($menu) && $menu->active == 0) ? 'selected' : ''; ?>>
													<?php echo _l('inactive'); ?>
                                                </option>
                                            </select>
                                        </div>

										<?php if (isset($menu)) { ?>
                                            <div class="form-group">
                                                <label><?php echo _l('created'); ?></label>
                                                <p class="form-control-static">
													<?php echo _dt($menu->created_at); ?>
                                                </p>
                                            </div>

											<?php if ($menu->updated_at) { ?>
                                                <div class="form-group">
                                                    <label><?php echo _l('last_updated'); ?></label>
                                                    <p class="form-control-static">
														<?php echo _dt($menu->updated_at); ?>
                                                    </p>
                                                </div>
											<?php } ?>

                                            <div class="form-group">
                                                <label><?php echo _l('items_count'); ?></label>
                                                <p class="form-control-static">
                                                    <span class="badge badge-info"><?php echo $menu_items_count ?? 0; ?></span>
                                                </p>
                                            </div>
										<?php } ?>

                                        <hr/>

                                        <button type="submit" class="btn btn-primary btn-block">
                                            <i class="fa fa-check"></i>
											<?php echo _l('submit'); ?>
                                        </button>

                                        <a href="<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menus'); ?>" class="btn btn-default btn-block">
                                            <i class="fa fa-times"></i>
											<?php echo _l('cancel'); ?>
                                        </a>

										<?php if (isset($menu)) { ?>
                                            <button type="button" class="btn btn-info btn-block" id="duplicate_menu_btn">
                                                <i class="fa fa-copy"></i>
												<?php echo _l('duplicate'); ?>
                                            </button>

                                            <a href="<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menus/export_pdf/'.$menu->id); ?>" class="btn btn-warning btn-block">
                                                <i class="fa fa-file-pdf"></i>
												<?php echo _l('export_pdf'); ?>
                                            </a>
										<?php } ?>
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

<!-- Add Item Modal -->
<div class="modal fade" id="add_item_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title"><?php echo _l('add_item_to_menu'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php echo _l('section'); ?> <span class="text-danger">*</span></label>
                            <select id="item_section" class="selectpicker" data-width="100%" required>
                                <option selected hidden disabled><?php echo _l('select_section'); ?></option>
								<?php foreach ($sections as $section) { ?>
                                    <option value="<?php echo $section['id']; ?>">
										<?php echo htmlspecialchars($section['name']); ?>
                                    </option>
								<?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label><?php echo _l('search_items'); ?></label>
                            <input type="text" id="search_items" class="form-control" placeholder="<?php echo _l('type_to_search'); ?>">
                        </div>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover table-sm" id="items_table">
                        <thead>
                        <tr>
                            <th><?php echo _l('item_name'); ?></th>
                            <th><?php echo _l('category'); ?></th>
                            <th><?php echo _l('price'); ?></th>
                            <th width="80px" class="text-center"><?php echo _l('action'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php foreach ($items as $item) { ?>
                            <tr data-item-id="<?php echo $item['id']; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($item['item_name']); ?></strong>
									<?php if ( ! empty($item['dietary_types']) || ! empty($item['allergens'])) { ?>
                                        <br>
										<?php
										$dietary_array = $item['dietary_types'];
										$allergen_array = $item['allergens'];
										?>
										<?php foreach ($dietary_array as $dietary) { ?>
                                            <span class="badge badge-success"><?php echo htmlspecialchars(trim($dietary['label'])); ?></span>
										<?php } ?>
										<?php foreach ($allergen_array as $allergen) {
											$badge_class = $allergen['severity'] == 'severe' ? 'danger' : ($allergen['severity'] == 'moderate' ? 'warning' : 'info');
                                            ?>
                                            <span class="label label-<?php echo $badge_class ?>"><?php echo htmlspecialchars(trim($allergen['label'])); ?></span>
										<?php } ?>
									<?php } ?>
                                </td>
                                <td><?php echo htmlspecialchars($item['category_name'] ?? ''); ?></td>
                                <td><?php echo catering_format_price($item['unit_price']); ?></td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-success btn-sm select-item" data-item='<?php echo json_encode($item); ?>' title="<?php echo _l('add'); ?>">
                                        <i class="fa fa-plus"></i>
                                    </button>
                                </td>
                            </tr>
						<?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Duplicate Menu Modal -->
<div class="modal fade" id="duplicate_menu_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><?php echo _l('duplicate_menu'); ?></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="duplicate_name"><?php echo _l('menu_name'); ?></label>
                    <input type="text" id="duplicate_name" class="form-control" placeholder="<?php echo _l('enter_menu_name'); ?>">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal"><?php echo _l('cancel'); ?></button>
                <button type="button" class="btn btn-primary" id="confirm_duplicate"><?php echo _l('duplicate'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    var itemCounter = 1000;
    var menu_id = '<?php echo isset($menu) ? $menu->id : 0; ?>';
    var sectionsList = <?php echo json_encode($sections); ?>;

    $(function () {
        // Initialize sortable for items
        initSortable();

        // Event Handlers
        $('#add_menu_item').on('click', function (e) {
            e.preventDefault();
            $('#add_item_modal').modal('show');
        });

        // Select item from modal
        $('body').on('click', '.select-item', function (e) {
            e.preventDefault();
            var item = $(this).data('item');
            var sectionId = $('#item_section').val();

            if (!sectionId) {
                alert_float('warning', '<?php echo _l("please_select_section"); ?>');
                return;
            }

            addItemToMenu(item, sectionId);
        });

        // Remove item
        $('body').on('click', '.remove-item', function (e) {
            e.preventDefault();
            if (confirm('<?php echo _l("confirm_remove_item"); ?>')) {
                $(this).closest('tr').fadeOut(function () {
                    $(this).remove();
                });
            }
        });

        // Search items
        $('#search_items').on('keyup', function () {
            var value = $(this).val().toLowerCase();
            $('#items_table tbody tr').filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });

        // Duplicate Menu
        $('#duplicate_menu_btn').on('click', function () {
            $('#duplicate_name').val('<?php echo isset($menu) ? $menu->menu_name." (Copy)" : ""; ?>');
            $('#duplicate_menu_modal').modal('show');
        });

        $('#confirm_duplicate').on('click', function () {
            var new_name = $('#duplicate_name').val();
            if (!new_name) {
                alert_float('warning', '<?php echo _l("field_required", _l("menu_name")); ?>');
                return;
            }

            $.ajax({
                url: '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME."/menus/duplicate"); ?>',
                type: 'POST',
                data: {menu_id: menu_id, new_name: new_name},
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert_float('success', response.message);
                        window.location.href = response.redirect;
                    } else {
                        alert_float('danger', response.message);
                    }
                },
                error: function () {
                    alert_float('danger', '<?php echo _l("something_went_wrong"); ?>');
                }
            });
        });

        // Form validation
        appValidateForm($('#menu_form'), {
            menu_name: 'required'
        });
    });

    /**
     * Initialize sortable
     */
    function initSortable() {
        $('.sortable-items').sortable({
            handle: '.handle',
            axis: 'y',
            placeholder: 'ui-sortable-placeholder',
            update: function (event, ui) {
                updatePositions($(this));
            }
        });
    }

    /**
     * Add item to menu (auto-creates section if needed)
     */
    function addItemToMenu(item, sectionId) {
        var sectionTable = $('.section-items-table[data-section="' + sectionId + '"]');

        // If section doesn't exist, create it
        if (sectionTable.length === 0) {
            createSection(sectionId, function () {
                sectionTable = $('.section-items-table[data-section="' + sectionId + '"]');
                insertItemIntoTable(item, sectionId, sectionTable);
            });
        } else {
            insertItemIntoTable(item, sectionId, sectionTable);
        }
    }

    /**
     * Create section on the fly
     */
    function createSection(sectionId, callback) {
        var section = sectionsList.find(function (s) {
            return s.id == sectionId;
        });

        if (!section) {
            alert_float('danger', '<?php echo _l("invalid_section"); ?>');
            return;
        }

        var sectionHtml = '<div class="menu-section" data-section-id="' + section.id + '">' +
            '<h5 class="bold">' +
            '<i class="fa fa-bars sortable-section-handle"></i>' +
            escapeHtml(section.name) +
            '</h5>' +
            '<div class="table-responsive">' +
            '<table class="table table-bordered section-items-table" data-section="' + section.id + '">' +
            '<thead>' +
            '<tr>' +
            '<th width="30px"><i class="fa fa-sort"></i></th>' +
            '<th><?php echo _l("item_name"); ?></th>' +
            '<th width="100px"><?php echo _l("price"); ?></th>' +
            '<th width="50px"><?php echo _l("actions"); ?></th>' +
            '</tr>' +
            '</thead>' +
            '<tbody class="sortable-items"></tbody>' +
            '</table>' +
            '</div>' +
            '</div>';

        $('#menu_sections').append(sectionHtml);
        initSortable();

        if (callback) {
            callback();
        }
    }

    /**
     * Insert item into table
     */
    function insertItemIntoTable(item, sectionId, sectionTable) {
        console.log(item);
        var position = sectionTable.find('tbody tr').length;
        var uniqueId = 'new_' + (itemCounter++);

        var row = '<tr data-link-id="' + uniqueId + '">' +
            '<td><span class="handle"><i class="fa fa-sort"></i></span></td>' +
            '<td><strong>' + escapeHtml(item.item_name) + '</strong>';

        if (item.description) {
            row += '<br><small class="text-muted">' + escapeHtml(item.description) + '</small>';
        }

        // Add badges
        if (item.dietary_types) {
            var dietaries = item.dietary_types
            dietaries.forEach(function (d) {
                row += '<br><span class="badge badge-success">' + escapeHtml(d.label) + '</span>';
            });
        }

        if (item.allergens) {
            var allergens = item.allergens;
            allergens.forEach(function (a) {
                row += '<span class="badge badge-danger">' + escapeHtml(a.label) + '</span>';
            });
        }

        row += '</td>' +
            '<td>' + format_money(item.unit_price) + '</td>' +
            '<td>' +
            '<button type="button" class="btn btn-danger btn-icon remove-item"><i class="fa fa-trash"></i></button>' +
            '<input type="hidden" name="items[' + uniqueId + '][item_id]" value="' + item.id + '">' +
            '<input type="hidden" name="items[' + uniqueId + '][section_id]" value="' + sectionId + '">' +
            '<input type="hidden" name="items[' + uniqueId + '][position]" value="' + position + '" class="position-input">' +
            '</td>' +
            '</tr>';

        sectionTable.find('tbody').append(row);
        $('#add_item_modal').modal('hide');
        $('#search_items').val(''); // Reset search

        alert_float('success', '<?php echo _l("item_added_successfully"); ?>');
    }

    /**
     * Update positions
     */
    function updatePositions(tbody) {
        tbody.find('tr').each(function (index) {
            $(this).find('.position-input').val(index);
        });
    }

    /**
     * Escape HTML
     */
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function (m) {
            return map[m];
        });
    }
</script>
</body>
</html>