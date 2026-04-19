<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
/** @var array|null $event */
/** @var array $clients */
/** @var array $event_types */
/** @var string $title */
?>

<?php init_head(); ?>

<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">

                        <h4 class="tw-mt-0 tw-font-semibold tw-text-lg">
                            <i class="fa fa-calendar-plus tw-mr-2"></i>
							<?php echo $title; ?>
                        </h4>

                        <hr class="hr-panel-separator">

						<?php echo form_open(admin_url('catering_management_module/events/event/'.(isset($event) ? $event->eventid : '')), ['id' => 'event-form']); ?>

                        <div class="row">
                            <!-- Left Column -->
                            <div class="col-md-6">

                                <!-- Event Name -->
								<?php echo render_input('event_name', 'event_name', isset($event) ? $event->event_name : '', 'text', ['required' => TRUE]); ?>

                                <!-- Client Selection -->
                                <div class="form-group">
                                    <label for="client_id"><?php echo _l('client'); ?></label>
                                    <select name="client_id" id="client_id" class="selectpicker" data-width="100%" data-live-search="true">
                                        <option value=""><?php echo _l('select_client'); ?></option>
										<?php foreach ($clients as $client): ?>
                                            <option value="<?php echo $client['userid']; ?>"
												<?php if (isset($event) && $event->client_id == $client['userid']) {
													echo 'selected';
												} ?>>
												<?php echo $client['company']; ?>
                                            </option>
										<?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Event Type -->
                                <div class="form-group">
                                    <label for="event_type_id"><?php echo _l('event_type'); ?></label>
                                    <select name="event_type_id" id="event_type_id" class="selectpicker" data-width="100%">
                                        <option value=""><?php echo _l('select_event_type'); ?></option>
										<?php foreach ($event_types as $type): ?>
                                            <option value="<?php echo $type['etid']; ?>"
												<?php if (isset($event) && $event->event_type_id == $type['etid']) {
													echo 'selected';
												} ?>>
												<?php echo $type['name']; ?>
                                            </option>
										<?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Status (only on edit) -->
								<?php if (isset($event)): ?>
                                    <div class="form-group">
                                        <label for="status"><?php echo _l('status'); ?></label>
                                        <select name="status" id="status" class="selectpicker" data-width="100%">
											<?php
											$statuses = ['enquiry', 'quoted', 'confirmed', 'in_progress', 'completed', 'cancelled', 'lost'];
											foreach ($statuses as $status):
												?>
                                                <option value="<?php echo $status; ?>"
													<?php if ($event->status == $status) {
														echo 'selected';
													} ?>>
													<?php echo _l('event_status_'.$status); ?>
                                                </option>
											<?php endforeach; ?>
                                        </select>
                                    </div>
								<?php endif; ?>

                                <!-- Event Dates -->
                                <div class="row">
                                    <div class="col-md-6">
										<?php
										$value = isset($event) ? _dt($event->event_start) : '';
										echo render_datetime_input('event_start', 'event_start', $value, ['required' => TRUE]);
										?>
                                    </div>
                                    <div class="col-md-6">
										<?php
										$value = isset($event) && $event->event_end ? _dt($event->event_end) : '';
										echo render_datetime_input('event_end', 'event_end', $value);
										?>
                                    </div>
                                </div>

                            </div>

                            <!-- Right Column -->
                            <div class="col-md-6">

                                <!-- Venue -->
								<?php echo render_input('venue_name', 'venue_name', isset($event) ? $event->venue_name : ''); ?>

								<?php echo render_textarea('venue_address', 'venue_address', isset($event) ? $event->venue_address : '', ['rows' => 3]); ?>

                                <!-- Guest Counts -->
                                <div class="row">
                                    <div class="col-md-6">
										<?php echo render_input('guest_count_expected', 'guest_count_expected', isset($event) ? $event->guest_count_expected : 0, 'number', ['min' => 0]); ?>
                                    </div>
                                    <div class="col-md-6">
										<?php echo render_input('guest_count_final', 'guest_count_final', isset($event) ? $event->guest_count_final : '', 'number', ['min' => 0]); ?>
                                    </div>
                                </div>

                                <!-- Dietary Notes -->
								<?php echo render_textarea('dietary_notes', 'dietary_notes', isset($event) ? $event->dietary_notes : '', ['rows' => 3]); ?>

                                <!-- Internal Notes -->
								<?php echo render_textarea('internal_notes', 'internal_notes', isset($event) ? $event->internal_notes : '', ['rows' => 3]); ?>

                            </div>
                        </div>

                        <hr class="hr-panel-separator">

                        <!-- Additional Options -->
						<?php if ( ! isset($event)): ?>
                            <div class="checkbox checkbox-primary">
                                <input type="checkbox" name="create_project" id="create_project" value="1">
                                <label for="create_project"><?php echo _l('create_linked_project'); ?></label>
                            </div>
						<?php endif; ?>

                        <!-- Form Actions -->
                        <div class="btn-bottom-toolbar text-right">
                            <a href="<?php echo admin_url('catering_management_module/events/index'); ?>" class="btn btn-default">
								<?php echo _l('cancel'); ?>
                            </a>
                            <button type="submit" class="btn btn-primary">
								<?php echo _l('submit'); ?>
                            </button>
                        </div>

						<?php echo form_close(); ?>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php init_tail(); ?>

<script>
    $(function () {
        appValidateForm($('#event-form'), {
            event_name: 'required',
            client_id: 'required',
            event_type_id: 'required',
            event_start: 'required',
            event_end: 'required',
            venue_name: 'required',
            venue_address: 'required',
            guest_count_expected: 'required'
        });

        // Initialize datetime pickers
        init_datepicker();
        init_selectpicker();
    });
</script>