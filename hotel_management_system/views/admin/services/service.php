<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-7">
                <div class="panel_s">
                    <div class="panel-body">
                        <h4 class="no-margin"><?php echo $title; ?></h4>
                        <hr class="hr-panel-heading" />
						<?php echo form_open($this->uri->uri_string()); ?>
                        <div class="row">
                            <div class="col-md-12">
								<?php echo render_input('name', 'name', isset($service) ? $service->name : ''); ?>
                            </div>
                            <div class="col-md-6">
								<?php echo render_select('service_type', $service_types, ['key', 'value'], 'service_type', isset($service) ? $service->service_type : ''); ?>
                            </div>
                            <div class="col-md-6">
								<?php echo render_input('price', 'price', isset($service) ? $service->price : '', 'number', ['step' => '0.01']); ?>
                            </div>
                            <div class="col-md-6">
								<?php echo render_input('duration_minutes', 'duration_minutes', isset($service) ? $service->duration_minutes : '', 'number'); ?>
                            </div>
                            <div class="col-md-6">
								<?php
								$statuses = [
									['id' => 'active', 'name' => _l('active')],
									['id' => 'inactive', 'name' => _l('inactive')]
								];
								echo render_select('status', $statuses, ['id', 'name'], 'status', isset($service) ? $service->status : 'active');
								?>
                            </div>
                            <div class="col-md-12">
								<?php echo render_textarea('description', 'description', isset($service) ? $service->description : ''); ?>
                            </div>
                        </div>
                        <div class="btn-bottom-toolbar text-right">
                            <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
                        </div>
						<?php echo form_close(); ?>
                    </div>
                </div>
            </div>
			<?php if (isset($service)) { ?>
                <div class="col-md-5">
                    <div class="panel_s">
                        <div class="panel-body">
                            <h4 class="no-margin"><?php echo _l('service_info'); ?></h4>
                            <hr class="hr-panel-heading" />
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="bold"><?php echo _l('created_date'); ?></h5>
                                    <p><?php echo _dt($service->datecreated); ?></p>
                                </div>
								<?php if ($service->datemodified) { ?>
                                    <div class="col-md-6">
                                        <h5 class="bold"><?php echo _l('updated_date'); ?></h5>
                                        <p><?php echo _dt($service->datemodified); ?></p>
                                    </div>
								<?php } ?>
                            </div>
                            <div class="clearfix"></div>
                            <hr class="hr-panel-heading" />
                            <h4 class="no-margin"><?php echo _l('service_assignments'); ?></h4>
                            <hr class="hr-panel-heading" />
							<?php
							// Get assignments for this service
							$CI = &get_instance();
							$CI->db->select(db_prefix() . 'hms_service_assignments.*, ' .
								db_prefix() . 'staff.firstname, ' .
								db_prefix() . 'staff.lastname, ' .
								db_prefix() . 'hms_rooms.name as room_name, ' .
								db_prefix() . 'hms_properties.name as property_name');
							$CI->db->from(db_prefix() . 'hms_service_assignments');
							$CI->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid = ' . db_prefix() . 'hms_service_assignments.staff_id', 'left');
							$CI->db->join(db_prefix() . 'hms_rooms', db_prefix() . 'hms_rooms.id = ' . db_prefix() . 'hms_service_assignments.room_id', 'left');
							$CI->db->join(db_prefix() . 'hms_properties', db_prefix() . 'hms_properties.id = ' . db_prefix() . 'hms_rooms.property_id', 'left');
							$CI->db->where(db_prefix() . 'hms_service_assignments.service_id', $service->id);
							$CI->db->order_by(db_prefix() . 'hms_service_assignments.day_of_week', 'asc');
							$CI->db->order_by(db_prefix() . 'hms_service_assignments.start_time', 'asc');
							$assignments = $CI->db->get()->result_array();

							$days_of_week = hms_get_days_of_week();
							?>

							<?php if (count($assignments) > 0) { ?>
                                <div class="table-responsive">
                                    <table class="table table-striped">
                                        <thead>
                                        <tr>
                                            <th><?php echo _l('staff'); ?></th>
                                            <th><?php echo _l('room'); ?></th>
                                            <th><?php echo _l('day'); ?></th>
                                            <th><?php echo _l('time'); ?></th>
                                        </tr>
                                        </thead>
                                        <tbody>
										<?php foreach ($assignments as $assignment) { ?>
                                            <tr>
                                                <td><?php echo $assignment['firstname'] . ' ' . $assignment['lastname']; ?></td>
                                                <td><?php echo $assignment['property_name'] . ' - ' . $assignment['room_name']; ?></td>
                                                <td><?php echo $days_of_week[$assignment['day_of_week']]; ?></td>
                                                <td><?php echo date('H:i', strtotime($assignment['start_time'])) . ' - ' . date('H:i', strtotime($assignment['end_time'])); ?></td>
                                            </tr>
										<?php } ?>
                                        </tbody>
                                    </table>
                                </div>
							<?php } else { ?>
                                <p class="text-muted"><?php echo _l('no_service_assignments_found'); ?></p>
							<?php } ?>
                        </div>
                    </div>
                </div>
			<?php } ?>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script>
    $(function() {
        appValidateForm($('form'), {
            name: 'required',
            service_type: 'required',
            price: 'required',
            status: 'required'
        });
    });
</script>