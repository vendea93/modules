<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons tw-flex tw-gap-2">
                            <a href="<?php echo admin_url('hotel_management_system/services/service'); ?>"
                               class="btn btn-primary pull-left display-block">
                                <i class="fa fa-plus"></i> <?php echo _l('new_service'); ?>
                            </a>
                            <a href="<?php echo admin_url('hotel_management_system/services/assignments'); ?>" class="btn btn-primary pull-left display-block mright5">
								<?php echo _l('view_service_assignments'); ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div class="clearfix"></div>
						<?php render_datatable([
							_l('id'),
							_l('name'),
							_l('service_type'),
							_l('price'),
							_l('duration_minutes'),
							_l('status'),
							_l('options')
						], 'hms-services'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {
        var ServiceServerParams = {};
        initDataTable('.table-hms-services', admin_url + 'hotel_management_system/services/table', undefined, undefined, ServiceServerParams, [0, 'desc']);
    });
</script>