<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                        <div class="_buttons">
                            <a href="<?php echo admin_url('hotel_management_system/services'); ?>" class="btn btn-default pull-left display-block">
								<?php echo _l('back_to_services'); ?>
                            </a>
                            <a href="#" class="btn btn-info pull-right display-block" id="add_assigment" onclick="add_service_assignment(); return false;">
								<?php echo _l('new_service_assignment'); ?>
                            </a>
                        </div>
                        <div class="clearfix"></div>
                        <hr class="hr-panel-heading"/>

                        <!-- Filters -->
                        <div class="row">
                            <div class="col-md-12">
                                <h4><?php echo _l('filter_assignments'); ?></h4>
                            </div>
                            <div class="col-md-12">
                                <form method="get" action="<?php echo admin_url('hotel_management_system/services/assignments'); ?>">
                                    <div class="row">
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="staff_id"><?php echo _l('staff'); ?></label>
                                                <select name="staff_id" id="staff_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                    <option value=""><?php echo _l('all'); ?></option>
													<?php foreach ($staff_members as $staff_member) { ?>
                                                        <option value="<?php echo $staff_member['staffid']; ?>" <?php if (isset($filters['staff_id']) && $filters['staff_id'] == $staff_member['staffid']) echo 'selected'; ?>><?php echo $staff_member['firstname'] . ' ' . $staff_member['lastname']; ?></option>
													<?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="property_id"><?php echo _l('property'); ?></label>
                                                <select name="property_id" id="property_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true">
                                                    <option value=""><?php echo _l('all'); ?></option>
													<?php foreach ($properties as $property) { ?>
                                                        <option value="<?php echo $property['id']; ?>" <?php if (isset($filters['property_id']) && $filters['property_id'] == $property['id']) echo 'selected'; ?>><?php echo $property['name']; ?></option>
													<?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-group">
                                                <label for="day_of_week"><?php echo _l('day_of_week'); ?></label>
                                                <select name="day_of_week" id="day_of_week" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                                                    <option value=""><?php echo _l('all'); ?></option>
													<?php foreach ($days_of_week as $day_number => $day_name) { ?>
                                                        <option value="<?php echo $day_number; ?>" <?php if (isset($filters['day_of_week']) && $filters['day_of_week'] == $day_number) echo 'selected'; ?>><?php echo $day_name; ?></option>
													<?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-3">
                                            <div class="form-group">
                                                <label for="service_id"><?php echo _l('service'); ?></label>
                                                <select name="service_id" id="service_id" class="selectpicker" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>" data-live-search="true">
                                                    <option value=""><?php echo _l('all'); ?></option>
													<?php foreach ($services as $service) { ?>
                                                        <option value="<?php echo $service['id']; ?>" <?php if (isset($filters['service_id']) && $filters['service_id'] == $service['id']) echo 'selected'; ?>><?php echo $service['name']; ?></option>
													<?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-1">
                                            <div class="form-group">
                                                <label>&nbsp;</label>
                                                <button type="submit" class="btn btn-info btn-block"><?php echo _l('filter'); ?></button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <hr class="hr-panel-heading"/>

                        <!-- Assignments list -->
                        <div class="clearfix"></div>
						<?php if (isset($assignments) && count($assignments) > 0) { ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                    <tr>
                                        <th><?php echo _l('staff'); ?></th>
                                        <th><?php echo _l('property'); ?></th>
                                        <th><?php echo _l('room'); ?></th>
                                        <th><?php echo _l('service'); ?></th>
                                        <th><?php echo _l('day'); ?></th>
                                        <th><?php echo _l('time'); ?></th>
                                        <th><?php echo _l('status'); ?></th>
                                        <th><?php echo _l('options'); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody>
									<?php foreach ($assignments as $assignment) { ?>
                                        <tr>
                                            <td>
                                                <a href="<?php echo admin_url('hotel_management_system/services/staff_schedule/' . $assignment['staff_id']); ?>">
													<?php echo $assignment['firstname'] . ' ' . $assignment['lastname']; ?>
                                                </a>
                                            </td>
                                            <td><?php echo $assignment['property_name']; ?></td>
                                            <td><?php echo $assignment['room_name']; ?></td>
                                            <td><?php echo $assignment['service_name']; ?></td>
                                            <td><?php echo $days_of_week[$assignment['day_of_week']]; ?></td>
                                            <td><?php echo date('H:i', strtotime($assignment['start_time'])) . ' - ' . date('H:i', strtotime($assignment['end_time'])); ?></td>
                                            <td>
												<?php
												$status_badge = 'success';
												if ($assignment['status'] == 'inactive')
												{
													$status_badge = 'danger';
												}
												?>
                                                <span class="label label-<?php echo $status_badge; ?>"><?php echo _l($assignment['status']); ?></span>
                                            </td>
                                            <td>
                                                <a href="#" class="btn btn-default btn-icon" onclick="edit_service_assignment(<?php echo $assignment['id']; ?>); return false;">
                                                    <i class="fa fa-pencil"></i>
                                                </a>
                                                <a href="#" class="btn btn-danger btn-icon" onclick="delete_service_assignment(<?php echo $assignment['id']; ?>); return false;">
                                                    <i class="fa fa-remove"></i>
                                                </a>
                                            </td>
                                        </tr>
									<?php } ?>
                                    </tbody>
                                </table>
                            </div>
						<?php } else { ?>
                            <div class="text-center">
                                <p class="text-muted"><?php echo _l('no_service_assignments_found'); ?></p>
                            </div>
						<?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Service Assignment Modal -->
<div class="modal fade" id="service_assignment_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('add_service_assignment'); ?></h4>
            </div>
            <div class="modal-body">
				<?php echo form_open('admin/hotel_management_system/services/add_assignment', ['id' => 'service-assignment-form']); ?>
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="staff_id_modal" class="control-label"><?php echo _l('staff'); ?> <span class="text-danger">*</span></label>
                            <select name="staff_id" id="staff_id_modal" class="selectpicker" data-width="100%" data-live-search="true" required>
                                <option value=""><?php echo _l('select_staff'); ?></option>
								<?php foreach ($staff_members as $staff_member) { ?>
                                    <option value="<?php echo $staff_member['staffid']; ?>"><?php echo $staff_member['firstname'] . ' ' . $staff_member['lastname']; ?></option>
								<?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="service_id_modal" class="control-label"><?php echo _l('service'); ?> <span class="text-danger">*</span></label>
                            <select name="service_id" id="service_id_modal" class="selectpicker" data-width="100%" data-live-search="true" required>
                                <option value=""><?php echo _l('select_service'); ?></option>
								<?php foreach ($services as $service) { ?>
                                    <option value="<?php echo $service['id']; ?>"><?php echo $service['name']; ?></option>
								<?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="property_id_modal" class="control-label"><?php echo _l('property'); ?> <span class="text-danger">*</span></label>
                            <select name="property_id" id="property_id_modal" class="selectpicker" data-width="100%" data-live-search="true" required onchange="get_property_rooms(this.value)">
                                <option value=""><?php echo _l('select_property'); ?></option>
								<?php foreach ($properties as $property) { ?>
                                    <option value="<?php echo $property['id']; ?>"><?php echo $property['name']; ?></option>
								<?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="room_id" class="control-label"><?php echo _l('room'); ?> <span class="text-danger">*</span></label>
                            <select name="room_id" id="room_id" class="selectpicker" data-width="100%" data-live-search="true" required>
                                <option value=""><?php echo _l('select_room'); ?></option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="day_of_week_modal" class="control-label"><?php echo _l('day_of_week'); ?> <span class="text-danger">*</span></label>
                            <select name="day_of_week" id="day_of_week_modal" class="selectpicker" data-width="100%" required>
								<?php foreach ($days_of_week as $day_number => $day_name) { ?>
                                    <option value="<?php echo $day_number; ?>"><?php echo $day_name; ?></option>
								<?php } ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="start_time" class="control-label"><?php echo _l('start_time'); ?> <span class="text-danger">*</span></label>
                            <input type="time" id="start_time" name="start_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="end_time" class="control-label"><?php echo _l('end_time'); ?> <span class="text-danger">*</span></label>
                            <input type="time" id="end_time" name="end_time" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="notes" class="control-label"><?php echo _l('notes'); ?></label>
                            <textarea id="notes" name="notes" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                </div>
				<?php echo form_close(); ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-info" onclick="submit_service_assignment()"><?php echo _l('submit'); ?></button>
            </div>
        </div>
    </div>
</div>

<!-- Edit Service Assignment Modal -->
<div class="modal fade" id="edit_service_assignment_modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title"><?php echo _l('edit_service_assignment'); ?></h4>
            </div>
            <div class="modal-body">
                <!-- Content will be loaded via AJAX -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-info" onclick="update_service_assignment()"><?php echo _l('update'); ?></button>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>
<script>
    // Add new service assignment
    function add_service_assignment() {
        $('#service_assignment_modal').modal('show');
        $('#service-assignment-form')[0].reset();
        $('.selectpicker').selectpicker('refresh');
    }

    // Submit service assignment form
    function submit_service_assignment() {
        var form = $('#service-assignment-form');

        if (!form.valid()) {
            return;
        }

        var data = form.serialize();

        $.ajax({
            url: admin_url + 'hotel_management_system/services/add_assignment',
            type: 'post',
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert_float('success', response.message);
                    $('#service_assignment_modal').modal('hide');
                    setTimeout(function () {
                        location.reload();
                    }, 3000);
                } else {
                    alert_float('danger', response.message);
                }
            }
        });
    }

    // Get rooms for selected property
    function get_property_rooms(property_id) {
        if (property_id) {
            $.ajax({
                url: admin_url + 'hotel_management_system/services/get_rooms_by_property/' + property_id,
                type: 'get',
                dataType: 'json',
                success: function (response) {
                    let options = '<option value=""><?php echo _l('select_room'); ?></option>';
                    const rooms = response.rooms;
                    console.log(rooms);
                    $.each(rooms, function (index, room) {
                        console.log(room);
                        options += '<option value="' + room.id + '">' + room.name + '</option>';
                    });

                    $('#room_id').html(options);
                    $('#room_id').selectpicker('refresh');
                }
            });
        } else {
            $('#room_id').html('<option value=""><?php echo _l('select_room'); ?></option>');
            $('#room_id').selectpicker('refresh');
        }
    }

    // Edit service assignment
    function edit_service_assignment(id) {
        $.ajax({
            url: admin_url + 'hotel_management_system/services/edit_assignment/' + id,
            type: 'get',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    $('#edit_service_assignment_modal .modal-body').html(response.html);
                    $('#edit_service_assignment_modal').modal('show');
                    $('.selectpicker').selectpicker('refresh');
                } else {
                    alert_float('danger', response.message);
                }
            }
        });
    }

    // Update service assignment
    function update_service_assignment() {
        var form = $('#edit-service-assignment-form');

        if (!form.valid()) {
            return;
        }

        var data = form.serialize();
        var id = $('#assignment_id').val();

        $.ajax({
            url: admin_url + 'hotel_management_system/services/edit_assignment/' + id,
            type: 'post',
            data: data,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert_float('success', response.message);
                    $('#edit_service_assignment_modal').modal('hide');
                    location.reload();
                } else {
                    alert_float('danger', response.message);
                }
            }
        });
    }

    // Delete service assignment
    function delete_service_assignment(id) {
        if (confirm('<?php echo _l('confirm_delete_service_assignment'); ?>')) {
            $.ajax({
                url: admin_url + 'hotel_management_system/services/delete_assignment/' + id,
                type: 'get',
                dataType: 'json',
                success: function (response) {
                    if (response.success) {
                        alert_float('success', response.message);
                        location.reload();
                    } else {
                        alert_float('danger', response.message);
                    }
                }
            });
        }
    }

    $(function () {
        // Validate service assignment form
        $('#service-assignment-form').validate({
            rules: {
                staff_id: 'required',
                service_id: 'required',
                property_id: 'required',
                room_id: 'required',
                day_of_week: 'required',
                start_time: 'required',
                end_time: 'required'
            }
        });
    });
</script>