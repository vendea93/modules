<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('hotel_management_system/rooms/room'); ?>"
                               onclick="new_source(); return false;" class="btn btn-primary">
                                <i class="fa-regular fa-plus tw-mr-1"></i>
								<?php echo _l('new_room'); ?>
                            </a>
                            <div class="btn-group pull-right mleft5">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<?php echo _l('filter_by_status'); ?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <li class="active"><a href="#" data-status="all"><?php echo _l('all'); ?></a></li>
                                    <li><a href="#" data-status="available"><?php echo _l('available'); ?></a></li>
                                    <li><a href="#" data-status="occupied"><?php echo _l('occupied'); ?></a></li>
                                    <li><a href="#" data-status="maintenance"><?php echo _l('maintenance'); ?></a></li>
                                    <li><a href="#" data-status="inactive"><?php echo _l('inactive'); ?></a></li>
                                </ul>
                            </div>
                            <div class="btn-group pull-right mleft5">
                                <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<?php echo _l('filter_by_property'); ?> <span class="caret"></span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-right property-filter">
                                    <li class="active"><a href="#" data-property="all"><?php echo _l('all_properties'); ?></a></li>
									<?php
									$CI =& get_instance();
									$CI->load->model('hotel_management_system/property_model');
									$properties = $CI->property_model->get_all(['status' => 'active']);
									foreach ($properties as $property)
									{
										?>
                                        <li><a href="#" data-property="<?php echo $property['id']; ?>"><?php echo $property['name']; ?></a></li>
									<?php } ?>
                                </ul>
                            </div>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>
                        <div class="clearfix"></div>
						<?php render_datatable([
							_l('id'),
							_l('room_photos'),
							_l('name'),
							_l('property'),
							_l('room_type'),
							_l('capacity'),
							_l('price_per_night'),
							_l('status'),
							_l('options'),
						], 'rooms'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function () {
        var roomsServerParams = {};
        var roomsTable = initDataTable('.table-rooms', admin_url + 'hotel_management_system/rooms/table', [0], [0], roomsServerParams, [1, 'asc']);

        // Status filter
        $('body').on('click', '.dropdown-menu a[data-status]', function (e) {
            e.preventDefault();
            const status = $(this).data('status');

            // Update active status in the UI
            $(this).parents('li').addClass('active').siblings().removeClass('active');

            // Update the filter parameter
            if (status !== 'all') {
                roomsServerParams.status = status;
            } else {
                delete roomsServerParams.status;
            }

            // Reload the table
            roomsTable.DataTable().ajax.reload();
        });

        // Property filter
        $('body').on('click', '.property-filter a[data-property]', function (e) {
            e.preventDefault();
            const property = $(this).data('property');

            // Update active status in the UI
            $(this).parents('li').addClass('active').siblings().removeClass('active');

            // Update the filter parameter
            if (property !== 'all') {
                roomsServerParams.property_id = property;
            } else {
                delete roomsServerParams.property_id;
            }

            // Reload the table
            roomsTable.DataTable().ajax.reload();
        });
    });
</script>