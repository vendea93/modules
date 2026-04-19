<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div id="staffing-tab-content">
<div class="row">

    <div class="col-md-8">

        <!-- Staff Assignments -->
        <div class="panel_s">
            <div class="panel-body">
                <div class="clearfix mb-3">
                    <h4 class="pull-left tw-font-semibold">
                        <i class="fa fa-users tw-mr-2"></i>
						<?php echo _l('staff_assignments'); ?>
                    </h4>
                    <button class="btn btn-success btn-sm pull-right" id="add-staff-assignment">
                        <i class="fa fa-plus"></i> <?php echo _l('assign_staff'); ?>
                    </button>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                        <tr>
                            <th><?php echo _l('staff_member'); ?></th>
                            <th><?php echo _l('role'); ?></th>
                            <th><?php echo _l('shift_start'); ?></th>
                            <th><?php echo _l('shift_end'); ?></th>
                            <th><?php echo _l('hours'); ?></th>
							<?php if (can_view_event_costs()): ?>
                                <th><?php echo _l('hourly_cost'); ?></th>
                                <th><?php echo _l('total_cost'); ?></th>
							<?php endif; ?>
                            <th><?php echo _l('status'); ?></th>
                            <th><?php echo _l('options'); ?></th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if (!empty($event_staff)): ?>
                            <?php foreach ($event_staff as $assignment): ?>
                                <tr data-assignment-id="<?php echo $assignment['id']; ?>">
                                    <td>
                                        <div class="media">
                                            <div class="media-left">
                                                <img src="<?php echo staff_profile_image_url($assignment['staff_id'], 'thumb'); ?>" class="img-circle" width="30">
                                            </div>
                                            <div class="media-body">
                                                <strong><?php echo $assignment['staff_name']; ?></strong><br>
                                                <small class="text-muted"><?php echo $assignment['staff_email']; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="label" style="background-color: <?php echo $assignment['role_color'] ?? '#007cba'; ?>">
                                            <?php echo $assignment['role']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo _dt($assignment['shift_start']); ?></td>
                                    <td><?php echo _dt($assignment['shift_end']); ?></td>
                                    <td><?php echo number_format($assignment['hours'], 1); ?> hrs</td>
									<?php if (can_view_event_costs()): ?>
                                        <td>$<?php echo number_format($assignment['hourly_rate'], 2); ?>/hr</td>
                                        <td><strong>$<?php echo number_format($assignment['hours'] * $assignment['hourly_rate'], 2); ?></strong></td>
									<?php endif; ?>
                                    <td>
                                        <?php
                                        $status_colors = [
                                            'pending' => 'warning',
                                            'confirmed' => 'success',
                                            'declined' => 'danger',
                                            'completed' => 'default'
                                        ];
                                        $status_color = $status_colors[$assignment['status']] ?? 'default';
                                        ?>
                                        <span class="label label-<?php echo $status_color; ?>">
                                            <?php echo ucfirst($assignment['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn btn-default btn-xs edit-assignment" data-assignment-id="<?php echo $assignment['id']; ?>">
                                            <i class="fa fa-pencil"></i>
                                        </button>
                                        <button class="btn btn-danger btn-xs delete-assignment" data-assignment-id="<?php echo $assignment['id']; ?>">
                                            <i class="fa fa-remove"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted">
                                    <i class="fa fa-info-circle"></i> <?php echo _l('no_staff_assignments'); ?>
                                </td>
                            </tr>
                        <?php endif; ?>
                        </tbody>
						<?php if (can_view_event_costs() && !empty($event_staff)): ?>
                            <tfoot>
                            <tr class="active">
                                <td colspan="5" class="text-right"><strong><?php echo _l('total_labor_cost'); ?>:</strong></td>
                                <td colspan="2"><strong>$<?php echo number_format($staff_summary['total_cost'], 2); ?></strong></td>
                                <td colspan="2"></td>
                            </tr>
                            </tfoot>
						<?php endif; ?>
                    </table>
                </div>

            </div>
        </div>

        <!-- Schedule Timeline -->
        <div class="panel_s">
            <div class="panel-body">
                <h4 class="tw-font-semibold tw-mb-4">
                    <i class="fa fa-clock-o tw-mr-2"></i>
					<?php echo _l('shift_timeline'); ?>
                </h4>

                <div id="shift-timeline" style="height: 300px;">
                    <?php if (!empty($event_staff)): ?>
                        <div class="timeline-container">
                            <?php foreach ($event_staff as $assignment): ?>
                                <div class="timeline-item" style="border-left-color: <?php echo $assignment['role_color'] ?? '#007cba'; ?>">
                                    <div class="timeline-time">
                                        <?php echo date('H:i', strtotime($assignment['shift_start'])); ?> - 
                                        <?php echo date('H:i', strtotime($assignment['shift_end'])); ?>
                                    </div>
                                    <div class="timeline-content">
                                        <strong><?php echo $assignment['staff_name']; ?></strong> - <?php echo $assignment['role']; ?>
                                        <br><small class="text-muted"><?php echo number_format($assignment['hours'], 1); ?> hours</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fa fa-info-circle"></i> <?php echo _l('no_staff_assignments_timeline'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div>

    <!-- Right Sidebar -->
    <div class="col-md-4">

        <!-- Staff Summary -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-bar-chart tw-mr-2"></i>
					<?php echo _l('staff_summary'); ?>
                </h5>
                <div class="tw-space-y-3">
                    <div class="tw-flex tw-justify-between">
                        <span><?php echo _l('total_staff'); ?>:</span>
                        <strong><?php echo $staff_summary['total_staff']; ?></strong>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span><?php echo _l('confirmed'); ?>:</span>
                        <strong class="text-success"><?php echo $staff_summary['confirmed']; ?></strong>
                    </div>
                    <div class="tw-flex tw-justify-between">
                        <span><?php echo _l('pending'); ?>:</span>
                        <strong class="text-warning"><?php echo $staff_summary['pending']; ?></strong>
                    </div>
                    <?php if ($staff_summary['declined'] > 0): ?>
                        <div class="tw-flex tw-justify-between">
                            <span><?php echo _l('declined'); ?>:</span>
                            <strong class="text-danger"><?php echo $staff_summary['declined']; ?></strong>
                        </div>
                    <?php endif; ?>
                    <div class="tw-flex tw-justify-between">
                        <span><?php echo _l('total_hours'); ?>:</span>
                        <strong><?php echo number_format($staff_summary['total_hours'], 1); ?> hrs</strong>
                    </div>
					<?php if (can_view_event_costs()): ?>
                        <hr>
                        <div class="tw-flex tw-justify-between">
                            <span><?php echo _l('avg_hourly_rate'); ?>:</span>
                            <strong>$<?php echo number_format($staff_summary['avg_hourly_rate'], 2); ?></strong>
                        </div>
					<?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Conflict Warnings -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-exclamation-triangle tw-mr-2"></i>
					<?php echo _l('scheduling_alerts'); ?>
                </h5>
                <div id="conflict-warnings">
                    <div class="alert alert-success alert-sm">
                        <i class="fa fa-check"></i> <?php echo _l('no_scheduling_conflicts'); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Staff Roles Legend -->
        <div class="panel_s">
            <div class="panel-body">
                <h5 class="tw-font-semibold tw-mb-3">
                    <i class="fa fa-tag tw-mr-2"></i>
					<?php echo _l('roles'); ?>
                </h5>
                <div class="tw-space-y-2">
                    <?php foreach ($staff_roles as $role): ?>
                        <div>
                            <span class="label" style="background-color: <?php echo $role['color']; ?>">
                                <?php echo $role['role_name']; ?>
                            </span>
                            <?php if ($role['default_hourly_rate']): ?>
                                <small class="text-muted">($<?php echo number_format($role['default_hourly_rate'], 2); ?>/hr)</small>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Actions -->
        <div class="panel_s">
            <div class="panel-body">
                <button class="btn btn-primary btn-block" id="notify-all-staff">
                    <i class="fa fa-envelope"></i> <?php echo _l('notify_all_staff'); ?>
                </button>
                <button class="btn btn-default btn-block">
                    <i class="fa fa-file-pdf-o"></i> <?php echo _l('generate_run_sheet'); ?>
                </button>
            </div>
        </div>

    </div>

</div>
</div>

<!-- Add/Edit Staff Assignment Modal -->
<div class="modal fade" id="staff-assignment-modal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title"><?php echo _l('assign_staff'); ?></h4>
            </div>
            <div class="modal-body">
                <form id="staff-assignment-form">
                    <input type="hidden" id="assignment_id" name="assignment_id">
                    <input type="hidden" id="event_id" name="event_id" value="<?php echo $event->eventid; ?>">
                    
                    <div class="form-group">
                        <label><?php echo _l('staff_member'); ?> <span class="text-danger">*</span></label>
                        <select class="selectpicker" data-width="100%" data-live-search="true" id="staff_id" name="staff_id" required>
                            <option value=""><?php echo _l('select_staff'); ?></option>
                            <?php foreach ($available_staff as $staff): ?>
                                <option value="<?php echo $staff['staffid']; ?>">
                                    <?php echo $staff['firstname'] . ' ' . $staff['lastname']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo _l('role'); ?> <span class="text-danger">*</span></label>
                        <select class="selectpicker" data-width="100%" id="role" name="role" required>
                            <option value=""><?php echo _l('select_role'); ?></option>
                            <?php foreach ($staff_roles as $role): ?>
                                <option value="<?php echo $role['role_name']; ?>" 
                                        data-rate="<?php echo $role['default_hourly_rate']; ?>"
                                        data-color="<?php echo $role['color']; ?>">
                                    <?php echo $role['role_name']; ?>
                                    <?php if ($role['default_hourly_rate']): ?>
                                        ($<?php echo number_format($role['default_hourly_rate'], 2); ?>/hr)
                                    <?php endif; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
							<?= render_datetime_input('shift_start', 'shift_start'); ?>
                        </div>
                        <div class="col-md-6">
							<?= render_datetime_input('shift_end', 'shift_end'); ?>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo _l('hourly_rate'); ?></label>
                        <div class="input-group">
                            <span class="input-group-addon">$</span>
                            <input type="number" class="form-control" id="hourly_rate" name="hourly_rate" step="0.01" min="0">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label><?php echo _l('notes'); ?></label>
                        <textarea class="form-control" id="notes" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="button" class="btn btn-primary" id="save-staff-assignment"><?php echo _l('save'); ?></button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    $(function () {
        var eventId = <?php echo $event->eventid; ?>;

        // Add staff assignment
        $('#add-staff-assignment').on('click', function () {
            $('#staff-assignment-modal').modal('show');
            $('#staff-assignment-form')[0].reset();
            $('#assignment_id').val('');
            
            // Refresh selectpickers to clear selections
            $('#staff_id').selectpicker('refresh');
            $('#role').selectpicker('refresh');
        });

        // Role change - auto-fill hourly rate
        $('#role').on('change', function () {
            var selectedOption = $(this).find('option:selected');
            var defaultRate = selectedOption.data('rate');
            if (defaultRate) {
                $('#hourly_rate').val(defaultRate);
            }
        });

        // Save staff assignment
        $('#save-staff-assignment').on('click', function () {
            var formData = {
                event_id: $('#event_id').val(),
                staff_id: $('#staff_id').val(),
                role: $('#role').val(),
                shift_start: $('#shift_start').val(),
                shift_end: $('#shift_end').val(),
                hourly_rate: $('#hourly_rate').val(),
                notes: $('#notes').val()
            };

            var assignmentId = $('#assignment_id').val();
            var url = assignmentId ? 
                admin_url + 'catering_management_module/events/update_staff_assignment' :
                admin_url + 'catering_management_module/events/add_staff_assignment';

            if (assignmentId) {
                formData.assignment_id = assignmentId;
            }

            $.post(url, formData, function (response) {
                if (response.success) {
                    alert_float('success', response.message);
                    $('#staff-assignment-modal').modal('hide');
                    // Reload the entire staffing tab
                    reloadStaffingTab();
                } else {
                    alert_float('danger', response.message);
                    if (response.conflicts) {
                        showSchedulingConflicts(response.conflicts);
                    }
                }
            }, 'json');
        });

        // Edit assignment
        $('.edit-assignment').on('click', function () {
            var assignmentId = $(this).data('assignment-id');
            
            $.get(admin_url + 'catering_management_module/events/get_staff_assignment/' + assignmentId, function (response) {
                if (response.success) {
                    var assignment = response.assignment;
                    $('#assignment_id').val(assignment.id);
                    
                    // Set staff selection
                    $('#staff_id').val(assignment.staff_id);
                    $('#staff_id').selectpicker('refresh');
                    
                    // Set role selection
                    $('#role').val(assignment.role);
                    $('#role').selectpicker('refresh');
                    
                    // Set datetime fields
                    $('#shift_start').val(assignment.shift_start);
                    $('#shift_end').val(assignment.shift_end);
                    
                    // Set other fields
                    $('#hourly_rate').val(assignment.hourly_rate);
                    $('#notes').val(assignment.notes);
                    
                    $('#staff-assignment-modal').modal('show');
                } else {
                    alert_float('danger', response.message);
                }
            }, 'json');
        });

        // Delete assignment
        $('.delete-assignment').on('click', function () {
            var assignmentId = $(this).data('assignment-id');
            
            if (confirm('<?php echo _l('confirm_delete_assignment'); ?>')) {
                $.post(admin_url + 'catering_management_module/events/remove_staff_assignment', {
                    assignment_id: assignmentId
                }, function (response) {
                    if (response.success) {
                        alert_float('success', response.message);
                        // Reload the entire staffing tab
                        reloadStaffingTab();
                    } else {
                        alert_float('danger', response.message);
                    }
                }, 'json');
            }
        });

        // Reload the staffing tab content
        function reloadStaffingTab() {
            $.get(admin_url + 'catering_management_module/events/get_staffing_content/' + eventId, function(response) {
                if (response.success) {
                    // Replace only the content inside the wrapper
                    $('#staffing-tab-content').html(response.html);
                    
                    // Re-initialize event handlers for the new content
                    initializeStaffingHandlers();
                } else {
                    alert_float('danger', 'Failed to reload staffing data');
                }
            }, 'json');
        }

        // Initialize event handlers for staffing tab
        function initializeStaffingHandlers() {
            // Re-bind all event handlers after content reload
            $('.edit-assignment').off('click').on('click', function () {
                var assignmentId = $(this).data('assignment-id');
                
                $.get(admin_url + 'catering_management_module/events/get_staff_assignment/' + assignmentId, function (response) {
                    if (response.success) {
                        var assignment = response.assignment;
                        $('#assignment_id').val(assignment.id);
                        
                        // Set staff selection
                        $('#staff_id').val(assignment.staff_id);
                        $('#staff_id').selectpicker('refresh');
                        
                        // Set role selection
                        $('#role').val(assignment.role);
                        $('#role').selectpicker('refresh');
                        
                        // Set datetime fields
                        $('#shift_start').val(assignment.shift_start);
                        $('#shift_end').val(assignment.shift_end);
                        
                        // Set other fields
                        $('#hourly_rate').val(assignment.hourly_rate);
                        $('#notes').val(assignment.notes);
                        
                        $('#staff-assignment-modal').modal('show');
                    } else {
                        alert_float('danger', response.message);
                    }
                }, 'json');
            });

            $('.delete-assignment').off('click').on('click', function () {
                var assignmentId = $(this).data('assignment-id');
                
                if (confirm('<?php echo _l('confirm_delete_assignment'); ?>')) {
                    $.post(admin_url + 'catering_management_module/events/remove_staff_assignment', {
                        assignment_id: assignmentId
                    }, function (response) {
                        if (response.success) {
                            alert_float('success', response.message);
                            reloadStaffingTab();
                        } else {
                            alert_float('danger', response.message);
                        }
                    }, 'json');
                }
            });
        }

        // Show scheduling conflicts
        function showSchedulingConflicts(conflicts) {
            var html = '';
            conflicts.forEach(function(conflict) {
                html += '<div class="alert alert-warning alert-sm">';
                html += '<i class="fa fa-warning"></i> ';
                html += 'Conflict with ' + conflict.event_name + ' (' + conflict.shift_start + ' - ' + conflict.shift_end + ')';
                html += '</div>';
            });
            $('#conflict-warnings').html(html);
        }

        // Notify all staff
        $('#notify-all-staff').on('click', function () {
            if (confirm('<?php echo _l('confirm_notify_all_staff'); ?>')) {
                $.post(admin_url + 'catering_management_module/events/notify_all_staff', {
                    event_id: eventId
                }, function (response) {
                    if (response.success) {
                        alert_float('success', response.message);
                    } else {
                        alert_float('danger', response.message);
                    }
                }, 'json');
            }
        });
        
        // Initialize handlers on page load
        initializeStaffingHandlers();
    });
});
</script>

<style>
.timeline-container {
    position: relative;
    padding-left: 20px;
}

.timeline-item {
    position: relative;
    padding: 10px 0 10px 30px;
    border-left: 3px solid #ddd;
    margin-bottom: 10px;
}

.timeline-item:before {
    content: '';
    position: absolute;
    left: -6px;
    top: 15px;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background-color: #ddd;
}

.timeline-time {
    font-weight: bold;
    color: #666;
    font-size: 12px;
}

.timeline-content {
    margin-top: 5px;
}
</style>