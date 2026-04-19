<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php echo form_open('admin/hotel_management_system/services/edit_assignment/' . $assignment['id'], ['id' => 'edit-service-assignment-form']); ?>
<input type="hidden" name="id" id="assignment_id" value="<?php echo $assignment['id']; ?>">

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label for="staff_id_edit" class="control-label"><?php echo _l('staff'); ?> <span class="text-danger">*</span></label>
            <select name="staff_id" id="staff_id_edit" class="selectpicker" data-width="100%" data-live-search="true" required>
                <option value=""><?php echo _l('select_staff'); ?></option>
				<?php foreach ($assignment['staff_list'] as $staff_member) { ?>
                    <option value="<?php echo $staff_member['staffid']; ?>" <?php if ($assignment['staff_id'] == $staff_member['staffid']) echo 'selected'; ?>><?php echo $staff_member['firstname'] . ' ' . $staff_member['lastname']; ?></option>
				<?php } ?>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="service_id_edit" class="control-label"><?php echo _l('service'); ?> <span class="text-danger">*</span></label>
            <select name="service_id" id="service_id_edit" class="selectpicker" data-width="100%" data-live-search="true" required>
                <option value=""><?php echo _l('select_service'); ?></option>
				<?php foreach ($assignment['services'] as $service) { ?>
                    <option value="<?php echo $service['id']; ?>" <?php if ($assignment['service_id'] == $service['id']) echo 'selected'; ?>><?php echo $service['name']; ?></option>
				<?php } ?>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="room_id_edit" class="control-label"><?php echo _l('room'); ?> <span class="text-danger">*</span></label>
            <select name="room_id" id="room_id_edit" class="selectpicker" data-width="100%" data-live-search="true" required>
                <option value=""><?php echo _l('select_room'); ?></option>
				<?php foreach ($assignment['rooms'] as $room) { ?>
                    <option value="<?php echo $room['id']; ?>" <?php if ($assignment['room_id'] == $room['id']) echo 'selected'; ?>><?php echo $room['name']; ?></option>
				<?php } ?>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="day_of_week_edit" class="control-label"><?php echo _l('day_of_week'); ?> <span class="text-danger">*</span></label>
            <select name="day_of_week" id="day_of_week_edit" class="selectpicker" data-width="100%" required>
				<?php foreach ($assignment['days_of_week'] as $day_number => $day_name) { ?>
                    <option value="<?php echo $day_number; ?>" <?php if ($assignment['day_of_week'] == $day_number) echo 'selected'; ?>><?php echo $day_name; ?></option>
				<?php } ?>
            </select>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="start_time_edit" class="control-label"><?php echo _l('start_time'); ?> <span class="text-danger">*</span></label>
            <input type="time" id="start_time_edit" name="start_time" class="form-control" required value="<?php echo date('H:i', strtotime($assignment['start_time'])); ?>">
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            <label for="end_time_edit" class="control-label"><?php echo _l('end_time'); ?> <span class="text-danger">*</span></label>
            <input type="time" id="end_time_edit" name="end_time" class="form-control" required value="<?php echo date('H:i', strtotime($assignment['end_time'])); ?>">
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="status_edit" class="control-label"><?php echo _l('status'); ?></label>
            <select name="status" id="status_edit" class="selectpicker" data-width="100%">
                <option value="active" <?php if ($assignment['status'] == 'active') echo 'selected'; ?>><?php echo _l('active'); ?></option>
                <option value="inactive" <?php if ($assignment['status'] == 'inactive') echo 'selected'; ?>><?php echo _l('inactive'); ?></option>
            </select>
        </div>
    </div>
    <div class="col-md-12">
        <div class="form-group">
            <label for="notes_edit" class="control-label"><?php echo _l('notes'); ?></label>
            <textarea id="notes_edit" name="notes" class="form-control" rows="3"><?php echo $assignment['notes']; ?></textarea>
        </div>
    </div>
</div>
<?php echo form_close(); ?>

<script>
    $(function () {
        // Initialize selectpicker
        $('.selectpicker').selectpicker('refresh');

        // Validate edit form
        $('#edit-service-assignment-form').validate({
            rules: {
                staff_id: 'required',
                service_id: 'required',
                room_id: 'required',
                day_of_week: 'required',
                start_time: 'required',
                end_time: 'required'
            }
        });
    });
</script>