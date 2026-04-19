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
									<?php echo _l('item_categories'); ?>
                                </h4>
                            </div>
                        </div>
                        <!-- View Mode Switcher & Actions -->
                        <div class="row tw-my-3">
                            <div class="col-md-12">
								<?php if (staff_can('create', 'catering_categories')) { ?>
                                    <a href="<?php echo admin_url('catering_management_module/categories/category'); ?>" class="btn btn-primary pull-left">
                                        <i class="fa fa-plus"></i>
										<?php echo _l('new_category'); ?>
                                    </a>
								<?php } ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>

                        <div class="clearfix"></div>
						<?php
						$table_data = [
							_l('category_name'),
							_l('parent_category'),
							_l('display_order'),
							_l('status'),
							_l('options'),
						];

						render_datatable($table_data, 'categories');
						?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    $(function () {
        initDataTable('.table-categories', admin_url + "catering_management_module/categories/table", [4], [4], {}, [0, 'asc']);
    });
</script>
</body>
</html>