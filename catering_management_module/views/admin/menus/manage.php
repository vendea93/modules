<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
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
									<?php echo _l('menus'); ?>
                                </h4>
                            </div>
                        </div>
                        <!-- View Mode Switcher & Actions -->
                        <div class="row tw-my-3">
							<div class="col-md-12">
								<?php if (staff_can('create', 'menu')): ?>
                                    <a href="<?php echo admin_url('catering_management_module/menus/menu'); ?>"
                                       class="btn btn-primary">
                                        <i class="fa fa-plus"></i> <?php echo _l('new_menu'); ?>
                                    </a>
								<?php endif; ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
						<?php
						render_datatable([
							'#',
							_l('menu_name'),
							_l('description'),
							_l('base_price_per_person'),
							_l('items_count'),
							_l('status'),
							_l('created_at'),
							_l('options')
						], 'menus'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function () {
        const table = initDataTable(
            ".table-menus",
            admin_url + "catering_management_module/menus/table",
            [],
            [],
            {},
            [0, "desc"],
        );

        // Delete menu item
        $('body').on('click', '.delete-menu', function (e) {
            e.preventDefault();
            const itemId = $(this).data('id');
            const itemName = $(this).data('name');

            if (confirm('<?php echo _l('confirm_delete'); ?> "' + itemName + '"?')) {
                $.ajax({
                    url: '<?php echo admin_url(CATERING_MANAGEMENT_MODULE_NAME.'/menus/delete/'); ?>' + itemId,
                    type: 'POST',
                    data: {
                        menu_id: itemId
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