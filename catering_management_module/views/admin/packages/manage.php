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
									<?php echo _l('packages'); ?>
                                </h4>
                            </div>
                        </div>
                        <!-- View Mode Switcher & Actions -->
                        <div class="row tw-my-3">
                            <div class="col-md-12">
								<?php if (staff_can('create', 'catering_packages')) { ?>
                                    <a href="<?php echo admin_url('catering_management_module/packages/package'); ?>" class="btn btn-primary pull-left display-block">
                                        <i class="fa fa-plus"></i>
										<?php echo _l('new_package'); ?>
                                    </a>
								<?php } ?>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
						<?php
						$table_data = [
							_l('package_name'),
							_l('description'),
							_l('price_per_person'),
							_l('min_guests'),
							_l('max_guests'),
							_l('items_count'),
							_l('active'),
							_l('options'),
						];
						render_datatable($table_data, 'packages');
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
        initDataTable('.table-packages', admin_url + 'catering_management_module/packages/table', [], [], [], [6, 'desc']);
    });
</script>
</body>
</html>
