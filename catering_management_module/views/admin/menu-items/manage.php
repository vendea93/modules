<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/** @var array $categories */

?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <h4 class="tw-mt-0 tw-font-semibold tw-text-lg tw-flex tw-items-center">
									<?php echo _l('menu_items'); ?>
                                </h4>
                            </div>
                        </div>
                        <!-- View Mode Switcher & Actions -->
                        <div class="row tw-my-3">
                            <div class="col-md-12">
                                <div class="_buttons">
									<?php if (staff_can('create', 'menu_item')) { ?>
                                        <a href="<?php echo admin_url('catering_management_module/menu_items/item'); ?>" class="btn btn-primary pull-left">
                                            <i class="fa fa-plus"></i>
											<?php echo _l('new_menu_item'); ?>
                                        </a>
									<?php } ?>

                                    <div class="btn-group pull-right">
                                        <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
											<?php echo _l('filter_by_category'); ?> <span class="caret"></span>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li><a href="#" data-category="all"><?php echo _l('all_categories'); ?></a></li>
                                            <li role="separator" class="divider"></li>
											<?php foreach ($categories as $category) { ?>
                                                <li><a href="#" data-category="<?php echo $category['id']; ?>">
														<?php echo htmlspecialchars($category['name']); ?>
                                                    </a></li>
											<?php } ?>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>


                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>

                        <div class="clearfix"></div>
						<?php
						$table_columns = [
							_l('#'),
							_l('menu_item_name'),
							_l('category'),
							_l('status'),
							_l('created_at'),
						];

						if (catering_can_view_costs())
						{
							$table_columns[] = _l('unit_cost');
							$table_columns[] = _l('unit_price');
							$table_columns[] = _l('margin');
						} else
						{
							$table_columns[] = _l('unit_price');
						}

						$table_columns[] = _l('dietary_allergen_info');

						$table_columns[] = _l('options');

						render_datatable($table_columns, 'menu-items');
						?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    var categoryFilter = 'all';

    $(function () {
        const table = initDataTable('.table-menu-items', admin_url + "catering_management_module/menu_items/table", [], [7,8], {}, [0, 'desc']);

        // Category filter
        $('.dropdown-menu a[data-category]').on('click', function (e) {
            e.preventDefault();
            categoryFilter = $(this).data('category');
            table.ajax.reload();
        });

        // Delete menu item
        $('body').on('click', '.delete-menu-item', function (e) {
            e.preventDefault();
            const itemId = $(this).data('id');
            const itemName = $(this).data('name');

            if (confirm('<?php echo _l('confirm_delete'); ?> "' + itemName + '"?')) {
                $.ajax({
                    url: '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menu_items/delete/'); ?>' + itemId,
                    type: 'POST',
                    data: {
                        id: itemId
                    },
                    success: function (response) {
                        response = JSON.parse(response);
                        if (response.success) {
                            alert_float('success', response.message);
                            table.ajax.reload();
                        } else {
                            alert_float('danger', response.message);
                        }
                    },
                    error: function () {
                        alert_float('danger', '<?php echo _l('something_went_wrong'); ?>');
                    }
                });
            }
        });
    });
</script>
</body>
</html>