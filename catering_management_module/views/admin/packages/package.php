<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <?php echo form_open($this->uri->uri_string(), ['id' => 'package-form']); ?>
        <div class="row">
            <div class="col-md-8">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />

                        <?php $value = (isset($package) ? $package->package_name : ''); ?>
                        <?php echo render_input('package_name', 'package_name', $value, 'text', [], [], '', 'ays-ignore'); ?>

                        <?php $value = (isset($package) ? $package->description : ''); ?>
                        <?php echo render_textarea('description', 'description', $value, [], [], '', 'ays-ignore'); ?>

                        <hr />
                        <div class="d-flex justify-content-between align-items-center mbot15">
                            <h5 class="font-medium no-margin"><?php echo _l('package_items'); ?></h5>
                            <?php if (isset($package)) { ?>
                            <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#add-items-modal">
                                <i class="fa fa-plus"></i> <?php echo _l('add_multiple_items'); ?>
                            </button>
                            <?php } else { ?>
                            <p class="text-muted"><?php echo _l('save_package_to_add_items'); ?></p>
                            <?php } ?>
                        </div>

                        <div id="package-items-wrapper">
                            <?php 
                            if (isset($package)) {
                                $this->load->view('admin/packages/package_items_list', ['items' => $package->items]);
                            } else {
                                echo '<div class="text-center text-muted p-4 border">' . _l('no_items_in_package_yet') . '</div>';
                            }
                            ?>
                        </div>

                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo _l('financial_summary'); ?></h4>
                        <hr class="hr-panel-heading" />

                        <div class="row">
                            <div class="col-md-12">
                                <?php $value = (isset($package) ? $package->price_per_person : ''); ?>
                                <?php echo render_input('price_per_person', 'price_per_person', $value, 'number', ['step' => '0.01']); ?>
                            </div>
                            <div class="col-md-6">
                                <?php $value = (isset($package) ? $package->min_guests : '1'); ?>
                                <?php echo render_input('min_guests', 'min_guests', $value, 'number', ['min' => 1]); ?>
                            </div>
                            <div class="col-md-6">
                                <?php $value = (isset($package) ? $package->max_guests : ''); ?>
                                <?php echo render_input('max_guests', 'max_guests', $value, 'number', ['min' => 1]); ?>
                            </div>
                        </div>

                        <div class="checkbox checkbox-primary">
                            <input type="hidden" name="active" value="0">
                            <input type="checkbox" name="active" value="1" id="active" <?php echo (isset($package) && $package->active == 1) || !isset($package) ? 'checked' : ''; ?>>
                            <label for="active"><?php echo _l('active'); ?></label>
                        </div>

                        <div class="p-3 border rounded bg-light">
                            <div class="row">
                                <div class="col-md-7">
                                    <span class="font-medium"><?php echo _l('calculated_cost'); ?>:</span>
                                </div>
                                <div class="col-md-5 text-right">
                                    <span id="calculated-cost"></span>
                                </div>
                                <div class="col-md-7">
                                    <span class="font-medium"><?php echo _l('profit_per_person'); ?>:</span>
                                </div>
                                <div class="col-md-5 text-right">
                                    <span id="profit-per-person"></span>
                                </div>
                                <div class="col-md-7">
                                    <span class="font-medium"><?php echo _l('profit_margin'); ?>:</span>
                                </div>
                                <div class="col-md-5 text-right">
                                    <span id="profit-margin"></span>
                                </div>
                            </div>
                        </div>

                        <hr />
                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-primary" data-loading-text="<?php echo _l('wait_text'); ?>" data-form="#package-form"><?php echo _l('submit'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php echo form_close(); ?>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="add-items-modal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('add_items_to_package'); ?></h4>
            </div>
            <div class="modal-body">
                <?php
                $table_data = [
                    [
                        'name' => '<div class="checkbox"><input type="checkbox" id="select-all-items"><label></label></div>',
                        'th_attrs' => ['class' => 'check-column'],
                    ],
                    _l('item_name'),
                    _l('category'),
                    _l('price'),
                    _l('cost'),
                ];
                render_datatable($table_data, 'package-add-items', [], ['data-url' => admin_url('catering_management_module/packages/items_table')]);
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-primary" id="add-selected-items"><?php echo _l('add_selected_items'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    var package_id = '<?php echo isset($package) ? $package->id : ""; ?>';
    var confirm_delete_item = '<?php echo _l("confirm_delete_item"); ?>';

    $(function() {
        // Init form validation
        appValidateForm($('#package-form'), {
            package_name: 'required',
            price_per_person: { required: true, number: true }
        });

        // Init items table in modal
        var addItemsTable = initDataTable('.table-package-add-items', $('.table-package-add-items').data('url'), [], [], [], [0, 'asc']);

        // Handle "Select All" checkbox
        $('#select-all-items').on('change', function() {
            var rows = addItemsTable.rows({ search: 'applied' }).nodes();
            $('input[type="checkbox"]', rows).prop('checked', this.checked);
        });

        // Handle "Add Selected Items" button
        $('#add-selected-items').on('click', function() {
            var selected_ids = [];
            var rows = addItemsTable.rows().nodes();
            $('input[type="checkbox"]:checked', rows).each(function() {
                selected_ids.push($(this).val());
            });

            if (selected_ids.length > 0 && package_id) {
                $.post(admin_url + 'catering_management_module/packages/add_multiple_items', {
                    package_id: package_id,
                    item_ids: selected_ids
                }).done(function(response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        reload_package_items();
                        alert_float('success', response.message);
                    }
                    $('#add-items-modal').modal('hide');
                });
            }
        });

        // Handle item deletion
        $('#package-items-wrapper').on('click', '.remove-item', function(e) {
            e.preventDefault();
            if (confirm(confirm_delete_item)) {
                var link_id = $(this).data('id');
                $.post(admin_url + 'catering_management_module/packages/remove_item_from_package', { link_id: link_id }).done(function(response) {
                    response = JSON.parse(response);
                    if (response.success) {
                        reload_package_items();
                        alert_float('success', response.message);
                    }
                });
            }
        });

        // Handle quantity updates on the client-side
        $('#package-items-wrapper').on('change keyup', '.item-qty', function() {
            var $row = $(this).closest('tr');
            var item_id = $row.data('item-id');
            var new_qty = parseFloat($(this).val()) || 0;

            // Update the corresponding hidden input for form submission
            $('#package-items-hidden-inputs input[name$="[item_id]"][value="' + item_id + '"]').next('input[name$="[qty_per_guest]"]').val(new_qty);
            
            calculate_financials();
        });

        // Financial calculations
        $('#price_per_person').on('change keyup', calculate_financials);

        function reload_package_items() {
            if (!package_id) return;
            $.get(admin_url + 'catering_management_module/packages/get_package_items/' + package_id, function(response) {
                $('#package-items-wrapper').html(response);
                calculate_financials();
            });
        }

        function calculate_financials() {
            var price = parseFloat($('#price_per_person').val()) || 0;
            var cost = 0;

            // Calculate cost from the visible table for instant feedback
            $('.table-package-items tbody tr').each(function() {
                var unit_cost_text = $(this).find('td').eq(1).text();
                var unit_cost = accounting.unformat(unit_cost_text);
                var qty = parseFloat($(this).find('.item-qty').val()) || 0;
                if (!isNaN(unit_cost) && !isNaN(qty)) {
                    cost += unit_cost * qty;
                }
            });

            var profit = price - cost;
            var margin = price > 0 ? (profit / price) * 100 : 0;

            $('#calculated-cost').text(format_money(cost));
            $('#profit-per-person').text(format_money(profit));
            $('#profit-margin').text(margin.toFixed(2) + '%');

            // Color coding
            $('#profit-per-person').toggleClass('text-success', profit >= 0).toggleClass('text-danger', profit < 0);
            $('#profit-margin').toggleClass('text-success', profit >= 0).toggleClass('text-danger', profit < 0);
        }

        // Initial calculation
        if (package_id) {
            calculate_financials();
        }
    });
</script>
</body>
</html>
